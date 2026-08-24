<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Livewire\Component;

class PerfilHorasPage extends Component
{
    public Empleado $empleado;
    public string $referenceMonth = '';

    public function mount(Empleado $empleado): void
    {
        $this->empleado = $empleado;
        $this->referenceMonth = $this->resolveInitialMonth();
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

        return view('livewire.perfil-horas', [
            'monthOptions' => $monthOptions,
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $analysis->reportePersonalizado($this->empleado->id, $rangeStart, $rangeEnd),
        ])->layout('layouts.guest', ['title' => 'Perfil de horas']);
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
