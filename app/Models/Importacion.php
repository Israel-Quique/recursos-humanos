<?php

namespace App\Models;

use App\Models\User;
use App\Models\RegistroAsistencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importacion extends Model
{
    protected $table = 'importaciones';

    protected $fillable = [
        'nombre_archivo',
        'ruta_archivo',
        'fecha_operativa',
        'registros_total',
        'registros_generados',
        'empleados_detectados',
        'estado',
        'mensaje_error',
        'resumen_json',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_operativa' => 'date',
            'resumen_json' => 'array',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroAsistencia::class, 'importacion_id');
    }
}
