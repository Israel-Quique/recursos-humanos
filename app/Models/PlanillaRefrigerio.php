<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanillaRefrigerio extends Model
{
    protected $table = 'planillas_refrigerio';

    protected $fillable = [
        'periodo',
        'sucursal',
        'tarifa_diaria',
        'datos',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tarifa_diaria' => 'decimal:2',
            'datos' => 'array',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
