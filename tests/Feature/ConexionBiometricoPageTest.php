<?php

namespace Tests\Feature;

use App\Livewire\ImportarExcelPage;
use App\Models\User;
use App\Models\BiometricoDispositivo;
use App\Services\ConexionBiometricoService;
use App\Services\ExtraccionBiometricoZkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ConexionBiometricoPageTest extends TestCase
{
    use RefreshDatabase;

    private function formularioBiometrico(): \Livewire\Features\SupportTesting\Testable
    {
        $user = User::query()->create([
            'name' => 'Admin equipos', 'email' => 'equipos@example.test', 'password' => bcrypt('test'),
        ]);
        $user->givePermissionTo(Permission::findOrCreate('importar biometria', 'web'));

        return Livewire::actingAs($user)->test(ImportarExcelPage::class)
            ->call('openBiometricoModal')
            ->set('deviceDepartment', 'Pando')
            ->set('deviceBranch', 'Sucursal Pando')
            ->set('deviceIp', '172.65.26.43');
    }

    public function test_agregar_equipo_acepta_espacios_al_pegar_y_puerto_predeterminado(): void
    {
        $this->formularioBiometrico()
            ->set('deviceDepartment', ' Pando ')
            ->set('deviceBranch', ' Sucursal Pando ')
            ->set('deviceIp', ' 172.65.26.43 ')
            ->set('devicePort', '')
            ->call('saveBiometrico')->assertHasNoErrors()
            ->assertSet('showBiometricoModal', false);

        $this->assertDatabaseHas('biometrico_dispositivos', [
            'department' => 'Pando', 'branch' => 'Sucursal Pando', 'ip' => '172.65.26.43', 'port' => 4370,
        ]);
        Livewire::test(ImportarExcelPage::class)->assertSee('Sucursal Pando')->assertSee('172.65.26.43');
    }

    public function test_ip_duplicada_muestra_error_y_conserva_los_datos_del_formulario(): void
    {
        BiometricoDispositivo::query()->create([
            'department' => 'Pando', 'branch' => 'Existente', 'ip' => '172.65.26.43', 'port' => 4370,
        ]);
        $this->formularioBiometrico()->set('deviceIp', ' 172.65.26.43 ')
            ->call('saveBiometrico')->assertHasErrors(['deviceIp' => 'unique'])
            ->assertSee('Ya existe un biometrico registrado con esa IP.')
            ->assertSet('showBiometricoModal', true)
            ->assertSet('deviceBranch', 'Sucursal Pando');
        $this->assertDatabaseCount('biometrico_dispositivos', 1);
    }

    public function test_error_de_auditoria_no_deja_un_equipo_guardado_a_medias(): void
    {
        $this->mock(\App\Services\AuditoriaService::class, function ($mock) {
            $mock->shouldReceive('registrar')->once()->andThrow(new \RuntimeException('Error de prueba'));
        });
        $this->formularioBiometrico()->call('saveBiometrico')
            ->assertHasErrors('biometrico')->assertSet('showBiometricoModal', true);
        $this->assertDatabaseCount('biometrico_dispositivos', 0);
    }

    public function test_editar_equipo_conserva_su_ip_y_actualiza_puerto_sin_crear_duplicados(): void
    {
        $device = BiometricoDispositivo::query()->create([
            'department' => 'Pando', 'branch' => 'Anterior', 'ip' => '172.65.26.43', 'port' => 4370,
        ]);
        $this->formularioBiometrico()->call('openEditBiometricoModal', $device->id)
            ->set('deviceBranch', 'Nueva sucursal')->set('devicePort', ' 4371 ')
            ->call('saveBiometrico')->assertHasNoErrors();
        $this->assertDatabaseCount('biometrico_dispositivos', 1);
        $this->assertDatabaseHas('biometrico_dispositivos', ['id' => $device->id, 'branch' => 'Nueva sucursal', 'port' => 4371]);
    }

    public function test_datos_invalidos_muestran_errores_sin_guardar_equipo(): void
    {
        $this->formularioBiometrico()->set('deviceDepartment', '   ')
            ->set('deviceIp', 'http://172.65.26.43:4370')->set('devicePort', '70000')
            ->call('saveBiometrico')->assertHasErrors(['deviceDepartment', 'deviceIp', 'devicePort'])
            ->assertSee('No se pudo guardar. Revisa los siguientes datos:')
            ->assertSee('El puerto debe estar entre 1 y 65535.')
            ->assertSet('showBiometricoModal', true);
        $this->assertDatabaseCount('biometrico_dispositivos', 0);
    }

    public function test_pando_se_registra_y_descarga_desde_su_propia_ip(): void
    {
        config()->set('biometrico.devices', []);
        $user = User::query()->create([
            'name' => 'Admin Pando',
            'email' => 'pando@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo(Permission::findOrCreate('gestionar personal', 'web'));
        BiometricoDispositivo::query()->create([
            'department' => 'Oruro', 'branch' => 'Sucursal Oruro',
            'ip' => '172.65.22.11', 'port' => 4370, 'is_active' => true,
        ]);
        $path = tempnam(sys_get_temp_dir(), 'pando_');
        file_put_contents($path, "Tiempo,Nombre\n14/10/2026,Pando\n");

        $this->mock(ExtraccionBiometricoZkService::class, function ($mock) use ($path) {
            $mock->shouldReceive('exportarExcel')->once()
                ->withArgs(fn(array $device) => $device['ip'] === '172.65.26.43' && $device['branch'] === 'Sucursal Pando')
                ->andReturn($path);
        });

        try {
            Livewire::actingAs($user)->test(ImportarExcelPage::class)
                ->set('deviceDepartment', 'Pando')
                ->set('deviceBranch', 'Sucursal Pando')
                ->set('deviceIp', '172.65.26.43')
                ->set('devicePort', '4370')
                ->call('saveBiometrico')
                ->assertHasNoErrors()
                ->assertSee('Sucursal Pando')
                ->assertSee('Extraer todo')
                ->call('extraerExcelCompleto', 1)
                ->assertFileDownloaded();

            $this->assertDatabaseHas('biometrico_dispositivos', [
                'department' => 'Pando', 'ip' => '172.65.26.43', 'port' => 4370, 'is_active' => true,
            ]);
        } finally {
            @unlink($path);
        }
    }

    public function test_prueba_y_reconexion_no_anuncian_exito_si_el_equipo_no_responde(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'conexion@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo(Permission::findOrCreate('gestionar personal', 'web'));
        $device = [
            'department' => 'Pando',
            'branch' => 'Sucursal Pando',
            'ip' => '172.65.26.43',
            'port' => 4370,
            'connected' => false,
            'last_sync' => 'Desconectado | Tiempo de espera agotado',
        ];

        $this->mock(ConexionBiometricoService::class, function ($mock) use ($device) {
            $mock->shouldReceive('dispositivosConfigurados')->andReturn([$device]);
            $mock->shouldReceive('estadoDispositivos')->with(false)->andReturn([$device]);
            $mock->shouldReceive('probarConexion')->with(0)->twice()->andReturn($device);
        });

        Livewire::actingAs($user)->test(ImportarExcelPage::class)
            ->call('probarConexion', 0)
            ->assertSee('No se pudo conectar con Sucursal Pando')
            ->assertDontSee('Conexion TCP/IP verificada correctamente con')
            ->call('reconectarTodos')
            ->assertSet('reconnectProgress.0.status', 'error');
    }
}
