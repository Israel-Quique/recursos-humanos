<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Importacion;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
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

        while ($current->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $current->copy();
                $dayEvents = collect($events[$day->format('Y-m-d')] ?? []);
                $week[] = [
                    'date' => $day->format('Y-m-d'),
                    'label' => $day->day,
                    'is_current_month' => $day->month === $reference->month,
                    'is_today' => $day->isToday(),
                    'is_weekend' => $day->isWeekend(),
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
                'Los sabados no se consideran para el control de tardanzas',
                'La tolerancia mensual vigente se calcula en '.$this->formatearMinutosEtiqueta((int) config('asistencia.tolerancia_mensual_min')),
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

        return [
            'date' => $selectedDate->toDateString(),
            'date_label' => $selectedDate->format('d/m/Y'),
            'day_label' => ucfirst($selectedDate->locale('es')->isoFormat('dddd')),
            'is_today' => $selectedDate->isToday(),
            'is_saturday' => $selectedDate->dayOfWeek === Carbon::SATURDAY,
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
            'marcaciones' => $registrosDia->map(fn (RegistroAsistencia $registro) => [
                'nombre' => $registro->empleado?->nombre_completo,
                'entrada' => $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--',
                'salida' => $registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--',
                'estado' => $registro->estado_marcacion ?: 'Sin estado',
                'sucursal' => $registro->empleado?->sucursal ?: 'Sin sucursal',
            ])->all(),
        ];
    }

    public function historialImportaciones(): array
    {
        return Importacion::query()
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
            ->filter(fn (RegistroAsistencia $registro) => blank($registro->hora_entrada) || blank($registro->hora_salida))
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
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
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
            ->filter(fn (RegistroAsistencia $registro) => blank($registro->hora_entrada) || blank($registro->hora_salida))
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
                ->whereBetween('fecha', [$start, $end])
                ->count();

            return [
                'label' => $offset === 0
                    ? ucfirst($date->locale('es')->translatedFormat('M')).' (Actual)'
                    : ucfirst($date->locale('es')->translatedFormat('M')),
                'count' => $count,
                'active' => $offset === 0,
            ];
        });

        $max = max($months->max('count'), 1);

        return $months->map(fn (array $month) => [
            'label' => $month['label'],
            'height' => max(18, (int) round(($month['count'] / $max) * 100)).'%',
            'active' => $month['active'],
            'count' => $month['count'],
        ])->values()->all();
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
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
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
                ->filter(fn (RegistroAsistencia $registro) => blank($registro->hora_entrada) || blank($registro->hora_salida))
                ->map(fn (RegistroAsistencia $registro) => [
                    'nombre' => $registro->empleado?->nombre_completo ?? 'Sin personal',
                    'detalle' => $registro->fecha?->format('d/m/Y').' - Entrada: '.($registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--').' / Salida: '.($registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--'),
                ])->values()->all(),
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
            ->whereBetween('fecha', [$monthStart, $monthEnd])
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
            $delay = $this->calcularMinutosRetraso($registro->hora_entrada, $horario['hora_entrada']);
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
            ->values()
            ->map(fn (array $item) => [
                'nombre' => $item['name'],
                'sucursal' => $item['branch'],
                'dias_tarde' => $item['late_days'] ?? 0,
                'retraso' => $this->formatearMinutosEtiqueta($item['late_minutes'] ?? 0),
            ])
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

    public function reportePersonalizado(?int $employeeId, Carbon $start, Carbon $end, ?string $branch = null): ?array
    {
        if (! $employeeId) {
            return null;
        }

        $empleado = Empleado::query()->find($employeeId);
        if (! $empleado || ($branch && $empleado->sucursal !== $branch)) {
            return null;
        }

        $attendance = RegistroAsistencia::query()
            ->where('empleado_id', $employeeId)
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->get();

        $incidents = PermisoLaboral::query()
            ->where('empleado_id', $employeeId)
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $workedMinutes = 0;
        $lateMinutes = 0;
        $lateDays = 0;
        $rows = [];

        foreach ($attendance as $registro) {
            $horario = $this->programacionLaboral->resolverHorario($empleado, $registro->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            $worked = $this->calcularMinutosTrabajados($registro->hora_entrada, $registro->hora_salida);
            $delay = $this->calcularMinutosRetraso($registro->hora_entrada, $horario['hora_entrada']);
            $workedMinutes += $worked;
            $lateMinutes += $delay;
            if ($delay > 0) {
                $lateDays++;
            }

            $rows[] = [
                'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                'entrada' => $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--',
                'salida' => $registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--',
                'horas' => $this->formatearMinutos($worked),
                'retraso' => $this->formatearMinutosEtiqueta($delay),
                'estado' => $registro->estado_marcacion ?: 'Sin estado',
            ];
        }

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

    public function empleadosParaReportes(?string $branch = null): array
    {
        return Empleado::query()
            ->when(filled($branch), fn ($query) => $query->where('sucursal', $branch))
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get()
            ->map(fn (Empleado $empleado) => [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre_completo,
            ])->all();
    }

    public function sucursalesParaReportes(): array
    {
        return Empleado::query()
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->orderBy('sucursal')
            ->distinct()
            ->pluck('sucursal')
            ->all();
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

        return $base->map(function (array $department, string $key) use ($employees, $attendances) {
            $departmentEmployees = $employees->filter(fn (Empleado $empleado) => $this->departamentoDesdeTexto($empleado->sucursal) === $key);
            $departmentAttendance = $attendances->filter(fn (RegistroAsistencia $registro) => $this->departamentoDesdeTexto($registro->empleado?->sucursal) === $key);

            $marked = $departmentAttendance->pluck('empleado_id')->unique()->count();
            $working = $departmentAttendance->filter(fn (RegistroAsistencia $registro) => filled($registro->hora_entrada))->count();
            $missing = max($departmentEmployees->count() - $marked, 0);

            return [
                'name' => $department['name'],
                'branch' => $departmentEmployees->isNotEmpty()
                    ? $departmentEmployees->pluck('sucursal')->filter()->unique()->join(', ')
                    : $department['branch'],
                'marked' => $marked,
                'working' => $working,
                'missing' => $missing,
                'employees' => $departmentEmployees->count(),
            ];
        })->all();
    }

    public function estadoBiometricos(): array
    {
        return [
            ['department' => 'La Paz', 'branch' => 'Oficina Central', 'ip' => '10.14.1.15', 'connected' => true, 'last_sync' => 'Hace 12 segundos'],
            ['department' => 'Santa Cruz', 'branch' => 'Regional Santa Cruz', 'ip' => '10.14.4.21', 'connected' => true, 'last_sync' => 'Hace 21 segundos'],
            ['department' => 'Cochabamba', 'branch' => 'Regional Cochabamba', 'ip' => '10.14.3.18', 'connected' => true, 'last_sync' => 'Hace 18 segundos'],
            ['department' => 'Tarija', 'branch' => 'Sucursal Tarija', 'ip' => '10.14.7.11', 'connected' => false, 'last_sync' => 'Sin conexion desde las 07:42'],
            ['department' => 'Beni', 'branch' => 'Sucursal Trinidad', 'ip' => '10.14.8.16', 'connected' => false, 'last_sync' => 'Sin conexion desde las 08:03'],
        ];
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
                ->filter(fn (RegistroAsistencia $registro) => blank($registro->hora_entrada) || blank($registro->hora_salida))
                ->map(fn (RegistroAsistencia $registro) => [
                    'nombre' => $registro->empleado?->nombre_completo,
                    'detalle' => 'Entrada: '.($registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--').' / Salida: '.($registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--'),
                ])->values()->all(),
        ];
    }

    private function empleadosActivos(string $fecha, ?string $branch = null): Collection
    {
        return Empleado::query()
            ->when(filled($branch), fn ($query) => $query->where('sucursal', $branch))
            ->where(function ($query) use ($fecha) {
                $query->whereNull('fecha_despido')
                    ->orWhereDate('fecha_despido', '>=', $fecha);
            })
            ->get();
    }

    private function contarFaltasEnRango(Carbon $start, Carbon $end, ?string $branch = null): int
    {
        return count($this->detalleFaltasEnRango($start, $end, $branch));
    }

    private function detalleFaltasEnRango(Carbon $start, Carbon $end, ?string $branch = null): array
    {
        $attendance = $this->filtrarAsistenciasPorSucursal(
            RegistroAsistencia::query(),
            $branch
        )
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (RegistroAsistencia $registro) => $registro->empleado_id.'|'.$registro->fecha?->toDateString());

        $permissions = $this->filtrarIncidenciasPorSucursal(
            PermisoLaboral::query(),
            $branch
        )
            ->with('empleado')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $end->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $details = [];
        $current = $start->copy();

        while ($current->lte($end)) {
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

    private function filtrarAsistenciasPorSucursal($query, ?string $branch = null)
    {
        if (! filled($branch)) {
            return $query;
        }

        return $query->whereHas('empleado', function ($empleadoQuery) use ($branch) {
            $empleadoQuery->where('sucursal', $branch);
        });
    }

    private function filtrarIncidenciasPorSucursal($query, ?string $branch = null)
    {
        if (! filled($branch)) {
            return $query;
        }

        return $query->whereHas('empleado', function ($empleadoQuery) use ($branch) {
            $empleadoQuery->where('sucursal', $branch);
        });
    }

    private function eventosTardanzaPorFecha(Carbon $reference): array
    {
        $monthStart = $reference->copy()->startOfMonth()->toDateString();
        $monthEnd = $reference->copy()->endOfMonth()->toDateString();
        $toleranciaMensual = (int) config('asistencia.tolerancia_mensual_min', 35);

        $registros = RegistroAsistencia::query()
            ->with('empleado')
            ->whereBetween('fecha', [$monthStart, $monthEnd])
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
            $minutosRetraso = $this->calcularMinutosRetraso($registro->hora_entrada, $horario['hora_entrada']);

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
                'entry_time' => $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--',
                'status' => $registro->estado_marcacion ?: 'Sin estado',
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

    private function calcularMinutosRetraso(?string $horaEntrada, ?string $horaProgramada): int
    {
        if (blank($horaEntrada) || blank($horaProgramada)) {
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
        $normalized = strtolower((string) str($texto)->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->trim());

        if ($normalized === '') {
            return null;
        }

        return match (true) {
            str_contains($normalized, 'la paz') => 'la-paz',
            str_contains($normalized, 'oruro') => 'oruro',
            str_contains($normalized, 'potosi') => 'potosi',
            str_contains($normalized, 'cochabamba') => 'cochabamba',
            str_contains($normalized, 'chuquisaca'), str_contains($normalized, 'sucre') => 'chuquisaca',
            str_contains($normalized, 'tarija') => 'tarija',
            str_contains($normalized, 'santa cruz') => 'santa-cruz',
            str_contains($normalized, 'beni'), str_contains($normalized, 'trinidad') => 'beni',
            str_contains($normalized, 'pando'), str_contains($normalized, 'cobija') => 'pando',
            default => null,
        };
    }
}
