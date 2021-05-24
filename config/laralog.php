<?php

return [
    'except' => [
        'fields' => [
            'password',
            'password_information',
            'password_confirm',
        ],
        // uri to avoid middleware capture the http lifecycle information
        'uri' => [

        ],
    ],
];
