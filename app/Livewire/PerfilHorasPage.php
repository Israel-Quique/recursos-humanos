<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PerfilHorasPage extends Component
{
    use WithFileUploads;

    public Empleado $empleado;
    public string $referenceMonth = '';
    public string $filterState = 'todos';
    public string $searchQuery = '';
    public string $sortDirection = 'asc';
    public string $searchCarnet = '';

    // Estado del modal de boleta
    public bool $showBoletaModal = false;
    public string $boletaNombre = '';
    public string $boletaCi = '';
    public string $boletaCargo = '';
    public string $boletaMotivo = '';
    public string $boletaTipo = 'particular'; // 'comision', 'particular', 'medico'
    public string $boletaDesdeFecha = '';
    public string $boletaDesdeHora = '08:30';
    public string $boletaHastaFecha = '';
    public string $boletaHastaHora = '16:30';
    public string $boletaTiempoSolicitado = '1 HORA';
    public string $boletaCiudad = '';
    public string $boletaFechaTexto = '';
    public $comprobante = null;

    public function mount(Empleado $empleado): void
    {
        $this->empleado = $empleado;
        $this->referenceMonth = $this->resolveInitialMonth();
    }

    public function buscarOtroCarnet(): void
    {
        $carnet = trim($this->searchCarnet);
        if ($carnet === '') {
            return;
        }

        $targetEmpleado = Empleado::query()
            ->where('codigo_biometrico', $carnet)
            ->orWhere('id', $carnet)
            ->first();

        if (! $targetEmpleado) {
            $this->addError('searchCarnet', 'No encontramos un trabajador con el carnet o código "' . $carnet . '".');

            return;
        }

        $signedPath = URL::signedRoute('perfil-horas', ['empleado' => $targetEmpleado->id], absolute: false);
        $this->redirect($signedPath, navigate: true);
    }

    public function setFilterState(string $state): void
    {
        $this->filterState = $state;
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function render()
    {
        $monthOptions = $this->monthOptions();

        if (! collect($monthOptions)->pluck('value')->contains($this->referenceMonth)) {
            $this->referenceMonth = $monthOptions[0]['value'] ?? now()->format('Y-m');
        }

        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $analysis = app(AnalisisAsistenciaService::class);
        $personalReport = $analysis->reportePersonalizado($this->empleado->id, $rangeStart, $rangeEnd);

        $allRows = collect($personalReport['rows'] ?? []);

        // Filter by state
        if ($this->filterState === 'retrasos') {
            $allRows = $allRows->filter(fn ($r) => ($r['retraso_minutos'] ?? 0) > 0 || ($r['es_retraso'] ?? false) || str_contains(strtolower($r['estado'] ?? ''), 'tarde') || str_contains(strtolower($r['estado'] ?? ''), 'retraso'));
        } elseif ($this->filterState === 'omisiones') {
            $allRows = $allRows->filter(fn ($r) => ($r['es_omision'] ?? false) || ($r['row_tone'] ?? '') === 'warning' || str_contains(strtolower($r['estado'] ?? ''), 'incompleto') || str_contains(strtolower($r['estado'] ?? ''), 'omision') || $r['entrada'] === '--:--' || $r['salida'] === '--:--');
        } elseif ($this->filterState === 'faltas') {
            $allRows = $allRows->filter(fn ($r) => ($r['es_falta'] ?? false) || ($r['row_tone'] ?? '') === 'danger' || str_contains(strtolower($r['estado'] ?? ''), 'falta'));
        } elseif ($this->filterState === 'puntuales') {
            $allRows = $allRows->filter(fn ($r) => ($r['row_tone'] ?? '') === 'default' && ($r['retraso_minutos'] ?? 0) === 0 && $r['entrada'] !== '--:--' && $r['salida'] !== '--:--');
        }

        // Filter by search query
        if (filled(trim($this->searchQuery))) {
            $q = strtolower(trim($this->searchQuery));
            $allRows = $allRows->filter(function ($r) use ($q) {
                return str_contains(strtolower($r['fecha'] ?? ''), $q)
                    || str_contains(strtolower($r['dia_semana'] ?? ''), $q)
                    || str_contains(strtolower($r['estado'] ?? ''), $q)
                    || str_contains(strtolower($r['evento_biometrico'] ?? ''), $q)
                    || str_contains(strtolower($r['estado_biometrico'] ?? ''), $q);
            });
        }

        // Sort direction
        if ($this->sortDirection === 'desc') {
            $allRows = $allRows->reverse();
        }

        $filteredRows = $allRows->values()->all();

        $filteredCount = count($filteredRows);
        $filteredRetrasoMinutos = collect($filteredRows)->sum(fn ($r) => $r['retraso_minutos'] ?? 0);
        $filteredOmisionesCount = collect($filteredRows)->filter(fn ($r) => $r['es_omision'] ?? false)->count();
        $filteredFaltasCount = collect($filteredRows)->filter(fn ($r) => $r['es_falta'] ?? false)->count();

        return view('livewire.perfil-horas', [
            'monthOptions' => $monthOptions,
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $personalReport,
            'filteredRows' => $filteredRows,
            'filteredCount' => $filteredCount,
            'filteredRetrasoMinutos' => $filteredRetrasoMinutos,
            'filteredOmisionesCount' => $filteredOmisionesCount,
            'filteredFaltasCount' => $filteredFaltasCount,
        ])->layout('layouts.guest', ['title' => 'Perfil de horas - ' . $this->empleado->nombre_completo]);
    }

    private function monthOptions(): array
    {
        $months = RegistroAsistencia::query()
            ->where('empleado_id', $this->empleado->id)
            ->whereNotNull('fecha')
            ->orderByDesc('fecha')
            ->get(['fecha'])
            ->map(fn (RegistroAsistencia $registro) => $registro->fecha?->copy()->startOfMonth())
            ->filter()
            ->unique(fn (Carbon $month) => $month->format('Y-m'))
            ->values();

        if ($months->isEmpty()) {
            $fallbackMonth = now()->copy()->startOfMonth();

            return [[
                'value' => $fallbackMonth->format('Y-m'),
                'label' => ucfirst($fallbackMonth->locale('es')->translatedFormat('F Y')),
            ]];
        }

        return $months
            ->map(fn (Carbon $month) => [
                'value' => $month->format('Y-m'),
                'label' => ucfirst($month->locale('es')->translatedFormat('F Y')),
            ])
            ->all();
    }

    private function resolveInitialMonth(): string
    {
        return $this->monthOptions()[0]['value'] ?? now()->format('Y-m');
    }

    public function abrirBoletaModal(?string $fecha = null, ?string $horaInicio = null, ?string $horaFin = null, ?string $motivo = null, ?string $tipo = null): void
    {
        $this->boletaNombre = $this->empleado->nombre_completo;
        $this->boletaCi = (string) ($this->empleado->codigo_biometrico ?: $this->empleado->id);
        $this->boletaCargo = (string) ($this->empleado->cargo ?: ($this->empleado->area ? 'AREA DE ' . $this->empleado->area : 'PERSONAL'));
        $this->boletaCiudad = !empty($this->empleado->sucursal) ? mb_strtoupper($this->empleado->sucursal) : 'LA PAZ';

        $hoy = now();
        $fechaParsed = $fecha ? $this->parsearFechaCarbon($fecha) : $hoy;
        $this->boletaDesdeFecha = $fechaParsed->format('d/m/Y');
        $this->boletaHastaFecha = $fechaParsed->format('d/m/Y');
        $this->boletaDesdeHora = $horaInicio ?: '08:30';
        $this->boletaHastaHora = $horaFin ?: '16:30';
        $this->boletaMotivo = $motivo ?: '';
        $this->boletaTipo = $tipo ?: 'particular';
        $this->boletaFechaTexto = $hoy->locale('es')->translatedFormat('d \de F \de Y');

        $this->recalcularTiempoSolicitado();
        $this->comprobante = null;
        $this->showBoletaModal = true;
        $this->resetValidation();
    }

    public function abrirBoletaParaOmision(string $fecha, string $entrada, string $salida, ?string $horarioProgramado = null): void
    {
        $motivo = 'JUSTIFICACIÓN POR OMISIÓN DE MARCACIÓN';
        $horaInicio = '08:30';
        $horaFin = '16:30';

        if ($horarioProgramado && str_contains($horarioProgramado, '-')) {
            $partes = explode('-', $horarioProgramado);
            $horaInicio = trim($partes[0]);
            $horaFin = trim($partes[1]);
        }

        if ($entrada === '--:--' && $salida !== '--:--') {
            $motivo = 'JUSTIFICACIÓN POR OMISIÓN DE ENTRADA';
            $horaFin = $horaInicio;
        } elseif ($salida === '--:--' && $entrada !== '--:--') {
            $motivo = 'JUSTIFICACIÓN POR OMISIÓN DE SALIDA';
            $horaInicio = $horaFin;
        }

        $this->abrirBoletaModal($fecha, $horaInicio, $horaFin, $motivo, 'particular');
    }

    public function cerrarBoletaModal(): void
    {
        $this->showBoletaModal = false;
        $this->comprobante = null;
        $this->resetValidation();
    }

    public function quitarComprobante(): void
    {
        $this->comprobante = null;
        $this->resetValidation('comprobante');
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

            if ($hasta->greaterThanOrEqualTo($desde)) {
                $diffMin = $desde->diffInMinutes($hasta);
                if ($diffMin === 0) {
                    $this->boletaTiempoSolicitado = 'MARCACIÓN PUNTUAL';
                } elseif ($diffMin < 60) {
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
        }
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

    public function descargarPdf()
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
            'comprobante' => ['required', 'image', 'max:5120'],
        ], [
            'boletaMotivo.required' => 'Ingresa el motivo de la justificación o boleta.',
            'boletaTiempoSolicitado.required' => 'Indica el tiempo solicitado.',
            'comprobante.required' => 'Es obligatorio subir una foto o comprobante que justifique el motivo de la boleta.',
            'comprobante.image' => 'El comprobante debe ser un archivo de imagen válido (JPG, PNG o WEBP).',
            'comprobante.max' => 'La imagen del comprobante no puede pesar más de 5MB.',
        ]);

        $empleado = $this->empleado;
        $fechaInicio = $this->parsearFechaCarbon($this->boletaDesdeFecha)->toDateString();
        $fechaFin = $this->parsearFechaCarbon($this->boletaHastaFecha)->toDateString();

        $inicioCarbon = Carbon::parse($fechaInicio . ' ' . ($this->boletaDesdeHora ?: '00:00'));
        $finCarbon = Carbon::parse($fechaFin . ' ' . ($this->boletaHastaHora ?: '23:59'));
        $minutosContabilizados = max(0, $inicioCarbon->diffInMinutes($finCarbon));
        $alcance = ($fechaInicio === $fechaFin && $this->boletaDesdeHora && $this->boletaHastaHora) ? 'horas' : 'dias';

        // 1. Guardar PermisoLaboral
        $permiso = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => $alcance,
            'estado' => 'pendiente',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'hora_inicio' => filled($this->boletaDesdeHora) ? $this->boletaDesdeHora . ':00' : null,
            'hora_fin' => filled($this->boletaHastaHora) ? $this->boletaHastaHora . ':00' : null,
            'minutos_contabilizados' => $minutosContabilizados,
            'motivo' => mb_strtoupper($this->boletaTipo) . ': ' . $this->boletaMotivo,
            'created_by' => null,
        ]);

        // 2. Guardar comprobante
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

        // 3. Auditoría
        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'solicitar_boleta_empleado',
            'El funcionario generó una boleta de permiso/omisión con comprobante adjunto.',
            $permiso,
            null,
            [
                'empleado_id' => $empleado->id,
                'empleado' => $empleado->nombre_completo,
                'ci' => $empleado->codigo_biometrico ?: (string) $empleado->id,
                'comprobante' => $comprobanteRegistro->nombre_original,
                'ruta' => $rutaArchivo,
            ]
        );

        $ciudad = !empty($empleado->sucursal) ? mb_strtoupper($empleado->sucursal) : 'LA PAZ';
        $boleta = [
            'nombre' => $empleado->nombre_completo,
            'ci' => (string) ($empleado->codigo_biometrico ?: $empleado->id),
            'cargo' => (string) ($empleado->cargo ?: ($empleado->area ? 'AREA DE ' . $empleado->area : 'PERSONAL')),
            'motivo' => $this->boletaMotivo,
            'tipo' => $this->boletaTipo,
            'desde_fecha' => $this->boletaDesdeFecha,
            'desde_hora' => $this->boletaDesdeHora,
            'hasta_fecha' => $this->boletaHastaFecha,
            'hasta_hora' => $this->boletaHastaHora,
            'tiempo_solicitado' => $this->boletaTiempoSolicitado,
            'ciudad' => $ciudad,
            'fecha_texto' => $this->boletaFechaTexto,
            'lugar_fecha' => mb_strtoupper($ciudad) . ', ' . mb_strtoupper($this->boletaFechaTexto),
        ];

        // 4. Generar PDF oficial (1 sola boleta para impresión limpia)
        $pdf = Pdf::loadView('pdf.boleta-permiso', [
            'boleta' => $boleta,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Boleta_' . Str::slug($boleta['nombre']) . '_' . now()->format('Ymd_His') . '.pdf';

        $this->showBoletaModal = false;
        $this->comprobante = null;
        session()->flash('status', 'Boleta y comprobante enviados correctamente a Recursos Humanos.');

        return response()->streamDownload(fn () => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
