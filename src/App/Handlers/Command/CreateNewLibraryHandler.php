<?php namespace Inoplate\Media\App\Handlers\Command;

use Inoplate\Media\Domain\Commands\CreateNewLibrary;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Repositories\Author as AuthorRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;
use Inoplate\Media\Domain\Events\LibraryWasCreated;

class CreateNewLibraryHandler
{
    protected $libraryRepository;

    protected $authorRepository;

    protected $events;

    public function __construct(
        LibraryRepository $libraryRepository, 
        AuthorRepository $authorRepository,
        Events $events
    ) {
        $this->libraryRepository = $libraryRepository;
        $this->authorRepository = $authorRepository;
        $this->events = $events;
    }

    public function handle(CreateNewLibrary $command)
    {
        $id = $this->libraryRepository->nextIdentity();
        $author = $this->authorRepository->findById( new MediaDomainModels\AuthorId($command->authorId));
        $description = new FoundationDomainModels\Description($command->description);

        $library = new MediaDomainModels\Library($id, $author, [], $description);

        $this->libraryRepository->save($library);

        $this->events->fire([ new LibraryWasCreated($library) ]);
    }
}