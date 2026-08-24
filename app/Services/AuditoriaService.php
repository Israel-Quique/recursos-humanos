<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

class AuditoriaService
{
    public function registrar(
        string $modulo,
        string $accion,
        string $descripcion,
        ?Model $modelo = null,
        ?array $antes = null,
        ?array $despues = null,
        ?array $meta = null
    ): Auditoria {
        return Auditoria::query()->create([
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'actor_id' => auth()->id(),
            'auditable_type' => $modelo ? $modelo::class : null,
            'auditable_id' => $modelo?->getKey(),
            'antes' => $this->limpiarPayload($antes),
            'despues' => $this->limpiarPayload($despues),
            'meta' => $this->limpiarPayload(($meta ?? []) + [
                'ruta' => request()->path(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]),
        ]);
    }

    private function limpiarPayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        return collect($payload)
            ->map(function ($value) {
                if (is_array($value)) {
                    return $this->limpiarPayload($value);
                }

                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }

                return $value;
            })
            ->all();
    }
}
