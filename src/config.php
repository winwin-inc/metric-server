<?php

declare(strict_types=1);

use function kuiper\helper\env;

return [
    'application' => [
        'database' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'name' => env('DB_NAME'),
            'user' => env('DB_USER'),
            'password' => env('DB_PASS'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'table-prefix' => env('DB_TABLE_PREFIX', ''),
            'logging' => 'true' === env('DB_LOGGING', 'true'),
        ],
    ],
];
