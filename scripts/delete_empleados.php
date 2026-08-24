<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$empleados = [
    ['nombre' => 'Bruno', 'apellido' => 'Mamani'],
    ['nombre' => 'Carla', 'apellido' => 'Rojas'],
    ['nombre' => 'Diego', 'apellido' => 'Vargas'],
    ['nombre' => 'Elena', 'apellido' => 'Suarez'],
    ['nombre' => 'Ana', 'apellido' => 'Lopez'],
    ['nombre' => 'Gloria', 'apellido' => 'Perez'],
    ['nombre' => 'Hugo', 'apellido' => 'Ribera'],
    ['nombre' => 'Irene', 'apellido' => 'Justiniano'],
    ['nombre' => 'Fabian', 'apellido' => 'Arias'],
];

use App\Models\Empleado;

$toDelete = Empleado::query()
    ->where(function ($query) use ($empleados) {
        foreach ($empleados as $empleado) {
            $query->orWhere(function ($q) use ($empleado) {
                $q->where('nombre', $empleado['nombre'])
                  ->where('apellido', $empleado['apellido']);
            });
        }
    })
    ->get();

if ($toDelete->isEmpty()) {
    echo "No se encontraron empleados para eliminar.\n";
    exit(0);
}

foreach ($toDelete as $empleado) {
    echo "Eliminar: {$empleado->id} | {$empleado->nombre} {$empleado->apellido}\n";
}

$deleted = 0;
foreach ($toDelete as $empleado) {
    $empleado->delete();
    $deleted++;
}

echo "Eliminados: $deleted empleados.\n";
