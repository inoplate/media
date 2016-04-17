<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class CreateNewLibrary extends Command
{
    /**
     * @var string
     */
    protected $userId;

    /**
     * @var array
     */
    protected $description;

    /**
     * Create new CreateNewLibrary instance
     * 
     * @param string $userId
     * @param array  $description
     */
    public function __construct($userId, $description = [])
    {
        $this->userId = $userId;
        $this->description = $description;
    }
}