<?php namespace Inoplate\Media\App\Handlers\Command;

use Inoplate\Media\Domain\Commands\DescribeLibrary;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Repositories\Author as AuthorRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;
use Inoplate\Media\Domain\Events\LibraryWasRedescribed;

class DescribeLibraryHandler
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

    public function handle(DescribeLibrary $command)
    {
        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($command->libraryId));
        $description = new FoundationDomainModels\Description($command->description);

        $library->describe($description);
        $this->libraryRepository->save($library);

        $this->events->fire([ new LibraryWasRedescribed($library) ]);
    }
}