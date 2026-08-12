<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AuditoriaService;
use App\Services\ProgramacionLaboralService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalPage extends Component
{
    use WithPagination;

    #[Url(as: 'vista')]
    public string $vista = 'personal';
    public string $search = '';
    public string $sucursalFiltro = '';
    public string $ordenMarcaciones = 'fecha_reciente';
    public int $registrosPage = 1;
    public ?int $detailEmpleadoId = null;
    public ?int $editingEmpleadoId = null;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDetailModal = false;
    public bool $showPdfModal = false;
    public bool $showDeleteModal = false;
    public bool $showDeleteRegistroModal = false;
    public ?int $pendingDeleteEmpleadoId = null;
    public ?int $pendingDeleteRegistroId = null;
    public string $pendingDeleteEmpleadoNombre = '';
    public string $pendingDeleteRegistroLabel = '';
    public string $nombre = '';
    public string $apellido = '';
    public string $codigoBiometrico = '';
    public string $area = '';
    public string $sucursal = '';
    public string $fechaNacimiento = '';
    public string $fechaContratacion = '';
    public string $fechaDespido = '';
    public string $editNombre = '';
    public string $editApellido = '';
    public string $editCodigoBiometrico = '';
    public string $editArea = '';
    public string $editSucursal = '';
    public string $editFechaNacimiento = '';
    public string $editFechaContratacion = '';
    public string $editFechaDespido = '';
    public string $detailReferenceMonth = '';
    public string $pdfReferenceMonth = '';
    public string $detailMarkingFilter = 'todas';
    public array $detailEmpleado = [];
    public array $detailMonthOptions = [];
    public ?int $pdfEmpleadoId = null;
    public array $pdfMonthOptions = [];
    public array $pdfEmpleado = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        if (! in_array($this->vista, ['personal', 'marcaciones', 'control'], true)) {
            $this->vista = 'personal';
        }

        $this->detailReferenceMonth = $this->referenceMonth()->format('Y-m');
        $this->pdfReferenceMonth = $this->referenceMonth()->format('Y-m');
    }

    public function setVista(string $vista): void
    {
        if (! in_array($vista, ['personal', 'marcaciones', 'control'], true)) {
            return;
        }

        $this->vista = $vista;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveEmpleado(): void
    {
        $data = $this->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'codigoBiometrico' => ['nullable', 'string', 'max:50', 'unique:empleados,codigo_biometrico'],
            'area' => ['nullable', 'string', 'max:120'],
            'sucursal' => ['required', 'string', 'max:120'],
            'fechaNacimiento' => ['nullable', 'date'],
        ], [
            'nombre.required' => 'Ingresa el nombre del personal.',
            'apellido.required' => 'Ingresa el apellido del personal.',
            'sucursal.required' => 'Ingresa la sucursal.',
        ]);

        $horarioRegional = $this->programacionLaboral()->obtenerHorarioRegional($data['sucursal']);

        $empleado = Empleado::query()->create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'codigo_biometrico' => $data['codigoBiometrico'] ?: null,
            'area' => trim((string) ($data['area'] ?? '')),
            'sucursal' => $data['sucursal'],
            'fecha_nacimiento' => $data['fechaNacimiento'] ?: null,
            'hora_entrada_programada' => $horarioRegional?->hora_entrada ?: config('asistencia.hora_entrada'),
            'hora_salida_programada' => $horarioRegional?->hora_salida ?: config('asistencia.hora_salida'),
            'fecha_contratacion' => now()->toDateString(),
            'fecha_despido' => null,
            'created_by' => auth()->id(),
        ]);

        app(AuditoriaService::class)->registrar(
            'Personal',
            'crear',
            'Se registro un nuevo integrante del personal.',
            $empleado,
            null,
            $this->snapshotEmpleado($empleado)
        );

        $this->reset(['nombre', 'apellido', 'codigoBiometrico', 'area', 'sucursal', 'fechaNacimiento', 'fechaDespido']);
        $this->resetPage();
        $this->showCreateModal = false;

        session()->flash('status', 'Personal registrado correctamente.');
    }

    public function openEditModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->editingEmpleadoId = $empleado->id;
        $this->editNombre = $empleado->nombre;
        $this->editApellido = $empleado->apellido;
        $this->editCodigoBiometrico = $empleado->codigo_biometrico ?: '';
        $this->editArea = $empleado->area;
        $this->editSucursal = $empleado->sucursal;
        $this->editFechaNacimiento = $empleado->fecha_nacimiento?->toDateString() ?? '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingEmpleadoId = null;
        $this->resetValidation();
    }

    public function openDetailModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->detailEmpleadoId = $empleado->id;
        $this->detailMonthOptions = $this->monthOptionsForEmpleado($empleado);
        $this->detailReferenceMonth = $this->resolveInitialDetailReferenceMonth($empleado);
        $this->detailMarkingFilter = 'todas';
        $this->detailEmpleado = $this->armarFichaEmpleado($empleadoId, $this->detailMonthCarbon());

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailEmpleadoId = null;
        $this->detailReferenceMonth = $this->referenceMonth()->format('Y-m');
        $this->detailMarkingFilter = 'todas';
        $this->detailEmpleado = [];
        $this->detailMonthOptions = [];
    }

    public function openPdfModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->pdfEmpleadoId = $empleado->id;
        $this->pdfMonthOptions = $this->monthOptionsForEmpleado($empleado);
        $this->pdfReferenceMonth = $this->resolveInitialMonthReference($empleado, $this->pdfMonthOptions);
        $this->pdfEmpleado = $this->armarFichaEmpleado($empleadoId, $this->pdfMonthCarbon());
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfEmpleadoId = null;
        $this->pdfReferenceMonth = $this->referenceMonth()->format('Y-m');
        $this->pdfMonthOptions = [];
        $this->pdfEmpleado = [];
    }

    public function openDeleteModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->pendingDeleteEmpleadoId = $empleado->id;
        $this->pendingDeleteEmpleadoNombre = $empleado->nombre_completo;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteEmpleadoId = null;
        $this->pendingDeleteEmpleadoNombre = '';
    }

    public function openDeleteRegistroModal(int $registroId): void
    {
        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($registroId);

        $this->pendingDeleteRegistroId = $registro->id;
        $this->pendingDeleteRegistroLabel = sprintf(
            '%s - %s',
            $registro->empleado?->nombre_completo ?? 'Sin empleado',
            $registro->fecha?->format('d/m/Y') ?? 'Sin fecha'
        );
        $this->showDeleteRegistroModal = true;
    }

    public function closeDeleteRegistroModal(): void
    {
        $this->showDeleteRegistroModal = false;
        $this->pendingDeleteRegistroId = null;
        $this->pendingDeleteRegistroLabel = '';
    }

    public function updatedDetailReferenceMonth(): void
    {
        $this->refreshDetailEmpleado();
    }

    public function updatedDetailMarkingFilter(): void
    {
        $this->refreshDetailEmpleado();
    }

    public function updatedPdfReferenceMonth(): void
    {
        $this->refreshPdfEmpleado();
    }

    public function setDetailMarkingFilter(string $filter): void
    {
        if (! in_array($filter, ['entrada', 'salida'], true)) {
            return;
        }

        $this->detailMarkingFilter = $this->detailMarkingFilter === $filter ? 'todas' : $filter;
        $this->refreshDetailEmpleado();
    }

    private function refreshDetailEmpleado(): void
    {
        if (! $this->showDetailModal || ! $this->detailEmpleadoId) {
            return;
        }

        $this->detailEmpleado = $this->armarFichaEmpleado($this->detailEmpleadoId, $this->detailMonthCarbon());
    }

    private function refreshPdfEmpleado(): void
    {
        if (! $this->showPdfModal || ! $this->pdfEmpleadoId) {
            return;
        }

        $this->pdfEmpleado = $this->armarFichaEmpleado($this->pdfEmpleadoId, $this->pdfMonthCarbon());
    }

    public function descargarPdfEmpleado(): void
    {
        if (blank($this->pdfEmpleado)) {
            return;
        }

        $this->dispatch('print-empleado-pdf');
    }

    public function updateEmpleado(): void
    {
        $this->validate([
            'editNombre' => ['required', 'string', 'max:120'],
            'editApellido' => ['required', 'string', 'max:120'],
            'editCodigoBiometrico' => ['nullable', 'string', 'max:50', Rule::unique('empleados', 'codigo_biometrico')->ignore($this->editingEmpleadoId)],
            'editArea' => ['nullable', 'string', 'max:120'],
            'editSucursal' => ['required', 'string', 'max:120'],
            'editFechaNacimiento' => ['nullable', 'date'],
        ], [
            'editNombre.required' => 'Ingresa el nombre del personal.',
            'editApellido.required' => 'Ingresa el apellido del personal.',
            'editSucursal.required' => 'Ingresa la sucursal.',
        ]);

        $empleado = Empleado::query()->findOrFail($this->editingEmpleadoId);
        $antes = $this->snapshotEmpleado($empleado);

        $empleado->update([
            'nombre' => $this->editNombre,
            'apellido' => $this->editApellido,
            'codigo_biometrico' => $this->editCodigoBiometrico ?: null,
            'area' => trim($this->editArea),
            'sucursal' => $this->editSucursal,
            'fecha_nacimiento' => $this->editFechaNacimiento ?: null,
            'created_by' => $empleado->created_by,
            'deleted_by' => $empleado->deleted_by,
        ]);

        app(AuditoriaService::class)->registrar(
            'Personal',
            'editar',
            'Se actualizaron los datos de un integrante del personal.',
            $empleado->fresh(),
            $antes,
            $this->snapshotEmpleado($empleado->fresh())
        );

        $this->showEditModal = false;
        $this->editingEmpleadoId = null;
        $this->resetValidation();
        $this->resetPage();

        session()->flash('status', 'Personal actualizado correctamente.');
    }

    public function deleteEmpleado(): void
    {
        if (! $this->pendingDeleteEmpleadoId) {
            return;
        }

        $empleado = Empleado::query()->findOrFail($this->pendingDeleteEmpleadoId);
        $antes = $this->snapshotEmpleado($empleado);
        $empleado->deleted_by = auth()->id();
        $empleado->save();
        $empleado->delete();

        app(AuditoriaService::class)->registrar(
            'Personal',
            'eliminar',
            'Se elimino un integrante del personal.',
            $empleado,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteModal();
        $this->resetPage();
        session()->flash('status', 'Personal marcado como eliminado.');
    }

    public function deleteRegistroAsistencia(): void
    {
        if (! $this->pendingDeleteRegistroId) {
            return;
        }

        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($this->pendingDeleteRegistroId);
        $antes = $this->snapshotRegistro($registro);
        $registro->delete();

        app(AuditoriaService::class)->registrar(
            'Marcaciones',
            'eliminar',
            'Se elimino una marcacion del historial reciente.',
            $registro,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteRegistroModal();
        $this->resetPage('registrosPage');
        session()->flash('status', 'Marcacion eliminada correctamente.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetPage('registrosPage');
    }

    public function updatingSucursalFiltro(): void
    {
        $this->resetPage();
        $this->resetPage('registrosPage');
    }

    public function updatingOrdenMarcaciones(): void
    {
        $this->resetPage('registrosPage');
    }

    public function render()
    {
        $referenceMonth = $this->referenceMonth();
        $monthStart = $referenceMonth->copy()->startOfMonth()->toDateString();
        $monthEnd = $referenceMonth->copy()->endOfMonth()->toDateString();

        $registrosPorNombre = $this->registrosPorNombre($monthStart, $monthEnd);
        $searchOperator = $this->caseInsensitiveLikeOperator();

        $empleadosQuery = Empleado::query()
            ->with(['asistencias' => function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('fecha', [$monthStart, $monthEnd])
                    ->orderByDesc('fecha');
            }]);

        if (filled($this->search)) {
            $empleadosQuery->where(function ($query) use ($searchOperator) {
                $query->where('codigo_biometrico', $searchOperator, "%{$this->search}%")
                    ->orWhere('nombre', $searchOperator, "%{$this->search}%")
                    ->orWhere('apellido', $searchOperator, "%{$this->search}%");
            });
        }

        if (filled($this->sucursalFiltro)) {
            SucursalNormalizer::applyFilter($empleadosQuery, 'sucursal', $this->sucursalFiltro);
        }

        $sucursales = SucursalNormalizer::optionsFromValues(Empleado::query()
            ->select('sucursal')
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->distinct()
            ->orderBy('sucursal')
            ->pluck('sucursal'));

        $empleadosResumen = (clone $empleadosQuery)
            ->orderByDesc('created_at')
            ->get();

        $empleadosResumen->transform(function (Empleado $empleado) use ($registrosPorNombre, $referenceMonth) {
            return $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $referenceMonth);
        });

        $totalMinutosMes = $empleadosResumen->sum(function (Empleado $empleado) {
            return $empleado->resumen_asistencia['minutos_mes'] ?? 0;
        });

        $empleados = (clone $empleadosQuery)
            ->orderByDesc('created_at')
            ->paginate(10);

        $empleados->getCollection()->transform(function (Empleado $empleado) use ($registrosPorNombre, $referenceMonth) {
            return $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $referenceMonth);
        });

        $totalHorasMes = $this->formatearMinutos((int) $totalMinutosMes);

        $registros = RegistroAsistencia::query()
            ->with('empleado')
            ->whereHas('empleado')
            ->where($this->excludeSaturdayRecords())
            ->when(filled($this->search), function ($query) {
                $searchOperator = $this->caseInsensitiveLikeOperator();

                $query->whereHas('empleado', function ($empleadoQuery) use ($searchOperator) {
                    $empleadoQuery->where(function ($nestedQuery) use ($searchOperator) {
                        $nestedQuery->where('codigo_biometrico', $searchOperator, "%{$this->search}%")
                            ->orWhere('nombre', $searchOperator, "%{$this->search}%")
                            ->orWhere('apellido', $searchOperator, "%{$this->search}%");
                    });
                });
            })
            ->when(filled($this->sucursalFiltro), function ($query) {
                $query->whereHas('empleado', function ($empleadoQuery) {
                    SucursalNormalizer::applyFilter($empleadoQuery, 'sucursal', $this->sucursalFiltro);
                });
            })
            ->tap(fn ($query) => $this->aplicarOrdenMarcaciones($query))
            ->paginate(10, ['*'], 'registrosPage');

        $registros->getCollection()->transform(function (RegistroAsistencia $registro) {
            return (object) [
                'id' => $registro->id,
                'empleado' => $registro->empleado,
                'fecha' => $registro->fecha,
                'fecha_formateada' => $registro->fecha?->format('d/m/Y'),
                'dia' => $registro->fecha?->locale('es')->isoFormat('dddd'),
                'hora_entrada' => $registro->hora_entrada,
                'hora_salida' => $registro->hora_salida,
                'estado_marcacion' => $registro->estado_marcacion,
            ];
        });

        return view('livewire.personal', [
            'empleados' => $empleados,
            'registros' => $registros,
            'mes_resumen' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'sucursales' => $sucursales,
        ])->layout('layouts.app', ['title' => 'Registro de personal']);
    }

    private function aplicarOrdenMarcaciones(Builder $query): Builder
    {
        return match ($this->ordenMarcaciones) {
            'fecha_antigua' => $query
                ->orderBy('fecha')
                ->orderBy('created_at'),
            'hora_asc' => $query
                ->orderBy('hora_entrada')
                ->orderByDesc('fecha')
                ->orderByDesc('created_at'),
            'hora_desc' => $query
                ->orderByDesc('hora_entrada')
                ->orderByDesc('fecha')
                ->orderByDesc('created_at'),
            'nombre_asc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderBy('empleados.nombre')
                ->orderBy('empleados.apellido')
                ->orderByDesc('registros_asistencia.fecha')
                ->orderByDesc('registros_asistencia.created_at'),
            'nombre_desc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderByDesc('empleados.nombre')
                ->orderByDesc('empleados.apellido')
                ->orderByDesc('registros_asistencia.fecha')
                ->orderByDesc('registros_asistencia.created_at'),
            default => $query
                ->orderByDesc('fecha')
                ->orderByDesc('created_at'),
        };
    }

    private function resumenMensualEmpleado(Empleado $empleado, EloquentCollection $registrosAsistencia, Carbon $referenceMonth): array
    {
        $toleranciaMensual = (int) config('asistencia.tolerancia_mensual_min', 35);
        $fechaReferencia = $referenceMonth->isSameMonth(now())
            ? now()->toDateString()
            : null;

        $minutosTrabajados = 0;
        $minutosRetraso = 0;
        $diasTarde = 0;
        $olvidosMarcacion = 0;
        $registroHoy = null;

        foreach ($registrosAsistencia as $asistencia) {
            $horario = $this->programacionLaboral()->resolverHorario($empleado, $asistencia->fecha);

            if (! $horario['laborable']) {
                continue;
            }

            if ($fechaReferencia && $asistencia->fecha?->toDateString() === $fechaReferencia) {
                $registroHoy = $asistencia;
            }

            $horaSalidaReal = $this->horaSalidaReal($asistencia);

            $minutosTrabajados += $this->calcularMinutosTrabajados($asistencia->hora_entrada, $horaSalidaReal);

            if ($this->debeContarComoOlvidoMarcacion($asistencia, $empleado)) {
                $olvidosMarcacion++;
            }

            $min = $this->calcularMinutosRetraso(
                $asistencia->hora_entrada,
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );
            $minutosRetraso += $min;
            if ($min > 0) {
                $diasTarde++;
            }
        }

        return [
            'entrada_hoy' => $registroHoy?->hora_entrada ? substr($registroHoy->hora_entrada, 0, 5) : '--:--',
            'salida_hoy' => $registroHoy && ($horaSalidaHoy = $this->horaSalidaReal($registroHoy))
                ? substr($horaSalidaHoy, 0, 5)
                : '--:--',
            'verificacion_hoy' => $registroHoy?->tipo_verificacion ?: 'Sin registro',
            'estado_hoy' => $registroHoy
                ? ($this->tieneSoloEntradaMarcada($registroHoy) ? 'En su puesto' : ($registroHoy->estado_marcacion ?: 'Sin registro'))
                : 'Sin registro',
            'evento_hoy' => $registroHoy?->evento_biometrico ?: 'Sin evento',
            'horas_mes' => $this->formatearMinutos($minutosTrabajados),
            'minutos_mes' => $minutosTrabajados,
            'retraso_mes' => $minutosRetraso,
            'retraso_mes_formateado' => $this->formatearMinutosEtiqueta($minutosRetraso),
            'dias_tarde' => $diasTarde,
            'saldo_retraso' => max($toleranciaMensual - $minutosRetraso, 0),
            'saldo_retraso_formateado' => $this->formatearMinutosEtiqueta(max($toleranciaMensual - $minutosRetraso, 0)),
            'tolerancia_mensual' => $toleranciaMensual,
            'tolerancia_mensual_formateada' => $this->formatearMinutosEtiqueta($toleranciaMensual),
            'exceso_retraso' => max($minutosRetraso - $toleranciaMensual, 0),
            'estado_retraso' => $minutosRetraso > $toleranciaMensual ? 'Excedido' : 'Dentro de tolerancia',
            'olvidos_marcacion' => $olvidosMarcacion,
        ];
    }

    private function hidratarResumenEmpleado(Empleado $empleado, \Illuminate\Support\Collection $registrosPorNombre, Carbon $referenceMonth): Empleado
    {
        $empleado->area = $this->normalizarArea($empleado->area);
        $registrosUnificados = $this->registrosUnificadosEmpleado($empleado, $registrosPorNombre);

        $empleado->resumen_asistencia = $this->resumenMensualEmpleado(
            $empleado,
            new EloquentCollection($registrosUnificados->all()),
            $referenceMonth
        );

        return $empleado;
    }

    private function armarFichaEmpleado(int $empleadoId, ?Carbon $referenceMonth = null): array
    {
        $referenceMonth ??= $this->referenceMonth();
        $registrosPorNombre = $this->registrosPorNombre(
            $referenceMonth->copy()->startOfMonth()->toDateString(),
            $referenceMonth->copy()->endOfMonth()->toDateString()
        );

        $empleado = Empleado::query()
            ->with(['asistencias' => function ($query) use ($referenceMonth) {
                $query->whereBetween('fecha', [
                    $referenceMonth->copy()->startOfMonth()->toDateString(),
                    $referenceMonth->copy()->endOfMonth()->toDateString(),
                ])->orderByDesc('fecha');
            }])
            ->findOrFail($empleadoId);

        $empleado = $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $referenceMonth);
        $horarioReferencia = $this->programacionLaboral()->resolverHorario($empleado, $referenceMonth);
        $registrosDetalle = $this->registrosUnificadosEmpleado($empleado, $registrosPorNombre)
            ->filter(function (RegistroAsistencia $registro) use ($empleado) {
                $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);

                return $horario['laborable'];
            })
            ->map(function (RegistroAsistencia $registro) use ($empleado) {
                $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);
                $minutosRetraso = $this->calcularMinutosRetraso(
                    $registro->hora_entrada,
                    $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
                );
                $olvidoEntrada = blank($registro->hora_entrada);
                $soloEntrada = $this->tieneSoloEntradaMarcada($registro);
                $horaSalidaReal = $this->horaSalidaReal($registro);
                $olvidoSalida = blank($horaSalidaReal);

                if ($olvidoEntrada && $olvidoSalida) {
                    $estadoRegistro = 'Sin entrada ni salida';
                } elseif ($olvidoEntrada) {
                    $estadoRegistro = 'Olvido de entrada';
                } elseif ($soloEntrada) {
                    $estadoRegistro = 'En su puesto';
                } elseif ($olvidoSalida) {
                    $estadoRegistro = 'Olvido de salida';
                } elseif ($minutosRetraso > 0) {
                    $estadoRegistro = 'Ingreso con retraso';
                } else {
                    $estadoRegistro = 'Marcacion completa';
                }

                return [
                    'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                    'dia' => ucfirst($registro->fecha?->locale('es')->isoFormat('dddd') ?? 'Sin dia'),
                    'entrada' => $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--',
                    'salida' => $horaSalidaReal ? substr($horaSalidaReal, 0, 5) : '--:--',
                    'retraso' => $this->formatearMinutosEtiqueta($minutosRetraso),
                    'estado' => $estadoRegistro,
                    'estado_biometrico' => $soloEntrada ? 'En su puesto' : ($registro->estado_marcacion ?: 'Sin registro'),
                ];
            })
            ->filter(function (array $registro) {
                return match ($this->detailMarkingFilter) {
                    'entrada' => $registro['entrada'] !== '--:--',
                    'salida' => $registro['salida'] !== '--:--',
                    default => true,
                };
            })
            ->values()
            ->all();

        return [
            'nombre_completo' => $empleado->nombre_completo,
            'codigo_biometrico' => $empleado->codigo_biometrico ?: 'Sin asignar',
            'area' => $empleado->area ?: 'Sin area',
            'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
            'fecha_nacimiento' => $empleado->fecha_nacimiento?->format('d/m/Y'),
            'fecha_contratacion' => $empleado->fecha_contratacion?->format('d/m/Y') ?? 'Sin fecha',
            'fecha_despido' => $empleado->fecha_despido?->format('d/m/Y'),
            'hora_entrada_programada' => $horarioReferencia['hora_entrada']
                ? substr($horarioReferencia['hora_entrada'], 0, 5)
                : substr((string) config('asistencia.hora_entrada'), 0, 5),
            'hora_salida_programada' => $horarioReferencia['hora_salida']
                ? substr($horarioReferencia['hora_salida'], 0, 5)
                : substr((string) config('asistencia.hora_salida'), 0, 5),
            'horas_mes' => $empleado->resumen_asistencia['horas_mes'],
            'retraso_mes' => $empleado->resumen_asistencia['retraso_mes_formateado'],
            'saldo_mes' => $empleado->resumen_asistencia['saldo_retraso_formateado'],
            'dias_tarde' => $empleado->resumen_asistencia['dias_tarde'],
            'olvidos_marcacion' => $empleado->resumen_asistencia['olvidos_marcacion'],
            'estado_retraso' => $empleado->resumen_asistencia['estado_retraso'],
            'tolerancia_mensual' => $empleado->resumen_asistencia['tolerancia_mensual_formateada'],
            'entrada_hoy' => $empleado->resumen_asistencia['entrada_hoy'],
            'salida_hoy' => $empleado->resumen_asistencia['salida_hoy'],
            'verificacion_hoy' => $empleado->resumen_asistencia['verificacion_hoy'],
            'estado_hoy' => $empleado->resumen_asistencia['estado_hoy'],
            'marcaciones_mes' => $registrosDetalle,
            'mes_referencia' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
        ];
    }

    private function monthOptionsForEmpleado(Empleado $empleado): array
    {
        $inicio = ($empleado->fecha_contratacion ?? now())->copy()->startOfMonth();
        $fin = $this->referenceMonth()->copy()->startOfMonth();

        if ($inicio->greaterThan($fin)) {
            $inicio = $fin->copy();
        }

        $options = [];
        $cursor = $inicio->copy();

        while ($cursor->lte($fin)) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('es')->translatedFormat('F Y')),
            ];
            $cursor->addMonthNoOverflow();
        }

        return array_reverse($options);
    }

    private function resolveInitialDetailReferenceMonth(Empleado $empleado): string
    {
        $options = $this->monthOptionsForEmpleado($empleado);

        return $this->resolveInitialMonthReference($empleado, $options);
    }

    private function resolveInitialMonthReference(Empleado $empleado, ?array $options = null): string
    {
        $options ??= $this->monthOptionsForEmpleado($empleado);

        return $options[0]['value'] ?? $this->referenceMonth()->format('Y-m');
    }

    private function detailMonthCarbon(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->detailReferenceMonth)->startOfMonth();
    }

    private function pdfMonthCarbon(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->pdfReferenceMonth)->startOfMonth();
    }

    private function calcularMinutosTrabajados(?string $horaEntrada, ?string $horaSalida): int
    {
        if (blank($horaEntrada) || blank($horaSalida)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $salida = $this->parseTimeToCarbon($horaSalida);

        if (! $entrada || ! $salida) {
            return 0;
        }

        if ($salida->lessThanOrEqualTo($entrada)) {
            return 0;
        }

        return $entrada->diffInMinutes($salida);
    }

    private function tieneSoloEntradaMarcada(RegistroAsistencia $registro): bool
    {
        if (blank($registro->hora_entrada)) {
            return false;
        }

        if (blank($registro->hora_salida)) {
            return true;
        }

        return $this->normalizarHoraComparable($registro->hora_entrada) === $this->normalizarHoraComparable($registro->hora_salida);
    }

    private function horaSalidaReal(RegistroAsistencia $registro): ?string
    {
        if ($this->tieneSoloEntradaMarcada($registro)) {
            return null;
        }

        return blank($registro->hora_salida) ? null : $registro->hora_salida;
    }

    private function debeContarComoOlvidoMarcacion(RegistroAsistencia $registro, Empleado $empleado): bool
    {
        if (blank($registro->hora_entrada)) {
            return true;
        }

        if (! blank($this->horaSalidaReal($registro))) {
            return false;
        }

        return ! $this->salidaSiguePendienteDentroDeJornada($registro, $empleado);
    }

    private function salidaSiguePendienteDentroDeJornada(RegistroAsistencia $registro, Empleado $empleado): bool
    {
        if (! $this->tieneSoloEntradaMarcada($registro)) {
            return false;
        }

        if (! $registro->fecha?->isToday()) {
            return false;
        }

        $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);

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

    private function normalizarHoraComparable(?string $hora): ?string
    {
        if (blank($hora)) {
            return null;
        }

        $parsed = $this->parseTimeToCarbon($hora);

        return $parsed?->format('H:i:s') ?: trim((string) $hora);
    }

    private function calcularMinutosRetraso(?string $horaEntrada, ?string $horaProgramada): int
    {
        if (blank($horaEntrada) || blank($horaProgramada)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $programada = $this->parseTimeToCarbon($horaProgramada);

        if (! $entrada || ! $programada) {
            return 0;
        }

        if ($entrada->lessThanOrEqualTo($programada)) {
            return 0;
        }

        return $programada->diffInMinutes($entrada);
    }

    private function parseTimeToCarbon(string $time): ?Carbon
    {
        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $time);
            } catch (\Exception $e) {
                // seguir intentando con otros formatos
            }
        }

        return null;
    }

    private function combinarFechaYHora(Carbon|string|null $fecha, ?string $hora): ?Carbon
    {
        if (blank($fecha) || blank($hora)) {
            return null;
        }

        $horaCarbon = $this->parseTimeToCarbon($hora);

        if (! $horaCarbon) {
            return null;
        }

        try {
            return Carbon::parse($fecha instanceof Carbon ? $fecha->toDateString() : $fecha)
                ->setTime($horaCarbon->hour, $horaCarbon->minute, $horaCarbon->second);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function formatearMinutos(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $restantes = $minutos % 60;

        return sprintf('%02d:%02d', $horas, $restantes);
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

    private function normalizarArea(?string $area): string
    {
        $texto = trim((string) $area);
        $normalizado = $this->normalizarTextoPerfil($texto);

        if ($normalizado === 'sin area asignada') {
            return '';
        }

        return $texto;
    }

    private function normalizarTextoPerfil(?string $texto): string
    {
        return Str::of((string) $texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function esSabado(Carbon|string|null $fecha): bool
    {
        if ($fecha instanceof Carbon) {
            return $fecha->isSaturday();
        }

        if (blank($fecha)) {
            return false;
        }

        return Carbon::parse((string) $fecha)->isSaturday();
    }

    private function excludeSaturdayRecords(): \Closure
    {
        return function (Builder $query) {
            $driver = $query->getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                $query->whereRaw('EXTRACT(DOW FROM fecha) <> 6');

                return;
            }

            if ($driver === 'sqlite') {
                $query->whereRaw("strftime('%w', fecha) <> '6'");

                return;
            }

            $query->whereRaw('DAYOFWEEK(fecha) <> 7');
        };
    }

    private function caseInsensitiveLikeOperator(): string
    {
        return Empleado::query()->getConnection()->getDriverName() === 'pgsql'
            ? 'ilike'
            : 'like';
    }

    private function programacionLaboral(): ProgramacionLaboralService
    {
        return app(ProgramacionLaboralService::class);
    }

    private function referenceMonth(): Carbon
    {
        $referenceDate = RegistroAsistencia::query()->max('fecha');

        $dt = $referenceDate ? Carbon::parse($referenceDate) : now();

        // No permitir que la referencia sea una fecha futura. Limitar a "ahora".
        if ($dt->greaterThan(Carbon::now())) {
            $dt = Carbon::now();
        }

        return $dt;
    }

    private function registrosPorNombre(string $monthStart, string $monthEnd): \Illuminate\Support\Collection
    {
        return RegistroAsistencia::query()
            ->with('empleado')
            ->whereHas('empleado')
            ->where($this->excludeSaturdayRecords())
            ->whereBetween('fecha', [$monthStart, $monthEnd])
            ->orderByDesc('fecha')
            ->get()
            ->filter(fn (RegistroAsistencia $registro) => filled($registro->empleado?->nombre_completo))
            ->groupBy(fn (RegistroAsistencia $registro) => $this->normalizarTextoPerfil($registro->empleado?->nombre_completo));
    }

    private function registrosUnificadosEmpleado(Empleado $empleado, \Illuminate\Support\Collection $registrosPorNombre): \Illuminate\Support\Collection
    {
        $nombrePerfil = $this->normalizarTextoPerfil($empleado->nombre_completo);
        $registrosPorRelacion = $empleado->asistencias instanceof EloquentCollection
            ? $empleado->asistencias
            : new EloquentCollection($empleado->asistencias?->all() ?? []);
        $registrosPorNombrePerfil = $registrosPorNombre->get($nombrePerfil, collect());

        return $registrosPorRelacion
            ->concat($registrosPorNombrePerfil)
            ->unique(fn (RegistroAsistencia $registro) => $registro->id ?: md5(
                ($registro->fecha?->toDateString() ?? '').
                '|'.($registro->hora_entrada ?? '').
                '|'.($registro->hora_salida ?? '')
            ))
            ->sortByDesc(fn (RegistroAsistencia $registro) => optional($registro->fecha)->timestamp ?? 0)
            ->values();
    }

    private function snapshotEmpleado(Empleado $empleado): array
    {
        return [
            'id' => $empleado->id,
            'nombre' => $empleado->nombre,
            'apellido' => $empleado->apellido,
            'codigo_biometrico' => $empleado->codigo_biometrico,
            'area' => $empleado->area,
            'sucursal' => $empleado->sucursal,
            'fecha_nacimiento' => $empleado->fecha_nacimiento?->toDateString(),
            'fecha_contratacion' => $empleado->fecha_contratacion?->toDateString(),
            'fecha_despido' => $empleado->fecha_despido?->toDateString(),
        ];
    }

    private function snapshotRegistro(RegistroAsistencia $registro): array
    {
        return [
            'id' => $registro->id,
            'empleado_id' => $registro->empleado_id,
            'empleado' => $registro->empleado?->nombre_completo,
            'importacion_id' => $registro->importacion_id,
            'fecha' => $registro->fecha?->toDateString(),
            'hora_entrada' => $registro->hora_entrada,
            'hora_salida' => $registro->hora_salida,
            'tipo_verificacion' => $registro->tipo_verificacion,
            'estado_marcacion' => $registro->estado_marcacion,
            'evento_biometrico' => $registro->evento_biometrico,
            'observacion' => $registro->observacion,
        ];
    }
}
