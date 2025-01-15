<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Restrict to relevant paths
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], // Define methods explicitly
    'allowed_origins' => ['https://event.utdsoftware.com'], // Specify frontend origin
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'], // Limit headers
    'exposed_headers' => [],
    'max_age' => 3600, // Cache the preflight response
    'supports_credentials' => true, // Allow credentials
];
