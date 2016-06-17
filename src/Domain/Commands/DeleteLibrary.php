<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class DeleteLibrary extends Command
{
    /**
     * @var array
     */
    protected $libraryId;

    /**
     * Create new DeleteLibrary instance
     * 
     * @param string $libraryId
     */
    public function __construct($libraryId)
    {
        $this->libraryId = $libraryId;
    }
}