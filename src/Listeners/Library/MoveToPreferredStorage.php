<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Foundation\Domain\Models\Description;
use Inoplate\Media\Domain\Events\LibraryWasCreated;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MoveToPreferredStorage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var FilesystemFactory
     */
    protected $filesystemFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        FilesystemFactory $filesystemFactory, 
        Config $config, 
        LibraryRepository $libraryRepository
    ) {
        $this->filesystemFactory = $filesystemFactory;
        $this->config = $config;
        $this->libraryRepository = $libraryRepository;
    }

    /**
     * @param  FileWasFailedToUpload $event [description]
     * @return void
     */
    public function handle(LibraryWasCreated $event)
    {
        $library = $event->library;
        // If the media library was deleted before it moved
        // Delete this queue
        if(!$this->libraryRepository->findById($library->id())) {
            $this->delete();
        }

        $description = $library->description()->value();
        $storage = $description['storage'];

        // We don't need to move if storage is local
        if($storage != 'local') {
            $preferredFilesystem = $this->filesystemFactory->disk($storage);
            $localFilesystem = $this->filesystemFactory->disk('local');

            $destination = $description['path'];
            $source = $destination;
            $stream = $localFilesystem->getDriver()->readStream($source);

            $preferredFilesystem->put($destination, $stream);

            // Remove local saved file
            $localFilesystem->delete($source);
        }

        // Mark as moved
        $description['is_moved'] = true;
        $library->describe( new Description($description) );
        $this->libraryRepository->save($library);
    }
}