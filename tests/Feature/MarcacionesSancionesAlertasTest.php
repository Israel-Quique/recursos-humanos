<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\ReglaSancionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MarcacionesSancionesAlertasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReglaSancionSeeder::class);
        Carbon::setTestNow('2026-06-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $user->givePermissionTo($permission);

        return $user;
    }

    public function test_control_view_shows_cumulative_bar_and_sanctions(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        // Crear horario regional con 30 min de tolerancia
        HorarioRegional::query()->create([
            'sucursal' => 'La Paz',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tolerancia_minutos' => 5,
            'tolerancia_mensual_minutos' => 30,
            'es_vigente' => true,
        ]);

        // Empleado con 70 min de atraso (Tramo 61-90 min: Acumulativo 2, 2 días de haber)
        $empleadoAcum2 = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Sánchez',
            'codigo_biometrico' => '1001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
            'created_by' => $user->id,
        ]);

        // Empleado puntual
        $empleadoPuntual = Empleado::query()->create([
            'nombre' => 'Beatriz',
            'apellido' => 'Morales',
            'codigo_biometrico' => '1002',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
            'created_by' => $user->id,
        ]);

        $lunes1 = Carbon::parse('2026-06-01'); // Lunes
        $lunes2 = Carbon::parse('2026-06-08'); // Lunes

        // Carlos llega tarde:
        // Entrada programada 08:30 + 5m tolerancia = 08:35. Llega a las 09:10 (35m retraso)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleadoAcum2->id,
            'fecha' => $lunes1->toDateString(),
            'hora_entrada' => '09:10:00',
            'hora_salida' => '16:30:00',
        ]);
        // Segundo día: llega a las 09:10 (otros 35m retraso) => Total 70 min (Acumulativo 2)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleadoAcum2->id,
            'fecha' => $lunes2->toDateString(),
            'hora_entrada' => '09:10:00',
            'hora_salida' => '16:30:00',
        ]);

        // Beatriz llega puntual
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleadoPuntual->id,
            'fecha' => $lunes1->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page', ['vista' => 'control'])
            ->call('seleccionarSucursal', 'La Paz')
            ->assertSee('Carlos Sánchez')
            ->assertSee('Acumulativo 2')
            ->assertSee('Dos (2) días')
            ->assertSee('Beatriz Morales')
            ->assertSee('Puntual • Sin retraso');
    }

    public function test_modal_sancionados_2_dias_can_be_opened_and_filtered(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        HorarioRegional::query()->create([
            'sucursal' => 'Cochabamba',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tolerancia_minutos' => 5,
            'tolerancia_mensual_minutos' => 30,
            'es_vigente' => true,
        ]);

        // Empleado con 70 min retraso (Acumulativo 2)
        $emp1 = Empleado::query()->create([
            'nombre' => 'Roberto',
            'apellido' => 'Flores',
            'codigo_biometrico' => '2001',
            'area' => 'Operaciones',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
            'created_by' => $user->id,
        ]);

        // Empleado con 40 min retraso (Sanción 0.5 días, no Acumulativo 2)
        $emp2 = Empleado::query()->create([
            'nombre' => 'Elena',
            'apellido' => 'Gutiérrez',
            'codigo_biometrico' => '2002',
            'area' => 'Operaciones',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
            'created_by' => $user->id,
        ]);

        $fecha = Carbon::parse('2026-06-01');

        // Roberto: 70 min retraso
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp1->id,
            'fecha' => $fecha->toDateString(),
            'hora_entrada' => '09:45:00', // 08:35 + 70 min
            'hora_salida' => '16:30:00',
        ]);

        // Elena: 40 min retraso
        RegistroAsistencia::query()->create([
            'empleado_id' => $emp2->id,
            'fecha' => $fecha->toDateString(),
            'hora_entrada' => '09:15:00', // 08:35 + 40 min
            'hora_salida' => '16:30:00',
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page', ['vista' => 'control'])
            ->call('seleccionarSucursal', 'Cochabamba')
            ->call('openModalSancionados2Dias', 'acumulativo_2')
            ->assertSet('showModalSancionados2Dias', true)
            ->assertSet('filtroModalSancion', 'acumulativo_2')
            ->assertSee('Personal con Sanción por Tolerancia Excedida')
            ->assertSee('Roberto Flores')
            ->assertViewHas('totalSancionadosAcumulativo2', 1)
            ->assertViewHas('totalSancionadosGeneral', 2)
            ->assertViewHas('empleadosSancionadosModal', function ($list) {
                return $list->count() === 1 && $list->first()->nombre === 'Roberto';
            })
            // Cambiar filtro a todos los sancionados
            ->call('setFiltroModalSancion', 'todos')
            ->assertViewHas('empleadosSancionadosModal', function ($list) {
                return $list->count() === 2;
            })
            ->call('closeModalSancionados2Dias')
            ->assertSet('showModalSancionados2Dias', false);
    }

    public function test_marcaciones_individual_view_displays_tolerance_bar_and_sanction(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Javier',
            'apellido' => 'Mamani',
            'codigo_biometrico' => '3001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
            'created_by' => $user->id,
        ]);

        // Retraso de 75 min en junio
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-06-01',
            'hora_entrada' => '09:50:00', // 08:35 + 75 min
            'hora_salida' => '16:30:00',
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page', ['vista' => 'marcaciones'])
            ->set('inputMarcacionesSearch', '3001')
            ->set('inputMarcacionesTipoFecha', 'mes')
            ->set('inputMarcacionesMes', '2026-06')
            ->call('aplicarBusquedaMarcaciones')
            ->assertSee('Javier Mamani')
            ->assertSee('Control de Tolerancia Mensual y Sanciones Económicas')
            ->assertSee('ALCANZÓ ACUMULATIVO 2')
            ->assertSee('Dos (2) días');
    }
}
