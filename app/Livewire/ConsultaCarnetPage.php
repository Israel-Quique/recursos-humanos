<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\ReglaSancion;
use App\Services\AnalisisAsistenciaService;
use App\Services\AuditoriaService;
use App\Services\BoletaExcelService;
use App\Services\ProgramacionLaboralService;
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

    // Filtro y modal de anuncios de faltas y reglamento
    public string $categoriaAnuncio = 'todas';
    public bool $mostrarModalAnuncios = false;

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
    public string $boletaTipo = 'comision'; // 'comision', 'particular', 'medico', 'omision', 'retraso'
    public string $boletaModalidad = 'horas'; // 'horas' (mismo día con horario) o 'dias' (jornada completa o rango de días)
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

    public function setCategoriaAnuncio(string $categoria): void
    {
        $this->categoriaAnuncio = $categoria;
    }

    public function abrirModalAnuncios(): void
    {
        $this->mostrarModalAnuncios = true;
    }

    public function cerrarModalAnuncios(): void
    {
        $this->mostrarModalAnuncios = false;
    }

    public function toggleModalAnuncios(): void
    {
        $this->mostrarModalAnuncios = ! $this->mostrarModalAnuncios;
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
                'color' => 'blue',
                'activo' => $atrasoActivo,
                'descripcion' => 'Régimen de puntualidad y tolerancia mensual institucional por sucursal.',
                'items' => [
                    [
                        'subtitulo' => 'Tolerancia Institucional',
                        'condicion' => 'Dentro del margen mensual (30 a 35 minutos según sucursal)',
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
                'color' => 'rose',
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
                'color' => 'indigo',
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
                'color' => 'slate',
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

    public function getResumenFaltasEmpleadoProperty(): ?array
    {
        if (! $this->empleadoEncontrado) {
            return null;
        }

        try {
            $empleado = $this->empleadoEncontrado;
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();

            $analisisService = app(AnalisisAsistenciaService::class);
            $reporte = $analisisService->reportePersonalizado($empleado->id, $start, $end);

            $toleranciaMensual = app(ProgramacionLaboralService::class)->resolverToleranciaMensual($empleado->sucursal);
            $minutosRetraso = (int) ($reporte['retraso_resumen']['total_minutos'] ?? 0);
            $diasTarde = (int) ($reporte['retraso_resumen']['dias_tarde'] ?? 0);
            $omisionesCount = collect($reporte['rows'] ?? [])->filter(fn ($r) => ! empty($r['es_omision']))->count();
            $faltasCount = collect($reporte['rows'] ?? [])->filter(fn ($r) => ! empty($r['es_falta']))->count();

            $porcentajeUso = $toleranciaMensual > 0
                ? (int) min(100, round(($minutosRetraso / $toleranciaMensual) * 100))
                : 100;

            $excesoMinutos = max(0, $minutosRetraso - $toleranciaMensual);

            // Alertas institucionales basadas en el Artículo 45 SIN exponer montos en dinero ni días descontados
            if ($minutosRetraso > $toleranciaMensual) {
                $nivel = 'sancion';
                $titulo = '🚨 Advertencia Reglamentaria · Artículo 45, Numeral I';
                $mensaje = "Has acumulado {$minutosRetraso} min de retraso en el mes, excediendo la tolerancia institucional de {$toleranciaMensual} min por {$excesoMinutos} min. Conforme al Artículo 45, superar este margen genera consecuencias y sanciones disciplinarias oficiales.";
            } elseif ($minutosRetraso >= ($toleranciaMensual * 0.75) && $toleranciaMensual > 0) {
                $nivel = 'preventivo';
                $titulo = '⚠️ Aviso Preventivo · Artículo 45, Numeral I';
                $mensaje = "Has acumulado {$minutosRetraso} min de retraso (has utilizado el {$porcentajeUso}% de tus {$toleranciaMensual} min de tolerancia mensual). Regula tus ingresos para evitar exceder el margen y quedar sujeto a sanciones según el Artículo 45.";
            } elseif ($minutosRetraso > 0) {
                $nivel = 'regular';
                $titulo = 'ℹ️ Control de Tolerancia · Artículo 45';
                $mensaje = "Llevas {$minutosRetraso} min acumulados de los {$toleranciaMensual} min de tolerancia institucional. Te encuentras dentro del margen permitido sin consecuencias.";
            } else {
                $nivel = 'optimo';
                $titulo = '✅ Asistencia Regular · Artículo 45';
                $mensaje = "No registras atrasos acumulados en el mes en curso. Cuentas con tus {$toleranciaMensual} min de tolerancia mensual disponibles.";
            }

            return [
                'minutos_retraso' => $minutosRetraso,
                'tolerancia_mensual' => $toleranciaMensual,
                'exceso_minutos' => $excesoMinutos,
                'porcentaje_uso' => $porcentajeUso,
                'dias_tarde' => $diasTarde,
                'omisiones_count' => $omisionesCount,
                'faltas_count' => $faltasCount,
                'nivel' => $nivel,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'mes_nombre' => ucfirst(now()->locale('es')->translatedFormat('F Y')),
            ];
        } catch (\Throwable) {
            return null;
        }
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
        if (! $this->empleadoEncontrado) {
            return collect();
        }

        return PermisoLaboral::query()
            ->with(['comprobantePrincipal'])
            ->where('empleado_id', $this->empleadoEncontrado->id)
            ->latest('id')
            ->take(8)
            ->get();
    }

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
        $this->boletaModalidad = 'horas';

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
                $this->boletaHastaHora = '09:00';
            }
        }
        $this->recalcularTiempoSolicitado();
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
        try {
            $desde = $this->parsearFechaCarbon($this->boletaDesdeFecha)->startOfDay();
            $hasta = $this->parsearFechaCarbon($this->boletaHastaFecha)->startOfDay();

            // Si la fecha hasta es menor, igualar automáticamente
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

            // Si la fecha hasta es mayor a la fecha desde, conmutar automáticamente a modalidad días
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

    public function descargarBoletaPdf(int $id)
    {
        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($id);

        return (new IncidenciasPage)->descargarBoletaPdf($incidencia->id);
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
            'boletaTipo' => ['required', 'in:comision,particular,medico,omision,retraso'],
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

        if ($this->plazo48HorasInfo['vencido']) {
            $this->addError('boletaDesdeFecha', $this->plazo48HorasInfo['mensaje']);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'boletaDesdeFecha' => $this->plazo48HorasInfo['mensaje'],
            ]);
        }

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
