<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalisisAsistenciaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_incidencias_por_rango_no_marca_faltas_en_fechas_futuras(): void
    {
        $this->travelTo(Carbon::parse('2026-08-10 09:00:00'));

        Empleado::query()->create([
            'nombre' => 'Laura',
            'apellido' => 'Rios',
            'codigo_biometrico' => 'LR-001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-10',
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $incidencias = $service->incidenciasPorRango(
            Carbon::parse('2026-08-10'),
            Carbon::parse('2026-08-12')
        );

        $this->assertCount(1, $incidencias['faltas']);
        $this->assertSame('10/08/2026 - La Paz - ausencia injustificada', $incidencias['faltas'][0]['detalle']);
    }

    public function test_reporte_personalizado_no_agrega_faltas_en_dias_futuros(): void
    {
        $this->travelTo(Carbon::parse('2026-08-10 10:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Eynar',
            'apellido' => 'Quispe Crispin',
            'codigo_biometrico' => '9996456',
            'area' => 'Operaciones',
            'sucursal' => 'LaPaz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-10',
            'hora_entrada' => '08:44:00',
            'hora_salida' => null,
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $reporte = $service->reportePersonalizado(
            $empleado->id,
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31')
        );

        $this->assertNotNull($reporte);
        $this->assertIsArray($reporte['rows']);
        $fechas = collect($reporte['rows'])->pluck('fecha')->all();

        $this->assertContains('10/08/2026', $fechas);
        $this->assertNotContains('11/08/2026', $fechas);
        $this->assertNotContains('12/08/2026', $fechas);
        $this->assertNotContains('13/08/2026', $fechas);
        $this->assertNotContains('14/08/2026', $fechas);
    }

    public function test_reporte_personalizado_incluye_marcacion_del_dia_final_aun_si_fecha_tiene_hora_en_sqlite(): void
    {
        $this->travelTo(Carbon::parse('2026-08-10 10:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Eynar Quispe',
            'apellido' => 'Crispin',
            'codigo_biometrico' => '9996456',
            'area' => 'Operaciones',
            'sucursal' => 'LaPaz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        DB::table('registros_asistencia')->insert([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-10 00:00:00',
            'hora_entrada' => '08:44:00',
            'hora_salida' => null,
            'estado_marcacion' => 'Salida',
            'evento_biometrico' => 'Boton de salida',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $reporte = $service->reportePersonalizado(
            $empleado->id,
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31')
        );

        $this->assertNotNull($reporte);
        $filaHoy = collect($reporte['rows'])->firstWhere('fecha', '10/08/2026');

        $this->assertNotNull($filaHoy);
        $this->assertSame('08:44', $filaHoy['entrada']);
        $this->assertSame('--:--', $filaHoy['salida']);
        $this->assertSame('En su puesto', $filaHoy['estado']);
    }
}
