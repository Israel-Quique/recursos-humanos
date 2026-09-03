<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Services\AuditoriaService;
use App\Services\BoletaExcelService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConsultaCarnetPage extends Component
{
    use WithFileUploads;

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

    // Archivo de comprobante (foto obligatoria)
    public $comprobante = null;

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

    public function quitarComprobante(): void
    {
        $this->comprobante = null;
        $this->resetValidation('comprobante');
    }

    private function parsearFechaCarbon(string $fecha): Carbon
    {
        $fecha = trim($fecha);
        try {
            return Carbon::createFromFormat('d/m/Y', $fecha);
        } catch (\Throwable) {
            return Carbon::parse($fecha);
        }
    }

    public function payloadBoleta(bool $requiereComprobante = true): array
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
            'comprobante' => $requiereComprobante ? ['required', 'image', 'max:5120'] : ['nullable'],
        ], [
            'boletaNombre.required' => 'Ingresa el nombre del funcionario.',
            'boletaCi.required' => 'Ingresa el carnet del funcionario.',
            'boletaMotivo.required' => 'Ingresa el motivo de la comisión o permiso.',
            'boletaTiempoSolicitado.required' => 'Indica el tiempo solicitado.',
            'comprobante.required' => 'Es obligatorio subir una foto o comprobante que justifique el motivo de la boleta.',
            'comprobante.image' => 'El comprobante debe ser un archivo de imagen válido (JPG, PNG o WEBP).',
            'comprobante.max' => 'La imagen del comprobante no puede pesar más de 5MB.',
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
        $empleado = Empleado::query()->findOrFail((int) $this->empleadoId);

        $fechaInicio = $this->parsearFechaCarbon($this->boletaDesdeFecha)->toDateString();
        $fechaFin = $this->parsearFechaCarbon($this->boletaHastaFecha)->toDateString();

        $inicioCarbon = Carbon::parse($fechaInicio . ' ' . ($this->boletaDesdeHora ?: '00:00'));
        $finCarbon = Carbon::parse($fechaFin . ' ' . ($this->boletaHastaHora ?: '23:59'));
        $minutosContabilizados = max(0, $inicioCarbon->diffInMinutes($finCarbon));

        $alcance = ($fechaInicio === $fechaFin && $this->boletaDesdeHora && $this->boletaHastaHora) ? 'horas' : 'dias';

        // 1. Guardar la solicitud como PermisoLaboral en la base de datos
        $permiso = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => $alcance,
            'estado' => 'pendiente', // Pendiente de revisión por RRHH
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'hora_inicio' => filled($this->boletaDesdeHora) ? $this->boletaDesdeHora . ':00' : null,
            'hora_fin' => filled($this->boletaHastaHora) ? $this->boletaHastaHora . ':00' : null,
            'minutos_contabilizados' => $minutosContabilizados,
            'motivo' => mb_strtoupper($this->boletaTipo) . ': ' . $this->boletaMotivo,
            'created_by' => null, // Solicitado directamente por el empleado
        ]);

        // 2. Almacenar la foto del comprobante en disco seguro
        $extension = $this->comprobante->getClientOriginalExtension() ?: 'jpg';
        $nombreOriginal = $this->comprobante->getClientOriginalName();
        $rutaArchivo = $this->comprobante->storeAs(
            'comprobantes',
            'comprobante_' . $permiso->id . '_' . time() . '.' . $extension,
            'public'
        );

        $mimeType = $this->comprobante->getMimeType() ?: 'image/' . $extension;
        $tamanoBytes = $this->comprobante->getSize();
        $realPath = $this->comprobante->getRealPath();
        $contenidoBinario = $realPath && file_exists($realPath) ? file_get_contents($realPath) : null;
        $contenidoBase64 = $contenidoBinario ? base64_encode($contenidoBinario) : null;

        $comprobanteRegistro = PermisoComprobante::query()->create([
            'permiso_laboral_id' => $permiso->id,
            'ruta_archivo' => $rutaArchivo,
            'archivo_binario' => null,
            'archivo_base64' => $contenidoBase64,
            'nombre_original' => $nombreOriginal,
            'mime_type' => $mimeType,
            'tamano_bytes' => $tamanoBytes,
            'created_by' => null,
        ]);

        // 3. Registrar en Auditoría
        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'solicitar_boleta_empleado',
            'El funcionario envió una solicitud de boleta con comprobante adjunto desde el portal.',
            $permiso,
            null,
            [
                'empleado_id' => $empleado->id,
                'empleado' => $empleado->nombre_completo,
                'ci' => $empleado->codigo_biometrico ?: $this->carnet,
                'comprobante' => $comprobanteRegistro->nombre_original,
                'ruta' => $rutaArchivo,
            ]
        );

        // 4. Generar Boleta Oficial en PDF (1 sola boleta para impresión limpia)
        $pdf = Pdf::loadView('pdf.boleta-permiso', [
            'boleta' => $boleta,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Boleta_' . Str::slug($boleta['nombre']) . '_' . now()->format('Ymd_His') . '.pdf';

        // 6. Cerrar modal y limpiar
        $this->showBoletaModal = false;
        $this->comprobante = null;
        session()->flash('status', 'Boleta y comprobante enviados correctamente a Recursos Humanos.');

        return response()->streamDownload(fn () => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function descargarExcel()
    {
        $boleta = $this->payloadBoleta(false);

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
