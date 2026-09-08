<?php

namespace Tests\Feature;

use App\Livewire\InicioPage;
use App\Models\User;
use App\Services\BiometricoAutoSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        Livewire::test(InicioPage::class)
            ->assertSee('Sincronizar biométrico');
    }

    public function test_manual_sync_action_triggers_synchronization_and_renders_summary(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $this->actingAs($user);

        $service = \Mockery::mock(\App\Services\SincronizacionBiometricoService::class);
        $service->shouldReceive('sincronizarTodos')->once()->andReturn([
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
