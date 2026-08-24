<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ImportacionBiometricaService;

$service = new ImportacionBiometricaService();
$examples = [
    '27/7/2026 08:33',
    '27/7/2026 17:33',
    '28/7/2026 08:30',
    '07/10/2026',
    '07/09/2026',
    '07/08/2026',
    '2026-07-28 08:30:00',
    '44383.35277', // excel serial example
];

foreach ($examples as $ex) {
    $dt = (new ReflectionClass($service))->getMethod('normalizarFechaHora');
    $dt->setAccessible(true);
    $res = $dt->invoke($service, $ex);
    echo str_pad($ex, 25) . ' => ' . ($res ? $res->toDateTimeString() : 'NULL') . PHP_EOL;
}
