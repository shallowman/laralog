<?php

use Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle;

return [
    'capture' => [
        'except' => [
            'http_req_fields' => [
                'password',
                'password_information',
                'password_confirm',
            ],
            // the uri to avoid middleware capture
            'uri' => [
                '/welcome',
            ],
        ],
    ],

    'log_clipped_length' => env('LARALOG_CLIPPED_LENGTH', CaptureRequestLifecycle::POSITIVE_INFINITY),
];
