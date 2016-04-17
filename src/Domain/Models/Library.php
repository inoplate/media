<?php

namespace Inoplate\Media\Domain\Models;

use Inoplate\Foundation\Domain\Models as FoundationModels;
use Inoplate\Foundation\Domain\Contracts\Describeable;
use Inoplate\Media\Domain\Events;
use Inoplate\Account\Domain\Models\User;

class Library extends FoundationModels\Entity implements Describeable
{
    use FoundationModels\Describeable;

    /**
     * @var array
     */
    protected $sharedTo;

    /**
     * @var User
     */
    protected $owner;

    /**
     * Create new Role instance
     * 
     * @param LibraryId                         $id
     * @param User                              $owner
     * @param array                             $sharedTo
     * @param FoundationModels\Description      $description
     */
    public function __construct(
        LibraryId $id,
        User $owner,
        $sharedTo = [],
        FoundationModels\Description $description
    ) {
        $this->id = $id;
        $this->owner = $owner;
        $this->sharedTo = $sharedTo;
        $this->description = $description;
    }

    /**
     * Retrieve library owner
     * 
     * @return User
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Retrieve to who library shared to
     * 
     * @return array
     */
    public function sharedTo()
    {
        return $this->sharedTo;
    }

    /**
     * Share library to user
     * 
     * @param  User   $user
     * @return void
     */
    public function share(User $user)
    {
        if(!in_array($user, $this->sharedTo)) {
            $this->sharedTo[] = $user;
        }

        $this->recordEvent(new Events\LibraryWasSharedToUser($this, $user));
    }

    /**
     * Unshare library form user
     * 
     * @param  User   $user
     * @return void
     */
    public function unshare(User $user)
    {
        $this->sharedTo = array_values(array_filter($this->sharedTo, function($search) use ($user){
            return !$search->equal($user);
        }));

        $this->recordEvent(new Events\LibraryWasUnsharedFromUser($this, $user));
    }
}