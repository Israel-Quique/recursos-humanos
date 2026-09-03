<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function comprobantes(): HasMany
    {
        return $this->hasMany(PermisoComprobante::class, 'permiso_laboral_id');
    }

    public function comprobantePrincipal(): HasOne
    {
        return $this->hasOne(PermisoComprobante::class, 'permiso_laboral_id')->latestOfMany();
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
            'dias' => 'Por días',
            'dia_completo' => 'Por días',
            'horas' => 'Por horas',
            'llegada_tarde' => 'Llegada tarde (Ingreso posterior)',
            'salida_temprana' => 'Salida temprana (Retiro anticipado)',
            'manana' => 'Mañana',
            'tarde' => 'Tarde',
            'medio_dia' => 'Medio día',
            default => ucfirst((string) $this->alcance),
        };
    }
}
