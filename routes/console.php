<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('biometrico:sync {--force : Reprocesa todas las marcaciones encontradas}', function () {
    $force = (bool) $this->option('force');
    $results = app(\App\Services\SincronizacionBiometricoService::class)->sincronizarTodos($force);

    if ($results === []) {
        $this->warn('No existen biometricos configurados para sincronizar.');

        return 0;
    }

    foreach ($results as $result) {
        $status = strtoupper((string) ($result['status'] ?? 'desconocido'));
        $device = (string) ($result['device'] ?? 'Biometrico');
        $message = (string) ($result['message'] ?? '');
        $imported = (int) ($result['imported'] ?? 0);

        $this->line("[{$status}] {$device} | {$imported} marcaciones | {$message}");
    }

    return collect($results)->contains(fn (array $result) => ($result['status'] ?? null) === 'error')
        ? 1
        : 0;
})->purpose('Sincroniza marcaciones nuevas desde biometricos ZKTeco sucursal por sucursal');

Schedule::command('biometrico:sync')
    ->dailyAt('00:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
