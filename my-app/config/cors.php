<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://jeanariellesantos-dev.github.io',
        'https://savisystem.vercel.app'
    ],

    'allowed_origins_patterns' => ['/^http:\/\/localhost:\d+$/'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
