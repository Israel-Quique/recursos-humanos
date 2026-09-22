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
        protected ?ProgramacionLaboralService $programacionLaboral = null,
    ) {
        $this->programacionLaboral ??= app(ProgramacionLaboralService::class);
    }

    /**
     * Genera el reporte integral del reglamento institucional para el mes y sucursal dados.
     */
    public function generarReporteReglamento(Carbon $referenceMonth, ?string $branch = null, ?array $preloadedBase = null): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();
        $gestion = (int) $referenceMonth->format('Y');

        $empleadosQuery = Empleado::query()
            ->activosLaboralmente($end)
            ->when(filled($branch), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));

        $empleados = $empleadosQuery->orderBy('sucursal')->orderBy('apellido')->orderBy('nombre')->get();

        $yearStart = Carbon::createFromDate($gestion, 1, 1)->startOfDay();
        $asistenciasPreviasGestion = RegistroAsistencia::query()
            ->whereIn('empleado_id', $empleados->pluck('id'))
            ->whereDate('fecha', '>=', $yearStart)
            ->whereDate('fecha', '<', $start)
            ->whereNotNull('hora_entrada')
            ->get(['empleado_id', 'fecha', 'hora_entrada', 'hora_salida', 'estado_marcacion', 'evento_biometrico'])
            ->groupBy('empleado_id');

        $reporteBase = $preloadedBase ?? $this->analisisAsistencia->reporteMensualNoMarcadosYAtrasos($referenceMonth, $branch);
        $atrasosPorEmpleado = collect($reporteBase['atrasos'] ?? [])->groupBy('empleado_id');
        $omisionesPorEmpleado = collect($reporteBase['omisiones'] ?? $reporteBase['no_marcados'] ?? [])->groupBy('empleado_id');
        $faltasPorEmpleado = collect($reporteBase['faltas'] ?? [])->groupBy('empleado_id');

        $enAlerta = [];
        $alertasArt45 = [];
        $alertasArt48 = [];
        $masSancionados = [];
        $reincidentes = [];
        $casosCriticos = [];
        $concurrentes = [];

        // Nuevos listados de detalle operativo solicitados
        $detalleAtrasos = [];
        $detalleOmisiones = [];
        $detalleReincidentes = [];
        $detalleFaltas = [];

        $totalDiasSancionInstitucional = 0.0;
        $totalConSancionEconomica = 0;
        $totalEnAlertaPreventiva = 0;
        $totalRiesgoCritico = 0;

        $desgloseSucursal = [];

        foreach ($empleados as $empleado) {
            $tardanzasEmp = $atrasosPorEmpleado->get($empleado->id, collect());
            $omisionesEmp = $omisionesPorEmpleado->get($empleado->id, collect());
            $faltasEmp = $faltasPorEmpleado->get($empleado->id, collect());

            $minutosAtraso = (int) $tardanzasEmp->sum(fn($t) => $t['minutos_retraso'] ?? ($t['retraso_minutos'] ?? 0));
            $tardanzas = $tardanzasEmp->all();
            $diasTarde = count($tardanzas);
            $omisionesLista = $omisionesEmp->all();
            $omisionesCount = count($omisionesLista);
            $faltasInjustificadas = $faltasEmp->count();
            $codigoEmpleado = !empty($empleado->codigo_biometrico) ? (string) $empleado->codigo_biometrico : 'CI: ' . $empleado->id;

            // Extraer detalle de fechas de atrasos
            $fechasAtrasos = [];
            foreach ($tardanzas as $t) {
                $min = (int) ($t['minutos_retraso'] ?? ($t['retraso_minutos'] ?? 0));
                $fechasAtrasos[] = [
                    'fecha' => $t['fecha'] ?? '',
                    'minutos' => $min,
                    'entrada' => $t['entrada_real'] ?? ($t['entrada'] ?? '--:--'),
                    'salida' => $t['salida'] ?? '--:--',
                    'etiqueta' => ($t['fecha'] ?? '') . ' (' . $min . ' min)',
                ];
            }
            $fechasAtrasosTexto = collect($fechasAtrasos)->pluck('etiqueta')->implode(', ');

            // Extraer detalle de fechas de omisiones
            $fechasOmisiones = [];
            foreach ($omisionesLista as $o) {
                $detalleTipo = $o['detalle'] ?? $o['estado'] ?? 'Omisión';
                $tipoCorto = str_contains($detalleTipo, 'Falta entrada') ? 'Sin entrada'
                    : (str_contains($detalleTipo, 'Falta salida') ? 'Sin salida'
                    : (str_contains($detalleTipo, 'Día sin marcación') ? 'Inasistencia' : 'Sin marcación'));
                $fechasOmisiones[] = [
                    'fecha' => $o['fecha'] ?? '',
                    'tipo' => $tipoCorto,
                    'etiqueta' => ($o['fecha'] ?? '') . ' (' . $tipoCorto . ')',
                ];
            }
            $fechasOmisionesTexto = collect($fechasOmisiones)->pluck('etiqueta')->implode(', ');

            // Extraer detalle de fechas de faltas e inasistencias
            $fechasFaltas = [];
            foreach ($faltasEmp as $f) {
                $fechasFaltas[] = [
                    'fecha' => $f['fecha'] ?? '',
                    'detalle' => $f['detalle'] ?? 'Inasistencia injustificada',
                    'etiqueta' => $f['fecha'] ?? '',
                ];
            }
            $fechasFaltasTexto = collect($fechasFaltas)->pluck('etiqueta')->filter()->implode(', ');

            // Calcular reincidencia anual de meses con más de 120 min en la gestión (Art. 48.I)
            $empPrevias = $asistenciasPreviasGestion->get($empleado->id);
            $mesesGravesGestion = $this->contarMesesConAtrasoGraveEnGestion($empleado, $gestion, (int) $referenceMonth->format('m'), $empPrevias);
            if ($minutosAtraso >= 121) {
                $mesesGravesGestion++;
            }

            // 1. Art. 45.I: Escala de Atrasos (evaluada dinámicamente según vigencia de la regla)
            $diasSancionAtraso = 0.0;
            $reglaAtraso = $this->reglamentoSancion->evaluarAtraso($minutosAtraso, 1, $referenceMonth);
            if ($reglaAtraso) {
                $diasSancionAtraso = (float) $reglaAtraso->dias_sancion;
            }

            // 2. Art. 45.II: Inasistencias y Ausencias en el Puesto de Trabajo (evaluada según vigencia)
            $diasSancionInasistencia = 0.0;
            $reglaInasistencia = $this->reglamentoSancion->evaluarInasistencia($faltasInjustificadas, $referenceMonth);
            if ($reglaInasistencia) {
                $diasSancionInasistencia = (float) ($faltasInjustificadas * 2.0);
            }

            // 3. Art. 45.III: Omisiones en el Registro de Asistencia (evaluada según vigencia)
            $diasSancionOmision = 0.0;
            $reglaOmision = $this->reglamentoSancion->evaluarOmision($omisionesCount, $referenceMonth);
            if ($reglaOmision) {
                $diasSancionOmision = (float) $reglaOmision->dias_sancion;
            }

            $totalDiasSancionEmpleado = $diasSancionAtraso + $diasSancionInasistencia + $diasSancionOmision;

            // 4. Art. 48: Causales de Destitución con Proceso Interno
            $causalesCriticas = [];
            $gravisimasVigentes = ReglaSancion::query()
                ->gravisimas()
                ->activo()
                ->get()
                ->filter(fn(ReglaSancion $r) => $r->aplicaEnPeriodo($referenceMonth));

            // Art. 48.I: Reincidencia en atrasos gravísimos (121+ min por 3ra vez)
            $reglaAtrasoGravisima = $gravisimasVigentes->first(fn($r) => $r->unidad === 'minutos' && $r->es_destitucion);
            if ($reglaAtrasoGravisima && $minutosAtraso >= 121 && $mesesGravesGestion >= 3) {
                $causalesCriticas[] = "121+ min de atraso acumulado por 3ra vez en la gestión anual (Art. 48.I)";
            }

            // Art. 48.IV: Omisiones en el registro de asistencia (>= 4)
            $reglaOmisionGravisima = $gravisimasVigentes->first(fn($r) => $r->unidad === 'ocurrencias' && $r->es_destitucion);
            if ($reglaOmisionGravisima && $omisionesCount >= 4) {
                $causalesCriticas[] = "{$omisionesCount} omisiones de registro de asistencia en el mes (Art. 48.IV)";
            }

            // Art. 48.II y 48.III: Inasistencias o abandono (>3 continuos o >6 discontinuos)
            $reglasInasistenciaGravisima = $gravisimasVigentes->filter(fn($r) => $r->unidad === 'dias' && $r->es_destitucion);
            if ($reglasInasistenciaGravisima->isNotEmpty()) {
                if ($faltasInjustificadas >= 6) {
                    $causalesCriticas[] = "{$faltasInjustificadas} días discontinuos de inasistencia/ausencia en el mes (Art. 48.III)";
                } elseif ($faltasInjustificadas >= 3) {
                    $causalesCriticas[] = "{$faltasInjustificadas} días de inasistencia/ausencia en el mes (Art. 48.II)";
                }
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
            // A. DETALLE DIRECTO DE ATRASOS (SOLICITADO: nombre, codigo, atraso, dias tarde, descuento, fechas)
            // Solo funcionarios con sanción económica por exceder el margen de tolerancia (> 30 min)
            // -------------------------------------------------------------
            if ($diasSancionAtraso > 0) {
                $detalleAtrasos[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'area' => $empleado->area ?: 'General',
                    'minutos_atraso' => $minutosAtraso,
                    'minutos_texto' => "{$minutosAtraso} min",
                    'dias_tarde' => $diasTarde,
                    'dias_tarde_texto' => $diasTarde . ' ' . ($diasTarde === 1 ? 'día' : 'días'),
                    'dias_descuento' => $diasSancionAtraso,
                    'dias_descuento_texto' => $this->formatearDiasSancion($diasSancionAtraso),
                    'fechas' => $fechasAtrasos,
                    'fechas_texto' => $fechasAtrasosTexto ?: 'Sin detalle registrado',
                    'es_sancionado' => true,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // B. DETALLE DIRECTO DE OMISIONES (SOLICITADO: nombre, codigo, omisiones, fechas, descuento)
            // -------------------------------------------------------------
            if ($omisionesCount > 0) {
                $detalleOmisiones[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'area' => $empleado->area ?: 'General',
                    'total_omisiones' => $omisionesCount,
                    'total_omisiones_texto' => $omisionesCount . ' ' . ($omisionesCount === 1 ? 'omisión' : 'omisiones'),
                    'dias_descuento' => $diasSancionOmision,
                    'dias_descuento_texto' => ($omisionesCount >= 4)
                        ? 'Causal Destitución (Art. 48.IV)'
                        : ($diasSancionOmision > 0 ? $this->formatearDiasSancion($diasSancionOmision) : 'Llamada de atención'),
                    'fechas' => $fechasOmisiones,
                    'fechas_texto' => $fechasOmisionesTexto ?: 'Sin detalle registrado',
                    'es_sancionado' => $diasSancionOmision > 0 || $omisionesCount >= 4,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // C. DETALLE DIRECTO DE FALTAS / INASISTENCIAS (Art. 45.II y Art. 48.II/III)
            // -------------------------------------------------------------
            if ($faltasInjustificadas > 0) {
                $esCriticoFalta = $faltasInjustificadas >= 3;
                $causalDisciplinariaFalta = $faltasInjustificadas >= 6
                    ? "Causal Destitución: {$faltasInjustificadas} faltas discontinuas (Art. 48.III)"
                    : ($faltasInjustificadas >= 3
                        ? "Causal Destitución: {$faltasInjustificadas} faltas consecutivas/graves (Art. 48.II)"
                        : "Sanción económica Art. 45.II (Descuento al doble)");

                $detalleFaltas[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'area' => $empleado->area ?: 'General',
                    'total_faltas' => $faltasInjustificadas,
                    'total_faltas_texto' => $faltasInjustificadas . ' ' . ($faltasInjustificadas === 1 ? 'falta' : 'faltas'),
                    'dias_descuento' => $diasSancionInasistencia,
                    'dias_descuento_texto' => $this->formatearDiasSancion($diasSancionInasistencia) . ' (Doble)',
                    'causal_disciplinaria' => $causalDisciplinariaFalta,
                    'fechas' => $fechasFaltas,
                    'fechas_texto' => $fechasFaltasTexto ?: 'Sin detalle registrado',
                    'es_critico' => $esCriticoFalta,
                    'es_sancionado' => true,
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // D. REPORTE DE REINCIDENTES:
            // 1) Si en más de dos meses superó sus 30 min de tolerancia
            // 2) Si tuvo omisiones reiteradas (>= 2) con el detalle de fechas
            // -------------------------------------------------------------
            $mesesPreviosExcedidos30 = $this->obtenerMesesConAtrasoMayorA30($empleado, $gestion, (int) $referenceMonth->format('m'), $empPrevias);
            $todosMeses30 = $mesesPreviosExcedidos30;
            if ($minutosAtraso > 30) {
                $nombreMesActual = ucfirst($referenceMonth->locale('es')->translatedFormat('F'));
                $todosMeses30[] = [
                    'numero' => (int) $referenceMonth->format('m'),
                    'mes' => $nombreMesActual,
                    'minutos' => $minutosAtraso,
                    'etiqueta' => "{$nombreMesActual} ({$minutosAtraso} min)",
                ];
            }

            $esReincidenteTolerancia = count($todosMeses30) >= 2 && ($minutosAtraso > 30 || $diasSancionAtraso > 0);
            $esReincidenteOmisiones = $omisionesCount >= 2;

            if ($esReincidenteTolerancia) {
                $detalleReincidentes[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'area' => $empleado->area ?: 'General',
                    'tipo' => 'atrasos',
                    'tipo_etiqueta' => 'Superó 30 min (> 2 meses)',
                    'frecuencia' => count($todosMeses30) . ' meses en la gestión',
                    'conteo_meses' => count($todosMeses30),
                    'meses_detalle' => $todosMeses30,
                    'detalle_texto' => collect($todosMeses30)->pluck('etiqueta')->implode(', '),
                    'sancion_texto' => $this->formatearDiasSancion($diasSancionAtraso) . ' (Mes actual)',
                    'fechas_texto' => $fechasAtrasosTexto ?: 'Sin atrasos este mes',
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            if ($esReincidenteOmisiones) {
                $detalleReincidentes[] = [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $codigoEmpleado,
                    'sucursal' => $empleado->sucursal ?: $sucursalNormalizada,
                    'area' => $empleado->area ?: 'General',
                    'tipo' => 'omisiones',
                    'tipo_etiqueta' => 'Omisiones Reiteradas',
                    'frecuencia' => "{$omisionesCount} omisiones registradas",
                    'conteo_meses' => 1,
                    'meses_detalle' => [],
                    'detalle_texto' => "Acumula {$omisionesCount} omisiones de registro en el periodo.",
                    'sancion_texto' => ($omisionesCount >= 4) ? 'Causal Destitución (Art. 48.IV)' : ($diasSancionOmision > 0 ? $this->formatearDiasSancion($diasSancionOmision) : 'Llamada de atención'),
                    'fechas_texto' => $fechasOmisionesTexto ?: 'N/D',
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            // -------------------------------------------------------------
            // ZONAS DE ALERTA Y REGLAMENTO GENERAL (Compatibilidad)
            // -------------------------------------------------------------
            $motivosAlerta = [];
            $distanciaAlerta = '';
            $articuloAlerta = 'Art. 45';
            $articuloTituloAlerta = 'Atrasos';

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

        // Ordenar personal en alerta por cercanía al umbral
        $enAlerta = collect($enAlerta)
            ->sortByDesc(fn($i) => ($i['omisiones'] * 100) + $i['minutos_atraso'])
            ->values()
            ->all();

        // Ordenar reincidentes
        $reincidentes = collect($reincidentes)
            ->sortByDesc('meses_graves_gestion')
            ->values()
            ->all();

        // Ordenar casos concurrentes
        $concurrentes = collect($concurrentes)
            ->sortByDesc(fn($i) => ($i['es_destitucion'] ? 1000 : 0) + $i['dias_sancion_total'])
            ->unique('id')
            ->values()
            ->all();

        $sancionadosArt45 = collect($masSancionados)
            ->filter(fn($i) => ($i['dias_sancion_total'] ?? 0) > 0)
            ->values()
            ->all();

        // Ordenar las listas de detalle
        $detalleAtrasos = collect($detalleAtrasos)
            ->sortByDesc('minutos_atraso')
            ->values()
            ->all();

        $detalleOmisiones = collect($detalleOmisiones)
            ->sortByDesc('total_omisiones')
            ->values()
            ->all();

        $detalleFaltas = collect($detalleFaltas)
            ->sortByDesc('total_faltas')
            ->values()
            ->all();

        $detalleReincidentes = collect($detalleReincidentes)
            ->sortByDesc(fn($r) => ($r['tipo'] === 'atrasos' ? 100 : 50) + ($r['conteo_meses'] ?? 1))
            ->values()
            ->all();

        return [
            'metricas' => [
                'total_evaluados' => $empleados->count(),
                'en_alerta_preventiva' => $totalEnAlertaPreventiva,
                'con_sancion_economica' => count($sancionadosArt45),
                'riesgo_critico' => $totalRiesgoCritico,
                'concurrencia_articulos' => count($concurrentes),
                'total_dias_sancion' => $totalDiasSancionInstitucional,
                'total_dias_sancion_formato' => $this->formatearDiasSancion($totalDiasSancionInstitucional),
                'total_con_atraso' => count($detalleAtrasos),
                'total_con_omision' => count($detalleOmisiones),
                'total_con_faltas' => count($detalleFaltas),
                'total_reincidentes' => count($detalleReincidentes),
            ],
            // Listados directos detallados
            'detalle_atrasos' => $detalleAtrasos,
            'detalle_omisiones' => $detalleOmisiones,
            'detalle_faltas' => $detalleFaltas,
            'detalle_reincidentes' => $detalleReincidentes,
            'reincidentes_atrasos' => collect($detalleReincidentes)->where('tipo', 'atrasos')->values()->all(),
            'reincidentes_omisiones' => collect($detalleReincidentes)->where('tipo', 'omisiones')->values()->all(),

            // Listados previos conservados para retrocompatibilidad
            'personal_en_alerta' => $enAlerta,
            'mas_sancionados' => $masSancionados,
            'masSancionados' => $masSancionados,
            'reincidentes' => $reincidentes,
            'casos_criticos' => $casosCriticos,
            'concurrentes' => $concurrentes,
            'art_45' => [
                'titulo' => 'Artículo 45 · Atrasos, Inasistencias y Sanciones Salariales',
                'alertas' => $alertasArt45,
                'sancionados' => $sancionadosArt45,
                'total_alertas' => count($alertasArt45),
                'total_sancionados' => count($sancionadosArt45),
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

    /**
     * Evalúa individualmente a un funcionario frente al reglamento interno en el mes dado.
     */
    public function evaluarEmpleadoIndividual(int $empleadoId, Carbon $referenceMonth): ?array
    {
        $empleado = Empleado::query()->find($empleadoId);
        if (! $empleado) {
            return null;
        }

        $reporteMensual = $this->analisisAsistencia->reporteMensualNoMarcadosYAtrasos($referenceMonth, null);
        $atrasosEmp = collect($reporteMensual['atrasos'] ?? [])->where('empleado_id', $empleadoId)->values();
        $omisionesEmp = collect($reporteMensual['omisiones'] ?? $reporteMensual['no_marcados'] ?? [])->where('empleado_id', $empleadoId)->values();
        $faltasEmp = collect($reporteMensual['faltas'] ?? [])->where('empleado_id', $empleadoId)->values();

        $minutosAtraso = (int) $atrasosEmp->sum(fn($t) => $t['minutos_retraso'] ?? ($t['retraso_minutos'] ?? 0));
        $diasTarde = $atrasosEmp->count();
        $omisionesCount = $omisionesEmp->count();
        $faltasInjustificadas = $faltasEmp->count();

        // 1. Evaluar Art. 45.I (Escala de atrasos)
        $diasSancionAtraso = 0.0;
        $reglaAtraso = $this->reglamentoSancion->evaluarAtraso($minutosAtraso, 1, $referenceMonth);
        if ($reglaAtraso) {
            $diasSancionAtraso = (float) $reglaAtraso->dias_sancion;
        }

        // 2. Evaluar Art. 45.III (Escala de omisiones)
        $diasSancionOmision = 0.0;
        $reglaOmision = $this->reglamentoSancion->evaluarOmision($omisionesCount, $referenceMonth);
        if ($reglaOmision) {
            $diasSancionOmision = (float) $reglaOmision->dias_sancion;
        }

        // 3. Evaluar Art. 45.II (Inasistencias al doble)
        $diasSancionInasistencia = (float) ($faltasInjustificadas * 2.0);

        $totalDiasDescuento = $diasSancionAtraso + $diasSancionOmision + $diasSancionInasistencia;
        $esDestitucion = ($omisionesCount >= 4) || ($faltasInjustificadas >= 3);
        $sePasaReglamento = ($minutosAtraso > 30 || $omisionesCount > 0 || $totalDiasDescuento > 0 || $esDestitucion);

        $fechasAtrasos = $atrasosEmp->map(fn($t) => [
            'fecha' => $t['fecha'] ?? '',
            'minutos' => (int) ($t['minutos_retraso'] ?? ($t['retraso_minutos'] ?? 0)),
            'entrada' => $t['entrada_real'] ?? ($t['entrada'] ?? '--:--'),
            'salida' => $t['salida'] ?? '--:--',
        ])->all();

        $fechasOmisiones = $omisionesEmp->map(fn($o) => [
            'fecha' => $o['fecha'] ?? '',
            'detalle' => $o['detalle'] ?? 'Omisión',
        ])->all();

        $desgloseTextos = [];
        if ($diasSancionAtraso > 0) {
            $desgloseTextos[] = "Atrasos ({$minutosAtraso} min): {$this->formatearDiasSancion($diasSancionAtraso)}";
        }
        if ($diasSancionOmision > 0) {
            $desgloseTextos[] = "Omisiones ({$omisionesCount}): {$this->formatearDiasSancion($diasSancionOmision)}";
        }
        if ($diasSancionInasistencia > 0) {
            $desgloseTextos[] = "Inasistencias ({$faltasInjustificadas}): {$this->formatearDiasSancion($diasSancionInasistencia)}";
        }

        return [
            'id' => $empleado->id,
            'nombre' => $empleado->nombre_completo,
            'codigo' => !empty($empleado->codigo_biometrico) ? (string) $empleado->codigo_biometrico : 'CI: ' . $empleado->id,
            'sucursal' => $empleado->sucursal ?: 'General',
            'area' => $empleado->area ?: 'General',
            'minutos_atraso' => $minutosAtraso,
            'dias_tarde' => $diasTarde,
            'omisiones_count' => $omisionesCount,
            'faltas_count' => $faltasInjustificadas,
            'dias_sancion_atraso' => $diasSancionAtraso,
            'dias_sancion_atraso_texto' => $this->formatearDiasSancion($diasSancionAtraso),
            'dias_sancion_omision' => $diasSancionOmision,
            'dias_sancion_omision_texto' => $this->formatearDiasSancion($diasSancionOmision),
            'dias_sancion_inasistencia' => $diasSancionInasistencia,
            'total_dias_descuento' => $totalDiasDescuento,
            'total_dias_descuento_texto' => $this->formatearDiasSancion($totalDiasDescuento),
            'desglose_sanciones' => $desgloseTextos,
            'es_sancionado' => $totalDiasDescuento > 0 || $esDestitucion,
            'es_destitucion' => $esDestitucion,
            'se_pasa_reglamento' => $sePasaReglamento,
            'fechas_atrasos' => $fechasAtrasos,
            'fechas_omisiones' => $fechasOmisiones,
            'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
        ];
    }

    private static array $reincidenciaCache = [];

    /**
     * Cuenta cuántos meses previos en la gestión anual un empleado superó o igualó los 121 minutos de retraso.
     */
    protected function contarMesesConAtrasoGraveEnGestion(Empleado $empleado, int $gestion, int $mesExcluir, ?Collection $preloadedRegistros = null): int
    {
        if ($mesExcluir <= 1) {
            return 0;
        }

        $reglaGravisimaAtraso = ReglaSancion::query()
            ->gravisimas()
            ->where('unidad', 'minutos')
            ->first();

        $mesInicioVigencia = 1;
        if ($reglaGravisimaAtraso) {
            if (! $reglaGravisimaAtraso->activo || $reglaGravisimaAtraso->tipo_vigencia === 'no_aplica') {
                return 0;
            }
            if ($reglaGravisimaAtraso->aplica_desde_gestion !== null) {
                if ($gestion < (int) $reglaGravisimaAtraso->aplica_desde_gestion) {
                    return 0;
                }
                if ($gestion === (int) $reglaGravisimaAtraso->aplica_desde_gestion && $reglaGravisimaAtraso->aplica_desde_mes !== null) {
                    $mesInicioVigencia = (int) $reglaGravisimaAtraso->aplica_desde_mes;
                }
            } elseif ($reglaGravisimaAtraso->aplica_desde_mes !== null) {
                $mesInicioVigencia = (int) $reglaGravisimaAtraso->aplica_desde_mes;
            }
        }

        if ($mesExcluir <= $mesInicioVigencia) {
            return 0;
        }

        $cacheKey = "{$empleado->id}_{$gestion}_{$mesExcluir}_{$mesInicioVigencia}";
        if (isset(self::$reincidenciaCache[$cacheKey])) {
            return self::$reincidenciaCache[$cacheKey];
        }

        $startDate = Carbon::createFromDate($gestion, $mesInicioVigencia, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($gestion, $mesExcluir, 1)->startOfMonth()->toDateString();

        $registros = $preloadedRegistros ?? RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<', $endDate)
            ->whereNotNull('hora_entrada')
            ->get(['fecha', 'hora_entrada', 'hora_salida', 'estado_marcacion', 'evento_biometrico']);

        if (!$registros || $registros->isEmpty()) {
            return self::$reincidenciaCache[$cacheKey] = 0;
        }

        $mesesConRegistros = $registros->groupBy(fn($r) => (int) $r->fecha?->format('m'));

        $conteo = 0;
        foreach ($mesesConRegistros as $m => $items) {
            if ($m < $mesInicioVigencia) {
                continue;
            }

            $minutosMes = 0;
            foreach ($items as $reg) {
                $horario = $this->programacionLaboral->resolverHorario($empleado, $reg->fecha);
                if (!$horario['laborable']) {
                    continue;
                }

                $horaProg = $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada'];
                if (!$horaProg || !$reg->hora_entrada) {
                    continue;
                }

                $reg->setRelation('empleado', $empleado);
                $marcacion = $this->analisisAsistencia->normalizarMarcacionAsistencia($reg);
                $delay = $this->analisisAsistencia->calcularMinutosRetraso($marcacion['entrada'], $horaProg);
                $minutosMes += $delay;
            }

            if ($minutosMes >= 121) {
                $conteo++;
            }
        }

        return self::$reincidenciaCache[$cacheKey] = $conteo;
    }

    /**
     * Obtiene los meses previos de la gestión donde el empleado superó los 30 minutos de tolerancia mensual.
     */
    protected function obtenerMesesConAtrasoMayorA30(Empleado $empleado, int $gestion, int $mesExcluir, ?Collection $preloadedRegistros = null): array
    {
        if ($mesExcluir <= 1) {
            return [];
        }

        $startDate = Carbon::createFromDate($gestion, 1, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($gestion, $mesExcluir, 1)->startOfMonth()->toDateString();

        $registros = $preloadedRegistros ?? RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', '>=', $startDate)
            ->whereDate('fecha', '<', $endDate)
            ->whereNotNull('hora_entrada')
            ->get(['fecha', 'hora_entrada', 'hora_salida', 'estado_marcacion', 'evento_biometrico']);

        if (!$registros || $registros->isEmpty()) {
            return [];
        }

        $mesesConRegistros = $registros->groupBy(fn($r) => (int) $r->fecha?->format('m'));
        $nombresMeses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $mesesExcedidos = [];
        foreach ($mesesConRegistros as $m => $items) {
            if ($m >= $mesExcluir) {
                continue;
            }

            $minutosMes = 0;
            foreach ($items as $reg) {
                $horario = $this->programacionLaboral->resolverHorario($empleado, $reg->fecha);
                if (!$horario['laborable']) {
                    continue;
                }

                $horaProg = $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada'];
                if (!$horaProg || !$reg->hora_entrada) {
                    continue;
                }

                $reg->setRelation('empleado', $empleado);
                $marcacion = $this->analisisAsistencia->normalizarMarcacionAsistencia($reg);
                $delay = $this->analisisAsistencia->calcularMinutosRetraso($marcacion['entrada'], $horaProg);
                $minutosMes += $delay;
            }

            if ($minutosMes > 30) {
                $mesesExcedidos[] = [
                    'numero' => $m,
                    'mes' => $nombresMeses[$m] ?? "Mes {$m}",
                    'minutos' => $minutosMes,
                    'etiqueta' => ($nombresMeses[$m] ?? "Mes {$m}") . " ({$minutosMes} min)",
                ];
            }
        }

        return $mesesExcedidos;
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
