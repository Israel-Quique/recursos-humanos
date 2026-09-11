<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RegistroSucursalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sucursales_vista_loads_successfully(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->assertStatus(200)
            ->assertSee('Registro mensual de sucursales')
            ->assertSee('Buscador de sucursal')
            ->assertSee('Por Mes')
            ->assertSee('Por Día');
    }

    public function test_sucursales_vista_filters_by_sucursal(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $empLaPaz = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
            'codigo_biometrico' => '1001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(3)->toDateString(),
            'created_by' => $user->id,
        ]);

        $empCbba = Empleado::query()->create([
            'nombre' => 'Rodrigo',
            'apellido' => 'Vargas',
            'codigo_biometrico' => '2001',
            'area' => 'Comercial',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(3)->toDateString(),
            'created_by' => $user->id,
        ]);

        $hoy = now()->toDateString();

        RegistroAsistencia::query()->create([
            'empleado_id' => $empLaPaz->id,
            'fecha' => $hoy,
            'hora_entrada' => '08:25:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empCbba->id,
            'fecha' => $hoy,
            'hora_entrada' => '08:45:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // Filtrar por La Paz
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->set('sucursalesPeriodoTipo', 'dia')
            ->set('sucursalesFechaDia', $hoy)
            ->set('sucursalesSucursal', 'La Paz')
            ->call('aplicarFiltroSucursales')
            ->assertSee('Carlos Mamani')
            ->assertDontSee('Rodrigo Vargas');

        // Filtrar por Cochabamba
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->set('sucursalesPeriodoTipo', 'dia')
            ->set('sucursalesFechaDia', $hoy)
            ->set('sucursalesSucursal', 'Cochabamba')
            ->call('aplicarFiltroSucursales')
            ->assertSee('Rodrigo Vargas')
            ->assertDontSee('Carlos Mamani');
    }

    public function test_sucursales_vista_toggles_between_dia_and_mes(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $emp = Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Flores',
            'codigo_biometrico' => '3001',
            'area' => 'Logística',
            'sucursal' => 'Santa Cruz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(4)->toDateString(),
            'created_by' => $user->id,
        ]);

        $ayer = now()->subDay()->toDateString();

        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => $ayer,
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // En modo mes actual, se ve el registro de ayer
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->call('setSucursalesPeriodoTipo', 'mes')
            ->set('sucursalesMes', now()->format('Y-m'))
            ->call('aplicarFiltroSucursales')
            ->assertSee('Ana Flores');

        // En modo día pero con fecha de hoy, no se ve el de ayer
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->call('setSucursalesPeriodoTipo', 'dia')
            ->set('sucursalesFechaDia', now()->toDateString())
            ->call('aplicarFiltroSucursales')
            ->assertDontSee('Ana Flores');
    }

    public function test_sucursales_descarga_excel_y_pdf(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $emp = Empleado::query()->create([
            'nombre' => 'Lucia',
            'apellido' => 'Torres',
            'codigo_biometrico' => '4001',
            'area' => 'Administración',
            'sucursal' => 'Tarija',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(2)->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $emp->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:20:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // Probar descarga Excel
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->call('descargarExcelSucursales')
            ->assertFileDownloaded();

        // Probar descarga PDF
        Livewire::test('personal-page', ['vista' => 'sucursales'])
            ->call('descargarPdfSucursales')
            ->assertFileDownloaded();
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_sucursales@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
