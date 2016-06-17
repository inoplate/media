<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class UnshareLibraryFromAuthor extends Command
{
    /**
     * @var string
     */
    protected $libraryId;

    /**
     * @var string
     */
    protected $authorId;

    /**
     * Create new UnshareLibraryFromAuthor instance
     * 
     * @param string $libraryId
     * @param string $authorId
     */
    public function __construct($libraryId, $authorId)
    {
        $this->libraryId = $libraryId;
        $this->authorId = $authorId;
    }
}