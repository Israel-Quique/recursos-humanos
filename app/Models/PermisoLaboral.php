<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoLaboral extends Model
{
    protected $table = 'permisos_laborales';

    protected $fillable = [
        'empleado_id',
        'tipo',
        'alcance',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'minutos_contabilizados',
        'motivo',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'minutos_contabilizados' => 'integer',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'permiso' => 'Permiso',
            'paro' => 'Paro',
            'incidencia' => 'Incidencia',
            'cumpleanos' => 'Cumpleaños',
            'falta' => 'Falta',
            default => ucfirst((string) $this->tipo),
        };
    }

    public function getAlcanceLabelAttribute(): string
    {
        return match ($this->alcance) {
            'manana' => 'Toda la mañana',
            'tarde' => 'Toda la tarde',
            'dia_completo' => 'Todo el día',
            'medio_dia' => 'Medio día',
            'horas' => 'Por horas',
            default => ucfirst((string) $this->alcance),
        };
    }
}
