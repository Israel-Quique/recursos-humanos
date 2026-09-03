<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PermisoComprobante extends Model
{
    protected $table = 'permiso_comprobantes';

    protected $fillable = [
        'permiso_laboral_id',
        'ruta_archivo',
        'archivo_binario',
        'archivo_base64',
        'nombre_original',
        'mime_type',
        'tamano_bytes',
        'created_by',
    ];

    public function permisoLaboral(): BelongsTo
    {
        return $this->belongsTo(PermisoLaboral::class, 'permiso_laboral_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUrlAttribute(): string
    {
        if (! empty($this->archivo_base64)) {
            $mime = $this->mime_type ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . $this->archivo_base64;
        }

        if (! empty($this->archivo_binario)) {
            $streamOrBytes = is_resource($this->archivo_binario)
                ? stream_get_contents($this->archivo_binario)
                : $this->archivo_binario;
            $mime = $this->mime_type ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode($streamOrBytes);
        }

        if ($this->ruta_archivo && Storage::disk('public')->exists($this->ruta_archivo)) {
            $path = Storage::disk('public')->path($this->ruta_archivo);
            if (file_exists($path)) {
                $bytes = file_get_contents($path);
                $mime = $this->mime_type ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
            return Storage::disk('public')->url($this->ruta_archivo);
        }

        return '';
    }

    public function getPathFisicoAttribute(): string
    {
        return Storage::disk('public')->path($this->ruta_archivo ?? '');
    }

    public function getBase64Attribute(): ?string
    {
        return $this->url ?: null;
    }
}
