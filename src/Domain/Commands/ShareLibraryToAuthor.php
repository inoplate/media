<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class ShareLibraryToAuthor extends Command
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
     * Create new ShareLibraryToAuthor instance
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