<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AuditoriaService;
use App\Services\ProgramacionLaboralService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalEspecialPage extends Component
{
    use WithPagination;

    // Pestaña activa: 'marcaciones' o 'personal'
    public string $tab = 'marcaciones';

    // Filtros
    public string $search = '';
    public string $sucursalFiltro = '';
    public string $tipoRangoFiltro = 'dia'; // 'dia' o 'rango'
    public string $fechaDiaFiltro = '';
    public string $fechaInicioFiltro = '';
    public string $fechaFinFiltro = '';

    // Modal Registro Marcación Especial (Crear/Editar)
    public bool $showRegistroModal = false;
    public ?int $editingRegistroId = null;
    public ?int $empleadoId = null;
    public string $fecha = '';
    public string $horaEntrada = '';
    public string $horaSalida = '';
    public string $observacion = '';

    // Modal Eliminar Marcación Especial
    public bool $showDeleteRegistroModal = false;
    public ?int $pendingDeleteRegistroId = null;
    public string $pendingDeleteRegistroLabel = '';

    // Modal Crear Nuevo Personal Especial
    public bool $showCreateEmpleadoModal = false;
    public string $nuevoNombre = '';
    public string $nuevoApellido = '';
    public string $nuevoCodigoBiometrico = '';
    public string $nuevaArea = '';
    public string $nuevaSucursal = '';
    public string $nuevaHoraEntrada = '';
    public string $nuevaHoraSalida = '';

    // Modal Vincular Personal Existente
    public bool $showVincularModal = false;
    public ?int $vincularEmpleadoId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $now = now();
        $this->fechaDiaFiltro = $now->toDateString();
        $this->fechaInicioFiltro = $now->copy()->startOfWeek()->toDateString();
        $this->fechaFinFiltro = $now->toDateString();
        $this->fecha = $now->toDateString();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['marcaciones', 'personal'], true)) {
            $this->tab = $tab;
            $this->resetPage();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSucursalFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingTipoRangoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingFechaDiaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingFechaInicioFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingFechaFinFiltro(): void
    {
        $this->resetPage();
    }

    // ─────────────────────────────────────────────────────────────
    // GESTIÓN DE MARCACIONES ESPECIALES
    // ─────────────────────────────────────────────────────────────

    public function openRegistroModal(?int $registroId = null, ?int $preselectedEmpleadoId = null): void
    {
        $this->resetValidation();

        if ($registroId) {
            $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($registroId);
            $this->editingRegistroId = $registro->id;
            $this->empleadoId = $registro->empleado_id;
            $this->fecha = $registro->fecha?->toDateString() ?? now()->toDateString();
            $this->horaEntrada = $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '';
            $this->horaSalida = $registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '';
            $this->observacion = $registro->observacion ?? '';
        } else {
            $this->editingRegistroId = null;
            $this->empleadoId = $preselectedEmpleadoId;
            $this->fecha = now()->toDateString();
            $this->horaEntrada = '08:30';
            $this->horaSalida = '17:30';
            $this->observacion = '';
        }

        $this->showRegistroModal = true;
    }

    public function closeRegistroModal(): void
    {
        $this->showRegistroModal = false;
        $this->editingRegistroId = null;
        $this->empleadoId = null;
        $this->fecha = '';
        $this->horaEntrada = '';
        $this->horaSalida = '';
        $this->observacion = '';
    }

    public function saveRegistro(): void
    {
        $this->validate([
            'empleadoId' => ['required', 'integer', 'exists:empleados,id'],
            'fecha' => ['required', 'date'],
            'horaEntrada' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/'],
            'horaSalida' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ], [
            'empleadoId.required' => 'Selecciona al personal especial.',
            'empleadoId.exists' => 'El personal seleccionado no existe.',
            'fecha.required' => 'Ingresa la fecha de la asistencia.',
            'horaEntrada.required' => 'Ingresa la hora de entrada.',
            'horaEntrada.regex' => 'El formato de hora de entrada debe ser HH:MM (ej. 08:30).',
            'horaSalida.regex' => 'El formato de hora de salida debe ser HH:MM (ej. 17:30).',
        ]);

        $formattedEntrada = strlen($this->horaEntrada) === 5 ? $this->horaEntrada . ':00' : $this->horaEntrada;
        $formattedSalida = filled($this->horaSalida)
            ? (strlen($this->horaSalida) === 5 ? $this->horaSalida . ':00' : $this->horaSalida)
            : null;

        $estadoMarcacion = filled($formattedSalida) ? 'Marcacion completa' : 'Solo entrada';
        $eventoBiometrico = 'Ingreso y salida especial RRHH';

        if ($this->editingRegistroId) {
            $registro = RegistroAsistencia::query()->findOrFail($this->editingRegistroId);
            $antes = $this->snapshotRegistro($registro);

            $registro->update([
                'empleado_id' => $this->empleadoId,
                'fecha' => $this->fecha,
                'hora_entrada' => $formattedEntrada,
                'hora_salida' => $formattedSalida,
                'tipo_verificacion' => 'Especial',
                'estado_marcacion' => $estadoMarcacion,
                'evento_biometrico' => $eventoBiometrico,
                'observacion' => $this->observacion ?: 'Registro especial RRHH',
                'updated_by' => auth()->id(),
            ]);

            app(AuditoriaService::class)->registrar(
                'Personal Especial',
                'actualizar_marcacion',
                'Se actualizó la marcación especial del personal.',
                $registro,
                $antes,
                $this->snapshotRegistro($registro)
            );

            session()->flash('status', 'Marcación especial actualizada correctamente.');
        } else {
            // Verificar si ya existe un registro para este empleado en esta fecha
            $registroExistente = RegistroAsistencia::query()
                ->where('empleado_id', $this->empleadoId)
                ->whereDate('fecha', $this->fecha)
                ->first();

            if ($registroExistente) {
                $antes = $this->snapshotRegistro($registroExistente);

                $registroExistente->update([
                    'hora_entrada' => $formattedEntrada,
                    'hora_salida' => $formattedSalida,
                    'tipo_verificacion' => 'Especial',
                    'estado_marcacion' => $estadoMarcacion,
                    'evento_biometrico' => $eventoBiometrico,
                    'observacion' => $this->observacion ?: 'Registro especial RRHH (sobreescrito)',
                    'updated_by' => auth()->id(),
                ]);

                app(AuditoriaService::class)->registrar(
                    'Personal Especial',
                    'actualizar_marcacion',
                    'Se actualizó el registro de asistencia existente para la fecha con datos especiales.',
                    $registroExistente,
                    $antes,
                    $this->snapshotRegistro($registroExistente)
                );

                session()->flash('status', 'Ya existía una marcación para esta fecha y fue actualizada con los datos especiales.');
            } else {
                $nuevoRegistro = RegistroAsistencia::query()->create([
                    'empleado_id' => $this->empleadoId,
                    'fecha' => $this->fecha,
                    'hora_entrada' => $formattedEntrada,
                    'hora_salida' => $formattedSalida,
                    'tipo_verificacion' => 'Especial',
                    'estado_marcacion' => $estadoMarcacion,
                    'evento_biometrico' => $eventoBiometrico,
                    'observacion' => $this->observacion ?: 'Registro especial RRHH',
                    'created_by' => auth()->id(),
                ]);

                app(AuditoriaService::class)->registrar(
                    'Personal Especial',
                    'crear_marcacion',
                    'Se registró una nueva entrada y salida especial para el personal.',
                    $nuevoRegistro,
                    null,
                    $this->snapshotRegistro($nuevoRegistro)
                );

                session()->flash('status', 'Marcación especial guardada correctamente.');
            }
        }

        $this->closeRegistroModal();
    }

    public function openDeleteRegistroModal(int $registroId): void
    {
        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($registroId);
        $this->pendingDeleteRegistroId = $registro->id;
        $this->pendingDeleteRegistroLabel = sprintf(
            '%s - %s (%s a %s)',
            $registro->empleado?->nombre_completo ?? 'Sin empleado',
            $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
            $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--',
            $registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--'
        );
        $this->showDeleteRegistroModal = true;
    }

    public function closeDeleteRegistroModal(): void
    {
        $this->showDeleteRegistroModal = false;
        $this->pendingDeleteRegistroId = null;
        $this->pendingDeleteRegistroLabel = '';
    }

    public function deleteRegistro(): void
    {
        if (!$this->pendingDeleteRegistroId) {
            return;
        }

        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($this->pendingDeleteRegistroId);
        $antes = $this->snapshotRegistro($registro);
        $registro->delete();

        app(AuditoriaService::class)->registrar(
            'Personal Especial',
            'eliminar_marcacion',
            'Se eliminó la marcación especial de asistencia.',
            $registro,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteRegistroModal();
        session()->flash('status', 'Marcación especial eliminada.');
    }

    // ─────────────────────────────────────────────────────────────
    // GESTIÓN DE PERSONAL ESPECIAL
    // ─────────────────────────────────────────────────────────────

    public function openCreateEmpleadoModal(): void
    {
        $this->resetValidation();
        $this->nuevoNombre = '';
        $this->nuevoApellido = '';
        $this->nuevoCodigoBiometrico = '';
        $this->nuevaArea = 'Operaciones';
        $this->nuevaSucursal = 'La Paz';
        $this->nuevaHoraEntrada = '08:30';
        $this->nuevaHoraSalida = '17:30';
        $this->showCreateEmpleadoModal = true;
    }

    public function closeCreateEmpleadoModal(): void
    {
        $this->showCreateEmpleadoModal = false;
        $this->nuevoNombre = '';
        $this->nuevoApellido = '';
        $this->nuevoCodigoBiometrico = '';
        $this->nuevaArea = '';
        $this->nuevaSucursal = '';
    }

    public function saveNuevoEmpleadoEspecial(): void
    {
        $this->validate([
            'nuevoNombre' => ['required', 'string', 'max:120'],
            'nuevoApellido' => ['required', 'string', 'max:120'],
            'nuevoCodigoBiometrico' => ['nullable', 'string', 'max:50', 'unique:empleados,codigo_biometrico'],
            'nuevaArea' => ['required', 'string', 'max:120'],
            'nuevaSucursal' => ['required', 'string', 'max:120'],
            'nuevaHoraEntrada' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'nuevaHoraSalida' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
        ], [
            'nuevoNombre.required' => 'Ingresa el nombre.',
            'nuevoApellido.required' => 'Ingresa el apellido.',
            'nuevoCodigoBiometrico.unique' => 'Este código biométrico ya está en uso.',
            'nuevaArea.required' => 'Ingresa el área.',
            'nuevaSucursal.required' => 'Selecciona la sucursal.',
        ]);

        $empleado = Empleado::query()->create([
            'nombre' => trim($this->nuevoNombre),
            'apellido' => trim($this->nuevoApellido),
            'codigo_biometrico' => filled($this->nuevoCodigoBiometrico) ? trim($this->nuevoCodigoBiometrico) : null,
            'area' => trim($this->nuevaArea),
            'sucursal' => $this->nuevaSucursal,
            'es_especial' => true,
            'hora_entrada_programada' => filled($this->nuevaHoraEntrada) ? $this->nuevaHoraEntrada . ':00' : '08:30:00',
            'hora_salida_programada' => filled($this->nuevaHoraSalida) ? $this->nuevaHoraSalida . ':00' : '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        app(AuditoriaService::class)->registrar(
            'Personal Especial',
            'crear_personal_especial',
            'Se registró un nuevo integrante con régimen especial.',
            $empleado,
            null,
            $empleado->toArray()
        );

        $this->closeCreateEmpleadoModal();
        session()->flash('status', 'Personal especial registrado exitosamente.');
    }

    public function openVincularModal(): void
    {
        $this->resetValidation();
        $this->vincularEmpleadoId = null;
        $this->showVincularModal = true;
    }

    public function closeVincularModal(): void
    {
        $this->showVincularModal = false;
        $this->vincularEmpleadoId = null;
    }

    public function vincularEmpleadoComoEspecial(): void
    {
        $this->validate([
            'vincularEmpleadoId' => ['required', 'integer', 'exists:empleados,id'],
        ], [
            'vincularEmpleadoId.required' => 'Selecciona al empleado que deseas designar como especial.',
        ]);

        $empleado = Empleado::query()->findOrFail($this->vincularEmpleadoId);
        $antes = $empleado->toArray();
        $empleado->update(['es_especial' => true]);

        app(AuditoriaService::class)->registrar(
            'Personal Especial',
            'designar_especial',
            'Se designó a un empleado existente como personal con régimen especial.',
            $empleado,
            $antes,
            $empleado->toArray()
        );

        $this->closeVincularModal();
        session()->flash('status', "Se designó a {$empleado->nombre_completo} como personal especial.");
    }

    public function vincularDirecto(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);
        $antes = $empleado->toArray();
        $empleado->update(['es_especial' => true]);

        app(AuditoriaService::class)->registrar(
            'Personal Especial',
            'vincular_directo',
            'Se vinculó directamente al personal como especial desde el buscador.',
            $empleado,
            $antes,
            $empleado->toArray()
        );

        session()->flash('status', "✓ {$empleado->nombre_completo} ha sido vinculado como Personal Especial.");
    }

    public function toggleEspecial(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);
        $nuevoEstado = ! $empleado->es_especial;
        $antes = $empleado->toArray();

        $empleado->update(['es_especial' => $nuevoEstado]);

        app(AuditoriaService::class)->registrar(
            'Personal Especial',
            'cambiar_estado_especial',
            $nuevoEstado ? 'Se activó régimen especial' : 'Se removió régimen especial',
            $empleado,
            $antes,
            $empleado->toArray()
        );

        session()->flash('status', $nuevoEstado
            ? "Se activó el régimen especial para {$empleado->nombre_completo}."
            : "Se quitó el régimen especial a {$empleado->nombre_completo}."
        );
    }

    // ─────────────────────────────────────────────────────────────
    // AUXILIARES
    // ─────────────────────────────────────────────────────────────

    private function snapshotRegistro(RegistroAsistencia $registro): array
    {
        return [
            'id' => $registro->id,
            'empleado_id' => $registro->empleado_id,
            'empleado' => $registro->empleado?->nombre_completo,
            'fecha' => $registro->fecha?->toDateString(),
            'hora_entrada' => $registro->hora_entrada,
            'hora_salida' => $registro->hora_salida,
            'tipo_verificacion' => $registro->tipo_verificacion,
            'estado_marcacion' => $registro->estado_marcacion,
            'evento_biometrico' => $registro->evento_biometrico,
            'observacion' => $registro->observacion,
        ];
    }

    public function calcularHorasTrabajadas(?string $entrada, ?string $salida): string
    {
        if (blank($entrada) || blank($salida)) {
            return '--:--';
        }

        try {
            $e = Carbon::parse($entrada);
            $s = Carbon::parse($salida);

            if ($s->lt($e)) {
                return '--:--';
            }

            $minutos = $e->diffInMinutes($s);
            $horas = intdiv($minutos, 60);
            $minsRestantes = $minutos % 60;

            return sprintf('%02d:%02d hrs', $horas, $minsRestantes);
        } catch (\Throwable $th) {
            return '--:--';
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        // Rango de fechas activo: 'dia' o 'rango'
        if ($this->tipoRangoFiltro === 'dia') {
            $startDate = filled($this->fechaDiaFiltro) ? $this->fechaDiaFiltro : now()->toDateString();
            $endDate = $startDate;
        } else {
            $startDate = filled($this->fechaInicioFiltro) ? $this->fechaInicioFiltro : now()->copy()->startOfWeek()->toDateString();
            $endDate = filled($this->fechaFinFiltro) ? $this->fechaFinFiltro : now()->toDateString();
            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }
        }

        // Candidatos sin régimen especial encontrados en el buscador para vincular directamente
        $candidatosVincular = collect();
        if (filled($this->search) && strlen(trim($this->search)) >= 2) {
            $term = '%' . trim($this->search) . '%';
            $candidatosVincular = Empleado::query()
                ->where('es_especial', false)
                ->where(function ($q) use ($term) {
                    $q->where('nombre', 'like', $term)
                        ->orWhere('apellido', 'like', $term)
                        ->orWhere('codigo_biometrico', 'like', $term);
                })
                ->take(5)
                ->get();
        }

        // Sucursales disponibles
        $sucursales = SucursalNormalizer::optionsFromValues(
            Empleado::query()->whereNotNull('sucursal')->distinct()->pluck('sucursal')
        );

        // Lista de empleados especiales para select
        $empleadosEspecialesList = Empleado::query()
            ->where('es_especial', true)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        // Empleados disponibles para vincular (que NO son especiales)
        $empleadosParaVincular = Empleado::query()
            ->where('es_especial', false)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->take(50)
            ->get();

        // Métricas
        $totalEspeciales = Empleado::query()->where('es_especial', true)->count();
        $registrosMesCount = RegistroAsistencia::query()
            ->whereHas('empleado', fn($q) => $q->where('es_especial', true))
            ->whereBetween('fecha', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();
        $registrosHoyCount = RegistroAsistencia::query()
            ->whereHas('empleado', fn($q) => $q->where('es_especial', true))
            ->whereDate('fecha', now()->toDateString())
            ->count();

        // Query principal de Registros de Marcaciones Especiales
        $registrosQuery = RegistroAsistencia::query()
            ->with(['empleado', 'creador'])
            ->where(function ($q) {
                $q->where('tipo_verificacion', 'Especial')
                    ->orWhereHas('empleado', fn($eq) => $eq->where('es_especial', true));
            })
            ->whereBetween('fecha', [$startDate, $endDate])
            ->when(filled($this->sucursalFiltro), function ($q) {
                $q->whereHas('empleado', fn($eq) => SucursalNormalizer::applyFilter($eq, 'sucursal', $this->sucursalFiltro));
            })
            ->when(filled($this->search), function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->whereHas('empleado', function ($eq) use ($term) {
                    $eq->where('nombre', 'like', $term)
                        ->orWhere('apellido', 'like', $term)
                        ->orWhere('codigo_biometrico', 'like', $term)
                        ->orWhere('area', 'like', $term);
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora_entrada');

        $registrosPaginados = $registrosQuery->paginate(15, ['*'], 'registrosPage');

        // Query de Personal Especial (pestaña 'personal')
        $personalQuery = Empleado::query()
            ->where('es_especial', true)
            ->when(filled($this->sucursalFiltro), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $this->sucursalFiltro))
            ->when(filled($this->search), function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('nombre', 'like', $term)
                        ->orWhere('apellido', 'like', $term)
                        ->orWhere('codigo_biometrico', 'like', $term)
                        ->orWhere('area', 'like', $term);
                });
            })
            ->withCount(['asistencias' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fecha', [$startDate, $endDate]);
            }])
            ->orderBy('nombre')
            ->orderBy('apellido');

        $personalPaginado = $personalQuery->paginate(15, ['*'], 'personalPage');

        return view('livewire.personal-especial', [
            'registros' => $registrosPaginados,
            'personalEspecial' => $personalPaginado,
            'empleadosEspecialesList' => $empleadosEspecialesList,
            'empleadosParaVincular' => $empleadosParaVincular,
            'candidatosVincular' => $candidatosVincular,
            'sucursales' => $sucursales,
            'totalEspeciales' => $totalEspeciales,
            'registrosMesCount' => $registrosMesCount,
            'registrosHoyCount' => $registrosHoyCount,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->layout('layouts.app', ['title' => 'Personal Especial']);
    }
}
