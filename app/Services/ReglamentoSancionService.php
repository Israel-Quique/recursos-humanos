<?php

namespace App\Services;

use App\Models\ReglaSancion;
use Carbon\CarbonInterface;
use Database\Seeders\ReglaSancionSeeder;
use Illuminate\Database\Eloquent\Collection;

class ReglamentoSancionService
{
    /**
     * Evalúa los minutos de atraso acumulados en el mes y devuelve la regla aplicable.
     * Toma en cuenta la reincidencia en la gestión anual para faltas gravísimas (121+ min por 3ra vez),
     * y filtra solo las reglas vigentes en la fecha/mes/gestión de referencia.
     */
    public function evaluarAtraso(int $minutosAtraso, int $vecesEnGestion = 1, ?CarbonInterface $fechaReferencia = null): ?ReglaSancion
    {
        if ($minutosAtraso <= 0) {
            return null;
        }

        // Si supera o iguala 121 minutos y es por 3ra vez en la gestión anual, es falta gravísima
        if ($minutosAtraso >= 121 && $vecesEnGestion >= 3) {
            $gravisimas = ReglaSancion::query()
                ->gravisimas()
                ->activo()
                ->where('unidad', 'minutos')
                ->where('es_destitucion', true)
                ->get();

            $gravisima = $gravisimas->first(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($fechaReferencia));

            if ($gravisima) {
                return $gravisima;
            }
        }

        // Buscar en la escala estándar de atrasos
        $reglas = ReglaSancion::query()
            ->atrasos()
            ->activo()
            ->orderBy('orden')
            ->get()
            ->filter(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($fechaReferencia));

        foreach ($reglas as $regla) {
            if ($regla->aplicaParaValor($minutosAtraso)) {
                return $regla;
            }
        }

        return null;
    }

    /**
     * Evalúa si existe alguna regla para los minutos dados ignorando las fechas/meses de vigencia
     * (para detectar periodos de transición o marcha blanca de reglamentos nuevos).
     */
    public function evaluarAtrasoSinFiltroFecha(int $minutosAtraso): ?ReglaSancion
    {
        if ($minutosAtraso <= 0) {
            return null;
        }

        return ReglaSancion::query()
            ->atrasos()
            ->where('activo', true)
            ->where('tipo_vigencia', '!=', 'no_aplica')
            ->orderBy('orden')
            ->get()
            ->first(fn(ReglaSancion $r) => $r->aplicaParaValor($minutosAtraso));
    }

    /**
     * Evalúa inasistencia o ausencia en días.
     */
    public function evaluarInasistencia(float $dias, ?CarbonInterface $fechaReferencia = null): ?ReglaSancion
    {
        if ($dias <= 0) {
            return null;
        }

        return ReglaSancion::query()
            ->inasistencias()
            ->activo()
            ->orderBy('orden')
            ->get()
            ->filter(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($fechaReferencia))
            ->first(function (ReglaSancion $regla) use ($dias) {
                if ($dias <= 0.5 && str_contains($regla->causal, 'Medio')) {
                    return true;
                }
                if ($dias > 0.5 && str_contains($regla->causal, 'Un (1) día')) {
                    return true;
                }
                return false;
            });
    }

    /**
     * Evalúa las omisiones en el registro de asistencia dentro del mes.
     */
    public function evaluarOmision(int $ocurrenciasMes, ?CarbonInterface $fechaReferencia = null): ?ReglaSancion
    {
        if ($ocurrenciasMes <= 0) {
            return null;
        }

        if ($ocurrenciasMes >= 4) {
            $gravisimas = ReglaSancion::query()
                ->gravisimas()
                ->activo()
                ->where('unidad', 'ocurrencias')
                ->get()
                ->filter(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($fechaReferencia));

            if ($gravisimas->isNotEmpty()) {
                return $gravisimas->first();
            }
        }

        return ReglaSancion::query()
            ->omisiones()
            ->activo()
            ->orderBy('orden')
            ->get()
            ->filter(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($fechaReferencia))
            ->first(fn(ReglaSancion $r) => $r->aplicaParaValor($ocurrenciasMes));
    }

    /**
     * Aplica configuración de vigencia masiva a una categoría de reglas o a todas.
     */
    public function aplicarVigenciaMasiva(string $categoria, array $params): int
    {
        $query = ReglaSancion::query();
        if ($categoria !== 'todas') {
            $query->where('categoria', $categoria);
        }

        $payload = [
            'activo' => (bool) ($params['activo'] ?? true),
            'tipo_vigencia' => $params['tipo_vigencia'] ?? 'siempre',
            'aplica_desde_gestion' => ! empty($params['aplica_desde_gestion']) ? (int) $params['aplica_desde_gestion'] : null,
            'aplica_desde_mes' => ! empty($params['aplica_desde_mes']) ? (int) $params['aplica_desde_mes'] : null,
            'aplica_hasta_gestion' => ! empty($params['aplica_hasta_gestion']) ? (int) $params['aplica_hasta_gestion'] : null,
            'aplica_hasta_mes' => ! empty($params['aplica_hasta_mes']) ? (int) $params['aplica_hasta_mes'] : null,
            'explicacion_vigencia' => ! empty($params['explicacion_vigencia']) ? trim($params['explicacion_vigencia']) : null,
        ];

        return $query->update($payload);
    }

    /**
     * Devuelve todas las reglas agrupadas por categoría para la interfaz del administrador.
     */
    public function obtenerReglasAgrupadas(): array
    {
        $todas = ReglaSancion::query()->orderBy('categoria')->orderBy('orden')->get();

        return [
            'atraso' => $todas->where('categoria', 'atraso')->values(),
            'inasistencia' => $todas->where('categoria', 'inasistencia')->values(),
            'omision' => $todas->where('categoria', 'omision')->values(),
            'gravisima' => $todas->where('categoria', 'gravisima')->values(),
        ];
    }

    /**
     * Restaura las reglas oficiales por defecto según el reglamento institucional.
     */
    public function restaurarPorDefecto(): void
    {
        app(ReglaSancionSeeder::class)->run();
    }
}
