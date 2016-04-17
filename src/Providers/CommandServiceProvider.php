<?php

namespace Inoplate\Media\Providers;

use Inoplate\Foundation\Providers\CommandServiceProvider as ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Commands to register
     * 
     * @var array
     */
    protected $commands = [
        'Inoplate\Media\Domain\Commands\CreateNewLibrary' => 
            'Inoplate\Media\App\Handlers\Command\CreateNewLibraryHandler@handle',
    ];
}