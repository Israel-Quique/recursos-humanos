<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\AnalisisAsistenciaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class ReportesPage extends Component
{
    public string $referenceMonth = '';
    public string $selectedBranch = '';
    public bool $showEmployeeDetailModal = false;
    public ?int $detailEmployeeId = null;
    public array $detailEmployeeReport = [];

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
        $branchLabel = $this->selectedBranch !== '' ? $this->selectedBranch : 'Todas las sucursales';

        $pdf = Pdf::loadView('pdf.reportes-general', [
            'monthLabel' => $monthLabel,
            'branchLabel' => $branchLabel,
            'report' => $report,
            'monthlyReport' => $monthlyReport,
            'incidents' => $incidents,
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

        return view('livewire.reportes', [
            'metrics' => $analysis->metricasReportePorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'frequency' => $analysis->frecuenciaAsistencia($referenceMonth, $this->selectedBranch),
            'incidents' => $analysis->incidenciasPorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'monthlyReport' => $analysis->resumenMensualReporte($referenceMonth, $this->selectedBranch),
            'branches' => $analysis->sucursalesParaReportes(),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'detailEmployeeReport' => $this->detailEmployeeReport,
        ])->layout('layouts.app', ['title' => 'Reportes de asistencia']);
    }
}
