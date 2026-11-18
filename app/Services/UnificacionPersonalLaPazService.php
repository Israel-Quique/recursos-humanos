<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Support\SucursalNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnificacionPersonalLaPazService
{
    public function ejecutar(): int
    {
        $total = DB::transaction(function () {
            $grupos = Empleado::query()->withTrashed()->orderBy('id')->lockForUpdate()->get()
                ->filter(fn (Empleado $empleado) => !$empleado->unificado_en_id && filled($empleado->codigo_biometrico)
                    && SucursalNormalizer::sedeLaPaz($empleado->sucursal))
                ->groupBy(fn (Empleado $empleado) => preg_replace('/^(?:la-paz|el-alto):/', '', trim($empleado->codigo_biometrico)));
            $total = 0;

            foreach ($grupos as $codigo => $empleados) {
                $principal = $empleados->firstWhere('codigo_biometrico', (string) $codigo) ?? $empleados->first();
                foreach ($empleados as $duplicado) {
                    if ($duplicado->id === $principal->id) {
                        continue;
                    }

                    $this->fusionar($principal, $duplicado);
                    $total++;
                }

                if ($principal->codigo_biometrico !== (string) $codigo
                    && !Empleado::withTrashed()->where('codigo_biometrico', (string) $codigo)->exists()) {
                    $principal->update(['codigo_biometrico' => (string) $codigo]);
                }
            }

            return $total;
        });

        Cache::forever('reportes:version', (string) Str::uuid());

        return $total;
    }

    private function fusionar(Empleado $principal, Empleado $duplicado): void
    {
        $antes = [
            'principal' => $principal->getAttributes(),
            'duplicado' => $duplicado->getAttributes(),
            'marcaciones' => RegistroAsistencia::withTrashed()
                ->whereIn('empleado_id', [$principal->id, $duplicado->id])->get()->toArray(),
            'reglas_acceso' => DB::table('acceso_personal_reglas')->where('tipo', 'persona')
                ->whereIn('referencia', [(string) $principal->id, (string) $duplicado->id])->get()->toArray(),
        ];
        if ($principal->trashed() && !$duplicado->trashed()) {
            $principal->restore();
        }

        foreach (['email', 'foto', 'fecha_nacimiento'] as $campo) {
            if (blank($principal->$campo) && filled($duplicado->$campo)) {
                $principal->$campo = $duplicado->$campo;
            }
        }
        if ($principal->apellido === 'Sin apellido' && $duplicado->apellido !== 'Sin apellido') {
            $principal->nombre = $duplicado->nombre;
            $principal->apellido = $duplicado->apellido;
        }
        $principal->es_especial = $principal->es_especial || $duplicado->es_especial;
        $principal->save();

        foreach ($duplicado->asistencias()->withTrashed()->lockForUpdate()->get() as $origen) {
            $destinos = $principal->asistencias()->withTrashed()
                ->whereDate('fecha', $origen->fecha->toDateString())->lockForUpdate()->get();
            $destino = $destinos->first(fn ($registro) => !$registro->trashed()) ?? $destinos->first();
            if (!$destino) {
                $origen->update(['empleado_id' => $principal->id]);
                continue;
            }
            if ($origen->trashed()) {
                continue;
            }

            if (!$destino->trashed() && $destino->tipo_verificacion !== 'Especial' && !$principal->es_especial) {
                $analisis = app(AnalisisAsistenciaService::class);
                $a = $analisis->normalizarMarcacionAsistencia($destino);
                $b = $analisis->normalizarMarcacionAsistencia($origen);
                $entrada = collect([$a['entrada'], $b['entrada']])->filter()->sort()->first();
                $salida = collect([$a['salida'], $b['salida']])->filter()->sortDesc()->first();
                $sedes = ($destino->sedes_biometricas ?? []) + ($origen->sedes_biometricas ?? []);
                $destino->fill([
                    'hora_entrada' => $entrada,
                    'hora_salida' => $salida === $entrada ? null : $salida,
                    'sedes_biometricas' => array_intersect_key($sedes, array_flip(array_filter([$entrada, $salida]))) ?: null,
                    'estado_marcacion' => $entrada ? ($salida ? 'Completo' : 'Entrada') : 'Salida',
                ])->save();
            }

            // El original se conserva archivado para poder revisar la fusión.
            $origen->delete();
        }

        foreach (['permisos_laborales', 'users', 'boletas_eliminadas'] as $tabla) {
            DB::table($tabla)->where('empleado_id', $duplicado->id)->update(['empleado_id' => $principal->id]);
        }
        $reglaPrincipal = DB::table('acceso_personal_reglas')->where('tipo', 'persona')
            ->where('referencia', (string) $principal->id)->exists();
        if (!$reglaPrincipal) {
            DB::table('acceso_personal_reglas')->where('tipo', 'persona')->where('referencia', (string) $duplicado->id)
                ->update(['referencia' => (string) $principal->id]);
        }

        $duplicado->codigo_biometrico = null;
        $duplicado->unificado_en_id = $principal->id;
        $duplicado->save();
        $duplicado->delete();
        app(AuditoriaService::class)->registrar(
            'Personal', 'Unificar', 'Se unificó el padrón compartido de La Paz y El Alto.',
            $principal, $antes, ['empleado_id' => $principal->id, 'duplicado_archivado_id' => $duplicado->id],
        );
    }

}
