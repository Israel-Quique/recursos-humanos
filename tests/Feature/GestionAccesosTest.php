<?php

namespace Tests\Feature;

use App\Livewire\GestionAccesosPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GestionAccesosTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar accesos');
        Role::findOrCreate('administrador');
        Role::findOrCreate('gestor');

        $this->adminUser = User::query()->create([
            'name' => 'admin.principal',
            'email' => 'admin@correos.gob.bo',
            'password' => bcrypt('password123'),
        ]);

        $this->adminUser->assignRole('administrador');
        $this->adminUser->givePermissionTo('gestionar accesos');
    }

    public function test_pantalla_accesos_carga_correctamente(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/accesos')
            ->assertOk()
            ->assertSee('Usuarios con acceso al sistema')
            ->assertSee('Dar de alta usuario');
    }

    public function test_alta_de_usuario_crea_registro_y_asigna_rol(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->set('name', 'gestor.nuevo')
            ->set('email', 'gestor.nuevo')
            ->set('password', 'password1234')
            ->set('password_confirmation', 'password1234')
            ->set('newUserRole', 'gestor')
            ->call('createUser')
            ->assertHasNoErrors()
            ->assertSee('Usuario dado de alta exitosamente');

        $this->assertDatabaseHas('users', [
            'name' => 'gestor.nuevo',
            'email' => 'gestor.nuevo@correos.gob.bo',
        ]);

        $nuevo = User::where('name', 'gestor.nuevo')->firstOrFail();
        $this->assertTrue($nuevo->hasRole('gestor'));
    }

    public function test_modificacion_de_usuario_actualiza_datos(): void
    {
        $usuario = User::query()->create([
            'name' => 'usuario.modificar',
            'email' => 'usuario.modificar@correos.gob.bo',
            'password' => bcrypt('password1234'),
        ]);
        $usuario->assignRole('gestor');

        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->call('openEditModal', $usuario->id)
            ->set('editName', 'usuario.actualizado')
            ->set('editEmail', 'usuario.actualizado')
            ->set('editRole', 'gestor')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSee('Datos del usuario modificados correctamente');

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'usuario.actualizado',
            'email' => 'usuario.actualizado@correos.gob.bo',
        ]);
    }

    public function test_baja_de_usuario_elimina_cuenta_y_registra_auditoria(): void
    {
        $usuario = User::query()->create([
            'name' => 'usuario.eliminar',
            'email' => 'usuario.eliminar@correos.gob.bo',
            'password' => bcrypt('password1234'),
        ]);
        $usuario->assignRole('gestor');

        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->call('confirmDelete', $usuario->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('pendingDeleteUserId', $usuario->id)
            ->call('deleteUser')
            ->assertHasNoErrors()
            ->assertSee('Usuario dado de baja y eliminado correctamente');

        $this->assertDatabaseMissing('users', [
            'id' => $usuario->id,
        ]);

        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'Accesos',
            'accion' => 'eliminar',
        ]);
    }

    public function test_no_permite_dar_de_baja_a_si_mismo(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->call('confirmDelete', $this->adminUser->id)
            ->assertSet('showDeleteModal', false)
            ->assertSee('No puedes dar de baja tu propia cuenta');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    public function test_no_permite_eliminar_al_unico_administrador(): void
    {
        $admin2 = User::query()->create([
            'name' => 'admin.secundario',
            'email' => 'admin.secundario@correos.gob.bo',
            'password' => bcrypt('password1234'),
        ]);
        $admin2->assignRole('administrador');

        // Eliminamos el admin secundario
        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->call('confirmDelete', $admin2->id)
            ->call('deleteUser')
            ->assertHasNoErrors();

        // Ahora solo queda 1 admin: no debe poder darse de baja si de algun modo se invoca deleteUser
        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->set('pendingDeleteUserId', $this->adminUser->id)
            ->call('deleteUser')
            ->assertSee('No puedes dar de baja');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    public function test_busqueda_y_filtro_de_roles_funciona(): void
    {
        $gestor = User::query()->create([
            'name' => 'carlos.gestor',
            'email' => 'carlos.gestor@correos.gob.bo',
            'password' => bcrypt('password1234'),
        ]);
        $gestor->assignRole('gestor');

        Livewire::actingAs($this->adminUser)
            ->test(GestionAccesosPage::class)
            ->set('search', 'carlos')
            ->assertSee('carlos.gestor')
            ->set('roleFilter', 'administrador')
            ->assertDontSee('carlos.gestor')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('roleFilter', '');
    }
}
