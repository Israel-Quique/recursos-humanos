<?php

namespace Tests\Unit;

use App\Services\ExtraccionBiometricoZkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraccionBiometricoZkServiceTest extends TestCase
{
    use RefreshDatabase;

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
