<?php

return [
    // default log channel
    'default'  => env('LARALOG_CHANNEL', 'laralog'),

    //Custom defined channels
    'channels' => [
        'laralog' => [
            'driver' => 'daily',
            'path'   => env('', 'logs/laralog-app.log'),
            'tap'    => [Shallowman\Laralog\Formatter\LaraLogFormatter::class],
            'days'   => 7,
        ],
    ],
];