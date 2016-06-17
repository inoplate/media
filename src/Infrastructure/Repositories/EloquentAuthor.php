<?php

namespace Inoplate\Media\Infrastructure\Repositories;

use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Media\Domain\Repositories\Author as Contract;
use Inoplate\Account\User as Model;

class EloquentAuthor implements Contract
{
    /**
     * @var Inoplate\Media\MediaLibrary
     */
    protected $model;

    /**
     * Create new EloquentAuthor instance
     * 
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve author by id
     * 
     * @param  AuthorId $authorId
     * @return Author
     */
    public function findById(MediaDomainModels\AuthorId $authorId)
    {
        $author = $this->model->find($authorId->value());

        return $this->toDomainModel($author);
    }

    /**
     * Convert eloquent to domain model
     * 
     * @param  Model    $author
     * @return MediaDomainModels\Author
     */
    protected function toDomainModel($author)
    {
        if( is_null($author) ) {
            return $author;
        } else {
            $id = new MediaDomainModels\AuthorId($author->id);
            $name = new FoundationDomainModels\Name($author->name);

            return new MediaDomainModels\Author($id, $name);
        }
    }
}