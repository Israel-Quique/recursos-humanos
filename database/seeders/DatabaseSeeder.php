<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $adminAlias = User::query()->updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'admin',
                'password' => Hash::make('CH1MU3L0'),
            ]
        );

        $adminPrincipal = User::query()->updateOrCreate(
            ['email' => 'admin@recursoshumanos.local'],
            [
                'name' => 'Administrador RH',
                'password' => Hash::make('CH1MU3L0'),
            ]
        );

        $usuario = User::query()->updateOrCreate(
            ['email' => 'usuario@recursoshumanos.local'],
            [
                'name' => 'Gestor RRHH',
                'password' => Hash::make('CH1MU3L0'),
            ]
        );

        $visor = User::query()->updateOrCreate(
            ['email' => 'visor@recursoshumanos.local'],
            [
                'name' => 'Visor RRHH',
                'password' => Hash::make('CH1MU3L0'),
            ]
        );

        $adminAlias->syncRoles(['administrador']);
        $adminPrincipal->syncRoles(['administrador']);
        $usuario->syncRoles(['gestor']);
        $visor->syncRoles(['visor']);

        $empleados = collect([
            ['nombre' => 'Ana', 'apellido' => 'Lopez', 'codigo' => '1045', 'area' => 'Recursos Humanos', 'sucursal' => 'La Paz'],
            ['nombre' => 'Bruno', 'apellido' => 'Mamani', 'codigo' => '1046', 'area' => 'Operaciones', 'sucursal' => 'Oruro'],
            ['nombre' => 'Carla', 'apellido' => 'Rojas', 'codigo' => '1047', 'area' => 'Tesoreria', 'sucursal' => 'Potosi'],
            ['nombre' => 'Diego', 'apellido' => 'Vargas', 'codigo' => '1048', 'area' => 'Atencion al Cliente', 'sucursal' => 'Cochabamba'],
            ['nombre' => 'Elena', 'apellido' => 'Suarez', 'codigo' => '1049', 'area' => 'Archivo', 'sucursal' => 'Chuquisaca'],
            ['nombre' => 'Fabian', 'apellido' => 'Arias', 'codigo' => '1050', 'area' => 'Distribucion', 'sucursal' => 'Tarija'],
            ['nombre' => 'Gloria', 'apellido' => 'Perez', 'codigo' => '1051', 'area' => 'Regional', 'sucursal' => 'Santa Cruz'],
            ['nombre' => 'Hugo', 'apellido' => 'Ribera', 'codigo' => '1052', 'area' => 'Logistica', 'sucursal' => 'Beni'],
            ['nombre' => 'Irene', 'apellido' => 'Justiniano', 'codigo' => '1053', 'area' => 'Administracion', 'sucursal' => 'Pando'],
        ])->map(function (array $item) use ($adminPrincipal) {
            return Empleado::query()->updateOrCreate(
                ['codigo_biometrico' => $item['codigo']],
                [
                    'nombre' => $item['nombre'],
                    'apellido' => $item['apellido'],
                    'area' => $item['area'],
                    'sucursal' => $item['sucursal'],
                    'hora_entrada_programada' => '08:30',
                    'hora_salida_programada' => '16:30',
                    'fecha_contratacion' => '2026-07-01',
                    'created_by' => $adminPrincipal->id,
                ]
            );
        });

        $this->seedAsistenciasDemo($empleados, $adminPrincipal->id);
    }

    private function seedAsistenciasDemo(Collection $empleados, int $adminUserId): void
    {
        $fecha = now()->toDateString();
        $porCodigo = $empleados->keyBy('codigo_biometrico');

        $registros = [
            '1045' => ['hora_entrada' => '08:24', 'hora_salida' => '16:37', 'observacion' => 'Registro inicial de ejemplo.'],
            '1046' => ['hora_entrada' => '08:31', 'hora_salida' => '16:26', 'observacion' => 'Turno normal.'],
            '1048' => ['hora_entrada' => '08:18', 'hora_salida' => null, 'observacion' => 'Pendiente de salida biometrica.'],
            '1050' => ['hora_entrada' => '08:41', 'hora_salida' => '16:32', 'observacion' => 'Ingreso con retraso leve.'],
            '1051' => ['hora_entrada' => '08:09', 'hora_salida' => '16:20', 'observacion' => 'Jornada completa.'],
            '1052' => ['hora_entrada' => '08:35', 'hora_salida' => null, 'observacion' => 'Olvido de marcacion de salida.'],
            '1053' => ['hora_entrada' => '08:12', 'hora_salida' => '16:40', 'observacion' => 'Cobija operativa.'],
        ];

        foreach ($registros as $codigo => $registro) {
            $empleado = $porCodigo->get($codigo);
            if (! $empleado) {
                continue;
            }

            RegistroAsistencia::query()->updateOrCreate(
                ['empleado_id' => $empleado->id, 'fecha' => $fecha],
                $registro + ['created_by' => $adminUserId]
            );
        }

        $empleadaConPermiso = $porCodigo->get('1049');
        if ($empleadaConPermiso) {
            PermisoLaboral::query()->updateOrCreate(
                ['empleado_id' => $empleadaConPermiso->id, 'fecha_inicio' => $fecha, 'fecha_fin' => $fecha],
                [
                    'tipo' => 'permiso',
                    'alcance' => 'dia_completo',
                    'estado' => 'aprobado',
                    'minutos_contabilizados' => 480,
                    'motivo' => 'Consulta programada.',
                    'created_by' => $adminUserId,
                ]
            );
        }
    }
}
