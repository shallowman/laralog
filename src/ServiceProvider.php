<?php


namespace Shallowman\Laralog;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        $this->publishes([dirname(__DIR__).'/config/laralog.php' => config_path('laralog.php')]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laralog.php', 'laralog');
    }
}