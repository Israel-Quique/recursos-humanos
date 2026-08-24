<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\Importacion;
use App\Models\BiometricoDispositivo;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalisisAsistenciaService
{
    public function __construct(private ProgramacionLaboralService $programacionLaboral)
    {
    }

    public function resumenProyecto(): array
    {
        $sourceModules = $this->modulosFuente();
        $integrated = collect($this->modulosIntegrados())->where('integrated', true)->count();
        $available = collect($sourceModules)->where('available', true)->count();

        return [
            'rh_routes' => $this->countOccurrences(base_path('routes/web.php'), 'Route::get('),
            'rh_livewire' => count(glob(base_path('app/Livewire/*.php')) ?: []),
            'available_source_modules' => $available,
            'integrated_modules' => $integrated,
            'missing_backend_blocks' => $this->bloquesPendientes(),
            'import_script_ready' => file_exists(base_path('scripts/process_biometrics.py')),
            'service_ready' => file_exists(base_path('app/Services/ImportacionBiometricaService.php')),
            'connected_devices' => collect($this->estadoBiometricos())->where('connected', true)->count(),
            'disconnected_devices' => collect($this->estadoBiometricos())->where('connected', false)->count(),
        ];
    }

    public function modulosIntegrados(): array
    {
        return [
            [
                'name' => 'Panel de asistencia',
                'route' => 'dashboard',
                'description' => 'Resumen ejecutivo del sistema y acceso al mapa operativo.',
                'integrated' => file_exists(base_path('app/Livewire/DashboardPage.php')),
            ],
            [
                'name' => 'Importacion biometrica',
                'route' => 'importar',
                'description' => 'Carga de archivos para crear asistencias reales en la base de datos.',
                'integrated' => file_exists(base_path('app/Livewire/ImportarExcelPage.php')),
            ],
            [
                'name' => 'Calendario laboral',
                'route' => 'calendario',
                'description' => 'Vista de jornada, hitos y eventos de operacion.',
                'integrated' => file_exists(base_path('app/Livewire/CalendarioPage.php')),
            ],
            [
                'name' => 'Reportes operativos',
                'route' => 'reportes',
                'description' => 'Permisos, faltas del dia, ausencias injustificadas y olvidos de marcacion.',
                'integrated' => file_exists(base_path('app/Livewire/ReportesPage.php')),
            ],
            [
                'name' => 'Registro de personal',
                'route' => 'personal',
                'description' => 'Alta y administracion de plantilla de RRHH con codigo biometrico.',
                'integrated' => file_exists(base_path('app/Livewire/PersonalPage.php')),
            ],
            [
                'name' => 'Fechas especiales',
                'route' => 'fechas-especiales',
                'description' => 'Configura feriados y jornadas reducidas para ajustar el calculo operativo.',
                'integrated' => file_exists(base_path('app/Livewire/FechasEspecialesPage.php')),
            ],
        ];
    }

    public function modulosFuente(): array
    {
        $sourceRoot = dirname(base_path()).DIRECTORY_SEPARATOR.'sistema-asistencia';

        $modules = [
            ['label' => 'Dashboard completo', 'path' => $sourceRoot.'\app\Livewire\DashboardPage.php'],
            ['label' => 'Importacion Excel', 'path' => $sourceRoot.'\app\Livewire\ImportarExcelPage.php'],
            ['label' => 'Calendario', 'path' => $sourceRoot.'\app\Livewire\CalendarioPage.php'],
            ['label' => 'Reportes', 'path' => $sourceRoot.'\app\Livewire\ReportesPage.php'],
            ['label' => 'Historial de importaciones', 'path' => $sourceRoot.'\app\Livewire\HistorialImportacionesPage.php'],
            ['label' => 'Perfil', 'path' => $sourceRoot.'\app\Livewire\PerfilPage.php'],
        ];

        return array_map(fn (array $module) => [
            'label' => $module['label'],
            'available' => file_exists($module['path']),
        ], $modules);
    }

    public function estructuraOrganizacional(): array
    {
        return [
            'central' => [
                'label' => 'Nodo central',
                'title' => 'RRHH y control biometrico',
            ],
            'branches' => [
                [
                    'label' => 'Operaciones',
                    'title' => 'Sincronizacion de marcaciones',
                    'detail' => 'Recibe archivos del biometrico, depura eventos y prepara la importacion diaria.',
                ],
                [
                    'label' => 'Administracion',
                    'title' => 'Gestion de personal',
                    'detail' => 'Mantiene el padron de empleados, sucursales, horarios y codigos biometricos.',
                ],
                [
                    'label' => 'Supervision',
                    'title' => 'Reportes e incidencias',
                    'detail' => 'Consolida permisos, faltas, olvidos de marcacion y cierres operativos.',
                ],
            ],
        ];
    }

    public function diagnosticoIntegracion(): array
    {
        return [
            [
                'title' => 'Base UI y navegacion',
                'status' => 'completado',
                'detail' => 'El sistema ya cuenta con layout, modulos y control de acceso por roles.',
            ],
            [
                'title' => 'Importacion biometrica real',
                'status' => Importacion::query()->exists() ? 'completado' : 'en progreso',
                'detail' => 'La importacion genera registros de asistencia en SQLite a partir de Excel.',
            ],
            [
                'title' => 'Plantilla de personal',
                'status' => Empleado::query()->exists() ? 'completado' : 'en progreso',
                'detail' => 'El sistema ya puede registrar personal con codigo biometrico y sucursal.',
            ],
            [
                'title' => 'Reportes diarios',
                'status' => RegistroAsistencia::query()->exists() ? 'completado' : 'en progreso',
                'detail' => 'Los reportes se calculan desde asistencias, permisos y faltas actuales.',
            ],
        ];
    }

    public function calendarioLaboral(?Carbon $reference = null): array
    {
        $reference ??= now();
        $start = $reference->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = $reference->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $current = $start->copy();
        $weeks = [];
        $events = $this->eventosTardanzaPorFecha($reference);

        // Cargar feriados nacionales e institucionales (DB y API Nager)
        $fechasEspecialesDB = FechaEspecialLaboral::query()
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->get()
            ->groupBy(fn ($f) => $f->fecha?->format('Y-m-d'));

        $apiHolidays = $this->obtenerFeriadosApiBolivia((int) $reference->year);

        while ($current->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $current->copy();
                $dateStr = $day->format('Y-m-d');
                $dayEvents = collect($events[$dateStr] ?? []);

                // Verificar feche especial DB o API
                $especialesDB = $fechasEspecialesDB->get($dateStr, collect());
                $apiHoliday = $apiHolidays[$dateStr] ?? null;

                $esFeriado = $especialesDB->contains(fn ($f) => in_array($f->tipo, ['feriado', 'paro'], true))
                    || ($apiHoliday !== null);

                $nombreFeriado = $especialesDB->first()?->nombre
                    ?? ($apiHoliday['nombre'] ?? null);

                $week[] = [
                    'date' => $dateStr,
                    'label' => $day->day,
                    'is_current_month' => $day->month === $reference->month,
                    'is_today' => $day->isToday(),
                    'is_weekend' => $day->isWeekend(),
                    'is_holiday' => $esFeriado,
                    'holiday_name' => $nombreFeriado,
                    'has_special_schedule' => $especialesDB->contains(fn ($f) => $f->tipo === 'horario_especial'),
                    'events' => $dayEvents->all(),
                    'summary' => [
                        'red' => $dayEvents->where('tone', 'red')->count(),
                        'black' => $dayEvents->where('tone', 'black')->count(),
                    ],
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'month_label' => ucfirst($reference->locale('es')->translatedFormat('F Y')),
            'entry_time' => config('asistencia.hora_entrada'),
            'exit_time' => config('asistencia.hora_salida'),
            'hours' => (int) config('asistencia.horas_jornada'),
            'tolerance' => (int) config('asistencia.tolerancia_mensual_min'),
            'weeks' => $weeks,
            'weekdays' => ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
            'prev_label' => ucfirst($reference->copy()->subMonth()->locale('es')->translatedFormat('F Y')),
            'next_label' => ucfirst($reference->copy()->addMonth()->locale('es')->translatedFormat('F Y')),
            'milestones' => [
                'Punto rojo: llego tarde en el dia',
                'Punto negro: ese atraso hizo que exceda su tolerancia mensual',
                'Punto morado/dorado: Feriado o Fecha Especial programada',
                'Los sabados, domingos y feriados no se consideran para el control de tardanzas',
            ],
        ];
    }

    public function detalleCalendarioDia(Carbon|string|null $date): array
    {
        $selectedDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date ?? now()->toDateString());
        $monthEvents = $this->eventosTardanzaPorFecha($selectedDate->copy()->startOfMonth());
        $dayEvents = collect($monthEvents[$selectedDate->format('Y-m-d')] ?? []);

        $registrosDia = RegistroAsistencia::query()
            ->with('empleado')
            ->whereDate('fecha', $selectedDate->toDateString())
            ->orderBy('hora_entrada')
            ->get()
            ->filter(fn (RegistroAsistencia $registro) => $registro->empleado)
            ->values();

        // Fechas especiales en la fecha
        $fechasEspeciales = FechaEspecialLaboral::query()
            ->whereDate('fecha', $selectedDate->toDateString())
            ->get();

        $apiHolidays = $this->obtenerFeriadosApiBolivia((int) $selectedDate->year);
        $apiHoliday = $apiHolidays[$selectedDate->format('Y-m-d')] ?? null;

        return [
            'date' => $selectedDate->toDateString(),
            'date_label' => $selectedDate->format('d/m/Y'),
            'day_label' => ucfirst($selectedDate->locale('es')->isoFormat('dddd')),
            'is_today' => $selectedDate->isToday(),
            'is_saturday' => $selectedDate->dayOfWeek === Carbon::SATURDAY,
            'fechas_especiales' => $fechasEspeciales->map(fn ($f) => [
                'id' => $f->id,
                'nombre' => $f->nombre,
                'tipo' => $f->tipo,
                'sucursal' => $f->sucursal,
                'horario' => $f->hora_entrada ? (substr($f->hora_entrada, 0, 5).' - '.substr((string) $f->hora_salida, 0, 5)) : null,
            ])->all(),
            'api_holiday' => $apiHoliday,
            'totals' => [
                'marcaciones' => $registrosDia->count(),
                'tardanzas' => $dayEvents->count(),
                'excedidos' => $dayEvents->where('tone', 'black')->count(),
                'minutos_retraso' => $dayEvents->sum('minutes_late'),
                'minutos_retraso_formateado' => $this->formatearMinutosEtiqueta((int) $dayEvents->sum('minutes_late')),
            ],
            'events' => $dayEvents->map(fn (array $event) => [
                'empleado_id' => $event['empleado_id'],
                'nombre' => $event['label'],
                'detalle' => $event['detail'],
                'entrada' => $event['entry_time'],
                'estado' => $event['status'],
                'sucursal' => $event['branch'],
                'tone' => $event['tone'],
            ])->values()->all(),
            'marcaciones' => $registrosDia->map(function (RegistroAsistencia $registro) {
                $marcacion = $this->normalizarMarcacionAsistencia($registro);

                return [
                    'nombre' => $registro->empleado?->nombre_completo,
                    'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                    'salida' => $marcacion['salida'] ? substr($marcacion['salida'], 0, 5) : '--:--',
                    'estado' => $this->resolverEstadoMarcacionVisible($registro, $marcacion),
                    'sucursal' => $registro->empleado?->sucursal ?: 'Sin sucursal',
                ];
            })->all(),
        ];
    }

    public function obtenerFeriadosApiBolivia(int $year): array
    {
        return \Illuminate\Support\Facades\Cache::remember('holidays_bo_v2_'.$year, 86400, function () use ($year) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->get("https://nagerholidays.com/api/v4/Holidays/BO/{$year}");

                if (! $response->successful()) {
                    return $this->feriadosOficialesFallbackBolivia($year);
                }

                $data = $response->json();
                $holidays = [];

                $traducciones = [
                    "New Year's Day" => "Año Nuevo",
                    "Plurinational State Foundation Day" => "Día del Estado Plurinacional",
                    "Feast of the Virgin of Candelaria" => "Virgen de la Candelaria",
                    "Carnival" => "Carnaval",
                    "Good Friday" => "Viernes Santo",
                    "Labour Day" => "Día del Trabajo",
                    "Corpus Christi" => "Corpus Christi",
                    "Andean New Year" => "Año Nuevo Andino Amazónico",
                    "Agrarian Reform Day" => "Día de la Revolución Agraria",
                    "Independence Day" => "Día de la Independencia de Bolivia",
                    "All Saints' Day" => "Día de Todos los Santos",
                    "Christmas Day" => "Navidad",
                ];

                foreach ($data as $item) {
                    if (isset($item['date'])) {
                        $rawName = $item['name'] ?? '';
                        $localName = $item['localName'] ?? '';
                        $nombreEspanol = $traducciones[$rawName] ?? ($localName ?: $rawName);

                        $holidays[$item['date']] = [
                            'date' => $item['date'],
                            'nombre' => $nombreEspanol ?: 'Feriado Nacional',
                            'tipo' => 'feriado',
                        ];
                    }
                }

                return $holidays;
            } catch (\Throwable $e) {
                return $this->feriadosOficialesFallbackBolivia($year);
            }
        });
    }

    private function feriadosOficialesFallbackBolivia(int $year): array
    {
        return [
            "{$year}-01-01" => ['date' => "{$year}-01-01", 'nombre' => "Año Nuevo", 'tipo' => 'feriado'],
            "{$year}-01-22" => ['date' => "{$year}-01-22", 'nombre' => "Día del Estado Plurinacional", 'tipo' => 'feriado'],
            "{$year}-02-02" => ['date' => "{$year}-02-02", 'nombre' => "Virgen de la Candelaria", 'tipo' => 'feriado'],
            "{$year}-05-01" => ['date' => "{$year}-05-01", 'nombre' => "Día del Trabajo", 'tipo' => 'feriado'],
            "{$year}-06-21" => ['date' => "{$year}-06-21", 'nombre' => "Año Nuevo Andino Amazónico", 'tipo' => 'feriado'],
            "{$year}-08-02" => ['date' => "{$year}-08-02", 'nombre' => "Día de la Revolución Agraria", 'tipo' => 'feriado'],
            "{$year}-08-06" => ['date' => "{$year}-08-06", 'nombre' => "Día de la Independencia de Bolivia", 'tipo' => 'feriado'],
            "{$year}-11-02" => ['date' => "{$year}-11-02", 'nombre' => "Día de Todos los Santos", 'tipo' => 'feriado'],
            "{$year}-12-25" => ['date' => "{$year}-12-25", 'nombre' => "Navidad", 'tipo' => 'feriado'],
        ];
    }

    public function historialImportaciones(?int $year = null, ?int $month = null): array
    {
        $query = Importacion::query();

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        return $query
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Importacion $importacion) => [
                'id' => $importacion->id,
                'file' => $importacion->nombre_archivo,
                'records' => number_format($importacion->registros_generados).' asistencias',
                'date' => $importacion->created_at?->format('d/m/Y H:i'),
                'status' => ucfirst($importacion->estado),
            ])
            ->all();
    }

    public function metricasReporte(): array
    {
        $today = now()->toDateString();
        $activeEmployees = $this->empleadosActivos($today);
        $attendanceToday = RegistroAsistencia::query()->whereDate('fecha', $today)->get();
        $incidentsToday = PermisoLaboral::query()
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->get();

        $forgotMarks = $attendanceToday
            ->filter(fn (RegistroAsistencia $registro) => $this->debeContarComoOlvidoMarcacion($registro))
            ->count();

        $markedIds = $attendanceToday->pluck('empleado_id')->unique();
        $faltas = $activeEmployees->filter(function (Empleado $empleado) use ($markedIds, $today) {
            if ($this->programacionLaboral->esDiaNoLaborable($today, $empleado->sucursal)) {
                return false;
            }

            if ($markedIds->contains($empleado->id)) {
                return false;
            }

            return ! PermisoLaboral::query()
                ->where('empleado_id', $empleado->id)
                ->where('estado', 'aprobado')
                ->whereDate('fecha_inicio', '<=', $today)
                ->whereDate('fecha_fin', '>=', $today)
                ->exists();
        })->count();

        return [
            ['label' => 'Incidencias vigentes hoy', 'value' => (string) $incidentsToday->count(), 'detail' => 'Permisos, paros, cumpleaños, incidencias y faltas aprobadas', 'tone' => 'emerald'],
            ['label' => 'Ausencias injustificadas', 'value' => (string) $faltas, 'detail' => 'Sin permiso y sin marcacion registrada', 'tone' => 'amber'],
            ['label' => 'Olvidos de marcar', 'value' => (string) $forgotMarks, 'detail' => 'Asistencia incompleta por falta de entrada o salida', 'tone' => 'rose'],
        ];
    }

    public function metricasReportePorRango(Carbon $start, Carbon $end, ?string $branch = null): array
    {
        $attendanceRange = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->with('empleado')
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->get();

        $incidentsRange = $this->filtrarIncidenciasPorSucursal(
            PermisoLaboral::query(),
            $branch
        )
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $forgotMarks = $attendanceRange
            ->filter(fn (RegistroAsistencia $registro) => $this->debeContarComoOlvidoMarcacion($registro))
            ->count();

        $faltas = $this->contarFaltasEnRango($start, $end, $branch);
        $horasIncidencia = (int) $incidentsRange->sum('minutos_contabilizados');

        return [
            ['label' => 'Incidencias en rango', 'value' => (string) $incidentsRange->count(), 'detail' => 'Horas justificadas: '.$this->formatearMinutosEtiqueta($horasIncidencia), 'tone' => 'emerald'],
            ['label' => 'Ausencias injustificadas', 'value' => (string) $faltas, 'detail' => 'Dias sin permiso ni marcacion en el rango seleccionado', 'tone' => 'amber'],
            ['label' => 'Olvidos de marcar', 'value' => (string) $forgotMarks, 'detail' => 'Registros incompletos dentro del rango filtrado', 'tone' => 'rose'],
        ];
    }

    public function frecuenciaAsistencia(?Carbon $reference = null, ?string $branch = null): array
    {
        $reference ??= now();

        $months = collect(range(5, 0))->map(function (int $offset) use ($reference, $branch) {
            $date = $reference->copy()->subMonths($offset);
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();

            $count = $this->filtrarAsistenciasPorSucursal(
                RegistroAsistencia::query(),
                $branch
            )
                ->whereDate('fecha', '>=', $start)
                ->whereDate('fecha', '<=', $end)
                ->count();

            return [
                'label' => $offset === 0
                    ? ucfirst($date->locale('es')->translatedFormat('M')).' (Actual)'
                    : ucfirst($date->locale('es')->translatedFormat('M')),
                'value' => $date->format('Y-m'),
                'count' => $count,
                'active' => $offset === 0,
            ];
        });

        $max = max($months->max('count'), 1);
        $half = max((int) ceil($max / 2), 1);
        $currentCount = (int) ($months->firstWhere('active', true)['count'] ?? 0);
        $peakMonth = $months->sortByDesc('count')->first();

        return [
            'scale' => [
                'max' => $max,
                'mid' => $half,
                'min' => 0,
            ],
            'summary' => [
                'current_count' => $currentCount,
                'peak_count' => (int) ($peakMonth['count'] ?? 0),
                'peak_label' => $peakMonth['label'] ?? '-',
            ],
            'bars' => $months->map(fn (array $month) => [
                'label' => $month['label'],
                'value' => $month['value'],
                'height' => max(18, (int) round(($month['count'] / $max) * 100)).'%',
                'active' => $month['active'],
                'count' => $month['count'],
                'is_peak' => $month['count'] === $max && $month['count'] > 0,
            ])->values()->all(),
        ];
    }

    public function incidenciasPorRango(Carbon $start, Carbon $end, ?string $branch = null): array
    {
        $permissions = $this->filtrarIncidenciasPorSucursal(
            PermisoLaboral::query(),
            $branch
        )
            ->with('empleado')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->orderBy('fecha_inicio')
            ->get();

        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->with('empleado')
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->orderBy('fecha')
            ->get();

        return [
            'permisos' => $permissions->where('tipo', '!=', 'falta')->map(fn (PermisoLaboral $permiso) => [
                'nombre' => $permiso->empleado?->nombre_completo ?? 'Sin personal',
                'detalle' => $permiso->tipo_label.' | '.$permiso->alcance_label.' | '.$this->formatearMinutosEtiqueta((int) ($permiso->minutos_contabilizados ?? 0)).' | '.$permiso->fecha_inicio?->format('d/m/Y').' al '.$permiso->fecha_fin?->format('d/m/Y'),
            ])->values()->all(),
            'faltas' => [
                ...$permissions->where('tipo', 'falta')->map(fn (PermisoLaboral $permiso) => [
                    'nombre' => $permiso->empleado?->nombre_completo ?? 'Sin personal',
                    'detalle' => 'Falta registrada | '.$permiso->alcance_label.' | '.$this->formatearMinutosEtiqueta((int) ($permiso->minutos_contabilizados ?? 0)).' | '.$permiso->fecha_inicio?->format('d/m/Y').' al '.$permiso->fecha_fin?->format('d/m/Y'),
                ])->values()->all(),
                ...$this->detalleFaltasEnRango($start, $end, $branch),
            ],
            'olvidos' => $attendance
                ->filter(fn (RegistroAsistencia $registro) => $this->debeContarComoOlvidoMarcacion($registro))
                ->map(function (RegistroAsistencia $registro) {
                    $marcacion = $this->normalizarMarcacionAsistencia($registro);

                    return [
                        'nombre' => $registro->empleado?->nombre_completo ?? 'Sin personal',
                        'detalle' => $registro->fecha?->format('d/m/Y').' - Entrada: '.($marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--').' / Salida: '.($marcacion['salida'] ? substr($marcacion['salida'], 0, 5) : '--:--'),
                    ];
                })->values()->all(),
        ];
    }

    public function resumenMensualReporte(Carbon $referenceMonth, ?string $branch = null): array
    {
        $monthStart = $referenceMonth->copy()->startOfMonth()->toDateString();
        $monthEnd = $referenceMonth->copy()->endOfMonth()->toDateString();
        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->with('empleado')
            ->whereDate('fecha', '>=', $monthStart)
            ->whereDate('fecha', '<=', $monthEnd)
            ->orderBy('fecha')
            ->get()
            ->filter(function (RegistroAsistencia $registro) {
                if (! $registro->empleado) {
                    return false;
                }

                return $this->programacionLaboral->resolverHorario($registro->empleado, $registro->fecha)['laborable'];
            });

        $workedMinutes = 0;
        $lateMinutes = 0;
        $lateDays = 0;
        $perEmployee = [];

        foreach ($attendance as $registro) {
            $empleado = $registro->empleado;
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);
            $workedMinutes += $this->calcularMinutosTrabajados($registro->hora_entrada, $registro->hora_salida);
            $delay = $this->calcularMinutosRetraso(
                $this->normalizarMarcacionAsistencia($registro)['entrada'],
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );
            $lateMinutes += $delay;
            if ($delay > 0) {
                $lateDays++;
                $perEmployee[$empleado->id]['late_days'] = ($perEmployee[$empleado->id]['late_days'] ?? 0) + 1;
                $perEmployee[$empleado->id]['late_minutes'] = ($perEmployee[$empleado->id]['late_minutes'] ?? 0) + $delay;
            }
            $perEmployee[$empleado->id]['name'] = $empleado->nombre_completo;
            $perEmployee[$empleado->id]['branch'] = $empleado->sucursal ?: 'Sin sucursal';
        }

        $topEmployees = collect($perEmployee)
            ->sortByDesc('late_minutes')
            ->take(8)
            ->map(function (array $item, $employeeId) {
                return [
                    'empleado_id' => (int) $employeeId,
                    'nombre' => $item['name'],
                    'sucursal' => $item['branch'],
                    'dias_tarde' => $item['late_days'] ?? 0,
                    'retraso' => $this->formatearMinutosEtiqueta($item['late_minutes'] ?? 0),
                ];
            })
            ->values()
            ->all();

        return [
            'metrics' => [
                ['label' => 'Personal con marcaciones', 'value' => (string) $attendance->pluck('empleado_id')->unique()->count()],
                ['label' => 'Marcaciones del mes', 'value' => (string) $attendance->count()],
                ['label' => 'Horas trabajadas', 'value' => $this->formatearMinutos($workedMinutes)],
                ['label' => 'Retraso acumulado', 'value' => $this->formatearMinutosEtiqueta($lateMinutes)],
            ],
            'late_days' => $lateDays,
            'top_employees' => $topEmployees,
        ];
    }

    public function detalleMensualPorEmpleado(int $employeeId, Carbon $referenceMonth, ?string $branch = null): ?array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end = $referenceMonth->copy()->endOfMonth();
        $empleado = Empleado::query()->find($employeeId);

        if (! $empleado || ($branch && ! SucursalNormalizer::matches($empleado->sucursal, $branch))) {
            return null;
        }

        $attendance = RegistroAsistencia::query()
            ->where('empleado_id', $employeeId)
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->orderBy('fecha')
            ->get();

        $lateRows = [];
        $forgotRows = [];

        foreach ($attendance as $registro) {
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            $marcacion = $this->normalizarMarcacionAsistencia($registro);
            $soloEntrada = $marcacion['solo_entrada'];
            $horaSalidaReal = $marcacion['salida'];
            $delay = $this->calcularMinutosRetraso(
                $marcacion['entrada'],
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );

            if ($delay > 0) {
                $lateRows[] = [
                    'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                    'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                    'salida' => $horaSalidaReal ? substr($horaSalidaReal, 0, 5) : '--:--',
                    'retraso' => $this->formatearMinutosEtiqueta($delay),
                    'estado' => $this->resolverEstadoRegistroPersonalizado($registro, $soloEntrada, $horaSalidaReal, $delay),
                ];
            }

            if (blank($marcacion['entrada']) || blank($horaSalidaReal)) {
                $forgotRows[] = [
                    'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                    'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                    'salida' => $horaSalidaReal ? substr($horaSalidaReal, 0, 5) : '--:--',
                    'estado' => $this->resolverEstadoRegistroPersonalizado($registro, $soloEntrada, $horaSalidaReal, $delay),
                ];
            }
        }

        $permissions = PermisoLaboral::query()
            ->where('empleado_id', $employeeId)
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $faltas = [
            ...$permissions->where('tipo', 'falta')->map(fn (PermisoLaboral $permiso) => [
                'fecha' => $permiso->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha',
                'detalle' => 'Falta registrada | '.$permiso->alcance_label.' | '.$this->formatearMinutosEtiqueta((int) ($permiso->minutos_contabilizados ?? 0)),
            ])->values()->all(),
            ...collect($this->detalleFaltasEnRango($start, $end, $branch))
                ->filter(fn (array $item) => ($item['nombre'] ?? null) === $empleado->nombre_completo)
                ->map(fn (array $item) => [
                    'fecha' => str((string) ($item['detalle'] ?? ''))->before(' -')->toString(),
                    'detalle' => $item['detalle'],
                ])->values()->all(),
        ];

        return [
            'empleado' => [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre_completo,
                'codigo' => $empleado->codigo_biometrico ?: 'Sin codigo',
                'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                'horario' => ($empleado->hora_entrada_programada ? substr($empleado->hora_entrada_programada, 0, 5) : '--:--')
                    .' - '.
                    ($empleado->hora_salida_programada ? substr($empleado->hora_salida_programada, 0, 5) : '--:--'),
            ],
            'metrics' => [
                ['label' => 'Dias tarde', 'value' => (string) count($lateRows)],
                ['label' => 'No marcados', 'value' => (string) count($forgotRows)],
                ['label' => 'Faltas', 'value' => (string) count($faltas)],
            ],
            'tardanzas' => $lateRows,
            'no_marcados' => $forgotRows,
            'faltas' => $faltas,
        ];
    }

    public function reporteMensualNoMarcadosYAtrasos(Carbon $referenceMonth, ?string $branch = null): array
    {
        $monthStart = $referenceMonth->copy()->startOfMonth();
        $monthEnd = $referenceMonth->copy()->endOfMonth();

        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->with('empleado')
            ->whereDate('fecha', '>=', $monthStart->toDateString())
            ->whereDate('fecha', '<=', $monthEnd->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_entrada')
            ->get()
            ->filter(fn (RegistroAsistencia $registro) => $registro->empleado);

        $forgotMarks = $attendance
            ->filter(fn (RegistroAsistencia $registro) => $this->debeContarComoOlvidoMarcacion($registro))
            ->map(function (RegistroAsistencia $registro) {
                $marcacion = $this->normalizarMarcacionAsistencia($registro);

                return [
                    'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                    'nombre' => $registro->empleado?->nombre_completo ?? 'Sin personal',
                    'codigo' => $registro->empleado?->codigo_biometrico ?? '',
                    'sucursal' => $registro->empleado?->sucursal ?: 'Sin sucursal',
                    'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                    'salida' => $marcacion['salida'] ? substr($marcacion['salida'], 0, 5) : '--:--',
                    'estado' => $this->resolverEstadoMarcacionVisible($registro, $marcacion),
                    'detalle' => blank($marcacion['entrada'])
                        ? 'Falta marcacion de entrada'
                        : 'Falta marcacion de salida',
                ];
            })
            ->values();

        $lateRows = [];
        $lateEmployees = [];
        $lateMinutes = 0;

        foreach ($attendance as $registro) {
            $empleado = $registro->empleado;
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            $delay = $this->calcularMinutosRetraso(
                $registro->hora_entrada,
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );
            if ($delay <= 0) {
                continue;
            }

            $lateMinutes += $delay;
            $lateRows[] = [
                'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                'nombre' => $empleado->nombre_completo,
                'codigo' => $empleado->codigo_biometrico ?? '',
                'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                'entrada_programada' => $horario['hora_entrada'] ? substr($horario['hora_entrada'], 0, 5) : '--:--',
                'entrada_real' => ($entradaReal = $this->normalizarMarcacionAsistencia($registro)['entrada']) ? substr($entradaReal, 0, 5) : '--:--',
                'retraso' => $this->formatearMinutosEtiqueta($delay),
                'minutos_retraso' => $delay,
                'estado' => $registro->estado_marcacion ?: 'Sin estado',
            ];

            $lateEmployees[$empleado->id]['nombre'] = $empleado->nombre_completo;
            $lateEmployees[$empleado->id]['sucursal'] = $empleado->sucursal ?: 'Sin sucursal';
            $lateEmployees[$empleado->id]['dias_tarde'] = ($lateEmployees[$empleado->id]['dias_tarde'] ?? 0) + 1;
            $lateEmployees[$empleado->id]['minutos_tarde'] = ($lateEmployees[$empleado->id]['minutos_tarde'] ?? 0) + $delay;
        }

        $lateSummary = collect($lateEmployees)
            ->sortByDesc('minutos_tarde')
            ->values()
            ->map(fn (array $item) => [
                'nombre' => $item['nombre'],
                'sucursal' => $item['sucursal'],
                'dias_tarde' => $item['dias_tarde'],
                'retraso' => $this->formatearMinutosEtiqueta($item['minutos_tarde']),
            ])
            ->all();

        return [
            'metrics' => [
                ['label' => 'No marcados', 'value' => (string) $forgotMarks->count()],
                ['label' => 'Atrasos', 'value' => (string) count($lateRows)],
                ['label' => 'Personal con atrasos', 'value' => (string) count($lateSummary)],
                ['label' => 'Retraso acumulado', 'value' => $this->formatearMinutosEtiqueta($lateMinutes)],
            ],
            'no_marcados' => $forgotMarks->all(),
            'atrasos' => $lateRows,
            'resumen_atrasos' => $lateSummary,
        ];
    }

    public function reportePersonalizado(?int $employeeId, Carbon $start, Carbon $end, ?string $branch = null): ?array
    {
        if (! $employeeId) {
            return null;
        }

        $empleado = Empleado::query()->find($employeeId);
        if (! $empleado || ($branch && ! SucursalNormalizer::matches($empleado->sucursal, $branch))) {
            return null;
        }

        $effectiveEnd = $this->limitarFinDeReporteHastaHoy($start, $end);

        if (! $effectiveEnd) {
            return [
                'empleado' => [
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'Sin codigo',
                    'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                    'horario' => ($empleado->hora_entrada_programada ? substr($empleado->hora_entrada_programada, 0, 5) : '--:--')
                        .' - '.
                        ($empleado->hora_salida_programada ? substr($empleado->hora_salida_programada, 0, 5) : '--:--'),
                ],
                'metrics' => [
                    ['label' => 'Dias con marcacion', 'value' => '0'],
                    ['label' => 'Horas acumuladas', 'value' => '00:00'],
                    ['label' => 'Dias tarde', 'value' => '0'],
                    ['label' => 'Retraso acumulado', 'value' => '0 min'],
                    ['label' => 'Incidencias aprobadas', 'value' => '0'],
                    ['label' => 'Horas justificadas', 'value' => '0 min'],
                ],
                'rows' => [],
            ];
        }

        $attendance = RegistroAsistencia::query()
            ->where('empleado_id', $employeeId)
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $effectiveEnd->toDateString())
            ->orderBy('fecha')
            ->get();

        $incidents = PermisoLaboral::query()
            ->where('empleado_id', $employeeId)
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $effectiveEnd->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $workedMinutes = 0;
        $lateMinutes = 0;
        $lateDays = 0;
        $rows = [];
        $attendanceByDate = [];

        foreach ($attendance as $registro) {
            $dateKey = $registro->fecha?->toDateString();
            if ($dateKey) {
                $attendanceByDate[$dateKey] = true;
            }
        }

        foreach ($attendance as $registro) {
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            $marcacion = $this->normalizarMarcacionAsistencia($registro);
            $soloEntrada = $marcacion['solo_entrada'];
            $horaSalidaReal = $marcacion['salida'];
            $worked = $this->calcularMinutosTrabajados($marcacion['entrada'], $horaSalidaReal);
            $delay = $this->calcularMinutosRetraso(
                $marcacion['entrada'],
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );
            $workedMinutes += $worked;
            $lateMinutes += $delay;
            if ($delay > 0) {
                $lateDays++;
            }

            $missingMark = blank($marcacion['entrada']) || blank($horaSalidaReal);

            $rows[] = [
                'raw_date' => $registro->fecha?->toDateString(),
                'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                'salida' => $horaSalidaReal ? substr($horaSalidaReal, 0, 5) : '--:--',
                'horas' => $this->formatearMinutos($worked),
                'retraso' => $this->formatearMinutosEtiqueta($delay),
                'estado' => $this->resolverEstadoRegistroPersonalizado($registro, $soloEntrada, $horaSalidaReal, $delay),
                'estado_biometrico' => $this->resolverEstadoMarcacionVisible($registro, $marcacion),
                'evento_biometrico' => $registro->evento_biometrico ?: 'Sin evento',
                'row_tone' => $missingMark ? 'warning' : 'default',
            ];
        }

        $permissionDays = [];
        foreach ($incidents as $incident) {
            if (! $incident->fecha_inicio || ! $incident->fecha_fin) {
                continue;
            }

            $cursor = $incident->fecha_inicio->copy()->startOfDay();
            $incidentEnd = $incident->fecha_fin->copy()->startOfDay();

            while ($cursor->lte($incidentEnd)) {
                $permissionDays[$cursor->toDateString()][] = $incident;
                $cursor->addDay();
            }
        }

        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($effectiveEnd)) {
            $dateKey = $cursor->toDateString();
            $isLaborable = $this->programacionLaboral->resolverHorario($empleado, $cursor)['laborable'];

            if (! $isLaborable || isset($attendanceByDate[$dateKey])) {
                $cursor->addDay();
                continue;
            }

            $dayIncidents = collect($permissionDays[$dateKey] ?? []);
            $hasJustifiedPermission = $dayIncidents->contains(fn (PermisoLaboral $incident) => $incident->tipo !== 'falta');
            $hasFaltaPermission = $dayIncidents->contains(fn (PermisoLaboral $incident) => $incident->tipo === 'falta');

            if ($hasJustifiedPermission) {
                $cursor->addDay();
                continue;
            }

            $rows[] = [
                'raw_date' => $dateKey,
                'fecha' => $cursor->format('d/m/Y'),
                'entrada' => '--:--',
                'salida' => '--:--',
                'horas' => '00:00',
                'retraso' => '0 min',
                'estado' => $hasFaltaPermission ? 'Falta registrada' : 'Falta',
                'estado_biometrico' => 'Sin marcacion',
                'evento_biometrico' => $hasFaltaPermission ? 'Ausencia registrada' : 'Ausencia injustificada',
                'row_tone' => 'danger',
            ];

            $cursor->addDay();
        }

        $rows = collect($rows)
            ->sortBy(fn (array $row) => $row['raw_date'] ?? '9999-12-31')
            ->values()
            ->map(function (array $row) {
                unset($row['raw_date']);

                return $row;
            })
            ->all();

        return [
            'empleado' => [
                'nombre' => $empleado->nombre_completo,
                'codigo' => $empleado->codigo_biometrico ?: 'Sin codigo',
                'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                'horario' => ($empleado->hora_entrada_programada ? substr($empleado->hora_entrada_programada, 0, 5) : '--:--')
                    .' - '.
                    ($empleado->hora_salida_programada ? substr($empleado->hora_salida_programada, 0, 5) : '--:--'),
            ],
            'metrics' => [
                ['label' => 'Dias con marcacion', 'value' => (string) count($rows)],
                ['label' => 'Horas acumuladas', 'value' => $this->formatearMinutos($workedMinutes)],
                ['label' => 'Dias tarde', 'value' => (string) $lateDays],
                ['label' => 'Retraso acumulado', 'value' => $this->formatearMinutosEtiqueta($lateMinutes)],
                ['label' => 'Incidencias aprobadas', 'value' => (string) $incidents->count()],
                ['label' => 'Horas justificadas', 'value' => $this->formatearMinutosEtiqueta((int) $incidents->sum('minutos_contabilizados'))],
            ],
            'rows' => $rows,
        ];
    }

    public function empleadosParaReportes(?string $branch = null, ?string $search = null, int $limit = 30): array
    {
        $normalizedSearch = $this->normalizarTexto((string) ($search ?? ''));

        return Empleado::query()
            ->withUltimaMarcacion()
            ->laboralVigente(now())
            ->when(filled($branch), fn ($query) => SucursalNormalizer::applyFilter($query, 'sucursal', $branch))
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get()
            ->filter(function (Empleado $empleado) use ($normalizedSearch) {
                if (! $empleado->estaActivoLaboralmente(now())) {
                    return false;
                }

                if ($normalizedSearch === '') {
                    return true;
                }

                $name = $this->normalizarTexto($empleado->nombre_completo);
                $code = $this->normalizarTexto((string) ($empleado->codigo_biometrico ?? ''));

                return str_contains($name, $normalizedSearch)
                    || ($code !== '' && str_contains($code, $normalizedSearch));
            })
            ->take(max(1, $limit))
            ->map(fn (Empleado $empleado) => [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre_completo,
                'codigo' => $empleado->codigo_biometrico ?: 'Sin codigo',
                'label' => $empleado->nombre_completo.' | '.$empleado->codigo_biometrico,
            ])->all();
    }

    public function sucursalesParaReportes(): array
    {
        return SucursalNormalizer::optionsFromValues(Empleado::query()
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->orderBy('sucursal')
            ->distinct()
            ->pluck('sucursal')
            ->all());
    }

    public function reportesAntiguedad(?string $branch = null, int $limit = 10): array
    {
        $hoy = now()->startOfDay();

        $query = Empleado::query()
            ->whereNotNull('fecha_contratacion')
            ->when(filled($branch), fn ($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch));

        // Empleados más antiguos (contratación más antigua)
        $masAntiguos = (clone $query)
            ->orderBy('fecha_contratacion', 'asc')
            ->take($limit)
            ->get()
            ->map(function (Empleado $empleado) use ($hoy) {
                $contratacion = $empleado->fecha_contratacion;
                $diff = $contratacion->diff($hoy);

                $anios = $diff->y;
                $meses = $diff->m;
                $dias = $diff->d;

                $antiguedadTexto = [];
                if ($anios > 0) $antiguedadTexto[] = $anios.($anios === 1 ? ' año' : ' años');
                if ($meses > 0) $antiguedadTexto[] = $meses.($meses === 1 ? ' mes' : ' meses');
                if ($dias > 0 && $anios === 0) $antiguedadTexto[] = $dias.($dias === 1 ? ' día' : ' días');

                return [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'Sin código',
                    'area' => $empleado->area ?: 'Sin área',
                    'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                    'fecha_contratacion' => $contratacion->format('d/m/Y'),
                    'antiguedad_texto' => implode(' y ', $antiguedadTexto) ?: 'Hoy ingresó',
                    'dias_totales' => (int) $contratacion->diffInDays($hoy),
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            })->all();

        // Empleados más nuevos (contratación más reciente)
        $masNuevos = (clone $query)
            ->orderBy('fecha_contratacion', 'desc')
            ->take($limit)
            ->get()
            ->map(function (Empleado $empleado) use ($hoy) {
                $contratacion = $empleado->fecha_contratacion;
                $diff = $contratacion->diff($hoy);

                $anios = $diff->y;
                $meses = $diff->m;
                $dias = $diff->d;

                $antiguedadTexto = [];
                if ($anios > 0) $antiguedadTexto[] = $anios.($anios === 1 ? ' año' : ' años');
                if ($meses > 0) $antiguedadTexto[] = $meses.($meses === 1 ? ' mes' : ' meses');
                if ($dias > 0 && $anios === 0) $antiguedadTexto[] = $dias.($dias === 1 ? ' día' : ' días');

                return [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'Sin código',
                    'area' => $empleado->area ?: 'Sin área',
                    'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                    'fecha_contratacion' => $contratacion->format('d/m/Y'),
                    'antiguedad_texto' => implode(' y ', $antiguedadTexto) ?: 'Hoy ingresó',
                    'dias_totales' => (int) $contratacion->diffInDays($hoy),
                    'inicial' => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            })->all();

        return [
            'mas_antiguos' => $masAntiguos,
            'mas_nuevos' => $masNuevos,
        ];
    }

    public function cumpleaniosMes(Carbon $referenceMonth, ?string $branch = null): array
    {
        $mes = (int) $referenceMonth->format('m');
        $hoy = now()->startOfDay();
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY);
        $finSemana = now()->endOfWeek(Carbon::SUNDAY);

        $empleados = Empleado::query()
            ->whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', $mes)
            ->when(filled($branch), fn ($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $branch))
            ->get()
            ->sortBy(fn (Empleado $e) => (int) $e->fecha_nacimiento?->format('d'));

        return $empleados->map(function (Empleado $empleado) use ($hoy, $inicioSemana, $finSemana, $referenceMonth) {
            $diaCumple = (int) $empleado->fecha_nacimiento->format('d');
            $fechaCumpleEsteAnio = Carbon::create($hoy->year, (int) $referenceMonth->format('m'), $diaCumple);

            $esHoy = $fechaCumpleEsteAnio->isSameDay($hoy);
            $esEstaSemana = !$esHoy
                && $fechaCumpleEsteAnio->betweenIncluded($inicioSemana, $finSemana);

            return [
                'id'          => $empleado->id,
                'nombre'      => $empleado->nombre_completo,
                'area'        => $empleado->area ?: 'Sin área',
                'sucursal'    => $empleado->sucursal ?: 'Sin sucursal',
                'dia'         => $diaCumple,
                'fecha_label' => $empleado->fecha_nacimiento->locale('es')->translatedFormat('d \de F'),
                'edad'        => (int) $empleado->fecha_nacimiento->diffInYears($hoy),
                'es_hoy'      => $esHoy,
                'es_esta_semana' => $esEstaSemana,
                'inicial'     => strtoupper(mb_substr($empleado->nombre, 0, 1)),
            ];
        })->all();
    }

    public function rankingPuntualidad(Carbon $start, Carbon $end, ?string $branch = null, int $top = 5): array
    {
        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->with('empleado')
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->get()
            ->filter(fn (RegistroAsistencia $r) => $r->empleado);

        $perEmployee = [];

        foreach ($attendance as $registro) {
            $empleado = $registro->empleado;
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            $delay = $this->calcularMinutosRetraso(
                $registro->hora_entrada,
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );

            $id = $empleado->id;
            if (! isset($perEmployee[$id])) {
                $perEmployee[$id] = [
                    'empleado_id'    => $id,
                    'nombre'         => $empleado->nombre_completo,
                    'sucursal'       => $empleado->sucursal ?: 'Sin sucursal',
                    'area'           => $empleado->area ?: 'Sin área',
                    'dias_marcados'  => 0,
                    'dias_tarde'     => 0,
                    'minutos_tarde'  => 0,
                    'inicial'        => strtoupper(mb_substr($empleado->nombre, 0, 1)),
                ];
            }

            $perEmployee[$id]['dias_marcados']++;

            if ($delay > 0) {
                $perEmployee[$id]['dias_tarde']++;
                $perEmployee[$id]['minutos_tarde'] += $delay;
            }
        }

        if (empty($perEmployee)) {
            return ['mas_puntuales' => [], 'mas_atrasados' => []];
        }

        $collection = collect(array_values($perEmployee))
            ->filter(fn (array $e) => $e['dias_marcados'] > 0);

        $masAtrasados = $collection
            ->sortByDesc('minutos_tarde')
            ->take($top)
            ->values()
            ->map(fn (array $e) => [
                ...$e,
                'retraso_label' => $this->formatearMinutosEtiqueta($e['minutos_tarde']),
            ])
            ->all();

        $masPuntuales = $collection
            ->sortBy('minutos_tarde')
            ->take($top)
            ->values()
            ->map(fn (array $e) => [
                ...$e,
                'retraso_label' => $this->formatearMinutosEtiqueta($e['minutos_tarde']),
            ])
            ->all();

        return [
            'mas_puntuales' => $masPuntuales,
            'mas_atrasados' => $masAtrasados,
        ];
    }

    public function rankingPuntualidadMensual(Carbon $referenceMonth, ?string $branch = null, int $top = 5): array
    {
        $start = $referenceMonth->copy()->startOfMonth();
        $end   = $referenceMonth->copy()->endOfMonth();

        return $this->rankingPuntualidad($start, $end, $branch, $top);
    }

    public function rankingPuntualidadSemanal(?string $branch = null, int $top = 5): array
    {
        $start = now()->startOfWeek(Carbon::MONDAY);
        $end   = now()->endOfWeek(Carbon::SUNDAY)->min(now());

        return $this->rankingPuntualidad($start, $end, $branch, $top);
    }

    public function asistenciaPorDepartamento(): array
    {
        $base = collect([
            'la-paz' => ['name' => 'La Paz', 'branch' => 'Oficina Central La Paz'],
            'oruro' => ['name' => 'Oruro', 'branch' => 'Sucursal Oruro'],
            'potosi' => ['name' => 'Potosi', 'branch' => 'Sucursal Potosi'],
            'cochabamba' => ['name' => 'Cochabamba', 'branch' => 'Regional Cochabamba'],
            'chuquisaca' => ['name' => 'Chuquisaca', 'branch' => 'Sucursal Sucre'],
            'tarija' => ['name' => 'Tarija', 'branch' => 'Sucursal Tarija'],
            'santa-cruz' => ['name' => 'Santa Cruz', 'branch' => 'Regional Santa Cruz'],
            'beni' => ['name' => 'Beni', 'branch' => 'Sucursal Trinidad'],
            'pando' => ['name' => 'Pando', 'branch' => 'Sucursal Cobija'],
        ]);

        $today = now()->toDateString();
        $employees = $this->empleadosActivos($today);
        $attendances = RegistroAsistencia::query()
            ->with('empleado')
            ->whereDate('fecha', $today)
            ->get();
        $deviceStatuses = BiometricoDispositivo::query()
            ->where('is_active', true)
            ->get()
            ->groupBy(fn (BiometricoDispositivo $device) => $this->departamentoDesdeTexto($device->department.' '.$device->branch) ?? '__unknown__');

        return $base->map(function (array $department, string $key) use ($employees, $attendances, $deviceStatuses) {
            $departmentEmployees = $employees->filter(fn (Empleado $empleado) => $this->departamentoDesdeTexto($empleado->sucursal) === $key);
            $departmentAttendance = $attendances->filter(fn (RegistroAsistencia $registro) => $this->departamentoDesdeTexto($registro->empleado?->sucursal) === $key);
            $departmentDevices = $deviceStatuses->get($key, collect());
            $payload = $this->construirPayloadDepartamento(
                $department['name'],
                $this->etiquetaSucursalDepartamento($key, $departmentEmployees->pluck('sucursal')->filter()->first()),
                $departmentEmployees,
                $departmentAttendance,
                $departmentDevices,
                $key
            );

            if ($key === 'la-paz') {
                $departmentName = $department['name'];
                $subregions = collect([
                    'la-paz' => [
                        'label' => 'La Paz',
                        'employee_filter' => fn (Empleado $empleado) => ! $this->esSucursalElAlto($empleado->sucursal),
                        'attendance_filter' => fn (RegistroAsistencia $registro) => ! $this->esSucursalElAlto($registro->empleado?->sucursal),
                        'device_filter' => fn (BiometricoDispositivo $device) => ! $this->esSucursalElAlto($device->branch),
                    ],
                    'el-alto' => [
                        'label' => 'El Alto',
                        'employee_filter' => fn (Empleado $empleado) => $this->esSucursalElAlto($empleado->sucursal),
                        'attendance_filter' => fn (RegistroAsistencia $registro) => $this->esSucursalElAlto($registro->empleado?->sucursal),
                        'device_filter' => fn (BiometricoDispositivo $device) => $this->esSucursalElAlto($device->branch),
                    ],
                ])->mapWithKeys(function (array $subregion, string $subregionKey) use ($departmentName, $departmentEmployees, $departmentAttendance, $departmentDevices, $key) {
                    $employeesSubset = $departmentEmployees->filter($subregion['employee_filter'])->values();
                    $attendanceSubset = $departmentAttendance->filter($subregion['attendance_filter'])->values();
                    $devicesSubset = $departmentDevices->filter($subregion['device_filter'])->values();
                    $branchLabel = $subregionKey === 'el-alto'
                        ? 'Regional El Alto'
                        : 'Oficina Central La Paz';

                    return [
                        $subregionKey => $this->construirPayloadDepartamento(
                            $departmentName,
                            $branchLabel,
                            $employeesSubset,
                            $attendanceSubset,
                            $devicesSubset,
                            $key
                        ) + ['label' => $subregion['label']],
                    ];
                })->all();

                $payload['subregions'] = $subregions;
                $payload['default_subregion'] = 'la-paz';
            }

            return $payload;
        })->all();
    }

    public function estadoBiometricos(): array
    {
        return app(ConexionBiometricoService::class)->estadoDispositivos();
    }

    public function incidenciasDelDia(): array
    {
        $today = now()->toDateString();
        $permissionsToday = PermisoLaboral::query()
            ->with('empleado')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->get();

        $attendanceToday = RegistroAsistencia::query()
            ->with('empleado')
            ->whereDate('fecha', $today)
            ->get();

        $activeEmployees = $this->empleadosActivos($today);
        $markedIds = $attendanceToday->pluck('empleado_id')->unique();
        $permissionIds = $permissionsToday->pluck('empleado_id')->unique();

        return [
            'permisos' => $permissionsToday->map(fn (PermisoLaboral $permiso) => [
                'nombre' => $permiso->empleado?->nombre_completo,
                'detalle' => $permiso->tipo_label.' | '.$permiso->alcance_label.' | '.$this->formatearMinutosEtiqueta((int) ($permiso->minutos_contabilizados ?? 0)),
            ])->values()->all(),
            'faltas' => $activeEmployees
                ->filter(fn (Empleado $empleado) => ! $this->programacionLaboral->esDiaNoLaborable($today, $empleado->sucursal) && ! $markedIds->contains($empleado->id) && ! $permissionIds->contains($empleado->id))
                ->map(fn (Empleado $empleado) => [
                    'nombre' => $empleado->nombre_completo,
                    'detalle' => $empleado->sucursal.' - ausencia injustificada en la fecha',
                ])->values()->all(),
            'olvidos' => $attendanceToday
                ->filter(fn (RegistroAsistencia $registro) => $this->debeContarComoOlvidoMarcacion($registro))
                ->map(function (RegistroAsistencia $registro) {
                    $marcacion = $this->normalizarMarcacionAsistencia($registro);

                    return [
                        'nombre' => $registro->empleado?->nombre_completo,
                        'detalle' => 'Entrada: '.($marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--').' / Salida: '.($marcacion['salida'] ? substr($marcacion['salida'], 0, 5) : '--:--'),
                    ];
                })->values()->all(),
        ];
    }

    private function empleadosActivos(string $fecha, ?string $branch = null): Collection
    {
        return Empleado::query()
            ->withUltimaMarcacion()
            ->laboralVigente($fecha)
            ->when(filled($branch), fn ($query) => SucursalNormalizer::applyFilter($query, 'sucursal', $branch))
            ->get()
            ->filter(fn (Empleado $empleado) => $empleado->estaActivoLaboralmente($fecha))
            ->values();
    }

    private function contarFaltasEnRango(Carbon $start, Carbon $end, ?string $branch = null): int
    {
        return count($this->detalleFaltasEnRango($start, $end, $branch));
    }

    private function detalleFaltasEnRango(Carbon $start, Carbon $end, ?string $branch = null): array
    {
        $effectiveEnd = $this->limitarFinDeReporteHastaHoy($start, $end);

        if (! $effectiveEnd) {
            return [];
        }

        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $effectiveEnd->toDateString())
            ->get()
            ->groupBy(fn (RegistroAsistencia $registro) => $registro->empleado_id.'|'.$registro->fecha?->toDateString());

        $permissions = $this->filtrarIncidenciasPorSucursal(
            PermisoLaboral::query(),
            $branch
        )
            ->with('empleado')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $effectiveEnd->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $details = [];
        $current = $start->copy();

        while ($current->lte($effectiveEnd)) {
            $activeEmployees = $this->empleadosActivos($current->toDateString(), $branch);
            foreach ($activeEmployees as $empleado) {
                if ($this->programacionLaboral->esDiaNoLaborable($current, $empleado->sucursal)) {
                    continue;
                }

                $key = $empleado->id.'|'.$current->toDateString();
                if ($attendance->has($key)) {
                    continue;
                }

                $hasPermission = $permissions->contains(function (PermisoLaboral $permiso) use ($empleado, $current) {
                    return (int) $permiso->empleado_id === (int) $empleado->id
                        && $permiso->tipo !== 'falta'
                        && $permiso->fecha_inicio
                        && $permiso->fecha_fin
                        && $current->betweenIncluded($permiso->fecha_inicio, $permiso->fecha_fin);
                });

                if ($hasPermission) {
                    continue;
                }

                $details[] = [
                    'nombre' => $empleado->nombre_completo,
                    'detalle' => $current->format('d/m/Y').' - '.$empleado->sucursal.' - ausencia injustificada',
                ];
            }

            $current->addDay();
        }

        return $details;
    }

    private function limitarFinDeReporteHastaHoy(Carbon $start, Carbon $end): ?Carbon
    {
        $today = now()->startOfDay();
        $effectiveEnd = $end->copy()->startOfDay()->min($today);

        if ($effectiveEnd->lt($start->copy()->startOfDay())) {
            return null;
        }

        return $effectiveEnd;
    }

    private function filtrarAsistenciasPorSucursal($query, ?string $branch = null)
    {
        if (! filled($branch)) {
            return $query;
        }

        return $query->whereHas('empleado', function ($empleadoQuery) use ($branch) {
            SucursalNormalizer::applyFilter($empleadoQuery, 'sucursal', $branch);
        });
    }

    private function filtrarIncidenciasPorSucursal($query, ?string $branch = null)
    {
        if (! filled($branch)) {
            return $query;
        }

        return $query->whereHas('empleado', function ($empleadoQuery) use ($branch) {
            SucursalNormalizer::applyFilter($empleadoQuery, 'sucursal', $branch);
        });
    }

    private function eventosTardanzaPorFecha(Carbon $reference): array
    {
        $monthStart = $reference->copy()->startOfMonth()->toDateString();
        $monthEnd = $reference->copy()->endOfMonth()->toDateString();
        $toleranciaMensual = (int) config('asistencia.tolerancia_mensual_min', 35);

        $registros = RegistroAsistencia::query()
            ->with('empleado')
            ->whereDate('fecha', '>=', $monthStart)
            ->whereDate('fecha', '<=', $monthEnd)
            ->orderBy('fecha')
            ->orderBy('hora_entrada')
            ->get()
            ->filter(function (RegistroAsistencia $registro) {
                if (! $registro->empleado) {
                    return false;
                }

                return $this->programacionLaboral->resolverHorario($registro->empleado, $registro->fecha)['laborable'];
            });

        $eventos = [];
        $retrasoAcumuladoPorEmpleado = [];

        foreach ($registros as $registro) {
            $empleado = $registro->empleado;
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);
            $minutosRetraso = $this->calcularMinutosRetraso(
                $registro->hora_entrada,
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );

            if ($minutosRetraso <= 0) {
                continue;
            }

            $acumuladoAnterior = $retrasoAcumuladoPorEmpleado[$empleado->id] ?? 0;
            $acumuladoActual = $acumuladoAnterior + $minutosRetraso;
            $retrasoAcumuladoPorEmpleado[$empleado->id] = $acumuladoActual;

            $fecha = $registro->fecha?->format('Y-m-d');
            if (! $fecha) {
                continue;
            }

            $eventos[$fecha][] = [
                'empleado_id' => $empleado->id,
                'label' => $empleado->nombre_completo,
                'detail' => $this->formatearMinutosEtiqueta($minutosRetraso).' de atraso',
                'minutes_late' => $minutosRetraso,
                'entry_time' => ($entradaReal = $this->normalizarMarcacionAsistencia($registro)['entrada']) ? substr($entradaReal, 0, 5) : '--:--',
                'status' => $this->resolverEstadoMarcacionVisible($registro),
                'branch' => $empleado->sucursal ?: 'Sin sucursal',
                'tone' => $acumuladoActual > $toleranciaMensual ? 'black' : 'red',
            ];
        }

        return $eventos;
    }

    private function bloquesPendientes(): int
    {
        $pending = 0;

        foreach ([
            base_path('app/Models'),
            base_path('app/Http/Controllers'),
            base_path('app/Http/Middleware'),
            base_path('database/migrations'),
        ] as $path) {
            if (! is_dir($path) || count(glob($path.DIRECTORY_SEPARATOR.'*') ?: []) === 0) {
                $pending++;
            }
        }

        return $pending;
    }

    private function countOccurrences(string $file, string $needle): int
    {
        if (! file_exists($file)) {
            return 0;
        }

        return substr_count((string) file_get_contents($file), $needle);
    }

    private function esEntradaOmisionPorHoraTardia(?string $horaEntrada): bool
    {
        if (blank($horaEntrada)) {
            return true;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        if (! $entrada) {
            return false;
        }

        // Si marca a las 12:30:00 PM o después (ej: 12:30, 16:00), se considera omisión de entrada
        $corteOmision = Carbon::createFromTime(12, 30, 0);

        return $entrada->greaterThanOrEqualTo($corteOmision);
    }

    private function calcularMinutosRetraso(?string $horaEntrada, ?string $horaProgramada): int
    {
        if (blank($horaEntrada) || blank($horaProgramada)) {
            return 0;
        }

        // Si la marcación de entrada es a las 12:30 PM o posterior, se considera omisión de entrada y no un retraso de 8 horas
        if ($this->esEntradaOmisionPorHoraTardia($horaEntrada)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $programada = $this->parseTimeToCarbon($horaProgramada);

        if (! $entrada || ! $programada || $entrada->lessThanOrEqualTo($programada)) {
            return 0;
        }

        return $programada->diffInMinutes($entrada);
    }

    private function calcularMinutosTrabajados(?string $horaEntrada, ?string $horaSalida): int
    {
        if (blank($horaEntrada) || blank($horaSalida)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $salida = $this->parseTimeToCarbon($horaSalida);

        if (! $entrada || ! $salida || $salida->lessThanOrEqualTo($entrada)) {
            return 0;
        }

        return $entrada->diffInMinutes($salida);
    }

    private function debeContarComoOlvidoMarcacion(RegistroAsistencia $registro): bool
    {
        if (blank($registro->hora_entrada) || $this->esEntradaOmisionPorHoraTardia($registro->hora_entrada)) {
            return true;
        }

        if (! blank($this->horaSalidaReal($registro))) {
            return false;
        }

        return ! $this->salidaSiguePendienteDentroDeJornada($registro, $registro->empleado);
    }

    private function salidaSiguePendienteDentroDeJornada(RegistroAsistencia $registro, ?Empleado $empleado = null): bool
    {
        if (! $this->tieneSoloEntradaMarcada($registro)) {
            return false;
        }

        if (! $registro->fecha?->isToday()) {
            return false;
        }

        $horario = $empleado
            ? $this->programacionLaboral->resolverHorario($empleado, $registro->fecha)
            : ['laborable' => true, 'hora_salida' => config('asistencia.hora_salida')];

        if (($horario['laborable'] ?? true) === false) {
            return false;
        }

        $salidaProgramada = $this->combinarFechaYHora(
            $registro->fecha,
            $horario['hora_salida'] ?? config('asistencia.hora_salida')
        );

        if (! $salidaProgramada) {
            return true;
        }

        return now()->lessThanOrEqualTo($salidaProgramada);
    }

    private function tieneSoloEntradaMarcada(RegistroAsistencia $registro): bool
    {
        return $this->normalizarMarcacionAsistencia($registro)['solo_entrada'];
    }

    private function horaSalidaReal(RegistroAsistencia $registro): ?string
    {
        return $this->normalizarMarcacionAsistencia($registro)['salida'];
    }

    private function resolverEstadoRegistroPersonalizado(
        RegistroAsistencia $registro,
        bool $soloEntrada,
        ?string $horaSalidaReal,
        int $delay
    ): string {
        $marcacion = $this->normalizarMarcacionAsistencia($registro);
        $olvidoEntrada = blank($marcacion['entrada']) || $this->esEntradaOmisionPorHoraTardia($marcacion['entrada']);
        $olvidoSalida = blank($horaSalidaReal);

        if ($olvidoEntrada && $olvidoSalida) {
            return 'Sin entrada ni salida';
        }

        if ($olvidoEntrada) {
            return 'Olvido de entrada';
        }

        if ($soloEntrada) {
            return 'En su puesto';
        }

        if ($olvidoSalida) {
            return 'Olvido de salida';
        }

        if ($delay > 0) {
            return 'Ingreso con retraso';
        }

        return 'Marcacion completa';
    }

    private function parseTimeToCarbon(string $time): ?Carbon
    {
        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $time);
            } catch (\Exception $exception) {
                continue;
            }
        }

        return null;
    }

    private function combinarFechaYHora(Carbon|string|null $fecha, ?string $hora): ?Carbon
    {
        if (blank($fecha) || blank($hora)) {
            return null;
        }

        try {
            $horaCarbon = $this->parseTimeToCarbon($hora);

            if (! $horaCarbon) {
                return null;
            }

            return Carbon::parse($fecha instanceof Carbon ? $fecha->toDateString() : $fecha)
                ->setTime($horaCarbon->hour, $horaCarbon->minute, $horaCarbon->second);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function esSabado(Carbon|string|null $fecha): bool
    {
        if ($fecha instanceof Carbon) {
            return $fecha->dayOfWeek === Carbon::SATURDAY;
        }

        if (blank($fecha)) {
            return false;
        }

        try {
            return Carbon::parse($fecha)->dayOfWeek === Carbon::SATURDAY;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function esDiaNoLaborable(Carbon|string|null $fecha, ?string $sucursal = null): bool
    {
        return $this->programacionLaboral->esDiaNoLaborable($fecha, $sucursal);
    }

    private function estaDentroDeAgencia(RegistroAsistencia $registro): bool
    {
        $estado = $this->normalizarTextoDepartamento((string) ($registro->estado_marcacion ?? ''));
        $entrada = $this->normalizarHoraSimple($registro->hora_entrada);
        $salida = $this->normalizarHoraSimple($registro->hora_salida);

        if ($entrada !== null && $salida !== null && $entrada === $salida) {
            return true;
        }

        if ($estado !== '') {
            if (str_contains($estado, 'retorno') || str_contains($estado, 'entrada')) {
                return true;
            }

            if (str_contains($estado, 'salida')) {
                return false;
            }
        }

        return filled($registro->hora_entrada) && blank($registro->hora_salida);
    }

    private function normalizarTextoDepartamento(string $texto): string
    {
        return str($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function normalizarTexto(string $texto): string
    {
        return str($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function normalizarHoraSimple(?string $hora): ?string
    {
        if (blank($hora)) {
            return null;
        }

        try {
            return Carbon::parse($hora)->format('H:i:s');
        } catch (\Throwable) {
            return trim((string) $hora) !== '' ? trim((string) $hora) : null;
        }
    }

    private function normalizarMarcacionAsistencia(RegistroAsistencia $registro): array
    {
        $entrada = $this->normalizarHoraSimple($registro->hora_entrada);
        $salida = $this->normalizarHoraSimple($registro->hora_salida);
        $estado = $this->normalizarTextoDepartamento((string) ($registro->estado_marcacion ?? ''));
        $evento = $this->normalizarTextoDepartamento((string) ($registro->evento_biometrico ?? ''));

        $entradaExplicita = $this->marcacionEsEntradaExplicita($estado, $evento);
        $salidaExplicita = $this->marcacionEsSalidaExplicita($estado, $evento);

        if ($salidaExplicita && ! $entradaExplicita && filled($entrada) && blank($salida)) {
            $salida = $entrada;
            $entrada = null;
        }

        if (filled($entrada) xor filled($salida)) {
            [$entrada, $salida] = $this->inferirMarcacionPorHorario($registro, $entrada, $salida);
        }

        if (filled($entrada) && filled($salida) && $entrada === $salida) {
            $salida = null;
        }

        return [
            'entrada' => $entrada,
            'salida' => $salida,
            'solo_entrada' => filled($entrada) && blank($salida),
        ];
    }

    private function marcacionEsEntradaExplicita(string $estado, string $evento): bool
    {
        return str_contains($estado, 'entrada')
            || str_contains($estado, 'retorno')
            || str_contains($estado, 'ingreso')
            || str_contains($evento, 'retorno');
    }

    private function marcacionEsSalidaExplicita(string $estado, string $evento): bool
    {
        return str_contains($estado, 'salida')
            || str_contains($evento, 'boton de salida');
    }

    private function inferirMarcacionPorHorario(RegistroAsistencia $registro, ?string $entrada, ?string $salida): array
    {
        $empleado = $registro->empleado;

        if (! $empleado || ! $registro->fecha) {
            return [$entrada, $salida];
        }

        $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

        if (($horario['laborable'] ?? true) === false) {
            return [$entrada, $salida];
        }

        $horaEntrada = $this->parseTimeToCarbon((string) ($horario['hora_entrada'] ?? ''));
        $horaSalida = $this->parseTimeToCarbon((string) ($horario['hora_salida'] ?? ''));

        if (! $horaEntrada || ! $horaSalida) {
            return [$entrada, $salida];
        }

        $entradaReferencia = ((int) $horaEntrada->format('H')) * 60 + (int) $horaEntrada->format('i');
        $salidaReferencia = ((int) $horaSalida->format('H')) * 60 + (int) $horaSalida->format('i');
        $puntoMedio = (int) floor(($entradaReferencia + $salidaReferencia) / 2);

        if (filled($entrada) && blank($salida)) {
            $marca = $this->parseTimeToCarbon($entrada);

            if ($marca && (((int) $marca->format('H')) * 60 + (int) $marca->format('i')) >= $puntoMedio) {
                return [null, $entrada];
            }
        }

        if (blank($entrada) && filled($salida)) {
            $marca = $this->parseTimeToCarbon($salida);

            if ($marca && (((int) $marca->format('H')) * 60 + (int) $marca->format('i')) < $puntoMedio) {
                return [$salida, null];
            }
        }

        return [$entrada, $salida];
    }

    private function resolverEstadoMarcacionVisible(RegistroAsistencia $registro, ?array $marcacion = null): string
    {
        $marcacion ??= $this->normalizarMarcacionAsistencia($registro);

        if ($marcacion['solo_entrada'] ?? false) {
            return 'Entrada';
        }

        if (blank($marcacion['entrada']) && filled($marcacion['salida'])) {
            return 'Salida';
        }

        if (filled($marcacion['entrada']) && blank($marcacion['salida'])) {
            return 'Entrada';
        }

        return $registro->estado_marcacion ?: 'Sin estado';
    }

    private function formatearMinutosEtiqueta(int $minutos): string
    {
        if ($minutos <= 0) {
            return '0 min';
        }

        $horas = intdiv($minutos, 60);
        $restantes = $minutos % 60;

        if ($horas === 0) {
            return $restantes.' min';
        }

        if ($restantes === 0) {
            return $horas.' h';
        }

        return $horas.' h '.$restantes.' min';
    }

    private function formatearMinutos(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $restantes = $minutos % 60;

        return sprintf('%02d:%02d', $horas, $restantes);
    }

    private function departamentoDesdeTexto(?string $texto): ?string
    {
        $normalized = str($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();

        if ($normalized === '') {
            return null;
        }

        return match (true) {
            str_contains($normalized, 'la paz'), str_contains($normalized, 'lapaz') => 'la-paz',
            str_contains($normalized, 'oruro') => 'oruro',
            str_contains($normalized, 'potosi') => 'potosi',
            str_contains($normalized, 'cochabamba') => 'cochabamba',
            str_contains($normalized, 'chuquisaca'), str_contains($normalized, 'sucre') => 'chuquisaca',
            str_contains($normalized, 'tarija') => 'tarija',
            str_contains($normalized, 'santa cruz'), str_contains($normalized, 'santacruz') => 'santa-cruz',
            str_contains($normalized, 'beni'), str_contains($normalized, 'trinidad') => 'beni',
            str_contains($normalized, 'pando'), str_contains($normalized, 'cobija') => 'pando',
            default => null,
        };
    }

    private function etiquetaSucursalDepartamento(?string $departmentKey, ?string $branch): string
    {
        return match ($departmentKey) {
            'la-paz' => $this->esSucursalElAlto($branch) ? 'Regional El Alto' : 'Oficina Central La Paz',
            'oruro' => 'Sucursal Oruro',
            'potosi' => 'Sucursal Potosi',
            'cochabamba' => 'Sucursal Cochabamba',
            'chuquisaca' => 'Sucursal Sucre',
            'tarija' => 'Sucursal Tarija',
            'santa-cruz' => 'Sucursal Santa Cruz',
            'beni' => 'Sucursal Beni',
            'pando' => 'Sucursal Cobija',
            default => trim((string) $branch) !== '' ? trim((string) $branch) : 'Sin sucursal',
        };
    }

    private function construirPayloadDepartamento(
        string $name,
        string $branch,
        Collection $departmentEmployees,
        Collection $departmentAttendance,
        Collection $departmentDevices,
        ?string $departmentKey = null
    ): array {
        $latestAttendanceByEmployee = $departmentAttendance
            ->filter(fn (RegistroAsistencia $registro) => $registro->empleado_id)
            ->groupBy('empleado_id')
            ->map(function (Collection $rows) {
                return $rows->sortByDesc(function (RegistroAsistencia $registro) {
                    return implode('|', [
                        $registro->hora_salida ?? '',
                        $registro->hora_entrada ?? '',
                        optional($registro->updated_at)->timestamp ?? 0,
                        $registro->id ?? 0,
                    ]);
                })->first();
            });

        $insideEmployees = $latestAttendanceByEmployee
            ->filter(fn (RegistroAsistencia $registro) => $this->estaDentroDeAgencia($registro))
            ->map(function (RegistroAsistencia $registro) use ($departmentKey) {
                $empleado = $registro->empleado;
                $employeeDepartmentKey = $this->departamentoDesdeTexto($empleado?->sucursal) ?? $departmentKey;

                return [
                    'name' => $empleado?->nombre_completo ?? 'Sin personal',
                    'branch' => $this->etiquetaSucursalDepartamento($employeeDepartmentKey, $empleado?->sucursal),
                    'area' => $empleado?->area ?: 'Sin area',
                    'status' => $registro->estado_marcacion ?: 'Dentro de agencia',
                ];
            })
            ->sortBy('name')
            ->values();

        $marked = $departmentAttendance->pluck('empleado_id')->filter()->unique()->count();
        $working = $insideEmployees->count();
        $missing = max($departmentEmployees->count() - $marked, 0);
        $latestSync = $departmentDevices
            ->flatMap(function (BiometricoDispositivo $device) {
                return collect([$device->last_synced_mark_at, $device->last_seen_at])->filter();
            })
            ->sortByDesc(fn (Carbon $date) => $date->timestamp)
            ->first();
        $hasConnectedDevice = $departmentDevices->contains(function (BiometricoDispositivo $device) {
            return $device->last_seen_at !== null || $device->last_synced_mark_at !== null;
        });

        return [
            'name' => $name,
            'branch' => $branch,
            'marked' => $marked,
            'working' => $working,
            'missing' => $missing,
            'employees' => $departmentEmployees->count(),
            'people_in_agency' => $insideEmployees->take(18)->all(),
            'people_in_agency_total' => $insideEmployees->count(),
            'updated_at' => $latestSync?->format('H:i') ?? 'Sin sync',
            'sync_label' => $hasConnectedDevice && $latestSync
                ? 'Ultima sincronizacion '.strtolower($latestSync->translatedFormat('d/m/Y H:i'))
                : 'Sin sincronizacion automatica registrada',
        ];
    }

    private function esSucursalElAlto(?string $texto): bool
    {
        $normalized = $this->normalizarTextoDepartamento((string) $texto);

        return str_contains($normalized, 'el alto');
    }
}
