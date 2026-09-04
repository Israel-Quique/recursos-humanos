<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Seeder;

class EmpleadoCorreosSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/empleados_correos.json');

        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            $actualizados = 0;

            foreach ($data as $item) {
                $codigo = $item['codigo_biometrico'] ?? null;
                $email = $item['email'] ?? null;
                $nombre = $item['nombre'] ?? '';
                $apellido = $item['apellido'] ?? '';

                if (!$email) {
                    continue;
                }

                // Buscar por código biométrico si existe
                $empleado = null;
                if ($codigo) {
                    $empleado = Empleado::query()->where('codigo_biometrico', $codigo)->first();
                }

                // Si no por código, buscar por nombre y apellido
                if (!$empleado && $nombre && $apellido) {
                    $empleado = Empleado::query()
                        ->where('nombre', $nombre)
                        ->where('apellido', $apellido)
                        ->first();
                }

                if ($empleado) {
                    $empleado->email = strtolower(trim($email));
                    $empleado->save();
                    $actualizados++;
                }
            }

            $this->command?->info("Seeder EmpleadoCorreos: {$actualizados} correos vinculados correctamente.");
        } else {
            $this->command?->warn("No se encontró el archivo de datos: {$jsonPath}");
        }
    }
}
