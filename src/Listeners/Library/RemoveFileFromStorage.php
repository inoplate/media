<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Media\Domain\Events\LibraryWasDeleted;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveFileFromStorage implements ShouldQueue
{
    /**
     * @var FilesystemFactory
     */
    protected $filesystemFactory;

    /**
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @param FilesystemFactory $filesystemFactory
     * @param LibraryRepository $libraryRepository
     */
    public function __construct(FilesystemFactory $filesystemFactory, LibraryRepository $libraryRepository)
    {
        $this->filesystemFactory = $filesystemFactory;
        $this->libraryRepository = $libraryRepository;
    }

    /**
     * Handle event
     * 
     * @param  LibraryWasDeleted $event
     * @return void
     */
    public function handle(LibraryWasDeleted $event)
    {  
        $library =  $event->library;
        $description = $library->description()->value();

        $storage = $description['is_moved'] ? $description['storage'] : 'local';
        $filesystem = $this->filesystemFactory->disk($storage);

        if($filesystem->exists($description['path'])) {
            $filesystem->delete($description['path']);
        }
    }
}