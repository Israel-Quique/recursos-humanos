<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Services\AnalisisAsistenciaService;
use Livewire\Component;

class ReportesPage extends Component
{
    public string $referenceMonth = '';
    public string $selectedEmployeeId = '';
    public string $selectedBranch = '';

    public function mount(): void
    {
        $this->referenceMonth = now()->format('Y-m');
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $selectedEmployeeId = filled($this->selectedEmployeeId) ? (int) $this->selectedEmployeeId : null;

        return view('livewire.reportes', [
            'metrics' => $analysis->metricasReportePorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'frequency' => $analysis->frecuenciaAsistencia($referenceMonth, $this->selectedBranch),
            'incidents' => $analysis->incidenciasPorRango($rangeStart, $rangeEnd, $this->selectedBranch),
            'monthlyReport' => $analysis->resumenMensualReporte($referenceMonth, $this->selectedBranch),
            'personalReport' => $analysis->reportePersonalizado($selectedEmployeeId, $rangeStart, $rangeEnd, $this->selectedBranch),
            'employees' => $analysis->empleadosParaReportes($this->selectedBranch),
            'branches' => $analysis->sucursalesParaReportes(),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
        ])->layout('layouts.app', ['title' => 'Reportes de asistencia']);
    }
}
