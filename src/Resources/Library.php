<?php

namespace Inoplate\Media\Resources;

use Roseffendi\Authis\Resource;
use Roseffendi\Authis\User;

class Library implements Resource
{
    protected $ownerId;

    public function __construct($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    public function isBelongsTo(User $user)
    {
        return $user->id() === $this->ownerId;
    }
}