<?php

namespace Tests\Unit;

use App\Models\Empleado;
use App\Services\ExtraccionBiometricoZkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraccionBiometricoZkServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportar_pando_no_reemplaza_nombres_del_reloj_con_empleados_de_oruro(): void
    {
        foreach ([['Oruro', '7', 'Empleado', 'Oruro'], ['Cobija', '8', 'Empleado', 'Pando']] as [$sucursal, $codigo, $nombre, $apellido]) {
            Empleado::query()->create([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'codigo_biometrico' => $codigo,
                'sucursal' => $sucursal,
                'area' => 'Operaciones',
                'hora_entrada_programada' => '08:30:00',
                'fecha_contratacion' => '2026-01-01',
            ]);
        }

        $path = app(ExtraccionBiometricoZkService::class)->exportarMarcacionesComoCsv([
            'department' => 'Pando',
            'branch' => 'Sucursal Pando',
        ], [
            ['codigo' => '7', 'nombre' => 'Nombre', 'apellido' => 'Del Reloj', 'fecha_hora' => '2026-10-01T08:15:00'],
            ['codigo' => '7', 'fecha_hora' => '2026-10-01T16:30:00'],
            ['codigo' => '8', 'fecha_hora' => '2026-10-02T08:30:00'],
            ['codigo' => '8', 'nombre' => 'Nombre Actual', 'apellido' => 'Del Reloj', 'fecha_hora' => '2026-10-02T16:30:00'],
        ]);

        try {
            $rows = array_map('str_getcsv', file($path, FILE_IGNORE_NEW_LINES));
            $this->assertSame(['Nombre', 'Del Reloj'], array_slice($rows[1], 2, 2));
            $this->assertSame(['', ''], array_slice($rows[2], 2, 2));
            $this->assertSame(['Empleado', 'Pando'], array_slice($rows[3], 2, 2));
            $this->assertSame(['Nombre Actual', 'Del Reloj'], array_slice($rows[4], 2, 2));
            $this->assertSame('01/10/2026 08:15', $rows[1][0]);
            $this->assertSame('Sucursal Pando', $rows[1][6]);
        } finally {
            @unlink($path);
        }
    }

    public function test_exportacion_csv_usa_nombre_del_biometrico_si_no_existe_empleado_local(): void
    {
        $service = app(ExtraccionBiometricoZkService::class);

        $path = $service->exportarMarcacionesComoCsv([
            'department' => 'La Paz',
            'branch' => 'Oficina Central La Paz',
        ], [[
            'codigo' => '9118507',
            'nombre' => 'SONIA',
            'apellido' => 'POMA LIMPIEZA',
            'nombre_completo' => 'SONIA POMA LIMPIEZA',
            'numero_tarjeta' => '',
            'fecha_hora' => '2025-04-04T11:30:52',
            'estado' => '1',
            'punch' => '1',
        ]]);

        $this->assertFileExists($path);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('SONIA', $lines[1]);
        $this->assertStringContainsString('POMA LIMPIEZA', $lines[1]);

        @unlink($path);
    }

    public function test_exportacion_csv_conserva_nombre_del_biometrico_en_cualquier_sucursal(): void
    {
        $service = app(ExtraccionBiometricoZkService::class);

        $path = $service->exportarMarcacionesComoCsv([
            'department' => 'Santa Cruz',
            'branch' => 'Sucursal Santa Cruz',
        ], [[
            'codigo' => '8001122',
            'nombre' => 'MARIA',
            'apellido' => 'ROJAS FLORES',
            'nombre_completo' => 'MARIA ROJAS FLORES',
            'numero_tarjeta' => '',
            'fecha_hora' => '2025-05-08T08:15:00',
            'estado' => '0',
            'punch' => '1',
        ]]);

        $this->assertFileExists($path);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('MARIA', $lines[1]);
        $this->assertStringContainsString('ROJAS FLORES', $lines[1]);
        $this->assertStringContainsString('Santa Cruz', $lines[1]);

        @unlink($path);
    }
}
