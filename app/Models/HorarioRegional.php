<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HorarioRegional extends Model
{
    use SoftDeletes;

    protected $table = 'horarios_regionales';

    protected $fillable = [
        'sucursal',
        'hora_entrada',
        'hora_salida',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
        static::saving(function (self $horario) {
            if ($horario->exists && auth()->check()) {
                $horario->updated_by = auth()->id();
            }
        });

        static::deleting(function (self $horario) {
            if (auth()->check()) {
                $horario->deleted_by = auth()->id();
            }
        });
    }
}
