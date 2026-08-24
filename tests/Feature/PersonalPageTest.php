<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PersonalPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_page_supports_search_on_sqlite(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        Empleado::query()->create([
            'nombre' => 'Marco',
            'apellido' => 'Rojas',
            'codigo_biometrico' => 'MARC-001',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Empleado::query()->create([
            'nombre' => 'Lucia',
            'apellido' => 'Fernandez',
            'codigo_biometrico' => 'LUC-002',
            'area' => 'Personal',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('search', 'marc')
            ->assertSee('Marco')
            ->assertDontSee('Lucia');
    }

    public function test_personal_page_groups_la_paz_aliases_under_one_filter(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Quispe',
            'codigo_biometrico' => 'LP-001',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Empleado::query()->create([
            'nombre' => 'Bruno',
            'apellido' => 'Mamani',
            'codigo_biometrico' => 'LP-002',
            'area' => 'Personal',
            'sucursal' => 'LaPaz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('sucursalFiltro', 'La Paz')
            ->assertSee('Ana')
            ->assertSee('Bruno')
            ->assertSet('sucursalFiltro', 'La Paz');
    }

    public function test_personal_page_does_not_count_today_open_shift_as_forgotten_exit(): void
    {
        $this->travelTo(Carbon::parse('2026-08-10 10:00:00'));

        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Yurguen',
            'apellido' => 'Terrazas',
            'codigo_biometrico' => '9066508',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:28:00',
            'hora_salida' => null,
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Entrada',
            'evento_biometrico' => 'Sin evento',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->call('openDetailModal', $empleado->id)
            ->assertSet('detailEmpleado.estado_hoy', 'En su puesto')
            ->assertSet('detailEmpleado.olvidos_marcacion', 0)
            ->assertSee('En su puesto');
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
