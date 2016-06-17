<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Media\Events\FileWasFailedToUpload;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteFileWhenUploadFailed implements ShouldQueue
{
    /**
     * @var FilesystemFactory
     */
    protected $filesystemFactory;

    /**
     * @param FilesystemFactory $filesystemFactory
     */
    public function __construct(FilesystemFactory $filesystemFactory)
    {
        $this->filesystemFactory = $filesystemFactory;
    }

    /**
     * @param  FileWasFailedToUpload $event [description]
     * @return void
     */
    public function handle(FileWasFailedToUpload $event)
    {
        $files = $event->uploadedFiles;

        // When a file is failed to upload
        // It will remain in local storage
        $filesystem = $this->filesystemFactory->disk('local');

        foreach ($files as $file) {
            // Ensure file is exist before delete it
            if($filesystem->exists($file)) {
                $filesystem->delete($file);
            }
        }
    }
}