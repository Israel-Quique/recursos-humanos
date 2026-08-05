<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Cache default: " . config('cache.default') . PHP_EOL;
echo "Cache stores: " . implode(', ', array_keys(config('cache.stores'))). PHP_EOL;
$key = 'biometrico:auto-sync:last-trigger-at';
$value = Cache::get($key);
if ($value) {
    echo "$key => ".($value instanceof DateTimeInterface ? $value->format('c') : print_r($value, true)).PHP_EOL;
} else {
    echo "$key => (null)".PHP_EOL;
}

$lockKey = 'biometrico:auto-sync:trigger-lock';
$lock = Cache::get($lockKey);
echo "$lockKey => ".var_export($lock, true).PHP_EOL;
