<?php

namespace App\Services;

use App\Models\Empleado;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ExtraccionBiometricoZkService
{
    public function probarSesion(array $device): array
    {
        return $this->ejecutarExtraccion($device, true);
    }

    public function exportarExcel(array $device, ?int $year = null, ?int $month = null): string
    {
        $csvPath = $this->exportarFilasComoCsv(
            $device,
            $this->filtrarMarcacionesPorPeriodo(
                $this->extraerMarcaciones($device),
                $year,
                $month
            )
        );
        $excelPath = substr($csvPath, 0, -4).'.xlsx';
        $spreadsheet = new Spreadsheet();
        $handle = null;
        try {
            $handle = fopen($csvPath, 'rb');
            if ($handle === false) {
                throw new \RuntimeException('No se pudo leer las marcaciones para generar el Excel.');
            }
            // Omitir el BOM UTF-8 que utiliza la exportación CSV interna.
            fread($handle, 3);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Marcaciones');
            $rowNumber = 1;
            while (($values = fgetcsv($handle)) !== false) {
                foreach ($values as $column => $value) {
                    // Conservar códigos/tarjetas con ceros iniciales y evitar interpretar fórmulas.
                    $sheet->setCellValueExplicit([$column + 1, $rowNumber], (string) $value, DataType::TYPE_STRING);
                }
                $rowNumber++;
            }
            $sheet->freezePane('A2');
            $sheet->setAutoFilter('A1:K'.($rowNumber - 1));
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);
            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setWidth(25);
            }
            (new Xlsx($spreadsheet))->save($excelPath);
            return $excelPath;
        } catch (\Throwable $exception) {
            @unlink($excelPath);
            throw $exception;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $spreadsheet->disconnectWorksheets();
            @unlink($csvPath);
        }
    }

    public function exportarMarcacionesComoCsv(array $device, array $rows): string
    {
        return $this->exportarFilasComoCsv($device, $rows);
    }

    public function extraerMarcaciones(array $device, ?int $timeout = null, bool $revisarHoraProgramada = false): array
    {
        $payload = $this->ejecutarExtraccion($device, false, $timeout, revisarHoraProgramada: $revisarHoraProgramada);

        return array_values(array_filter($payload['rows'] ?? [], fn($row) => is_array($row)));
    }

    public function sincronizarReloj(array $device): void
    {
        $this->ejecutarExtraccion($device, true, null, true);
    }

    public function estadoReloj(array $device): array
    {
        return Cache::get($this->claveEstadoReloj($device), []);
    }

    private function claveEstadoReloj(array $device): string
    {
        return 'biometrico:hora:'.hash('sha256', ($device['ip'] ?? '').':'.($device['port'] ?? 4370));
    }

    private function guardarEstadoReloj(array $device, array $state): void
    {
        try {
            Cache::forever($this->claveEstadoReloj($device), array_replace($this->estadoReloj($device), $state));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function ejecutarExtraccion(
        array $device,
        bool $probeOnly,
        ?int $requestedTimeout = null,
        bool $sincronizarHora = false,
        bool $revisarHoraProgramada = false,
    ): array
    {
        $script = base_path('scripts/extract_zkteco_attendance.py');

        if (!file_exists($script)) {
            throw new \RuntimeException('No se encontro el script de extraccion del biometrico.');
        }

        $outputPath = storage_path('app/biometrico_extract_' . uniqid() . '.json');
        $clockPath = substr($outputPath, 0, -5).'.clock.json';
        $password = trim((string) ($device['communication_password'] ?? config('biometrico.password', '')));
        $timeout = $probeOnly
            ? 20
            : max(5, $requestedTimeout ?? (int) config('biometrico.export_timeout', 180));
        $lockKey = 'biometrico:extract:' . preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($device['ip'] ?? 'sin-ip'));
        $lock = Cache::lock($lockKey, $timeout + 30);

        if (!$lock->get()) {
            @unlink($outputPath);
            throw new \RuntimeException('El biometrico esta siendo leido por otra sincronizacion. Intenta nuevamente en unos segundos.');
        }

        try {
            $sincronizarHora = $sincronizarHora || ($revisarHoraProgramada
                && (int) ($this->estadoReloj($device)['attempted_at'] ?? 0) <= now()->subHour()->timestamp);
            $process = new Process([
                $this->pythonBinary(),
                $script,
                '--ip',
                (string) ($device['ip'] ?? ''),
                '--port',
                (string) ($device['port'] ?? 4370),
                '--output',
                $outputPath,
                '--timeout',
                (string) $timeout,
                '--socket-timeout',
                (string) ($probeOnly ? 5 : max(1, (int) config('biometrico.socket_timeout', 30))),
                '--password',
                $password !== '' ? $password : '0',
                ...($probeOnly ? ['--probe-only'] : []),
                ...($sincronizarHora ? ['--clock-offset', (string) now()->offset] : []),
            ], base_path(), $this->pythonProcessEnvironment());

            $process->setTimeout($timeout + 15);
            $payload = null;
            $descargaLista = false;
            $salidaProceso = '';
            $limitarAjuste = function (string $tipo, string $fragmento) use ($process, &$descargaLista, &$salidaProceso): void {
                if ($tipo !== Process::OUT || $descargaLista) {
                    return;
                }

                $salidaProceso .= $fragmento;
                if (str_contains($salidaProceso, "MARCACIONES_LISTAS")) {
                    $descargaLista = true;
                    $process->setTimeout(min($process->getTimeout(), microtime(true) - $process->getStartTime() + 7));
                }
            };

            try {
                $process->run($sincronizarHora && !$probeOnly ? $limitarAjuste : null);
            } catch (ProcessTimedOutException) {
                $detalle = trim($process->getErrorOutput());
                Log::warning('Tiempo de espera agotado al extraer biometrico', [
                    'device_ip' => $device['ip'] ?? null,
                    'probe_only' => $probeOnly,
                    'timeout' => $timeout,
                    'detalle' => $detalle,
                ]);
                if ($sincronizarHora && !$probeOnly && is_file($outputPath)) {
                    $payload = json_decode((string) file_get_contents($outputPath), true);
                    $descargaLista = ($payload['ok'] ?? false) === true;
                }
                if (!$descargaLista) {
                    throw new \RuntimeException(
                        'La comunicacion con el biometrico supero el tiempo limite de ' . $timeout . ' segundos.'
                        . ($detalle !== '' ? ' Ultimo progreso: ' . $detalle : ' No se recibio progreso del extractor Python.')
                    );
                }
            }

            Log::info('Extraccion biometrico Python process ejecutado', [
                'probe_only' => $probeOnly,
                'python' => $this->pythonBinary(),
                'device_ip' => $device['ip'] ?? null,
                'device_port' => $device['port'] ?? null,
                'successful' => $process->isSuccessful(),
                'exit_code' => $process->getExitCode(),
                'output' => trim($process->getOutput()),
                'error_output' => trim($process->getErrorOutput()),
            ]);

            if (!$process->isSuccessful() && !$descargaLista) {
                $failurePayload = file_exists($outputPath)
                    ? json_decode((string) file_get_contents($outputPath), true)
                    : null;
                $error = trim($process->getErrorOutput() . ' ' . $process->getOutput());
                @unlink($outputPath);
                throw new \RuntimeException(
                    is_array($failurePayload) && is_string($failurePayload['message'] ?? null)
                        ? $failurePayload['message']
                        : ($error !== '' ? $error : 'No se pudo extraer marcaciones desde el biometrico.')
                );
            }

            if (!file_exists($outputPath)) {
                throw new \RuntimeException('La extraccion no genero un archivo temporal con marcaciones.');
            }

            $payload ??= json_decode((string) file_get_contents($outputPath), true);

            if (!is_array($payload)) {
                throw new \RuntimeException('La respuesta del extractor ZKTeco no es valida.');
            }

            if (($payload['ok'] ?? false) !== true) {
                throw new \RuntimeException((string) ($payload['message'] ?? 'El biometrico rechazo la extraccion.'));
            }

            if ($sincronizarHora) {
                $clock = is_file($clockPath) ? json_decode((string) file_get_contents($clockPath), true) : null;
                $this->guardarEstadoReloj($device, (is_array($clock) ? $clock : ['status' => 'unavailable']) + [
                    'attempted_at' => now()->timestamp,
                ]);
            }

            return $payload;
        } catch (\Throwable $exception) {
            if ($sincronizarHora) {
                $this->guardarEstadoReloj($device, ['status' => 'unavailable']);
            }
            throw $exception;
        } finally {
            optional($lock)->release();
            @unlink($outputPath);
            @unlink($clockPath);
        }
    }

    private function pythonBinary(): string
    {
        $venv = base_path('.venv/Scripts/python.exe');

        if (DIRECTORY_SEPARATOR === '\\' && file_exists($venv)) {
            return $venv;
        }

        return (string) (config('app.python_bin') ?: env('PYTHON_BIN', 'python'));
    }

    private function pythonProcessEnvironment(): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return [];
        }

        $python = $this->pythonBinary();
        $pythonDir = dirname($python);
        $projectDir = base_path();
        $safePath = array_filter(array_unique([
            $pythonDir,
            $projectDir,
            getenv('SystemRoot') ?: 'C:\\Windows',
            (getenv('SystemRoot') ?: 'C:\\Windows') . DIRECTORY_SEPARATOR . 'System32',
            (getenv('SystemRoot') ?: 'C:\\Windows') . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'Wbem',
            (getenv('SystemRoot') ?: 'C:\\Windows') . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'WindowsPowerShell' . DIRECTORY_SEPARATOR . 'v1.0',
        ]));

        return [
            'PATH' => implode(PATH_SEPARATOR, $safePath),
            'SYSTEMROOT' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: (getenv('SystemRoot') ?: 'C:\\Windows'),
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
            'PYTHONHOME' => false,
            'PYTHONPATH' => $projectDir . DIRECTORY_SEPARATOR . '.python-packages',
            'CONDA_DEFAULT_ENV' => false,
            'CONDA_EXE' => false,
            'CONDA_PREFIX' => false,
            'CONDA_PROMPT_MODIFIER' => false,
            'CONDA_PYTHON_EXE' => false,
            'CONDA_SHLVL' => false,
        ];
    }

    private function traducirEstadoHumano(array $row): string
    {
        $punch = trim((string) ($row['punch'] ?? ''));
        $status = $punch !== '' ? $punch : (string) ($row['estado'] ?? '');

        return match ($status) {
            '0' => 'Entrada',
            '1' => 'Salida',
            '2' => 'Salida a descanso',
            '3' => 'Retorno de descanso',
            '4' => 'Entrada extra',
            '5' => 'Salida extra',
            '255' => 'Marcacion general',
            default => $status !== '' ? 'Estado ' . $status : 'Sin estado',
        };
    }

    private function traducirEventoHumano(array $row): string
    {
        $punch = (string) ($row['punch'] ?? '');

        return match ($punch) {
            '0' => 'Registro biometrico',
            '1' => 'Apertura con tarjeta de proximidad',
            '2' => 'Apertura remota',
            '3' => 'Boton de salida',
            '4' => 'Alarma',
            default => $punch !== '' ? 'Evento ' . $punch : 'Sin evento',
        };
    }

    private function traducirVerificacionHumana(array $row): string
    {
        $verification = (string) ($row['verificacion'] ?? '');

        return match ($verification) {
            '0' => 'Contrasena',
            '1' => 'Huella',
            '2' => 'Tarjeta',
            '3' => 'Huella + contrasena',
            '4' => 'Huella + tarjeta',
            '5' => 'Tarjeta + contrasena',
            '6' => 'Tarjeta + huella + contrasena',
            '7' => 'Rostro',
            '8' => 'Rostro + huella',
            '9' => 'Rostro + tarjeta',
            '10' => 'Rostro + contrasena',
            '11' => 'Rostro + tarjeta + huella',
            '12' => 'Rostro + huella + contrasena',
            '13' => 'Rostro + tarjeta + contrasena',
            '14' => 'Solo rostro',
            '15' => 'Tarjeta de proximidad',
            default => $verification !== '' ? 'Metodo ' . $verification : 'No disponible',
        };
    }

    private function filtrarMarcacionesPorPeriodo(array $rows, ?int $year, ?int $month): array
    {
        if (!$year || !$month) {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($year, $month) {
            $fechaHora = $row['fecha_hora'] ?? null;

            if (!$fechaHora) {
                return false;
            }

            try {
                $date = Carbon::parse((string) $fechaHora);
            } catch (\Throwable) {
                return false;
            }

            return (int) $date->format('Y') === $year
                && (int) $date->format('m') === $month;
        }));
    }

    private function exportarFilasComoCsv(array $device, array $rows): string
    {
        if ($rows === []) {
            throw new \RuntimeException('No existen marcaciones del biometrico para el periodo seleccionado.');
        }

        $directory = storage_path('app/exportaciones-biometrico');

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('No se pudo crear la carpeta de exportaciones del biometrico.');
        }

        $timestamp = now()->format('Ymd_His').'_'.bin2hex(random_bytes(4));
        $safeBranch = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($device['branch'] ?? 'biometrico')) ?: 'biometrico';
        $filePath = $directory . DIRECTORY_SEPARATOR . "marcaciones_{$safeBranch}_{$timestamp}.csv";
        $handle = fopen($filePath, 'wb');
        $sucursal = $device['branch'] ?? $device['department'] ?? '';
        $regional = SucursalNormalizer::canonicalKey($sucursal) ?? Str::slug($sucursal);
        $esElAlto = SucursalNormalizer::sedeLaPaz($sucursal) === 'El Alto';
        $employeesByCode = Empleado::query()
            ->whereNotNull('codigo_biometrico')
            ->when(filled($sucursal), fn($query) =>
                $query->where(function ($query) use ($sucursal, $esElAlto) {
                    SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    if ($esElAlto) {
                        $query->orWhere('codigo_biometrico', 'like', 'el-alto:%');
                    }
                }))
            ->get()
            ->keyBy(fn(Empleado $empleado) => trim((string) $empleado->codigo_biometrico));

        if (!$handle) {
            throw new \RuntimeException('No se pudo crear el archivo exportado del biometrico.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Tiempo', 'ID de Usuario', 'Nombre', 'Apellido', 'Numero de tarjeta', 'Dispositivo', 'Punto del evento', 'Verificacion', 'Estado', 'Evento', 'Notas']);

        foreach ($rows as $row) {
            $fechaHora = Carbon::parse((string) ($row['fecha_hora'] ?? now()->toIso8601String()));
            $codigo = trim((string) ($row['codigo'] ?? ''));
            $empleado = $employeesByCode->get($codigo);
            if ($esElAlto) {
                $empleado = $employeesByCode->get('el-alto:'.$codigo)
                    ?? (SucursalNormalizer::sedeLaPaz($empleado?->sucursal) === 'El Alto' ? $empleado : null);
            } else {
                $empleado = $employeesByCode->get($regional.':'.$codigo) ?? $empleado;
            }
            $nombreBiometrico = trim((string) ($row['nombre'] ?? ''));
            $apellidoBiometrico = trim((string) ($row['apellido'] ?? ''));
            $nombreCompletoBiometrico = trim((string) ($row['nombre_completo'] ?? ''));

            if (($nombreBiometrico === '' || $apellidoBiometrico === '') && $nombreCompletoBiometrico !== '') {
                ['nombre' => $nombreBiometricoNormalizado, 'apellido' => $apellidoBiometricoNormalizado] = $this->splitFullName($nombreCompletoBiometrico);
                $nombreBiometrico = $nombreBiometrico !== '' ? $nombreBiometrico : $nombreBiometricoNormalizado;
                $apellidoBiometrico = $apellidoBiometrico !== '' ? $apellidoBiometrico : $apellidoBiometricoNormalizado;
            }

            // El reloj es la fuente principal; el respaldo local debe ser de su sucursal.
            $tieneNombreBiometrico = $nombreBiometrico !== '' || $apellidoBiometrico !== '';
            $nombreExportado = $tieneNombreBiometrico ? $nombreBiometrico : trim((string) ($empleado?->nombre ?? ''));
            $apellidoExportado = $tieneNombreBiometrico ? $apellidoBiometrico : trim((string) ($empleado?->apellido ?? ''));

            $estadoHumano = $this->traducirEstadoHumano($row);
            $eventoHumano = $this->traducirEventoHumano($row);
            $verificacionHumana = $this->traducirVerificacionHumana($row);
            $dispositivo = (string) ($row['dispositivo'] ?? $device['department'] ?? '');
            $puntoEvento = (string) ($row['punto_evento'] ?? $device['branch'] ?? '');

            fputcsv($handle, [
                $fechaHora->format('d/m/Y H:i'),
                $codigo,
                $nombreExportado,
                $apellidoExportado,
                $row['numero_tarjeta'] ?? '',
                $dispositivo,
                $puntoEvento,
                $verificacionHumana,
                $estadoHumano,
                $eventoHumano,
                $row['notas'] ?? '',
            ]);
        }

        fclose($handle);

        return $filePath;
    }

    private function splitFullName(string $fullName): array
    {
        $value = trim($fullName);

        if ($value === '') {
            return ['nombre' => '', 'apellido' => ''];
        }

        $parts = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) <= 1) {
            return ['nombre' => $parts[0] ?? '', 'apellido' => ''];
        }

        if (count($parts) >= 3) {
            return [
                'nombre' => implode(' ', array_slice($parts, 0, -2)),
                'apellido' => implode(' ', array_slice($parts, -2)),
            ];
        }

        return [
            'nombre' => $parts[0],
            'apellido' => $parts[1],
        ];
    }
}
