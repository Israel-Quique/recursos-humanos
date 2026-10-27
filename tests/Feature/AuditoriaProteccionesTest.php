<?php

namespace Tests\Feature;

use App\Livewire\AuditoriaPage;
use App\Livewire\IncidenciasPage;
use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditoriaProteccionesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(bool $admin = true): User
    {
        $user = User::query()->create([
            'name' => $admin ? 'Administrador histórico' : 'Lector auditoría',
            'email' => $admin ? 'admin@example.test' : 'lector@example.test',
            'password' => 'password',
        ]);
        Permission::findOrCreate('ver auditoria', 'web');
        if ($admin) {
            $user->assignRole(Role::findOrCreate('administrador', 'web'));
        } else {
            $user->givePermissionTo('ver auditoria');
        }

        return $user;
    }

    private function boleta(): PermisoLaboral
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Ana', 'apellido' => 'Prueba', 'area' => 'Sistemas',
            'sucursal' => 'La Paz', 'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => '2026-01-01',
        ]);
        $boleta = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id, 'tipo' => 'permiso', 'alcance' => 'dias',
            'estado' => 'rechazado', 'fecha_inicio' => '2026-10-27',
            'fecha_fin' => '2026-10-27', 'motivo' => 'Boleta para auditoría',
            'motivo_rechazo' => 'Falta de sello', 'tipo_permiso_clave' => 'salud',
        ]);
        PermisoComprobante::query()->create([
            'permiso_laboral_id' => $boleta->id, 'ruta_archivo' => 'prueba.jpg',
            'nombre_original' => 'prueba.jpg', 'archivo_base64' => base64_encode('imagen'),
        ]);

        return $boleta;
    }

    private function creacion(PermisoLaboral $boleta): Auditoria
    {
        return app(AuditoriaService::class)->registrar(
            'Incidencias', 'crear', 'Creación para prueba', $boleta, null, $boleta->getAttributes()
        );
    }

    public function test_identidad_se_conserva_al_cambiar_y_eliminar_usuario(): void
    {
        $actor = $this->usuario();
        $this->actingAs($actor);
        $auditoria = app(AuditoriaService::class)->registrar('Prueba', 'editar', 'Identidad histórica');
        $actor->update(['name' => 'Nombre cambiado']);
        $actor->delete();

        $auditoria->refresh();
        $this->assertNull($auditoria->actor_id);
        $this->assertEquals($actor->id, $auditoria->actor_id_original);
        $this->assertSame('Administrador histórico', $auditoria->actor_nombre);
        $this->assertSame('admin@example.test', $auditoria->actor_email);

        Livewire::actingAs($this->usuario(false))->test(AuditoriaPage::class)
            ->set('search', 'admin@example.test')
            ->set('actorFiltro', (string) $actor->id)
            ->assertSee('Administrador histórico')->assertSee('Identidad histórica');
    }

    public function test_consulta_no_permite_deshacer_ni_rehacer(): void
    {
        $admin = $this->usuario();
        $this->actingAs($admin);
        $boleta = $this->boleta();
        $auditoria = $this->creacion($boleta);
        $lector = $this->usuario(false);

        Livewire::actingAs($lector)->test(AuditoriaPage::class)
            ->assertDontSeeHtml('wire:click="deshacerAccion(')
            ->call('deshacerAccion', $auditoria->id)->assertForbidden();
        Livewire::actingAs($lector)->test(AuditoriaPage::class)
            ->call('rehacerAccion', $auditoria->id)->assertForbidden();
        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
    }

    public function test_no_administrador_con_permiso_de_reversion_no_puede_eliminar_boletas(): void
    {
        $this->actingAs($this->usuario());
        $boleta = $this->boleta();
        $auditoria = $this->creacion($boleta);
        $lector = $this->usuario(false);
        $lector->givePermissionTo('revertir auditoria');

        Livewire::actingAs($lector)->test(AuditoriaPage::class)
            ->call('deshacerAccion', $auditoria->id);
        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
    }

    public function test_deshacer_y_rehacer_creacion_archiva_y_restaura_boleta_completa(): void
    {
        $this->actingAs($this->usuario());
        $boleta = $this->boleta();
        $auditoria = $this->creacion($boleta);
        $component = Livewire::test(AuditoriaPage::class)->call('deshacerAccion', $auditoria->id);
        $this->assertDatabaseMissing('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 1);

        $component->call('rehacerAccion', $auditoria->id);
        $this->assertDatabaseHas('permisos_laborales', [
            'id' => $boleta->id, 'motivo_rechazo' => 'Falta de sello', 'tipo_permiso_clave' => 'salud',
        ]);
        $this->assertDatabaseHas('permiso_comprobantes', [
            'permiso_laboral_id' => $boleta->id, 'archivo_base64' => base64_encode('imagen'),
        ]);

        $component->call('deshacerAccion', $auditoria->id);
        $this->assertDatabaseCount('boletas_eliminadas', 2);
        $this->assertDatabaseCount('permiso_comprobantes', 0);
    }

    public function test_eliminacion_desde_incidencias_tambien_se_puede_restaurar_y_rehacer(): void
    {
        $this->actingAs($this->usuario());
        $boleta = $this->boleta();
        Livewire::test(IncidenciasPage::class)
            ->call('openDeleteModal', $boleta->id)->call('deleteIncidencia');
        $auditoria = Auditoria::query()->where('accion', 'eliminar')->sole();
        Livewire::test(AuditoriaPage::class)->call('deshacerAccion', $auditoria->id)
            ->call('rehacerAccion', $auditoria->id);

        $this->assertDatabaseMissing('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('boletas_eliminadas', 2);
        $this->assertDatabaseCount('permiso_comprobantes', 0);
    }

    public function test_error_de_registro_revierte_el_borrado_desde_auditoria(): void
    {
        $this->actingAs($this->usuario());
        $boleta = $this->boleta();
        $auditoria = $this->creacion($boleta);
        $this->mock(AuditoriaService::class)->shouldReceive('registrar')->once()
            ->andThrow(new \RuntimeException('Fallo controlado'));

        try {
            (new AuditoriaPage)->deshacerAccion($auditoria->id);
            $this->fail('Debe fallar y revertir.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo controlado', $e->getMessage());
        }
        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id]);
        $this->assertDatabaseCount('permiso_comprobantes', 1);
        $this->assertDatabaseCount('boletas_eliminadas', 0);
        $this->assertNull($auditoria->fresh()->meta['estado_reversion'] ?? null);
        $this->assertSame(1, DB::table('auditorias')->count());
    }

    public function test_fallo_de_auditoria_revierte_creacion_edicion_y_aprobacion_de_incidencias(): void
    {
        $this->actingAs($this->usuario());
        $boleta = $this->boleta();
        $boleta->update(['estado' => 'pendiente']);

        foreach (['crear', 'editar', 'aprobar'] as $accion) {
            $this->mock(AuditoriaService::class)->shouldReceive('registrar')->once()
                ->andThrow(new \RuntimeException('Fallo controlado'));
            $component = new IncidenciasPage;
            $component->empleadoId = (string) $boleta->empleado_id;
            $component->fechaInicio = '2026-10-28';
            $component->fechaFin = '2026-10-28';
            $component->editingIncidenciaId = $boleta->id;
            $component->editEmpleadoId = (string) $boleta->empleado_id;
            $component->editFechaInicio = '2026-10-27';
            $component->editFechaFin = '2026-10-27';
            $component->editMotivo = 'Motivo modificado';

            try {
                match ($accion) {
                    'crear' => $component->saveIncidencia(),
                    'editar' => $component->updateIncidencia(),
                    'aprobar' => $component->cambiarEstado($boleta->id, 'aprobado'),
                };
                $this->fail('Debe revertirse la operación: '.$accion);
            } catch (\RuntimeException $e) {
                $this->assertSame('Fallo controlado', $e->getMessage());
            }

            $this->assertDatabaseCount('permisos_laborales', 1);
            $this->assertDatabaseHas('permisos_laborales', [
                'id' => $boleta->id, 'estado' => 'pendiente', 'motivo' => 'Boleta para auditoría',
            ]);
            $this->assertDatabaseCount('auditorias', 0);
        }
    }
}
