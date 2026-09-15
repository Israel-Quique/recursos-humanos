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

    public function test_asistencia_computa_dias_transcurridos_y_no_muestra_cero_injustificado(): void
    {
        // Simulamos 14 de septiembre de 2026
        $this->travelTo(Carbon::parse('2026-09-14 10:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Mateo',
            'apellido' => 'Quispe',
            'codigo_biometrico' => 'MQ-99',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
        ]);

        // Registrar asistencia puntual en 3 días hábiles
        foreach (['2026-09-01', '2026-09-02', '2026-09-03'] as $fecha) {
            RegistroAsistencia::query()->create([
                'empleado_id' => $empleado->id,
                'fecha' => $fecha,
                'hora_entrada' => '08:25:00',
                'hora_salida' => '16:30:00',
                'estado_marcacion' => 'Completo',
                'evento_biometrico' => 'Verificado',
            ]);
        }

        $service = app(AnalisisAsistenciaService::class);
        $detalle = $service->detalleMensualPorEmpleado($empleado->id, Carbon::parse('2026-09-01'));

        $this->assertSame(3, $detalle['dias_asistidos']);
        $this->assertSame(10, $detalle['dias_laborables_transcurridos']);
        $this->assertSame(22, $detalle['dias_laborables_mes']);
        $this->assertSame(30, $detalle['porcentaje_asistencia']); // 3 de 10 = 30%
        $this->assertTrue($detalle['es_mes_en_curso']);

        $consolidado = $service->reporteConsolidadoPorSucursal(Carbon::parse('2026-09-01'), 'La Paz');
        $colaborador = collect($consolidado['sucursales']['La Paz']['empleados'])->firstWhere('id', $empleado->id);

        $this->assertNotNull($colaborador);
        $this->assertSame(3, $colaborador['dias_asistidos']);
        $this->assertSame(10, $colaborador['dias_laborables_transcurridos']);
        $this->assertSame(22, $colaborador['dias_laborables_mes']);
        $this->assertSame(30, $colaborador['porcentaje_asistencia']);
    }

    public function test_omisiones_y_faltas_estan_separadas_y_desvinculadas_de_atrasos(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Roberto',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'LP-555',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        // Caso 1: Atraso puro (marcó entrada y salida, con retraso)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-03',
            'hora_entrada' => '08:50:00',
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Caso 2: Omisión de salida (marcó entrada pero no salida)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-04',
            'hora_entrada' => '08:30:00',
            'hora_salida' => null,
            'estado_marcacion' => 'Entrada',
            'evento_biometrico' => 'Verificado',
        ]);

        // Caso 3: Omisión de entrada (no marcó entrada pero sí salida)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-05',
            'hora_entrada' => null,
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Salida',
            'evento_biometrico' => 'Verificado',
        ]);

        // 2026-08-06: No marcó nada (es una falta / inasistencia, no una omisión)

        $service = app(AnalisisAsistenciaService::class);
        $detalle = $service->detalleMensualPorEmpleado($empleado->id, Carbon::parse('2026-08-01'));

        // Atraso: solo el día 2026-08-03 (15 min)
        $this->assertSame(1, count($detalle['tardanzas']));
        $this->assertSame(15, $detalle['retraso_resumen']['total_minutos']);

        // Omisiones: exactamente 2 (el 4 falta salida y el 5 falta entrada)
        $this->assertSame(2, $detalle['total_omisiones']);
        $this->assertSame(2, count($detalle['no_marcados']));

        // Faltas: al menos 1 día sin marcación (el 2026-08-06 y posteriores hasta el 20)
        $this->assertGreaterThanOrEqual(1, $detalle['total_faltas']);
        $this->assertGreaterThanOrEqual(1, count($detalle['faltas']));

        // Verificar el consolidado por sucursal
        $consolidado = $service->reporteConsolidadoPorSucursal(Carbon::parse('2026-08-01'), 'La Paz');
        $this->assertArrayHasKey('total_omisiones', $consolidado);
        $this->assertArrayHasKey('total_faltas', $consolidado);
        $laPaz = $consolidado['sucursales']['La Paz'];
        $this->assertSame(2, $laPaz['total_omisiones']);
        $this->assertGreaterThanOrEqual(1, $laPaz['total_faltas']);

        $empConsolidado = collect($laPaz['empleados'])->firstWhere('id', $empleado->id);
        $this->assertSame(2, $empConsolidado['omisiones']);
        $this->assertGreaterThanOrEqual(1, $empConsolidado['faltas']);
        $this->assertSame(15, $empConsolidado['minutos_atraso']);
        $this->assertSame('La Paz', $empConsolidado['sucursal']);

        // Verificar que el PDF renderiza la columna 'Sucursal' y no 'Área / Cargo'
        $viewContent = view('pdf.reportes-general', [
            'monthLabel' => 'Agosto 2026',
            'branchLabel' => 'La Paz',
            'reporteSucursales' => $consolidado,
        ])->render();

        $this->assertStringContainsString('Correos de Bolivia', $viewContent);
        $this->assertStringContainsString('>Sucursal</th>', $viewContent);
        $this->assertStringNotContainsString('Área / Cargo', $viewContent);
        $this->assertStringContainsString('>Faltas</th>', $viewContent);
        $this->assertStringContainsString('>Omisiones</th>', $viewContent);
    }
}


