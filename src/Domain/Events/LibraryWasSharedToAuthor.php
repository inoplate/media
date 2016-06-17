<?php namespace Inoplate\Media\Domain\Events;

use Inoplate\Foundation\Domain\Event;
use Inoplate\Media\Domain\Models\Library;
use Inoplate\Media\Domain\Models\Author;

class LibraryWasSharedToAuthor extends Event
{
    /**
     * @var Inoplate\Media\Domain\Models\Library
     */
    protected $library;

    /**
     * @var Inoplate\Account\Domain\Models\Author
     */
    protected $author;

    /**
     * Create new LibraryWasSharedToAuthor instance
     * 
     * @param Library   $library
     * @param Author    $author
     */
    public function __construct(Library $library, Author $author)
    {
        $this->library = $library;
        $this->author = $author;
    }
}