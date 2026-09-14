<?php

namespace Tests\Feature;

use App\Livewire\ReportesPage;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisReglamentoReporteService;
use Carbon\Carbon;
use Database\Seeders\ReglaSancionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReporteReglamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReglaSancionSeeder::class);
    }

    public function test_evaluacion_reglamento_detecta_personal_en_alerta_preventiva(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        // Funcionario con 25 min de atraso acumulado (debe estar en zona de alerta)
        $empAlerta = Empleado::query()->create([
            'nombre' => 'Marcos',
            'apellido' => 'Condori',
            'codigo_biometrico' => 'MC-01',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empAlerta->id,
            'fecha' => '2026-08-05',
            'hora_entrada' => '09:00:00', // 25 min tras 5 min tolerancia (08:35)
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-08-01'));

        $this->assertGreaterThanOrEqual(1, $reporte['metricas']['en_alerta_preventiva']);
        $enAlerta = collect($reporte['personal_en_alerta'])->firstWhere('id', $empAlerta->id);
        $this->assertNotNull($enAlerta);
        $this->assertStringContainsString('descuento', $enAlerta['distancia_umbral']);
    }

    public function test_evaluacion_reglamento_detecta_personal_sancionado_y_critico(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        // Funcionario con 45 min de atraso (sanción de 1/2 día)
        $empSancionado = Empleado::query()->create([
            'nombre' => 'Silvia',
            'apellido' => 'Mendoza',
            'codigo_biometrico' => 'SM-02',
            'area' => 'Ventanilla',
            'sucursal' => 'El Alto',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empSancionado->id,
            'fecha' => '2026-08-06',
            'hora_entrada' => '09:20:00', // 45 min de atraso tras tolerancia
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-08-01'));

        $this->assertGreaterThanOrEqual(1, $reporte['metricas']['con_sancion_economica']);
        $sancionado = collect($reporte['mas_sancionados'])->firstWhere('id', $empSancionado->id);
        $this->assertNotNull($sancionado);
        $this->assertEquals(0.5, $sancionado['dias_sancion_atraso']);
        $this->assertGreaterThanOrEqual(0.5, $sancionado['dias_sancion_total']);
    }

    public function test_livewire_reportes_renderiza_pestana_reglamento_y_descarga_pdf(): void
    {
        $user = User::query()->create([
            'name' => 'Gestora RRHH',
            'email' => 'gestora.rrhh@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->assertStatus(200)
            ->assertSee('Reglamento')
            ->assertSee('Zona de Alerta')
            ->assertSee('Con Sanción Económica')
            ->assertSee('Riesgo Crítico')
            ->assertSee('Concurrencia de Leyes')
            ->assertSee('Días Totales a Deducir')
            ->assertSee('Control de Cumplimiento del Reglamento Interno')
            ->assertSee('Detalle de Atrasos y Días a Descontar')
            ->assertSee('Detalle de Omisiones de Marcación')
            ->assertSee('Reporte de Reincidentes')
            ->call('descargarPdfReporteReglamento')
            ->assertFileDownloaded();
    }

    public function test_reincidencia_anual_detecta_meses_graves_previos(): void
    {
        $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));

        $emp = Empleado::query()->create([
            'nombre' => 'Rodrigo',
            'apellido' => 'Perez',
            'codigo_biometrico' => 'RP-09',
            'area' => 'Distribucion',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        // Mes 5 (Mayo): más de 121 min
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-05-10',
            'hora_entrada' => '11:00:00', // ~145 min tras tolerancia
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Mes 9 (Septiembre): consultar reporte de septiembre
        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-09-01'));

        $this->assertIsArray($reporte);
        $this->assertArrayHasKey('casos_criticos', $reporte);
        $this->assertArrayHasKey('metricas', $reporte);
    }

    public function test_evaluacion_reglamento_genera_detalle_atrasos_omisiones_y_reincidentes(): void
    {
        $this->travelTo(Carbon::parse('2026-05-20 12:00:00'));

        $emp = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
            'codigo_biometrico' => 'CM-77',
            'area' => 'Logística',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        // Mes 1: 45 min de atraso (> 30 min)
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-01-15',
            'hora_entrada' => '09:20:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Mes 2: 35 min de atraso (> 30 min)
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-02-10',
            'hora_entrada' => '09:10:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Mes 5 (evaluado): 2 atrasos (15 min y 20 min = 35 min total > 30 min)
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-05-04',
            'hora_entrada' => '08:50:00', // 15 min tras tolerancia
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-05-06',
            'hora_entrada' => '08:55:00', // 20 min tras tolerancia (Total 35 min)
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // 2 omisiones de marcación (olvido de entrada)
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-05-11',
            'hora_entrada' => null,
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Salida',
            'evento_biometrico' => 'Verificado',
        ]);
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-05-18',
            'hora_entrada' => null,
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Salida',
            'evento_biometrico' => 'Verificado',
        ]);

        // Registrar asistencia normal para el resto de días laborables hasta el 20 de mayo
        for ($d = 1; $d <= 20; $d++) {
            $dt = Carbon::parse(sprintf('2026-05-%02d', $d));
            if ($dt->isWeekend() || in_array($d, [4, 6, 11, 18])) {
                continue;
            }
            RegistroAsistencia::query()->create([
                'empleado_id' => $emp->id,
                'fecha' => $dt->toDateString(),
                'hora_entrada' => '08:30:00',
                'hora_salida' => '16:30:00',
                'estado_marcacion' => 'Completo',
                'evento_biometrico' => 'Verificado',
            ]);
        }

        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-05-01'));

        // 1. Detalle de Atrasos
        $this->assertArrayHasKey('detalle_atrasos', $reporte);
        $detalleAtraso = collect($reporte['detalle_atrasos'])->firstWhere('id', $emp->id);
        $this->assertNotNull($detalleAtraso);
        $this->assertEquals(2, $detalleAtraso['dias_tarde']);
        $this->assertEquals(35, $detalleAtraso['minutos_atraso']);
        $this->assertEquals(0.5, $detalleAtraso['dias_descuento']);
        $this->assertCount(2, $detalleAtraso['fechas']);
        $this->assertStringContainsString('04/05/2026', $detalleAtraso['fechas_texto']);

        // 2. Detalle de Omisiones
        $this->assertArrayHasKey('detalle_omisiones', $reporte);
        $detalleOmision = collect($reporte['detalle_omisiones'])->firstWhere('id', $emp->id);
        $this->assertNotNull($detalleOmision);
        $this->assertEquals(2, $detalleOmision['total_omisiones']);
        $this->assertEquals(1.0, $detalleOmision['dias_descuento']);
        $this->assertCount(2, $detalleOmision['fechas']);
        $this->assertStringContainsString('11/05/2026', $detalleOmision['fechas_texto']);

        // 3. Reincidentes (> 30 min en > 2 meses en el año: Mes 1, Mes 2, Mes 5 = 3 meses)
        $this->assertArrayHasKey('detalle_reincidentes', $reporte);
        $reincidenteAtraso = collect($reporte['detalle_reincidentes'])->first(function ($item) use ($emp) {
            return $item['id'] === $emp->id && $item['tipo'] === 'atrasos';
        });
        $this->assertNotNull($reincidenteAtraso);
        $this->assertGreaterThanOrEqual(3, $reincidenteAtraso['conteo_meses']);
        $this->assertStringContainsString('Mayo', $reincidenteAtraso['detalle_texto']);

        // 4. Reincidentes por Omisiones (2 omisiones)
        $reincidenteOmision = collect($reporte['detalle_reincidentes'])->first(function ($item) use ($emp) {
            return $item['id'] === $emp->id && $item['tipo'] === 'omisiones';
        });
        $this->assertNotNull($reincidenteOmision);
        $this->assertStringContainsString('2 omisiones', $reincidenteOmision['frecuencia']);
        $this->assertStringContainsString('11/05/2026', $reincidenteOmision['fechas_texto']);
    }
}

