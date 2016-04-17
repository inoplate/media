<?php namespace Inoplate\Media\Domain\Events;

use Inoplate\Foundation\Domain\Event;
use Inoplate\Media\Domain\Models\Library;
use Inoplate\Account\Domain\Models\User;

class LibraryWasSharedToUser
{
    /**
     * @var Inoplate\Media\Domain\Models\Library
     */
    protected $library;

    /**
     * @var Inoplate\Account\Domain\Models\User
     */
    protected $user;

    /**
     * Create new LibraryWasSharedToUser instance
     * 
     * @param Library $library
     * @param User    $user
     */
    public function __construct(Library $library, User $user)
    {
        $this->library = $library;
        $this->user = $user
    }
}