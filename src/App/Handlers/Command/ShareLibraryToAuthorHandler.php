<?php namespace Inoplate\Media\App\Handlers\Command;

use Inoplate\Media\Domain\Commands\ShareLibraryToAuthor;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Repositories\Author as AuthorRepository;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;

class ShareLibraryToAuthorHandler
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

    public function handle(ShareLibraryToAuthor $command)
    {
        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($command->libraryId));
        $author = $this->authorRepository->findById( new MediaDomainModels\AuthorId($command->authorId));

        $library->share($author);

        $this->libraryRepository->save($library);

        $this->events->fire($library->releaseEvents());
    }
}