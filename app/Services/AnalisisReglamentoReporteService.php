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
        $concurrentes = [];

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
            $codigoEmpleado = !empty($empleado->codigo_biometrico) ? (string) $empleado->codigo_biometrico : 'CI: ' . $empleado->id;

            // Calcular reincidencia anual de meses con más de 120 min en la gestión
            $mesesGravesGestion = $this->contarMesesConAtrasoGraveEnGestion($empleado, $gestion, (int) $referenceMonth->format('m'));
            if ($minutosAtraso >= 121) {
                $mesesGravesGestion++;
            }

            // 1. Art. 45.I: Escala de Atrasos
            $diasSancionAtraso = 0.0;
            if ($minutosAtraso >= 31 && $minutosAtraso <= 45) {
                $diasSancionAtraso = 0.5;
            } elseif ($minutosAtraso >= 46 && $minutosAtraso <= 60) {
                $diasSancionAtraso = 1.0;
            } elseif ($minutosAtraso >= 61 && $minutosAtraso <= 90) {
                $diasSancionAtraso = 2.0;
            } elseif ($minutosAtraso >= 91 && $minutosAtraso <= 120) {
                $diasSancionAtraso = 3.0;
            } elseif ($minutosAtraso >= 121) {
                $diasSancionAtraso = 4.0;
            }

            // 2. Art. 45.II: Inasistencias y Ausencias en el Puesto de Trabajo
            // Según Art. 45.II: Genera el DOBLE de descuento (engloba el día no trabajado más infracción administrativa)
            // 1/2 día de falta = 1 día descuento; 1 día de falta = 2 días descuento
            $diasSancionInasistencia = (float) ($faltasInjustificadas * 2.0);

            // 3. Art. 45.III: Omisiones en el Registro de Asistencia
            // 1ra vez = 1/2 día; 2da vez = 1 día; 3ra vez = 1.5 días; 4ta vez = destitución (Art. 48.IV) + 2 días
            $diasSancionOmision = 0.0;
            if ($omisionesCount === 1) {
                $diasSancionOmision = 0.5;
            } elseif ($omisionesCount === 2) {
                $diasSancionOmision = 1.0;
            } elseif ($omisionesCount === 3) {
                $diasSancionOmision = 1.5;
            } elseif ($omisionesCount >= 4) {
                $diasSancionOmision = 2.0;
            }

            $totalDiasSancionEmpleado = $diasSancionAtraso + $diasSancionInasistencia + $diasSancionOmision;

            // 4. Art. 48: Causales de Destitución con Proceso Interno
            $causalesCriticas = [];
            if ($minutosAtraso >= 121 && $mesesGravesGestion >= 3) {
                $causalesCriticas[] = "121+ min de atraso acumulado por 3ra vez en la gestión anual (Art. 48.I)";
            }
            if ($omisionesCount >= 4) {
                $causalesCriticas[] = "{$omisionesCount} omisiones de registro de asistencia en el mes (Art. 48.IV)";
            }
            if ($faltasInjustificadas >= 6) {
                $causalesCriticas[] = "{$faltasInjustificadas} días discontinuos de inasistencia/ausencia en el mes (Art. 48.III)";
            } elseif ($faltasInjustificadas >= 3) {
                $causalesCriticas[] = "{$faltasInjustificadas} días de inasistencia/ausencia en el mes (Art. 48.II)";
            }

            $esDestitucion = !empty($causalesCriticas);
            $sucursalNormalizada = SucursalNormalizer::normalize($empleado->sucursal) ?: 'General';

            $infringeArt45 = ($diasSancionAtraso > 0 || $diasSancionInasistencia > 0 || $diasSancionOmision > 0 || ($minutosAtraso >= 20 && $minutosAtraso <= 30));
            $infringeArt48 = $esDestitucion || ($omisionesCount === 3) || ($mesesGravesGestion === 2);
            $esConcurrente = $infringeArt45 && $infringeArt48;

            // Desglose oficial de sanciones económicas
            $desgloseSanciones = [];
            if ($diasSancionAtraso > 0) {
                $desgloseSanciones[] = "Atraso ({$minutosAtraso} min) → Descuento: {$this->formatearDiasSancion($diasSancionAtraso)} (Art. 45.I)";
            }
            if ($diasSancionInasistencia > 0) {
                $desgloseSanciones[] = "Inasistencia ({$faltasInjustificadas} faltas) → Descuento al doble: {$this->formatearDiasSancion($diasSancionInasistencia)} (Art. 45.II: día no trabajado + infracción)";
            }
            if ($diasSancionOmision > 0) {
                $desgloseSanciones[] = "Omisión de registro ({$omisionesCount} veces) → Descuento: {$this->formatearDiasSancion($diasSancionOmision)} (Art. 45.III)";
            }
            if ($esDestitucion) {
                $desgloseSanciones[] = "Causal de Destitución: " . implode('; ', $causalesCriticas);
            }

            // Resumen claro y directo de sanción
            if ($esDestitucion) {
                $resumenSancionTexto = "DESTITUCIÓN (Art. 48) · " . implode('; ', $causalesCriticas) . " · Total a descontar: {$this->formatearDiasSancion($totalDiasSancionEmpleado)}";
            } elseif ($totalDiasSancionEmpleado > 0) {
                $resumenSancionTexto = "Se le descontará {$this->formatearDiasSancion($totalDiasSancionEmpleado)} de sueldo (" . implode(' + ', $desgloseSanciones) . ")";
            } else {
                $resumenSancionTexto = "Sin descuento salarial";
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
                $motivosAlerta[] = "Atraso de {$minutosAtraso} min (a {$restantesParaSancion} min de descuento de 1/2 día)";
                $distanciaAlerta = "A {$restantesParaSancion} min del descuento";
                $articuloAlerta = 'Art. 45';
                $articuloTituloAlerta = 'Atraso al límite';

                $alertasArt45[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 45',
                    'articulo_titulo' => 'Atraso al límite',
                    'es_concurrente' => $esConcurrente,
                    'resumen_sancion' => "A {$restantesParaSancion} min de incurrir en descuento de 1/2 día de haber",
                    'minutos_atraso' => $minutosAtraso,
                    'tolerancia_limite_minutos' => 30,
                    'tolerancia_usada_minutos' => $minutosAtraso,
                    'minutos_restantes_sancion' => $restantesParaSancion,
                    'porcentaje_tolerancia_usada' => min(100, (int) round(($minutosAtraso / 30) * 100)),
                    'sancion_inminente' => 'Descuento de 1/2 día de haber',
                    'distancia_umbral' => "A {$restantesParaSancion} min de descuento",
                    'explicacion' => "Ha acumulado {$minutosAtraso} min de tolerancia mensual (de 30 min libres). Le restan solo {$restantesParaSancion} min antes del descuento de 1/2 día al cruzar los 31 min (Art. 45.I).",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // Omisiones: exactamente 3 (Art. 48: a 1 de destitución por Art. 48.IV)
            if ($omisionesCount === 3) {
                $motivosAlerta[] = "Registra 3 omisiones en el mes (a solo 1 omisión de destitución según Art. 48.IV)";
                $distanciaAlerta = "A 1 omisión de destitución";
                $articuloAlerta = 'Art. 48';
                $articuloTituloAlerta = 'Omisiones críticas';

                $alertasArt48[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 48',
                    'articulo_titulo' => 'Límite de Omisiones (3 de 4)',
                    'es_concurrente' => $esConcurrente,
                    'resumen_sancion' => "A 1 omisión de proceso de destitución (Art. 48.IV)",
                    'omisiones' => $omisionesCount,
                    'omisiones_usadas' => $omisionesCount,
                    'omisiones_limite' => 4,
                    'omisiones_restantes' => 1,
                    'porcentaje_usado' => 75,
                    'sancion_inminente' => 'Causal de Destitución (Art. 48.IV)',
                    'distancia_umbral' => "A 1 omisión de destitución",
                    'explicacion' => "Ha acumulado 3 omisiones de marcación. Con 1 omisión más incurrirá en causal de destitución con proceso interno según el Art. 48.IV.",
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
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'articulo' => 'Art. 48',
                    'articulo_titulo' => 'Reincidencia Anual (2 de 3)',
                    'es_concurrente' => $esConcurrente,
                    'resumen_sancion' => "A 1 mes con 121+ min de destitución por reincidencia (Art. 48.I)",
                    'meses_graves_usados' => 2,
                    'meses_graves_limite' => 3,
                    'meses_restantes' => 1,
                    'porcentaje_usado' => 67,
                    'sancion_inminente' => 'Causal de Destitución (Art. 48.I)',
                    'distancia_umbral' => "A 1 mes grave de destitución",
                    'explicacion' => "Ha acumulado 2 meses con más de 120 min de atraso en la gestión. Si este mes supera 120 min, será su 3ra vez e ingresará a destitución (Art. 48.I).",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            if (!empty($motivosAlerta)) {
                $totalEnAlertaPreventiva++;
                $enAlerta[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'minutos_atraso' => $minutosAtraso,
                    'omisiones' => $omisionesCount,
                    'faltas' => $faltasInjustificadas,
                    'es_concurrente' => $esConcurrente,
                    'motivos' => $motivosAlerta,
                    'motivo_principal' => $motivosAlerta[0],
                    'distancia_umbral' => $distanciaAlerta,
                    'articulo' => $articuloAlerta,
                    'articulo_titulo' => $articuloTituloAlerta,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // 2. EVALUAR: PERSONAL MÁS SANCIONADO (ART. 45 Y ART. 48)
            // -------------------------------------------------------------
            if ($totalDiasSancionEmpleado > 0 || $esDestitucion) {
                $totalConSancionEconomica++;
                $totalDiasSancionInstitucional += $totalDiasSancionEmpleado;

                $masSancionados[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'minutos_atraso' => $minutosAtraso,
                    'minutos_acumulados' => $minutosAtraso,
                    'dias_tarde' => $diasTarde,
                    'omisiones' => $omisionesCount,
                    'faltas' => $faltasInjustificadas,
                    'es_concurrente' => $esConcurrente,
                    'dias_sancion_atraso' => $diasSancionAtraso,
                    'dias_sancion_atraso_texto' => $this->formatearDiasSancion($diasSancionAtraso),
                    'dias_sancion_inasistencia' => $diasSancionInasistencia,
                    'dias_sancion_inasistencia_texto' => $this->formatearDiasSancion($diasSancionInasistencia),
                    'dias_sancion_omision' => $diasSancionOmision,
                    'dias_sancion_omision_texto' => $this->formatearDiasSancion($diasSancionOmision),
                    'dias_sancion_total' => $totalDiasSancionEmpleado,
                    'total_dias_sancion_texto' => $this->formatearDiasSancion($totalDiasSancionEmpleado),
                    'resumen_atraso' => $minutosAtraso > 0 ? "{$minutosAtraso} min atraso → {$this->formatearDiasSancion($diasSancionAtraso)}" : 'Sin atraso',
                    'resumen_total_descuento' => $esDestitucion ? "DESTITUCIÓN (Art. 48) · {$this->formatearDiasSancion($totalDiasSancionEmpleado)} deducibles" : "Se descontará {$this->formatearDiasSancion($totalDiasSancionEmpleado)} de haber",
                    'resumen_sancion' => $resumenSancionTexto,
                    'es_destitucion' => $esDestitucion,
                    'desglose' => $desgloseSanciones,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // 3. EVALUAR: CASOS CRÍTICOS Y REINCIDENTES (ART. 48)
            // -------------------------------------------------------------
            if ($mesesGravesGestion >= 2) {
                $reincidentes[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'meses_graves_gestion' => $mesesGravesGestion,
                    'minutos_mes_actual' => $minutosAtraso,
                    'omisiones_mes_actual' => $omisionesCount,
                    'es_critico' => $esDestitucion,
                    'es_concurrente' => $esConcurrente,
                    'resumen_sancion' => "{$mesesGravesGestion} meses en la gestión con más de 120 min de atraso (Art. 48.I)",
                    'detalle_reincidencia' => "Ha superado los 120 min en {$mesesGravesGestion} meses de la gestión {$gestion}.",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            if ($esDestitucion) {
                $totalRiesgoCritico++;
                $casosCriticos[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'causales' => $causalesCriticas,
                    'causal_principal' => $causalesCriticas[0],
                    'minutos_atraso' => $minutosAtraso,
                    'faltas' => $faltasInjustificadas,
                    'omisiones' => $omisionesCount,
                    'es_concurrente' => $esConcurrente,
                    'dias_sancion_total' => $totalDiasSancionEmpleado,
                    'total_dias_sancion_texto' => $this->formatearDiasSancion($totalDiasSancionEmpleado),
                    'resumen_sancion' => "DESTITUCIÓN (Art. 48) · Días deducibles: {$this->formatearDiasSancion($totalDiasSancionEmpleado)}",
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // 4. EVALUAR: CASOS CONCURRENTES (ART. 45 Y ART. 48)
            // -------------------------------------------------------------
            if ($esConcurrente) {
                $concurrentes[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'area' => $empleado->area ?: 'General',
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'minutos_atraso' => $minutosAtraso,
                    'dias_tarde' => $diasTarde,
                    'dias_sancion_atraso' => $diasSancionAtraso,
                    'dias_sancion_atraso_texto' => $this->formatearDiasSancion($diasSancionAtraso),
                    'faltas' => $faltasInjustificadas,
                    'dias_sancion_inasistencia' => $diasSancionInasistencia,
                    'dias_sancion_inasistencia_texto' => $this->formatearDiasSancion($diasSancionInasistencia),
                    'omisiones' => $omisionesCount,
                    'dias_sancion_omision' => $diasSancionOmision,
                    'dias_sancion_omision_texto' => $this->formatearDiasSancion($diasSancionOmision),
                    'dias_sancion_total' => $totalDiasSancionEmpleado,
                    'total_dias_sancion_texto' => $this->formatearDiasSancion($totalDiasSancionEmpleado),
                    'es_destitucion' => $esDestitucion,
                    'causales_destitucion' => $causalesCriticas,
                    'infraccion_art45' => ($minutosAtraso > 0 ? "{$minutosAtraso} min atraso" : "Atrasos")
                        . ($diasSancionAtraso > 0 ? " ({$this->formatearDiasSancion($diasSancionAtraso)})" : "")
                        . ($faltasInjustificadas > 0 ? " + {$faltasInjustificadas} faltas al doble ({$this->formatearDiasSancion($diasSancionInasistencia)})" : "")
                        . ($diasSancionOmision > 0 ? " + {$omisionesCount} omisiones ({$this->formatearDiasSancion($diasSancionOmision)})" : ""),
                    'infraccion_art48' => !empty($causalesCriticas) ? implode('; ', $causalesCriticas) : 'Alerta de riesgo crítico',
                    'resumen_sancion' => $esDestitucion
                        ? "DESTITUCIÓN (Art. 48) · Total a descontar: {$this->formatearDiasSancion($totalDiasSancionEmpleado)}"
                        : "Se le descontará {$this->formatearDiasSancion($totalDiasSancionEmpleado)} de haber (Art. 45)",
                    'desglose' => $desgloseSanciones,
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
            if ($esDestitucion) {
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

        // Ordenar casos concurrentes de mayor gravedad a menor
        $concurrentes = collect($concurrentes)
            ->sortByDesc(fn($i) => ($i['es_destitucion'] ? 1000 : 0) + $i['dias_sancion_total'])
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
