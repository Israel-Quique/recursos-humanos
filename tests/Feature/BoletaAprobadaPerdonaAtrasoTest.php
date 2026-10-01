<?php

namespace Tests\Feature;

use App\Livewire\PerfilHorasPage;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use App\Services\AnalisisReglamentoReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoletaAprobadaPerdonaAtrasoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-08-14 10:00:00'));
    }

    public function test_boleta_aprobada_perdona_minutos_atraso_y_se_muestra_en_perfil_horas(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'JUAN',
            'apellido' => 'PEREZ',
            'codigo_biometrico' => '123456',
            'area' => 'ADMINISTRACION',
            'cargo' => 'ANALISTA',
            'sucursal' => 'CENTRAL',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
            'es_especial' => false,
            'tolerancia_personalizada' => 30,
        ]);

        // Registro de asistencia con atraso de 30 minutos (09:00 vs 08:30)
        $fecha = '2026-08-10'; // Lunes
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => $fecha,
            'hora_entrada' => '09:00:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'estado' => 'Presente',
        ]);

        $analisis = app(AnalisisAsistenciaService::class);

        // Sin boleta aprobada: el servicio marca 25 min de atraso (09:00 vs 08:35 con tolerancia)
        $repAntes = $analisis->reportePersonalizado($empleado->id, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
        $this->assertSame(25, $repAntes['retraso_resumen']['total_minutos']);
        $this->assertSame(1, $repAntes['retraso_resumen']['dias_tarde']);

        // Ahora creamos la boleta / permiso APROBADO para esa fecha
        $boleta = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'aprobado',
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'hora_inicio' => '08:30:00',
            'hora_fin' => '09:00:00',
            'motivo' => 'RETRASO: Tráfico pesado',
            'minutos_contabilizados' => 30,
        ]);

        // 1. Validar reportePersonalizado: el atraso queda en 0 y perdonado
        $repDespues = $analisis->reportePersonalizado($empleado->id, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
        $this->assertSame(0, $repDespues['retraso_resumen']['total_minutos']);
        $this->assertSame(0, $repDespues['retraso_resumen']['dias_tarde']);
        $fila = collect($repDespues['rows'])->firstWhere('raw_date', $fecha);
        $this->assertNotNull($fila);
        $this->assertTrue($fila['retraso_justificado']);
        $this->assertSame(0, $fila['retraso_minutos']);
        $this->assertSame(25, $fila['retraso_original']);

        // 2. Validar perfil-horas Livewire view
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->set('referenceMonth', '2026-08')
            ->assertSee('0 min (Justificado)')
            ->assertSee('Boleta Aprobada')
            ->assertDontSee('Mis Boletas y Permisos Registrados');

        // 3. Validar reporte mensual de atrasos
        $reporteMensual = $analisis->reporteMensualNoMarcadosYAtrasos(Carbon::parse('2026-08-01'));
        $empleadoEnAtrasos = collect($reporteMensual['atrasos'])->contains('empleado_id', $empleado->id);
        $this->assertFalse($empleadoEnAtrasos, 'El empleado con boleta aprobada no debe figurar en el reporte de atrasos.');

        // 4. Validar sanciones del reglamento
        $reglamentoService = app(AnalisisReglamentoReporteService::class);
        $analisisReglamento = $reglamentoService->generarReporteReglamento(Carbon::parse('2026-08-01'));
        $empleadoSancionado = collect($analisisReglamento['detalle_atrasos'])->contains('id', $empleado->id);
        $this->assertFalse($empleadoSancionado, 'El empleado con boleta aprobada no debe ser sancionado por atrasos.');
    }
}
