<?php

namespace Inoplate\Media\Services\Uploader;

use Closure;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

abstract class BaseReceiver
{
    /**
     * @var int
     */
    protected $maximumUploadSize = 8388608; // 8 Mb

    /**
     * @var array
     */
    protected $allowedUploadExtensions = [
                    'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', // Image file extensions
                    'pdf', 'doc', 'docx', 'ppt', 'pptx', 'pps', 'ppsx', 'odt', 'xls', 'xlsx', 'psd', // Documents extensions
                    'mp3', 'm4a', 'ogg', 'wav', // Audio extensions
                    'mp4', 'm4v', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gp', '3g2' // Video extensions
                ];

    /**
     * @var stirng
     */
    protected $destination;

    /**
     * @var string
     */
    protected $fileInputField = 'file';

    /**
     * Set file input field
     * 
     * @param string $field
     */
    public function setFileInputField($field)
    {
        $this->fileInputField = $field;
    }

    /**
     * Retrieve file input field
     * 
     * @return string
     */
    public function getFileInputField()
    {
        return $this->fileInputField;
    }

    /**
     * Set maximum file upload size
     * @param int $size
     */
    public function setMaximumUploadSize($size)
    {
        $this->maximumUploadSize = $size;
    }

    /**
     * Retrieve maximum upload size
     * @return int
     */
    public function getMaximumUploadSize()
    {
        return $this->maximumUploadSize;
    }

    /**
     * Set allowed upload extensions
     * @param array $extensions
     */
    public function setAllowedUploadExtensions($extensions = [])
    {
        $this->allowedUploadExtensions = $extensions;
    }

    /**
     * Retrieve allowed apload extensions
     * @return array
     */
    public function getAllowedUploadExtensions()
    {
        return $this->allowedUploadExtensions;
    }

    /**
     * Generating unique name
     * 
     * @param string $name basic file name
     * @return string
     */
    protected function generateFileDestination($name)
    {
        // We need to separate files upload by year and month
        // It's so usefull to reduce file load with directory splitting
        $dir = Carbon::now()->year.'/'.Carbon::now()->month;

        // Give it a unique uuid prefix to make sure it is unique
        // To avoid annoying space we need to slug it
        $name = Uuid::uuid4()->toString().'-'.$this->slugName($name);

        $destination = $dir.'/'.$name;

        return $destination;
    }

    /**
     * Normalize name
     * 
     * @param  string $name
     * @return string
     */
    protected function slugName($name)
    {
        $exploded = explode('.', $name);

        $name = str_slug(implode('.', array_slice( $exploded , 0, -1) ));
        $extension = $exploded[ count($exploded) - 1 ];

        return $name.'.'.$extension;
    }

    /**
     * Retrieve file input
     * @param  Request $request
     * @return UploadedFile
     */
    protected function getFile(Request $request)
    {
        return $request->file($this->getFileInputField());
    }

    /**
     * Ensure that directory exist
     * @param  Filesystem $filesystem
     * @param  string     $path
     * @return void
     */
    protected function ensureDirectoryExist(Filesystem $filesystem, $path)
    {
        // Remove file name from string to get directory
        $dir = implode('/', array_slice( explode('/', $path) , 0, -1 ));

        if( !$filesystem->exists($dir)) {
            $filesystem->makeDirectory($dir);
        }
    }

    /**
     * Validate file extension
     * 
     * @param  string $path
     * @return void
     */
    protected function validateFileExtension($path)
    {
        $extensions = $this->getAllowedUploadExtensions();

        // Get file mime type
        $mime = $this->filesystem->mimeType($path);

        $extensionGuesser = ExtensionGuesser::getInstance();
        $extension = $extensionGuesser->guess($mime);

        if( !in_array($extension, $extensions)) {
            throw new UnallowedFileExtension("Uploading [.$extension] extension is prohibited");
        }
    }

    /**
     * Validating file size
     * @return void
     */
    protected function validateFileSize()
    {
        $nextFileSize = $this->getFile($this->request)->getClientSize();
        $maxUploadSize = $this->getMaximumUploadSize();
        $totalChunks = $this->request->input('flowTotalChunks');
        $size = $nextFileSize;

        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunk = $this->getChunkPath($i);
            if ($this->checkChunk($i)) {
                $size += $this->filesystem->size($chunk);
            }

            if($size > $maxUploadSize) {
                throw new MaximumUploadSizeExceeded("File upload must not exceed ".format_size_units($maxUploadSize));
            }
        }
    }
}