<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'graphql'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8000', 'http://localhost:5173'], // Adjust to your front-end URL
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
