<?php

namespace Inoplate\Media\Domain\Repositories;

use Inoplate\Media\Domain\Models\Library as Model;
use Inoplate\Media\Domain\Models\LibraryId;
use Inoplate\Media\Domain\Models\AuthorId;

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
     * @param  AuthorId $authorId
     * @return array
     */
    public function findByOwner(AuthorId $authorId);

    /**
     * Retreive library shared to author
     * 
     * @param  AuthorId $authorId
     * @return array
     */
    public function sharedToAuthor(AuthorId $authorId);

    /**
     * Save library
     * 
     * @param  Library $entity
     * @return void
     */
    public function save(Model $entity);

    /**
     * Remove library
     * 
     * @param  Library $entity
     * @return void
     */
    public function remove(Model $entity);
}