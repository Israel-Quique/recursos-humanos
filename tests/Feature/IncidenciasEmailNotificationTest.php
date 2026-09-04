<?php

namespace Tests\Feature;

use App\Livewire\IncidenciasPage;
use App\Mail\BoletaEstadoMailable;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class IncidenciasEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_rrhh_' . uniqid() . '@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);
        Permission::findOrCreate('gestionar personal');
        $user->givePermissionTo('gestionar personal');

        return $user;
    }

    private function crearEmpleado(?string $email = 'empleado.test@correos.gob.bo'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Gutiérrez',
            'codigo_biometrico' => '889911',
            'email' => $email,
            'cargo' => 'RESPONSABLE DE CORRESPONDENCIA',
            'area' => 'OPERACIONES',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
        ]);
    }

    private function crearPermiso(Empleado $empleado, string $estado = 'pendiente'): PermisoLaboral
    {
        return PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'tipo_permiso' => 'medico',
            'tipo_licencia' => 'particular',
            'alcance' => 'horas',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-09-15',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '11:00:00',
            'tiempo_solicitado' => '2 HORAS',
            'motivo' => 'Cita médica en la Caja Nacional',
            'estado' => $estado,
        ]);
    }

    public function test_aprobar_boleta_envia_correo_al_empleado(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado(email: 'ana.gutierrez@correos.gob.bo');
        $permiso = $this->crearPermiso($empleado);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('abrirConfirmacion', $permiso->id, 'aprobado')
            ->assertSet('confirmandoIncidenciaId', $permiso->id)
            ->assertSet('confirmandoNuevoEstado', 'aprobado')
            ->call('confirmarAccion')
            ->assertHasNoErrors()
            ->assertSet('confirmandoIncidenciaId', null);

        $this->assertSame('aprobado', $permiso->fresh()->estado);

        Mail::assertSent(BoletaEstadoMailable::class, function (BoletaEstadoMailable $mail) {
            return $mail->hasTo('ana.gutierrez@correos.gob.bo')
                && $mail->estado === 'aprobado'
                && str_contains($mail->envelope()->subject, 'Aprobada');
        });
    }

    public function test_rechazar_boleta_falla_si_no_se_ingresa_motivo(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado();
        $permiso = $this->crearPermiso($empleado);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('abrirConfirmacion', $permiso->id, 'rechazado')
            ->set('motivoRechazo', '') // Vacío
            ->call('confirmarAccion')
            ->assertHasErrors(['motivoRechazo' => 'required']);

        $this->assertSame('pendiente', $permiso->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_rechazar_boleta_guarda_motivo_y_envia_correo_con_explicacion(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado(email: 'ana.gutierrez@correos.gob.bo');
        $permiso = $this->crearPermiso($empleado);

        $motivoExplicado = 'El comprobante médico adjunto no cuenta con sello ni firma legible del médico tratante.';

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('abrirConfirmacion', $permiso->id, 'rechazado')
            ->set('motivoRechazo', $motivoExplicado)
            ->call('confirmarAccion')
            ->assertHasNoErrors()
            ->assertSet('confirmandoIncidenciaId', null);

        $permisoActualizado = $permiso->fresh();
        $this->assertSame('rechazado', $permisoActualizado->estado);
        $this->assertSame($motivoExplicado, $permisoActualizado->motivo_rechazo);

        Mail::assertSent(BoletaEstadoMailable::class, function (BoletaEstadoMailable $mail) use ($motivoExplicado) {
            return $mail->hasTo('ana.gutierrez@correos.gob.bo')
                && $mail->estado === 'rechazado'
                && $mail->motivoRechazo === $motivoExplicado
                && str_contains($mail->envelope()->subject, 'Rechazada');
        });
    }

    public function test_aprobar_o_rechazar_sin_correo_registrado_no_falla_ni_envia_correo(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado(email: null);
        $permiso = $this->crearPermiso($empleado);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('abrirConfirmacion', $permiso->id, 'aprobado')
            ->call('confirmarAccion')
            ->assertHasNoErrors();

        $this->assertSame('aprobado', $permiso->fresh()->estado);
        Mail::assertNothingSent();
    }
}
