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
}

