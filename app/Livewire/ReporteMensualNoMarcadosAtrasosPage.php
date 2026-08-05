<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Livewire\Component;

class ReporteMensualNoMarcadosAtrasosPage extends Component
{
    private const PER_PAGE = 10;

    public string $referenceMonth = '';
    public string $selectedBranch = '';
    public string $activeTable = 'resumen_atrasos';
    public int $lateSummaryPage = 1;
    public int $lateDetailsPage = 1;
    public int $forgotMarksPage = 1;

    public function mount(): void
    {
        $this->referenceMonth = now()->format('Y-m');
    }

    public function updatedReferenceMonth(): void
    {
        $this->resetPaginationState();
    }

    public function updatedSelectedBranch(): void
    {
        $this->resetPaginationState();
    }

    public function goToLateSummaryPage(int $page): void
    {
        $this->lateSummaryPage = max(1, $page);
    }

    public function goToLateDetailsPage(int $page): void
    {
        $this->lateDetailsPage = max(1, $page);
    }

    public function goToForgotMarksPage(int $page): void
    {
        $this->forgotMarksPage = max(1, $page);
    }

    public function showTable(string $table): void
    {
        if (! in_array($table, ['resumen_atrasos', 'atrasos', 'no_marcados'], true)) {
            return;
        }

        $this->activeTable = $table;
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $report = $analysis->reporteMensualNoMarcadosYAtrasos($referenceMonth, $this->selectedBranch);

        $lateSummaryPagination = $this->paginateItems($report['resumen_atrasos'], $this->lateSummaryPage);
        $lateDetailsPagination = $this->paginateItems($report['atrasos'], $this->lateDetailsPage);
        $forgotMarksPagination = $this->paginateItems($report['no_marcados'], $this->forgotMarksPage);

        $this->lateSummaryPage = $lateSummaryPagination['page'];
        $this->lateDetailsPage = $lateDetailsPagination['page'];
        $this->forgotMarksPage = $forgotMarksPagination['page'];

        return view('livewire.reporte-mensual-no-marcados-atrasos', [
            'report' => [
                ...$report,
                'resumen_atrasos' => $lateSummaryPagination['items'],
                'atrasos' => $lateDetailsPagination['items'],
                'no_marcados' => $forgotMarksPagination['items'],
            ],
            'branches' => $analysis->sucursalesParaReportes(),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'lateSummaryPagination' => $lateSummaryPagination,
            'lateDetailsPagination' => $lateDetailsPagination,
            'forgotMarksPagination' => $forgotMarksPagination,
        ])->layout('layouts.app', ['title' => 'Reporte mensual de no marcados y atrasos']);
    }

    private function resetPaginationState(): void
    {
        $this->lateSummaryPage = 1;
        $this->lateDetailsPage = 1;
        $this->forgotMarksPage = 1;
    }

    private function paginateItems(array $items, int $page): array
    {
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $lastPage);
        $offset = ($page - 1) * self::PER_PAGE;

        return [
            'items' => array_slice($items, $offset, self::PER_PAGE),
            'page' => $page,
            'per_page' => self::PER_PAGE,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total === 0 ? 0 : $offset + 1,
            'to' => min($offset + self::PER_PAGE, $total),
        ];
    }
}
