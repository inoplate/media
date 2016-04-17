<?php namespace Inoplate\Media\Domain\Events;

use Inoplate\Foundation\Domain\Event;
use Inoplate\Media\Domain\Models\Library;
use Inoplate\Account\Domain\Models\User;

class LibraryWasUnsharedFromUser
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
     * Create new LibraryWasUnsharedFromUser instance
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