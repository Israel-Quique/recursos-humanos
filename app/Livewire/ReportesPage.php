<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\AnalisisAsistenciaService;
use App\Support\SucursalNormalizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;

class ReportesPage extends Component
{
    use WithPagination;

    public string $referenceMonth = '';
    public string $selectedBranch = '';
    public string $search = '';
    public string $sortOrder = 'fecha_desc'; // fecha_desc, fecha_asc, nombre_asc, retraso_desc, retraso_asc
    public int $perPage = 15; // 10, 15, 20, 25, 30
    public bool $showEmployeeDetailModal = false;
    public ?int $detailEmployeeId = null;
    public array $detailEmployeeReport = [];

    public function updatingReferenceMonth(): void
    {
        $this->resetPage('atrasosPage');
        $this->resetPage('omisionesPage');
    }

    public function updatingSelectedBranch(): void
    {
        $this->resetPage('atrasosPage');
        $this->resetPage('omisionesPage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('atrasosPage');
        $this->resetPage('omisionesPage');
    }

    public function updatingSortOrder(): void
    {
        $this->resetPage('atrasosPage');
        $this->resetPage('omisionesPage');
    }

    public function updatingPerPage(): void
    {
        // Validar min 10 max 30
        $this->perPage = max(10, min(30, (int) $this->perPage));
        $this->resetPage('atrasosPage');
        $this->resetPage('omisionesPage');
    }

    public function mount(): void
    {
        $this->referenceMonth = now()->format('Y-m');
    }

    public function openEmployeeDetailModal(int $employeeId): void
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();

        $this->detailEmployeeId = $employeeId;
        $this->detailEmployeeReport = $analysis->detalleMensualPorEmpleado($employeeId, $referenceMonth, $this->selectedBranch) ?? [];
        $this->showEmployeeDetailModal = ! empty($this->detailEmployeeReport);
    }

    public function closeEmployeeDetailModal(): void
    {
        $this->showEmployeeDetailModal = false;
        $this->detailEmployeeId = null;
        $this->detailEmployeeReport = [];
    }

    public function selectReferenceMonth(string $month): void
    {
        try {
            $this->referenceMonth = Carbon::createFromFormat('Y-m', $month)->format('Y-m');
        } catch (\Throwable $exception) {
            return;
        }
    }

    public function descargarPdfReporte()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $monthLabel = ucfirst($referenceMonth->locale('es')->translatedFormat('F Y'));
        $report = $analysis->reporteMensualNoMarcadosYAtrasos($referenceMonth, $this->selectedBranch);
        $monthlyReport = $analysis->resumenMensualReporte($referenceMonth, $this->selectedBranch);
        $incidents = $analysis->incidenciasPorRango($rangeStart, $rangeEnd, $this->selectedBranch);
        $reporteSucursales = $analysis->reporteConsolidadoPorSucursal($referenceMonth, $this->selectedBranch);
        $branchLabel = $this->selectedBranch !== '' ? $this->selectedBranch : 'Todas las sucursales';

        $pdf = Pdf::loadView('pdf.reportes-general', [
            'monthLabel' => $monthLabel,
            'branchLabel' => $branchLabel,
            'report' => $report,
            'monthlyReport' => $monthlyReport,
            'incidents' => $incidents,
            'reporteSucursales' => $reporteSucursales,
        ])->setPaper('a4');

        $fileName = 'reporte-general-asistencia-'.Str::slug($branchLabel).'-'.$referenceMonth->format('Y-m').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function descargarPdfDetalleEmpleado()
    {
        if (empty($this->detailEmployeeReport)) {
            return;
        }

        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $monthLabel = ucfirst($referenceMonth->locale('es')->translatedFormat('F Y'));
        $employeeName = $this->detailEmployeeReport['empleado']['nombre'] ?? 'personal';
        $fileName = 'detalle-mensual-'.Str::slug($employeeName).'-'.$referenceMonth->format('Y-m').'.pdf';

        $pdf = Pdf::loadView('pdf.reportes-detalle-empleado', [
            'detailEmployeeReport' => $this->detailEmployeeReport,
            'monthLabel' => $monthLabel,
        ])->setPaper('a4');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName);
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();

        // Reporte personal del empleado autenticado
        $authUser = auth()->user()?->loadMissing('empleado');
        $reportePersonal = null;
        if ($authUser?->empleado_id) {
            $reportePersonal = $analysis->detalleMensualPorEmpleado(
                (int) $authUser->empleado_id,
                $referenceMonth,
                null
            );
        }

        // Datos de atrasos y omisiones del mes
        $reporteAtrasoOmision = $analysis->reporteMensualNoMarcadosYAtrasos($referenceMonth, $this->selectedBranch);

        // --- FILTRADO Y ORDENACIÓN DE ATRASOS ---
        $atrasosItems = collect($reporteAtrasoOmision['atrasos'] ?? []);
        if (filled($this->search)) {
            $term = Str::ascii(Str::lower(trim($this->search)));
            $atrasosItems = $atrasosItems->filter(function ($item) use ($term) {
                $nombre = Str::ascii(Str::lower($item['nombre'] ?? ''));
                $codigo = Str::ascii(Str::lower($item['codigo'] ?? ''));
                return str_contains($nombre, $term) || str_contains($codigo, $term);
            });
        }

        switch ($this->sortOrder) {
            case 'nombre_asc':
                $atrasosItems = $atrasosItems->sortBy('nombre');
                break;
            case 'retraso_desc':
                $atrasosItems = $atrasosItems->sortByDesc('minutos_retraso');
                break;
            case 'retraso_asc':
                $atrasosItems = $atrasosItems->sortBy('minutos_retraso');
                break;
            case 'fecha_asc':
                $atrasosItems = $atrasosItems->sortBy('fecha');
                break;
            case 'fecha_desc':
            default:
                $atrasosItems = $atrasosItems->sortByDesc('fecha');
                break;
        }

        $perPageVal = max(10, min(30, (int) $this->perPage));

        $atrasosPage = $this->getPage('atrasosPage');
        $atrasosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $atrasosItems->forPage($atrasosPage, $perPageVal)->values(),
            $atrasosItems->count(),
            $perPageVal,
            $atrasosPage,
            ['pageName' => 'atrasosPage']
        );

        // --- FILTRADO Y ORDENACIÓN DE OMISIONES ---
        $omisionesItems = collect($reporteAtrasoOmision['no_marcados'] ?? []);
        if (filled($this->search)) {
            $term = Str::ascii(Str::lower(trim($this->search)));
            $omisionesItems = $omisionesItems->filter(function ($item) use ($term) {
                $nombre = Str::ascii(Str::lower($item['nombre'] ?? ''));
                $codigo = Str::ascii(Str::lower($item['codigo'] ?? ''));
                return str_contains($nombre, $term) || str_contains($codigo, $term);
            });
        }

        switch ($this->sortOrder) {
            case 'nombre_asc':
                $omisionesItems = $omisionesItems->sortBy('nombre');
                break;
            case 'fecha_asc':
                $omisionesItems = $omisionesItems->sortBy('fecha');
                break;
            case 'fecha_desc':
            default:
                $omisionesItems = $omisionesItems->sortByDesc('fecha');
                break;
        }

        $omisionesPage = $this->getPage('omisionesPage');
        $omisionesPaginadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $omisionesItems->forPage($omisionesPage, $perPageVal)->values(),
            $omisionesItems->count(),
            $perPageVal,
            $omisionesPage,
            ['pageName' => 'omisionesPage']
        );

        // Reporte consolidado por sucursal para vista y agrupaciones
        $reporteSucursales = $analysis->reporteConsolidadoPorSucursal($referenceMonth, $this->selectedBranch);
        if (filled($this->search)) {
            $term = Str::ascii(Str::lower(trim($this->search)));
            $filteredSucursales = [];
            foreach ($reporteSucursales['sucursales'] as $suc) {
                $matchedEmpleados = array_values(array_filter($suc['empleados'], function ($emp) use ($term) {
                    $nombre = Str::ascii(Str::lower($emp['nombre'] ?? ''));
                    $codigo = Str::ascii(Str::lower($emp['codigo'] ?? ''));
                    return str_contains($nombre, $term) || str_contains($codigo, $term);
                }));
                if (! empty($matchedEmpleados)) {
                    $suc['empleados'] = $matchedEmpleados;
                    $filteredSucursales[] = $suc;
                }
            }
            $reporteSucursales['sucursales'] = $filteredSucursales;
        }

        // Estadísticas ejecutivas de Atrasos
        $minutosTotalesAtrasos = (int) $atrasosItems->sum('minutos_retraso');
        $atrasosStats = [
            'total_registros' => $atrasosItems->count(),
            'total_minutos' => $minutosTotalesAtrasos,
            'total_minutos_formato' => $analysis->formatearMinutosEtiqueta($minutosTotalesAtrasos),
            'personal_afectado' => $atrasosItems->pluck('codigo')->filter()->unique()->count(),
            'promedio_minutos' => $atrasosItems->count() > 0 ? (int) round($minutosTotalesAtrasos / $atrasosItems->count()) : 0,
            'maximo_minutos' => (int) ($atrasosItems->max('minutos_retraso') ?? 0),
            'por_sucursal' => $atrasosItems->groupBy(fn($i) => SucursalNormalizer::normalize($i['sucursal'] ?? '') ?: 'General')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'minutos' => (int) $group->sum('minutos_retraso'),
                    'formato' => $analysis->formatearMinutosEtiqueta((int) $group->sum('minutos_retraso')),
                ]),
        ];

        // Estadísticas ejecutivas de Omisiones
        $omisionesFaltasCount = $omisionesItems->filter(fn($i) => str_contains($i['detalle'] ?? '', 'Día') || str_contains($i['estado'] ?? '', 'Día'))->count();
        $omisionesIncompletasCount = $omisionesItems->count() - $omisionesFaltasCount;
        $omisionesStats = [
            'total_omisiones' => $omisionesItems->count(),
            'dias_sin_marcar' => $omisionesFaltasCount,
            'marcas_incompletas' => $omisionesIncompletasCount,
            'personal_afectado' => $omisionesItems->pluck('codigo')->filter()->unique()->count(),
            'por_sucursal' => $omisionesItems->groupBy(fn($i) => SucursalNormalizer::normalize($i['sucursal'] ?? '') ?: 'General')
                ->map(fn($group) => [
                    'count' => $group->count(),
                ]),
        ];

        return view('livewire.reportes', [
            'metrics'              => $analysis->metricasReportePorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'frequency'            => $analysis->frecuenciaAsistencia($referenceMonth, $this->selectedBranch),
            'incidents'            => $analysis->incidenciasPorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'monthlyReport'        => $analysis->resumenMensualReporte($referenceMonth, $this->selectedBranch),
            'branches'             => $analysis->sucursalesParaReportes(),
            'monthLabel'           => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'detailEmployeeReport' => $this->detailEmployeeReport,
            // Reportes
            'cumpleanos'           => $analysis->cumpleaniosMes($referenceMonth, $this->selectedBranch),
            'rankingMensual'       => $analysis->rankingPuntualidadMensual($referenceMonth, $this->selectedBranch, 5),
            'rankingSemanal'       => $analysis->rankingPuntualidadSemanal($this->selectedBranch, 5),
            'reportesAntiguedad'   => $analysis->reportesAntiguedad($this->selectedBranch, 10),
            'detalleAtrasos'       => $atrasosPaginados,
            'totalAtrasos'         => $atrasosItems->count(),
            'atrasosStats'         => $atrasosStats,
            'detalleOmisiones'     => $omisionesPaginadas,
            'totalOmisiones'       => $omisionesItems->count(),
            'omisionesStats'       => $omisionesStats,
            'reportePersonal'      => $reportePersonal,
            'reporteSucursales'    => $reporteSucursales,
            'authEmpleadoNombre'   => $authUser?->empleado?->nombre_completo ?? null,
        ])->layout('layouts.app', ['title' => 'Reportes de asistencia']);
    }
}
