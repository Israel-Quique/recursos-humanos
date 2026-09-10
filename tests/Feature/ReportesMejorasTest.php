<?php

namespace Tests\Feature;

use App\Livewire\ReportesPage;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportesMejorasTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporte_consolidado_por_sucursal_agrupa_y_suma_minutos_y_omisiones(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        $empLaPaz = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
            'codigo_biometrico' => 'LP-101',
            'area' => 'Correspondencia',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        $empCbba = Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Flores',
            'codigo_biometrico' => 'CB-201',
            'area' => 'Distribucion',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        // Carlos Mamani llegó 20 minutos tarde el 2026-08-05
        RegistroAsistencia::query()->create([
            'empleado_id' => $empLaPaz->id,
            'fecha' => '2026-08-05',
            'hora_entrada' => '08:50:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Ana Flores olvidó marcar salida el 2026-08-06 (omisión)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empCbba->id,
            'fecha' => '2026-08-06',
            'hora_entrada' => '08:30:00',
            'hora_salida' => null,
            'estado_marcacion' => 'Entrada',
            'evento_biometrico' => 'Verificado',
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $consolidado = $service->reporteConsolidadoPorSucursal(Carbon::parse('2026-08-01'));

        $this->assertGreaterThanOrEqual(2, $consolidado['total_empleados']);
        $this->assertArrayHasKey('total_minutos_atraso', $consolidado);
        $this->assertArrayHasKey('total_omisiones', $consolidado);

        $sucursales = collect($consolidado['sucursales']);
        $laPaz = $sucursales->firstWhere('sucursal', 'La Paz');
        $cbba = $sucursales->firstWhere('sucursal', 'Cochabamba');

        $this->assertNotNull($laPaz);
        $this->assertNotNull($cbba);

        $this->assertSame(15, $laPaz['total_minutos_atraso']);
        $this->assertGreaterThanOrEqual(1, $cbba['total_omisiones']);
    }

    public function test_livewire_reportes_page_renders_with_branch_consolidated_report(): void
    {
        $user = \App\Models\User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin.test@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        $component = Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->assertStatus(200)
            ->assertSee('Reporte Consolidado de Asistencia y Puntualidad')
            ->assertSee('Atrasos acumulados')
            ->assertSee('Total Omisiones');
    }

    public function test_descargar_pdf_reporte_genera_stream(): void
    {
        $user = \App\Models\User::query()->create([
            'name' => 'Admin User 2',
            'email' => 'admin2.test@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        $response = Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReporte');

        $response->assertFileDownloaded();
    }

    public function test_todos_los_reportes_atrasos_omisiones_ranking_y_antiguedad_se_renderizan_con_metricas_ejecutivas(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        $empVeterano = Empleado::query()->create([
            'nombre' => 'Roberto',
            'apellido' => 'Vargas',
            'codigo_biometrico' => 'VET-001',
            'area' => 'Gerencia',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2010-01-15',
        ]);

        $empNuevo = Empleado::query()->create([
            'nombre' => 'Lucia',
            'apellido' => 'Paredes',
            'codigo_biometrico' => 'NUE-002',
            'area' => 'Ventanilla',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-07-01',
        ]);

        // Retraso
        RegistroAsistencia::query()->create([
            'empleado_id' => $empNuevo->id,
            'fecha' => '2026-08-10',
            'hora_entrada' => '09:05:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        $user = \App\Models\User::query()->create([
            'name' => 'Admin User 3',
            'email' => 'admin3.test@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->assertStatus(200)
            // Atrasos
            ->assertSee('Minutos acumulados')
            ->assertSee('Promedio por atraso')
            // Omisiones
            ->assertSee('Total Omisiones')
            ->assertSee('Días sin marcar')
            // Ranking
            ->assertSee('Líder puntual mensual')
            ->assertSee('Mayor demora mensual')
            ->assertSee('Líder semanal')
            ->assertSee('Ranking Mensual')
            ->assertSee('Ranking Semanal')
            // Antigüedad
            ->assertSee('Personal evaluado')
            ->assertSee('+10 Años trayectoria')
            ->assertSee('Ingresos recientes')
            ->assertSee('Mayor antigüedad')
            ->assertSee('Roberto Vargas')
            ->assertSee('Lucia Paredes');
    }
}

