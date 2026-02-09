<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'graphql'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Adjust to your front-end URL
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
