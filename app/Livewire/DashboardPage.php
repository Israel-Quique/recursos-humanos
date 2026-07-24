<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);

        return view('livewire.dashboard', [
            'summary' => $analysis->resumenProyecto(),
            'modules' => $analysis->modulosIntegrados(),
            'sourceModules' => $analysis->modulosFuente(),
            'diagnosis' => $analysis->diagnosticoIntegracion(),
            'departmentStats' => $analysis->asistenciaPorDepartamento(),
        ])->layout('layouts.app', ['title' => 'Panel de recursos humanos']);
    }
}
