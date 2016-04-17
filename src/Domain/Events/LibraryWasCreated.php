<?php namespace Inoplate\Media\Domain\Events;

use Inoplate\Foundation\Domain\Event;
use Inoplate\Media\Domain\Models\Library;

class LibraryWasCreated extends Event
{
    /**
     * @var Inoplate\Media\Domain\Models\Library
     */
    protected $library;

    /**
     * Create new LibraryWasCreated instance
     * 
     * @param Library $library
     */
    public function __construct(Library $library)
    {
        $this->library = $library;
    }
}