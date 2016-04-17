<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class ShareLibraryToUser extends Command
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
     * Create new ShareLibraryToUser instance
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