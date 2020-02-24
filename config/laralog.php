<?php

return [
    // default log channel
    'default'  => env('LARALOG_CHANNEL', 'laralog'),

    //Custom defined channels
    'channels' => [
        'laralog' => [
            'driver' => 'daily',
            'path'   => env('', '/application/storage/logs/app.log'),
            'tap'    => [Shallowman\Laralog\LaraLogFormatter::class],
            'days'   => 7,
        ],
    ],
];