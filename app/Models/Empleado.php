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
        'area',
        'sucursal',
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
                ->orWhereDate('fecha_despido', '>=', $fechaReferencia);
        });
    }

    public function ultimaMarcacion(): ?Carbon
    {
        $fecha = $this->asistencias_max_fecha ?? null;

        if ($fecha instanceof Carbon) {
            return $fecha;
        }

        if (blank($fecha)) {
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
        $umbralInactividad = $fechaReferencia->copy()->subDays($diasInactividad)->startOfDay();
        $ultimaMarcacion = $this->ultimaMarcacion();

        if ($ultimaMarcacion) {
            return $ultimaMarcacion->lt($umbralInactividad) ? 'Inactivo' : 'Activo';
        }

        $fechaContratacion = $this->fecha_contratacion?->copy()?->startOfDay();

        if ($fechaContratacion && $fechaContratacion->lt($umbralInactividad)) {
            return 'Inactivo';
        }

        return 'Activo';
    }

    public function estaActivoLaboralmente(Carbon|string|null $fecha = null, int $diasInactividad = 30): bool
    {
        return $this->estadoLaboral($fecha, $diasInactividad) === 'Activo';
    }
}
