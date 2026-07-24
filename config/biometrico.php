<?php

return [
    'driver' => env('BIOMETRICO_DRIVER', 'archivo'),
    'host' => env('BIOMETRICO_HOST'),
    'port' => env('BIOMETRICO_PORT'),
    'username' => env('BIOMETRICO_USERNAME'),
    'password' => env('BIOMETRICO_PASSWORD'),
    'database' => env('BIOMETRICO_DATABASE'),
    'table' => env('BIOMETRICO_TABLE'),
    'pull_interval' => (int) env('BIOMETRICO_PULL_INTERVAL', 60),
];
