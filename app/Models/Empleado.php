<?php

namespace App\Models;

use App\Models\User;
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
}
