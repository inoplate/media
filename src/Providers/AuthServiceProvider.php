<?php

namespace Inoplate\Media\Providers;

use Inoplate\Account\Services\Permission\Collector as PermissionCollector;
use Inoplate\Foundation\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @inherit_docs
     */
    protected $moduleName = 'inoplate-media::labels.library.title';
    
    /**
     * Register permisions
     * 
     * @return array
     */
    protected function registeredPermissions()
    {
        return require __DIR__.'/../../database/collections/permissions.php';
    }
}