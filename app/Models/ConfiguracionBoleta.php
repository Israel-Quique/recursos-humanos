<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBoleta extends Model
{
    protected $table = 'configuracion_boletas';

    protected $fillable = ['comprobantes_habilitados'];

    protected function casts(): array
    {
        return ['comprobantes_habilitados' => 'boolean'];
    }

    public static function comprobantesHabilitados(): bool
    {
        return (bool) static::query()->whereKey(1)->value('comprobantes_habilitados');
    }
}
