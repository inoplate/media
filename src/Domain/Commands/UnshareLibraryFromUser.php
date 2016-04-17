<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class UnshareLibraryFromUser extends Command
{
    /**
     * @var string
     */
    protected $libraryId;

    /**
     * @var string
     */
    protected $userId;

    /**
     * Create new UnshareLibraryFromUser instance
     * 
     * @param string $libraryId
     * @param string $userId
     */
    public function __construct($libraryId, $userId)
    {
        $this->libraryId = $libraryId;
        $this->userId = $userId;
    }
}