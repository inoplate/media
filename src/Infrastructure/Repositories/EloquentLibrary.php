<?php

namespace Inoplate\Media\Infrastructure\Repositories;

use Ramsey\Uuid\Uuid;
use Inoplate\Media\MediaLibrary as Model;
use Inoplate\Media\Domain\Repositories\Library as Contract;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Account\Domain\Repositories\User as UserRepository;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Account\Domain\Models as AccountDomainModels;
use Roseffendi\Authis\Authis;
use Illuminate\Contracts\Auth\Guard;

class EloquentLibrary implements Contract
{
    /**
     * @var Inoplate\Media\MediaLibrary
     */
    protected $model;

    /**
     * @var Inoplate\Account\Domain\Repositories\User
     */
    protected $userRepository;

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
     * @param Model          $model
     * @param UserRepository $userRepository
     * @param Authis         $authis
     * @param Guard          $auth
     */
    public function __construct(
        Model $model, 
        UserRepository $userRepository, 
        Authis $authis,
        Guard $auth
    ) {
        $this->model = $model;
        $this->userRepository = $userRepository;
        $this->authis = $authis;
        $this->auth = $auth;
    }

    /**
     * Retrieve user generated identity
     * 
     * @return Inoplate\Account\Domain\Models\UserId
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
        $library = $this->model->find($id->value());

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
        $library = $this->model->where('path', $path)->first();

        return $this->toDomainModel($library);
    }

    /**
     * Retrieve library by owner
     * 
     * @param  UserId $userId
     * @return array
     */
    public function findByOwner(AccountDomainModels\UserId $userId)
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
     * @param  UserId $userId
     * @return array
     */
    public function sharedToUser(AccountDomainModels\UserId $userId)
    {
        $eloquentLibraries = $this->model->whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId->value());
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
    public function get($page, $search = '')
    {
        $this->model = $this->setupQuery($this->model);

        $perPage = config('inoplate.media.library.per_page', 10);

        if($search) {
            $this->model = $this->model->where(function($query) use ($search){
                $query->where('title', 'like', "%search%")
                      ->orWhere('name', 'like', "%search%");
            });
        }

        $eloquentLibraries = $this->model->skip(($page-1) * $perPage)->take($perPage)->get();

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
        $domainUsers = $entity->sharedTo();

        $users = [];

        foreach ($description as $key => $value) {
            $library->{$key} = $value;
        }
        
        $library->save();
        
        foreach ($domainUsers as $user) {
            $users[] = $user->id()->value();
        }

        $library->users()->sync($users);
    }

    /**
     * Setup query
     * 
     * @param  Model $model
     * @return Model
     */
    protected function setupQuery($model)
    {
        if($this->authis->check('media.admin.library.view.all')) {
            return $model;
        }else {
            return $model->where(function($query) {
                $query->where('user_id', $this->auth->user()->id)
                      ->orWhere(function($query){
                        $query->whereHas('users', function($query){
                            $query->where('user_id', $this->auth->user()->id);
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
            $owner = $this->userRepository->findById( new AccountDomainModels\UserId($library->user_id));
            $description = new FoundationDomainModels\Description(array_except( $library->toArray(), ['id', 'user', 'users'] ));

            $plainUsers = $library->users;
            $sharedTo = [];

            foreach ($plainUsers as $user) {
                $sharedTo[] = $this->userRepository->findById( new AccountDomainModels\UserId($user->id));
            }

            return new MediaDomainModels\Library($id, $owner, $sharedTo, $description);
        }
    }
}