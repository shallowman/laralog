<?php

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
            ]
        ]
    ]
];
