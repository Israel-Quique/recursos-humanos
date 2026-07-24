<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Importacion;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportacionBiometricaService
{
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

            $entrada = $this->resolverHoraEntrada($horas);
            $salida = $this->resolverHoraSalida($horas);
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

            $registro->fill([
                'empleado_id' => $empleado->id,
                'importacion_id' => $importacion->id,
                'hora_entrada' => $entrada,
                'hora_salida' => $salida,
                'tipo_verificacion' => $ultimaMarca['verificacion'] ?? null,
                'estado_marcacion' => $ultimaMarca['estado_original'] ?? null,
                'evento_biometrico' => $ultimaMarca['evento'] ?? null,
                'observacion' => $this->observacionDesdeFila($primeraMarca),
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
            'olvidos_marcacion' => $olvidosMarcacion,
            'marcas_omitidas' => $marcasOmitidas,
            'empleados_no_registrados' => $empleadosNoRegistrados->filter()->unique()->values()->take(10)->all(),
        ];
    }

    private function resolverEmpleado(array $mark, ?User $usuario = null): array
    {
        $codigo = trim((string) ($mark['codigo'] ?? ''));
        $datosOriginales = $mark['datos_originales'] ?? [];
        $nombreOriginal = $this->nombreCompletoDesdeFila($datosOriginales);

        if ($codigo !== '') {
            $existingByCode = Empleado::query()->where('codigo_biometrico', $codigo)->first();
            if ($existingByCode) {
                return ['empleado' => $this->actualizarEmpleadoDesdeMarca($existingByCode, $mark), 'created' => false];
            }
        }

        if ($nombreOriginal !== '') {
            $normalizedName = $this->normalizarTexto($nombreOriginal);
            $existingByName = Empleado::query()
                ->get()
                ->first(fn (Empleado $empleado) => $this->normalizarTexto($empleado->nombre_completo) === $normalizedName);

            if ($existingByName) {
                return ['empleado' => $this->actualizarEmpleadoDesdeMarca($existingByName, $mark), 'created' => false];
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

        try {
            return Carbon::parse((string) $value);
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

        return Empleado::query()->create([
            'nombre' => $nombre,
            'apellido' => $apellido !== '' ? $apellido : 'Sin apellido',
            'codigo_biometrico' => trim((string) ($mark['codigo'] ?? '')) ?: null,
            'area' => 'Personal',
            'sucursal' => $this->resolverSucursal($fila),
            'hora_entrada_programada' => config('asistencia.hora_entrada'),
            'hora_salida_programada' => config('asistencia.hora_salida'),
            'fecha_contratacion' => $this->resolverFechaContratacionDesdeMarca($mark),
            'created_by' => $usuario?->id,
        ]);
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

    private function resolverHoraEntrada(Collection $horas): ?string
    {
        $entrada = $horas->first(function (array $mark) {
            return str_contains($mark['estado'], 'entrada');
        });

        if ($entrada) {
            return $entrada['fecha_hora']->format('H:i:s');
        }

        return $horas->first()['fecha_hora']->format('H:i:s');
    }

    private function resolverHoraSalida(Collection $horas): ?string
    {
        $salida = $horas->filter(function (array $mark) {
            return str_contains($mark['estado'], 'salida');
        })->last();

        if ($salida) {
            return $salida['fecha_hora']->format('H:i:s');
        }

        if ($horas->count() > 1) {
            return $horas->last()['fecha_hora']->format('H:i:s');
        }

        return null;
    }
}
