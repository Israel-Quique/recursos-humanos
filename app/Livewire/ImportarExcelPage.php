<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use App\Services\ImportacionBiometricaService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportarExcelPage extends Component
{
    use WithFileUploads;

    #[Validate(['required', 'file', 'mimes:xls,xlsx,csv', 'max:10240'])]
    public $archivo;

    public ?array $lastImportSummary = null;
    public bool $showDeleteModal = false;
    public ?int $pendingDeleteImportacionId = null;
    public string $pendingDeleteImportacionNombre = '';

    public function importFile(): void
    {
        $this->validate();

        $storedPath = $this->archivo->store('importaciones');

        try {
            $importacion = app(ImportacionBiometricaService::class)->importarArchivo(
                storage_path('app/'.$storedPath),
                $this->archivo->getClientOriginalName(),
                auth()->user(),
                $storedPath
            );

            $this->lastImportSummary = $importacion->resumen_json;
            $this->reset('archivo');

            session()->flash('status', 'Archivo importado y asistencias generadas correctamente.');
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('archivo', $exception->getMessage());
        }
    }

    public function updatedArchivo(): void
    {
        $this->resetErrorBag('archivo');
    }

    public function openDeleteModal(int $importacionId, string $nombreArchivo): void
    {
        $this->pendingDeleteImportacionId = $importacionId;
        $this->pendingDeleteImportacionNombre = $nombreArchivo;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteImportacionId = null;
        $this->pendingDeleteImportacionNombre = '';
    }

    public function deleteImportacion(): void
    {
        if (! $this->pendingDeleteImportacionId) {
            return;
        }

        $importacion = \App\Models\Importacion::query()->findOrFail($this->pendingDeleteImportacionId);

        $registros = \App\Models\RegistroAsistencia::query()
            ->where('importacion_id', $importacion->id)
            ->get();

        foreach ($registros as $registro) {
            $registro->forceDelete();
        }

        if ($importacion->ruta_archivo) {
            $relativePath = str_replace('\\', '/', $importacion->ruta_archivo);
            $absolutePath = storage_path('app/'.$relativePath);

            if (Storage::exists($relativePath)) {
                Storage::delete($relativePath);
            } elseif (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }

        $importacion->delete();

        $this->closeDeleteModal();
        $this->lastImportSummary = null;
        session()->flash('status', 'Importacion eliminada y registros asociados borrados.');
    }

    protected function messages(): array
    {
        return [
            'archivo.required' => 'Selecciona un archivo Excel o CSV antes de importar.',
            'archivo.file' => 'El archivo seleccionado no es valido.',
            'archivo.mimes' => 'Solo se admiten archivos .xls, .xlsx o .csv.',
            'archivo.max' => 'El archivo no debe superar los 10 MB.',
        ];
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);

        return view('livewire.importar-excel', [
            'history' => $analysis->historialImportaciones(),
            'connections' => $analysis->estadoBiometricos(),
            'summary' => $this->lastImportSummary,
        ])->layout('layouts.app', ['title' => 'Importacion biometrica']);
    }
}
