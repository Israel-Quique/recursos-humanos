<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FechaEspecialLaboral extends Model
{
    use SoftDeletes;

    protected $table = 'fechas_especiales_laborales';

    protected $fillable = [
        'fecha',
        'sucursal',
        'nombre',
        'descripcion',
        'tipo',
        'hora_entrada',
        'hora_salida',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function getSucursalLabelAttribute(): string
    {
        return $this->sucursal === 'TODAS' ? 'Todas las sucursales' : (string) $this->sucursal;
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted(): void
    {
        static::saving(function (self $fechaEspecial) {
            if ($fechaEspecial->exists && auth()->check()) {
                $fechaEspecial->updated_by = auth()->id();
            }
        });

        static::deleting(function (self $fechaEspecial) {
            if (auth()->check()) {
                $fechaEspecial->deleted_by = auth()->id();
            }
        });
    }
}
