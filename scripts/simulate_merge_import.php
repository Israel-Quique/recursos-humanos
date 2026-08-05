<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ImportacionBiometricaService;
use App\Models\Empleado;
use App\Models\Importacion;
use App\Models\RegistroAsistencia;

// Crear empleado de prueba (si no existe)
$empleado = Empleado::query()->firstOrCreate(
    ['codigo_biometrico' => '10909669'],
    ['nombre' => 'hijodecotel', 'apellido' => 'Sin apellido', 'sucursal' => 'LaPaz', 'fecha_contratacion' => now()->toDateString(), 'created_by' => 1]
);

// Crear importacion base
$importacion = Importacion::query()->create([
    'nombre_archivo' => 'simulado.xlsx',
    'ruta_archivo' => 'simulado',
    'fecha_operativa' => now()->toDateString(),
    'registros_total' => 0,
    'empleados_detectados' => 0,
    'estado' => 'procesando',
    'created_by' => 1,
]);

// Insertar un registro existente (entrada tardia)
$registro = RegistroAsistencia::query()->updateOrCreate([
    'empleado_id' => $empleado->id,
    'fecha' => now()->toDateString(),
], [
    'hora_entrada' => '08:34:00',
    'hora_salida' => '08:34:00',
    'created_by' => 1,
]);

echo "Antes: entrada={$registro->hora_entrada} salida={$registro->hora_salida}\n";

$service = new ImportacionBiometricaService();

// Simular nuevas marcas importadas con mejor entrada y salida
$rows = [
    ['codigo' => '10909669', 'fecha_hora' => '2026-07-28 08:30:00', 'estado' => 'entrada', 'Verificacion' => 'Huella'],
    ['codigo' => '10909669', 'fecha_hora' => '2026-07-28 17:38:00', 'estado' => 'salida', 'Verificacion' => 'Huella'],
];

$device = ['ip' => 'sync://simulado', 'branch' => 'LaPaz', 'department' => 'Oficina Cent'];

$import = $service->importarMarcacionesBiometrico($device, $rows);

$registroFresh = RegistroAsistencia::query()->where('empleado_id', $empleado->id)->whereDate('fecha', now()->toDateString())->first();

echo "Despues: entrada={$registroFresh->hora_entrada} salida={$registroFresh->hora_salida}\n";
