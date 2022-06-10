<?php

namespace Shallowman\Laralog;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\LogManager;

class LaraLogger extends LogManager
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
}
