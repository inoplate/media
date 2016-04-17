<?php

namespace Inoplate\Media\Domain\Models;

use Inoplate\Foundation\Domain\Models\ValueObject;

class LibraryId extends ValueObject
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Create new LibraryId instance
     * 
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}