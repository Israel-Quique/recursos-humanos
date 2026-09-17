<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class EmpleadoFotosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/empleados_fotos.json');

        if (! file_exists($jsonPath)) {
            $this->command?->error("No se encontró el archivo de datos con las fotos en base64: {$jsonPath}");
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($items)) {
            $this->command?->error("El archivo JSON de fotos está vacío o tiene un formato no válido.");
            return;
        }

        // Asegurar que el symlink de storage exista en el servidor
        $publicStoragePath = public_path('storage');
        if (! file_exists($publicStoragePath)) {
            $this->command?->info("Creando symlink 'public/storage'...");
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                $this->command?->warn("No se pudo ejecutar storage:link automáticamente: {$e->getMessage()}");
            }
        }

        // Directorio destino de fotos en storage público
        $targetDir = storage_path('app/public/fotos/empleados');
        if (! is_dir($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $guardarComoBase64EnBd = (bool) env('SEED_FOTOS_AS_BASE64', false);

        $vinculados = 0;
        $noEncontrados = 0;

        foreach ($items as $item) {
            $codigo = $item['codigo_biometrico'] ?? null;
            $nombre = $item['nombre'] ?? '';
            $apellido = $item['apellido'] ?? '';
            $empleadoId = $item['empleado_id'] ?? null;
            $filename = $item['filename'] ?? "emp_{$empleadoId}.jpg";
            $relativePath = 'fotos/empleados/' . $filename;
            $base64Data = $item['base64'] ?? '';
            $dataUri = $item['base64_data_uri'] ?? '';

            // Buscar empleado: primero por código biométrico
            $empleado = null;
            if (! empty($codigo)) {
                $empleado = Empleado::query()->where('codigo_biometrico', (string) $codigo)->first();
            }

            // Si no se encuentra, buscar por ID
            if (! $empleado && ! empty($empleadoId)) {
                $empleado = Empleado::query()->find($empleadoId);
            }

            // Si no se encuentra, buscar por coincidencia de nombre y apellido
            if (! $empleado && ! empty($nombre)) {
                $empleado = Empleado::query()
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($nombre))])
                    ->when(! empty($apellido), function ($q) use ($apellido) {
                        $q->whereRaw('LOWER(TRIM(apellido)) = ?', [mb_strtolower(trim($apellido))]);
                    })
                    ->first();
            }

            if (! $empleado) {
                $noEncontrados++;
                $this->command?->warn(" [SEPARADO] Empleado no encontrado en BD: '{$nombre} {$apellido}' (código: {$codigo})");
                continue;
            }

            // Restaurar archivo físico a partir de base64 si no existe o si se solicita
            if (! empty($base64Data)) {
                $targetFilePath = $targetDir . DIRECTORY_SEPARATOR . $filename;
                $binaryContent = base64_decode($base64Data);
                if ($binaryContent !== false) {
                    file_put_contents($targetFilePath, $binaryContent);
                }
            }

            // Asignar en BD: ruta relativa estándar o data-uri en base64
            if ($guardarComoBase64EnBd && ! empty($dataUri)) {
                $empleado->foto = $dataUri;
            } else {
                $empleado->foto = $relativePath;
            }

            $empleado->save();
            $vinculados++;
        }

        $this->command?->info("==================================================");
        $this->command?->info(" Seeder EmpleadoFotosSeeder ejecutado con éxito: ");
        $this->command?->info(" - Fotografías restauradas y vinculadas: {$vinculados}");
        if ($noEncontrados > 0) {
            $this->command?->warn(" - Registros no encontrados en la BD: {$noEncontrados}");
        }
        $this->command?->info("==================================================");
    }
}
