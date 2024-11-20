<?php

return [
    /*
    |----------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |----------------------------------------------------------------------
    */

    'paths' => ['*', 'api/*', 'sanctum/csrf-cookie', 'http://127.0.0.1:4200', 'http://localhost:4200/*'], 

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], // Make sure OPTIONS is included

    'allowed_origins' => ['*'], // '*' allows all origins, but you can restrict this to your front-end URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Make sure to allow all headers or specific headers if necessary

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
