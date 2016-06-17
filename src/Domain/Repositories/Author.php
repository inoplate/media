<?php

namespace Inoplate\Media\Domain\Repositories;

use Inoplate\Media\Domain\Models\AuthorId;

interface Author
{
    /**
     * Retrieve author by id
     * 
     * @param  AuthorId $authorId
     * @return Author
     */
    public function findById(AuthorId $authorId);
}