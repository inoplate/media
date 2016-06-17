<?php 

namespace Inoplate\Media\Services\Resizer;

abstract class BaseResizer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $lifetime = 150; // it's 5 minutes

    /**
     * Set name
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retrieve name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set resize mode
     * 
     * @param string $mode
     * @return self
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Retrieve resize mode
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set cache lifetime
     * 
     * @param int $lifetime
     * @return self
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Retrieve cache lifetime
     * 
     * @return int $lifetime
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set dimension
     * 
     * @param  int $height
     * @param  int $width
     * @return self
     */
    public function setDimension($width, $height)
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Retrieve dimension
     * 
     * @return array
     */
    public function getDimension()
    {
        return ['width' => $this->height, 'height' => $this->height];
    }
}