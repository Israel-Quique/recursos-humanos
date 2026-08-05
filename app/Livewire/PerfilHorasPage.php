<?php

namespace App\Livewire;

use App\Models\Empleado;
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
        $this->referenceMonth = now()->format('Y-m');
    }

    public function render()
    {
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $analysis = app(AnalisisAsistenciaService::class);

        return view('livewire.perfil-horas', [
            'monthOptions' => $this->monthOptions(),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $analysis->reportePersonalizado($this->empleado->id, $rangeStart, $rangeEnd),
        ])->layout('layouts.guest', ['title' => 'Perfil de horas']);
    }

    private function monthOptions(): array
    {
        $start = ($this->empleado->fecha_contratacion ?? now())->copy()->startOfMonth();
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
