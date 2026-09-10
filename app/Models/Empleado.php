<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'apellido',
        'codigo_biometrico',
        'email',
        'area',
        'sucursal',
        'es_especial',
        'hora_entrada_programada',
        'hora_salida_programada',
        'fecha_nacimiento',
        'fecha_contratacion',
        'fecha_despido',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'es_especial' => 'boolean',
            'fecha_nacimiento' => 'date',
            'fecha_contratacion' => 'date',
            'fecha_despido' => 'date',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(RegistroAsistencia::class, 'empleado_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }

    public function getEstadoLaboralAttribute(): string
    {
        return $this->estadoLaboral(now());
    }

    public function scopeWithUltimaMarcacion(Builder $query): Builder
    {
        return $query->withMax('asistencias', 'fecha');
    }

    public function scopeLaboralVigente(Builder $query, Carbon|string|null $fecha = null): Builder
    {
        $fechaReferencia = $fecha instanceof Carbon
            ? $fecha->toDateString()
            : ($fecha ?: now()->toDateString());

        return $query->where(function ($nestedQuery) use ($fechaReferencia) {
            $nestedQuery->whereNull('fecha_despido')
                ->orWhereDate('fecha_despido', '>', $fechaReferencia);
        })->where(function ($nestedQuery) use ($fechaReferencia) {
            $nestedQuery->whereNull('fecha_contratacion')
                ->orWhereDate('fecha_contratacion', '<=', $fechaReferencia);
        });
    }

    public function scopeActivosLaboralmente(Builder $query, Carbon|string|null $fecha = null, int $diasInactividad = 30): Builder
    {
        $fechaReferencia = $fecha instanceof Carbon
            ? $fecha->copy()
            : Carbon::parse($fecha ?: now());
        $fechaRefStr = $fechaReferencia->toDateString();
        $umbralStr = $fechaReferencia->copy()->subDays($diasInactividad)->startOfDay()->toDateString();

        return $query->where(function ($nestedQuery) use ($fechaRefStr) {
            $nestedQuery->whereNull('fecha_despido')
                ->orWhereDate('fecha_despido', '>', $fechaRefStr);
        })->where(function ($nestedQuery) use ($fechaRefStr) {
            $nestedQuery->whereNull('fecha_contratacion')
                ->orWhereDate('fecha_contratacion', '<=', $fechaRefStr);
        })->where(function ($nestedQuery) use ($umbralStr) {
            $nestedQuery->where('es_especial', true)
                ->orWhereHas('asistencias', function ($sub) use ($umbralStr) {
                    $sub->where('fecha', '>=', $umbralStr);
                })
                ->orWhere(function ($sub) use ($umbralStr) {
                    $sub->whereNotNull('fecha_contratacion')
                        ->where('fecha_contratacion', '>=', $umbralStr);
                });
        });
    }

    public function scopeInactivosLaboralmente(Builder $query, Carbon|string|null $fecha = null, int $diasInactividad = 30): Builder
    {
        $fechaReferencia = $fecha instanceof Carbon
            ? $fecha->copy()
            : Carbon::parse($fecha ?: now());
        $fechaRefStr = $fechaReferencia->toDateString();
        $umbralStr = $fechaReferencia->copy()->subDays($diasInactividad)->startOfDay()->toDateString();

        return $query->where(function ($q) use ($fechaRefStr, $umbralStr) {
            $q->where(function ($sub) use ($fechaRefStr) {
                $sub->whereNotNull('fecha_despido')
                    ->whereDate('fecha_despido', '<=', $fechaRefStr);
            })->orWhere(function ($sub) use ($fechaRefStr) {
                $sub->whereNotNull('fecha_contratacion')
                    ->whereDate('fecha_contratacion', '>', $fechaRefStr);
            })->orWhere(function ($sub) use ($umbralStr) {
                $sub->where('es_especial', false)
                    ->whereDoesntHave('asistencias', function ($asist) use ($umbralStr) {
                        $asist->where('fecha', '>=', $umbralStr);
                    })
                    ->where(function ($contratacion) use ($umbralStr) {
                        $contratacion->whereNull('fecha_contratacion')
                            ->orWhere('fecha_contratacion', '<', $umbralStr);
                    });
            });
        });
    }

    public function scopeEspeciales(Builder $query): Builder
    {
        return $query->where('es_especial', true);
    }

    public function scopeNoEspeciales(Builder $query): Builder
    {
        return $query->where('es_especial', false);
    }

    public function ultimaMarcacion(): ?Carbon
    {
        $fecha = $this->asistencias_max_fecha ?? null;

        if ($fecha instanceof Carbon) {
            return $fecha;
        }

        if (blank($fecha)) {
            if ($this->relationLoaded('asistencias')) {
                $maxFecha = $this->asistencias->max('fecha');
                return $maxFecha ? Carbon::parse($maxFecha) : null;
            }

            if ($this->exists) {
                $maxFecha = $this->asistencias()->max('fecha');
                return $maxFecha ? Carbon::parse($maxFecha) : null;
            }

            return null;
        }

        try {
            return Carbon::parse((string) $fecha);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function estadoLaboral(Carbon|string|null $fecha = null, int $diasInactividad = 30): string
    {
        $fechaReferencia = $fecha instanceof Carbon
            ? $fecha->copy()
            : Carbon::parse($fecha ?: now());

        if ($this->trashed()) {
            return 'Inactivo';
        }

        if ($this->fecha_despido !== null) {
            $fechaDespido = $this->fecha_despido instanceof Carbon
                ? $this->fecha_despido->copy()->startOfDay()
                : Carbon::parse((string) $this->fecha_despido)->startOfDay();

            if ($fechaReferencia->copy()->startOfDay()->gte($fechaDespido)) {
                return 'Inactivo';
            }
        }

        if ($this->fecha_contratacion !== null) {
            $fechaContratacion = $this->fecha_contratacion instanceof Carbon
                ? $this->fecha_contratacion->copy()->startOfDay()
                : Carbon::parse((string) $this->fecha_contratacion)->startOfDay();

            if ($fechaReferencia->copy()->startOfDay()->lt($fechaContratacion)) {
                return 'Inactivo';
            }
        }

        if ($this->es_especial) {
            return 'Activo';
        }

        $umbralInactividad = $fechaReferencia->copy()->subDays($diasInactividad)->startOfDay();
        $ultimaMarcacion = $this->ultimaMarcacion();

        if ($ultimaMarcacion) {
            return $ultimaMarcacion->lt($umbralInactividad) ? 'Inactivo' : 'Activo';
        }

        $fechaContratacion = $this->fecha_contratacion?->copy()?->startOfDay();

        if ($fechaContratacion && $fechaContratacion->lt($umbralInactividad)) {
            return 'Inactivo';
        }

        if (!$fechaContratacion && !$ultimaMarcacion) {
            return 'Inactivo';
        }

        return 'Activo';
    }

    public function estaActivoLaboralmente(Carbon|string|null $fecha = null, int $diasInactividad = 30): bool
    {
        return $this->estadoLaboral($fecha, $diasInactividad) === 'Activo';
    }
}
