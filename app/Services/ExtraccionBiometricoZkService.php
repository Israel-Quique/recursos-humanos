<?php

namespace App\Services;

use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ExtraccionBiometricoZkService
{
    public function probarSesion(array $device): array
    {
        return $this->ejecutarExtraccion($device, true);
    }

    public function exportarExcel(array $device, ?int $year = null, ?int $month = null): string
    {
        return $this->exportarFilasComoCsv(
            $device,
            $this->filtrarMarcacionesPorPeriodo(
                $this->extraerMarcaciones($device),
                $year,
                $month
            )
        );
    }

    public function exportarMarcacionesComoCsv(array $device, array $rows): string
    {
        return $this->exportarFilasComoCsv($device, $rows);
    }

    public function extraerMarcaciones(array $device): array
    {
        $payload = $this->ejecutarExtraccion($device, false);

        return array_values(array_filter($payload['rows'] ?? [], fn ($row) => is_array($row)));
    }

    private function ejecutarExtraccion(array $device, bool $probeOnly): array
    {
        $script = base_path('scripts/extract_zkteco_attendance.py');

        if (! file_exists($script)) {
            throw new \RuntimeException('No se encontro el script de extraccion del biometrico.');
        }

        $outputPath = storage_path('app/biometrico_extract_'.uniqid().'.json');
        $password = trim((string) config('biometrico.password', ''));

        $process = new Process([
            $this->pythonBinary(),
            $script,
            '--ip', (string) ($device['ip'] ?? ''),
            '--port', (string) ($device['port'] ?? 4370),
            '--output', $outputPath,
            '--timeout', (string) max((int) config('biometrico.timeout', 8), 8),
            '--password', $password !== '' ? $password : '0',
            ...($probeOnly ? ['--probe-only'] : []),
        ], base_path(), $this->pythonProcessEnvironment());

        $process->setTimeout($probeOnly ? 20 : 60);
        $process->run();

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

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput().' '.$process->getOutput());
            @unlink($outputPath);
            throw new \RuntimeException($error !== '' ? $error : 'No se pudo extraer marcaciones desde el biometrico.');
        }

        if (! file_exists($outputPath)) {
            throw new \RuntimeException('La extraccion no genero un archivo temporal con marcaciones.');
        }

        $payload = json_decode((string) file_get_contents($outputPath), true);
        @unlink($outputPath);

        if (! is_array($payload)) {
            throw new \RuntimeException('La respuesta del extractor ZKTeco no es valida.');
        }

        if (($payload['ok'] ?? false) !== true) {
            throw new \RuntimeException((string) ($payload['message'] ?? 'El biometrico rechazo la extraccion.'));
        }

        return $payload;
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
            (getenv('SystemRoot') ?: 'C:\\Windows').DIRECTORY_SEPARATOR.'System32',
            (getenv('SystemRoot') ?: 'C:\\Windows').DIRECTORY_SEPARATOR.'System32'.DIRECTORY_SEPARATOR.'Wbem',
            (getenv('SystemRoot') ?: 'C:\\Windows').DIRECTORY_SEPARATOR.'System32'.DIRECTORY_SEPARATOR.'WindowsPowerShell'.DIRECTORY_SEPARATOR.'v1.0',
        ]));

        return [
            'PATH' => implode(PATH_SEPARATOR, $safePath),
            'SYSTEMROOT' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: (getenv('SystemRoot') ?: 'C:\\Windows'),
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
            'PYTHONHOME' => false,
            'PYTHONPATH' => false,
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
        $status = (string) ($row['estado'] ?? '');

        return match ($status) {
            '0' => 'Entrada',
            '1' => 'Salida',
            '2' => 'Salida a descanso',
            '3' => 'Retorno de descanso',
            '4' => 'Entrada extra',
            '5' => 'Salida extra',
            default => $status !== '' ? 'Estado '.$status : 'Sin estado',
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
            default => $punch !== '' ? 'Evento '.$punch : 'Sin evento',
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
            default => $verification !== '' ? 'Metodo '.$verification : 'No disponible',
        };
    }

    private function filtrarMarcacionesPorPeriodo(array $rows, ?int $year, ?int $month): array
    {
        if (! $year || ! $month) {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($year, $month) {
            $fechaHora = $row['fecha_hora'] ?? null;

            if (! $fechaHora) {
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

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException('No se pudo crear la carpeta de exportaciones del biometrico.');
        }

        $timestamp = now()->format('Ymd_His');
        $safeBranch = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($device['branch'] ?? 'biometrico')) ?: 'biometrico';
        $filePath = $directory.DIRECTORY_SEPARATOR."marcaciones_{$safeBranch}_{$timestamp}.csv";
        $handle = fopen($filePath, 'wb');
        $employeesByCode = Empleado::query()
            ->whereNotNull('codigo_biometrico')
            ->get()
            ->keyBy(fn (Empleado $empleado) => trim((string) $empleado->codigo_biometrico));

        if (! $handle) {
            throw new \RuntimeException('No se pudo crear el archivo exportado del biometrico.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Tiempo', 'ID de Usuario', 'Nombre', 'Apellido', 'Numero de tarjeta', 'Dispositivo', 'Punto del evento', 'Verificacion', 'Estado', 'Evento', 'Notas']);

        foreach ($rows as $row) {
            $fechaHora = Carbon::parse((string) ($row['fecha_hora'] ?? now()->toIso8601String()));
            $codigo = trim((string) ($row['codigo'] ?? ''));
            $empleado = $employeesByCode->get($codigo);
            $estadoHumano = $this->traducirEstadoHumano($row);
            $eventoHumano = $this->traducirEventoHumano($row);
            $verificacionHumana = $this->traducirVerificacionHumana($row);
            $dispositivo = (string) ($row['dispositivo'] ?? $device['department'] ?? '');
            $puntoEvento = (string) ($row['punto_evento'] ?? $device['branch'] ?? '');

            fputcsv($handle, [
                $fechaHora->format('d/m/Y H:i'),
                $codigo,
                $empleado?->nombre ?? '',
                $empleado?->apellido ?? '',
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
}
