<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MarcacionesTotalesConsistentesTest extends TestCase
{
    use RefreshDatabase;

    private Empleado $empleado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-04 12:00:00'));
        $user = User::query()->create([
            'name' => 'RRHH', 'email' => 'totales@example.test', 'password' => 'secret123',
        ]);
        $user->givePermissionTo(Permission::findOrCreate('gestionar personal', 'web'));
        $this->actingAs($user);
        $this->empleado = Empleado::query()->create([
            'nombre' => 'Joeldaniel', 'apellido' => 'Leiguez Villa',
            'codigo_biometrico' => '8237795', 'sucursal' => 'Santa Cruz', 'area' => 'Personal',
            'hora_entrada_programada' => '08:30:00', 'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-07-20',
        ]);
    }

    public function test_actualiza_octava_omision_y_cuenta_registros_vacios_como_faltas(): void
    {
        foreach ([1, 2, 5, 6, 7, 8, 9] as $dia) {
            $this->registrar($dia, '08:30:00', null);
        }
        foreach ([12, 13, 14, 15, 16] as $dia) {
            $this->registrar($dia, null, null);
        }
        foreach ([22, 23, 26, 27, 28, 29] as $dia) {
            $this->registrar($dia, '08:30:00', '16:30:00');
        }

        $page = $this->buscarOctubre()
            ->assertSet('marcacionesStats.total_omisiones', 7)
            ->assertSet('marcacionesStats.total_faltas', 8);

        RegistroAsistencia::query()->where('empleado_id', $this->empleado->id)
            ->whereDate('fecha', '2026-10-29')->update(['hora_salida' => null]);

        $page->call('openModalOmisiones')
            ->assertSet('marcacionesStats.total_omisiones', 8)
            ->assertSet('marcacionesStats.total_faltas', 8)
            ->assertSet('marcacionesStats.lista_omisiones.7.fecha', '29/10/2026')
            ->assertSee('Detalle de Omisiones')
            ->assertSee('8 registros');
    }

    public function test_tabla_identifica_permiso_que_excluye_una_omision_del_resumen(): void
    {
        $this->registrar(29, '08:52:00', null);
        PermisoLaboral::query()->create([
            'empleado_id' => $this->empleado->id, 'tipo' => 'permiso',
            'alcance' => 'salida_temprana', 'estado' => 'aprobado',
            'fecha_inicio' => '2026-10-29', 'fecha_fin' => '2026-10-29',
            'motivo' => 'Salida autorizada',
        ]);

        $this->buscarOctubre()
            ->assertSet('marcacionesStats.total_omisiones', 0)
            ->assertSee('Permiso Autorizado')
            ->assertViewHas('registros', fn ($rows) => $rows->first()->estado_marcacion === 'Permiso Autorizado');
    }

    public function test_sin_filtro_de_fechas_resumen_incluye_registros_de_octubre(): void
    {
        $this->registrar(29, '08:52:00', null);

        Livewire::test('personal-page', ['vista' => 'marcaciones'])
            ->set('inputMarcacionesSearch', '8237795')
            ->set('inputMarcacionesTipoFecha', 'rango')
            ->set('inputMarcacionesFechaInicio', '')
            ->set('inputMarcacionesFechaFin', '')
            ->call('aplicarBusquedaMarcaciones')
            ->assertSet('marcacionesStats.total_omisiones', 1)
            ->assertSet('marcacionesStats.lista_omisiones.0.fecha', '29/10/2026')
            ->assertSee('29/10/2026');
    }

    private function registrar(int $dia, ?string $entrada, ?string $salida): void
    {
        RegistroAsistencia::query()->create([
            'empleado_id' => $this->empleado->id,
            'fecha' => sprintf('2026-10-%02d', $dia),
            'hora_entrada' => $entrada, 'hora_salida' => $salida,
            'estado_marcacion' => 'Normal',
        ]);
    }

    private function buscarOctubre()
    {
        return Livewire::test('personal-page', ['vista' => 'marcaciones'])
            ->set('inputMarcacionesSearch', '8237795')
            ->set('inputMarcacionesTipoFecha', 'rango')
            ->set('inputMarcacionesFechaInicio', '2026-10-01')
            ->set('inputMarcacionesFechaFin', '2026-10-29')
            ->call('aplicarBusquedaMarcaciones');
    }
}
