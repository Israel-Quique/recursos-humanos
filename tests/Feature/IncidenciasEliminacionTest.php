<?php

namespace Tests\Feature;

use App\Livewire\IncidenciasPage;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncidenciasEliminacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        Permission::findOrCreate('gestionar personal', 'web');
        Role::findOrCreate($rol, 'web')->givePermissionTo('gestionar personal');
        $usuario = User::query()->create([
            'name' => 'Usuario de prueba',
            'email' => $rol.'@example.test',
            'password' => 'password',
        ]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    private function boleta(): PermisoLaboral
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'codigo_biometrico' => '123456',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => '2026-01-01',
        ]);

        return PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'dias',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-10-27',
            'fecha_fin' => '2026-10-27',
            'motivo' => 'Boleta de prueba',
        ]);
    }

    public function test_administrador_elimina_boleta_con_confirmacion_y_auditoria(): void
    {
        $admin = $this->usuario('administrador');
        $boleta = $this->boleta();
        $comprobante = PermisoComprobante::query()->create([
            'permiso_laboral_id' => $boleta->id,
            'ruta_archivo' => 'comprobantes/prueba.jpg',
            'nombre_original' => 'prueba.jpg',
            'mime_type' => 'image/jpeg',
            'tamano_bytes' => 100,
            'archivo_base64' => base64_encode('respaldo de prueba'),
        ]);

        $component = Livewire::actingAs($admin)->test(IncidenciasPage::class)
            ->assertSeeHtml('wire:click="openDeleteModal('.$boleta->id.')"')
            ->call('openDeleteModal', $boleta->id)
            ->assertSet('showDeleteModal', true)
            ->assertSee('Sí, eliminar boleta');

        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);

        $component->call('deleteIncidencia')->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseMissing('permiso_comprobantes', ['id' => $comprobante->id]);
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'Incidencias',
            'accion' => 'eliminar',
            'actor_id' => $admin->id,
            'auditable_id' => $boleta->id,
        ]);

        $respaldo = DB::table('boletas_eliminadas')->sole();
        $this->assertEquals($boleta->id, $respaldo->permiso_laboral_id);
        $this->assertEquals($admin->id, $respaldo->eliminado_por);
        $this->assertSame($admin->email, $respaldo->eliminado_por_email);
        $this->assertSame($admin->name, $respaldo->eliminado_por_nombre);
        $this->assertNotEmpty($respaldo->eliminado_en);
        $this->assertSame('Boleta de prueba', json_decode($respaldo->datos_boleta, true)['motivo']);
        $this->assertSame('Ana', json_decode($respaldo->datos_empleado, true)['nombre']);
        $this->assertSame($comprobante->archivo_base64, json_decode($respaldo->comprobantes, true)[0]['archivo_base64']);

        // El historial no depende de que el empleado o el administrador sigan existiendo.
        $boleta->empleado->forceDelete();
        $admin->delete();
        $this->assertDatabaseHas('boletas_eliminadas', ['id' => $respaldo->id]);
    }

    public function test_cancelar_conserva_la_boleta(): void
    {
        $boleta = $this->boleta();

        Livewire::actingAs($this->usuario('administrador'))->test(IncidenciasPage::class)
            ->call('openDeleteModal', $boleta->id)
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false)
            ->assertSet('pendingDeleteIncidenciaId', null)
            ->call('deleteIncidencia');

        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
    }

    public function test_gestor_no_ve_el_boton_ni_puede_abrir_confirmacion(): void
    {
        $boleta = $this->boleta();

        Livewire::actingAs($this->usuario('gestor'))->test(IncidenciasPage::class)
            ->assertDontSeeHtml('wire:click="openDeleteModal('.$boleta->id.')"')
            ->call('openDeleteModal', $boleta->id)
            ->assertForbidden();

        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
    }

    public function test_gestor_no_puede_eliminar_mediante_llamada_directa(): void
    {
        $boleta = $this->boleta();

        Livewire::actingAs($this->usuario('gestor'))->test(IncidenciasPage::class)
            ->set('pendingDeleteIncidenciaId', $boleta->id)
            ->call('deleteIncidencia')
            ->assertForbidden();

        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
    }

    public function test_respalda_comprobantes_antiguos_binarios_y_archivos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('comprobantes/antiguo.jpg', 'imagen en disco');
        $boleta = $this->boleta();
        foreach (['binario', 'disco'] as $origen) {
            PermisoComprobante::query()->create([
                'permiso_laboral_id' => $boleta->id,
                'ruta_archivo' => 'comprobantes/antiguo.jpg',
                'nombre_original' => $origen.'.jpg',
                'archivo_binario' => $origen === 'binario' ? "\xFF\xD8\x00\xFE" : null,
            ]);
        }

        Livewire::actingAs($this->usuario('administrador'))->test(IncidenciasPage::class)
            ->call('openDeleteModal', $boleta->id)
            ->call('deleteIncidencia');

        $comprobantes = collect(json_decode(DB::table('boletas_eliminadas')->sole()->comprobantes, true))
            ->keyBy('nombre_original');
        $this->assertSame(base64_encode("\xFF\xD8\x00\xFE"), $comprobantes['binario.jpg']['archivo_base64']);
        $this->assertSame(base64_encode('imagen en disco'), $comprobantes['disco.jpg']['archivo_base64']);
    }

    public function test_si_falla_la_auditoria_se_revierte_toda_la_eliminacion(): void
    {
        $this->actingAs($this->usuario('administrador'));
        $boleta = $this->boleta();
        $comprobante = PermisoComprobante::query()->create([
            'permiso_laboral_id' => $boleta->id,
            'ruta_archivo' => 'comprobantes/prueba.jpg',
            'nombre_original' => 'prueba.jpg',
        ]);
        $this->mock(AuditoriaService::class)
            ->shouldReceive('registrar')->once()->andThrow(new \RuntimeException('Error de auditoría'));

        $component = new IncidenciasPage;
        $component->pendingDeleteIncidenciaId = $boleta->id;
        try {
            $component->deleteIncidencia();
            $this->fail('La eliminación debe fallar si no se registra la auditoría.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Error de auditoría', $e->getMessage());
        }

        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseHas('permiso_comprobantes', ['id' => $comprobante->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
        $this->assertDatabaseCount('auditorias', 0);
    }
}
