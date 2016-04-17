<?php

namespace Inoplate\Media\Providers;

use Authis;
use Inoplate\Account\Services\Permission\Collector as PermissionCollector;
use Inoplate\Foundation\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register permisions
     * 
     * @return array
     */
    protected function registerPermissions()
    {
        return require __DIR__.'/../../database/collections/permissions.php';
    }

    /**
     * Register permissions aliases
     * 
     * @return array
     */
    protected function registerPermissionsAliases()
    {
        return require __DIR__.'/../../database/collections/permissions_aliases.php';
    }

    /**
     * Register permissions overrides interceptors
     * 
     * @return array
     */
    protected function registerPermissionsOverrideInterceptors()
    {
        return require __DIR__.'/../../database/collections/permissions_override_interceptors.php';   
    }
}