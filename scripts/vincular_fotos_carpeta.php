<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function removeAccents($str) {
    $unwanted = [
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C',
        'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'à'=>'a', 'á'=>'a',
        'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
        'î'=>'i', 'ï'=>'i', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
        'û'=>'u', 'ü'=>'u', 'ý'=>'y'
    ];
    return strtr($str, $unwanted);
}

function normTokens($s) {
    $s = mb_strtoupper(removeAccents($s), 'UTF-8');
    $s = preg_replace('/[^A-Z0-9]/', ' ', $s);
    return array_values(array_filter(explode(' ', $s)));
}

function simplifyString($s) {
    $s = mb_strtoupper(removeAccents($s), 'UTF-8');
    return preg_replace('/[^A-Z0-9]/', '', $s);
}

$sourceDir = __DIR__ . '/../FOTOS PARA CREDENCIALES';
$targetDir = storage_path('app/public/fotos/empleados');

if (!File::isDirectory($targetDir)) {
    File::makeDirectory($targetDir, 0755, true);
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir));
$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $ext = strtolower($file->getExtension());
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $files[] = $file->getPathname();
    }
}

$empleados = Empleado::all();

echo "========================================================\n";
echo " VINCULACIÓN AUTOMÁTICA DE FOTOGRAFÍAS DE EMPLEADOS     \n";
echo "========================================================\n";
echo "Total de archivos de imagen encontrados: " . count($files) . "\n";
echo "Total de empleados en el sistema: " . $empleados->count() . "\n\n";

$actualizados = 0;
$sinCoincidencia = [];

DB::beginTransaction();

try {
    foreach ($files as $filePath) {
        $baseName = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Limpieza de nombre del archivo (quitar menciones como (1), (BENI), etc.)
        $cleanName = preg_replace('/\s*\(\d+\)\s*/', '', $baseName);
        $cleanName = preg_replace('/\s*\([A-Z\s]+\)\s*/', ' ', $cleanName);
        $cleanName = preg_replace('/\s*\d+\.jpg/i', '', $cleanName);

        $tokens = normTokens($cleanName);
        $simplifiedPhoto = simplifyString($cleanName);

        // Detectar si la carpeta padre indica regional
        $parentFolder = strtoupper(basename(dirname($filePath)));
        $regionalHint = '';
        if (str_contains($parentFolder, 'BENI')) $regionalHint = 'Beni';
        elseif (str_contains($parentFolder, 'COCHABAMBA')) $regionalHint = 'Cochabamba';
        elseif (str_contains($parentFolder, 'ORURO')) $regionalHint = 'Oruro';
        elseif (str_contains($parentFolder, 'PANDO')) $regionalHint = 'Pando';
        elseif (str_contains($parentFolder, 'POTOSI')) $regionalHint = 'Potosi';
        elseif (str_contains($parentFolder, 'SANTA CRUZ')) $regionalHint = 'Santa Cruz';
        elseif (str_contains($parentFolder, 'SUCRE')) $regionalHint = 'Sucre';
        elseif (str_contains($parentFolder, 'LA PAZ')) $regionalHint = 'La Paz';

        $bestEmp = null;
        $maxScore = 0;
        $matchReason = '';

        foreach ($empleados as $emp) {
            $empFullName = $emp->nombre . ' ' . $emp->apellido;
            $empTokens = array_merge(normTokens($emp->nombre), normTokens($emp->apellido));
            $empSimplified = simplifyString($empFullName);

            if (empty($empTokens) && empty($empSimplified)) continue;

            // 1. Coincidencia por subcadena exacta concatenada (ej: JOSEALFREDOMALPARTIDA)
            if (strlen($simplifiedPhoto) >= 6 && strlen($empSimplified) >= 6) {
                if (str_contains($empSimplified, $simplifiedPhoto) || str_contains($simplifiedPhoto, $empSimplified)) {
                    $bestEmp = $emp;
                    $maxScore = 1.0;
                    $matchReason = 'concat_subcadena';
                    break;
                }

                // Typos fonéticos como MICHELLSMEDRANODAZA vs MISHELLSMEDRANODAZA
                $lev = levenshtein(substr($simplifiedPhoto, 0, 255), substr($empSimplified, 0, 255));
                if ($lev <= 2) {
                    $bestEmp = $emp;
                    $maxScore = 0.95;
                    $matchReason = 'concat_levenshtein';
                    break;
                }
            }

            // 2. Coincidencia token a token (nombre y apellidos)
            $overlap = 0;
            foreach ($tokens as $t) {
                $matchedToken = false;
                foreach ($empTokens as $et) {
                    if ($t === $et) {
                        $overlap += 1.0;
                        $matchedToken = true;
                        break;
                    } elseif (strlen($t) >= 4 && strlen($et) >= 4 && (str_starts_with($t, $et) || str_starts_with($et, $t))) {
                        $overlap += 0.9;
                        $matchedToken = true;
                        break;
                    } elseif (strlen($t) >= 5 && strlen($et) >= 5 && levenshtein($t, $et) === 1) {
                        $overlap += 0.85;
                        $matchedToken = true;
                        break;
                    }
                }

                // Solo si no coincidió con ningún token individual, buscar si está contenido en nombre concatenado (ej. MEDRANO en mishellsmedranodaza)
                if (!$matchedToken && strlen($t) >= 4 && str_contains($empSimplified, $t)) {
                    $overlap += 0.95;
                }
            }

            // Si la foto indica una regional específica (ej. BENI) y el empleado es de otra regional (ej. SANTA CRUZ),
            // descartar para evitar falsos positivos por un solo apellido
            if ($regionalHint && $emp->sucursal) {
                $empSuc = str_replace(' ', '', strtoupper($emp->sucursal));
                $hintSuc = str_replace(' ', '', strtoupper($regionalHint));
                if (!str_contains($empSuc, $hintSuc) && !str_contains($hintSuc, $empSuc)) {
                    // Solo permitir si al menos 3 tokens coinciden exactamente
                    if ($overlap < 2.8) {
                        continue;
                    }
                }
            }

            $tokenCoverage = $overlap / max(1, count($tokens));
            $empCoverage = $overlap / max(1, count($empTokens));
            $score = ($tokenCoverage * 0.6) + ($empCoverage * 0.4);

            // Si el nombre de la foto tiene 2 o más palabras, exigir al menos 1.7 de overlap
            if (count($tokens) >= 2) {
                if ($overlap >= 1.7 && $score > $maxScore) {
                    $maxScore = $score;
                    $bestEmp = $emp;
                    $matchReason = 'tokens_coincidentes';
                }
            } else {
                if ($overlap >= 1.0 && $score > $maxScore) {
                    $maxScore = $score;
                    $bestEmp = $emp;
                    $matchReason = 'token_unico';
                }
            }
        }

        // Caso especial: RODRIGO PAOLO ROCA en regional Beni cuando en biometrico figura solo como 'rodrigo'
        if ((!$bestEmp || $maxScore < 0.7) && $regionalHint === 'Beni' && in_array('RODRIGO', $tokens, true)) {
            $rodrigoBeni = $empleados->first(fn($e) => strtoupper($e->sucursal ?? '') === 'BENI' && strtoupper($e->nombre) === 'RODRIGO');
            if ($rodrigoBeni) {
                $bestEmp = $rodrigoBeni;
                $maxScore = 0.8;
                $matchReason = 'regional_rodrigo_beni';
            }
        }

        // Caso especial: 'jose malpartida' en Potosí para JOSEALFREDOMALPARTIDA
        if ((!$bestEmp || $maxScore < 0.7) && str_contains($simplifiedPhoto, 'MALPARTIDA')) {
            $malpartida = $empleados->first(fn($e) => str_contains(simplifyString($e->nombre . $e->apellido), 'MALPARTIDA'));
            if ($malpartida) {
                $bestEmp = $malpartida;
                $maxScore = 1.0;
                $matchReason = 'malpartida_potosi';
            }
        }

        if ($bestEmp && $maxScore >= 0.5) {
            // Destino seguro: fotos/empleados/empleado_{id}_{codigo}.ext
            $cleanExt = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';
            $safeCodigo = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$bestEmp->codigo_biometrico ?: (string)$bestEmp->id);
            $newFileName = 'emp_' . $bestEmp->id . '_' . $safeCodigo . '.' . $cleanExt;
            $destPath = $targetDir . DIRECTORY_SEPARATOR . $newFileName;

            // Copiar archivo físico
            File::copy($filePath, $destPath);

            // Guardar ruta relativa en empleado
            $relativeFotoPath = 'fotos/empleados/' . $newFileName;
            $bestEmp->foto = $relativeFotoPath;
            $bestEmp->save();

            // Si tiene usuario vinculado sin foto, asignársela también
            $linkedUser = User::where('empleado_id', $bestEmp->id)->first();
            if ($linkedUser && empty($linkedUser->foto)) {
                $linkedUser->foto = $relativeFotoPath;
                $linkedUser->save();
            }

            $actualizados++;
            echo " [VINCULADO] '{$baseName}' => ID {$bestEmp->id} ({$bestEmp->nombre} {$bestEmp->apellido}) - Sucursal: {$bestEmp->sucursal}\n";
        } else {
            $sinCoincidencia[] = [
                'archivo' => $baseName,
                'ruta' => $filePath,
                'regional' => $regionalHint ?: 'Sin regional',
            ];
        }
    }

    DB::commit();
    echo "\n=== PROCESO COMPLETADO EXITOSAMENTE ===\n";
    echo "Fotos vinculadas y copiadas: {$actualizados}\n";
    echo "Fotos sin coincidencia en BD: " . count($sinCoincidencia) . "\n\n";

    if (!empty($sinCoincidencia)) {
        echo "=== DETALLE DE FOTOS SIN COINCIDENCIA EN LA BASE DE DATOS ===\n";
        foreach ($sinCoincidencia as $sc) {
            echo "  - [{$sc['regional']}] '{$sc['archivo']}'\n";
        }
        echo "\n(Nota: Estas personas no existen aún en la base de datos o fueron registradas con nombres totalmente distintos. Podrán ser asignadas manualmente desde el módulo de Personal por el Gestor).\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR DURANTE LA VINCULACIÓN: " . $e->getMessage() . "\n";
    exit(1);
}
