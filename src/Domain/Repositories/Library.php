<?php

namespace Inoplate\Media\Domain\Repositories;

use Inoplate\Account\Domain\Models\UserId;
use Inoplate\Media\Domain\Models\LibraryId;

interface Library
{
    /**
     * Retrieve library by id
     * 
     * @param  LibraryId $libraryId
     * @return Library
     */
    public function findById(LibraryId $libraryId);

    /**
     * Retrieve library by path
     * 
     * @param  string $path
     * @return Library
     */
    public function findByPath($path);

    /**
     * Retrieve library by owner
     * 
     * @param  UserId $userId
     * @return array
     */
    public function findByOwner(UserId $userId);

    /**
     * Retreive library shared to user
     * 
     * @param  UserId $userId
     * @return array
     */
    public function sharedToUser(UserId $userId);
}