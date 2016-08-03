<?php

namespace Inoplate\Media\Providers;

use Assets;
use Inoplate\Media\Services\Uploader\Flow\Receiver;
use Inoplate\Foundation\Providers\AppServiceProvider as ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{   
    /**
     * @var array
     */
    protected $providers = [
        'Inoplate\Notifier\Laravel\NotifierServiceProvider',
        'Inoplate\Media\Providers\AuthServiceProvider',
        'Inoplate\Media\Providers\RouteServiceProvider',
        'Inoplate\Media\Providers\CommandServiceProvider',
        'Inoplate\Media\Providers\EventServiceProvider',
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
        $this->registerViewCreators();

        $this->app['navigation']->register(require __DIR__ .'/../Http/navigations.php');
        $this->app['validator']->extend('inoplate_media_validate_mime', 
            'Inoplate\Media\Validators\MimetypeValidatorFromPath@validate');
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
            $config = $this->buildReceiverConfig();

            return new Receiver($app['filesystem'], $app['request'], $config);
        });

        $this->app->bind('Inoplate\Media\Services\Renderer\Renderer', 'Inoplate\Media\Services\Renderer\StreamRenderer');
        $this->app->bind('Inoplate\Media\Services\Resizer\Resizer', 'Inoplate\Media\Services\Resizer\RuntimeResizer');
        $this->app->bind('Inoplate\Media\Domain\Repositories\Author', 'Inoplate\Media\Infrastructure\Repositories\EloquentAuthor');
        $this->app->bind('Inoplate\Media\Domain\Repositories\Library', 'Inoplate\Media\Infrastructure\Repositories\EloquentLibrary');
    }

    /**
     * Registering view creator
     * 
     * @return void
     */
    protected function registerViewCreators()
    {
        view()->creator('inoplate-media::library.*', function($view){
            Assets::add('vendor/inoplate-media/media.js');
        });
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

    /**
     * Build receiver config
     * 
     * @return
     */
    protected function buildReceiverConfig()
    {
        $return = [];

        if($this->app['config']['inoplate.media.library.size.max'])
            $return['maximum_upload_size'] = size_to_bytes($this->app['config']['inoplate.media.library.size.max'].'m');

        if($this->app['config']['inoplate.media.library.extensions'])
            $return['allowed_extension'] = $this->app['config']['inoplate.media.library.extensions'];

        if($this->app['config']['inoplate.media.library.chunks_temp_path'])
            $return['chunks_temp_path'] = $this->app['config']['inoplate.media.library.chunks_temp_path'];

        return $return;
    }
}