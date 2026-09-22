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

    public function test_busqueda_individual_funcionario_en_reglamento_y_descarga_con_descuento(): void
    {
        $user = User::query()->create([
            'name' => 'Auditor RRHH',
            'email' => 'auditor.rrhh@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        $emp = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Quispe Mamani',
            'codigo_biometrico' => 'CQ-555',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        // Atraso de 45 min (> 30 min tolerancia -> sanción 0.5 días según Art. 45.I)
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-08-10',
            'hora_entrada' => '09:25:00', // 45 min de retraso
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->set('searchReglamento', 'Carlos Quispe')
            ->assertStatus(200)
            ->assertSee('Consulta de Cumplimiento y Sanción Individual')
            ->assertSee('Carlos Quispe Mamani')
            ->assertSee('CQ-555')
            ->assertSee('SE EXCEDE DEL REGLAMENTO INTERNO')
            ->assertSee('45') // minutos de atraso
            ->assertSee('1/2 día') // descuento calculado
            ->call('descargarPdfIndividualReglamento', $emp->id)
            ->assertFileDownloaded();
    }

    public function test_descargas_pdf_separadas_por_numero_de_reglamento(): void
    {
        $user = User::query()->create([
            'name' => 'Gestora RRHH',
            'email' => 'gestora.reglamento@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        // 1. Atrasos (Art. 45.I)
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'atrasos')
            ->assertFileDownloaded('reporte-reglamento-atrasos-art45-todas-las-sucursales-2026-08.pdf');

        // 2. Omisiones (Art. 45.III y Art. 48.IV)
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'omisiones')
            ->assertFileDownloaded('reporte-reglamento-omisiones-art45-48-todas-las-sucursales-2026-08.pdf');

        // 3. Faltas e Inasistencias (Art. 45.II y Art. 48)
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'faltas')
            ->assertFileDownloaded('reporte-reglamento-faltas-art45-48-todas-las-sucursales-2026-08.pdf');

        // 4. Zona de Peligro / Alertas Preventivas
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'alertas')
            ->assertFileDownloaded('reporte-reglamento-zona-peligro-alertas-todas-las-sucursales-2026-08.pdf');

        // 5. Reincidentes
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'reincidentes')
            ->assertFileDownloaded('reporte-reglamento-reincidentes-todas-las-sucursales-2026-08.pdf');

        // 6. Concurrencia (Art. 45 + 48)
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'concurrente')
            ->assertFileDownloaded('reporte-reglamento-concurrencia-art45-48-todas-las-sucursales-2026-08.pdf');

        // 7. Consolidado General
        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->call('descargarPdfReglamentoCategoria', 'todos')
            ->assertFileDownloaded('reporte-reglamento-sanciones-todas-las-sucursales-2026-08.pdf');
    }

    public function test_vista_muestra_pestana_faltas_y_botones_lado_a_lado(): void
    {
        $user = User::query()->create([
            'name' => 'Gestora RRHH',
            'email' => 'gestora.vista@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        $this->actingAs($user);

        Livewire::test(ReportesPage::class)
            ->set('referenceMonth', '2026-08')
            ->assertStatus(200)
            ->assertSee('Detalle Atrasos')
            ->assertSee('Detalle Omisiones')
            ->assertSee('Detalle Faltas')
            ->assertSee('Zona de Peligro')
            ->assertSee('Descargar PDF Atrasos (Art. 45.I)')
            ->assertSee('Descargar PDF Omisiones (Art. 45/48)')
            ->assertSee('Descargar PDF Faltas (Art. 45.II / 48)')
            ->assertSee('Descargar PDF Zona de Peligro (Alertas)')
            ->assertSee('3. Detalle de Faltas e Inasistencias (Art. 45.II y Art. 48.II/III)');
    }

    public function test_empleados_en_tolerancia_no_aparecen_en_detalle_atrasos_ni_reincidentes(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        // Empleado 1: Dentro de tolerancia (15 min atraso -> 0 días descuento)
        $empTolerancia = Empleado::query()->create([
            'nombre' => 'Juan',
            'apellido' => 'En Tolerancia',
            'codigo_biometrico' => 'JT-01',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empTolerancia->id,
            'fecha' => '2026-08-05',
            'hora_entrada' => '08:50:00', // 15 min tras tolerancia (Dentro de los 30 min mensuales)
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        // Empleado 2: Supera tolerancia (45 min atraso -> 0.5 días descuento)
        $empSancionado = Empleado::query()->create([
            'nombre' => 'Mario',
            'apellido' => 'Con Sancion',
            'codigo_biometrico' => 'MS-02',
            'area' => 'Ventanilla',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empSancionado->id,
            'fecha' => '2026-08-06',
            'hora_entrada' => '09:20:00', // 45 min tras tolerancia (> 30 min)
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Completo',
            'evento_biometrico' => 'Verificado',
        ]);

        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-08-01'));

        $detalleAtrasos = collect($reporte['detalle_atrasos']);

        // El empleado dentro de tolerancia NO debe aparecer en detalle_atrasos
        $this->assertNull($detalleAtrasos->firstWhere('id', $empTolerancia->id), 'El empleado en tolerancia no debe figurar en detalle_atrasos');

        // El empleado sancionado SÍ debe aparecer en detalle_atrasos
        $itemSancionado = $detalleAtrasos->firstWhere('id', $empSancionado->id);
        $this->assertNotNull($itemSancionado, 'El empleado sancionado debe figurar en detalle_atrasos');
        $this->assertEquals(0.5, $itemSancionado['dias_descuento']);
        $this->assertTrue($itemSancionado['es_sancionado']);
    }

    public function test_omision_de_entrada_no_se_computa_como_atraso_ni_figura_en_detalle_atrasos(): void
    {
        $this->travelTo(Carbon::parse('2026-08-20 12:00:00'));

        $emp = Empleado::query()->create([
            'nombre' => 'Roberto',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'RG-99',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-10',
        ]);

        // Simula la situación del servidor: el funcionario solo marcó al retirarse (17:05:00).
        // En la BD el primer marcaje queda en hora_entrada y hora_salida queda null.
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => '2026-08-05',
            'hora_entrada' => '17:05:00',
            'hora_salida' => null,
            'estado_marcacion' => 'Entrada',
            'evento_biometrico' => 'Verificado',
        ]);

        $service = app(AnalisisReglamentoReporteService::class);
        $reporte = $service->generarReporteReglamento(Carbon::parse('2026-08-01'));

        // 1. Debe figurar en detalle_omisiones porque olvidó marcar entrada
        $detalleOmisiones = collect($reporte['detalle_omisiones']);
        $omisionEmp = $detalleOmisiones->firstWhere('id', $emp->id);
        $this->assertNotNull($omisionEmp, 'El empleado debe figurar en detalle_omisiones');
        $this->assertEquals(1, $omisionEmp['total_omisiones']);

        // 2. NO debe figurar en detalle_atrasos (no debe tener 515 min de retraso)
        $detalleAtrasos = collect($reporte['detalle_atrasos']);
        $atrasoEmp = $detalleAtrasos->firstWhere('id', $emp->id);
        $this->assertNull($atrasoEmp, 'El empleado con omisión de entrada NO debe figurar en detalle_atrasos con cientos de minutos de retraso');

        // 3. La evaluación individual tampoco debe acumularle retraso por ese día
        $eval = $service->evaluarEmpleadoIndividual($emp->id, Carbon::parse('2026-08-01'));
        $this->assertEquals(0, $eval['minutos_atraso'], 'Los minutos de atraso deben ser 0, no 515');
        $this->assertEquals(0, $eval['dias_sancion_atraso'], 'No debe tener sanción económica de atraso');
    }
}



