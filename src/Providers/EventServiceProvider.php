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
            'Inoplate\Media\Listeners\Library\ResizeImage',
        ],
    ];
}