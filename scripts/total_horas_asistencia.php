<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RegistroAsistencia;
use Carbon\Carbon;

$totalMinutos = 0;
$totalRegistros = 0;
$errores = 0;

$registros = RegistroAsistencia::whereNotNull('hora_entrada')
    ->whereNotNull('hora_salida')
    ->get();

foreach ($registros as $registro) {
    try {
        $entrada = Carbon::parse($registro->hora_entrada);
        $salida = Carbon::parse($registro->hora_salida);

        if ($salida->greaterThan($entrada)) {
            $duracionMinutos = (int) floor($entrada->diffInSeconds($salida) / 60);
            $totalMinutos += $duracionMinutos;
            $totalRegistros++;
        }
    } catch (\Exception $e) {
        $errores++;
    }
}

$horas = intdiv($totalMinutos, 60);
$minutos = $totalMinutos % 60;

echo "Total registros procesados: {$totalRegistros}\n";
echo "Total minutos trabajados: {$totalMinutos}\n";
echo "Total horas trabajadas: " . sprintf('%02d:%02d', $horas, $minutos) . "\n";
if ($errores > 0) {
    echo "Registros con error de parseo: {$errores}\n";
}
