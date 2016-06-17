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
        'Inoplate\Media\Domain\Commands\ShareLibraryToAuthor' => 
            'Inoplate\Media\App\Handlers\Command\ShareLibraryToAuthorHandler@handle',
        'Inoplate\Media\Domain\Commands\UnshareLibraryFromAuthor' => 
            'Inoplate\Media\App\Handlers\Command\UnshareLibraryFromAuthorHandler@handle',
        'Inoplate\Media\Domain\Commands\DescribeLibrary' => 
            'Inoplate\Media\App\Handlers\Command\DescribeLibraryHandler@handle',
        'Inoplate\Media\Domain\Commands\DeleteLibrary' => 
            'Inoplate\Media\App\Handlers\Command\DeleteLibraryHandler@handle',
    ];
}