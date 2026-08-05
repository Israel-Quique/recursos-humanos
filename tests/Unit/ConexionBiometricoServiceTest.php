<?php

namespace Tests\Unit;

use App\Services\ConexionBiometricoService;
use Tests\TestCase;

class ConexionBiometricoServiceTest extends TestCase
{
    public function test_estado_dispositivos_prioriza_estado_positivo_si_hay_actividad_reciente_y_error_historico(): void
    {
        config()->set('biometrico.devices', [[
            'department' => 'Administracion',
            'branch' => 'Santa Cruz',
            'ip' => '172.65.21.11',
            'port' => 4370,
            'connection_mode' => 'TCP/IP',
            'last_seen_at' => now()->subMinute()->toDateTimeString(),
            'last_error' => 'Se detecto una fecha futura en la ultima sincronizacion y fue reiniciada automaticamente.',
        ]]);

        $service = app(ConexionBiometricoService::class);
        $result = $service->estadoDispositivos();

        $this->assertCount(1, $result);
        $this->assertTrue($result[0]['connected']);
        $this->assertStringContainsString('Ultima verificacion', $result[0]['last_sync']);
    }
}
