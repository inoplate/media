<?php

namespace Inoplate\Media\Domain\Models;

use Inoplate\Foundation\Domain\Models as FoundationModels;

class Author extends FoundationModels\Entity
{
    /**
     * @var AuthorId
     */
    protected $id;

    /**
     * @var Inoplate\Foundation\Domain\Models\Name
     */
    protected $name;

    /**
     * Create new Author instance
     * 
     * @param AuthorId              $id
     * @param FoundationModels\Name $name
     */
    public function __construct(AuthorId $id, FoundationModels\Name $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Retrieve author name
     * 
     * @return Inoplate\Foundation\Domain\Models\Name
     */
    public function name()
    {
        return $this->name;
    }
}