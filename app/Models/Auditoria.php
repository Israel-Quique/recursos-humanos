<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = [
        'modulo',
        'accion',
        'descripcion',
        'actor_id',
        'auditable_type',
        'auditable_id',
        'antes',
        'despues',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'antes' => 'array',
            'despues' => 'array',
            'meta' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
