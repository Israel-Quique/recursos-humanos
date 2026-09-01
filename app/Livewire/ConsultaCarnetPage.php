<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Services\BoletaExcelService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConsultaCarnetPage extends Component
{
    public string $carnet = '';

    // Estado del modal de boleta
    public bool $showBoletaModal = false;
    public ?int $empleadoId = null;
    public string $boletaNombre = '';
    public string $boletaCi = '';
    public string $boletaCargo = '';
    public string $boletaMotivo = '';
    public string $boletaTipo = 'comision'; // 'comision', 'particular', 'medico'
    public string $boletaDesdeFecha = '';
    public string $boletaDesdeHora = '08:30';
    public string $boletaHastaFecha = '';
    public string $boletaHastaHora = '09:00';
    public string $boletaTiempoSolicitado = '30 MIN';
    public string $boletaCiudad = 'LA PAZ';
    public string $boletaFechaTexto = '';

    public function buscar(): void
    {
        $data = $this->validate([
            'carnet' => ['required', 'string', 'max:50'],
        ], [
            'carnet.required' => 'Ingresa tu carnet o codigo.',
        ]);

        $carnet = trim($data['carnet']);

        $empleado = Empleado::query()
            ->where('codigo_biometrico', $carnet)
            ->first();

        if (! $empleado) {
            $this->addError('carnet', 'No encontramos un trabajador con ese carnet o codigo registrado.');

            return;
        }

        $signedPath = URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false);

        $this->redirect($signedPath, navigate: true);
    }

    public function abrirBoletaModal(): void
    {
        $data = $this->validate([
            'carnet' => ['required', 'string', 'max:50'],
        ], [
            'carnet.required' => 'Ingresa tu carnet o codigo para generar la boleta.',
        ]);

        $carnet = trim($data['carnet']);

        $empleado = Empleado::query()
            ->where('codigo_biometrico', $carnet)
            ->first();

        if (! $empleado) {
            $this->addError('carnet', 'No encontramos un trabajador con ese carnet o codigo registrado.');

            return;
        }

        $this->empleadoId = $empleado->id;
        $this->boletaNombre = $empleado->nombre_completo;
        $this->boletaCi = (string) ($empleado->codigo_biometrico ?: $carnet);
        $this->boletaCargo = (string) ($empleado->cargo ?: ($empleado->area ? 'AREA DE ' . $empleado->area : 'PERSONAL'));
        $this->boletaMotivo = '';
        $this->boletaTipo = 'comision';

        $hoy = now();
        $this->boletaDesdeFecha = $hoy->format('d/m/Y');
        $this->boletaHastaFecha = $hoy->format('d/m/Y');
        $this->boletaDesdeHora = '08:30';
        $this->boletaHastaHora = '09:00';
        $this->boletaCiudad = !empty($empleado->sucursal) ? mb_strtoupper($empleado->sucursal) : 'LA PAZ';
        $this->boletaFechaTexto = $hoy->locale('es')->translatedFormat('d \de F \de Y');

        $this->recalcularTiempoSolicitado();

        $this->showBoletaModal = true;
        $this->resetValidation();
    }

    public function cerrarBoletaModal(): void
    {
        $this->showBoletaModal = false;
        $this->resetValidation();
    }

    public function updatedBoletaDesdeHora(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaHastaHora(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaDesdeFecha(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaHastaFecha(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    private function recalcularTiempoSolicitado(): void
    {
        try {
            $desde = Carbon::createFromFormat('d/m/Y H:i', trim($this->boletaDesdeFecha) . ' ' . trim($this->boletaDesdeHora));
            $hasta = Carbon::createFromFormat('d/m/Y H:i', trim($this->boletaHastaFecha) . ' ' . trim($this->boletaHastaHora));

            if ($hasta->greaterThan($desde)) {
                $diffMin = $desde->diffInMinutes($hasta);
                if ($diffMin < 60) {
                    $this->boletaTiempoSolicitado = $diffMin . ' MIN';
                } elseif ($diffMin % 60 === 0) {
                    $horas = intdiv($diffMin, 60);
                    $this->boletaTiempoSolicitado = $horas === 1 ? '1 HORA' : $horas . ' HORAS';
                } else {
                    $horas = intdiv($diffMin, 60);
                    $min = $diffMin % 60;
                    $this->boletaTiempoSolicitado = "{$horas} H {$min} MIN";
                }
            }
        } catch (\Throwable) {
            // Mantener el valor actual si las fechas no se pueden parsear aún
        }
    }

    public function payloadBoleta(): array
    {
        $this->validate([
            'boletaNombre' => ['required', 'string', 'max:150'],
            'boletaCi' => ['required', 'string', 'max:50'],
            'boletaCargo' => ['nullable', 'string', 'max:150'],
            'boletaMotivo' => ['required', 'string', 'max:255'],
            'boletaTipo' => ['required', 'in:comision,particular,medico'],
            'boletaDesdeFecha' => ['required', 'string', 'max:20'],
            'boletaDesdeHora' => ['required', 'string', 'max:10'],
            'boletaHastaFecha' => ['required', 'string', 'max:20'],
            'boletaHastaHora' => ['required', 'string', 'max:10'],
            'boletaTiempoSolicitado' => ['required', 'string', 'max:50'],
            'boletaCiudad' => ['required', 'string', 'max:60'],
            'boletaFechaTexto' => ['required', 'string', 'max:80'],
        ], [
            'boletaNombre.required' => 'Ingresa el nombre del funcionario.',
            'boletaCi.required' => 'Ingresa el carnet del funcionario.',
            'boletaMotivo.required' => 'Ingresa el motivo de la comisión o permiso.',
            'boletaTiempoSolicitado.required' => 'Indica el tiempo solicitado.',
        ]);

        return [
            'nombre' => $this->boletaNombre,
            'ci' => $this->boletaCi,
            'cargo' => $this->boletaCargo,
            'motivo' => $this->boletaMotivo,
            'tipo' => $this->boletaTipo,
            'desde_fecha' => $this->boletaDesdeFecha,
            'desde_hora' => $this->boletaDesdeHora,
            'hasta_fecha' => $this->boletaHastaFecha,
            'hasta_hora' => $this->boletaHastaHora,
            'tiempo_solicitado' => $this->boletaTiempoSolicitado,
            'ciudad' => $this->boletaCiudad,
            'fecha_texto' => $this->boletaFechaTexto,
            'lugar_fecha' => mb_strtoupper($this->boletaCiudad) . ', ' . mb_strtoupper($this->boletaFechaTexto),
        ];
    }

    public function descargarPdf()
    {
        $boleta = $this->payloadBoleta();

        $pdf = Pdf::loadView('pdf.boleta-permiso', [
            'boleta' => $boleta,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Boleta_' . Str::slug($boleta['nombre']) . '_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function descargarExcel()
    {
        $boleta = $this->payloadBoleta();

        $excelService = app(BoletaExcelService::class);
        $spreadsheet = $excelService->generarSpreadsheet($boleta);
        $writer = new Xlsx($spreadsheet);

        $fileName = 'Boleta_' . Str::slug($boleta['nombre']) . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function render()
    {
        return view('livewire.consulta-carnet')
            ->layout('layouts.guest', ['title' => 'Consulta por carnet y boletas']);
    }
}
