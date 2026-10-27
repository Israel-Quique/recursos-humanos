<?php

namespace App\Services;

use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BoletaEliminacionService
{
    public function eliminar(PermisoLaboral $incidencia): int
    {
        abort_unless(auth()->user()?->hasRole('administrador'), 403);

        return DB::transaction(function () use ($incidencia) {
            $incidencia = PermisoLaboral::query()->with(['empleado', 'comprobantes'])
                ->lockForUpdate()->findOrFail($incidencia->id);
            $comprobantes = $incidencia->comprobantes->map(function (PermisoComprobante $comprobante) {
                $datos = $comprobante->getAttributes();
                $binario = $datos['archivo_binario'] ?? null;
                unset($datos['archivo_binario']);

                if (empty($datos['archivo_base64'])) {
                    if (is_resource($binario)) {
                        $binario = stream_get_contents($binario);
                    }
                    if (! $binario && $comprobante->ruta_archivo && Storage::disk('public')->exists($comprobante->ruta_archivo)) {
                        $binario = Storage::disk('public')->get($comprobante->ruta_archivo);
                    }
                    $datos['archivo_base64'] = $binario ? base64_encode($binario) : null;
                }

                return $datos;
            })->all();

            $respaldoId = DB::table('boletas_eliminadas')->insertGetId([
                'permiso_laboral_id' => $incidencia->id,
                'empleado_id' => $incidencia->empleado_id,
                'eliminado_por' => auth()->id(),
                'eliminado_por_nombre' => auth()->user()->name,
                'eliminado_por_email' => auth()->user()->email,
                'eliminado_en' => now(),
                'ip' => request()->ip(),
                'datos_boleta' => json_encode($incidencia->getAttributes(), JSON_THROW_ON_ERROR),
                'datos_empleado' => json_encode($incidencia->empleado?->getAttributes(), JSON_THROW_ON_ERROR),
                'comprobantes' => json_encode($comprobantes, JSON_THROW_ON_ERROR),
            ]);
            $incidencia->delete();

            return $respaldoId;
        });
    }
}
