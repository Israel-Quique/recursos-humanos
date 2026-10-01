<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TipoPermiso extends Model
{
    protected $table = 'tipos_permisos';

    protected $fillable = [
        'clave',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public static function obtenerTodos(): array
    {
        if (! Schema::hasTable('tipos_permisos')) {
            return static::defaults();
        }

        try {
            $items = static::query()->where('activo', true)->orderBy('nombre')->get();
            if ($items->isEmpty()) {
                static::sembrarDefaults();
                $items = static::query()->where('activo', true)->orderBy('nombre')->get();
            }

            $resultado = [];
            foreach ($items as $item) {
                $resultado[$item->clave] = $item->nombre;
            }

            return $resultado ?: static::defaults();
        } catch (\Throwable $e) {
            return static::defaults();
        }
    }

    public static function defaults(): array
    {
        return [
            'salud' => 'Permiso por salud',
            'consulta_medica' => 'Consulta medica',
            'tramite_personal' => 'Tramite personal',
            'comision_laboral' => 'Comision laboral',
            'estudio' => 'Permiso por estudio',
            'asunto_familiar' => 'Asunto familiar',
        ];
    }

    public static function sembrarDefaults(): void
    {
        foreach (static::defaults() as $clave => $nombre) {
            static::query()->firstOrCreate(['clave' => $clave], [
                'nombre' => $nombre,
                'activo' => true,
            ]);
        }
    }

    public static function generarClave(string $nombre): string
    {
        $base = Str::slug($nombre, '_');
        if (blank($base)) {
            $base = 'tipo_' . time();
        }

        $clave = $base;
        $count = 1;
        while (static::query()->where('clave', $clave)->exists()) {
            $clave = $base . '_' . $count;
            $count++;
        }

        return $clave;
    }
}
