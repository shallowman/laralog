<?php

use Shallowman\Laralog\Http\Middleware\CaptureRequestLifecycle;

return [
    'except' => [
        // The fields filled in the blow array will be excluded to print in the log which in the http request body carried items
        // perfect match
        'fields' => [
            'password',
            'password_information',
            'password_confirm',
        ],
        // The uris filled in the blow array will be excluded to print http response body
        // Using full fuzzy matching
        'uris' => [
            '/welcome',
        ],
    ],

    'log_clipped_length' => env('LARALOG_CLIPPED_LENGTH', CaptureRequestLifecycle::POSITIVE_INFINITY),
];
