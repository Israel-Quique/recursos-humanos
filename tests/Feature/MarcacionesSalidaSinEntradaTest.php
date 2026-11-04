<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use App\Services\ImportacionBiometricaService;
use App\Services\ProgramacionLaboralService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MarcacionesSalidaSinEntradaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-04 12:00:00'));
        $user = User::query()->create(['name' => 'RRHH', 'email' => 'salida-sin-entrada@example.test', 'password' => 'secret123']);
        $user->givePermissionTo(Permission::findOrCreate('gestionar personal', 'web'));
        $this->actingAs($user);
    }

    public function test_dos_marcas_despues_del_horario_son_omision_de_entrada_en_todas_las_sucursales(): void
    {
        foreach (['Santa Cruz' => '16:30:00', 'La Paz' => '17:00:00', 'Cochabamba' => '18:00:00'] as $sucursal => $fin) {
            $empleado = $this->empleado($sucursal, $fin);
            $salida = Carbon::createFromTimeString($fin)->addHour()->format('H:i:s');
            $registro = RegistroAsistencia::query()->create([
                'empleado_id' => $empleado->id, 'fecha' => '2026-10-29',
                'hora_entrada' => $fin, 'hora_salida' => $salida, 'estado_marcacion' => 'Entrada',
            ]);
            $analysis = app(AnalisisAsistenciaService::class);
            $normalizado = $analysis->normalizarMarcacionAsistencia($registro);
            $this->assertNull($normalizado['entrada']);
            $this->assertSame($salida, $normalizado['salida']);
            $reporte = $analysis->reportePersonalizado($empleado->id, Carbon::parse('2026-10-29'), Carbon::parse('2026-10-29')->endOfDay());
            $this->assertSame(0, $reporte['rows'][0]['retraso_minutos']);
            $this->assertSame('Omisión (Falta entrada)', $reporte['rows'][0]['estado']);
            $this->assertTrue($reporte['rows'][0]['es_omision']);
            Livewire::test('personal-page', ['vista' => 'marcaciones'])
                ->set('inputMarcacionesSearch', $empleado->codigo_biometrico)
                ->set('inputMarcacionesTipoFecha', 'rango')
                ->set('inputMarcacionesFechaInicio', '2026-10-29')->set('inputMarcacionesFechaFin', '2026-10-29')
                ->call('aplicarBusquedaMarcaciones')
                ->assertSet('marcacionesStats.total_omisiones', 1)
                ->assertSet('marcacionesStats.total_atrasos', 0)
                ->assertSet('marcacionesStats.minutos_atraso_totales', 0)
                ->assertSet('marcacionesStats.lista_omisiones.0.entrada', '--:--')
                ->assertSet('marcacionesStats.lista_omisiones.0.salida', substr($salida, 0, 5));
        }
    }

    public function test_marcas_unicas_repetidas_o_cercanas_a_la_salida_no_producen_atraso(): void
    {
        $empleado = $this->empleado('Santa Cruz', '16:30:00');
        $analysis = app(AnalisisAsistenciaService::class);
        foreach ([['16:30:00', null], ['17:00:00', null], ['17:00:00', '17:00:00'], ['17:00:00', '17:03:00']] as [$entrada, $salida]) {
            $registro = new RegistroAsistencia([
                'fecha' => '2026-10-29', 'hora_entrada' => $entrada, 'hora_salida' => $salida,
                'estado_marcacion' => 'Entrada',
            ]);
            $registro->setRelation('empleado', $empleado);
            $norm = $analysis->normalizarMarcacionAsistencia($registro);
            $this->assertNull($norm['entrada']);
            $this->assertSame($salida ?? $entrada, $norm['salida']);
            $this->assertSame(0, $analysis->calcularMinutosRetraso($norm['entrada'], '08:35:00'));
        }
    }

    public function test_importacion_y_reimportacion_corrigen_la_posicion_sin_perder_una_entrada_real(): void
    {
        $empleado = $this->empleado('Santa Cruz', '16:30:00');
        $registro = RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id, 'fecha' => '2026-10-29',
            'hora_entrada' => '17:00:00', 'hora_salida' => '18:09:03', 'estado_marcacion' => 'Entrada',
        ]);
        $device = ['department' => 'Santa Cruz', 'branch' => 'Sucursal Santa Cruz', 'ip' => '127.0.0.1'];
        $rows = [];
        foreach (['17:00:00', '18:09:03'] as $hora) {
            $rows[] = ['codigo' => $empleado->codigo_biometrico, 'nombre' => 'Ana', 'apellido' => 'Perez', 'fecha_hora' => '2026-10-29T'.$hora, 'punch' => '0'];
        }
        $importador = app(ImportacionBiometricaService::class);
        $resultado = $importador->importarMarcacionesBiometrico($device, $rows);
        $this->assertNull($registro->fresh()->hora_entrada);
        $this->assertSame('18:09:03', $registro->fresh()->hora_salida);
        $this->assertSame('Salida', $registro->fresh()->estado_marcacion);
        $this->assertSame(1, $resultado->resumen_json['olvidos_marcacion']);
        $importador->importarMarcacionesBiometrico($device, $rows);
        $this->assertSame(1, $empleado->asistencias()->count());
        $importador->importarMarcacionesBiometrico($device, [[
            'codigo' => $empleado->codigo_biometrico, 'nombre' => 'Ana', 'apellido' => 'Perez',
            'fecha_hora' => '2026-10-29T08:30:00', 'punch' => '0',
        ]]);
        $this->assertSame('08:30:00', $registro->fresh()->hora_entrada);
        $this->assertSame('18:09:03', $registro->fresh()->hora_salida);
        $resultado = $importador->importarMarcacionesBiometrico($device, $rows);
        $this->assertSame('08:30:00', $registro->fresh()->hora_entrada);
        $this->assertSame(0, $resultado->resumen_json['olvidos_marcacion']);
        $this->assertSame(1, $empleado->asistencias()->count());
    }

    public function test_usa_horario_especial_y_conserva_jornada_con_entrada_anterior_a_la_salida_programada(): void
    {
        $empleado = $this->empleado('Santa Cruz', '16:30:00');
        FechaEspecialLaboral::query()->create([
            'fecha' => '2026-10-29', 'sucursal' => 'Santa Cruz', 'nombre' => 'Jornada reducida',
            'tipo' => 'horario_especial', 'hora_salida' => '14:00:00',
        ]);
        $programacion = app(ProgramacionLaboralService::class);
        $especial = $programacion->resolverHorario($empleado, '2026-10-29');
        $this->assertSame([null, '14:15:00'], $programacion->normalizarHorasMarcadas('14:00:00', '14:15:00', $especial));
        $normal = $programacion->resolverHorario($empleado, '2026-10-28');
        $this->assertSame(['08:45:00', '17:00:00'], $programacion->normalizarHorasMarcadas('08:45:00', '17:00:00', $normal));
        $this->assertSame(['15:00:00', '17:00:00'], $programacion->normalizarHorasMarcadas('15:00:00', '17:00:00', $normal));
    }

    private function empleado(string $sucursal, string $fin): Empleado
    {
        HorarioRegional::query()->create(['sucursal' => $sucursal, 'hora_entrada' => '08:30:00', 'hora_salida' => $fin]);

        return Empleado::query()->create([
            'nombre' => 'Ana', 'apellido' => 'Perez', 'codigo_biometrico' => 'OM-'.Empleado::query()->count(),
            'area' => 'Personal', 'sucursal' => $sucursal, 'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => $fin, 'fecha_contratacion' => '2026-01-01',
        ]);
    }
}
