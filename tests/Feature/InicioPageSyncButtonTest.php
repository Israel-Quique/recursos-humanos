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

    public function test_manual_sync_action_starts_background_sync_instead_of_blocking_the_request(): void
    {
        $user = $this->crearUsuarioConPermisos();
        $this->actingAs($user);

        $service = \Mockery::mock(BiometricoAutoSyncService::class);
        $service->shouldReceive('triggerNow')->once();

        $this->app->instance(BiometricoAutoSyncService::class, $service);

        Livewire::test(InicioPage::class)
            ->call('sincronizarBiometrico')
            ->assertSee('Sincronización iniciada en segundo plano');
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
