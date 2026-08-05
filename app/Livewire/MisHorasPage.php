<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Livewire\Component;

class MisHorasPage extends Component
{
    public string $referenceMonth = '';

    public function mount(): void
    {
        $this->referenceMonth = now()->format('Y-m');
    }

    public function render()
    {
        $user = auth()->user()?->loadMissing('empleado');
        $empleado = $user?->empleado;
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $analysis = app(AnalisisAsistenciaService::class);

        return view('livewire.mis-horas', [
            'empleado' => $empleado,
            'monthOptions' => $this->monthOptions($empleado),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $empleado
                ? $analysis->reportePersonalizado($empleado->id, $rangeStart, $rangeEnd)
                : null,
        ])->layout('layouts.app', ['title' => 'Mis horas marcadas']);
    }

    private function monthOptions($empleado): array
    {
        if (! $empleado) {
            return [[
                'value' => now()->format('Y-m'),
                'label' => ucfirst(now()->locale('es')->translatedFormat('F Y')),
            ]];
        }

        $start = ($empleado->fecha_contratacion ?? now())->copy()->startOfMonth();
        $end = now()->copy()->startOfMonth();

        if ($start->greaterThan($end)) {
            $start = $end->copy();
        }

        $options = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('es')->translatedFormat('F Y')),
            ];
            $cursor->addMonthNoOverflow();
        }

        return array_reverse($options);
    }
}
