<?php

namespace App\Services;

use App\Models\BiometricoDispositivo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ConexionBiometricoService
{
    public function dispositivosConfigurados(): array
    {
        return $this->configuredDevices();
    }

    public function diagnostico(): array
    {
        $driver = config('biometrico.driver', 'archivo');

        $supported = [
            'archivo' => 'Importacion manual desde Excel o TXT exportado por el reloj.',
            'api' => 'Consumo de una API o SDK expuesto por el proveedor del biometrico.',
            'sql' => 'Lectura directa de la base de datos donde el biometrico guarda marcaciones.',
            'zk' => 'Conexion directa a dispositivos ZKTeco compatibles por IP/puerto.',
        ];

        return [
            'driver' => $driver,
            'supported' => array_keys($supported),
            'description' => $supported[$driver] ?? 'Driver no reconocido.',
            'is_configured' => match ($driver) {
                'archivo' => true,
                'api', 'sql', 'zk' => filled(config('biometrico.host')),
                default => false,
            },
            'next_steps' => $this->nextSteps($driver),
        ];
    }

    public function estadoDispositivos(): array
    {
        $devices = $this->dispositivosConfigurados();

        if ($devices === []) {
            return [[
                'department' => 'Sin configurar',
                'branch' => 'Biometrico no configurado',
                'ip' => 'N/D',
                'port' => (int) config('biometrico.port', 4370),
                'connected' => false,
                'last_sync' => 'Configure BIOMETRICO_DEVICES o BIOMETRICO_HOST en el entorno.',
            ]];
        }

        return array_map(fn (array $device) => $this->snapshotDeviceStatus($device), $devices);
    }

    public function probarConexion(int $index): array
    {
        $devices = $this->dispositivosConfigurados();

        if (! isset($devices[$index])) {
            throw new \RuntimeException('No se encontro el biometrico seleccionado para probar la conexion.');
        }

        $probe = $this->probeDevice($devices[$index]);
        $this->persistProbeResult($devices[$index], $probe);

        return $probe;
    }

    private function nextSteps(string $driver): array
    {
        return match ($driver) {
            'api' => [
                'Confirmar marca y modelo del biometrico.',
                'Solicitar SDK, API o manual tecnico al proveedor.',
                'Validar autenticacion, formato de marcaciones y frecuencia de sincronizacion.',
            ],
            'sql' => [
                'Confirmar motor de base de datos del biometrico.',
                'Identificar tabla de marcaciones y columnas de empleado, fecha, hora y tipo.',
                'Configurar lectura incremental por ultima marcacion sincronizada.',
            ],
            'zk' => [
                'Confirmar que el equipo sea compatible con protocolo ZKTeco.',
                'Definir IP fija, puerto y credenciales del dispositivo.',
                'Probar extraccion de logs desde un servicio Python dedicado.',
            ],
            default => [
                'Seguir usando la importacion por archivo como respaldo.',
                'Recopilar datos tecnicos del equipo de huella para integrar conexion directa.',
            ],
        };
    }

    private function configuredDevices(): array
    {
        $configuredDevices = $this->devicesFromEnvironment();

        if (Schema::hasTable('biometrico_dispositivos')) {
            $storedDevices = BiometricoDispositivo::query()
                ->where('is_active', true)
                ->orderBy('department')
                ->orderBy('branch')
                ->get()
                ->map(fn (BiometricoDispositivo $device) => [
                    'id' => $device->id,
                    'department' => trim((string) $device->department) ?: 'Sin departamento',
                    'branch' => trim((string) $device->branch) ?: 'Sin sucursal',
                    'ip' => trim((string) $device->ip),
                    'port' => (int) ($device->port ?: config('biometrico.port', 4370)),
                    'connection_mode' => trim((string) $device->connection_mode) ?: 'TCP/IP',
                    'communication_password' => trim((string) ($device->communication_password ?? '')),
                    'last_synced_mark_at' => $device->last_synced_mark_at,
                    'last_seen_at' => $device->last_seen_at,
                    'last_error' => $device->last_error,
                ])
                ->filter(fn (array $device) => $device['ip'] !== '')
                ->values()
                ->all();

            if ($storedDevices !== []) {
                return $this->mergeDevices($storedDevices, $configuredDevices);
            }
        }

        if ($configuredDevices !== []) {
            return $configuredDevices;
        }

        $legacyHost = trim((string) config('biometrico.host'));

        if ($legacyHost === '') {
            return [];
        }

        return [[
            'id' => null,
            'department' => 'General',
            'branch' => 'Biometrico principal',
            'ip' => $legacyHost,
            'port' => (int) config('biometrico.port', 4370),
            'connection_mode' => 'TCP/IP',
            'communication_password' => trim((string) config('biometrico.password', '')),
            'last_synced_mark_at' => null,
            'last_seen_at' => null,
            'last_error' => null,
        ]];
    }

    private function devicesFromEnvironment(): array
    {
        $devices = config('biometrico.devices', []);

        if (is_array($devices) && $devices !== []) {
            return array_values(array_filter(array_map(function ($device) {
                if (! is_array($device)) {
                    return null;
                }

                $host = trim((string) ($device['ip'] ?? $device['host'] ?? ''));
                if ($host === '') {
                    return null;
                }

                return [
                    'id' => null,
                    'department' => trim((string) ($device['department'] ?? 'Sin departamento')) ?: 'Sin departamento',
                    'branch' => trim((string) ($device['branch'] ?? $device['name'] ?? $host)) ?: $host,
                    'ip' => $host,
                    'port' => (int) ($device['port'] ?? config('biometrico.port', 4370) ?? 4370),
                    'connection_mode' => trim((string) ($device['connection_mode'] ?? 'TCP/IP')) ?: 'TCP/IP',
                    'communication_password' => trim((string) ($device['communication_password'] ?? '')),
                    'last_synced_mark_at' => $device['last_synced_mark_at'] ?? null,
                    'last_seen_at' => $device['last_seen_at'] ?? null,
                    'last_error' => $device['last_error'] ?? null,
                ];
            }, $devices)));
        }

        return [];
    }

    private function mergeDevices(array $storedDevices, array $configuredDevices): array
    {
        return collect($storedDevices)
            ->concat($configuredDevices)
            ->unique(fn (array $device) => trim((string) ($device['ip'] ?? '')))
            ->values()
            ->all();
    }

    private function snapshotDeviceStatus(array $device): array
    {
        $liveProbe = $this->probeDevice($device, $this->statusProbeTimeout());

        if ($liveProbe['connected']) {
            return $liveProbe;
        }

        $lastSeenAt = $device['last_seen_at'] ?? null;
        $lastSyncedAt = $device['last_synced_mark_at'] ?? null;
        $lastError = trim((string) ($device['last_error'] ?? ''));
        $hasPositiveActivity = (bool) ($lastSeenAt || $lastSyncedAt);

        if (str_contains(strtolower($lastError), 'fecha futura')) {
            $lastError = '';
        }

        if ($lastError !== '') {
            $connected = false;
            $label = 'Desconectado | '.$lastError;
        } elseif ($lastSeenAt) {
            $connected = true;
            $label = 'Ultima verificacion '.Carbon::parse($lastSeenAt)->format('d/m/Y H:i');
        } elseif ($lastSyncedAt) {
            $connected = true;
            $label = 'Ultima sincronizacion '.Carbon::parse($lastSyncedAt)->format('d/m/Y H:i');
        } else {
            $connected = false;
            $label = $hasPositiveActivity ? 'Sin estado disponible' : 'Pendiente de prueba de conexion';
        }

        return [
            'id' => $device['id'] ?? null,
            'department' => $device['department'],
            'branch' => $device['branch'],
            'ip' => $device['ip'],
            'port' => (int) ($device['port'] ?? 4370),
            'connection_mode' => $device['connection_mode'] ?? 'TCP/IP',
            'connected' => $connected,
            'last_sync' => $label,
        ];
    }

    private function probeDevice(array $device): array
    {
        $ip = $device['ip'];
        $port = max(1, (int) ($device['port'] ?? 4370));
        $timeout = max(1, (float) config('biometrico.timeout', 2));
        return $this->probeDeviceTcp($device, $ip, $port, $timeout);
    }

    private function probeDeviceTcp(array $device, string $ip, int $port, float $timeout): array
    {
        $start = microtime(true);
        $socket = @fsockopen($ip, $port, $errorNumber, $errorMessage, $timeout);
        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if (is_resource($socket)) {
            fclose($socket);

            return [
                'id' => $device['id'] ?? null,
                'department' => $device['department'],
                'branch' => $device['branch'],
                'ip' => $ip,
                'port' => $port,
                'connection_mode' => $device['connection_mode'] ?? 'TCP/IP',
                'connected' => true,
                'last_sync' => 'Disponible ahora'.($latencyMs > 0 ? ' | '.$latencyMs.' ms' : ''),
            ];
        }

        $reason = trim($errorMessage ?? '');

        return [
            'id' => $device['id'] ?? null,
            'department' => $device['department'],
            'branch' => $device['branch'],
            'ip' => $ip,
            'port' => $port,
            'connection_mode' => $device['connection_mode'] ?? 'TCP/IP',
            'connected' => false,
            'last_sync' => $reason !== ''
                ? 'Desconectado | '.$reason
                : 'Desconectado | Sin respuesta en '.Carbon::now()->format('H:i:s'),
        ];
    }

    private function statusProbeTimeout(): float
    {
        return max(0.5, (float) config('biometrico.status_timeout', 1));
    }

    private function persistProbeResult(array $device, array $probe): void
    {
        $deviceId = $device['id'] ?? null;
        $ip = trim((string) ($device['ip'] ?? ''));

        if (! $deviceId && $ip === '') {
            return;
        }

        $storedDevice = $deviceId
            ? BiometricoDispositivo::query()->find($deviceId)
            : BiometricoDispositivo::query()->where('ip', $ip)->first();

        if (! $storedDevice) {
            return;
        }

        $storedDevice->forceFill([
            'last_seen_at' => $probe['connected'] ? now() : null,
            'last_error' => $probe['connected'] ? null : ($probe['last_sync'] ?? 'No fue posible conectar'),
        ])->save();
    }
}
