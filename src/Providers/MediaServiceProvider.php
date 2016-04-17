<?php

namespace Inoplate\Media\Providers;

use Inoplate\Media\Services\Uploader\Flow\Receiver;
use Inoplate\Foundation\Providers\AppServiceProvider as ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{   
    /**
     * @var array
     */
    protected $providers = [
        'Inoplate\Media\Providers\AuthServiceProvider',
        'Inoplate\Media\Providers\RouteServiceProvider',
        'Inoplate\Media\Providers\CommandServiceProvider',
    ];

    /**
     * Boot package
     * 
     * @return void
     */
    public function boot()
    {
        $this->loadPublic();
        $this->loadView();
        $this->loadTranslation();
        $this->loadConfiguration();
        $this->loadMigration();

        $this->app['navigation']->register(require __DIR__ .'/../Http/navigations.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->bind('Inoplate\Media\Services\Uploader\Receiver', function($app){
            $config = $app['config']['receiver'] ?: [];

            return new Receiver($app['filesystem'], $app['request'], $config);
        });

        $this->app->bind('Inoplate\Media\Domain\Repositories\Library', 
            'Inoplate\Media\Infrastructure\Repositories\EloquentLibrary');
    }

    /**
     * Publish public assets
     * @return void
     */
    protected function loadPublic()
    {
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/inoplate-media'),
        ], 'public');
    }

    /**
     * Load package's views
     * 
     * @return void
     */
    protected function loadView()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'inoplate-media');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/inoplate-media'),
        ], 'views');
    }

    /**
     * Load packages's translation
     * 
     * @return void
     */
    protected function loadTranslation()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'inoplate-media');
    }

    /**
     * Load packages migration
     * 
     * @return void
     */
    protected function loadMigration()
    {
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Load package configuration
     * 
     * @return void
     */
    protected function loadConfiguration()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/media.php', 'inoplate.media'
        );

        $this->publishes([
            __DIR__.'/../../config/media.php' => config_path('inoplate/media.php'),
        ], 'config');
    }
}