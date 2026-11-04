<?php

namespace Tests\Feature;

use App\Livewire\FechasEspecialesPage;
use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use App\Services\PlanillaRefrigerioService;
use App\Services\ProgramacionLaboralService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalidaAnticipadaTest extends TestCase
{
    use RefreshDatabase;

    private Empleado $empleado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-04 12:00:00'));
        $user = User::query()->create([
            'name' => 'RRHH', 'email' => 'salidas@example.test', 'password' => 'secret123',
        ]);
        $user->givePermissionTo(Permission::findOrCreate('gestionar personal', 'web'));
        $this->actingAs($user);
        HorarioRegional::query()->create([
            'sucursal' => 'Santa Cruz', 'hora_entrada' => '08:30:00', 'hora_salida' => '18:00:00',
        ]);
        $this->empleado = Empleado::query()->create([
            'nombre' => 'Ana', 'apellido' => 'Perez', 'codigo_biometrico' => 'SAL-01',
            'area' => 'Personal', 'sucursal' => 'Santa Cruz', 'fecha_contratacion' => '2026-01-01',
            'hora_entrada_programada' => '08:30:00', 'hora_salida_programada' => '16:30:00',
        ]);
    }

    public function test_salida_anticipada_cuenta_una_omision_y_conserva_hora_en_todas_las_vistas(): void
    {
        $registro = $this->registrar('17:59:00');
        $page = $this->buscar()
            ->assertSet('marcacionesStats.total_omisiones', 1)
            ->assertSet('marcacionesStats.lista_omisiones.0.salida', '17:59')
            ->assertSet('marcacionesStats.lista_omisiones.0.tipo_omision', 'Salida anticipada')
            ->assertSee('Omisión (Salida anticipada)')
            ->call('openModalOmisiones')->assertSee('17:59');
        $page->call('descargarPdfMarcaciones', 'personal')->assertFileDownloaded();
        $component = $page->instance();
        $component->appliedSucursalesPeriodoTipo = 'dia';
        $component->appliedSucursalesFechaDia = '2026-10-29';
        $sucursales = $component->obtenerColeccionMarcacionesSucursales();
        $this->assertSame(1, $sucursales['stats']['total_omisiones']);
        $this->assertSame('Salida anticipada', $sucursales['allRows']->first()->omision);
        $method = new \ReflectionMethod($component, 'obtenerColeccionMarcacionesReporte');
        $reporte = $method->invoke($component, false);
        $html = view('pdf.marcaciones-personal', [
            'periodoLabel' => 'Octubre 2026', 'modoReporte' => 'personal',
            'empleadoInfo' => $component->marcacionesEmpleadoInfo,
            'stats' => $component->marcacionesStats, 'registros' => $reporte['rows'],
        ])->render();
        $this->assertStringContainsString('Salida anticipada</span>', $html);
        $this->assertStringContainsString('17:59', $html);
        $page->call('setFilterEstadoMarcaciones', 'faltante')
            ->assertViewHas('registros', fn ($rows) => $rows->total() === 1);
        $page->call('setFilterEstadoMarcaciones', 'completo')
            ->assertViewHas('registros', fn ($rows) => $rows->total() === 0);

        $analysis = app(AnalisisAsistenciaService::class);
        $report = $analysis->reportePersonalizado($this->empleado->id, Carbon::parse('2026-10-29'), Carbon::parse('2026-10-29')->endOfDay());
        $this->assertTrue($report['rows'][0]['es_omision']);
        $this->assertFalse($report['rows'][0]['es_falta']);
        $this->assertSame('17:59', $report['rows'][0]['salida']);
        $this->assertSame('Omisión (Salida anticipada)', $report['rows'][0]['estado']);
        $monthly = $analysis->detalleMensualPorEmpleado($this->empleado->id, Carbon::parse('2026-10-01'));
        $this->assertSame(1, $monthly['total_omisiones']);
        $this->assertSame('17:59', $monthly['omisiones'][0]['salida']);
        $global = $analysis->reporteMensualNoMarcadosYAtrasos(Carbon::parse('2026-10-01'));
        $this->assertSame(1, $global['total_omisiones']);
        $this->assertSame('Omisión (Salida anticipada)', $global['omisiones'][0]['detalle']);
        $calendar = $analysis->detalleCalendarioDia('2026-10-29');
        $this->assertCount(1, $calendar['omisiones']);
        $this->assertSame('Omisión (Salida anticipada)', $calendar['marcaciones'][0]['estado']);
        $this->assertSame('os', $this->planilla()['dias']['2026-10-29']);
        $this->assertSame('17:59:00', $registro->fresh()->hora_salida);
    }

    public function test_salir_a_la_hora_exacta_o_despues_y_entrar_antes_no_genera_omision(): void
    {
        $service = app(ProgramacionLaboralService::class);
        $horario = $service->resolverHorario($this->empleado, '2026-10-29');
        $this->assertTrue($service->esSalidaAnticipada('17:59:59', $horario));
        $this->assertFalse($service->esSalidaAnticipada('18:00', $horario));
        $this->assertFalse($service->esSalidaAnticipada('18:00:01', $horario));
        $this->registrar('18:00:00', '07:30:00');
        $this->buscar()->assertSet('marcacionesStats.total_omisiones', 0)->assertSee('Completo');
        $this->assertSame('a', $this->planilla()['dias']['2026-10-29']);
    }

    public function test_horario_especial_de_sucursal_tiene_prioridad_y_no_penaliza_salida_autorizada(): void
    {
        FechaEspecialLaboral::query()->create([
            'fecha' => '2026-10-29', 'sucursal' => 'TODAS', 'nombre' => 'Jornada global',
            'tipo' => 'horario_especial', 'hora_salida' => '16:00:00',
        ]);
        FechaEspecialLaboral::query()->create([
            'fecha' => '2026-10-29', 'sucursal' => 'Santa Cruz', 'nombre' => 'Jornada regional',
            'tipo' => 'horario_especial', 'hora_salida' => '14:00:00',
        ]);
        $registro = $this->registrar('13:59:00');
        $service = app(ProgramacionLaboralService::class);
        $horario = $service->resolverHorario($this->empleado, '2026-10-29');
        $this->assertSame('14:00:00', $horario['hora_salida']);
        $this->assertTrue($service->esSalidaAnticipada('13:59:00', $horario));
        $this->assertFalse($service->esSalidaAnticipada('14:00:00', $horario));
        $this->buscar()->assertSet('marcacionesStats.total_omisiones', 1);
        $registro->update(['hora_salida' => '14:00:00']);
        $this->buscar()->assertSet('marcacionesStats.total_omisiones', 0);
        $this->assertSame('a', $this->planilla()['dias']['2026-10-29']);
    }

    public function test_feriado_y_paro_no_generan_omision_por_salir_antes(): void
    {
        foreach (['2026-10-28' => 'feriado', '2026-10-29' => 'paro'] as $fecha => $tipo) {
            FechaEspecialLaboral::query()->create([
                'fecha' => $fecha, 'sucursal' => 'TODAS', 'nombre' => $tipo, 'tipo' => $tipo,
            ]);
            $this->assertFalse(app(ProgramacionLaboralService::class)->esSalidaAnticipada(
                '10:00:00', app(ProgramacionLaboralService::class)->resolverHorario($this->empleado, $fecha)
            ));
        }
        $this->registrar('10:00:00');
        $this->buscar()->assertSet('marcacionesStats.total_omisiones', 0)
            ->assertSet('marcacionesStats.total_faltas', 0)->assertSee('Feriado trabajado');
    }

    public function test_permiso_aprobado_justifica_salida_anticipada_y_pendiente_no(): void
    {
        $this->registrar('17:00:00');
        $permiso = PermisoLaboral::query()->create([
            'empleado_id' => $this->empleado->id, 'tipo' => 'permiso', 'alcance' => 'salida_temprana',
            'fecha_inicio' => '2026-10-29', 'fecha_fin' => '2026-10-29',
            'hora_inicio' => '17:00:00', 'hora_fin' => '18:00:00', 'estado' => 'pendiente',
        ]);
        $page = $this->buscar()->assertSet('marcacionesStats.total_omisiones', 1);
        $permiso->update(['estado' => 'aprobado']);
        $page->call('openModalOmisiones')->assertSet('marcacionesStats.total_omisiones', 0)
            ->assertSee('Permiso Autorizado');
        $report = app(AnalisisAsistenciaService::class)->reportePersonalizado(
            $this->empleado->id, Carbon::parse('2026-10-29'), Carbon::parse('2026-10-29')->endOfDay()
        );
        $this->assertFalse($report['rows'][0]['es_omision']);
        $this->assertTrue($report['rows'][0]['omision_justificada']);
        $this->assertSame('17:00', $report['rows'][0]['salida']);
    }

    public function test_formulario_valida_horas_y_permite_definir_solo_salida_especial(): void
    {
        $page = Livewire::test(FechasEspecialesPage::class)
            ->set('fecha', '2026-10-29')->set('tipo', 'horario_especial')
            ->set('sucursal', 'Santa Cruz')->set('nombre', 'Salida autorizada');
        $page->call('saveFechaEspecial')->assertHasErrors(['horaSalida']);
        $page->set('horaEntrada', '14:00')->set('horaSalida', '13:00')
            ->call('saveFechaEspecial')->assertHasErrors(['horaSalida']);
        $page->set('horaEntrada', '')->set('horaSalida', '14:00')
            ->call('saveFechaEspecial')->assertHasNoErrors();
        $this->assertDatabaseHas('fechas_especiales_laborales', [
            'fecha' => '2026-10-29 00:00:00', 'sucursal' => 'Santa Cruz', 'hora_salida' => '14:00:00',
        ]);
    }

    private function registrar(string $salida, string $entrada = '08:30:00'): RegistroAsistencia
    {
        return RegistroAsistencia::query()->create([
            'empleado_id' => $this->empleado->id, 'fecha' => '2026-10-29',
            'hora_entrada' => $entrada, 'hora_salida' => $salida, 'estado_marcacion' => 'Normal',
        ]);
    }

    private function buscar()
    {
        return Livewire::test('personal-page', ['vista' => 'marcaciones'])
            ->set('inputMarcacionesSearch', 'SAL-01')->set('inputMarcacionesTipoFecha', 'rango')
            ->set('inputMarcacionesFechaInicio', '2026-10-29')->set('inputMarcacionesFechaFin', '2026-10-29')
            ->call('aplicarBusquedaMarcaciones');
    }

    private function planilla(): array
    {
        $result = app(PlanillaRefrigerioService::class)->calcularPlanilla(Carbon::parse('2026-10-01'), 'Santa Cruz');

        return collect($result['items'])->firstWhere('empleado_id', $this->empleado->id);
    }
}
