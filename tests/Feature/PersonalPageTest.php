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

    public function test_personal_page_descarga_excel_marcaciones_correctamente(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'CG-100',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('personal-page')
            ->set('vista', 'marcaciones')
            ->call('descargarExcelMarcaciones');

        $component->assertFileDownloaded();
    }

    public function test_personal_page_descarga_pdf_y_excel_control_correctamente(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Laura',
            'apellido' => 'Vargas',
            'codigo_biometrico' => 'LV-200',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('descargarPdfControl')
            ->assertFileDownloaded();

        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('descargarExcelControl')
            ->assertFileDownloaded();
    }

    public function test_control_vista_excluye_empleados_inactivos_de_las_metricas_y_listado(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        // Empleado ACTIVO
        $activo = Empleado::query()->create([
            'nombre' => 'Empleado',
            'apellido' => 'Activo',
            'codigo_biometrico' => 'ACT-001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $activo->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // Empleado ANTIGUO INACTIVO (sin despido formal, pero sin marcaciones desde hace 120 días)
        $inactivo = Empleado::query()->create([
            'nombre' => 'Empleado',
            'apellido' => 'AntiguoInactivo',
            'codigo_biometrico' => 'INA-999',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(6)->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $inactivo->id,
            'fecha' => now()->subDays(120)->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        // Verificar que en vista control sólo se liste y contabilice al activo
        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('seleccionarSucursal', 'La Paz')
            ->assertSee('Empleado Activo')
            ->assertDontSee('Empleado AntiguoInactivo')
            ->assertViewHas('sucursalKpis', function (array $kpis) {
                return ($kpis['total_empleados'] ?? 0) === 1;
            });
    }

    public function test_personal_page_can_create_edit_and_search_by_email(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        // 1. Crear personal con correo
        Livewire::test('personal-page')
            ->set('nombre', 'Carlos')
            ->set('apellido', 'Mamani')
            ->set('codigoBiometrico', 'BIO-999')
            ->set('email', 'carlos.mamani@correos.gob.bo')
            ->set('sucursal', 'La Paz')
            ->call('saveEmpleado')
            ->assertHasNoErrors();

        $empleado = Empleado::query()->where('codigo_biometrico', 'BIO-999')->firstOrFail();
        $this->assertSame('carlos.mamani@correos.gob.bo', $empleado->email);

        // 2. Buscar por correo
        Livewire::test('personal-page')
            ->set('search', 'carlos.mamani@correos.gob.bo')
            ->assertSee('Carlos')
            ->assertSee('BIO-999')
            ->assertSee('carlos.mamani@correos.gob.bo');

        // 3. Editar correo
        Livewire::test('personal-page')
            ->call('openEditModal', $empleado->id)
            ->assertSet('editEmail', 'carlos.mamani@correos.gob.bo')
            ->set('editEmail', 'carlos.nuevo@correos.gob.bo')
            ->call('updateEmpleado')
            ->assertHasNoErrors();

        $this->assertSame('carlos.nuevo@correos.gob.bo', $empleado->fresh()->email);
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
