<?php

namespace Inoplate\Media\Events;

class FileWasFailedToUpload
{
    /**
     * Files that successfully uploaded
     * 
     * @var array
     */
    public $uploadedFiles = [];

    /**
     * Create new FileWasFailedToUpload instance
     * 
     * @param array $uploadedFiles
     */
    public function __construct($uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;
    }
}