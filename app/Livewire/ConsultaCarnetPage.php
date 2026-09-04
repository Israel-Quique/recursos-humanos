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
    public bool $showPedirEmailModal = false;
    public bool $tieneEmailRegistrado = false;
    public ?int $empleadoId = null;
    public string $boletaNombre = '';
    public string $boletaCi = '';
    public string $boletaCargo = '';
    public string $boletaEmail = '';
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

    // Empleado encontrado para visualización de datos iniciales en la consulta
    public ?Empleado $empleadoEncontrado = null;

    public function updatedCarnet($value): void
    {
        $carnet = trim((string) $value);
        if (strlen($carnet) >= 3) {
            $this->empleadoEncontrado = Empleado::query()
                ->where('codigo_biometrico', $carnet)
                ->first();
        } else {
            $this->empleadoEncontrado = null;
        }
    }

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

        $this->empleadoEncontrado = $empleado;

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
        $this->boletaEmail = (string) ($empleado->email ?: '');
        $this->tieneEmailRegistrado = filled($empleado->email);
        $this->showPedirEmailModal = false;
        $this->boletaMotivo = '';
        $this->boletaTipo = 'comision';

        $hoy = now();
        $this->boletaDesdeFecha = $hoy->format('Y-m-d');
        $this->boletaHastaFecha = $hoy->format('Y-m-d');
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
        $this->showPedirEmailModal = false;
        $this->resetValidation();
    }

    public function getEsRangoDiasProperty(): bool
    {
        try {
            $desde = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hasta = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            return $hasta->greaterThan($desde);
        } catch (\Throwable) {
            return false;
        }
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
            $desdeFecha = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hastaFecha = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            // Si la fecha hasta es anterior a la fecha desde, ajustar fecha hasta
            if ($hastaFecha->lessThan($desdeFecha)) {
                $hastaFecha = $desdeFecha->copy();
                $this->boletaHastaFecha = $this->boletaDesdeFecha;
            }

            // Si es más de un día (rango de días):
            if ($hastaFecha->greaterThan($desdeFecha)) {
                $dias = (int) $desdeFecha->diffInDays($hastaFecha) + 1;
                $this->boletaTiempoSolicitado = $dias === 1 ? '1 DÍA' : "{$dias} DÍAS";
                $this->boletaDesdeHora = '';
                $this->boletaHastaHora = '';

                return;
            }

            // Si es en el mismo día (solicitud por horas):
            if (blank($this->boletaDesdeHora)) {
                $this->boletaDesdeHora = '08:30';
            }
            if (blank($this->boletaHastaHora)) {
                $this->boletaHastaHora = '09:00';
            }

            $desde = Carbon::createFromFormat('Y-m-d H:i', $desdeFecha->format('Y-m-d') . ' ' . trim($this->boletaDesdeHora));
            $hasta = Carbon::createFromFormat('Y-m-d H:i', $hastaFecha->format('Y-m-d') . ' ' . trim($this->boletaHastaHora));

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
            } else {
                $this->boletaTiempoSolicitado = '0 MIN';
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
            return Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Throwable) {
            try {
                return Carbon::createFromFormat('d/m/Y', $fecha);
            } catch (\Throwable) {
                return Carbon::parse($fecha);
            }
        }
    }

    public function payloadBoleta(bool $requiereComprobante = true): array
    {
        $esRangoDias = $this->esRangoDias;

        $this->validate([
            'boletaNombre' => ['required', 'string', 'max:150'],
            'boletaCi' => ['required', 'string', 'max:50'],
            'boletaCargo' => ['nullable', 'string', 'max:150'],
            'boletaMotivo' => ['required', 'string', 'max:255'],
            'boletaTipo' => ['required', 'in:comision,particular,medico'],
            'boletaDesdeFecha' => ['required', 'string', 'max:20'],
            'boletaDesdeHora' => $esRangoDias ? ['nullable', 'string', 'max:10'] : ['required', 'string', 'max:10'],
            'boletaHastaFecha' => ['required', 'string', 'max:20'],
            'boletaHastaHora' => $esRangoDias ? ['nullable', 'string', 'max:10'] : ['required', 'string', 'max:10'],
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

        $desdeCarbon = $this->parsearFechaCarbon($this->boletaDesdeFecha);
        $hastaCarbon = $this->parsearFechaCarbon($this->boletaHastaFecha);

        return [
            'nombre' => $this->boletaNombre,
            'ci' => $this->boletaCi,
            'cargo' => $this->boletaCargo,
            'motivo' => $this->boletaMotivo,
            'tipo' => $this->boletaTipo,
            'desde_fecha' => $desdeCarbon->format('d/m/Y'),
            'desde_hora' => $esRangoDias ? '--:--' : $this->boletaDesdeHora,
            'hasta_fecha' => $hastaCarbon->format('d/m/Y'),
            'hasta_hora' => $esRangoDias ? '--:--' : $this->boletaHastaHora,
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

        // Si el empleado no tiene correo registrado en la base de datos:
        if (blank($empleado->email)) {
            // Si tampoco lo ingresó en el popup emergente:
            if (blank(trim($this->boletaEmail))) {
                $this->showPedirEmailModal = true;

                return;
            }

            // Validar y registrar por primera y única vez
            $this->validate([
                'boletaEmail' => ['required', 'email', 'max:191'],
            ], [
                'boletaEmail.required' => 'Ingresa el correo electrónico donde recibirás el estado de tu boleta.',
                'boletaEmail.email' => 'Ingresa un correo válido (ej. tu.nombre@correos.gob.bo o correo@gmail.com).',
            ]);

            $empleado->email = strtolower(trim($this->boletaEmail));
            $empleado->save();
            $this->tieneEmailRegistrado = true;
            if ($this->empleadoEncontrado && $this->empleadoEncontrado->id === $empleado->id) {
                $this->empleadoEncontrado->email = $empleado->email;
            }
        }
        // Nota de seguridad: Si el empleado YA tiene correo registrado, NO se permite modificarlo
        // desde la boleta para evitar peticiones y cambios continuos. Solo el administrador puede modificarlo.

        return $this->procesarEnvioBoleta($boleta, $empleado);
    }

    public function confirmarEmailYDescargar()
    {
        $this->validate([
            'boletaEmail' => ['required', 'email', 'max:191'],
        ], [
            'boletaEmail.required' => 'Ingresa el correo electrónico donde recibirás el estado de tu boleta.',
            'boletaEmail.email' => 'Ingresa un correo válido (ej. tu.nombre@correos.gob.bo o correo@gmail.com).',
        ]);

        $empleado = Empleado::query()->findOrFail((int) $this->empleadoId);

        // Solo se guarda si el empleado aún no tenía correo registrado
        if (blank($empleado->email)) {
            $empleado->email = strtolower(trim($this->boletaEmail));
            $empleado->save();
            if ($this->empleadoEncontrado && $this->empleadoEncontrado->id === $empleado->id) {
                $this->empleadoEncontrado->email = $empleado->email;
            }
        }

        $this->tieneEmailRegistrado = true;
        $this->showPedirEmailModal = false;

        $boleta = $this->payloadBoleta();

        return $this->procesarEnvioBoleta($boleta, $empleado);
    }

    public function cerrarPedirEmailModal(): void
    {
        $this->showPedirEmailModal = false;
        $this->resetValidation('boletaEmail');
    }

    private function procesarEnvioBoleta(array $boleta, Empleado $empleado)
    {
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
                'email' => $empleado->email,
                'comprobante' => $comprobanteRegistro->nombre_original,
                'ruta' => $rutaArchivo,
            ]
        );

        // 4. Generar Boleta Oficial en PDF (1 sola boleta para impresión limpia)
        $pdf = Pdf::loadView('pdf.boleta-permiso', [
            'boleta' => $boleta,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Boleta_' . Str::slug($boleta['nombre']) . '_' . now()->format('Ymd_His') . '.pdf';

        // 5. Cerrar modal y limpiar
        $this->showBoletaModal = false;
        $this->showPedirEmailModal = false;
        $this->comprobante = null;

        $notificacionMsg = $empleado->email
            ? "Boleta y comprobante enviados a Recursos Humanos. Te llegará la notificación a: {$empleado->email}."
            : 'Boleta y comprobante enviados correctamente a Recursos Humanos.';

        session()->flash('status', $notificacionMsg);

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
