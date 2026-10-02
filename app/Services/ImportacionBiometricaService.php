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
        $this->asegurarMemoriaImportacion();
        @ini_set('max_execution_time', '1200');
        @set_time_limit(1200);

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

        try {
            $resumen = $this->persistirMarcas($importacion, collect($resultado['marks'] ?? []), $usuario);

            $importacion->update([
                'registros_generados' => $resumen['registros_generados'],
                'empleados_detectados' => $resumen['empleados_detectados'],
                'mensaje_error' => null,
                'estado' => 'completado',
                'resumen_json' => $resumen,
            ]);
        } catch (\Throwable $exception) {
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
        $this->asegurarMemoriaImportacion();

        // Pre-cargar todos los empleados en mapas de memoria para evitar N+1 queries
        $empleadosByCodigo = [];
        $empleadosByNombre = [];

        foreach (Empleado::query()->withTrashed()->get() as $emp) {
            $c = trim((string) $emp->codigo_biometrico);
            if ($c !== '') {
                $empleadosByCodigo[$c] = $emp;
            }
            $n = $this->normalizarTexto($emp->nombre_completo);
            if ($n !== '') {
                $empleadosByNombre[$n] = $emp;
            }
        }

        $agrupados = $marks
            ->filter(fn (array $mark) => filled($mark['fecha_hora'] ?? null))
            ->groupBy(function (array $mark) {
                $fecha = substr((string) $mark['fecha_hora'], 0, 10);
                $codigo = trim((string) ($mark['codigo'] ?? ''));
                $nombre = $this->nombrePlanoDesdeFila($mark);

                return md5($fecha.'|'.$codigo.'|'.$nombre);
            });

        // Pre-cargar registros de asistencia existentes para las fechas del lote
        $fechasUnicas = [];
        foreach ($agrupados as $grupo) {
            $fh = $grupo->first()['fecha_hora'] ?? null;
            if ($fh) {
                $fechasUnicas[substr((string) $fh, 0, 10)] = true;
            }
        }

        $registrosExistentes = [];
        $fechasLista = array_keys($fechasUnicas);
        if (! empty($fechasLista)) {
            foreach (array_chunk($fechasLista, 60) as $fechaChunk) {
                $records = RegistroAsistencia::query()
                    ->whereIn('fecha', $fechaChunk)
                    ->get();

                foreach ($records as $rec) {
                    $fStr = Carbon::parse($rec->fecha)->toDateString();
                    $registrosExistentes[$rec->empleado_id.'_'.$fStr] = $rec;
                }
            }
        }

        $registrosGenerados = 0;
        $empleadosDetectados = collect();
        $olvidosMarcacion = 0;
        $registrosActualizados = 0;
        $marcasOmitidas = 0;
        $empleadosNoRegistrados = collect();
        $empleadosCreados = collect();

        $chunks = $agrupados->chunk(500);

        foreach ($chunks as $chunk) {
            $manageTx = DB::transactionLevel() === 0;
            if ($manageTx) {
                DB::beginTransaction();
            }

            try {
                foreach ($chunk as $grupo) {
                    $primeraMarca = $grupo->first();
                    [
                        'empleado' => $empleado,
                        'created' => $created,
                    ] = $this->resolverEmpleadoEnMemoria($primeraMarca, $usuario, $empleadosByCodigo, $empleadosByNombre);

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
                            $fh = Carbon::parse($mark['fecha_hora']);
                            $orig = $mark['datos_originales'] ?? [];
                            return [
                                'fecha_hora' => $fh,
                                'estado' => $this->normalizarTexto((string) ($orig['Estado'] ?? $orig['estado'] ?? '')),
                                'estado_original' => $orig['Estado'] ?? $orig['estado'] ?? 'Sin estado',
                                'verificacion' => $orig['Verificacion'] ?? $orig['Verificación'] ?? $orig['verificacion'] ?? 'Sin verificacion',
                                'evento' => $orig['Evento'] ?? $orig['evento'] ?? 'Sin evento',
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
                    $cacheKey = $empleado->id.'_'.$fechaOperativa->toDateString();

                    $registro = $registrosExistentes[$cacheKey] ?? null;

                    // Si el empleado tiene régimen especial o la marcación fue registrada/editada como Especial,
                    // no se actualizan sus datos desde el biométrico para preservar las asignaciones de RRHH.
                    if ($registro && ($empleado->es_especial || $registro->tipo_verificacion === 'Especial')) {
                        $marcasOmitidas += $grupo->count();
                        continue;
                    }

                    $esNuevo = false;
                    if (! $registro) {
                        $registro = new RegistroAsistencia([
                            'empleado_id' => $empleado->id,
                            'fecha' => $fechaOperativa,
                        ]);
                        $esNuevo = true;
                    }

                    // Fusionar con registro existente: no sobreescribir con nulos y
                    // elegir la entrada más temprana y la salida más tardía.
                    $existingEntrada = $registro->hora_entrada ?? null;
                    $existingSalida = $registro->hora_salida ?? null;

                    if ($existingEntrada && ! $salida && $entrada && $this->esPosterior($entrada, $existingEntrada)) {
                        $cEntrada = $this->parseTimeStringToCarbon($entrada);
                        $cExisting = $this->parseTimeStringToCarbon($existingEntrada);
                        if ($cEntrada && $cExisting && abs($cEntrada->diffInMinutes($cExisting)) >= 5) {
                            $salida = $entrada;
                            $entrada = $existingEntrada;
                        }
                    }

                    $mergedEntrada = $this->minTime($existingEntrada, $entrada);
                    $mergedSalida = $this->maxTime($existingSalida, $salida);

                    if ($this->sameTime($mergedEntrada, $mergedSalida)) {
                        $mergedSalida = null;
                    } elseif ($mergedEntrada && $mergedSalida) {
                        $cEntrada = $this->parseTimeStringToCarbon($mergedEntrada);
                        $cSalida = $this->parseTimeStringToCarbon($mergedSalida);
                        if ($cEntrada && $cSalida && abs($cSalida->diffInMinutes($cEntrada)) < 5) {
                            $mergedSalida = null;
                        }
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
                    $registrosExistentes[$cacheKey] = $registro;

                    if ($registro->wasRecentlyCreated || $esNuevo) {
                        $registrosGenerados++;
                    } else {
                        $registrosActualizados++;
                    }
                }

                if ($manageTx) {
                    DB::commit();
                }
            } catch (\Throwable $exception) {
                if ($manageTx) {
                    DB::rollBack();
                }
                throw $exception;
            }

            gc_collect_cycles();
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

    private function resolverEmpleadoEnMemoria(
        array $mark,
        ?User $usuario,
        array &$empleadosByCodigo,
        array &$empleadosByNombre
    ): array {
        $codigo = trim((string) ($mark['codigo'] ?? ''));
        $datosOriginales = $mark['datos_originales'] ?? [];
        $nombreOriginal = $this->nombreCompletoDesdeFila($datosOriginales);
        $normNombre = $this->normalizarTexto($nombreOriginal);

        $empleado = null;

        if ($codigo !== '' && isset($empleadosByCodigo[$codigo])) {
            $empleado = $empleadosByCodigo[$codigo];
        } elseif ($normNombre !== '' && isset($empleadosByNombre[$normNombre])) {
            $empleado = $empleadosByNombre[$normNombre];
        }

        if ($empleado) {
            $empleado = $this->restaurarEmpleadoSiEliminado($empleado);
            $empleado = $this->actualizarEmpleadoDesdeMarca($empleado, $mark);

            if ($codigo !== '') {
                $empleadosByCodigo[$codigo] = $empleado;
            }
            if ($normNombre !== '') {
                $empleadosByNombre[$normNombre] = $empleado;
            }

            return ['empleado' => $empleado, 'created' => false];
        }

        $empleadoCreado = $this->crearEmpleadoDesdeMarca($mark, $usuario);

        if ($empleadoCreado) {
            if ($codigo !== '') {
                $empleadosByCodigo[$codigo] = $empleadoCreado;
            }
            if ($normNombre !== '') {
                $empleadosByNombre[$normNombre] = $empleadoCreado;
            }
        }

        return [
            'empleado' => $empleadoCreado,
            'created' => $empleadoCreado !== null,
        ];
    }

    private function resolverEmpleado(array $mark, ?User $usuario = null): array
    {
        $dummyCodigo = [];
        $dummyNombre = [];

        foreach (Empleado::query()->withTrashed()->get() as $emp) {
            $c = trim((string) $emp->codigo_biometrico);
            if ($c !== '') {
                $dummyCodigo[$c] = $emp;
            }
            $n = $this->normalizarTexto($emp->nombre_completo);
            if ($n !== '') {
                $dummyNombre[$n] = $emp;
            }
        }

        return $this->resolverEmpleadoEnMemoria($mark, $usuario, $dummyCodigo, $dummyNombre);
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
            'csv', 'txt' => $this->leerCsv($rutaArchivo),
            'xlsx', 'xls' => $this->leerSpreadsheet($rutaArchivo),
            default => throw new \RuntimeException('Formato de archivo no soportado para importacion biometrica.'),
        };

        if (empty($rows)) {
            return [
                'marks' => [],
                'summary' => [
                    'valid_rows' => 0,
                    'employees' => 0,
                    'duplicates' => 0,
                ],
            ];
        }

        $headers = array_keys($rows[0]);
        $keyFechaHora = $this->buscarClaveHeader($headers, ['Tiempo', 'FechaHora', 'fecha_hora', 'Fecha y Hora', 'Datetime', 'date_time', 'time']);
        $keyFecha = $this->buscarClaveHeader($headers, ['Fecha', 'fecha', 'date']);
        $keyHora = $this->buscarClaveHeader($headers, ['Hora', 'hora']);
        $keyCodigo = $this->buscarClaveHeader($headers, ['ID de Usuario', 'Codigo', 'codigo', 'id_externo', 'ID', 'user_id', 'userid', 'pin']);
        $keyNombre = $this->buscarClaveHeader($headers, ['Nombre', 'nombre', 'Empleado', 'Funcionario', 'name']);
        $keyApellido = $this->buscarClaveHeader($headers, ['Apellido', 'apellido', 'last_name', 'lastname']);
        $keyEstado = $this->buscarClaveHeader($headers, ['Estado', 'estado', 'state', 'punch', 'tipo']);
        $keyVerificacion = $this->buscarClaveHeader($headers, ['Verificacion', 'Verificación', 'verificacion', 'verify', 'verify_mode']);
        $keyEvento = $this->buscarClaveHeader($headers, ['Evento', 'evento', 'event']);

        $marks = [];
        $employeeKeys = [];
        $baseArchivo = basename($rutaArchivo);
        $lastFormat = null;

        foreach ($rows as $row) {
            $fechaHora = $this->resolverFechaHoraRapida($row, $keyFechaHora, $keyFecha, $keyHora, $lastFormat);

            if (! $fechaHora) {
                continue;
            }

            $codigo = $keyCodigo ? trim((string) ($row[$keyCodigo] ?? '')) : '';
            $nombre = $keyNombre ? trim((string) ($row[$keyNombre] ?? '')) : '';
            $tipo = $keyEstado ? trim((string) ($row[$keyEstado] ?? 'entrada')) : 'entrada';
            $verificacion = $keyVerificacion ? trim((string) ($row[$keyVerificacion] ?? '')) : '';

            $marks[] = [
                'codigo' => $codigo,
                'fecha_hora' => $fechaHora->toIso8601String(),
                'tipo' => $tipo !== '' ? $tipo : 'entrada',
                'metodo_verificacion' => $verificacion,
                'datos_originales' => $row + ['Archivo' => $baseArchivo],
            ];

            $employeeKey = $codigo !== '' ? $codigo : $nombre;
            if ($employeeKey !== '') {
                $employeeKeys[$employeeKey] = true;
            }
        }

        return [
            'marks' => $marks,
            'summary' => [
                'valid_rows' => count($marks),
                'employees' => count($employeeKeys),
                'duplicates' => 0,
            ],
        ];
    }

    private function buscarClaveHeader(array $headers, array $posibles): ?string
    {
        $normHeaders = [];
        foreach ($headers as $h) {
            $normHeaders[$this->normalizarTexto((string) $h)] = (string) $h;
        }

        foreach ($posibles as $posible) {
            $normPosible = $this->normalizarTexto($posible);
            if (isset($normHeaders[$normPosible])) {
                return $normHeaders[$normPosible];
            }
        }

        return null;
    }

    private function leerCsv(string $rutaArchivo): array
    {
        if (! file_exists($rutaArchivo)) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $handle = fopen($rutaArchivo, 'rb');
        if (! $handle) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        // Detectar si el archivo es UTF-16 (común en exportaciones de biométricos ZKTeco)
        $bomCheck = fread($handle, 2);
        if ($bomCheck === "\xFF\xFE" || $bomCheck === "\xFE\xFF") {
            fclose($handle);
            $content = file_get_contents($rutaArchivo);
            $utf8 = mb_convert_encoding($content, 'UTF-8', $bomCheck === "\xFF\xFE" ? 'UTF-16LE' : 'UTF-16BE');
            $handle = fopen('php://temp', 'r+b');
            fwrite($handle, $utf8);
            rewind($handle);
        } else {
            rewind($handle);
            // Si tiene UTF-8 BOM (\xEF\xBB\xBF), saltarlo
            $bom3 = fread($handle, 3);
            if ($bom3 !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
        }

        // Leer primera línea para detectar delimitador
        $currentPos = ftell($handle);
        $firstLine = fgets($handle) ?: '';
        fseek($handle, $currentPos);

        // Detectar delimitador: coma, punto y coma, tabulador o pipe
        $delimiters = [',', ';', "\t", '|'];
        $counts = [];
        foreach ($delimiters as $delim) {
            $counts[$delim] = substr_count($firstLine, $delim);
        }
        arsort($counts);
        $delimiter = array_key_first($counts) ?: ',';
        if (($counts[$delimiter] ?? 0) === 0) {
            $delimiter = ',';
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->filaVacia($data)) {
                continue;
            }

            // Limpiar valores con posible codificación no-UTF8 (ISO-8859-1 / Windows-1252)
            $data = array_map(function ($val) {
                $valStr = (string) $val;
                if (! mb_check_encoding($valStr, 'UTF-8')) {
                    $valStr = mb_convert_encoding($valStr, 'UTF-8', 'ISO-8859-1');
                }
                return trim($valStr);
            }, $data);

            if ($headers === null) {
                // Verificar si esta primera fila no vacía es una cabecera o ya son datos reales
                if ($this->esFilaCabecera($data)) {
                    $headers = $this->normalizarCabeceras($data);
                    continue;
                }

                // Si no es cabecera (es una fila con fecha y código de marcación),
                // generamos cabeceras estándar sintéticas y procesamos esta fila como dato
                $headers = $this->generarCabecerasSinteticas($data);
                $rows[] = $this->combinarFila($headers, $data);
                continue;
            }

            $rows[] = $this->combinarFila($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function esFilaCabecera(array $data): bool
    {
        $palabrasClave = [
            'tiempo', 'fecha', 'hora', 'fechahora', 'datetime', 'date', 'time',
            'codigo', 'id de usuario', 'id_externo', 'usuario', 'userid', 'pin', 'id',
            'nombre', 'apellido', 'empleado', 'funcionario', 'name', 'lastname',
            'dispositivo', 'punto del evento', 'sucursal', 'departamento', 'ciudad',
            'verificacion', 'estado', 'evento', 'notas', 'punch', 'status',
        ];

        $coincidenciasCabecera = 0;
        foreach ($data as $cell) {
            $norm = $this->normalizarTexto((string) $cell);
            if ($norm !== '' && in_array($norm, $palabrasClave, true)) {
                $coincidenciasCabecera++;
            }
        }

        if ($coincidenciasCabecera >= 2) {
            return true;
        }

        $c0 = trim((string) ($data[0] ?? ''));
        $c1 = trim((string) ($data[1] ?? ''));

        if ($this->pareceFechaHora($c0) || $this->pareceFechaHora($c1)) {
            return false;
        }

        return $coincidenciasCabecera > 0;
    }

    private function pareceFechaHora(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        return (bool) preg_match('/^\d{1,4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,4}/', $text);
    }

    private function generarCabecerasSinteticas(array $data): array
    {
        $count = count($data);

        // Formato estándar ZKTeco (10 a 11 columnas):
        // 0: Tiempo, 1: ID de Usuario, 2: Nombre, 3: Apellido, 4: Num Tarjeta,
        // 5: Dispositivo, 6: Punto de evento, 7: Verificación, 8: Estado, 9: Evento, 10: Notas
        if ($count >= 8 && $this->pareceFechaHora((string) ($data[0] ?? ''))) {
            $standard = [
                'Tiempo',
                'ID de Usuario',
                'Nombre',
                'Apellido',
                'Numero de tarjeta',
                'Dispositivo',
                'Punto del evento',
                'Verificacion',
                'Estado',
                'Evento',
                'Notas',
            ];

            return array_map(function ($idx) use ($standard) {
                return $standard[$idx] ?? ('columna_'.$idx);
            }, range(0, $count - 1));
        }

        if ($count >= 8 && $this->pareceFechaHora((string) ($data[1] ?? ''))) {
            $standard = [
                'ID de Usuario',
                'Tiempo',
                'Nombre',
                'Apellido',
                'Numero de tarjeta',
                'Dispositivo',
                'Punto del evento',
                'Verificacion',
                'Estado',
                'Evento',
                'Notas',
            ];

            return array_map(function ($idx) use ($standard) {
                return $standard[$idx] ?? ('columna_'.$idx);
            }, range(0, $count - 1));
        }

        if ($this->pareceFechaHora((string) ($data[1] ?? ''))) {
            $standard = ['ID de Usuario', 'Fecha', 'Hora', 'Nombre', 'Apellido', 'Sucursal'];
            return array_map(function ($idx) use ($standard) {
                return $standard[$idx] ?? ('columna_'.$idx);
            }, range(0, $count - 1));
        }

        if ($this->pareceFechaHora((string) ($data[0] ?? ''))) {
            $standard = ['Fecha', 'Hora', 'ID de Usuario', 'Nombre', 'Apellido', 'Sucursal'];
            return array_map(function ($idx) use ($standard) {
                return $standard[$idx] ?? ('columna_'.$idx);
            }, range(0, $count - 1));
        }

        return array_map(fn ($idx) => 'columna_'.$idx, range(0, $count - 1));
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

            $firstRow = array_shift($sheetRows) ?: [];
            if ($this->esFilaCabecera($firstRow)) {
                $headers = $this->normalizarCabeceras($firstRow);
            } else {
                $headers = $this->generarCabecerasSinteticas($firstRow);
                array_unshift($sheetRows, $firstRow);
            }

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

    private function resolverFechaHoraRapida(
        array $row,
        ?string $keyFechaHora,
        ?string $keyFecha,
        ?string $keyHora,
        ?string &$lastFormat
    ): ?Carbon {
        $val = $keyFechaHora ? ($row[$keyFechaHora] ?? null) : null;

        if (filled($val)) {
            return $this->normalizarFechaHoraRapida($val, $lastFormat);
        }

        if ($keyFecha) {
            $fecha = trim((string) ($row[$keyFecha] ?? ''));
            $hora = $keyHora ? trim((string) ($row[$keyHora] ?? '')) : '';

            if ($fecha !== '' && $hora !== '') {
                return $this->normalizarFechaHoraRapida($fecha.' '.$hora, $lastFormat);
            }

            if ($fecha !== '') {
                return $this->normalizarFechaHoraRapida($fecha, $lastFormat);
            }
        }

        return null;
    }

    private function normalizarFechaHoraRapida(mixed $value, ?string &$lastFormat): ?Carbon
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

        // Regex rápido para formato día/mes/año con o sin hora
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $text, $m)) {
            return Carbon::create(
                (int) $m[3],
                (int) $m[2],
                (int) $m[1],
                isset($m[4]) ? (int) $m[4] : 0,
                isset($m[5]) ? (int) $m[5] : 0,
                isset($m[6]) ? (int) $m[6] : 0
            );
        }

        // Regex rápido para formato ISO año-mes-día
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})(?:[T\s](\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $text, $m)) {
            return Carbon::create(
                (int) $m[1],
                (int) $m[2],
                (int) $m[3],
                isset($m[4]) ? (int) $m[4] : 0,
                isset($m[5]) ? (int) $m[5] : 0,
                isset($m[6]) ? (int) $m[6] : 0
            );
        }

        if ($lastFormat) {
            try {
                $dt = Carbon::createFromFormat($lastFormat, $text);
                if ($dt !== false) {
                    return $dt;
                }
            } catch (\Throwable) {
                $lastFormat = null;
            }
        }

        $formats = [
            'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
            'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
            'm/d/Y H:i:s', 'm/d/Y H:i', 'm/d/Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $text);
                if ($dt !== false) {
                    $lastFormat = $fmt;
                    return $dt;
                }
            } catch (\Throwable) {
                // continuar
            }
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolverFechaHoraFila(array $row): ?Carbon
    {
        $dummy = null;
        return $this->resolverFechaHoraRapida($row, 'Tiempo', 'Fecha', 'Hora', $dummy)
            ?? $this->normalizarFechaHora($this->valorFilaFlexible($row, ['Tiempo', 'FechaHora', 'fecha_hora', 'Fecha y Hora', 'Datetime']));
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
        $dummy = null;
        return $this->normalizarFechaHoraRapida($value, $dummy);
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
        if ($empleado->es_especial) {
            return $empleado;
        }

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
        if ($horas->isEmpty()) {
            return [null, null];
        }

        if ($horas->count() === 1) {
            $unica = $horas->first();
            return $this->resolverMarcacionUnicaPorHorario($empleado, $unica['fecha_hora']);
        }

        // Si hay 2 o más marcas en la jornada:
        // Ordenamos estrictamente por timestamp
        $sorted = $horas->sortBy(fn (array $mark) => $mark['fecha_hora']->timestamp)->values();
        $primera = $sorted->first();
        $ultima = $sorted->last();
        $primeraHora = $primera['fecha_hora']->format('H:i:s');
        $ultimaHora = $ultima['fecha_hora']->format('H:i:s');

        // Si la última marca es posterior por al menos 5 minutos, se considera salida válida
        if ($this->esPosterior($ultimaHora, $primeraHora) && abs($ultima['fecha_hora']->diffInMinutes($primera['fecha_hora'])) >= 5) {
            return [$primeraHora, $ultimaHora];
        }

        // Si todas las marcas ocurrieron en una ventana menor a 5 minutos (doble marcación al llegar):
        return [$primeraHora, null];
    }

    private function marcaEsEntrada(array $mark): bool
    {
        $estado = $mark['estado'] ?? '';
        $evento = $mark['evento'] ?? '';

        return str_contains($estado, 'entrada')
            || str_contains($estado, 'retorno')
            || str_contains($estado, 'ingreso')
            || str_contains($evento, 'retorno')
            || str_contains($evento, 'entrada');
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

        $marcaMinutos = ((int) $fechaHora->format('H')) * 60 + (int) $fechaHora->format('i');

        if (! $horaEntrada || ! $horaSalida) {
            // Si no hay horario configurado, usar 13:00 (1:00 PM) como punto medio estimado
            if ($marcaMinutos >= 13 * 60) {
                return [null, $fechaHora->format('H:i:s')];
            }
            return [$fechaHora->format('H:i:s'), null];
        }

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

        $punchRaw = trim((string) ($row['punch'] ?? ''));
        $estadoRaw = trim((string) ($row['estado'] ?? ''));
        $verifRaw = trim((string) ($row['verificacion'] ?? ''));

        // En ZKTeco/pyzk:
        // punch: 0 = Entrada, 1 = Salida, 2 = Descanso salida, 3 = Descanso entrada, 4 = Extra entrada, 5 = Extra salida, 255 = Marcacion
        // status/verificacion: 0 = Contraseña, 1 = Huella, 2 = Tarjeta, 15 = Rostro
        $punchToUse = ($punchRaw !== '' && $punchRaw !== '255') 
            ? $punchRaw 
            : ($estadoRaw !== '' && in_array($estadoRaw, ['0', '1', '2', '3', '4', '5'], true) ? $estadoRaw : '');
        
        $verifToUse = $verifRaw !== '' 
            ? $verifRaw 
            : ($estadoRaw !== '' && !in_array($estadoRaw, ['0', '1', '2', '3', '4', '5'], true) ? $estadoRaw : ($punchRaw !== '' && in_array($punchRaw, ['1', '15'], true) ? $punchRaw : ''));

        $estadoHumano = $this->traducirEstadoHumano($punchToUse);
        $eventoHumano = $this->traducirEventoHumano($punchRaw !== '' ? $punchRaw : $punchToUse);
        $verificacionHumana = $this->traducirVerificacionHumana($verifToUse);

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
            default => $status !== '' ? 'Estado '.$status : 'Marcacion general',
        };
    }

    private function traducirEventoHumano(string $punch): string
    {
        return match ($punch) {
            '0' => 'Entrada / Check-in',
            '1' => 'Salida / Check-out',
            '2' => 'Salida a descanso',
            '3' => 'Retorno de descanso',
            '4' => 'Entrada extra',
            '5' => 'Salida extra',
            '255' => 'Registro biometrico',
            default => $punch !== '' ? 'Evento '.$punch : 'Registro biometrico',
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
            '7', '14', '15' => 'Rostro',
            '8' => 'Rostro + huella',
            '9' => 'Rostro + tarjeta',
            '10' => 'Rostro + contrasena',
            '11' => 'Rostro + tarjeta + huella',
            '12' => 'Rostro + huella + contrasena',
            '13' => 'Rostro + tarjeta + contrasena',
            default => $verification !== '' ? 'Metodo '.$verification : 'Biometrico',
        };
    }

    private function asegurarMemoriaImportacion(): void
    {
        $memoryLimit = trim((string) ini_get('memory_limit'));

        if ($memoryLimit === '' || $memoryLimit === '-1') {
            return;
        }

        $limitBytes = $this->memoryLimitToBytes($memoryLimit);

        if ($limitBytes !== null && $limitBytes < 1024 * 1024 * 1024) {
            @ini_set('memory_limit', '1024M');
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
