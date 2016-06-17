<?php

namespace Inoplate\Media\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    
    protected $listen = [
        'Inoplate\Media\Domain\Events\LibraryWasCreated' => [
            'Inoplate\Media\Listeners\Library\MoveToPreferredStorage',
        ],
        'Inoplate\Media\Domain\Events\LibraryWasDeleted' => [
            'Inoplate\Media\Listeners\Library\RemoveFileFromStorage',
        ],
        'Inoplate\Media\Domain\Events\LibraryWasSharedToAuthor' => [
            'Inoplate\Media\Listeners\Library\NotifyAuthorMediaShared',
        ],
        'Inoplate\Media\Domain\Events\LibraryWasUnsharedFromAuthor' => [
            'Inoplate\Media\Listeners\Library\NotifyAuthorMediaUnshared',
        ],
        'Inoplate\Media\Events\FileWasFailedToUpload' => [
            'Inoplate\Media\Listeners\Library\DeleteFileWhenUploadFailed',
        ],
    ];
}