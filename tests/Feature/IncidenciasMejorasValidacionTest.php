<?php

namespace Tests\Feature;

use App\Livewire\IncidenciasPage;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\TipoPermiso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class IncidenciasMejorasValidacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_rrhh_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
        Permission::findOrCreate('gestionar personal');
        $user->givePermissionTo('gestionar personal');

        return $user;
    }

    private function crearEmpleado(): Empleado
    {
        return Empleado::query()->create([
            'nombre' => 'María',
            'apellido' => 'Gómez',
            'codigo_biometrico' => '998877',
            'email' => 'maria.gomez@example.com',
            'cargo' => 'ANALISTA RRHH',
            'area' => 'RECURSOS HUMANOS',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
        ]);
    }

    public function test_permite_crear_incidencia_con_comprobante_adjunto(): void
    {
        Storage::fake('public');
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado();

        $file = UploadedFile::fake()->image('certificado_medico.png', 400, 400);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('openCreateModal')
            ->call('seleccionarEmpleado', $empleado->id)
            ->set('tipo', 'permiso')
            ->set('alcance', 'dias')
            ->set('estado', 'aprobado')
            ->set('fechaInicio', '2026-10-01')
            ->set('fechaFin', '2026-10-02')
            ->set('motivo', 'Permiso por salud')
            ->set('comprobante', $file)
            ->call('saveIncidencia')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permisos_laborales', [
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'motivo' => 'Permiso por salud',
        ]);

        $incidencia = PermisoLaboral::query()->first();
        $this->assertNotNull($incidencia);

        $comprobante = PermisoComprobante::query()->where('permiso_laboral_id', $incidencia->id)->first();
        $this->assertNotNull($comprobante);
        $this->assertSame('certificado_medico.png', $comprobante->nombre_original);
        $this->assertNotEmpty($comprobante->archivo_base64);
    }

    public function test_validacion_falla_si_falta_empleado(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('openCreateModal')
            ->set('empleadoId', '')
            ->set('fechaInicio', '2026-10-01')
            ->set('fechaFin', '2026-10-02')
            ->call('saveIncidencia')
            ->assertHasErrors(['empleadoId']);
    }

    public function test_administrar_tipos_de_permiso_permite_crear_editar_y_eliminar(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('openGestionTiposModal')
            ->set('nuevoTipoNombre', 'Licencia por Maternidad')
            ->call('crearTipoPermiso')
            ->assertHasNoErrors();

        $tipo = TipoPermiso::query()->where('nombre', 'Licencia por Maternidad')->first();
        $this->assertNotNull($tipo);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('iniciarEditarTipoPermiso', $tipo->id)
            ->set('editandoTipoNombre', 'Licencia de Maternidad Especial')
            ->call('guardarEdicionTipoPermiso')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tipos_permisos', [
            'id' => $tipo->id,
            'nombre' => 'Licencia de Maternidad Especial',
        ]);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('eliminarTipoPermiso', $tipo->id);

        $this->assertDatabaseMissing('tipos_permisos', [
            'id' => $tipo->id,
        ]);
    }
}
