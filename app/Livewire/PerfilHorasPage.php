<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\ReglaSancion;
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

    // Modal de anuncios y normativa de faltas (Artículo 45)
    public bool $mostrarModalNormativa = false;
    public string $categoriaNormativa = 'todas';

    // Estado del modal de boleta
    public bool $showBoletaModal = false;
    public bool $showPedirEmailModal = false;
    public bool $tieneEmailRegistrado = false;
    public string $boletaNombre = '';
    public string $boletaCi = '';
    public string $boletaCargo = '';
    public string $boletaEmail = '';
    public string $boletaMotivo = '';
    public string $boletaTipo = 'particular'; // 'comision', 'particular', 'medico', 'omision', 'retraso'
    public string $boletaModalidad = 'horas'; // 'horas' o 'dias'
    public string $boletaDesdeFecha = '';
    public string $boletaDesdeHora = '08:30';
    public string $boletaHastaFecha = '';
    public string $boletaHastaHora = '16:30';
    public string $boletaTiempoSolicitado = '1 HORA';
    public string $boletaCiudad = '';
    public string $boletaFechaTexto = '';

    public function toggleModalNormativa(): void
    {
        $this->mostrarModalNormativa = ! $this->mostrarModalNormativa;
    }

    public function abrirModalNormativa(): void
    {
        $this->mostrarModalNormativa = true;
    }

    public function cerrarModalNormativa(): void
    {
        $this->mostrarModalNormativa = false;
    }

    public function setCategoriaNormativa(string $categoria): void
    {
        $this->categoriaNormativa = $categoria;
    }

    public function getAnunciosReglamentoProperty(): array
    {
        $atrasoActivo = ReglaSancion::where('categoria', 'atraso')->where('activo', true)->exists();
        $inasistenciaActiva = ReglaSancion::where('categoria', 'inasistencia')->where('activo', true)->exists();
        $omisionActiva = ReglaSancion::where('categoria', 'omision')->where('activo', true)->exists();
        $gravisimaActiva = ReglaSancion::where('categoria', 'gravisima')->where('activo', true)->exists();

        return [
            'atraso' => [
                'clave' => 'atraso',
                'punto' => 'Punto I',
                'titulo' => 'Atrasos en los Horarios de Ingreso',
                'articulo' => 'Artículo 45, Numeral I',
                'icono' => '⏰',
                'activo' => $atrasoActivo,
                'descripcion' => 'Régimen de puntualidad y margen de tolerancia mensual institucional por sucursal.',
                'items' => [
                    [
                        'subtitulo' => 'Tolerancia Institucional',
                        'condicion' => 'Dentro del margen mensual establecido (30 a 35 min según sucursal)',
                        'efecto' => 'Sin consecuencias disciplinarias. Margen reservado para imprevistos menores.',
                        'badge' => 'Tolerancia permitida',
                        'badge_color' => 'emerald',
                    ],
                    [
                        'subtitulo' => 'Exceso Leve de Tolerancia (31 a 60 min)',
                        'condicion' => 'Acumulación de 31 a 60 minutos de atraso en el mes',
                        'efecto' => 'Consecuencias administrativas y sanción disciplinaria oficial según el Artículo 45, Numeral I.',
                        'badge' => 'Infracción Leve',
                        'badge_color' => 'amber',
                    ],
                    [
                        'subtitulo' => 'Exceso Moderado de Tolerancia (61 a 90 min)',
                        'condicion' => 'Acumulación de 61 a 90 minutos de atraso en el mes',
                        'efecto' => 'Sanción disciplinaria formal e informe a la Dirección Administrativa Financiera.',
                        'badge' => 'Infracción Moderada',
                        'badge_color' => 'orange',
                    ],
                    [
                        'subtitulo' => 'Exceso Grave de Tolerancia (91 a 120 min)',
                        'condicion' => 'Acumulación de 91 a 120 minutos de atraso en el mes',
                        'efecto' => 'Sanción disciplinaria agravada y emisión de Memorándum oficial por la DAF.',
                        'badge' => 'Infracción Severa',
                        'badge_color' => 'rose',
                    ],
                    [
                        'subtitulo' => 'Atraso Mayor a 120 min / Reincidencias',
                        'condicion' => 'Más de 120 minutos acumulados o reincidencia en la gestión',
                        'efecto' => 'Memorándum de severa llamada de atención. En caso de tercera reincidencia, remisión a proceso interno de destitución.',
                        'badge' => 'Régimen Especial',
                        'badge_color' => 'purple',
                    ],
                ],
            ],
            'inasistencia' => [
                'clave' => 'inasistencia',
                'punto' => 'Punto II',
                'titulo' => 'Inasistencias y Ausencias en el Puesto de Trabajo',
                'articulo' => 'Artículo 45, Numeral II',
                'icono' => '🚫',
                'activo' => $inasistenciaActiva,
                'descripcion' => 'Obligación de permanencia en el puesto laboral y justificación oportuna.',
                'items' => [
                    [
                        'subtitulo' => '1er Día de Inasistencia Injustificada',
                        'condicion' => 'Un (1) día laboral de ausencia sin boleta autorizada',
                        'efecto' => 'Sanción disciplinaria y reporte formal a la Dirección Administrativa Financiera (Art. 45 - II).',
                        'badge' => 'Falta Directa',
                        'badge_color' => 'rose',
                    ],
                    [
                        'subtitulo' => '2do Día Consecutivo Injustificado',
                        'condicion' => 'Dos (2) días continuos de ausencia sin justificación',
                        'efecto' => 'Sanción administrativa agravada con registro en el expediente del funcionario.',
                        'badge' => 'Falta Agravada',
                        'badge_color' => 'rose',
                    ],
                    [
                        'subtitulo' => '3er Día Consecutivo Injustificado',
                        'condicion' => 'Tres (3) días continuos de inasistencia no regularizada',
                        'efecto' => 'Severa sanción disciplinaria y último apercibimiento antes de destitución.',
                        'badge' => 'Severa Advertencia',
                        'badge_color' => 'rose',
                    ],
                    [
                        'subtitulo' => 'Abandono de Funciones (>3 días continuos o >6 discontinuos)',
                        'condicion' => 'Más de 3 días continuos o más de 6 discontinuos en el mes',
                        'efecto' => 'Destitución del cargo conforme a la Ley General del Trabajo y Reglamento Interno de la institución.',
                        'badge' => 'Destitución',
                        'badge_color' => 'red',
                    ],
                ],
            ],
            'omision' => [
                'clave' => 'omision',
                'punto' => 'Punto III',
                'titulo' => 'Omisión en el Registro de Asistencia',
                'articulo' => 'Artículo 45, Numeral III',
                'icono' => '📝',
                'activo' => $omisionActiva,
                'descripcion' => 'Obligatoriedad del marcado biométrico y regularización dentro de plazo fatal.',
                'items' => [
                    [
                        'subtitulo' => '1ra y 2da Omisión de Registro',
                        'condicion' => 'Olvido de marcado de entrada o salida en el biométrico',
                        'efecto' => 'Tolerancia regularizable. Debe presentarse la papeleta/boleta oficial con respaldo.',
                        'badge' => 'Regularizable',
                        'badge_color' => 'blue',
                    ],
                    [
                        'subtitulo' => 'A partir de la 3ra Omisión en el Mes',
                        'condicion' => 'Tres o más omisiones no justificadas en la misma mensualidad',
                        'efecto' => 'Consecuencias y sanciones disciplinarias por cada omisión subsiguiente conforme al Artículo 45.',
                        'badge' => 'Sancionable',
                        'badge_color' => 'amber',
                    ],
                    [
                        'subtitulo' => '⏱️ Plazo Improrrogable de 48 Horas',
                        'condicion' => 'Límite máximo para registrar boletas de retraso u omisión',
                        'efecto' => 'Toda justificación por olvido de marcado o retraso debe tramitarse dentro de las 48 horas de la falta. Vencido el plazo, el sistema bloquea el trámite y se consolida la sanción.',
                        'badge' => 'Regla de 48h',
                        'badge_color' => 'purple',
                    ],
                ],
            ],
            'gravisima' => [
                'clave' => 'gravisima',
                'punto' => 'Punto IV',
                'titulo' => 'Faltas Gravísimas y Régimen Disciplinario',
                'articulo' => 'Artículo 45, Numeral IV',
                'icono' => '⚖️',
                'activo' => $gravisimaActiva,
                'descripcion' => 'Reincidencias graves y conductas sujetas a proceso administrativo.',
                'items' => [
                    [
                        'subtitulo' => 'Reincidencia Reiterada en Infracciones',
                        'condicion' => 'Tercera reincidencia en atrasos mayores a 120 min o faltas graves',
                        'efecto' => 'Remisión del funcionario a proceso administrativo interno con sanción de destitución.',
                        'badge' => 'Proceso Administrativo',
                        'badge_color' => 'red',
                    ],
                ],
            ],
        ];
    }

    public function getEsBoletaOmisionORetrasoProperty(): bool
    {
        if (in_array($this->boletaTipo, ['omision', 'retraso'])) {
            return true;
        }

        if (in_array($this->boletaTipo, ['comision', 'medico'])) {
            return false;
        }

        $motivo = trim($this->boletaMotivo);
        if ($motivo === '') {
            return false;
        }

        return preg_match('/\b(omisi[oó]n|olvido|retraso|atraso|tardanza|falta de marcado)\b/ui', $motivo) === 1
            || preg_match('/no\s+marc[oó]/ui', $motivo) === 1;
    }

    public function getPlazo48HorasInfoProperty(): array
    {
        if (! $this->esBoletaOmisionORetraso) {
            return [
                'aplica' => false,
                'vencido' => false,
                'es_futuro' => false,
                'horas_transcurridas' => 0,
                'horas_restantes' => 48,
                'mensaje' => null,
            ];
        }

        if (blank($this->boletaDesdeFecha)) {
            return [
                'aplica' => true,
                'vencido' => false,
                'es_futuro' => false,
                'horas_transcurridas' => 0,
                'horas_restantes' => 48,
                'mensaje' => null,
            ];
        }

        try {
            $hora = filled($this->boletaDesdeHora) && $this->boletaDesdeHora !== '--:--' ? trim($this->boletaDesdeHora) : '08:30';
            $fechaIncidencia = Carbon::parse($this->parsearFechaCarbon($this->boletaDesdeFecha)->format('Y-m-d') . ' ' . $hora);
            $ahora = now();

            if ($fechaIncidencia->isFuture()) {
                return [
                    'aplica' => true,
                    'vencido' => true,
                    'es_futuro' => true,
                    'horas_transcurridas' => 0,
                    'horas_restantes' => 0,
                    'mensaje' => 'La boleta por omisión o retraso no puede emitirse para una fecha u hora futura.',
                ];
            }

            $horasTranscurridas = (int) $fechaIncidencia->diffInHours($ahora);

            if ($horasTranscurridas > 48) {
                return [
                    'aplica' => true,
                    'vencido' => true,
                    'es_futuro' => false,
                    'horas_transcurridas' => $horasTranscurridas,
                    'horas_restantes' => 0,
                    'mensaje' => "El plazo máximo de 48 horas para presentar la boleta por omisión o retraso ha vencido (Han transcurrido {$horasTranscurridas} horas desde la falta).",
                ];
            }

            $horasRestantes = max(0, 48 - $horasTranscurridas);

            return [
                'aplica' => true,
                'vencido' => false,
                'es_futuro' => false,
                'horas_transcurridas' => $horasTranscurridas,
                'horas_restantes' => $horasRestantes,
                'mensaje' => "Dentro del plazo permitido: te quedan {$horasRestantes} horas de las 48 horas reglamentarias para presentar esta boleta.",
            ];
        } catch (\Throwable) {
            return [
                'aplica' => true,
                'vencido' => false,
                'es_futuro' => false,
                'horas_transcurridas' => 0,
                'horas_restantes' => 48,
                'mensaje' => null,
            ];
        }
    }

    public function getSolicitudesRecientesProperty()
    {
        return PermisoLaboral::query()
            ->with(['comprobantePrincipal'])
            ->where('empleado_id', $this->empleado->id)
            ->latest('id')
            ->take(8)
            ->get();
    }
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
        $this->boletaEmail = (string) ($this->empleado->email ?: '');
        $this->tieneEmailRegistrado = filled($this->empleado->email);
        $this->showPedirEmailModal = false;
        $this->boletaCiudad = !empty($this->empleado->sucursal) ? mb_strtoupper($this->empleado->sucursal) : 'LA PAZ';

        $hoy = now();
        $fechaParsed = $fecha ? $this->parsearFechaCarbon($fecha) : $hoy;
        $this->boletaDesdeFecha = $fechaParsed->format('Y-m-d');
        $this->boletaHastaFecha = $fechaParsed->format('Y-m-d');
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
        $this->showPedirEmailModal = false;
        $this->comprobante = null;
        $this->resetValidation();
    }

    public function quitarComprobante(): void
    {
        $this->comprobante = null;
        $this->resetValidation('comprobante');
    }

    public function descargarBoletaPdf(int $id)
    {
        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($id);

        return (new IncidenciasPage)->descargarBoletaPdf($incidencia->id);
    }

    public function updatedBoletaDesdeHora(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaHastaHora(): void
    {
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaModalidad(): void
    {
        if ($this->boletaModalidad === 'dias') {
            $this->boletaDesdeHora = '';
            $this->boletaHastaHora = '';
        } else {
            if (blank($this->boletaDesdeHora)) {
                $this->boletaDesdeHora = '08:30';
            }
            if (blank($this->boletaHastaHora)) {
                $this->boletaHastaHora = '16:30';
            }
        }
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaDesdeFecha(): void
    {
        try {
            $desde = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hasta = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            if ($hasta->lessThan($desde)) {
                $this->boletaHastaFecha = $this->boletaDesdeFecha;
            }
        } catch (\Throwable) {
        }
        $this->recalcularTiempoSolicitado();
    }

    public function updatedBoletaHastaFecha(): void
    {
        try {
            $desde = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hasta = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            if ($hasta->lessThan($desde)) {
                $this->boletaDesdeFecha = $this->boletaHastaFecha;
            }
        } catch (\Throwable) {
        }
        $this->recalcularTiempoSolicitado();
    }

    public function getEsRangoDiasProperty(): bool
    {
        if ($this->boletaModalidad === 'dias') {
            return true;
        }

        try {
            $desde = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hasta = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            return $hasta->greaterThan($desde);
        } catch (\Throwable) {
            return false;
        }
    }

    private function recalcularTiempoSolicitado(): void
    {
        try {
            $desdeFecha = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hastaFecha = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            if ($hastaFecha->lessThan($desdeFecha)) {
                $hastaFecha = $desdeFecha->copy();
                $this->boletaHastaFecha = $this->boletaDesdeFecha;
            }

            if ($hastaFecha->greaterThan($desdeFecha)) {
                $this->boletaModalidad = 'dias';
            }

            // Si es modalidad por días (1 día completo o rango de varios días):
            if ($this->boletaModalidad === 'dias' || $hastaFecha->greaterThan($desdeFecha)) {
                $dias = (int) $desdeFecha->diffInDays($hastaFecha) + 1;
                $this->boletaTiempoSolicitado = $dias === 1 ? '1 DÍA' : "{$dias} DÍAS";
                $this->boletaDesdeHora = '';
                $this->boletaHastaHora = '';

                return;
            }

            if (blank($this->boletaDesdeHora)) {
                $this->boletaDesdeHora = '08:30';
            }
            if (blank($this->boletaHastaHora)) {
                $this->boletaHastaHora = '16:30';
            }

            $desde = Carbon::createFromFormat('Y-m-d H:i', $desdeFecha->format('Y-m-d') . ' ' . trim($this->boletaDesdeHora));
            $hasta = Carbon::createFromFormat('Y-m-d H:i', $hastaFecha->format('Y-m-d') . ' ' . trim($this->boletaHastaHora));

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
            } else {
                $this->boletaTiempoSolicitado = '0 MIN';
            }
        } catch (\Throwable) {
            // Mantener el valor actual si las fechas no se pueden parsear aún
        }
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

    public function descargarPdf()
    {
        $esRangoDias = $this->esRangoDias;

        $this->validate([
            'boletaMotivo' => ['required', 'string', 'max:255'],
            'boletaTipo' => ['required', 'in:comision,particular,medico,omision,retraso'],
            'boletaDesdeFecha' => ['required', 'string', 'max:20'],
            'boletaDesdeHora' => $esRangoDias ? ['nullable', 'string', 'max:10'] : ['required', 'string', 'max:10'],
            'boletaHastaFecha' => ['required', 'string', 'max:20'],
            'boletaHastaHora' => $esRangoDias ? ['nullable', 'string', 'max:10'] : ['required', 'string', 'max:10'],
            'boletaTiempoSolicitado' => ['required', 'string', 'max:50'],
            'comprobante' => ['required', 'image', 'max:5120'],
        ], [
            'boletaMotivo.required' => 'Ingresa el motivo de la justificación o boleta.',
            'boletaTiempoSolicitado.required' => 'Indica el tiempo solicitado.',
            'comprobante.required' => 'Es obligatorio subir una foto o comprobante que justifique el motivo de la boleta.',
            'comprobante.image' => 'El comprobante debe ser un archivo de imagen válido (JPG, PNG o WEBP).',
            'comprobante.max' => 'La imagen del comprobante no puede pesar más de 5MB.',
        ]);

        if ($this->plazo48HorasInfo['vencido']) {
            $this->addError('boletaDesdeFecha', $this->plazo48HorasInfo['mensaje']);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'boletaDesdeFecha' => $this->plazo48HorasInfo['mensaje'],
            ]);
        }

        $empleado = $this->empleado;

        // Si el empleado no tiene correo registrado en la base de datos:
        if (blank($empleado->email)) {
            if (blank(trim($this->boletaEmail))) {
                $this->showPedirEmailModal = true;

                return;
            }

            $this->validate([
                'boletaEmail' => ['required', 'email', 'max:191'],
            ], [
                'boletaEmail.required' => 'Ingresa el correo electrónico donde recibirás el estado de tu boleta.',
                'boletaEmail.email' => 'Ingresa un correo válido (ej. tu.nombre@correos.gob.bo o correo@gmail.com).',
            ]);

            $empleado->email = strtolower(trim($this->boletaEmail));
            $empleado->save();
            $this->tieneEmailRegistrado = true;
        }
        // Nota: Si ya tiene correo, solo el administrador desde el módulo de personal puede modificarlo.

        return $this->procesarEnvioBoleta($empleado);
    }

    public function confirmarEmailYDescargar()
    {
        $this->validate([
            'boletaEmail' => ['required', 'email', 'max:191'],
        ], [
            'boletaEmail.required' => 'Ingresa el correo electrónico donde recibirás el estado de tu boleta.',
            'boletaEmail.email' => 'Ingresa un correo válido (ej. tu.nombre@correos.gob.bo o correo@gmail.com).',
        ]);

        $empleado = $this->empleado;

        // Solo se guarda si el empleado aún no tenía correo registrado
        if (blank($empleado->email)) {
            $empleado->email = strtolower(trim($this->boletaEmail));
            $empleado->save();
        }

        $this->tieneEmailRegistrado = true;
        $this->showPedirEmailModal = false;

        return $this->procesarEnvioBoleta($empleado);
    }

    public function cerrarPedirEmailModal(): void
    {
        $this->showPedirEmailModal = false;
        $this->resetValidation('boletaEmail');
    }

    private function procesarEnvioBoleta(Empleado $empleado)
    {
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
                'email' => $empleado->email,
                'comprobante' => $comprobanteRegistro->nombre_original,
                'ruta' => $rutaArchivo,
            ]
        );

        $desdeCarbon = $this->parsearFechaCarbon($this->boletaDesdeFecha);
        $hastaCarbon = $this->parsearFechaCarbon($this->boletaHastaFecha);
        $ciudad = !empty($empleado->sucursal) ? mb_strtoupper($empleado->sucursal) : 'LA PAZ';
        $boleta = [
            'nombre' => $empleado->nombre_completo,
            'ci' => (string) ($empleado->codigo_biometrico ?: $empleado->id),
            'cargo' => (string) ($empleado->cargo ?: ($empleado->area ? 'AREA DE ' . $empleado->area : 'PERSONAL')),
            'motivo' => $this->boletaMotivo,
            'tipo' => $this->boletaTipo,
            'desde_fecha' => $desdeCarbon->format('d/m/Y'),
            'desde_hora' => $this->esRangoDias ? '--:--' : $this->boletaDesdeHora,
            'hasta_fecha' => $hastaCarbon->format('d/m/Y'),
            'hasta_hora' => $this->esRangoDias ? '--:--' : $this->boletaHastaHora,
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
}
