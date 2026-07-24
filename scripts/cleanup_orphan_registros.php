<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RegistroAsistencia;

echo "Buscando registros de asistencia sin empleado asociado...\n";
$orphan = RegistroAsistencia::query()
    ->whereDoesntHave('empleado')
    ->get();

if ($orphan->isEmpty()) {
    echo "No se encontraron registros huérfanos.\n";
    exit(0);
}

foreach ($orphan as $r) {
    echo sprintf("ID: %d | empleado_id: %s | fecha: %s | entrada: %s | salida: %s\n", $r->id, (string)$r->empleado_id, $r->fecha?->toDateString(), $r->hora_entrada, $r->hora_salida);
}

echo "\nEliminando " . $orphan->count() . " registros huérfanos (force delete)...\n";
$deleted = 0;
foreach ($orphan as $r) {
    $r->forceDelete();
    $deleted++;
}

echo "Eliminados: $deleted\n";
