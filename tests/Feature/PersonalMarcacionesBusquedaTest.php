<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PersonalMarcacionesBusquedaTest extends TestCase
{
    use RefreshDatabase;

    public function test_marcaciones_search_isolates_records_and_shows_correct_laboral_status(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        // Empleado 1: Omar 1 - Inactivo (dado de baja / fecha de despido hace un año)
        $omarInactivo = Empleado::query()->create([
            'nombre' => 'Omar',
            'apellido' => 'Quispe Condori',
            'codigo_biometrico' => '29',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subYears(2)->toDateString(),
            'fecha_despido' => now()->subYear()->toDateString(),
            'created_by' => $user->id,
        ]);

        // Empleado 2: Omar 2 - Activo con marcación reciente
        $omarActivo = Empleado::query()->create([
            'nombre' => 'Omar',
            'apellido' => 'Parra Grajeda',
            'codigo_biometrico' => '9623271',
            'area' => 'Personal',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(6)->toDateString(),
            'created_by' => $user->id,
        ]);

        // Registro de asistencia para Omar Inactivo
        RegistroAsistencia::query()->create([
            'empleado_id' => $omarInactivo->id,
            'fecha' => now()->subYear()->toDateString(),
            'hora_entrada' => '08:15:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // Registro de asistencia para Omar Activo
        RegistroAsistencia::query()->create([
            'empleado_id' => $omarActivo->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:25:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        // 1. Al buscar "Omar", debe detectar 2 coincidencias y seleccionar al activo prioritariamente
        $test = Livewire::test('personal-page')
            ->set('vista', 'marcaciones')
            ->set('inputMarcacionesSearch', 'Omar')
            ->call('aplicarBusquedaMarcaciones');

        $test->assertSet('selectedMarcacionesEmpleadoId', $omarActivo->id)
            ->assertSee('Omar Parra Grajeda')
            ->assertSee('9623271')
            ->assertSee('Activo')
            ->assertSeeHtml('08:25')
            ->assertDontSeeHtml('08:15'); // No debe mezclar la marcación de Omar Quispe

        // 2. Al seleccionar a Omar Inactivo (Quispe Condori)
        $test->call('seleccionarEmpleadoMarcaciones', $omarInactivo->id)
            ->assertSet('selectedMarcacionesEmpleadoId', $omarInactivo->id)
            ->assertSee('Omar Quispe Condori')
            ->assertSee('29')
            ->assertSee('Inactivo')
            ->assertSeeHtml('08:15')
            ->assertDontSeeHtml('08:25'); // No debe mostrar la marcación del otro Omar
    }

    public function test_inactive_employee_without_despido_but_no_recent_records_shows_as_inactivo(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        // Empleado sin fecha_despido pero con última marcación hace más de 6 meses
        $empleado = Empleado::query()->create([
            'nombre' => 'Omar',
            'apellido' => 'Antiguo',
            'codigo_biometrico' => '8441338',
            'area' => 'Personal',
            'sucursal' => 'Santa Cruz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subYears(2)->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->subMonths(6)->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('vista', 'marcaciones')
            ->set('inputMarcacionesSearch', '8441338')
            ->call('aplicarBusquedaMarcaciones')
            ->assertSet('selectedMarcacionesEmpleadoId', $empleado->id)
            ->assertSee('Omar Antiguo')
            ->assertSee('Inactivo')
            ->assertSee('INACTIVO / DADO DE BAJA');
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh_marcaciones@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
