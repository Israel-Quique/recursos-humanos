<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Empleado;

echo "Generando dataset de fotos en base64 para el Seeder..." . PHP_EOL;

$empleados = Empleado::query()
    ->whereNotNull('foto')
    ->where('foto', '!=', '')
    ->orderBy('id')
    ->get();

echo "Total empleados con foto: " . $empleados->count() . PHP_EOL;

$dataset = [];
$missingFiles = 0;

foreach ($empleados as $emp) {
    $fullPath = storage_path('app/public/' . ltrim($emp->foto, '/'));

    if (!file_exists($fullPath)) {
        echo " [ALERTA] Archivo no existe para ID {$emp->id}: {$fullPath}" . PHP_EOL;
        $missingFiles++;
        continue;
    }

    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) ?: 'jpg';
    $mime = match ($extension) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };

    $rawContent = file_get_contents($fullPath);
    $rawBase64 = base64_encode($rawContent);
    $dataUri = "data:{$mime};base64,{$rawBase64}";

    $dataset[] = [
        'empleado_id' => $emp->id,
        'codigo_biometrico' => $emp->codigo_biometrico,
        'nombre' => $emp->nombre,
        'apellido' => $emp->apellido,
        'sucursal' => $emp->sucursal,
        'area' => $emp->area,
        'filename' => basename($fullPath),
        'mime' => $mime,
        'extension' => $extension,
        'relative_path' => 'fotos/empleados/' . basename($fullPath),
        'base64_data_uri' => $dataUri,
        'base64' => $rawBase64,
    ];
}

$outputPath = database_path('data/empleados_fotos.json');
if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0755, true);
}

file_put_contents($outputPath, json_encode($dataset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

$fileSizeMB = round(filesize($outputPath) / 1024 / 1024, 2);

echo "Dataset generado con éxito en: {$outputPath}" . PHP_EOL;
echo "Total de fotos empaquetadas en base64: " . count($dataset) . PHP_EOL;
echo "Tamaño del archivo JSON: {$fileSizeMB} MB" . PHP_EOL;
