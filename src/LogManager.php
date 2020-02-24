<?php


namespace Shallowman\Laralog;

use Illuminate\Log\LogManager as LaravelLogManager;
use Shallowman\Laralog\Formatter\LaraLogFormatter;

class LogManager extends LaravelLogManager
{
    public function formatter()
    {
        return tap(new LaraLogFormatter());
    }
}