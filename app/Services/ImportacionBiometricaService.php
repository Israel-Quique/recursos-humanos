<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Importacion;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportacionBiometricaService
{
    private const BIOMETRICO_CHUNK_SIZE = 500;

    public function importarArchivo(string $rutaArchivo, string $nombreArchivo, ?User $usuario = null, ?string $rutaRelativa = null): Importacion
    {
        $resultado = $this->procesarPython($rutaArchivo);

        $importacion = Importacion::query()->create([
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaRelativa ?: $rutaArchivo,
            'fecha_operativa' => now()->toDateString(),
            'registros_total' => (int) ($resultado['summary']['valid_rows'] ?? 0),
            'empleados_detectados' => (int) ($resultado['summary']['employees'] ?? 0),
            'estado' => 'procesando',
            'created_by' => $usuario?->id,
        ]);

        DB::beginTransaction();

        try {
            $resumen = $this->persistirMarcas($importacion, collect($resultado['marks'] ?? []), $usuario);

            $importacion->update([
                'registros_generados' => $resumen['registros_generados'],
                'empleados_detectados' => $resumen['empleados_detectados'],
                'mensaje_error' => null,
                'estado' => 'completado',
                'resumen_json' => $resumen,
            ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            $importacion->update([
                'estado' => 'error',
                'mensaje_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $importacion->fresh();
    }

    public function importarMarcacionesBiometrico(array $device, array $rows, ?User $usuario = null): Importacion
    {
        $this->asegurarMemoriaImportacion();

        $nombreArchivo = 'sync_biometrico_'.preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($device['branch'] ?? 'equipo')).'_'.now()->format('Ymd_His').'.json';

        $importacion = Importacion::query()->create([
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => 'sync://'.trim((string) ($device['ip'] ?? 'sin-ip')),
            'fecha_operativa' => now()->toDateString(),
            'registros_total' => 0,
            'empleados_detectados' => 0,
            'estado' => 'procesando',
            'created_by' => $usuario?->id,
        ]);

        $resumenGlobal = [
            'registros_generados' => 0,
            'registros_actualizados' => 0,
            'empleados_detectados_ids' => [],
            'empleados_creados_ids' => [],
            'olvidos_marcacion' => 0,
            'marcas_omitidas' => 0,
            'empleados_no_registrados' => [],
        ];
        $procesados = 0;
        $registrosTotales = 0;
        $uniqueCodes = [];
        $chunkRows = [];

        try {
            foreach ($rows as $row) {
                if (! is_array($row) || ! filled($row['fecha_hora'] ?? null)) {
                    continue;
                }

                $registrosTotales++;
                $codigo = trim((string) ($row['codigo'] ?? ''));

                if ($codigo !== '') {
                    $uniqueCodes[$codigo] = true;
                }

                $chunkRows[] = $row;

                if (count($chunkRows) >= self::BIOMETRICO_CHUNK_SIZE) {
                    $procesados += $this->procesarChunkBiometrico($importacion, $device, $chunkRows, $usuario, $resumenGlobal, $registrosTotales, count($uniqueCodes));
                    $chunkRows = [];
                    gc_collect_cycles();
                }
            }

            if ($chunkRows !== []) {
                $procesados += $this->procesarChunkBiometrico($importacion, $device, $chunkRows, $usuario, $resumenGlobal, $registrosTotales, count($uniqueCodes));
                $chunkRows = [];
                gc_collect_cycles();
            }
        } catch (\Throwable $exception) {
            $importacion->update([
                'estado' => 'error',
                'mensaje_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $resumenFinal = [
            'registros_generados' => $resumenGlobal['registros_generados'],
            'registros_actualizados' => $resumenGlobal['registros_actualizados'],
            'empleados_detectados' => count($resumenGlobal['empleados_detectados_ids']),
            'empleados_creados' => count($resumenGlobal['empleados_creados_ids']),
            'olvidos_marcacion' => $resumenGlobal['olvidos_marcacion'],
            'marcas_omitidas' => $resumenGlobal['marcas_omitidas'],
            'empleados_no_registrados' => array_slice($resumenGlobal['empleados_no_registrados'], 0, 10),
            'sync_device_ip' => $device['ip'] ?? null,
            'sync_device_branch' => $device['branch'] ?? null,
            'sync_mode' => 'automatico',
            'chunk_size' => self::BIOMETRICO_CHUNK_SIZE,
        ];

        $importacion->update([
            'registros_total' => $registrosTotales,
            'registros_generados' => $resumenFinal['registros_generados'],
            'empleados_detectados' => $resumenFinal['empleados_detectados'],
            'mensaje_error' => null,
            'estado' => 'completado',
            'resumen_json' => $resumenFinal,
        ]);

        return $importacion->fresh();
    }

    private function procesarChunkBiometrico(
        Importacion $importacion,
        array $device,
        array $chunkRows,
        ?User $usuario,
        array &$resumenGlobal,
        int $registrosTotales,
        int $empleadosTotales
    ): int {
        $marks = collect($chunkRows)
            ->map(fn (array $row) => $this->normalizarMarcaDesdeBiometrico($device, $row))
            ->values();

        DB::beginTransaction();

        try {
            $resumenChunk = $this->persistirMarcas($importacion, $marks, $usuario);
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $procesadosChunk = $marks->count();
        $resumenGlobal['registros_generados'] += (int) ($resumenChunk['registros_generados'] ?? 0);
        $resumenGlobal['registros_actualizados'] += (int) ($resumenChunk['registros_actualizados'] ?? 0);
        $resumenGlobal['olvidos_marcacion'] += (int) ($resumenChunk['olvidos_marcacion'] ?? 0);
        $resumenGlobal['marcas_omitidas'] += (int) ($resumenChunk['marcas_omitidas'] ?? 0);
        $resumenGlobal['empleados_detectados_ids'] = array_values(array_unique([
            ...$resumenGlobal['empleados_detectados_ids'],
            ...($resumenChunk['empleados_detectados_ids'] ?? []),
        ]));
        $resumenGlobal['empleados_creados_ids'] = array_values(array_unique([
            ...$resumenGlobal['empleados_creados_ids'],
            ...($resumenChunk['empleados_creados_ids'] ?? []),
        ]));
        $resumenGlobal['empleados_no_registrados'] = array_values(array_unique([
            ...$resumenGlobal['empleados_no_registrados'],
            ...($resumenChunk['empleados_no_registrados'] ?? []),
        ]));

        $importacion->update([
            'registros_total' => $registrosTotales,
            'registros_generados' => $resumenGlobal['registros_generados'],
            'empleados_detectados' => max($empleadosTotales, count($resumenGlobal['empleados_detectados_ids'])),
            'mensaje_error' => null,
            'estado' => 'procesando',
            'resumen_json' => [
                'procesados' => min($registrosTotales, $procesadosChunk + $resumenGlobal['registros_actualizados'] + $resumenGlobal['registros_generados']),
                'pendientes' => 0,
                'registros_generados' => $resumenGlobal['registros_generados'],
                'registros_actualizados' => $resumenGlobal['registros_actualizados'],
                'empleados_detectados' => count($resumenGlobal['empleados_detectados_ids']),
                'empleados_creados' => count($resumenGlobal['empleados_creados_ids']),
                'olvidos_marcacion' => $resumenGlobal['olvidos_marcacion'],
                'marcas_omitidas' => $resumenGlobal['marcas_omitidas'],
                'empleados_no_registrados' => array_slice($resumenGlobal['empleados_no_registrados'], 0, 10),
                'sync_device_ip' => $device['ip'] ?? null,
                'sync_device_branch' => $device['branch'] ?? null,
                'sync_mode' => 'automatico',
                'chunk_size' => self::BIOMETRICO_CHUNK_SIZE,
            ],
        ]);

        unset($marks);

        return $procesadosChunk;
    }

    public function procesarPython(string $rutaArchivo): array
    {
        return $this->procesarArchivoBiometrico($rutaArchivo);
    }

    private function persistirMarcas(Importacion $importacion, Collection $marks, ?User $usuario = null): array
    {
        $agrupados = $marks
            ->filter(fn (array $mark) => filled($mark['fecha_hora'] ?? null))
            ->groupBy(function (array $mark) {
                $fecha = Carbon::parse($mark['fecha_hora'])->toDateString();
                $codigo = trim((string) ($mark['codigo'] ?? ''));
                $nombre = $this->nombrePlanoDesdeFila($mark);

                return md5($fecha.'|'.$codigo.'|'.$nombre);
            });

        $registrosGenerados = 0;
        $empleadosDetectados = collect();
        $olvidosMarcacion = 0;
        $registrosActualizados = 0;
        $marcasOmitidas = 0;
        $empleadosNoRegistrados = collect();
        $empleadosCreados = collect();

        foreach ($agrupados as $grupo) {
            $primeraMarca = $grupo->first();
            ['empleado' => $empleado, 'created' => $created] = $this->resolverEmpleado($primeraMarca, $usuario);

            if (! $empleado) {
                $marcasOmitidas += $grupo->count();
                $empleadosNoRegistrados->push($this->descriptorEmpleadoNoRegistrado($primeraMarca));
                continue;
            }

            $empleadosDetectados->push($empleado->id);
            if ($created) {
                $empleadosCreados->push($empleado->id);
            }

            $horas = $grupo
                ->map(function (array $mark) {
                    return [
                        'fecha_hora' => Carbon::parse($mark['fecha_hora']),
                        'estado' => $this->normalizarTexto((string) ($mark['datos_originales']['Estado'] ?? $mark['datos_originales']['estado'] ?? '')),
                        'estado_original' => $this->valorFilaFlexible($mark['datos_originales'] ?? [], ['Estado', 'estado']) ?: 'Sin estado',
                        'verificacion' => $this->valorFilaFlexible($mark['datos_originales'] ?? [], ['Verificacion', 'Verificación', 'verificacion']) ?: 'Sin verificacion',
                        'evento' => $this->valorFilaFlexible($mark['datos_originales'] ?? [], ['Evento', 'evento']) ?: 'Sin evento',
                    ];
                })
                ->sortBy(fn (array $mark) => $mark['fecha_hora']->timestamp)
                ->values();

            if ($horas->isEmpty()) {
                continue;
            }

            [$entrada, $salida] = $this->resolverHorasJornada($empleado, $horas);
            $ultimaMarca = $horas->last();

            if (! $salida) {
                $olvidosMarcacion++;
            }

            $fechaOperativa = $horas->first()['fecha_hora']->copy()->startOfDay();

            $registro = RegistroAsistencia::query()
                ->where('empleado_id', $empleado->id)
                ->whereDate('fecha', $fechaOperativa->toDateString())
                ->first();

            if (! $registro) {
                $registro = new RegistroAsistencia([
                    'empleado_id' => $empleado->id,
                    'fecha' => $fechaOperativa,
                ]);
            }

            // Fusionar con registro existente: no sobreescribir con nulos y
            // elegir la entrada más temprana y la salida más tardía.
            $mergedEntrada = $this->minTime($registro->hora_entrada ?? null, $entrada);
            $mergedSalida = $this->maxTime($registro->hora_salida ?? null, $salida);

            if ($this->sameTime($mergedEntrada, $mergedSalida)) {
                $mergedSalida = null;
            }

            $registro->fill([
                'empleado_id' => $empleado->id,
                'importacion_id' => $importacion->id,
                'hora_entrada' => $mergedEntrada,
                'hora_salida' => $mergedSalida,
                'tipo_verificacion' => $ultimaMarca['verificacion'] ?? $registro->tipo_verificacion ?? null,
                'estado_marcacion' => $this->resolverEstadoMarcacionHumano($ultimaMarca) ?: ($registro->estado_marcacion ?? null),
                'evento_biometrico' => $ultimaMarca['evento'] ?? $registro->evento_biometrico ?? null,
                'observacion' => $this->observacionDesdeFila($primeraMarca) ?: $registro->observacion,
                'created_by' => $registro->exists ? $registro->created_by : $usuario?->id,
                'updated_by' => $registro->exists ? $usuario?->id : null,
            ]);

            $registro->save();

            if ($registro->wasRecentlyCreated) {
                $registrosGenerados++;
            } else {
                $registrosActualizados++;
            }
        }

        return [
            'registros_generados' => $registrosGenerados,
            'registros_actualizados' => $registrosActualizados,
            'empleados_detectados' => $empleadosDetectados->unique()->count(),
            'empleados_creados' => $empleadosCreados->unique()->count(),
            'empleados_detectados_ids' => $empleadosDetectados->unique()->values()->all(),
            'empleados_creados_ids' => $empleadosCreados->unique()->values()->all(),
            'olvidos_marcacion' => $olvidosMarcacion,
            'marcas_omitidas' => $marcasOmitidas,
            'empleados_no_registrados' => $empleadosNoRegistrados->filter()->unique()->values()->take(10)->all(),
        ];
    }

    private function minTime(?string $a, ?string $b): ?string
    {
        if (blank($a)) {
            return $b ?: null;
        }

        if (blank($b)) {
            return $a ?: null;
        }

        $ca = $this->parseTimeStringToCarbon($a);
        $cb = $this->parseTimeStringToCarbon($b);

        if (! $ca) {
            return $b;
        }

        if (! $cb) {
            return $a;
        }

        return $ca->lessThanOrEqualTo($cb) ? $ca->format('H:i:s') : $cb->format('H:i:s');
    }

    private function maxTime(?string $a, ?string $b): ?string
    {
        if (blank($a)) {
            return $b ?: null;
        }

        if (blank($b)) {
            return $a ?: null;
        }

        $ca = $this->parseTimeStringToCarbon($a);
        $cb = $this->parseTimeStringToCarbon($b);

        if (! $ca) {
            return $b;
        }

        if (! $cb) {
            return $a;
        }

        return $ca->greaterThanOrEqualTo($cb) ? $ca->format('H:i:s') : $cb->format('H:i:s');
    }

    private function parseTimeStringToCarbon(?string $time): ?Carbon
    {
        if (blank($time)) {
            return null;
        }

        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $time);
            } catch (\Exception) {
                // continuar
            }
        }

        // Intentar parseo liberal
        try {
            return Carbon::parse($time);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolverEmpleado(array $mark, ?User $usuario = null): array
    {
        $codigo = trim((string) ($mark['codigo'] ?? ''));
        $datosOriginales = $mark['datos_originales'] ?? [];
        $nombreOriginal = $this->nombreCompletoDesdeFila($datosOriginales);

        if ($codigo !== '') {
            $existingByCode = Empleado::query()->withTrashed()->where('codigo_biometrico', $codigo)->first();
            if ($existingByCode) {
                return ['empleado' => $this->actualizarEmpleadoDesdeMarca($this->restaurarEmpleadoSiEliminado($existingByCode), $mark), 'created' => false];
            }
        }

        if ($nombreOriginal !== '') {
            $normalizedName = $this->normalizarTexto($nombreOriginal);
            $existingByName = Empleado::query()
                ->withTrashed()
                ->get()
                ->first(fn (Empleado $empleado) => $this->normalizarTexto($empleado->nombre_completo) === $normalizedName);

            if ($existingByName) {
                return ['empleado' => $this->actualizarEmpleadoDesdeMarca($this->restaurarEmpleadoSiEliminado($existingByName), $mark), 'created' => false];
            }
        }

        $empleadoCreado = $this->crearEmpleadoDesdeMarca($mark, $usuario);

        return [
            'empleado' => $empleadoCreado,
            'created' => $empleadoCreado !== null,
        ];
    }

    private function nombrePlanoDesdeFila(array $mark): string
    {
        return $this->normalizarTexto($this->nombreCompletoDesdeFila($mark['datos_originales'] ?? []));
    }

    private function archivoOrigenDesdeFila(array $mark): string
    {
        return (string) (($mark['datos_originales']['Archivo'] ?? $mark['datos_originales']['archivo'] ?? 'planilla biometrica'));
    }

    private function resolverSucursal(array $fila): string
    {
        $sucursal = $this->valorFilaFlexible($fila, [
            'Sucursal',
            'sucursal',
            'Regional',
            'regional',
            'Dispositivo',
            'dispositivo',
            'Punto del evento',
            'Punto de evento',
            'punto del evento',
        ]);

        if ($sucursal) {
            return $sucursal;
        }

        $departamento = $this->valorFilaFlexible($fila, ['Departamento', 'departamento', 'Ciudad', 'ciudad']);

        return $departamento ?: 'Sin sucursal asignada';
    }

    private function normalizarTexto(string $texto): string
    {
        return Str::of($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function procesarArchivoBiometrico(string $rutaArchivo): array
    {
        if (! file_exists($rutaArchivo)) {
            throw new \RuntimeException('No se encontro el archivo biometrico en disco.');
        }

        $extension = strtolower((string) pathinfo($rutaArchivo, PATHINFO_EXTENSION));

        $rows = match ($extension) {
            'csv' => $this->leerCsv($rutaArchivo),
            'xlsx', 'xls' => $this->leerSpreadsheet($rutaArchivo),
            default => throw new \RuntimeException('Formato de archivo no soportado para importacion biometrica.'),
        };

        $marks = [];
        $employeeKeys = [];

        foreach ($rows as $row) {
            $fechaHora = $this->resolverFechaHoraFila($row);

            if (! $fechaHora) {
                continue;
            }

            $codigo = trim((string) ($this->valorFilaFlexible($row, ['ID de Usuario', 'Codigo', 'codigo', 'id_externo', 'ID']) ?? ''));
            $nombre = trim((string) ($this->valorFilaFlexible($row, ['Nombre', 'nombre', 'Empleado', 'Funcionario']) ?? ''));

            $marks[] = [
                'codigo' => $codigo,
                'fecha_hora' => $fechaHora->toIso8601String(),
                'tipo' => trim((string) ($this->valorFilaFlexible($row, ['Estado', 'estado']) ?? 'entrada')),
                'metodo_verificacion' => trim((string) ($this->valorFilaFlexible($row, ['Verificacion', 'Verificación', 'verificacion']) ?? '')),
                'datos_originales' => $row + ['Archivo' => basename($rutaArchivo)],
            ];

            $employeeKey = $codigo !== '' ? $codigo : $nombre;
            if ($employeeKey !== '') {
                $employeeKeys[] = $employeeKey;
            }
        }

        return [
            'marks' => $marks,
            'summary' => [
                'valid_rows' => count($marks),
                'employees' => count(array_unique($employeeKeys)),
                'duplicates' => 0,
            ],
        ];
    }

    private function leerCsv(string $rutaArchivo): array
    {
        $handle = fopen($rutaArchivo, 'rb');

        if (! $handle) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $firstLine = fgets($handle) ?: '';
        rewind($handle);

        $delimiter = str_contains($firstLine, ';') ? ';' : ',';
        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($headers === null) {
                $headers = $this->normalizarCabeceras($data);
                continue;
            }

            if ($this->filaVacia($data)) {
                continue;
            }

            $rows[] = $this->combinarFila($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function leerSpreadsheet(string $rutaArchivo): array
    {
        try {
            $reader = IOFactory::createReaderForFile($rutaArchivo);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($rutaArchivo);
            $worksheet = $spreadsheet->getSheet(0);
            $sheetRows = $worksheet->toArray(null, true, true, false);

            if ($sheetRows === []) {
                return [];
            }

            $headers = $this->normalizarCabeceras(array_shift($sheetRows) ?: []);
            $rows = collect($sheetRows)
                ->filter(fn (array $row) => ! $this->filaVacia($row))
                ->map(fn (array $row) => $this->combinarFila($headers, $row))
                ->values()
                ->all();

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $rows;
        } catch (\Throwable $exception) {
            throw new \RuntimeException('No se pudo leer el archivo Excel biometrico. Detalle: '.$exception->getMessage());
        }
    }

    private function resolverFechaHoraFila(array $row): ?Carbon
    {
        $fechaHora = $this->valorFilaFlexible($row, ['Tiempo', 'FechaHora', 'fecha_hora', 'Fecha y Hora', 'Datetime']);

        if ($fechaHora !== null && $fechaHora !== '') {
            return $this->normalizarFechaHora($fechaHora);
        }

        $fecha = $this->valorFilaFlexible($row, ['Fecha', 'fecha']);
        $hora = $this->valorFilaFlexible($row, ['Hora', 'hora']);

        if ($fecha !== null && $fecha !== '' && $hora !== null && $hora !== '') {
            return $this->normalizarFechaHora(trim((string) $fecha).' '.trim((string) $hora));
        }

        if ($fecha !== null && $fecha !== '') {
            return $this->normalizarFechaHora($fecha);
        }

        return null;
    }

    private function sameTime(?string $a, ?string $b): bool
    {
        if (blank($a) || blank($b)) {
            return false;
        }

        $ca = $this->parseTimeStringToCarbon($a);
        $cb = $this->parseTimeStringToCarbon($b);

        if (! $ca || ! $cb) {
            return false;
        }

        return $ca->format('H:i:s') === $cb->format('H:i:s');
    }

    private function esPosterior(?string $a, ?string $b): bool
    {
        if (blank($a) || blank($b)) {
            return false;
        }

        $ca = $this->parseTimeStringToCarbon($a);
        $cb = $this->parseTimeStringToCarbon($b);

        if (! $ca || ! $cb) {
            return false;
        }

        return $ca->greaterThan($cb);
    }

    private function normalizarFechaHora(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            $days = (int) floor($serial);
            $seconds = (int) round(($serial - $days) * 86400);

            return Carbon::create(1899, 12, 30, 0, 0, 0)->addDays($days)->addSeconds($seconds);
        }

        $text = trim((string) $value);

        // Intentar formatos comunes (día/mes/año) antes de delegar en parse() para evitar
        // ambigüedades entre formato 'd/m/Y' y 'm/d/Y'. Esto corrige saltos de mes al importar.
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'd-n-Y H:i:s',
            'd-n-Y H:i',
            'd-n-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm/d/Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $text);
                if ($dt !== false) {
                    return $dt;
                }
            } catch (\Exception) {
                // ignorar y probar siguiente formato
            }
        }

        // Último recurso: permitir Carbon intentar parseo liberal.
        try {
            return Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }

    private function valorFilaFlexible(array $fila, array $claves): ?string
    {
        foreach ($claves as $clave) {
            foreach ($fila as $header => $valor) {
                if ($this->normalizarTexto((string) $header) === $this->normalizarTexto($clave)) {
                    $texto = trim((string) $valor);
                    if ($texto !== '') {
                        return $texto;
                    }
                }
            }
        }

        return null;
    }

    private function normalizarCabeceras(array $headers): array
    {
        return array_map(function ($header, int $index) {
            $value = trim((string) $header);

            return $value !== '' ? $value : 'columna_'.$index;
        }, array_values($headers), array_keys(array_values($headers)));
    }

    private function combinarFila(array $headers, array $values): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            $row[$header] = isset($values[$index]) ? trim((string) $values[$index]) : '';
        }

        return $row;
    }

    private function filaVacia(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function nombreCompletoDesdeFila(array $fila): string
    {
        $nombre = trim((string) ($this->valorFilaFlexible($fila, ['Nombre', 'nombre', 'Empleado', 'Funcionario']) ?? ''));
        $apellido = trim((string) ($this->valorFilaFlexible($fila, ['Apellido', 'apellido']) ?? ''));

        if ($apellido === '' && str_contains($nombre, ' ')) {
            $partes = preg_split('/\s+/', $nombre, -1, PREG_SPLIT_NO_EMPTY);

            if (count($partes) > 1) {
                $apellido = implode(' ', array_slice($partes, 1));
                $nombre = $partes[0];
            }
        }

        return trim($nombre.' '.$apellido);
    }

    private function observacionDesdeFila(array $mark): string
    {
        $fila = $mark['datos_originales'] ?? [];
        $evento = $this->valorFilaFlexible($fila, ['Evento', 'evento']) ?: 'Sin evento';
        $verificacion = $this->valorFilaFlexible($fila, ['Verificacion', 'verificacion']) ?: 'Sin verificacion';
        $estado = $this->valorFilaFlexible($fila, ['Estado', 'estado']) ?: 'Sin estado';

        return 'Importado desde '.$this->archivoOrigenDesdeFila($mark).' | '.$evento.' | '.$verificacion.' | '.$estado;
    }

    private function descriptorEmpleadoNoRegistrado(array $mark): ?string
    {
        $fila = $mark['datos_originales'] ?? [];
        $nombre = $this->nombreCompletoDesdeFila($fila);
        $codigo = trim((string) ($mark['codigo'] ?? ''));

        if ($nombre !== '' && $codigo !== '') {
            return $nombre.' ('.$codigo.')';
        }

        if ($nombre !== '') {
            return $nombre;
        }

        if ($codigo !== '') {
            return 'Codigo '.$codigo;
        }

        return null;
    }

    private function crearEmpleadoDesdeMarca(array $mark, ?User $usuario = null): ?Empleado
    {
        $fila = $mark['datos_originales'] ?? [];
        $nombreCompleto = $this->nombreCompletoDesdeFila($fila);

        if ($nombreCompleto === '') {
            return null;
        }

        ['nombre' => $nombre, 'apellido' => $apellido] = $this->separarNombreApellido($nombreCompleto);

        if ($nombre === '') {
            return null;
        }

        $codigo = trim((string) ($mark['codigo'] ?? '')) ?: null;

        try {
            return Empleado::query()->create([
                'nombre' => $nombre,
                'apellido' => $apellido !== '' ? $apellido : 'Sin apellido',
                'codigo_biometrico' => $codigo,
                'area' => 'Personal',
                'sucursal' => $this->resolverSucursal($fila),
                'hora_entrada_programada' => config('asistencia.hora_entrada'),
                'hora_salida_programada' => config('asistencia.hora_salida'),
                'fecha_contratacion' => $this->resolverFechaContratacionDesdeMarca($mark),
                'created_by' => $usuario?->id,
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateEmpleadoCodigoException($exception, $codigo)) {
                $empleadoExistente = $codigo
                    ? Empleado::query()->withTrashed()->where('codigo_biometrico', $codigo)->first()
                    : null;

                if ($empleadoExistente) {
                    return $this->actualizarEmpleadoDesdeMarca(
                        $this->restaurarEmpleadoSiEliminado($empleadoExistente),
                        $mark
                    );
                }

                throw new \RuntimeException(
                    $codigo
                        ? 'Ya fueron importados estos datos o el codigo biometrico '.$codigo.' ya existe en el sistema.'
                        : 'Ya fueron importados estos datos o el personal ya existe en el sistema.'
                );
            }

            throw $exception;
        }
    }

    private function actualizarEmpleadoDesdeMarca(Empleado $empleado, array $mark): Empleado
    {
        $fila = $mark['datos_originales'] ?? [];
        $payload = [];
        $codigo = trim((string) ($mark['codigo'] ?? ''));
        $sucursal = $this->resolverSucursal($fila);

        if ($codigo !== '' && blank($empleado->codigo_biometrico)) {
            $payload['codigo_biometrico'] = $codigo;
        }

        if (blank($empleado->sucursal) || $empleado->sucursal === 'Sin sucursal asignada') {
            $payload['sucursal'] = $sucursal;
        }

        if (blank($empleado->area)) {
            $payload['area'] = 'Personal';
        }

        if ($payload !== []) {
            $empleado->update($payload);
        }

        return $empleado->fresh();
    }

    private function restaurarEmpleadoSiEliminado(Empleado $empleado): Empleado
    {
        if (method_exists($empleado, 'trashed') && $empleado->trashed()) {
            $empleado->restore();
        }

        return $empleado;
    }

    private function separarNombreApellido(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($partes === []) {
            return ['nombre' => '', 'apellido' => ''];
        }

        if (count($partes) === 1) {
            return ['nombre' => $partes[0], 'apellido' => ''];
        }

        return [
            'nombre' => array_shift($partes),
            'apellido' => implode(' ', $partes),
        ];
    }

    private function resolverFechaContratacionDesdeMarca(array $mark): string
    {
        $fechaHora = $mark['fecha_hora'] ?? null;

        if (filled($fechaHora)) {
            return Carbon::parse($fechaHora)->toDateString();
        }

        return now()->toDateString();
    }

    private function resolverHorasJornada(Empleado $empleado, Collection $horas): array
    {
        $entradaExplicita = $horas->first(fn (array $mark) => $this->marcaEsEntrada($mark));
        $salidaExplicita = $horas->filter(fn (array $mark) => $this->marcaEsSalida($mark))->last();

        $entrada = ($entradaExplicita['fecha_hora'] ?? null)?->format('H:i:s');
        $salida = ($salidaExplicita['fecha_hora'] ?? null)?->format('H:i:s');

        if ($entrada && $salida) {
            return [$entrada, $salida];
        }

        if ($horas->count() === 1) {
            $unica = $horas->first();
            return $this->resolverMarcacionUnicaPorHorario($empleado, $unica['fecha_hora']);
        }

        if (! $entrada) {
            $primera = $horas->first();
            $entrada = $primera['fecha_hora']->format('H:i:s');
        }

        if (! $salida) {
            $ultima = $horas->last();
            $ultimaHora = $ultima['fecha_hora']->format('H:i:s');

            $salida = $entrada && $this->esPosterior($ultimaHora, $entrada)
                ? $ultimaHora
                : null;
        }

        return [$entrada, $salida];
    }

    private function marcaEsEntrada(array $mark): bool
    {
        $estado = $mark['estado'] ?? '';
        $evento = $mark['evento'] ?? '';

        return str_contains($estado, 'entrada')
            || str_contains($estado, 'retorno')
            || str_contains($estado, 'ingreso')
            || str_contains($evento, 'retorno');
    }

    private function marcaEsSalida(array $mark): bool
    {
        $estado = $mark['estado'] ?? '';
        $evento = $mark['evento'] ?? '';

        return str_contains($estado, 'salida')
            || str_contains($evento, 'boton de salida')
            || str_contains($evento, 'salida');
    }

    private function resolverMarcacionUnicaPorHorario(Empleado $empleado, Carbon $fechaHora): array
    {
        $horario = app(ProgramacionLaboralService::class)->resolverHorario($empleado, $fechaHora);

        if (($horario['laborable'] ?? true) === false) {
            return [$fechaHora->format('H:i:s'), null];
        }

        $horaEntrada = $this->parseTimeStringToCarbon((string) ($horario['hora_entrada'] ?? ''));
        $horaSalida = $this->parseTimeStringToCarbon((string) ($horario['hora_salida'] ?? ''));

        if (! $horaEntrada || ! $horaSalida) {
            return [$fechaHora->format('H:i:s'), null];
        }

        $marcaMinutos = ((int) $fechaHora->format('H')) * 60 + (int) $fechaHora->format('i');
        $entradaMinutos = ((int) $horaEntrada->format('H')) * 60 + (int) $horaEntrada->format('i');
        $salidaMinutos = ((int) $horaSalida->format('H')) * 60 + (int) $horaSalida->format('i');
        $puntoMedio = (int) floor(($entradaMinutos + $salidaMinutos) / 2);

        if ($marcaMinutos >= $puntoMedio) {
            return [null, $fechaHora->format('H:i:s')];
        }

        return [$fechaHora->format('H:i:s'), null];
    }

    private function resolverEstadoMarcacionHumano(array $mark): string
    {
        $estadoOriginal = trim((string) ($mark['estado_original'] ?? ''));
        $estado = $mark['estado'] ?? '';
        $evento = $mark['evento'] ?? '';
        $esEntrada = $this->marcaEsEntrada($mark);
        $esSalida = $this->marcaEsSalida($mark);

        if ($esSalida && ! $esEntrada) {
            return 'Salida';
        }

        if ($esEntrada && ! $esSalida) {
            return 'Entrada';
        }

        if ($estadoOriginal !== '') {
            return $estadoOriginal;
        }

        return trim((string) ($mark['estado'] ?? '')) !== '' ? (string) $mark['estado'] : 'Sin estado';
    }

    private function normalizarMarcaDesdeBiometrico(array $device, array $row): array
    {
        $fechaHora = Carbon::parse((string) $row['fecha_hora']);
        $codigo = trim((string) ($row['codigo'] ?? ''));
        $nombre = trim((string) ($row['nombre'] ?? ''));
        $apellido = trim((string) ($row['apellido'] ?? ''));
        $nombreCompleto = trim((string) ($row['nombre_completo'] ?? trim($nombre.' '.$apellido)));
        $estadoHumano = $this->traducirEstadoHumano((string) ($row['estado'] ?? ''));
        $eventoHumano = $this->traducirEventoHumano((string) ($row['punch'] ?? ''));
        $verificacionHumana = $this->traducirVerificacionHumana((string) ($row['verificacion'] ?? ''));

        return [
            'codigo' => $codigo,
            'fecha_hora' => $fechaHora->toIso8601String(),
            'tipo' => $estadoHumano,
            'metodo_verificacion' => $verificacionHumana,
            'datos_originales' => [
                'Tiempo' => $fechaHora->format('d/m/Y H:i'),
                'ID de Usuario' => $codigo,
                'Nombre' => $nombre !== '' ? $nombre : $nombreCompleto,
                'Apellido' => $apellido,
                'Empleado' => $nombreCompleto,
                'Numero de tarjeta' => $row['numero_tarjeta'] ?? '',
                'Dispositivo' => $device['department'] ?? '',
                'Punto del evento' => $device['branch'] ?? '',
                'Verificacion' => $verificacionHumana,
                'Estado' => $estadoHumano,
                'Evento' => $eventoHumano,
                'Notas' => 'Sincronizacion automatica desde biometrico ZKTeco',
                'Archivo' => 'sync://'.trim((string) ($device['ip'] ?? 'sin-ip')),
            ],
        ];
    }

    private function traducirEstadoHumano(string $status): string
    {
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

    private function traducirEventoHumano(string $punch): string
    {
        return match ($punch) {
            '0' => 'Registro biometrico',
            '1' => 'Apertura con tarjeta de proximidad',
            '2' => 'Apertura remota',
            '3' => 'Boton de salida',
            '4' => 'Alarma',
            default => $punch !== '' ? 'Evento '.$punch : 'Sin evento',
        };
    }

    private function traducirVerificacionHumana(string $verification): string
    {
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

    private function asegurarMemoriaImportacion(): void
    {
        $memoryLimit = trim((string) ini_get('memory_limit'));

        if ($memoryLimit === '' || $memoryLimit === '-1') {
            return;
        }

        $limitBytes = $this->memoryLimitToBytes($memoryLimit);

        if ($limitBytes !== null && $limitBytes < 512 * 1024 * 1024) {
            @ini_set('memory_limit', '512M');
        }
    }

    private function memoryLimitToBytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || ! preg_match('/^\s*(\d+)\s*([KMG]?)\s*$/i', $value, $matches)) {
            return null;
        }

        $bytes = (int) $matches[1];
        $unit = strtoupper($matches[2] ?? '');

        return match ($unit) {
            'G' => $bytes * 1024 * 1024 * 1024,
            'M' => $bytes * 1024 * 1024,
            'K' => $bytes * 1024,
            default => $bytes,
        };
    }

    private function isDuplicateEmpleadoCodigoException(QueryException $exception, ?string $codigo): bool
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'empleados_codigo_biometrico_unique')) {
            return true;
        }

        if ($codigo === null || $codigo === '') {
            return false;
        }

        return str_contains($message, 'codigo_biometrico')
            && str_contains($message, (string) $codigo);
    }
}
