<?php

namespace Tests\Feature;

use App\Livewire\ImportarExcelPage;
use App\Livewire\PersonalPage;
use App\Livewire\ReportesPage;
use App\Models\BiometricoDispositivo;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use App\Services\ConexionBiometricoService;
use App\Services\ExtraccionBiometricoZkService;
use App\Services\ImportacionBiometricaService;
use App\Services\SincronizacionBiometricoService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PandoVinculacionReportesTest extends TestCase
{
    use RefreshDatabase;

    private function empleado(string $sucursal, string $codigo, string $nombre = 'Persona'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => $nombre, 'apellido' => 'Prueba', 'codigo_biometrico' => $codigo,
            'sucursal' => $sucursal, 'area' => 'Operaciones',
            'hora_entrada_programada' => '08:30:00', 'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
        ]);
    }

    private function marcas(string $fecha = '2026-08-05'): array
    {
        return [
            ['codigo' => '1', 'nombre' => 'Persona', 'apellido' => 'Prueba', 'fecha_hora' => $fecha.'T08:30:00', 'punch' => '0'],
            ['codigo' => '1', 'nombre' => 'Persona', 'apellido' => 'Prueba', 'fecha_hora' => $fecha.'T16:30:00', 'punch' => '1'],
        ];
    }

    public function test_primera_sincronizacion_vincula_historial_a_pando_y_aparece_en_reportes(): void
    {
        $this->travelTo(Carbon::parse('2026-10-19 12:00:00'));
        $pando = $this->empleado(' PANDO ', '1053');
        $oruro = $this->empleado('Oruro', '1', 'Otro');
        $device = BiometricoDispositivo::query()->create([
            'department' => 'Pando', 'branch' => 'Sucursal Pando',
            'ip' => '172.65.26.43', 'port' => 4370, 'is_active' => true,
        ]);
        $this->mock(ConexionBiometricoService::class, function ($mock) {
            $mock->shouldReceive('verificarConexion')->once()->andReturn(['connected' => true]);
        });
        $this->partialMock(ExtraccionBiometricoZkService::class, function ($mock) {
            $mock->shouldReceive('extraerMarcaciones')->once()->andReturn($this->marcas());
        });

        $result = app(SincronizacionBiometricoService::class)->sincronizarDispositivo($device->toArray());

        $this->assertSame('sincronizado', $result['status'], $result['message']);
        $this->assertSame(1, $result['created']);
        $this->assertDatabaseCount('empleados', 2);
        $registro = RegistroAsistencia::query()->where('empleado_id', $pando->id)->whereDate('fecha', '2026-08-05')->firstOrFail();
        $this->assertSame('16:30:00', $registro->hora_salida);
        $this->assertSame(0, $oruro->asistencias()->count());
        $this->assertSame('1053', $pando->fresh()->codigo_biometrico);
        $this->assertSame('2026-08-05', $device->fresh()->last_synced_mark_at->toDateString());

        foreach (['Pando', 'Sucursal Pando', 'Cobija', 'Regional Cobija'] as $branch) {
            $report = app(AnalisisAsistenciaService::class)->reporteConsolidadoPorSucursal(Carbon::parse('2026-08-01'), $branch);
            $this->assertSame(1, $report['total_empleados']);
            $this->assertSame($pando->id, $report['sucursales']['Pando']['empleados'][0]['id']);
        }

        $page = new PersonalPage();
        $page->appliedSucursalesMes = '2026-08';
        $page->appliedSucursalesSucursal = 'Sucursal Pando';
        $this->assertCount(1, $page->obtenerColeccionMarcacionesSucursales(false)['allRows']);

        // Reprocesar el historial no duplica trabajadores ni dias de asistencia.
        app(ImportacionBiometricaService::class)->importarMarcacionesBiometrico($device->toArray(), $this->marcas());
        $this->assertDatabaseCount('registros_asistencia', 1);
        $this->assertDatabaseCount('empleados', 2);
    }

    public function test_filtro_no_mezcla_otra_regional_y_no_depende_de_mayusculas_o_espacios(): void
    {
        $pando = $this->empleado('  SUCURSAL PANDO  ', 'PA-1');
        $this->empleado('Oruro', 'OR-1');
        $this->assertSame([$pando->id], SucursalNormalizer::applyFilter(Empleado::query(), 'sucursal', 'Cobija')->pluck('id')->all());
    }

    public function test_usuarios_nuevos_del_reloj_se_registran_en_pando_aunque_el_codigo_exista_en_oruro(): void
    {
        $oruro = $this->empleado('Oruro', '1', 'Otro');
        $device = ['department' => 'Pando', 'branch' => 'Sucursal Pando', 'ip' => '172.65.26.43'];
        $rows = [...$this->marcas(), ...array_map(fn ($row) => array_replace($row, ['codigo' => '2', 'nombre' => 'Segunda']), $this->marcas())];
        $service = app(ImportacionBiometricaService::class);
        $first = $service->importarMarcacionesBiometrico($device, $rows);
        $second = $service->importarMarcacionesBiometrico($device, $rows);
        $this->assertSame(2, $first->resumen_json['empleados_creados']);
        $this->assertSame(0, $second->resumen_json['empleados_creados']);
        $this->assertSame(0, $oruro->asistencias()->count());
        $this->assertDatabaseCount('empleados', 3);
        $this->assertDatabaseCount('registros_asistencia', 2);
        $this->assertDatabaseHas('empleados', ['codigo_biometrico' => 'pando:1', 'sucursal' => 'Pando']);
    }

    public function test_reporte_historico_conserva_al_trabajador_que_ya_no_esta_activo_hoy(): void
    {
        $this->travelTo(Carbon::parse('2026-10-19 12:00:00'));
        $pando = $this->empleado('Pando', '1053');
        RegistroAsistencia::query()->create([
            'empleado_id' => $pando->id, 'fecha' => '2026-08-05',
            'hora_entrada' => '08:30:00', 'hora_salida' => '16:30:00',
        ]);
        $this->assertFalse($pando->estaActivoLaboralmente(now()));
        $report = app(AnalisisAsistenciaService::class)->detalleCalendarioDia(Carbon::parse('2026-08-06'));
        $this->assertSame([$pando->id], array_column($report['faltas'], 'empleado_id'));
    }

    public function test_reportes_se_actualizan_despues_de_importar_sin_esperar_la_cache(): void
    {
        $this->travelTo(Carbon::parse('2026-10-19 12:00:00'));
        $this->empleado('Pando', '1');
        $page = new ReportesPage();
        $page->referenceMonth = '2026-10';
        $page->selectedBranch = 'Pando';
        $this->assertSame(0, $page->render()->getData()['reporteSucursales']['total_empleados']);
        $before = Cache::get('reportes:version');
        app(ImportacionBiometricaService::class)->importarMarcacionesBiometrico([
            'department' => 'Pando', 'branch' => 'Sucursal Pando', 'ip' => '172.65.26.43',
        ], $this->marcas('2026-10-19'));
        $this->assertNotSame($before, Cache::get('reportes:version'));
        $this->assertSame(1, $page->render()->getData()['reporteSucursales']['total_empleados']);
    }

    public function test_sincronizacion_desde_el_sistema_usa_solo_el_equipo_elegido_y_todo_el_historial(): void
    {
        $user = User::query()->create(['name' => 'Admin', 'email' => 'historial@example.test', 'password' => bcrypt('test')]);
        $user->givePermissionTo(Permission::findOrCreate('importar biometria', 'web'));
        $device = ['id' => 1, 'department' => 'Pando', 'branch' => 'Sucursal Pando', 'ip' => '172.65.26.43', 'port' => 4370, 'connected' => true, 'last_sync' => 'Disponible'];
        $this->mock(ConexionBiometricoService::class, function ($mock) use ($device) {
            $mock->shouldReceive('dispositivosConfigurados')->andReturn([$device]);
            $mock->shouldReceive('estadoDispositivos')->andReturn([$device]);
        });
        $this->mock(SincronizacionBiometricoService::class, function ($mock) use ($device) {
            $mock->shouldReceive('sincronizarDispositivo')->once()->with($device, true)
                ->andReturn(['device' => 'Sucursal Pando', 'message' => 'Historial sincronizado']);
        });
        Livewire::actingAs($user)->test(ImportarExcelPage::class)
            ->call('sincronizarBiometrico', 0)->assertSee('Historial sincronizado');
    }
}
