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

    return collect($results)->contains(fn(array $result) => ($result['status'] ?? null) === 'error')
        ? 1
        : 0;
})->purpose('Sincroniza marcaciones nuevas desde biometricos ZKTeco sucursal por sucursal');

Schedule::command('biometrico:sync')
    ->everyMinute()
    ->timezone(config('app.timezone'))
    ->withoutOverlapping(10);

Artisan::command('biometrico:diagnosticar {ip? : IP del equipo; sin IP revisa los registrados} {--port= : Puerto para una IP especifica} {--sesion : Comprueba tambien la sesion ZKTeco sin descargar marcaciones}', function () {
    $ip = $this->argument('ip');
    $port = $this->option('port');

    if (($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP))
        || ($port !== null && (!$ip || !filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]])))) {
        $this->error('Indica una IP valida y un puerto entre 1 y 65535. --port requiere una IP.');
        return 1;
    }

    $conexion = app(\App\Services\ConexionBiometricoService::class);
    $devices = $conexion->dispositivosConfigurados();

    if ($ip !== null) {
        $device = collect($devices)->firstWhere('ip', $ip)
            ?? ['department' => 'Diagnostico', 'branch' => $ip, 'ip' => $ip, 'port' => 4370];
        if ($port !== null) {
            $device['port'] = (int) $port;
        }
        $devices = [$device];
    }

    $this->info('Prueba desde el servidor: ' . gethostname() . ' | PHP ' . PHP_VERSION . ' | ' . PHP_OS_FAMILY);

    if ($devices === []) {
        $this->warn('No hay biometricos registrados en este servidor. Agregalos desde /importar o indica una IP.');
        return 1;
    }

    $failed = false;
    foreach ($devices as $device) {
        $this->line($device['branch'] . ' | ' . $device['ip'] . ':' . $device['port']);
        $probe = $conexion->verificarConexion($device);
        $this->line('TCP: ' . $probe['last_sync']);
        if (!$probe['connected']) {
            $failed = true;
            $this->warn('Revisa la ruta de red y el firewall entre ESTE servidor y el biometrico.');
            continue;
        }
        if ($this->option('sesion')) {
            try {
                $result = app(\App\Services\ExtraccionBiometricoZkService::class)->probarSesion($device);
                $this->info('ZKTeco: ' . ($result['message'] ?? 'Sesion abierta correctamente.'));
            } catch (\Throwable $exception) {
                $failed = true;
                $this->error('ZKTeco: ' . $exception->getMessage());
                $this->warn('Verifica Python, el paquete pyzk y la contrasena de comunicacion del equipo.');
            }
        }
    }

    return $failed ? 1 : 0;
})->purpose('Diagnostica desde este servidor la red y opcionalmente la sesion ZKTeco, sin importar marcaciones');

