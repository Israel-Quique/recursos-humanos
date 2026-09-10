<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\ReglaSancion;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalisisReglamentoReporteService
{
    public function __construct(
        protected AnalisisAsistenciaService $analisisAsistencia,
        protected ReglamentoSancionService $reglamentoSancion,
    ) {}

    /**
     * Genera el reporte integral del reglamento institucional para el mes y sucursal dados.
     */
    public function generarReporteReglamento(Carbon $referenceMonth, ?string $branch = null): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();
        $gestion = (int) $referenceMonth->format('Y');

        $empleadosQuery = Empleado::query()
            ->activosLaboralmente($end)
            ->when(filled($branch), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));

        $empleados = $empleadosQuery->orderBy('sucursal')->orderBy('apellido')->orderBy('nombre')->get();

        $enAlerta = [];
        $alertasArt45 = [];
        $alertasArt48 = [];
        $masSancionados = [];
        $reincidentes = [];
        $casosCriticos = [];

        $totalDiasSancionInstitucional = 0.0;
        $totalConSancionEconomica = 0;
        $totalEnAlertaPreventiva = 0;
        $totalRiesgoCritico = 0;

        $desgloseSucursal = [];

        foreach ($empleados as $empleado) {
            $detalle = $this->analisisAsistencia->detalleMensualPorEmpleado($empleado->id, $referenceMonth, $empleado->sucursal);

            if (!$detalle) {
                continue;
            }

            $minutosAtraso = (int) ($detalle['retraso_resumen']['total_minutos'] ?? 0);
            $diasTarde = count($detalle['tardanzas'] ?? []);
            $omisionesCount = (int) ($detalle['total_omisiones'] ?? (count($detalle['no_marcados'] ?? []) + count($detalle['faltas'] ?? [])));
            $faltasInjustificadas = count($detalle['faltas'] ?? []);

            // Calcular reincidencia anual de meses con más de 120 min en la gestión
            $mesesGravesGestion = $this->contarMesesConAtrasoGraveEnGestion($empleado, $gestion, (int) $referenceMonth->format('m'));
            if ($minutosAtraso >= 121) {
                $mesesGravesGestion++;
            }

            // Evaluar reglas aplicables
            $reglaAtraso = $this->reglamentoSancion->evaluarAtraso($minutosAtraso, $mesesGravesGestion, $referenceMonth);
            $reglaOmision = $this->reglamentoSancion->evaluarOmision($omisionesCount, $referenceMonth);

            $diasSancionAtraso = (float) ($reglaAtraso?->dias_sancion ?? 0.0);
            $diasSancionOmision = (float) ($reglaOmision?->dias_sancion ?? 0.0);
            $diasSancionInasistencia = 0.0;
            if ($faltasInjustificadas > 0) {
                $reglaInasistencia = $this->reglamentoSancion->evaluarInasistencia($faltasInjustificadas, $referenceMonth);
                $diasSancionInasistencia = (float) ($reglaInasistencia?->dias_sancion ?? ($faltasInjustificadas * 2.0));
            }

            $totalDiasSancionEmpleado = $diasSancionAtraso + $diasSancionOmision + $diasSancionInasistencia;
            $esDestitucion = ($reglaAtraso?->es_destitucion ?? false)
                || ($reglaOmision?->es_destitucion ?? false)
                || $omisionesCount >= 4
                || $mesesGravesGestion >= 3;

            $sucursalNormalizada = SucursalNormalizer::normalize($empleado->sucursal) ?: 'General';

            $infringeArt45 = ($diasSancionAtraso > 0 || $diasSancionOmision > 0 || $diasSancionInasistencia > 0 || $minutosAtraso >= 20);
            $infringeArt48 = $esDestitucion || ($omisionesCount >= 4) || ($mesesGravesGestion >= 3) || ($faltasInjustificadas >= 3) || ($omisionesCount === 3) || ($mesesGravesGestion === 2);
            $esConcurrente = $infringeArt45 && $infringeArt48;

            // Determinar propuesta de resolución administrativa considerando contingencias (bloqueos, feriados, sincronización)
            $propuestaResolucion = '';
            if ($esConcurrente) {
                $propuestaResolucion = "Auditoría de contingencia: Infracción concurrente (Art. 45 y Art. 48). Se propone verificar boletas rezagadas, feriados locales o bloqueos de transporte antes de consolidar sanción doble o proceso de destitución.";
            } elseif ($esDestitucion) {
                $propuestaResolucion = "Revisión técnica de marcaciones: Causal de Art. 48. Verificar si las omisiones corresponden a fallas de lector o justificaciones pendientes antes de remitir a Proceso Administrativo.";
            } elseif ($totalDiasSancionEmpleado > 0) {
                $propuestaResolucion = "Descuento en planilla computable ({$this->formatearDiasSancion($totalDiasSancionEmpleado)}). Proceder según escala oficial del Art. 45.";
            } else {
                $propuestaResolucion = "Aviso preventivo institucional: Notificar al funcionario sobre proximidad a umbrales de sanción.";
            }

            // -------------------------------------------------------------
            // 1. EVALUAR: PERSONAL A PUNTO DE SER SANCIONADO (ZONA DE ALERTA)
            // -------------------------------------------------------------
            $motivosAlerta = [];
            $distanciaAlerta = '';
            $articuloAlerta = 'Art. 45';
            $articuloTituloAlerta = 'Atrasos';

            // Atraso entre 20 y 30 minutos (Art. 45: a punto del descuento de 1/2 día a los 31 min)
            if ($minutosAtraso >= 20 && $minutosAtraso <= 30) {
                $restantesParaSancion = 31 - $minutosAtraso;
                $motivosAlerta[] = "Atraso de {$minutosAtraso} min (a {$restantesParaSancion} min de sanción de 1/2 día)";
                $distanciaAlerta = "A {$restantesParaSancion} min del descuento";
                $articuloAlerta = 'Art. 45';
                $articuloTituloAlerta = 'Atraso al límite';

                $alertasArt45[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 45',
                    'articulo_titulo' => 'Atraso al límite',
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'minutos_atraso' => $minutosAtraso,
                    'tolerancia_limite_minutos' => 30,
                    'tolerancia_usada_minutos' => $minutosAtraso,
                    'minutos_restantes_sancion' => $restantesParaSancion,
                    'porcentaje_tolerancia_usada' => min(100, (int) round(($minutosAtraso / 30) * 100)),
                    'sancion_inminente' => 'Descuento de 1/2 día de haber',
                    'distancia_umbral' => "A {$restantesParaSancion} min de descuento",
                    'explicacion' => "Ha utilizado {$minutosAtraso} min de tolerancia mensual (de 30 min libres). Le restan solo {$restantesParaSancion} min antes de que se le aplique el descuento de 1/2 día al cruzar los 31 min.",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // Omisiones: exactamente 3 (Art. 48: a 1 de destitución por Art. 48.IV)
            if ($omisionesCount === 3) {
                $motivosAlerta[] = "Registra 3 omisiones en el mes (a solo 1 omisión de destitución según Art. 48)";
                $distanciaAlerta = "A 1 omisión de destitución";
                $articuloAlerta = 'Art. 48';
                $articuloTituloAlerta = 'Omisiones críticas';

                $alertasArt48[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 48',
                    'articulo_titulo' => 'Límite de Omisiones (3 de 4)',
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'omisiones' => $omisionesCount,
                    'omisiones_usadas' => $omisionesCount,
                    'omisiones_limite' => 4,
                    'omisiones_restantes' => 1,
                    'porcentaje_usado' => 75,
                    'sancion_inminente' => 'Causal de Destitución (Art. 48.IV)',
                    'distancia_umbral' => "A 1 omisión de destitución",
                    'explicacion' => "Ha acumulado 3 omisiones de marcación (de 3 permitidas). Le resta 1 omisión para ingresar a proceso de destitución según el Art. 48 inciso IV.",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // Reincidencia anual: exactamente 2 meses con 121+ min (Art. 48: a 1 reincidencia de destitución)
            if ($mesesGravesGestion === 2 && $minutosAtraso < 121) {
                $motivosAlerta[] = "Acumula 2 meses en el año con más de 120 min (si supera 120 min este mes, alcanzará la 3ra vez y destitución)";
                $distanciaAlerta = "A 1 mes grave de destitución";
                $articuloAlerta = 'Art. 48';
                $articuloTituloAlerta = 'Reincidencia anual';

                $alertasArt48[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 48',
                    'articulo_titulo' => 'Reincidencia Anual (2 de 3)',
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'meses_graves' => $mesesGravesGestion,
                    'meses_graves_usados' => $mesesGravesGestion,
                    'meses_graves_limite' => 3,
                    'meses_restantes' => 1,
                    'porcentaje_usado' => 67,
                    'sancion_inminente' => 'Causal de Destitución (Art. 48.I)',
                    'distancia_umbral' => "A 1 mes de destitución",
                    'explicacion' => "Ya tiene 2 meses en la gestión {$gestion} con más de 120 min de retraso. Si supera los 120 min este mes, será la 3ra reincidencia con destitución.",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            if (!empty($motivosAlerta)) {
                $totalEnAlertaPreventiva++;
                $enAlerta[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'minutos_atraso' => $minutosAtraso,
                    'omisiones' => $omisionesCount,
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'motivos' => $motivosAlerta,
                    'motivo_principal' => $motivosAlerta[0],
                    'distancia_umbral' => $distanciaAlerta,
                    'articulo' => $articuloAlerta,
                    'articulo_titulo' => $articuloTituloAlerta,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // 2. EVALUAR: PERSONAL MÁS SANCIONADO (TOP SANCIONADOS - ART. 45)
            // -------------------------------------------------------------
            if ($totalDiasSancionEmpleado > 0 || $esDestitucion) {
                $totalConSancionEconomica++;
                $totalDiasSancionInstitucional += $totalDiasSancionEmpleado;

                $desgloseSanciones = [];
                if ($diasSancionAtraso > 0) {
                    $desgloseSanciones[] = "Atraso: {$minutosAtraso} min → {$reglaAtraso?->sancion_texto}";
                }
                if ($diasSancionOmision > 0) {
                    $desgloseSanciones[] = "Omisión: {$omisionesCount} veces → {$reglaOmision?->sancion_texto}";
                }
                if ($diasSancionInasistencia > 0) {
                    $desgloseSanciones[] = "Inasistencia: {$faltasInjustificadas} faltas → {$diasSancionInasistencia} días";
                }

                $masSancionados[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'minutos_atraso' => $minutosAtraso,
                    'minutos_acumulados' => $minutosAtraso,
                    'dias_tarde' => $diasTarde,
                    'omisiones' => $omisionesCount,
                    'faltas' => $faltasInjustificadas,
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'dias_sancion_atraso' => $diasSancionAtraso,
                    'dias_sancion_atraso_texto' => $this->formatearDiasSancion($diasSancionAtraso),
                    'dias_sancion_omision' => $diasSancionOmision,
                    'dias_sancion_omision_texto' => $this->formatearDiasSancion($diasSancionOmision),
                    'dias_sancion_inasistencia' => $diasSancionInasistencia,
                    'dias_sancion_inasistencia_texto' => $this->formatearDiasSancion($diasSancionInasistencia),
                    'dias_sancion_total' => $totalDiasSancionEmpleado,
                    'total_dias_sancion_texto' => $this->formatearDiasSancion($totalDiasSancionEmpleado),
                    'resumen_atraso' => $minutosAtraso > 0 ? "{$minutosAtraso} min acumulados → Descuento: {$this->formatearDiasSancion($diasSancionAtraso)}" : 'Sin atraso',
                    'resumen_total_descuento' => "Se le descontará {$this->formatearDiasSancion($totalDiasSancionEmpleado)} de sueldo",
                    'es_destitucion' => $esDestitucion,
                    'desglose' => $desgloseSanciones,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // 3. EVALUAR: CASOS CRÍTICOS Y REINCIDENTES (ART. 48)
            // -------------------------------------------------------------
            $esCritico = false;
            $causalesCriticas = [];

            if ($esDestitucion || $mesesGravesGestion >= 3) {
                $esCritico = true;
                $causalesCriticas[] = "121+ min de atraso acumulado por 3ra vez en la gestión anual (Art. 48.I)";
            }

            if ($omisionesCount >= 4) {
                $esCritico = true;
                $causalesCriticas[] = "4 o más omisiones de asistencia en el mes (Art. 48.IV)";
            }

            if ($mesesGravesGestion >= 2) {
                $reincidentes[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'meses_graves_gestion' => $mesesGravesGestion,
                    'minutos_mes_actual' => $minutosAtraso,
                    'omisiones_mes_actual' => $omisionesCount,
                    'es_critico' => $esCritico,
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'detalle_reincidencia' => "Ha superado los 120 min en {$mesesGravesGestion} meses de la gestión {$gestion}.",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            if ($esCritico) {
                $totalRiesgoCritico++;
                $casosCriticos[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'S/C',
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'causales' => $causalesCriticas,
                    'causal_principal' => $causalesCriticas[0],
                    'es_concurrente' => $esConcurrente,
                    'propuesta_resolucion' => $propuestaResolucion,
                    'dias_sancion_total' => $totalDiasSancionEmpleado,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // Acumular desglose por sucursal
            if (!isset($desgloseSucursal[$sucursalNormalizada])) {
                $desgloseSucursal[$sucursalNormalizada] = [
                    'nombre' => $sucursalNormalizada,
                    'evaluados' => 0,
                    'en_alerta' => 0,
                    'sancionados' => 0,
                    'criticos' => 0,
                    'dias_sancion' => 0.0,
                ];
            }

            $desgloseSucursal[$sucursalNormalizada]['evaluados']++;
            if (!empty($motivosAlerta)) {
                $desgloseSucursal[$sucursalNormalizada]['en_alerta']++;
            }
            if ($totalDiasSancionEmpleado > 0 || $esDestitucion) {
                $desgloseSucursal[$sucursalNormalizada]['sancionados']++;
                $desgloseSucursal[$sucursalNormalizada]['dias_sancion'] += $totalDiasSancionEmpleado;
            }
            if ($esCritico) {
                $desgloseSucursal[$sucursalNormalizada]['criticos']++;
            }
        }

        // Ordenar personal más sancionado de mayor sanción a menor
        $masSancionados = collect($masSancionados)
            ->sortByDesc(fn($i) => ($i['es_destitucion'] ? 1000 : 0) + $i['dias_sancion_total'])
            ->values()
            ->all();

        // Ordenar personal en alerta por cercanía al umbral (mayor retraso o más omisiones primero)
        $enAlerta = collect($enAlerta)
            ->sortByDesc(fn($i) => ($i['omisiones'] * 100) + $i['minutos_atraso'])
            ->values()
            ->all();

        // Ordenar reincidentes de mayor número de meses graves a menor
        $reincidentes = collect($reincidentes)
            ->sortByDesc('meses_graves_gestion')
            ->values()
            ->all();

        // Extraer casos concurrentes (infringen o rozan ambos artículos)
        $concurrentes = collect(array_merge($masSancionados, $casosCriticos, $enAlerta))
            ->filter(fn($i) => !empty($i['es_concurrente']))
            ->unique('id')
            ->values()
            ->all();

        return [
            'metricas' => [
                'total_evaluados' => $empleados->count(),
                'en_alerta_preventiva' => $totalEnAlertaPreventiva,
                'con_sancion_economica' => $totalConSancionEconomica,
                'riesgo_critico' => $totalRiesgoCritico,
                'concurrencia_articulos' => count($concurrentes),
                'total_dias_sancion' => $totalDiasSancionInstitucional,
                'total_dias_sancion_formato' => $this->formatearDiasSancion($totalDiasSancionInstitucional),
            ],
            'personal_en_alerta' => $enAlerta,
            'mas_sancionados' => $masSancionados,
            'reincidentes' => $reincidentes,
            'casos_criticos' => $casosCriticos,
            'concurrentes' => $concurrentes,
            'art_45' => [
                'titulo' => 'Artículo 45 · Atrasos, Inasistencias y Sanciones Salariales',
                'alertas' => $alertasArt45,
                'sancionados' => $masSancionados,
                'total_alertas' => count($alertasArt45),
                'total_sancionados' => count($masSancionados),
            ],
            'art_48' => [
                'titulo' => 'Artículo 48 · Causales Graves y Destitución',
                'alertas' => $alertasArt48,
                'casos_criticos' => $casosCriticos,
                'reincidentes' => $reincidentes,
                'total_alertas' => count($alertasArt48),
                'total_criticos' => count($casosCriticos),
            ],
            'por_sucursal' => $desgloseSucursal,
            'periodo_label' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'gestion' => $gestion,
        ];
    }

    private static array $reincidenciaCache = [];

    /**
     * Cuenta cuántos meses previos en la gestión anual un empleado superó o igualó los 121 minutos de retraso.
     * Optimizado para verificar únicamente meses donde existen registros biométricos del funcionario.
     */
    protected function contarMesesConAtrasoGraveEnGestion(Empleado $empleado, int $gestion, int $mesExcluir): int
    {
        if ($mesExcluir <= 1) {
            return 0;
        }

        $cacheKey = "{$empleado->id}_{$gestion}_{$mesExcluir}";
        if (isset(self::$reincidenciaCache[$cacheKey])) {
            return self::$reincidenciaCache[$cacheKey];
        }

        $startDate = "{$gestion}-01-01";
        $endDate = Carbon::createFromDate($gestion, $mesExcluir, 1)->startOfMonth()->toDateString();

        // Si no tiene registros tardíos tras tolerancia en meses anteriores, evitamos cálculos costosos
        $hasLate = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<', $endDate)
            ->whereNotNull('hora_entrada')
            ->whereTime('hora_entrada', '>', '08:35:00')
            ->exists();

        if (! $hasLate) {
            return self::$reincidenciaCache[$cacheKey] = 0;
        }

        $fechas = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<', $endDate)
            ->whereNotNull('hora_entrada')
            ->pluck('fecha');

        if ($fechas->isEmpty()) {
            return self::$reincidenciaCache[$cacheKey] = 0;
        }

        $mesesConRegistros = $fechas->map(fn($f) => (int) Carbon::parse($f)->format('m'))->unique()->all();

        $conteo = 0;
        foreach ($mesesConRegistros as $m) {
            $mesRef = Carbon::createFromDate($gestion, $m, 1);
            $detalle = $this->analisisAsistencia->detalleMensualPorEmpleado($empleado->id, $mesRef, $empleado->sucursal);

            if (!$detalle) {
                continue;
            }

            $minutosMes = (int) ($detalle['retraso_resumen']['total_minutos'] ?? 0);

            if ($minutosMes >= 121) {
                $conteo++;
            }
        }

        return self::$reincidenciaCache[$cacheKey] = $conteo;
    }

    /**
     * Da formato legible a los días de haber sancionados.
     */
    public function formatearDiasSancion(float $dias): string
    {
        if ($dias <= 0.0) {
            return '0 días';
        }

        if ($dias == 0.5) {
            return '1/2 día';
        }

        if ($dias == 1.0) {
            return '1 día';
        }

        if (floor($dias) == $dias) {
            return ((int) $dias) . ' días';
        }

        $entero = (int) floor($dias);
        return "{$entero} ½ días";
    }
}
