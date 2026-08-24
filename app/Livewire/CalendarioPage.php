<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Livewire\Component;

class CalendarioPage extends Component
{
    public string $referenceMonth = '';
    public string $selectedDate = '';
    public bool $showLateEmployees = true;
    public bool $showMarkedEmployees = true;

    public function mount(): void
    {
        $this->referenceMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate = now()->toDateString();
    }

    public function goToCurrentMonth(): void
    {
        $this->referenceMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate = now()->toDateString();
    }

    public function goToPreviousMonth(): void
    {
        $newReference = Carbon::parse($this->referenceMonth)
            ->subMonthNoOverflow()
            ->startOfMonth();

        $this->referenceMonth = $newReference->toDateString();
        $this->selectedDate = $newReference->toDateString();
    }

    public function goToNextMonth(): void
    {
        $newReference = Carbon::parse($this->referenceMonth)
            ->addMonthNoOverflow()
            ->startOfMonth();

        $this->referenceMonth = $newReference->toDateString();
        $this->selectedDate = $newReference->toDateString();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = Carbon::parse($date)->toDateString();
    }

    public function toggleLateEmployees(): void
    {
        $this->showLateEmployees = ! $this->showLateEmployees;
    }

    public function toggleMarkedEmployees(): void
    {
        $this->showMarkedEmployees = ! $this->showMarkedEmployees;
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::parse($this->referenceMonth);

        return view('livewire.calendario', [
            'calendar' => $analysis->calendarioLaboral($referenceMonth),
            'selectedDay' => $analysis->detalleCalendarioDia($this->selectedDate),
        ])->layout('layouts.app', ['title' => 'Calendario laboral']);
    }
}
