<?php

namespace Tests\Feature;

use App\Services\ConexionBiometricoService;
use App\Services\ExtraccionBiometricoZkService;
use Tests\TestCase;

class DiagnosticoBiometricoTest extends TestCase
{
    private function mockConexion(bool $connected): void
    {
        $this->mock(ConexionBiometricoService::class, function ($mock) use ($connected) {
            $mock->shouldReceive('dispositivosConfigurados')->once()->andReturn([[
                'department' => 'Pando', 'branch' => 'Sucursal Pando',
                'ip' => '172.65.26.43', 'port' => 4370,
                'communication_password' => '12345',
            ]]);
            $mock->shouldReceive('verificarConexion')->once()
                ->withArgs(fn (array $device) => $device['ip'] === '172.65.26.43' && $device['port'] === 4370)
                ->andReturn(['connected' => $connected, 'last_sync' => $connected ? 'Disponible ahora' : 'Desconectado']);
        });
    }

    public function test_diagnostica_el_equipo_registrado_sin_descargar_marcaciones(): void
    {
        $this->mockConexion(true);
        $this->mock(ExtraccionBiometricoZkService::class, function ($mock) {
            $mock->shouldReceive('probarSesion')->once()
                ->withArgs(fn (array $device) => $device['communication_password'] === '12345')
                ->andReturn(['ok' => true, 'message' => 'Sesion ZKTeco abierta correctamente']);
            $mock->shouldNotReceive('extraerMarcaciones');
        });

        $this->artisan('biometrico:diagnosticar', ['ip' => '172.65.26.43', '--sesion' => true])
            ->expectsOutputToContain('Sucursal Pando | 172.65.26.43:4370')
            ->expectsOutputToContain('Sesion ZKTeco abierta correctamente')
            ->assertExitCode(0);
    }

    public function test_fallo_tcp_no_intenta_abrir_sesion(): void
    {
        $this->mockConexion(false);
        $this->mock(ExtraccionBiometricoZkService::class, fn ($mock) => $mock->shouldNotReceive('probarSesion'));

        $this->artisan('biometrico:diagnosticar', ['--sesion' => true])
            ->expectsOutputToContain('Revisa la ruta de red y el firewall')
            ->assertExitCode(1);
    }

    public function test_tcp_abierto_no_oculta_un_fallo_de_sesion(): void
    {
        $this->mockConexion(true);
        $this->mock(ExtraccionBiometricoZkService::class, function ($mock) {
            $mock->shouldReceive('probarSesion')->once()->andThrow(new \RuntimeException('Sesion rechazada'));
        });

        $this->artisan('biometrico:diagnosticar', ['--sesion' => true])
            ->expectsOutputToContain('ZKTeco: Sesion rechazada')
            ->assertExitCode(1);
    }

    public function test_puede_probar_una_ip_aun_no_registrada(): void
    {
        $this->mock(ConexionBiometricoService::class, function ($mock) {
            $mock->shouldReceive('dispositivosConfigurados')->once()->andReturn([]);
            $mock->shouldReceive('verificarConexion')->once()
                ->withArgs(fn (array $device) => $device['ip'] === '172.65.26.43' && $device['port'] === 4371)
                ->andReturn(['connected' => true, 'last_sync' => 'Disponible ahora']);
        });

        $this->artisan('biometrico:diagnosticar', ['ip' => '172.65.26.43', '--port' => '4371'])->assertExitCode(0);
    }

    public function test_rechaza_destinos_invalidos(): void
    {
        $this->artisan('biometrico:diagnosticar', ['ip' => 'incorrecta'])->assertExitCode(1);
        $this->artisan('biometrico:diagnosticar', ['ip' => '172.65.26.43', '--port' => '0'])->assertExitCode(1);
    }
}
