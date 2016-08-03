<?php

namespace Inoplate\Media\Infrastructure\Repositories;

use Ramsey\Uuid\Uuid;
use Inoplate\Media\MediaLibrary as Model;
use Inoplate\Media\Domain\Repositories\Library as Contract;
use Inoplate\Media\Domain\Repositories\Author as AuthorRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Roseffendi\Authis\Authis;
use Illuminate\Contracts\Auth\Guard;

class EloquentLibrary implements Contract
{
    /**
     * @var Inoplate\Media\MediaLibrary
     */
    protected $model;

    /**
     * @var Inoplate\Media\Domain\Repositories\Author
     */
    protected $authorRepository;

    /**
     * @var Roseffendi\Authis\Authis
     */
    protected $authis;

    /**
     * @var Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create new EloquentLibrary instance
     * 
     * @param Model             $model
     * @param AuthorRepository  $authorRepository
     * @param Authis            $authis
     * @param Guard             $auth
     */
    public function __construct(
        Model $model, 
        AuthorRepository $authorRepository, 
        Authis $authis,
        Guard $auth
    ) {
        $this->model = $model;
        $this->authorRepository = $authorRepository;
        $this->authis = $authis;
        $this->auth = $auth;
    }

    /**
     * Retrieve user generated identity
     * 
     * @return Inoplate\Media\Domain\Models\LibraryId
     */
    public function nextIdentity()
    {
        $id = Uuid::uuid4();

        return new MediaDomainModels\LibraryId($id->toString());
    }

    /**
     * Retrieve library by id
     * 
     * @param  LibraryId $libraryId
     * @return Library
     */
    public function findById(MediaDomainModels\LibraryId $libraryId)
    {
        $library = $this->model->find($libraryId->value());

        return $this->toDomainModel($library);
    }

    /**
     * Retrieve library by path
     * 
     * @param  string $path
     * @return Library
     */
    public function findByPath($path)
    {
        $sizes = config('inoplate.media.library.sizes');

        $path = preg_replace('/^\/uploads\//', '', $path);
        $path = preg_replace('/^\/download\//', '', $path);

        foreach ($sizes as $size => $dimension) {
            $pattern = '/\/thumb$/';
            $path = preg_replace($pattern, '', $path);
        }

        $library = $this->model->where('path', $path)->first();

        return $this->toDomainModel($library);
    }

    /**
     * Retrieve library by owner
     * 
     * @param  AuthorId $authorId
     * @return array
     */
    public function findByOwner(MediaDomainModels\AuthorId $authorId)
    {
        $eloquentLibraries = $this->model->where('user_id', $userId->value())->get();

        $libraries = [];

        foreach ($eloquentLibraries as $library) {
            $libraries[] = $this->toDomainModel($library);
        }

        return $libraries;
    }

    /**
     * Retreive library shared to user
     * 
     * @param  AuthorId $authorId
     * @return array
     */
    public function sharedToAuthor(MediaDomainModels\AuthorId $authorId)
    {
        $eloquentLibraries = $this->model->whereHas('users', function($query) use ($authorId) {
            $query->where('user_id', $authorId->value());
        })->get();

        $libraries = [];

        foreach ($eloquentLibraries as $library) {
            $libraries[] = $this->toDomainModel($library);
        }

        return $libraries;
    }

    /**
     * Count data
     * 
     * @return integer
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * Retrieve pagination
     * 
     * @param  integer $page
     * @param  integer $start
     * @param  string  $search
     * @return array
     */
    public function get($page, $search = '', $visibility = null, $ownership = null)
    {
        $this->model = $this->setupQuery($this->model);

        $userId = $this->auth->user()->id;
        $perPage = config('inoplate.media.library.per_page', 10);

        if($search) {
            $this->model = $this->model->where(function($query) use ($search){
                $query->where('title', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%");
            });
        }

        if($ownership == 1) {
            $this->model = $this->model->where('user_id', $userId);
        }elseif($ownership == 2) {
            $this->model = $this->model->where('user_id', '!=', $userId)
                                       ->whereHas('users', function($query) use ($userId){
                                            $query->where('user_id', $userId);
                                        });
        }

        if($visibility) {
            $this->model = $this->model->where('visibility', $visibility);
        }

        $eloquentLibraries = $this->model->skip(($page-1) * $perPage)->take($perPage)->orderBy('created_at', 'desc')->get();
        $libraries = [];

        foreach ($eloquentLibraries as $library) {
            $libraries[] = $this->toDomainModel($library);
        }

        return $libraries;
    }

    /**
     * Save entity updates
     * 
     * @param  Libray   $entity
     * @return void
     */
    public function save(MediaDomainModels\Library $entity)
    {
        $library = $this->model->firstOrNew([ 'id' => $entity->id()->value() ]);
        $library->id = $entity->id()->value();
        $library->user_id = $entity->owner()->id()->value();

        $description = $entity->description()->value();
        $domainAuthors = $entity->sharedTo();

        $authors = [];

        foreach ($description as $key => $value) {
            $library->{$key} = $value;
        }
        
        $library->save();
        
        foreach ($domainAuthors as $author) {
            $authors[] = $author->id()->value();
        }

        $library->users()->sync($authors);
    }

    /**
     * Remove library
     * 
     * @param  Library $entity
     * @return void
     */
    public function remove(MediaDomainModels\Library $entity)
    {
        $library = $this->model->find($entity->id()->value());
        $library->users()->detach();

        $library->delete();
    }

    /**
     * Setup query
     * 
     * @param  Model $model
     * @return Model
     */
    protected function setupQuery($model)
    {
        $userId = $this->auth->user()->id;
        if($this->authis->check('media.admin.libraries.view.all')) {
            return $model;
        }else {
            return $model->where(function($query) use ($userId){
                $query->where('user_id', $userId)
                      ->orWhere(function($query) use ($userId){
                        $query->whereHas('users', function($query) use ($userId){
                            $query->where('user_id', $userId);
                        });
                      })
                      ->orWhere('visibility', 'public');
            });
        }
    }

    /**
     * Convert eloquent to domain model
     * 
     * @param  Model    $library
     * @return MediaDomainModels\Model 
     */
    protected function toDomainModel($library)
    {
        if( is_null($library) ) {
            return $library;
        }else {
            $id = new MediaDomainModels\LibraryId($library->id);
            $owner = $this->authorRepository->findById( new MediaDomainModels\AuthorId($library->user_id));
            $description = new FoundationDomainModels\Description(array_except( $library->toArray(), ['id', 'user', 'users'] ));

            $plainUsers = $library->users;
            $sharedTo = [];

            foreach ($plainUsers as $user) {
                $sharedTo[] = $this->authorRepository->findById( new MediaDomainModels\AuthorId($user->id));
            }

            return new MediaDomainModels\Library($id, $owner, $sharedTo, $description);
        }
    }
}