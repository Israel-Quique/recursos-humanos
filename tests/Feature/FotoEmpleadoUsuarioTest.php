<?php

namespace Tests\Feature;

use App\Livewire\GestionAccesosPage;
use App\Livewire\PerfilHorasPage;
use App\Livewire\PersonalPage;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FotoEmpleadoUsuarioTest extends TestCase
{
    use RefreshDatabase;

    private User $gestorUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Permission::findOrCreate('gestionar personal');
        Permission::findOrCreate('gestionar accesos');
        Role::findOrCreate('administrador');
        Role::findOrCreate('gestor');

        $this->gestorUser = User::query()->create([
            'name' => 'gestor.rrhh',
            'email' => 'gestor@correos.gob.bo',
            'password' => bcrypt('secret123'),
        ]);

        $this->gestorUser->assignRole('gestor');
        $this->gestorUser->givePermissionTo(['gestionar personal', 'gestionar accesos']);
    }

    public function test_empleado_puede_tener_foto_y_accesor_foto_url(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'JUAN PABLO',
            'apellido' => 'VARGAS',
            'codigo_biometrico' => '1048',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
            'foto' => 'fotos/empleados/emp_1.jpg',
        ]);

        $this->assertNotNull($empleado->foto);
        $this->assertStringContainsString('fotos/empleados/emp_1.jpg', $empleado->foto_url);
    }

    public function test_usuario_hereda_foto_de_empleado_vinculado(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'MARIA',
            'apellido' => 'LOPEZ',
            'codigo_biometrico' => '2001',
            'area' => 'RRHH',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
            'foto' => 'fotos/empleados/emp_maria.jpg',
        ]);

        $user = User::query()->create([
            'name' => 'maria.lopez',
            'email' => 'maria.lopez@correos.gob.bo',
            'password' => bcrypt('password123'),
            'empleado_id' => $empleado->id,
            'foto' => null, // Sin foto propia
        ]);

        $this->assertNotNull($user->foto_url);
        $this->assertStringContainsString('fotos/empleados/emp_maria.jpg', $user->foto_url);
    }

    public function test_empleado_hereda_foto_de_usuario_vinculado_si_no_tiene_propia(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'CARLOS',
            'apellido' => 'MAMANI',
            'codigo_biometrico' => '3001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
            'foto' => null,
        ]);

        User::query()->create([
            'name' => 'carlos.mamani',
            'email' => 'carlos.mamani@correos.gob.bo',
            'password' => bcrypt('password123'),
            'empleado_id' => $empleado->id,
            'foto' => 'fotos/usuarios/user_carlos.jpg',
        ]);

        $this->assertNotNull($empleado->foto_url);
        $this->assertStringContainsString('fotos/usuarios/user_carlos.jpg', $empleado->foto_url);
    }

    public function test_perfil_horas_muestra_imagen_cuando_empleado_tiene_foto(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'ADELA',
            'apellido' => 'FERNANDEZ',
            'codigo_biometrico' => '4001',
            'area' => 'Auditoria',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
            'foto' => 'fotos/empleados/adela.jpg',
        ]);

        $signedUrl = URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false);

        $this->get($signedUrl)
            ->assertOk()
            ->assertSee('ph-avatar-img')
            ->assertSee('adela.jpg');
    }

    public function test_gestor_puede_subir_y_quitar_foto_en_personal(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'DIEGO',
            'apellido' => 'ROJAS',
            'codigo_biometrico' => '5001',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
        ]);

        $file = UploadedFile::fake()->image('diego_foto.jpg', 200, 200);

        // Subir foto al editar
        Livewire::actingAs($this->gestorUser)
            ->test(PersonalPage::class)
            ->call('openEditModal', $empleado->id)
            ->set('fotoNueva', $file)
            ->call('updateEmpleado')
            ->assertHasNoErrors();

        $empleado->refresh();
        $this->assertNotNull($empleado->foto);
        Storage::disk('public')->assertExists($empleado->foto);

        // Quitar foto
        Livewire::actingAs($this->gestorUser)
            ->test(PersonalPage::class)
            ->call('openEditModal', $empleado->id)
            ->call('quitarFoto')
            ->call('updateEmpleado')
            ->assertHasNoErrors();

        $empleado->refresh();
        $this->assertNull($empleado->foto);
    }

    public function test_gestor_puede_subir_foto_de_usuario(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 150, 150);

        Livewire::actingAs($this->gestorUser)
            ->test(GestionAccesosPage::class)
            ->set('name', 'usuario.foto')
            ->set('email', 'usuario.foto')
            ->set('password', 'password1234')
            ->set('password_confirmation', 'password1234')
            ->set('newUserRole', 'gestor')
            ->set('userFotoNueva', $file)
            ->call('createUser')
            ->assertHasNoErrors();

        $newUser = User::where('name', 'usuario.foto')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->foto);
        Storage::disk('public')->assertExists($newUser->foto);
    }

    public function test_seeder_empleado_fotos_ejecuta_correctamente(): void
    {
        $this->seed(\Database\Seeders\EmpleadoFotosSeeder::class);
        $this->assertTrue(true);
    }
}
