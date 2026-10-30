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
        'tipo_permiso_clave',
        'alcance',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'minutos_contabilizados',
        'motivo',
        'motivo_rechazo',
        'created_by',
        'boleta_cargo',
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

    public function editablePorTitular(): bool
    {
        return $this->tipo === 'permiso' && $this->created_at
            && now()->greaterThanOrEqualTo($this->created_at)
            && now()->lessThan($this->created_at->copy()->addHours(24));
    }

    public function getTipoBoletaAttribute(): string
    {
        if (! str_contains((string) $this->motivo, ':')) {
            return 'particular';
        }

        $prefijo = mb_strtolower(trim(explode(':', $this->motivo ?? '', 2)[0]));

        return match ($prefijo) {
            'comision', 'comisión' => 'comision',
            'medico', 'médico' => 'medico',
            'omision', 'omisión' => 'omision',
            'retraso' => 'retraso',
            'rip' => 'rip',
            'particular' => 'particular',
            default => str_contains($prefijo, 'comision') || str_contains($prefijo, 'comisión')
                ? 'comision'
                : ((str_contains($prefijo, 'medico') || str_contains($prefijo, 'médico'))
                    ? 'medico'
                    : ((str_contains($prefijo, 'rip') || str_contains($prefijo, 'art')) ? 'rip' : 'particular')),
        };
    }

    public function getMotivoSinPrefijoAttribute(): string
    {
        $texto = trim((string) ($this->motivo ?? ''));

        while (preg_match('/^(?:particular|comisi[oó]n|m[eé]dico|rip|art\.?\s*45(?:\s*,?\s*par[aá]g\.?\s*iii)?(?:\s*del)?\s*r\.?i\.?p\.?|omisi[oó]n|retraso)\s*:\s*(.*)$/iu', $texto, $matches)) {
            $texto = trim($matches[1]);
        }

        return $texto;
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
