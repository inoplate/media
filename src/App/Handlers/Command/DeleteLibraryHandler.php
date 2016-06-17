<?php namespace Inoplate\Media\App\Handlers\Command;

use Inoplate\Media\Domain\Commands\DeleteLibrary;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;
use Inoplate\Media\Domain\Events\LibraryWasDeleted;

class DeleteLibraryHandler
{
    protected $libraryRepository;

    protected $events;

    public function __construct(
        LibraryRepository $libraryRepository, 
        Events $events
    ) {
        $this->libraryRepository = $libraryRepository;
        $this->events = $events;
    }

    public function handle(DeleteLibrary $command)
    {
        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($command->libraryId));
        $this->libraryRepository->remove($library);

        $this->events->fire([ new LibraryWasDeleted($library) ]);
    }
}