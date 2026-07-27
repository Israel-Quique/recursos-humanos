<?php

use Illuminate\Support\Str;

$devices = json_decode((string) env('BIOMETRICO_DEVICES', '[]'), true);
$deviceList = array_filter(array_map('trim', explode(';', (string) env('BIOMETRICO_DEVICE_LIST', ''))));
$fallbackDevices = array_filter(array_map(function (string $device) {
    $parts = array_map('trim', explode('|', $device));
    $ip = $parts[2] ?? '';

    if ($ip === '') {
        return null;
    }

    return [
        'department' => $parts[0] ?? 'Sin departamento',
        'branch' => $parts[1] ?? 'Sin sucursal',
        'ip' => $ip,
        'port' => isset($parts[3]) && Str::of($parts[3])->trim()->isNotEmpty()
            ? (int) $parts[3]
            : (int) env('BIOMETRICO_PORT', 4370),
    ];
}, $deviceList));

return [
    'driver' => env('BIOMETRICO_DRIVER', 'archivo'),
    'host' => env('BIOMETRICO_HOST'),
    'port' => env('BIOMETRICO_PORT'),
    'username' => env('BIOMETRICO_USERNAME'),
    'password' => env('BIOMETRICO_PASSWORD'),
    'database' => env('BIOMETRICO_DATABASE'),
    'table' => env('BIOMETRICO_TABLE'),
    'pull_interval' => (int) env('BIOMETRICO_PULL_INTERVAL', 60),
    'timeout' => (float) env('BIOMETRICO_TIMEOUT', 2),
    'devices' => is_array($devices) ? $devices : $fallbackDevices,
];
