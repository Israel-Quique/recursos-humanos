<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricoDispositivo extends Model
{
    protected $table = 'biometrico_dispositivos';

    protected $fillable = [
        'department',
        'branch',
        'ip',
        'port',
        'connection_mode',
        'communication_password',
        'is_active',
        'last_synced_mark_at',
        'last_seen_at',
        'last_error',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
        'last_synced_mark_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
