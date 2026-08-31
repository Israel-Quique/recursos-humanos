<?php

namespace App\Services;

use App\Models\BiometricoDispositivo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SincronizacionBiometricoService
{
    public function __construct(
        private ConexionBiometricoService $conexionBiometricoService,
        private ExtraccionBiometricoZkService $extraccionBiometricoZkService,
        private ImportacionBiometricaService $importacionBiometricaService,
    ) {
    }

    public function sincronizarTodos(bool $force = false): array
    {
        $devices = $this->conexionBiometricoService->dispositivosConfigurados();
        $results = [];

        foreach ($devices as $device) {
            $results[] = $this->sincronizarDispositivo($device, $force);
        }

        return $results;
    }

    public function sincronizarDispositivo(array $device, bool $force = false): array
    {
        $storedDevice = $this->resolveStoredDevice($device);
        $syncCutoff = $force ? null : $this->resolveSyncCutoff($storedDevice);

        try {
            $rows = $this->extraccionBiometricoZkService->extraerMarcaciones(
                $device,
                max(5, (int) config('biometrico.sync_timeout', 15))
            );
            $newRows = $this->filterRowsAfter($rows, $syncCutoff);
            $today = now()->toDateString();
            $todayRows = collect($newRows)
                ->filter(fn (array $row) => $this->parseTimestamp($row['fecha_hora'] ?? null)?->toDateString() === $today)
                ->count();

            Log::info('Sincronizacion biometrica completada para dispositivo.', [
                'device_ip' => $device['ip'] ?? null,
                'device_branch' => $device['branch'] ?? null,
                'rows_received' => count($rows),
                'rows_new' => count($newRows),
                'rows_today' => $todayRows,
                'sync_cutoff' => $syncCutoff?->toIso8601String(),
            ]);

            $storedDevice?->forceFill([
                'last_seen_at' => now(),
                'last_error' => null,
            ])->save();

            if ($newRows === []) {
                return [
                    'device' => $device['branch'] ?? ($device['ip'] ?? 'Biometrico'),
                    'status' => 'sin-cambios',
                    'imported' => 0,
                    'message' => 'No existen marcaciones nuevas y validas para sincronizar en la ventana reciente.',
                ];
            }

            $csvPath = $this->extraccionBiometricoZkService->exportarMarcacionesComoCsv(
                $storedDevice ? $this->deviceArrayFromModel($storedDevice) : $device,
                $newRows
            );

            $importacion = $this->importacionBiometricaService->importarMarcacionesBiometrico(
                $storedDevice ? $this->deviceArrayFromModel($storedDevice) : $device,
                $newRows
            );

            $maxTimestamp = collect($newRows)
                ->map(fn(array $row) => $this->parseTimestamp($row['fecha_hora'] ?? null))
                ->filter()
                ->sortBy(fn(Carbon $date) => $date->timestamp)
                ->last();

            if ($storedDevice && $maxTimestamp) {
                $storedDevice->forceFill([
                    'last_synced_mark_at' => $maxTimestamp,
                    'last_seen_at' => now(),
                    'last_error' => null,
                ])->save();
            }

            return [
                'device' => $device['branch'] ?? ($device['ip'] ?? 'Biometrico'),
                'status' => 'sincronizado',
                'imported' => (int) ($importacion->registros_total ?? count($newRows)),
                'message' => 'Marcaciones sincronizadas correctamente. CSV generado en ' . basename($csvPath) . '.',
            ];
        } catch (\Throwable $exception) {
            if ($storedDevice) {
                $storedDevice->forceFill([
                    'last_seen_at' => null,
                    'last_error' => $exception->getMessage(),
                ])->save();
            }

            return [
                'device' => $device['branch'] ?? ($device['ip'] ?? 'Biometrico'),
                'status' => 'error',
                'imported' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }

    private function resolveStoredDevice(array $device): ?BiometricoDispositivo
    {
        $ip = trim((string) ($device['ip'] ?? ''));

        if ($ip === '') {
            return null;
        }

        return BiometricoDispositivo::query()->firstOrCreate(
            ['ip' => $ip],
            [
                'department' => trim((string) ($device['department'] ?? 'Sin departamento')) ?: 'Sin departamento',
                'branch' => trim((string) ($device['branch'] ?? $ip)) ?: $ip,
                'port' => (int) ($device['port'] ?? 4370),
                'connection_mode' => trim((string) ($device['connection_mode'] ?? 'TCP/IP')) ?: 'TCP/IP',
                'communication_password' => trim((string) ($device['communication_password'] ?? '')) ?: null,
                'is_active' => true,
            ]
        );
    }

    private function filterRowsAfter(array $rows, ?Carbon $lastSyncedAt): array
    {
        $validRows = array_values(array_filter($rows, function (array $row) {
            $timestamp = $this->parseTimestamp($row['fecha_hora'] ?? null);

            return $timestamp && !$this->isFutureTimestamp($timestamp);
        }));

        if (!$lastSyncedAt) {
            return $this->sortRowsByTimestamp($validRows);
        }

        return $this->sortRowsByTimestamp(array_values(array_filter($validRows, function (array $row) use ($lastSyncedAt) {
            $timestamp = $this->parseTimestamp($row['fecha_hora'] ?? null);

            return $timestamp && $timestamp->greaterThan($lastSyncedAt);
        })));
    }

    private function sortRowsByTimestamp(array $rows): array
    {
        return collect($rows)
            ->sortBy(function (array $row) {
                return $this->parseTimestamp($row['fecha_hora'] ?? null)?->timestamp ?? 0;
            })
            ->values()
            ->all();
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if (!filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveLastSyncedAt(?BiometricoDispositivo $storedDevice): ?Carbon
    {
        if (!$storedDevice?->last_synced_mark_at) {
            return null;
        }

        if (!$this->isFutureTimestamp($storedDevice->last_synced_mark_at)) {
            return $storedDevice->last_synced_mark_at;
        }

        $storedDevice->forceFill([
            'last_synced_mark_at' => null,
            'last_error' => null,
        ])->save();

        Log::warning('Se reinicio last_synced_mark_at por fecha futura detectada.', [
            'device_id' => $storedDevice->id,
            'device_ip' => $storedDevice->ip,
            'future_sync_at' => $storedDevice->getOriginal('last_synced_mark_at'),
        ]);

        return null;
    }

    private function resolveSyncCutoff(?BiometricoDispositivo $storedDevice): ?Carbon
    {
        $windowStart = now()->copy()->subDays($this->syncWindowDays());
        $lastSyncedAt = $this->resolveLastSyncedAt($storedDevice);

        if (!$lastSyncedAt) {
            return $windowStart;
        }

        // Reprocesar una ventana reciente evita que un timestamp guardado
        // bloquee marcas reales pendientes; la importacion fusiona sin duplicar.
        return $lastSyncedAt->lessThan($windowStart) ? $lastSyncedAt : $windowStart;
    }

    private function syncWindowDays(): int
    {
        return max(1, (int) config('biometrico.sync_window_days', 2));
    }

    private function isFutureTimestamp(Carbon $timestamp): bool
    {
        return $timestamp->greaterThan(now()->copy()->addDay());
    }

    private function deviceArrayFromModel(BiometricoDispositivo $device): array
    {
        return [
            'id' => $device->id,
            'department' => $device->department,
            'branch' => $device->branch,
            'ip' => $device->ip,
            'port' => $device->port,
            'connection_mode' => $device->connection_mode,
            'communication_password' => $device->communication_password,
        ];
    }
}
