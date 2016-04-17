<?php

namespace Inoplate\Media\Services\Uploader\Flow;

use Closure;
use Carbon\Carbon;
use Inoplate\Media\Services\Uploader\Receiver as Contract;
use Inoplate\Media\Services\Uploader\BaseReceiver;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class Receiver extends BaseReceiver implements Contract
{
    /**
     * @var Illuminate\Contracts\Filesyste\Filesystem
     */
    protected $filesystem;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $chunkTempPath;

    /**
     * @var int
     */
    protected $chunkExpiry = 86400; // A day

    /**
     * Create new Receiver instance
     *
     * @param FilesystemFactory $filesystemFactory
     * @param Request $request
     * @param array $config
     * @return void
     */
    public function __construct(
        FilesystemFactory $filesystemFactory, 
        Request $request,
        array $config = []
    ) {
        // Since the strategy is to save each upload to local storage first,
        // We need to set the filesystem to local
        $this->filesystem = $filesystemFactory->disk('local');
        $this->request = $request;

        $this->setup($config);
    }

    /**
     * Receive file upload
     * 
     * @param  Closure $handler
     * @return mixed
     */
    public function receive(Closure $callback = null)
    {
        if($this->request->method() == 'GET') {
            return $this->checkChunk() ? response('Ok', 200) : response('No Content', 204);
        }else {
            return $this->receiveFile($callback);
        }
    }

    /**
     * Receve file
     * 
     * @return mixed
     */
    protected function receiveFile(Closure $callback = null)
    {
        // Validate file size
        $this->validateFileSize();

        $this->saveChunk();

        // Determine if all chunk already in place
        if($this->isChunkComplete()) {
            $name = $this->getClientFilename();

            if( !$this->destination) {
                // Generating file name
                $destination = $this->generateFileDestination($name);

                // Set destination 
                $this->destination = $destination;
            }

            // Ensure directory existency
            $this->ensureDirectoryExist($this->filesystem, $destination);

            $this->saveFile($destination);

            $this->validateFileExtension($destination);

            if($callback) {
                return call_user_func_array($callback, compact('destination'));
            }
        }
    }

    /**
     * Saving complete file
     * 
     * @param  string $destination
     * @return void
     */
    protected function saveFile($destination)
    {
        $base = $this->getBasePath();

        $destination = $base.'/'.$destination;

        $fh = fopen($destination, 'wb');
        if (!$fh) {
            throw new FileOpenException('failed to open destination file: '.$destination);
        }

        if (!flock($fh, LOCK_EX | LOCK_NB, $blocked)) {
            // @codeCoverageIgnoreStart
            if ($blocked) {
                // Concurrent request has requested a lock.
                // File is being processed at the moment.
                // Warning: lock is not checked in windows.
                // Throw exception;
                throw new Exception("Unable to obtain lock on file: $file");
            }
            // @codeCoverageIgnoreEnd
            throw new FileLockException('failed to lock file: '.$destination);
        }

        $totalChunks = $this->request->input('flowTotalChunks');

        try {

            for ($i = 1; $i <= $totalChunks; $i++) {
                $file = $this->getChunkPath($i);

                $chunk = @fopen($base.$file, "rb");

                if (!$chunk) {
                    // Something was wrong
                    // throw Exception;
                    throw new Exception("Can't open file: $file");       
                }

                stream_copy_to_stream($chunk, $fh);
                fclose($chunk);

                // Save storage space by immediately delete chunks
                @unlink($base.$file);
            }
        } catch (\Exception $e) {
            flock($fh, LOCK_UN);
            fclose($fh);
            throw $e;
        }

        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
     * Saving chunk
     * 
     * @return void
     */
    protected function saveChunk()
    {
        $file = $this->getFile($this->request);
        $dir = $this->chunkTempPath;

        // Ensure directory is exist
        $this->ensureDirectoryExist($this->filesystem, $dir);

        $destination = $this->getChunkPath($this->getCurrentChunkNumber());

        $this->filesystem->put($destination, fopen($file->getRealPath(), 'rb'));
    }

    /**
     * Determine if chunk is complete
     * 
     * @return boolean
     */
    protected function isChunkComplete()
    {
        $totalChunks = $this->request->input('flowTotalChunks');
        $totalChunksSize = 0;

        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunk = $this->getChunkPath($i);
            if ( !$this->checkChunk($i)) {
                return false;
            }
            $totalChunksSize += $this->filesystem->size($chunk);
        }

        return $this->request->input('flowTotalSize') == $totalChunksSize;
    }

    /**
     * Retrieve current chunk number
     * 
     * @return string
     */
    protected function getCurrentChunkNumber()
    {
        return $this->request->input('flowChunkNumber');
    }

    /**
     * Retrieve chunk path
     * 
     * @param  string $part
     * @return string
     */
    protected function getChunkPath($part)
    {
        $identifier = $this->getFileIdentifier();

        return $this->chunkTempPath.'/'.$identifier.'_'.$part;
    }

    /**
     * Checking chunk
     * 
     * @return Response
     */
    protected function checkChunk($number = null)
    {
        $number = $number ?: $this->getCurrentChunkNumber();
        $chunk = $this->getChunkPath($number);

        // Check if chunk is exists
        if( !$this->filesystem->exists($chunk)) {
            return false;
        }

        $lastModified = $this->filesystem->lastModified($chunk);
        $expiry = Carbon::createFromTimestamp($lastModified)->addSeconds($this->getChunkExpiry());

        // Check if chunk is expired
        if( Carbon::now()->gt($expiry) ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve chunk expiry
     * 
     * @return int
     */
    protected function getChunkExpiry()
    {
        return $this->chunkExpiry;
    }

    /**
     * Retrieve client file name
     * 
     * @return string
     */
    protected function getClientFilename()
    {
        return $this->request->input('flowFilename');
    }

    /**
     * Retrieve file identifier
     * 
     * @return string
     */
    protected function getFileIdentifier()
    {
        return sha1($this->request->input('flowIdentifier'));
    }

    /**
     * Setup receiver
     * 
     * @param  array  $config
     * @return void
     */
    protected function setup(array $config = [])
    {
        $this->chunkTempPath = isset($config['chunks_temp_path']) ?: 'chunks';

        if(isset($config['maximum_upload_size'])) {
            $this->setMaximumUploadSize($config['maximum_upload_size']);
        }

        if(isset($config['allowed_extension'])) {
            $this->setAllowedUploadExtensions($config['allowed_extension']);
        }

        if(isset($config['input_field'])) {
            $this->setFileInputField($config['input_field']);
        }
        
    }

    /**
     * Retrieve local storage base path
     * 
     * @return string
     */
    protected function getBasePath()
    {
        return $this->filesystem->getDriver()->getAdapter()->getPathPrefix();
    }
}