<?php 

namespace Inoplate\Media\Services\Resizer;

interface Resizer
{
    /**
     * Set name
     * 
     * @param string $name
     */
    public function setName($name);

    /**
     * Retrieve name
     * 
     * @return string
     */
    public function getName();

    /**
     * Set resize mode
     * 
     * @param string $mode
     * @return self
     */
    public function setMode($mode);

    /**
     * Retrieve resize mode
     * 
     * @return string
     */
    public function getMode();

    /**
     * Set cache lifetime
     * 
     * @param int $lifetime
     * @return self
     */
    public function setLifetime($lifetime);

    /**
     * Retrieve cache lifetime
     * 
     * @return int $lifetime
     */
    public function getLifetime();

    /**
     * Set dimension
     * 
     * @param  int $height
     * @param  int $width
     * @return self
     */
    public function setDimension($width, $height);

    /**
     * Retrieve dimension
     * 
     * @return array
     */
    public function getDimension();

    /**
     * Apply resize
     * 
     * @param string $path
     * @return Psr-7 stream
     */
    public function apply($path);
}