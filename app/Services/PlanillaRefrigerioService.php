<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\TipoPermiso;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlanillaRefrigerioService
{
    public function __construct(
        protected AnalisisAsistenciaService $analisisAsistencia,
        protected ProgramacionLaboralService $programacionLaboral
    ) {}

    /**
     * Obtiene los días hábiles del mes (Lunes a Viernes), excluyendo Sábados y Domingos.
     */
    public function obtenerDiasHabilesMes(Carbon $referenceMonth): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();
        $diasMes = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            // Quitar sábados (6) y domingos (7)
            if (!$cursor->isWeekend()) {
                $diaSemana = match ($cursor->dayOfWeekIso) {
                    1 => 'Lun',
                    2 => 'Mar',
                    3 => 'Mié',
                    4 => 'Jue',
                    5 => 'Vie',
                    default => '',
                };

                $diasMes[] = [
                    'fecha' => $cursor->format('Y-m-d'),
                    'dia' => $cursor->format('d'),
                    'dia_nombre' => $diaSemana,
                    'fecha_corta' => $cursor->format('d/m/Y'),
                    'dia_num' => (int) $cursor->format('d'),
                ];
            }
            $cursor->addDay();
        }

        return $diasMes;
    }

    /**
     * Calcula la planilla de descuento de refrigerio/comida para un mes y sucursal.
     */
    public function calcularPlanilla(Carbon $referenceMonth, ?string $branch = null, float $tarifaDiaria = 20.00): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();
        $diasMes = $this->obtenerDiasHabilesMes($referenceMonth);

        // 1. Permisos aprobados en el rango (excluyendo permisos por horas)
        $permisosQuery = PermisoLaboral::query()
            ->with('empleado')
            ->where(function ($q) {
                $q->where('estado', 'aprobado')->orWhere('estado', 'Aprobado');
            })
            ->where('alcance', '!=', 'horas')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString());

        if (filled($branch)) {
            $permisosQuery->whereHas('empleado', fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));
        }

        $permisosAprobadosColeccion = $permisosQuery->get();
        $permisosEmpIds = $permisosAprobadosColeccion->pluck('empleado_id')->unique()->all();
        $permisosAprobados = $permisosAprobadosColeccion->groupBy('empleado_id');

        // 2. Obtener empleados activos en el mes.
        // Se usa activosLaboralmente($start) en vez de ($end) para que la ventana de
        // 30 días de inactividad cuente desde el INICIO del mes, no desde el fin.
        // Ejemplo: para octubre ($start=01/10), el umbral es 01/09, por lo que los
        // datos biométricos de septiembre sí califican al empleado como activo.
        $empleados = Empleado::query()
            ->where(function ($q) use ($start, $end, $permisosEmpIds) {
                $q->activosLaboralmente($start)
                  ->orWhereHas('asistencias', fn($sub) => $sub->whereBetween('fecha', [$start->toDateString(), $end->toDateString()]))
                  ->when(!empty($permisosEmpIds), fn($sub) => $sub->orWhereIn('id', $permisosEmpIds));
            })
            ->where(function ($q) use ($start) {
                $q->whereNull('fecha_despido')->orWhereDate('fecha_despido', '>=', $start->toDateString());
            })
            ->when(filled($branch), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch))
            ->orderBy('sucursal')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();


        // 3. Asistencias de los empleados en el mes
        $asistenciasQuery = RegistroAsistencia::query()
            ->with('empleado')
            ->whereIn('empleado_id', $empleados->pluck('id'))
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()]);

        if (filled($branch)) {
            $asistenciasQuery->whereHas('empleado', fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));
        }

        $asistenciasColeccion = $asistenciasQuery->get();

        // Agrupar asistencias por empleado_id|fecha
        $asistencias = $asistenciasColeccion->groupBy(function (RegistroAsistencia $r) {
            $f = $r->fecha instanceof Carbon ? $r->fecha->toDateString() : Carbon::parse($r->fecha)->toDateString();
            return $r->empleado_id . '|' . $f;
        });

        try {
            $tiposPermisosMap = TipoPermiso::obtenerTodos();
        } catch (\Throwable) {
            $tiposPermisosMap = [];
        }

        // 5. Procesar cada empleado
        $items = [];
        $totalFaltas = 0;
        $totalOmisiones = 0;
        $totalPermisos = 0;
        $totalBajas = 0;
        $totalComisiones = 0;
        $totalFeriados = 0;
        $granTotalDiasPagados = 0;
        $granTotalDias = 0;
        $granTotalMontoPagado = 0.0;
        $granTotalMonto = 0.0;

        foreach ($empleados as $empleado) {
            $empId = $empleado->id;

            // Permisos del empleado
            $permisosEmp = $permisosAprobados->get($empId, collect());
            $permisosPorFecha = [];

            foreach ($permisosEmp as $permiso) {
                if ($permiso->alcance === 'horas') {
                    continue;
                }

                // ── FUENTE PRINCIPAL: clave estructurada guardada en BD ──────────────
                // Cuando el permiso viene de Incidencias y tiene tipo_permiso_clave
                // (campo agregado en la migración 2026_09_30_*), lo usamos directamente.
                // Mapeo especial para claves que tienen abreviatura diferente a su clave DB:
                $claveDirecta = $permiso->tipo_permiso_clave;

                // Normalizar claves especiales que en la planilla se muestran como Bm / Cv
                if ($claveDirecta === 'consulta_medica' || $claveDirecta === 'salud') {
                    $claveDirecta = 'bm';
                } elseif ($claveDirecta === 'comision_laboral') {
                    $claveDirecta = 'cv';
                }

                if (filled($claveDirecta)) {
                    // Tenemos la clave directa de Incidencias → asignar y continuar
                    $pInicio = $permiso->fecha_inicio ? $permiso->fecha_inicio->copy()->max($start) : $start->copy();
                    $pFin    = $permiso->fecha_fin    ? $permiso->fecha_fin->copy()->min($end)       : $pInicio->copy();
                    $pCursor = $pInicio->copy();
                    while ($pCursor->lte($pFin)) {
                        if (!$pCursor->isWeekend()) {
                            $permisosPorFecha[$pCursor->format('Y-m-d')] = $claveDirecta;
                        }
                        $pCursor->addDay();
                    }
                    continue;
                }

                // ── FALLBACK para registros anteriores sin tipo_permiso_clave ─────────
                // Solo se ejecuta si el permiso fue creado antes de la migración y
                // todavía no tiene la clave estructurada.
                $motivoTexto = mb_strtolower(trim(($permiso->tipo ?? '') . ' ' . ($permiso->motivo ?? '')));

                $esBajaMedica = $permiso->tipo === 'medico'
                    || str_contains($motivoTexto, 'medic')
                    || str_contains($motivoTexto, 'médic')
                    || str_contains($motivoTexto, 'baja')
                    || str_contains($motivoTexto, 'salud')
                    || str_contains($motivoTexto, 'enferm')
                    || str_contains($motivoTexto, 'reposo')
                    || str_contains($motivoTexto, 'incapacidad')
                    || str_contains($motivoTexto, 'consulta');

                $esComisionViaje = $permiso->tipo === 'comision'
                    || str_contains($motivoTexto, 'comision')
                    || str_contains($motivoTexto, 'comisión')
                    || str_contains($motivoTexto, 'viaje')
                    || str_contains($motivoTexto, 'viatic')
                    || str_contains($motivoTexto, 'viátic');

                // Detectar si coincide con algún tipo dinámico registrado
                $clavePermisoDinamico = null;
                foreach ($tiposPermisosMap as $clave => $nombre) {
                    $nombreLow = mb_strtolower($nombre);
                    if (!in_array($clave, ['salud', 'consulta_medica', 'comision_laboral'], true)
                        && str_contains($motivoTexto, $nombreLow)) {
                        $clavePermisoDinamico = $clave;
                        break;
                    }
                }

                $pInicio = $permiso->fecha_inicio ? $permiso->fecha_inicio->copy()->max($start) : $start->copy();
                $pFin    = $permiso->fecha_fin    ? $permiso->fecha_fin->copy()->min($end)       : $pInicio->copy();
                $pCursor = $pInicio->copy();
                while ($pCursor->lte($pFin)) {
                    if (!$pCursor->isWeekend()) {
                        $fKey = $pCursor->format('Y-m-d');
                        if ($esBajaMedica) {
                            $permisosPorFecha[$fKey] = 'bm';
                        } elseif ($esComisionViaje) {
                            $permisosPorFecha[$fKey] = 'cv';
                        } elseif ($permiso->tipo === 'falta' || str_contains($motivoTexto, 'falta')) {
                            $permisosPorFecha[$fKey] = 'f';
                        } elseif ($clavePermisoDinamico) {
                            $permisosPorFecha[$fKey] = $clavePermisoDinamico;
                        } else {
                            $tipoKey = $permiso->tipo ?? 'permiso';
                            $clavePerm = strtolower(substr(preg_replace('/[^a-z]/', '', $tipoKey), 0, 2)) ?: 'px';
                            $permisosPorFecha[$fKey] = $clavePerm;
                        }
                    }
                    $pCursor->addDay();
                }
            }

            // Construir la matriz de días hábiles para el empleado
            $diasEmp = [];
            $fechasFaltas = [];
            $fechasOmisiones = [];
            $fechasPermisos = [];
            $fechasBajas = [];
            $fechasComisiones = [];
            $fechasFeriados = [];
            $fechasNoPagadas = [];

            foreach ($diasMes as $diaInfo) {
                $fIso = $diaInfo['fecha'];
                $fCorta = $diaInfo['fecha_corta'];
                $carbonDia = Carbon::parse($fIso);

                // 1. Un feriado o asueto tampoco genera pago de refrigerio.
                $fechaEspecial = $this->programacionLaboral->obtenerFechaEspecial($carbonDia, $empleado->sucursal);
                $esNoLaborable = $this->programacionLaboral->esDiaNoLaborable($carbonDia, $empleado->sucursal);

                $key = $empId . '|' . $fIso;
                $asist = $asistencias->get($key)?->first();

                if ($esNoLaborable) {
                    $estado = 'fe';
                } elseif ($asist) {
                    // Solo una marcación completa genera pago. Entrada o salida faltante = omisión.
                    $norm = $this->analisisAsistencia->normalizarMarcacionAsistencia($asist);
                    $marcacionCompleta = filled($norm['entrada']) && filled($norm['salida']);
                    $estado = $marcacionCompleta ? 'a' : ($carbonDia->isToday() ? '' : 'o');
                } elseif (isset($permisosPorFecha[$fIso])) {
                    $estado = $permisosPorFecha[$fIso];
                } else {
                    $contratado = $empleado->fecha_contratacion === null || $empleado->fecha_contratacion->toDateString() <= $fIso;
                    $noDespedido = $empleado->fecha_despido === null || $empleado->fecha_despido->toDateString() > $fIso;
                    $jornadaFinalizada = $carbonDia->copy()->startOfDay()->lt(now()->startOfDay());

                    // Una jornada pasada sin marcación es inasistencia, aunque el biométrico
                    // de la sucursal no tenga otros registros. Hoy y fechas futuras quedan pendientes.
                    $estado = $contratado && $noDespedido && $jornadaFinalizada ? 'f' : '';
                }

                $diasEmp[$fIso] = $estado;

                // Desgloses por tipo - '' (sin dato) y 'a' no se penalizan
                if ($estado === 'f') {
                    $fechasFaltas[] = ['fecha' => $fCorta, 'detalle' => 'Inasistencia injustificada'];
                } elseif ($estado === 'o') {
                    $fechasOmisiones[] = ['fecha' => $fCorta, 'detalle' => 'Omisión de marcado'];
                } elseif ($estado === 'fe') {
                    $motivoFeriado = $fechaEspecial?->nombre ?: 'Feriado o día no laborable';
                    $fechasFeriados[] = ['fecha' => $fCorta, 'detalle' => $motivoFeriado];
                } elseif ($estado !== 'a' && $estado !== '' && $estado !== 'p') {
                    // Todos los permisos e incidencias jalados desde Incidencias y Permisos
                    $labelPermiso = match($estado) {
                        'bm' => 'Baja médica autorizada',
                        'cv' => 'Comisión de viaje laboral',
                        default => ($tiposPermisosMap[$estado] ?? ('Permiso ' . strtoupper($estado))),
                    };
                    $fechasPermisos[] = ['fecha' => $fCorta, 'dias' => 1, 'motivo' => $labelPermiso, 'tipo' => $estado];

                    if ($estado === 'bm') {
                        $fechasBajas[] = ['fecha' => $fCorta, 'dias' => 1, 'motivo' => 'Baja médica autorizada'];
                    } elseif ($estado === 'cv') {
                        $fechasComisiones[] = ['fecha' => $fCorta, 'dias' => 1, 'motivo' => 'Comisión de viaje laboral'];
                    }
                }

                if ($estado !== 'a' && $estado !== '' && $estado !== 'p') {
                    $detalleNoPagado = match ($estado) {
                        'f' => 'Inasistencia sin permiso',
                        'o' => 'Omisión de entrada o salida',
                        'fe' => $fechaEspecial?->nombre ?: 'Feriado o día no laborable',
                        'bm' => 'Baja médica autorizada',
                        'cv' => 'Comisión de viaje laboral',
                        default => $tiposPermisosMap[$estado] ?? ('Permiso ' . strtoupper($estado)),
                    };
                    $fechasNoPagadas[] = [
                        'fecha' => $fCorta,
                        'fecha_iso' => $fIso,
                        'tipo' => $estado,
                        'detalle' => $detalleNoPagado,
                    ];
                }
            }

            // Conteos de días
            $faltasCount = count($fechasFaltas);
            $omisionesCount = count($fechasOmisiones);
            $permisosCount = count($fechasPermisos);
            $bajasCount = count($fechasBajas);
            $comisionesCount = count($fechasComisiones);
            $feriadosCount = count($fechasFeriados);
            $diasPagadosCount = count(array_filter($diasEmp, fn(string $estado) => in_array($estado, ['a', 'p'], true)));
            $diasSinDatoCount = count(array_filter($diasEmp, fn(string $estado) => $estado === ''));

            $totalDiasEmp = $faltasCount + $omisionesCount + $permisosCount + $feriadosCount;
            $totalMontoEmp = round($totalDiasEmp * $tarifaDiaria, 2);
            $montoPagadoEmp = round($diasPagadosCount * $tarifaDiaria, 2);

            $totalFaltas += $faltasCount;
            $totalOmisiones += $omisionesCount;
            $totalPermisos += $permisosCount;
            $totalBajas += $bajasCount;
            $totalComisiones += $comisionesCount;
            $totalFeriados += $feriadosCount;
            $granTotalDiasPagados += $diasPagadosCount;
            $granTotalDias += $totalDiasEmp;
            $granTotalMontoPagado += $montoPagadoEmp;
            $granTotalMonto += $totalMontoEmp;

            $items[] = [
                'empleado_id' => $empleado->id,
                'codigo' => $empleado->codigo_biometrico ?: (string) $empleado->id,
                'nombre' => $empleado->nombre_completo,
                'cargo' => $empleado->cargo ?: 'Personal',
                'sucursal' => $empleado->sucursal ?: 'General',
                'area' => $empleado->area ?: 'General',
                // Matriz de días hábiles Lunes a Viernes
                'dias' => $diasEmp,
                // Días individuales acumulados
                'faltas' => $faltasCount,
                'omisiones' => $omisionesCount,
                'permisos' => $permisosCount,
                'bajas_medicas' => $bajasCount,
                'comisiones_viaje' => $comisionesCount,
                'feriados' => $feriadosCount,
                'dias_pagados' => $diasPagadosCount,
                'dias_sin_dato' => $diasSinDatoCount,
                // Totales
                'total_dias' => $totalDiasEmp,
                'tarifa_diaria' => $tarifaDiaria,
                'total_monto' => $totalMontoEmp,
                'monto_pagado' => $montoPagadoEmp,
                'observaciones' => '',
                // Desgloses de fechas para consulta
                'fechas_faltas' => $fechasFaltas,
                'fechas_omisiones' => $fechasOmisiones,
                'fechas_permisos' => $fechasPermisos,
                'fechas_bajas' => $fechasBajas,
                'fechas_comisiones' => $fechasComisiones,
                'fechas_feriados' => $fechasFeriados,
                'fechas_no_pagadas' => $fechasNoPagadas,
            ];
        }

        return [
            'periodo' => $referenceMonth->format('Y-m'),
            'periodo_label' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'sucursal' => $branch ?: 'Todas las sucursales',
            'tarifa_diaria' => $tarifaDiaria,
            'dias_mes' => $diasMes,
            'items' => $items,
            'metricas' => [
                'total_personal' => count($items),
                'personal_con_descuento' => collect($items)->filter(fn($i) => ($i['total_dias'] ?? 0) > 0)->count(),
                'total_faltas' => $totalFaltas,
                'total_omisiones' => $totalOmisiones,
                'total_permisos' => $totalPermisos,
                'total_bajas_medicas' => $totalBajas,
                'total_comisiones_viaje' => $totalComisiones,
                'total_feriados' => $totalFeriados,
                'gran_total_dias_pagados' => $granTotalDiasPagados,
                'gran_total_dias' => $granTotalDias,
                'gran_total_monto_pagado' => round($granTotalMontoPagado, 2),
                'gran_total_monto' => round($granTotalMonto, 2),
            ],
        ];
    }

    /**
     * Cuenta los días hábiles/laborables entre dos fechas limitadas al mes de referencia.
     */
    private function contarDiasLaborablesRango(?Carbon $inicio, ?Carbon $fin, Carbon $limiteMesInicio, Carbon $limiteMesFin, ?string $sucursal): int
    {
        if (!$inicio) {
            return 1;
        }

        $fin = $fin ? $fin->copy() : $inicio->copy();

        // Limitar al mes consultado
        $actualInicio = $inicio->max($limiteMesInicio);
        $actualFin = $fin->min($limiteMesFin);

        if ($actualInicio->gt($actualFin)) {
            return 0;
        }

        $diasLaborables = 0;
        $curr = $actualInicio->copy();

        while ($curr->lte($actualFin)) {
            if (!$this->programacionLaboral->esDiaNoLaborable($curr, $sucursal)) {
                $diasLaborables++;
            }
            $curr->addDay();
        }

        return max(1, $diasLaborables);
    }

    /**
     * Genera un Spreadsheet Excel (.xlsx) con la planilla de refrigerio/comida.
     * Incluye Sheet 1 (Planilla Consolidada) y Sheet 2 (Ficha Detallada por Funcionario).
     */
    public function generarExcel(array $planilla): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $items = $planilla['items'] ?? [];
        $periodoLabel = mb_strtoupper($planilla['periodo_label'] ?? $planilla['periodo'] ?? '');
        $sucursalLabel = mb_strtoupper($planilla['sucursal'] ?? 'TODAS LAS SUCURSALES');
        $tarifaDiaria = (float) ($planilla['tarifa_diaria'] ?? 20.00);

        try {
            $refCarbon = Carbon::createFromFormat('Y-m', $planilla['periodo'] ?? now()->format('Y-m'));
        } catch (\Throwable) {
            $refCarbon = now();
        }
        $diasMes = $planilla['dias_mes'] ?? $this->obtenerDiasHabilesMes($refCarbon);

        // =========================================================================
        // HOJA 1: CONTROL DE ASISTENCIA Y PLANILLA CONSOLIDADA
        // =========================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Control de Asistencia');
        $sheet1->setShowGridLines(true);

        // Logo si existe
        $logoPath = public_path('images/menu-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(42);
            $drawing->setWorksheet($sheet1);
        }

        $r = 1;
        // Membrete
        $numDias = count($diasMes);
        $totalColsCount = 2 + $numDias + 9; // N°, Nombre, Días..., incidencias y totales pagados/no pagados
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColsCount);

        $sheet1->mergeCells("C{$r}:{$lastColLetter}{$r}");
        $sheet1->setCellValue("C{$r}", 'CONTROL DE ASISTENCIA DEL PERSONAL - PLANILLA DE REFRIGERIO');
        $sheet1->getStyle("C{$r}")->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('1E3A8A');
        $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet1->mergeCells("C{$r}:{$lastColLetter}{$r}");
        $sheet1->setCellValue("C{$r}", "Mes: {$periodoLabel} | Sucursal: {$sucursalLabel} | Tarifa: Bs. " . number_format($tarifaDiaria, 2) . "/día");
        $sheet1->getStyle("C{$r}")->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('475569');
        $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r += 2;
        $headerRow = $r;

        // Columnas fijas iniciales
        $sheet1->setCellValue("A{$headerRow}", "N°");
        $sheet1->setCellValue("B{$headerRow}", "Nombre y Apellido");
        $colIdx = 3;

        // Columnas de días hábiles (Lunes a Viernes)
        $colDiasMap = [];
        foreach ($diasMes as $dia) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet1->setCellValue("{$colLetter}{$headerRow}", "{$dia['dia']}/" . substr($dia['fecha_corta'], 3, 2) . "\n{$dia['dia_nombre']}");
            $colDiasMap[$dia['fecha']] = $colLetter;
            $sheet1->getColumnDimension($colLetter)->setWidth(6.5);
            $colIdx++;
        }

        $firstDayCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3);
        $lastDayCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx - 1);

        // Columnas de resumen
        $colF = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colO = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colBm = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colCv = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colFe = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colTotal = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colMonto = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colDiasPagados = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
        $colMontoPagado = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);

        $sheet1->setCellValue("{$colF}{$headerRow}", "F\n(Faltas)");
        $sheet1->setCellValue("{$colO}{$headerRow}", "O\n(Omisión)");
        $sheet1->setCellValue("{$colBm}{$headerRow}", "Bm\n(Baja Méd)");
        $sheet1->setCellValue("{$colCv}{$headerRow}", "Cv\n(Comisión)");
        $sheet1->setCellValue("{$colFe}{$headerRow}", "Fe\n(Feriado)");
        $sheet1->setCellValue("{$colTotal}{$headerRow}", "Días No\nPagados");
        $sheet1->setCellValue("{$colMonto}{$headerRow}", "A No\nPagar (Bs.)");
        $sheet1->setCellValue("{$colDiasPagados}{$headerRow}", "Días\nPagados");
        $sheet1->setCellValue("{$colMontoPagado}{$headerRow}", "A Pagar\n(Bs.)");

        // Estilos cabecera
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C81');
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet1->getRowDimension($headerRow)->setRowHeight(32);

        $r++;
        $dataStartRow = $r;
        $itemIndex = 1;

        foreach ($items as $item) {
            $sheet1->setCellValue("A{$r}", $itemIndex++);
            $sheet1->setCellValue("B{$r}", mb_strtoupper($item['nombre'] ?? ''));

            // Días de la matriz
            $diasItem = $item['dias'] ?? [];
            foreach ($diasMes as $dia) {
                $fIso = $dia['fecha'];
                $cLet = $colDiasMap[$fIso] ?? null;
                if ($cLet) {
                    $st = strtoupper($diasItem[$fIso] ?? '');
                    if ($st === 'BM') { $st = 'Bm'; }
                    if ($st === 'CV') { $st = 'Cv'; }
                    if ($st === 'FE') { $st = 'Fe'; }
                    $sheet1->setCellValue("{$cLet}{$r}", $st);

                    // Colores por celda según simbología
                    $bgRGB = match ($st) {
                        'A', 'P' => 'DCFCE7',
                        'F' => 'FEE2E2',
                        'O' => 'FEF3C7',
                        'Bm' => 'DBEAFE',
                        'Cv' => 'EDE9FE',
                        'Fe' => 'E2E8F0',
                        default => 'FFFFFF',
                    };
                    $txtRGB = match ($st) {
                        'A', 'P' => '15803D',
                        'F' => '991B1B',
                        'O' => '9A3412',
                        'Bm' => '1D4ED8',
                        'Cv' => '6D28D9',
                        'Fe' => '334155',
                        default => '0F172A',
                    };

                    $sheet1->getStyle("{$cLet}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgRGB);
                    $sheet1->getStyle("{$cLet}{$r}")->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB($txtRGB);
                    $sheet1->getStyle("{$cLet}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }

            // Los totales vienen de la misma regla del sistema e incluyen permisos dinámicos.
            $sheet1->setCellValue("{$colF}{$r}", (int) ($item['faltas'] ?? 0));
            $sheet1->setCellValue("{$colO}{$r}", (int) ($item['omisiones'] ?? 0));
            $sheet1->setCellValue("{$colBm}{$r}", (int) ($item['bajas_medicas'] ?? 0));
            $sheet1->setCellValue("{$colCv}{$r}", (int) ($item['comisiones_viaje'] ?? 0));
            $sheet1->setCellValue("{$colFe}{$r}", (int) ($item['feriados'] ?? 0));
            $sheet1->setCellValue("{$colTotal}{$r}", (int) ($item['total_dias'] ?? 0));
            $sheet1->setCellValue("{$colMonto}{$r}", (float) ($item['total_monto'] ?? 0));
            $sheet1->setCellValue("{$colDiasPagados}{$r}", (int) ($item['dias_pagados'] ?? 0));
            $sheet1->setCellValue("{$colMontoPagado}{$r}", (float) ($item['monto_pagado'] ?? 0));

            // Formatos
            $sheet1->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle("{$colF}{$r}:{$colDiasPagados}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("{$colMonto}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("{$colMontoPagado}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("{$colMonto}{$r}:{$colMontoPagado}{$r}")->getNumberFormat()->setFormatCode('#,##0.00');

            // Resaltar si tiene descuento
            $totalDias = (int) ($item['total_dias'] ?? 0);
            if ($totalDias > 0) {
                $sheet1->getStyle("{$colTotal}{$r}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
                $sheet1->getStyle("{$colMonto}{$r}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
            }
            $sheet1->getStyle("{$colDiasPagados}{$r}:{$colMontoPagado}{$r}")->getFont()->setBold(true)->getColor()->setRGB('166534');

            $r++;
        }

        $dataEndRow = max($headerRow + 1, $r - 1);

        // Fila de Totales
        $totalRow = $r;
        $sheet1->mergeCells("A{$totalRow}:{$lastDayCol}{$totalRow}");
        $sheet1->setCellValue("A{$totalRow}", 'TOTALES GENERALES:');
        $sheet1->setCellValue("{$colF}{$totalRow}", "=SUM({$colF}{$dataStartRow}:{$colF}{$dataEndRow})");
        $sheet1->setCellValue("{$colO}{$totalRow}", "=SUM({$colO}{$dataStartRow}:{$colO}{$dataEndRow})");
        $sheet1->setCellValue("{$colBm}{$totalRow}", "=SUM({$colBm}{$dataStartRow}:{$colBm}{$dataEndRow})");
        $sheet1->setCellValue("{$colCv}{$totalRow}", "=SUM({$colCv}{$dataStartRow}:{$colCv}{$dataEndRow})");
        $sheet1->setCellValue("{$colFe}{$totalRow}", "=SUM({$colFe}{$dataStartRow}:{$colFe}{$dataEndRow})");
        $sheet1->setCellValue("{$colTotal}{$totalRow}", "=SUM({$colTotal}{$dataStartRow}:{$colTotal}{$dataEndRow})");
        $sheet1->setCellValue("{$colMonto}{$totalRow}", "=SUM({$colMonto}{$dataStartRow}:{$colMonto}{$dataEndRow})");
        $sheet1->setCellValue("{$colDiasPagados}{$totalRow}", "=SUM({$colDiasPagados}{$dataStartRow}:{$colDiasPagados}{$dataEndRow})");
        $sheet1->setCellValue("{$colMontoPagado}{$totalRow}", "=SUM({$colMontoPagado}{$dataStartRow}:{$colMontoPagado}{$dataEndRow})");

        $sheet1->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getFont()->setBold(true)->setSize(9.5);
        $sheet1->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet1->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("{$colF}{$totalRow}:{$colDiasPagados}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("{$colMonto}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("{$colMontoPagado}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("{$colMonto}{$totalRow}:{$colMontoPagado}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Bordes de tabla
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        $sheet1->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0F172A');
        $sheet1->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0F172A');

        // Sección SIMBOLOGÍA
        $r += 2;
        $sheet1->mergeCells("A{$r}:{$lastColLetter}{$r}");
        $sheet1->setCellValue("A{$r}", 'SIMBOLOGÍA');
        $sheet1->getStyle("A{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C81');
        $sheet1->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $r++;
        $simbologias = [
            ['code' => 'A', 'bg' => 'DCFCE7', 'txt' => '15803D', 'name' => 'Asistencia', 'desc' => 'Entrada y salida completas: día pagado.'],
            ['code' => 'F', 'bg' => 'FEE2E2', 'txt' => '991B1B', 'name' => 'Falta', 'desc' => 'No asistió a su jornada.'],
            ['code' => 'O', 'bg' => 'FEF3C7', 'txt' => '9A3412', 'name' => 'Omisión', 'desc' => 'No registró entrada o salida.'],
            ['code' => 'Bm', 'bg' => 'DBEAFE', 'txt' => '1D4ED8', 'name' => 'Baja médica', 'desc' => 'Incapacidad médica o reposo.'],
            ['code' => 'Cv', 'bg' => 'EDE9FE', 'txt' => '6D28D9', 'name' => 'Comisión de viaje', 'desc' => 'En comisión de trabajo o viaje laboral.'],
            ['code' => 'Fe', 'bg' => 'E2E8F0', 'txt' => '334155', 'name' => 'Feriado / asueto', 'desc' => 'Día no laborable: no genera pago de refrigerio.'],
        ];

        foreach ($simbologias as $sim) {
            $sheet1->setCellValue("A{$r}", $sim['code']);
            $sheet1->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($sim['bg']);
            $sheet1->getStyle("A{$r}")->getFont()->setBold(true)->getColor()->setRGB($sim['txt']);
            $sheet1->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet1->setCellValue("B{$r}", $sim['name']);
            $sheet1->getStyle("B{$r}")->getFont()->setBold(true);

            $sheet1->mergeCells("C{$r}:{$lastColLetter}{$r}");
            $sheet1->setCellValue("C{$r}", $sim['desc']);
            $sheet1->getStyle("C{$r}")->getFont()->setItalic(true)->getColor()->setRGB('475569');
            $r++;
        }

        $sheet1->getColumnDimension('A')->setWidth(6);
        $sheet1->getColumnDimension('B')->setWidth(28);
        $sheet1->getColumnDimension($colF)->setWidth(10);
        $sheet1->getColumnDimension($colO)->setWidth(10);
        $sheet1->getColumnDimension($colBm)->setWidth(11);
        $sheet1->getColumnDimension($colCv)->setWidth(11);
        $sheet1->getColumnDimension($colFe)->setWidth(10);
        $sheet1->getColumnDimension($colTotal)->setWidth(12);
        $sheet1->getColumnDimension($colMonto)->setWidth(14);
        $sheet1->getColumnDimension($colDiasPagados)->setWidth(11);
        $sheet1->getColumnDimension($colMontoPagado)->setWidth(14);

        // =========================================================================
        // HOJA 2: FORMATO VERTICAL / FICHA POR FUNCIONARIO (Solicitado específicamente)
        // "poner el nombre en una columna y en esa misma columna poner si tiene falta abajo 1
        // omision abajo 2 baja medica abajo 2 comision de viaje abajo 3 y que haya una sumatoria..."
        // =========================================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Ficha Vertical por Personal');
        $sheet2->setShowGridLines(true);

        $r2 = 1;
        $sheet2->setCellValue("A{$r2}", 'EMPRESA DE CORREOS DE BOLIVIA - FORMATO VERTICAL DE DESCUENTO DE REFRIGERIO');
        $sheet2->getStyle("A{$r2}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A');
        $r2++;
        $sheet2->setCellValue("A{$r2}", "Período: {$periodoLabel} | Sucursal: {$sucursalLabel} | Tarifa Diaria: Bs. " . number_format($tarifaDiaria, 2));
        $sheet2->getStyle("A{$r2}")->getFont()->setSize(9)->getColor()->setRGB('475569');
        $r2 += 2;

        // Ancho columnas
        $sheet2->getColumnDimension('A')->setWidth(30);
        $sheet2->getColumnDimension('B')->setWidth(18);
        $sheet2->getColumnDimension('C')->setWidth(35);

        foreach ($items as $item) {
            // Cabecera del Funcionario
            $sheet2->mergeCells("A{$r2}:C{$r2}");
            $sheet2->setCellValue("A{$r2}", mb_strtoupper($item['nombre'] ?? '') . " (CI/Cód: " . ($item['codigo'] ?? '') . " - " . ($item['sucursal'] ?? '') . ")");
            $sheet2->getStyle("A{$r2}")->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('FFFFFF');
            $sheet2->getStyle("A{$r2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0284C7');
            $sheet2->getStyle("A{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $startCard = $r2;
            $r2++;

            // Fila 1: Falta
            $sheet2->setCellValue("A{$r2}", 'Falta injustificada');
            $sheet2->setCellValue("B{$r2}", (int) ($item['faltas'] ?? 0));
            $sheet2->setCellValue("C{$r2}", count($item['fechas_faltas'] ?? []) > 0 ? collect($item['fechas_faltas'])->pluck('fecha')->implode(', ') : 'Sin faltas');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowFalta = $r2;
            $r2++;

            // Fila 2: Omisión
            $sheet2->setCellValue("A{$r2}", 'Omisión de registro');
            $sheet2->setCellValue("B{$r2}", (int) ($item['omisiones'] ?? 0));
            $sheet2->setCellValue("C{$r2}", count($item['fechas_omisiones'] ?? []) > 0 ? collect($item['fechas_omisiones'])->pluck('fecha')->implode(', ') : 'Sin omisiones');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowOmision = $r2;
            $r2++;

            // Fila 3: Baja Médica
            $sheet2->setCellValue("A{$r2}", 'Baja médica autorizada');
            $sheet2->setCellValue("B{$r2}", (int) ($item['bajas_medicas'] ?? 0));
            $sheet2->setCellValue("C{$r2}", count($item['fechas_bajas'] ?? []) > 0 ? collect($item['fechas_bajas'])->map(fn($b) => "{$b['fecha']} ({$b['dias']}d)")->implode('; ') : 'Sin bajas');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowBaja = $r2;
            $r2++;

            // Fila 4: Comisión de Viaje
            $sheet2->setCellValue("A{$r2}", 'Comisión de viaje institucional');
            $sheet2->setCellValue("B{$r2}", (int) ($item['comisiones_viaje'] ?? 0));
            $sheet2->setCellValue("C{$r2}", count($item['fechas_comisiones'] ?? []) > 0 ? collect($item['fechas_comisiones'])->map(fn($c) => "{$c['fecha']} ({$c['dias']}d)")->implode('; ') : 'Sin comisiones');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowComision = $r2;
            $r2++;

            // Fila 5: Feriado / asueto
            $sheet2->setCellValue("A{$r2}", 'Feriado o asueto');
            $sheet2->setCellValue("B{$r2}", (int) ($item['feriados'] ?? 0));
            $sheet2->setCellValue("C{$r2}", count($item['fechas_feriados'] ?? []) > 0 ? collect($item['fechas_feriados'])->pluck('fecha')->implode(', ') : 'Sin feriados');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r2++;

            // Sumatoria de días no pagados (incluye permisos dinámicos)
            $sheet2->setCellValue("A{$r2}", 'SUMATORIA DÍAS A DESCONTAR:');
            $sheet2->setCellValue("B{$r2}", (int) ($item['total_dias'] ?? 0));
            $sheet2->setCellValue("C{$r2}", "Total días no correspondidos");
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true);
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowSum = $r2;
            $r2++;

            // Cuánto no se debe pagar (Monto en Bs.)
            $sheet2->setCellValue("A{$r2}", 'CUÁNTO NO SE DEBE PAGAR:');
            $sheet2->setCellValue("B{$r2}", (float) ($item['total_monto'] ?? 0));
            $sheet2->setCellValue("C{$r2}", "Tarifa: Bs. " . number_format($tarifaDiaria, 2) . "/día");
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("B{$r2}")->getNumberFormat()->setFormatCode('"Bs. "#,##0.00');
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF2F2');
            $r2++;

            $sheet2->setCellValue("A{$r2}", 'DÍAS PAGADOS:');
            $sheet2->setCellValue("B{$r2}", (int) ($item['dias_pagados'] ?? 0));
            $sheet2->setCellValue("C{$r2}", 'Solo jornadas con entrada y salida completas');
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true)->getColor()->setRGB('166534');
            $r2++;

            $sheet2->setCellValue("A{$r2}", 'TOTAL A PAGAR:');
            $sheet2->setCellValue("B{$r2}", (float) ($item['monto_pagado'] ?? 0));
            $sheet2->setCellValue("C{$r2}", "Tarifa: Bs. " . number_format($tarifaDiaria, 2) . "/día pagado");
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true)->getColor()->setRGB('166534');
            $sheet2->getStyle("B{$r2}")->getNumberFormat()->setFormatCode('"Bs. "#,##0.00');

            // Bordes del bloque
            $sheet2->getStyle("A{$startCard}:C{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $sheet2->getStyle("A{$startCard}:C{$startCard}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0284C7');

            $r2 += 2; // Espacio entre empleados
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
