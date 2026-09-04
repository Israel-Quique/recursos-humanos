<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

$jsonPath = 'C:/Users/WILLIAMS/.gemini/antigravity-ide/brain/28037d38-e3b2-4d0e-8f43-6d0b4d8d6802/scratch/correos_extraidos.json';

if (!file_exists($jsonPath)) {
    echo "Error: no se encontro el archivo con los correos extraidos.\n";
    exit(1);
}

$raw = json_decode(file_get_contents($jsonPath), true);

function normalizeName($str) {
    $str = mb_strtoupper(trim($str), 'UTF-8');
    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    $str = preg_replace('/[^A-Z0-9 ]/', ' ', $str);
    return preg_replace('/\s+/', ' ', trim($str));
}

$empleados = Empleado::all();
$excelItems = [];

foreach ($raw as $r) {
    $nombre = $r['A'] ?? '';
    $correo = trim($r['B'] ?? '');
    if ($correo && str_contains($correo, '@') && $nombre !== 'Nombre del Personal') {
        $excelItems[] = [
            'nombre_original' => $nombre,
            'nombre_norm' => normalizeName($nombre),
            'correo' => strtolower($correo),
        ];
    }
}

echo "========================================================\n";
echo " VINCULACIÓN DE CORREOS DEL PERSONAL - RECURSOS HUMANOS \n";
echo "========================================================\n";
echo "Total de empleados en el sistema: " . $empleados->count() . "\n";
echo "Total de correos válidos en Excel: " . count($excelItems) . "\n\n";

$actualizados = 0;
$errores = 0;

DB::beginTransaction();

try {
    foreach ($excelItems as $item) {
        $found = null;
        $itemWords = explode(' ', $item['nombre_norm']);

        // 1. Coincidencia exacta por nombre completo o invertido
        foreach ($empleados as $emp) {
            $empFullName = normalizeName($emp->nombre . ' ' . $emp->apellido);
            $empInverted = normalizeName($emp->apellido . ' ' . $emp->nombre);
            if ($empFullName === $item['nombre_norm'] || $empInverted === $item['nombre_norm']) {
                $found = $emp;
                break;
            }
        }

        // 2. Coincidencia por contención de palabras
        if (!$found) {
            foreach ($empleados as $emp) {
                $empFullName = normalizeName($emp->nombre . ' ' . $emp->apellido);
                $empWords = explode(' ', $empFullName);
                $intersect = array_intersect($itemWords, $empWords);
                if (count($intersect) >= 2 && count($intersect) >= (count($empWords) - 1)) {
                    $found = $emp;
                    break;
                }
            }
        }

        if ($found) {
            $found->email = $item['correo'];
            $found->save();
            $actualizados++;
            echo "✓ [ID: {$found->id}] {$found->nombre} {$found->apellido} (Cód: " . ($found->codigo_biometrico ?: 'S/C') . ") -> {$item['correo']}\n";
        } else {
            echo "✗ No se encontró coincidencia para: {$item['nombre_original']} ({$item['correo']})\n";
            $errores++;
        }
    }

    DB::commit();
    echo "\n--------------------------------------------------------\n";
    echo "PROCESO COMPLETADO EXITOSAMENTE\n";
    echo "Empleados actualizados con su correo: {$actualizados}\n";
    echo "Sin coincidencia: {$errores}\n";
    echo "--------------------------------------------------------\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR durante la vinculación: " . $e->getMessage() . "\n";
    exit(1);
}
