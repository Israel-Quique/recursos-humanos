<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class PerfilHorasPage extends Component
{
    public Empleado $empleado;
    public string $referenceMonth = '';
    public string $filterState = 'todos';
    public string $searchQuery = '';
    public string $sortDirection = 'asc';
    public string $searchCarnet = '';

    public function mount(Empleado $empleado): void
    {
        $this->empleado = $empleado;
        $this->referenceMonth = $this->resolveInitialMonth();
    }

    public function buscarOtroCarnet(): void
    {
        $carnet = trim($this->searchCarnet);
        if ($carnet === '') {
            return;
        }

        $targetEmpleado = Empleado::query()
            ->where('codigo_biometrico', $carnet)
            ->orWhere('id', $carnet)
            ->first();

        if (! $targetEmpleado) {
            $this->addError('searchCarnet', 'No encontramos un trabajador con el carnet o código "' . $carnet . '".');

            return;
        }

        $signedPath = URL::signedRoute('perfil-horas', ['empleado' => $targetEmpleado->id], absolute: false);
        $this->redirect($signedPath, navigate: true);
    }

    public function setFilterState(string $state): void
    {
        $this->filterState = $state;
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function render()
    {
        $monthOptions = $this->monthOptions();

        if (! collect($monthOptions)->pluck('value')->contains($this->referenceMonth)) {
            $this->referenceMonth = $monthOptions[0]['value'] ?? now()->format('Y-m');
        }

        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $analysis = app(AnalisisAsistenciaService::class);
        $personalReport = $analysis->reportePersonalizado($this->empleado->id, $rangeStart, $rangeEnd);

        $allRows = collect($personalReport['rows'] ?? []);

        // Filter by state
        if ($this->filterState === 'retrasos') {
            $allRows = $allRows->filter(fn ($r) => ($r['retraso_minutos'] ?? 0) > 0 || ($r['es_retraso'] ?? false) || str_contains(strtolower($r['estado'] ?? ''), 'tarde') || str_contains(strtolower($r['estado'] ?? ''), 'retraso'));
        } elseif ($this->filterState === 'omisiones') {
            $allRows = $allRows->filter(fn ($r) => ($r['es_omision'] ?? false) || ($r['row_tone'] ?? '') === 'warning' || str_contains(strtolower($r['estado'] ?? ''), 'incompleto') || str_contains(strtolower($r['estado'] ?? ''), 'omision') || $r['entrada'] === '--:--' || $r['salida'] === '--:--');
        } elseif ($this->filterState === 'faltas') {
            $allRows = $allRows->filter(fn ($r) => ($r['es_falta'] ?? false) || ($r['row_tone'] ?? '') === 'danger' || str_contains(strtolower($r['estado'] ?? ''), 'falta'));
        } elseif ($this->filterState === 'puntuales') {
            $allRows = $allRows->filter(fn ($r) => ($r['row_tone'] ?? '') === 'default' && ($r['retraso_minutos'] ?? 0) === 0 && $r['entrada'] !== '--:--' && $r['salida'] !== '--:--');
        }

        // Filter by search query
        if (filled(trim($this->searchQuery))) {
            $q = strtolower(trim($this->searchQuery));
            $allRows = $allRows->filter(function ($r) use ($q) {
                return str_contains(strtolower($r['fecha'] ?? ''), $q)
                    || str_contains(strtolower($r['dia_semana'] ?? ''), $q)
                    || str_contains(strtolower($r['estado'] ?? ''), $q)
                    || str_contains(strtolower($r['evento_biometrico'] ?? ''), $q)
                    || str_contains(strtolower($r['estado_biometrico'] ?? ''), $q);
            });
        }

        // Sort direction
        if ($this->sortDirection === 'desc') {
            $allRows = $allRows->reverse();
        }

        $filteredRows = $allRows->values()->all();

        $filteredCount = count($filteredRows);
        $filteredRetrasoMinutos = collect($filteredRows)->sum(fn ($r) => $r['retraso_minutos'] ?? 0);
        $filteredOmisionesCount = collect($filteredRows)->filter(fn ($r) => $r['es_omision'] ?? false)->count();
        $filteredFaltasCount = collect($filteredRows)->filter(fn ($r) => $r['es_falta'] ?? false)->count();

        return view('livewire.perfil-horas', [
            'monthOptions' => $monthOptions,
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $personalReport,
            'filteredRows' => $filteredRows,
            'filteredCount' => $filteredCount,
            'filteredRetrasoMinutos' => $filteredRetrasoMinutos,
            'filteredOmisionesCount' => $filteredOmisionesCount,
            'filteredFaltasCount' => $filteredFaltasCount,
        ])->layout('layouts.guest', ['title' => 'Perfil de horas - ' . $this->empleado->nombre_completo]);
    }

    private function monthOptions(): array
    {
        $months = RegistroAsistencia::query()
            ->where('empleado_id', $this->empleado->id)
            ->whereNotNull('fecha')
            ->orderByDesc('fecha')
            ->get(['fecha'])
            ->map(fn (RegistroAsistencia $registro) => $registro->fecha?->copy()->startOfMonth())
            ->filter()
            ->unique(fn (Carbon $month) => $month->format('Y-m'))
            ->values();

        if ($months->isEmpty()) {
            $fallbackMonth = now()->copy()->startOfMonth();

            return [[
                'value' => $fallbackMonth->format('Y-m'),
                'label' => ucfirst($fallbackMonth->locale('es')->translatedFormat('F Y')),
            ]];
        }

        return $months
            ->map(fn (Carbon $month) => [
                'value' => $month->format('Y-m'),
                'label' => ucfirst($month->locale('es')->translatedFormat('F Y')),
            ])
            ->all();
    }

    private function resolveInitialMonth(): string
    {
        return $this->monthOptions()[0]['value'] ?? now()->format('Y-m');
    }
}
