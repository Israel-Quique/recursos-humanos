<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
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
     * Calcula la planilla de descuento de refrigerio/comida para un mes y sucursal.
     */
    public function calcularPlanilla(Carbon $referenceMonth, ?string $branch = null, float $tarifaDiaria = 20.00): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();

        // 1. Obtener empleados activos
        $empleados = Empleado::query()
            ->activosLaboralmente($end)
            ->when(filled($branch), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch))
            ->orderBy('sucursal')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        // 2. Extraer datos base de atrasos, omisiones y faltas
        $reporteBase = $this->analisisAsistencia->reporteMensualNoMarcadosYAtrasos($referenceMonth, $branch);
        $omisionesPorEmpleado = collect($reporteBase['omisiones'] ?? $reporteBase['no_marcados'] ?? [])->groupBy('empleado_id');
        $faltasPorEmpleado = collect($reporteBase['faltas'] ?? [])->groupBy('empleado_id');

        // 3. Extraer permisos laborales aprobados en el rango
        $permisosQuery = PermisoLaboral::query()
            ->with('empleado')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString());

        if (filled($branch)) {
            $permisosQuery->whereHas('empleado', fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));
        }

        $permisosAprobados = $permisosQuery->get()->groupBy('empleado_id');

        // 4. Procesar cada empleado
        $items = [];
        $totalFaltas = 0;
        $totalOmisiones = 0;
        $totalBajas = 0;
        $totalComisiones = 0;
        $granTotalDias = 0;
        $granTotalMonto = 0.0;

        foreach ($empleados as $empleado) {
            $empId = $empleado->id;

            // --- A. FALTAS (Inasistencias injustificadas del biométrico / sistema) ---
            $faltasColeccion = $faltasPorEmpleado->get($empId, collect());
            $fechasFaltas = [];
            foreach ($faltasColeccion as $f) {
                $fechasFaltas[] = [
                    'fecha' => $f['fecha'] ?? '',
                    'detalle' => $f['detalle'] ?? 'Inasistencia sin registro',
                ];
            }
            $faltasCount = count($fechasFaltas);

            // --- B. OMISIONES (Marcaciones incompletas: falta entrada o falta salida) ---
            $omisionesColeccion = $omisionesPorEmpleado->get($empId, collect());
            $fechasOmisiones = [];
            foreach ($omisionesColeccion as $o) {
                $fechasOmisiones[] = [
                    'fecha' => $o['fecha'] ?? '',
                    'detalle' => $o['detalle'] ?? 'Omisión de marcado',
                ];
            }
            $omisionesCount = count($fechasOmisiones);

            // --- C. PERMISOS: Bajas Médicas y Comisiones de Viaje ---
            $permisosEmp = $permisosAprobados->get($empId, collect());

            $fechasBajas = [];
            $fechasComisiones = [];

            foreach ($permisosEmp as $permiso) {
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

                // Si es tipo falta explícito
                if ($permiso->tipo === 'falta' && !$esBajaMedica && !$esComisionViaje) {
                    $diasFaltaPermiso = $this->contarDiasLaborablesRango($permiso->fecha_inicio, $permiso->fecha_fin, $start, $end, $empleado->sucursal);
                    $faltasCount += $diasFaltaPermiso;
                    $fechasFaltas[] = [
                        'fecha' => $permiso->fecha_inicio?->format('d/m/Y') . ($permiso->fecha_fin && $permiso->fecha_fin->ne($permiso->fecha_inicio) ? ' al ' . $permiso->fecha_fin->format('d/m/Y') : ''),
                        'detalle' => $permiso->motivo ?: 'Falta registrada en incidencias',
                    ];
                    continue;
                }

                if ($esBajaMedica) {
                    $diasBaja = $this->contarDiasLaborablesRango($permiso->fecha_inicio, $permiso->fecha_fin, $start, $end, $empleado->sucursal);
                    $fechasBajas[] = [
                        'fecha' => $permiso->fecha_inicio?->format('d/m/Y') . ($permiso->fecha_fin && $permiso->fecha_fin->ne($permiso->fecha_inicio) ? ' al ' . $permiso->fecha_fin->format('d/m/Y') : ''),
                        'dias' => $diasBaja,
                        'motivo' => $permiso->motivo ?: 'Baja médica autorizada',
                    ];
                } elseif ($esComisionViaje) {
                    $diasComision = $this->contarDiasLaborablesRango($permiso->fecha_inicio, $permiso->fecha_fin, $start, $end, $empleado->sucursal);
                    $fechasComisiones[] = [
                        'fecha' => $permiso->fecha_inicio?->format('d/m/Y') . ($permiso->fecha_fin && $permiso->fecha_fin->ne($permiso->fecha_inicio) ? ' al ' . $permiso->fecha_fin->format('d/m/Y') : ''),
                        'dias' => $diasComision,
                        'motivo' => $permiso->motivo ?: 'Comisión de viaje laboral',
                    ];
                }
            }

            $bajasCount = (int) collect($fechasBajas)->sum('dias');
            $comisionesCount = (int) collect($fechasComisiones)->sum('dias');

            $totalDiasEmp = $faltasCount + $omisionesCount + $bajasCount + $comisionesCount;
            $totalMontoEmp = round($totalDiasEmp * $tarifaDiaria, 2);

            $totalFaltas += $faltasCount;
            $totalOmisiones += $omisionesCount;
            $totalBajas += $bajasCount;
            $totalComisiones += $comisionesCount;
            $granTotalDias += $totalDiasEmp;
            $granTotalMonto += $totalMontoEmp;

            $items[] = [
                'empleado_id' => $empleado->id,
                'codigo' => $empleado->codigo_biometrico ?: (string) $empleado->id,
                'nombre' => $empleado->nombre_completo,
                'cargo' => $empleado->cargo ?: 'Personal',
                'sucursal' => $empleado->sucursal ?: 'General',
                'area' => $empleado->area ?: 'General',
                // Días individuales (editables)
                'faltas' => $faltasCount,
                'omisiones' => $omisionesCount,
                'bajas_medicas' => $bajasCount,
                'comisiones_viaje' => $comisionesCount,
                // Totales
                'total_dias' => $totalDiasEmp,
                'tarifa_diaria' => $tarifaDiaria,
                'total_monto' => $totalMontoEmp,
                'observaciones' => '',
                // Desgloses de fechas para consulta
                'fechas_faltas' => $fechasFaltas,
                'fechas_omisiones' => $fechasOmisiones,
                'fechas_bajas' => $fechasBajas,
                'fechas_comisiones' => $fechasComisiones,
            ];
        }

        return [
            'periodo' => $referenceMonth->format('Y-m'),
            'periodo_label' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'sucursal' => $branch ?: 'Todas las sucursales',
            'tarifa_diaria' => $tarifaDiaria,
            'items' => $items,
            'metricas' => [
                'total_personal' => count($items),
                'personal_con_descuento' => collect($items)->filter(fn($i) => ($i['total_dias'] ?? 0) > 0)->count(),
                'total_faltas' => $totalFaltas,
                'total_omisiones' => $totalOmisiones,
                'total_bajas_medicas' => $totalBajas,
                'total_comisiones_viaje' => $totalComisiones,
                'gran_total_dias' => $granTotalDias,
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

        // =========================================================================
        // HOJA 1: PLANILLA CONSOLIDADA
        // =========================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Planilla Consolidada');
        $sheet1->setShowGridLines(true);

        // Logo si existe
        $logoPath = public_path('images/menu-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(46);
            $drawing->setWorksheet($sheet1);
        }

        $r = 1;
        // Membrete
        $sheet1->mergeCells("C{$r}:L{$r}");
        $sheet1->setCellValue("C{$r}", 'EMPRESA DE CORREOS DE BOLIVIA');
        $sheet1->getStyle("C{$r}")->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('1E3A8A');
        $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet1->mergeCells("C{$r}:L{$r}");
        $sheet1->setCellValue("C{$r}", 'DIRECCIÓN DE RECURSOS HUMANOS · CONTROL DE ASISTENCIA');
        $sheet1->getStyle("C{$r}")->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('475569');
        $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet1->mergeCells("C{$r}:L{$r}");
        $sheet1->setCellValue("C{$r}", 'PLANILLA DE DÍAS QUE NO CORRESPONDE PAGAR REFRIGERIO / COMIDA');
        $sheet1->getStyle("C{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('0F172A');
        $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r += 2;
        // Metadatos
        $sheet1->mergeCells("A{$r}:D{$r}");
        $sheet1->setCellValue("A{$r}", "PERÍODO: {$periodoLabel}");
        $sheet1->getStyle("A{$r}")->getFont()->setBold(true)->setSize(9);

        $sheet1->mergeCells("E{$r}:H{$r}");
        $sheet1->setCellValue("E{$r}", "SUCURSAL: {$sucursalLabel}");
        $sheet1->getStyle("E{$r}")->getFont()->setBold(true)->setSize(9);

        $sheet1->mergeCells("I{$r}:L{$r}");
        $sheet1->setCellValue("I{$r}", "TARIFA DIARIA: Bs. " . number_format($tarifaDiaria, 2) . " | EMISIÓN: " . now()->format('d/m/Y H:i'));
        $sheet1->getStyle("I{$r}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('0F766E');
        $sheet1->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $r += 2;
        $headerRow = $r;
        $headers = [
            'A' => 'N°',
            'B' => 'CÓDIGO',
            'C' => 'FUNCIONARIO',
            'D' => 'CARGO / ÁREA',
            'E' => 'SUCURSAL',
            'F' => "FALTAS\n(Días)",
            'G' => "OMISIONES\n(Días)",
            'H' => "BAJAS MÉDICAS\n(Días)",
            'I' => "COMISIÓN VIAJE\n(Días)",
            'J' => "TOTAL DÍAS\nDESCUENTO",
            'K' => "TARIFA\n(Bs./Día)",
            'L' => "MONTO TOTAL\nA NO PAGAR (Bs.)",
            'M' => 'OBSERVACIONES',
        ];

        foreach ($headers as $col => $text) {
            $sheet1->setCellValue("{$col}{$headerRow}", $text);
        }

        $sheet1->getStyle("A{$headerRow}:M{$headerRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle("A{$headerRow}:M{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
        $sheet1->getStyle("A{$headerRow}:M{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet1->getRowDimension($headerRow)->setRowHeight(32);

        $r++;
        $dataStartRow = $r;
        $itemIndex = 1;

        foreach ($items as $item) {
            $sheet1->setCellValue("A{$r}", $itemIndex++);
            $sheet1->setCellValue("B{$r}", $item['codigo'] ?? '');
            $sheet1->setCellValue("C{$r}", mb_strtoupper($item['nombre'] ?? ''));
            $sheet1->setCellValue("D{$r}", ($item['cargo'] ?? 'Personal') . ' - ' . ($item['area'] ?? ''));
            $sheet1->setCellValue("E{$r}", $item['sucursal'] ?? '');
            $sheet1->setCellValue("F{$r}", (int) ($item['faltas'] ?? 0));
            $sheet1->setCellValue("G{$r}", (int) ($item['omisiones'] ?? 0));
            $sheet1->setCellValue("H{$r}", (int) ($item['bajas_medicas'] ?? 0));
            $sheet1->setCellValue("I{$r}", (int) ($item['comisiones_viaje'] ?? 0));

            // Fórmulas de Excel
            $sheet1->setCellValue("J{$r}", "=SUM(F{$r}:I{$r})");
            $sheet1->setCellValue("K{$r}", (float) ($item['tarifa_diaria'] ?? $tarifaDiaria));
            $sheet1->setCellValue("L{$r}", "=J{$r}*K{$r}");
            $sheet1->setCellValue("M{$r}", $item['observaciones'] ?? '');

            // Formatos y alineaciones
            $sheet1->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("F{$r}:J{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("K{$r}:L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("K{$r}:L{$r}")->getNumberFormat()->setFormatCode('#,##0.00');

            // Resaltar suave si tiene descuento
            $totalDias = (int) ($item['total_dias'] ?? 0);
            if ($totalDias > 0) {
                $sheet1->getStyle("J{$r}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
                $sheet1->getStyle("L{$r}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
            }

            $r++;
        }

        $dataEndRow = max($headerRow + 1, $r - 1);

        // Fila de Totales
        $totalRow = $r;
        $sheet1->mergeCells("A{$totalRow}:E{$totalRow}");
        $sheet1->setCellValue("A{$totalRow}", 'TOTALES GENERALES:');
        $sheet1->setCellValue("F{$totalRow}", "=SUM(F{$dataStartRow}:F{$dataEndRow})");
        $sheet1->setCellValue("G{$totalRow}", "=SUM(G{$dataStartRow}:G{$dataEndRow})");
        $sheet1->setCellValue("H{$totalRow}", "=SUM(H{$dataStartRow}:H{$dataEndRow})");
        $sheet1->setCellValue("I{$totalRow}", "=SUM(I{$dataStartRow}:I{$dataEndRow})");
        $sheet1->setCellValue("J{$totalRow}", "=SUM(J{$dataStartRow}:J{$dataEndRow})");
        $sheet1->setCellValue("K{$totalRow}", '');
        $sheet1->setCellValue("L{$totalRow}", "=SUM(L{$dataStartRow}:L{$dataEndRow})");
        $sheet1->setCellValue("M{$totalRow}", '');

        $sheet1->getStyle("A{$totalRow}:M{$totalRow}")->getFont()->setBold(true)->setSize(9.5);
        $sheet1->getStyle("A{$totalRow}:M{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet1->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("F{$totalRow}:J{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("L{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("L{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Bordes de tabla
        $sheet1->getStyle("A{$headerRow}:M{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        $sheet1->getStyle("A{$headerRow}:M{$headerRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0F172A');
        $sheet1->getStyle("A{$totalRow}:M{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0F172A');

        // Sección de firmas
        $r += 4;
        $sheet1->mergeCells("B{$r}:D{$r}");
        $sheet1->mergeCells("F{$r}:H{$r}");
        $sheet1->mergeCells("J{$r}:L{$r}");
        $sheet1->getStyle("B{$r}:D{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet1->getStyle("F{$r}:H{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet1->getStyle("J{$r}:L{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        $sheet1->setCellValue("B{$r}", "ELABORADO POR\nResponsable de Asistencia");
        $sheet1->setCellValue("F{$r}", "REVISADO POR\nJefe de Recursos Humanos");
        $sheet1->setCellValue("J{$r}", "APROBADO POR\nDirector Adm. Financiero");
        $sheet1->getStyle("B{$r}:L{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet1->getStyle("B{$r}:L{$r}")->getFont()->setSize(8.5)->setBold(true);

        // Auto-dimensionar columnas
        foreach (range('A', 'M') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

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

            // Fila 5: Sumatoria de días
            $sheet2->setCellValue("A{$r2}", 'SUMATORIA DÍAS A DESCONTAR:');
            $sheet2->setCellValue("B{$r2}", "=SUM(B{$rowFalta}:B{$rowComision})");
            $sheet2->setCellValue("C{$r2}", "Total días no correspondidos");
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true);
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowSum = $r2;
            $r2++;

            // Fila 6: Cuánto no se debe pagar (Monto en Bs.)
            $sheet2->setCellValue("A{$r2}", 'CUÁNTO NO SE DEBE PAGAR:');
            $sheet2->setCellValue("B{$r2}", "=B{$rowSum}*{$tarifaDiaria}");
            $sheet2->setCellValue("C{$r2}", "Tarifa: Bs. " . number_format($tarifaDiaria, 2) . "/día");
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
            $sheet2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("B{$r2}")->getNumberFormat()->setFormatCode('"Bs. "#,##0.00');
            $sheet2->getStyle("A{$r2}:C{$r2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF2F2');

            // Bordes del bloque
            $sheet2->getStyle("A{$startCard}:C{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $sheet2->getStyle("A{$startCard}:C{$startCard}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0284C7');

            $r2 += 2; // Espacio entre empleados
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
