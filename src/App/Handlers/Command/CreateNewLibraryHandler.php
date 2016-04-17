<?php namespace Inoplate\Media\App\Handlers\Command;

use Inoplate\Media\Domain\Commands\CreateNewLibrary;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Account\Domain\Repositories\User as UserRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Account\Domain\Models as AccountDomainModels;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;
use Inoplate\Media\Domain\Events\LibraryWasCreated;

class CreateNewLibraryHandler
{
    protected $libraryRepository;

    protected $userRepository;

    protected $events;

    public function __construct(
        LibraryRepository $libraryRepository, 
        UserRepository $userRepository,
        Events $events
    ) {
        $this->libraryRepository = $libraryRepository;
        $this->userRepository = $userRepository;
        $this->events = $events;
    }

    public function handle(CreateNewLibrary $command)
    {
        $id = $this->libraryRepository->nextIdentity();
        $user = $this->userRepository->findById( new AccountDomainModels\UserId($command->userId));
        $description = new FoundationDomainModels\Description($command->description);

        $library = new MediaDomainModels\Library($id, $user, [], $description);

        $this->libraryRepository->save($library);

        $this->events->fire([ new LibraryWasCreated($library) ]);
    }
}