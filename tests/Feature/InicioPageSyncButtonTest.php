<?php

namespace Tests\Feature;

use App\Livewire\InicioPage;
use App\Models\BiometricoDispositivo;
use App\Models\User;
use App\Services\ExtraccionBiometricoZkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InicioPageSyncButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_manual_sync_button_for_allowed_users(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $this->actingAs($user);
        $this->registrarReloj();

        Livewire::test(InicioPage::class)
            ->assertSee('Ajustar hora de Oficina Central La Paz')
            ->assertSee('Sincronizar marcaciones');
    }

    public function test_manual_sync_action_triggers_synchronization_and_renders_summary(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $this->actingAs($user);

        $service = \Mockery::mock(\App\Services\SincronizacionBiometricoService::class);
        $service->shouldReceive('sincronizarTodos')->once()->with(false)->andReturn([
            [
                'device' => 'Oficina Central',
                'status' => 'sincronizado',
                'message' => 'Sincronizado correctamente',
                'imported' => 10,
                'updated' => 2,
                'created' => 8,
            ],
        ]);

        $this->app->instance(\App\Services\SincronizacionBiometricoService::class, $service);

        Livewire::test(InicioPage::class)
            ->call('sincronizarBiometrico')
            ->assertSee('Sincronización completada')
            ->assertSee('Oficina Central');
    }

    public function test_el_panel_muestra_la_ultima_hora_y_sincroniza_el_equipo_registrado(): void
    {
        $this->actingAs($this->crearUsuarioConPermisos());
        $device = $this->registrarReloj();
        $equipo = hash('sha256', $device->ip.':4370');
        Cache::forever('biometrico:hora:'.$equipo, [
            'device_time' => '2026-11-16T12:30:00',
            'checked_at' => '2026-11-16T12:30:01-04:00',
            'status' => 'adjusted',
        ]);
        $this->partialMock(ExtraccionBiometricoZkService::class, function ($mock) use ($device) {
            $mock->shouldReceive('sincronizarReloj')
                ->withArgs(fn ($d) => $d['ip'] === $device->ip)
                ->once();
        });

        Livewire::test(InicioPage::class)
            ->assertSee('Relojes biométricos')
            ->assertSee('16/11/2026')->assertSee('12:30:00')
            ->assertSee('Hora sincronizada')
            ->call('sincronizarHoraTrasReinicio', $equipo)
            ->assertSee('Hora sincronizada');
    }

    public function test_usuario_sin_permiso_puede_ver_relojes_pero_no_ajustarlos(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $user->revokePermissionTo('importar biometria');
        $device = $this->registrarReloj();

        Livewire::actingAs($user)->test(InicioPage::class)
            ->assertSee('Relojes biométricos')
            ->assertDontSee('Ajustar hora de Oficina Central La Paz')
            ->call('sincronizarHoraTrasReinicio', hash('sha256', $device->ip.':4370'))
            ->assertForbidden();
    }

    private function registrarReloj(): BiometricoDispositivo
    {
        return BiometricoDispositivo::query()->create([
            'department' => 'La Paz', 'branch' => 'Oficina Central La Paz', 'ip' => '192.0.2.10', 'is_active' => true,
        ])->refresh();
    }

    public function test_home_page_displays_active_staff_and_daily_attendance_metrics(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $this->actingAs($user);

        // Empleado 1: Activo con marcación hoy
        $activo = \App\Models\Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Activo',
            'codigo_biometrico' => '1001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subYear()->toDateString(),
            'created_by' => $user->id,
        ]);

        \App\Models\RegistroAsistencia::query()->create([
            'empleado_id' => $activo->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'created_by' => $user->id,
        ]);

        // Empleado 2: Inactivo histórico (> 30 días sin marcar y sin ser especial)
        $inactivo = \App\Models\Empleado::query()->create([
            'nombre' => 'Pedro',
            'apellido' => 'Inactivo',
            'codigo_biometrico' => '1002',
            'area' => 'Operaciones',
            'sucursal' => 'Santa Cruz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subYears(2)->toDateString(),
            'created_by' => $user->id,
        ]);

        \App\Models\RegistroAsistencia::query()->create([
            'empleado_id' => $inactivo->id,
            'fecha' => now()->subMonths(2)->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'created_by' => $user->id,
        ]);

        Livewire::test(InicioPage::class)
            ->assertSee('Personal Activo')
            ->assertSee('Marcaron Hoy')
            ->assertSee('Marcaciones de Hoy por Sucursal')
            ->assertViewHas('totalEmpleadosActivos', 1)
            ->assertViewHas('totalEmpleadosPadron', 2)
            ->assertViewHas('totalMarcacionesHoy', 1);
    }

    private function crearUsuarioConPermisos(): User
    {
        Permission::findOrCreate('ver panel', 'web');
        Permission::findOrCreate('importar biometria', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo(['ver panel', 'importar biometria']);

        return $user;
    }
}
