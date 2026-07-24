<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use Livewire\Component;

class EstructuraCodigoPage extends Component
{
    public function render()
    {
        $files = [
            'config/asistencia.php' => base_path('config/asistencia.php'),
            'app/Services/ImportacionBiometricaService.php' => base_path('app/Services/ImportacionBiometricaService.php'),
            'scripts/process_biometrics.py' => base_path('scripts/process_biometrics.py'),
            'app/Livewire/ImportarExcelPage.php' => base_path('app/Livewire/ImportarExcelPage.php'),
        ];

        $list = [];
        foreach ($files as $label => $path) {
            $list[$label] = file_exists($path)
                ? file_get_contents($path)
                : "(archivo no disponible: $path)";
        }

        $analysis = app(AnalisisAsistenciaService::class);

        return view('livewire.estructura-codigo', [
            'files' => $list,
            'summary' => $analysis->resumenProyecto(),
            'structure' => $analysis->estructuraOrganizacional(),
        ])->layout('layouts.app', ['title' => 'Estructura del proyecto']);
    }
}
