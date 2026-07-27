<?php

namespace App\Services;

use App\Models\BiometricoDispositivo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        $lastSyncedAt = $force ? null : $storedDevice?->last_synced_mark_at;

        try {
            $rows = $this->extraccionBiometricoZkService->extraerMarcaciones($device);
            $newRows = $this->filterRowsAfter($rows, $lastSyncedAt);

            $storedDevice?->forceFill([
                'last_seen_at' => now(),
                'last_error' => null,
            ])->save();

            if ($newRows === []) {
                return [
                    'device' => $device['branch'] ?? ($device['ip'] ?? 'Biometrico'),
                    'status' => 'sin-cambios',
                    'imported' => 0,
                    'message' => 'No existen marcaciones nuevas para sincronizar.',
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
                ->map(fn (array $row) => $this->parseTimestamp($row['fecha_hora'] ?? null))
                ->filter()
                ->sortBy(fn (Carbon $date) => $date->timestamp)
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
                'message' => 'Marcaciones sincronizadas correctamente. CSV generado en '.basename($csvPath).'.',
            ];
        } catch (\Throwable $exception) {
            if ($storedDevice) {
                $storedDevice->forceFill([
                    'last_seen_at' => now(),
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
        if (! $lastSyncedAt) {
            return $this->sortRowsByTimestamp($rows);
        }

        return $this->sortRowsByTimestamp(array_values(array_filter($rows, function (array $row) use ($lastSyncedAt) {
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
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
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
