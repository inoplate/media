<?php

namespace Inoplate\Media\Exceptions;

use Exception;

class BaseException extends Exception
{
    /**
     * Library's uploaded files
     * 
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * Library's mete
     * 
     * @var array
     */
    protected $meta = [];

    /**
     * Create new instance
     * 
     * @param array          $uploadedFiles
     * @param array          $meta
     * @param string         $message
     * @param integer        $code
     * @param Exception|null $previous
     */
    public function __construct($uploadedFiles, $meta, $message = '', $code = 0 , Exception $previous = null)
    {
        $this->uploadedFiles = $uploadedFiles;
        $this->meta = $meta;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieve library's uploaded files
     * 
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Retrieve library's meta
     * 
     * @return array
     */
    public function getLibraryMeta()
    {
        return $this->meta;
    }
}