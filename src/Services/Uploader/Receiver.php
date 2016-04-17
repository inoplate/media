<?php

namespace Inoplate\Media\Services\Uploader;

use Closure;

interface Receiver
{
    /**
     * Set file input field
     * 
     * @param string $field
     */
    public function setFileInputField($field);

    /**
     * Retrieve file input field
     * 
     * @return string
     */
    public function getFileInputField();

    /**
     * Receive file upload
     * 
     * @param  Closure $handler
     * @return mixed
     */
    public function receive(Closure $callback = null);

    /**
     * Set maximum file upload size
     * 
     * @param int $size
     */
    public function setMaximumUploadSize($size);

    /**
     * Retrieve maximum upload size
     * 
     * @return int
     */
    public function getMaximumUploadSize();

    /**
     * Set allowed upload extensions
     * 
     * @param array $extensions
     */
    public function setAllowedUploadExtensions($extensions = []);

    /**
     * Retrieve allowed apload extensions
     * 
     * @return array
     */
    public function getAllowedUploadExtensions();
}