<?php


namespace Shallowman\Laralog;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\LogManager as LaravelLogManager;

class LogManager extends LaravelLogManager
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
}