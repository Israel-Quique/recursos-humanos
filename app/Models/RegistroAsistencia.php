<?php

namespace App\Models;

use App\Models\Importacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroAsistencia extends Model
{
    use SoftDeletes;

    protected $table = 'registros_asistencia';

    protected $fillable = [
        'empleado_id',
        'importacion_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'tipo_verificacion',
        'estado_marcacion',
        'evento_biometrico',
        'observacion',
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

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
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

    public function importacion(): BelongsTo
    {
        return $this->belongsTo(Importacion::class, 'importacion_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $registro) {
            if ($registro->exists && auth()->check()) {
                $registro->updated_by = auth()->id();
            }
        });

        static::deleting(function (self $registro) {
            if (auth()->check()) {
                $registro->deleted_by = auth()->id();
            }
        });
    }
}
