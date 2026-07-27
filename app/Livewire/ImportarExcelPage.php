<?php

namespace App\Livewire;

use App\Models\BiometricoDispositivo;
use App\Services\AnalisisAsistenciaService;
use App\Services\ConexionBiometricoService;
use App\Services\ExtraccionBiometricoZkService;
use App\Services\ImportacionBiometricaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportarExcelPage extends Component
{
    use WithFileUploads;

    #[Validate(['required', 'file', 'mimes:xls,xlsx,csv', 'max:10240'])]
    public $archivo;

    public string $deviceDepartment = '';
    public string $deviceBranch = '';
    public string $deviceIp = '';
    public string $devicePort = '4370';
    public string $deviceConnectionMode = 'TCP/IP';
    public string $deviceCommunicationPassword = '';
    public string $exportYear = '';
    public string $exportMonth = '';
    public ?array $lastImportSummary = null;
    public bool $showBiometricoModal = false;
    public ?int $editingBiometricoId = null;
    public bool $showDeleteModal = false;
    public ?int $pendingDeleteImportacionId = null;
    public string $pendingDeleteImportacionNombre = '';

    public function mount(): void
    {
        $this->deviceDepartment = 'La Paz';
        $this->exportYear = now()->format('Y');
        $this->exportMonth = now()->format('m');
    }

    public function openBiometricoModal(): void
    {
        $this->resetBiometricoForm();
        $this->resetValidation();
        $this->showBiometricoModal = true;
    }

    public function openEditBiometricoModal(int $deviceId): void
    {
        $device = BiometricoDispositivo::query()->findOrFail($deviceId);

        $this->editingBiometricoId = $device->id;
        $this->deviceDepartment = $device->department;
        $this->deviceBranch = $device->branch;
        $this->deviceIp = $device->ip;
        $this->devicePort = (string) $device->port;
        $this->deviceConnectionMode = $device->connection_mode ?: 'TCP/IP';
        $this->deviceCommunicationPassword = $device->communication_password ?? '';
        $this->resetValidation();
        $this->showBiometricoModal = true;
    }

    public function openEditBiometricoModalByIndex(int $deviceIndex): void
    {
        $devices = app(ConexionBiometricoService::class)->dispositivosConfigurados();

        if (! isset($devices[$deviceIndex])) {
            throw new \RuntimeException('No se encontro el biometrico seleccionado para editar.');
        }

        $device = $devices[$deviceIndex];

        $this->editingBiometricoId = $device['id'] ?? null;
        $this->deviceDepartment = (string) ($device['department'] ?? 'La Paz');
        $this->deviceBranch = (string) ($device['branch'] ?? '');
        $this->deviceIp = (string) ($device['ip'] ?? '');
        $this->devicePort = (string) ($device['port'] ?? 4370);
        $this->deviceConnectionMode = (string) ($device['connection_mode'] ?? 'TCP/IP');
        $this->deviceCommunicationPassword = (string) ($device['communication_password'] ?? '');
        $this->resetValidation();
        $this->showBiometricoModal = true;
    }

    public function closeBiometricoModal(): void
    {
        $this->showBiometricoModal = false;
        $this->resetBiometricoForm();
        $this->resetValidation();
    }

    public function saveBiometrico(): void
    {
        $data = $this->validate([
            'deviceDepartment' => ['required', 'string', 'max:120'],
            'deviceBranch' => ['required', 'string', 'max:160'],
            'deviceIp' => ['required', 'ip', 'max:80', Rule::unique('biometrico_dispositivos', 'ip')->ignore($this->editingBiometricoId)],
            'devicePort' => ['required', 'integer', 'min:1', 'max:65535'],
            'deviceConnectionMode' => ['required', 'string', 'max:30'],
            'deviceCommunicationPassword' => ['nullable', 'string', 'max:80'],
        ], [
            'deviceDepartment.required' => 'Ingresa el departamento.',
            'deviceBranch.required' => 'Ingresa la sucursal o nombre del biometrico.',
            'deviceIp.required' => 'Ingresa la IP del biometrico.',
            'deviceIp.ip' => 'La IP del biometrico no tiene un formato valido.',
            'deviceIp.unique' => 'Ya existe un biometrico registrado con esa IP.',
            'devicePort.required' => 'Ingresa el puerto del biometrico.',
        ]);

        $payload = [
            'department' => trim($data['deviceDepartment']),
            'branch' => trim($data['deviceBranch']),
            'ip' => trim($data['deviceIp']),
            'port' => (int) $data['devicePort'],
            'connection_mode' => trim($data['deviceConnectionMode']),
            'communication_password' => trim((string) ($data['deviceCommunicationPassword'] ?? '')) ?: null,
            'is_active' => true,
        ];

        if ($this->editingBiometricoId) {
            $device = BiometricoDispositivo::query()->findOrFail($this->editingBiometricoId);
            $device->update($payload);
            $message = 'Biometrico actualizado correctamente.';
        } else {
            BiometricoDispositivo::query()->create($payload);
            $message = 'Biometrico registrado correctamente.';
        }

        $this->resetBiometricoForm();
        $this->resetValidation();
        $this->showBiometricoModal = false;

        session()->flash('status', $message);
    }

    public function deleteBiometrico(int $deviceId): void
    {
        $device = BiometricoDispositivo::query()->findOrFail($deviceId);
        $device->delete();

        session()->flash('status', 'Biometrico eliminado correctamente.');
    }

    public function probarConexion(int $deviceIndex): void
    {
        try {
            $devices = app(ConexionBiometricoService::class)->dispositivosConfigurados();

            if (! isset($devices[$deviceIndex])) {
                throw new \RuntimeException('No se encontro el biometrico seleccionado para probar la conexion.');
            }

            $device = $devices[$deviceIndex];
            $probe = app(ExtraccionBiometricoZkService::class)->probarSesion($device);

            session()->flash(
                'status',
                'Conexion ZKTeco verificada correctamente con '.$device['branch'].'. Transporte: '.strtoupper((string) ($probe['transport'] ?? 'tcp')).'.'
            );
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('status', 'Error al probar la conexion del biometrico: '.$exception->getMessage());
        }
    }

    public function extraerExcel(int $deviceIndex)
    {
        try {
            $devices = app(ConexionBiometricoService::class)->dispositivosConfigurados();

            if (! isset($devices[$deviceIndex])) {
                throw new \RuntimeException('No se encontro el biometrico seleccionado para exportar.');
            }

            $path = app(ExtraccionBiometricoZkService::class)->exportarExcel(
                $devices[$deviceIndex],
                (int) $this->exportYear,
                (int) $this->exportMonth
            );

            return response()->download($path, basename($path))->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('status', 'No se pudo extraer el Excel del biometrico: '.$exception->getMessage());

            return null;
        }
    }

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
            'connections' => array_values($analysis->estadoBiometricos()),
            'connectionModes' => $this->connectionModes(),
            'exportYearOptions' => $this->yearOptions(),
            'exportMonthOptions' => $this->monthOptions(),
            'summary' => $this->lastImportSummary,
        ])->layout('layouts.app', ['title' => 'Importacion biometrica']);
    }

    private function yearOptions(): array
    {
        $currentYear = (int) now()->format('Y');

        return array_reverse(range($currentYear - 5, $currentYear));
    }

    private function monthOptions(): array
    {
        return [
            ['value' => '01', 'label' => 'Enero'],
            ['value' => '02', 'label' => 'Febrero'],
            ['value' => '03', 'label' => 'Marzo'],
            ['value' => '04', 'label' => 'Abril'],
            ['value' => '05', 'label' => 'Mayo'],
            ['value' => '06', 'label' => 'Junio'],
            ['value' => '07', 'label' => 'Julio'],
            ['value' => '08', 'label' => 'Agosto'],
            ['value' => '09', 'label' => 'Septiembre'],
            ['value' => '10', 'label' => 'Octubre'],
            ['value' => '11', 'label' => 'Noviembre'],
            ['value' => '12', 'label' => 'Diciembre'],
        ];
    }

    private function connectionModes(): array
    {
        return ['TCP/IP', 'RS485', 'RS232'];
    }

    private function resetBiometricoForm(): void
    {
        $this->editingBiometricoId = null;
        $this->deviceDepartment = 'La Paz';
        $this->deviceBranch = '';
        $this->deviceIp = '';
        $this->devicePort = '4370';
        $this->deviceConnectionMode = 'TCP/IP';
        $this->deviceCommunicationPassword = '';
    }
}
