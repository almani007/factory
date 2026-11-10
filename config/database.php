<?php

use Illuminate\Database\Connector\PostgresConnector;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'factory'),
            'username' => env('DB_USERNAME', 'factory'),
            'password' => env('DB_PASSWORD', ''),
            'schema' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],
    ],
];
