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
