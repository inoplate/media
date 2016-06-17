<?php

namespace Inoplate\Media\Services\Renderer;

interface Renderer
{
    /**
     * Set headers
     * 
     * @param string|array $key
     * @param string       $value
     * @return self
     */
    public function setHeaders($key, $value = null);

    /**
     * Retrieve header
     * 
     * @param  key $key
     * @return array|string|null
     */
    public function getHeaders($key = null);

    /**
     * Render file
     * 
     * @param  mixed $content
     * @return Reponse
     */
    public function render($content);
}