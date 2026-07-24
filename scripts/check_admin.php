<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('email', 'admin')
    ->orWhere('email', 'admin@recursoshumanos.local')
    ->get();

foreach ($users as $user) {
    echo $user->email . '|' . $user->name . "\n";
}

echo 'count:' . $users->count() . "\n";
