<?php


namespace Shallowman\Laralog;

use Illuminate\Log\LogManager as LaravelLogManager;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $path = $this->resolveConfigPath();
        $this->publishes([$path => config_path('laralog.php')], 'config');
        $this->mergeConfigFrom($path, 'laralog');
    }

    public function resolveConfigPath()
    {
        return realpath(__DIR__.'/../config/laralog.php');
    }

    public function register()
    {
        $this->app->extend(LaravelLogManager::class, function() {
            return new LogManager($this->app);
        });
    }
}