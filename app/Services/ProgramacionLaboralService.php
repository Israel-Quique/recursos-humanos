<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProgramacionLaboralService
{
    private array $cacheFechas = [];
    private array $cacheHorarios = [];

    public function obtenerFechaEspecial(Carbon|string|null $fecha, ?string $sucursal = null): ?FechaEspecialLaboral
    {
        if (blank($fecha)) {
            return null;
        }

        $scope = $this->normalizarSucursal($sucursal);
        $key = $this->normalizarFecha($fecha).'|'.$scope;

        if (array_key_exists($key, $this->cacheFechas)) {
            return $this->cacheFechas[$key];
        }

        return $this->cacheFechas[$key] = FechaEspecialLaboral::query()
            ->whereDate('fecha', $this->normalizarFecha($fecha))
            ->whereIn('sucursal', array_values(array_unique([$scope, 'TODAS'])))
            ->orderByRaw("CASE WHEN sucursal = ? THEN 0 ELSE 1 END", [$scope])
            ->first();
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

        if ($carbon->isSaturday()) {
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
                'hora_salida' => null,
            ];
        }

        return [
            'fecha_especial' => $fechaEspecial,
            'horario_regional' => $horarioRegional,
            'laborable' => true,
            'es_feriado' => false,
            'hora_entrada' => $fechaEspecial?->hora_entrada ?: $horaEntradaBase,
            'hora_salida' => $fechaEspecial?->hora_salida ?: $horaSalidaBase,
        ];
    }

    public function obtenerHorarioRegional(?string $sucursal): ?HorarioRegional
    {
        $key = trim((string) $sucursal);

        if ($key === '') {
            return null;
        }

        if (array_key_exists($key, $this->cacheHorarios)) {
            return $this->cacheHorarios[$key];
        }

        return $this->cacheHorarios[$key] = HorarioRegional::query()
            ->where('sucursal', $key)
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
            'dia_completo' => $jornada,
            'horas' => $this->minutosEntreHoras($horaInicio, $horaFin),
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

        return $value === '' ? 'TODAS' : $value;
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
