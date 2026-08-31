<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProgramacionLaboralService
{
    private array $cacheFechas = [];
    private array $cacheFechasPorDia = [];
    private array $cacheHorarios = [];

    public function obtenerFechaEspecial(Carbon|string|null $fecha, ?string $sucursal = null): ?FechaEspecialLaboral
    {
        if (blank($fecha)) {
            return null;
        }

        $scope = $this->normalizarSucursal($sucursal);
        $normalizedDate = $this->normalizarFecha($fecha);
        $key = $normalizedDate.'|'.$scope;

        if (array_key_exists($key, $this->cacheFechas)) {
            return $this->cacheFechas[$key];
        }

        $fechaEspeciales = $this->fechasEspecialesPorDia($normalizedDate);

        $branchMatch = $fechaEspeciales->first(function (FechaEspecialLaboral $fechaEspecial) use ($scope) {
            return $this->normalizarSucursal($fechaEspecial->sucursal) !== 'TODAS'
                && SucursalNormalizer::matches($fechaEspecial->sucursal, $scope);
        });

        if ($branchMatch) {
            return $this->cacheFechas[$key] = $branchMatch;
        }

        return $this->cacheFechas[$key] = $fechaEspeciales->first(function (FechaEspecialLaboral $fechaEspecial) {
            return $this->normalizarSucursal($fechaEspecial->sucursal) === 'TODAS';
        });
    }

    public function fechasEnRango(Carbon $inicio, Carbon $fin): Collection
    {
        return FechaEspecialLaboral::query()
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderBy('fecha')
            ->orderBy('sucursal')
            ->get()
            ->map(function (FechaEspecialLaboral $fechaEspecial) {
                $this->cacheFechas[$fechaEspecial->fecha->toDateString().'|'.$this->normalizarSucursal($fechaEspecial->sucursal)] = $fechaEspecial;

                return $fechaEspecial;
            });
    }

    public function esFeriado(Carbon|string|null $fecha, ?string $sucursal = null): bool
    {
        $fechaEspecial = $this->obtenerFechaEspecial($fecha, $sucursal);

        return $fechaEspecial?->tipo === 'feriado';
    }

    public function esDiaNoLaborable(Carbon|string|null $fecha, ?string $sucursal = null): bool
    {
        if (blank($fecha)) {
            return false;
        }

        $carbon = $fecha instanceof Carbon ? $fecha->copy() : Carbon::parse($fecha);

        if ($carbon->isWeekend()) {
            return true;
        }

        $fechaEspecial = $this->obtenerFechaEspecial($carbon, $sucursal);

        return in_array($fechaEspecial?->tipo, ['feriado', 'paro'], true);
    }

    public function resolverHorario(Empleado $empleado, Carbon|string|null $fecha): array
    {
        $horarioRegional = $this->obtenerHorarioRegional($empleado->sucursal);
        $horaEntradaBase = $horarioRegional?->hora_entrada ?: config('asistencia.hora_entrada');
        $horaSalidaBase = $horarioRegional?->hora_salida ?: config('asistencia.hora_salida');
        $fechaEspecial = $this->obtenerFechaEspecial($fecha, $empleado->sucursal);

        if ($this->esDiaNoLaborable($fecha, $empleado->sucursal)) {
            return [
                'fecha_especial' => $fechaEspecial,
                'horario_regional' => $horarioRegional,
                'laborable' => false,
                'es_feriado' => in_array($fechaEspecial?->tipo, ['feriado', 'paro'], true),
                'hora_entrada' => null,
                'hora_entrada_tolerancia' => null,
                'hora_salida' => null,
            ];
        }

        $horaEntrada = $fechaEspecial?->hora_entrada ?: $horaEntradaBase;

        return [
            'fecha_especial' => $fechaEspecial,
            'horario_regional' => $horarioRegional,
            'laborable' => true,
            'es_feriado' => false,
            'hora_entrada' => $horaEntrada,
            'hora_entrada_tolerancia' => $this->resolverHoraEntradaTolerancia($fecha, $horaEntrada),
            'hora_salida' => $fechaEspecial?->hora_salida ?: $horaSalidaBase,
        ];
    }

    public function obtenerHorarioRegional(?string $sucursal): ?HorarioRegional
    {
        $key = trim((string) $sucursal);

        if ($key === '') {
            return null;
        }

        $cacheKey = SucursalNormalizer::canonicalLabel($key);

        if (array_key_exists($cacheKey, $this->cacheHorarios)) {
            return $this->cacheHorarios[$cacheKey];
        }

        return $this->cacheHorarios[$cacheKey] = HorarioRegional::query()
            ->where(function ($query) use ($key) {
                SucursalNormalizer::applyFilter($query, 'sucursal', $key);
            })
            ->first();
    }

    public function horarioRegionalFormateado(?string $sucursal): string
    {
        $horario = $this->obtenerHorarioRegional($sucursal);
        $entrada = $horario?->hora_entrada ? substr($horario->hora_entrada, 0, 5) : substr((string) config('asistencia.hora_entrada'), 0, 5);
        $salida = $horario?->hora_salida ? substr($horario->hora_salida, 0, 5) : substr((string) config('asistencia.hora_salida'), 0, 5);

        return $entrada.' - '.$salida;
    }

    public function minutosJornada(Empleado $empleado, Carbon|string|null $fecha): int
    {
        $horario = $this->resolverHorario($empleado, $fecha);

        if (! $horario['laborable']) {
            return 0;
        }

        return $this->minutosEntreHoras($horario['hora_entrada'], $horario['hora_salida']);
    }

    public function minutosIncidencia(
        Empleado $empleado,
        Carbon|string|null $fecha,
        string $alcance,
        ?string $horaInicio = null,
        ?string $horaFin = null
    ): int {
        $horario = $this->resolverHorario($empleado, $fecha);

        if (! $horario['laborable']) {
            return 0;
        }

        $jornada = $this->minutosEntreHoras($horario['hora_entrada'], $horario['hora_salida']);

        return match ($alcance) {
            'manana', 'tarde', 'medio_dia' => (int) floor($jornada / 2),
            'dias', 'dia_completo' => $jornada,
            'horas', 'llegada_tarde', 'salida_temprana' => $this->minutosEntreHoras($horaInicio, $horaFin),
            default => 0,
        };
    }

    private function normalizarFecha(Carbon|string $fecha): string
    {
        return $fecha instanceof Carbon
            ? $fecha->toDateString()
            : Carbon::parse($fecha)->toDateString();
    }

    private function normalizarSucursal(?string $sucursal): string
    {
        $value = trim((string) $sucursal);

        if ($value === '') {
            return 'TODAS';
        }

        return SucursalNormalizer::canonicalLabel($value);
    }

    private function resolverHoraEntradaTolerancia(Carbon|string|null $fecha, ?string $horaEntrada): ?string
    {
        if (blank($fecha) || blank($horaEntrada)) {
            return $horaEntrada;
        }

        $carbon = $fecha instanceof Carbon ? $fecha->copy() : Carbon::parse($fecha);
        $diasToleranciaExtendida = collect(config('asistencia.dias_tolerancia_extendida', []))
            ->map(fn ($day) => (int) $day)
            ->filter(fn (int $day) => $day > 0 && $day <= 31)
            ->all();

        if (! in_array($carbon->day, $diasToleranciaExtendida, true)) {
            return $horaEntrada;
        }

        $horaExtendida = config('asistencia.hora_entrada_tolerancia_extendida', '09:00:00');

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $entrada = Carbon::createFromFormat($format, $horaEntrada);
                $extendida = Carbon::createFromFormat($format, $horaExtendida);

                return $extendida->greaterThan($entrada)
                    ? $extendida->format('H:i:s')
                    : $entrada->format('H:i:s');
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $horaEntrada;
    }

    private function fechasEspecialesPorDia(string $fecha): Collection
    {
        if (array_key_exists($fecha, $this->cacheFechasPorDia)) {
            return $this->cacheFechasPorDia[$fecha];
        }

        return $this->cacheFechasPorDia[$fecha] = FechaEspecialLaboral::query()
            ->whereDate('fecha', $fecha)
            ->orderBy('sucursal')
            ->get();
    }

    private function minutosEntreHoras(?string $horaInicio, ?string $horaFin): int
    {
        if (blank($horaInicio) || blank($horaFin)) {
            return 0;
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $inicio = Carbon::createFromFormat($format, $horaInicio);
                break;
            } catch (\Exception $exception) {
                $inicio = null;
            }
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $fin = Carbon::createFromFormat($format, $horaFin);
                break;
            } catch (\Exception $exception) {
                $fin = null;
            }
        }

        if (! isset($inicio, $fin) || $fin->lessThanOrEqualTo($inicio)) {
            return 0;
        }

        return $inicio->diffInMinutes($fin);
    }
}
