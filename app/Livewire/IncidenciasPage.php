<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Services\AuditoriaService;
use App\Services\ProgramacionLaboralService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class IncidenciasPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tipoFiltro = '';
    public string $mesFiltro = '';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingIncidenciaId = null;
    public ?int $pendingDeleteIncidenciaId = null;
    public string $pendingDeleteIncidenciaLabel = '';
    public string $empleadoId = '';
    public string $tipo = 'permiso';
    public string $alcance = 'dia_completo';
    public string $estado = 'aprobado';
    public string $fechaInicio = '';
    public string $fechaFin = '';
    public string $horaInicio = '';
    public string $horaFin = '';
    public string $motivo = '';
    public string $empleadoSearch = '';
    public string $editEmpleadoId = '';
    public string $editEmpleadoSearch = '';
    public string $editTipo = 'permiso';
    public string $editAlcance = 'dia_completo';
    public string $editEstado = 'aprobado';
    public string $editFechaInicio = '';
    public string $editFechaFin = '';
    public string $editHoraInicio = '';
    public string $editHoraFin = '';
    public string $editMotivo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $this->mesFiltro = now()->format('Y-m');
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingMesFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(string $value): void
    {
        $this->sincronizarReglaTipo($value, false);
    }

    public function updatedAlcance(string $value): void
    {
        $this->sincronizarReglaAlcance($value, false);
    }

    public function updatedEditTipo(string $value): void
    {
        $this->sincronizarReglaTipo($value, true);
    }

    public function updatedEditAlcance(string $value): void
    {
        $this->sincronizarReglaAlcance($value, true);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function openEditModal(int $incidenciaId): void
    {
        $incidencia = PermisoLaboral::query()->findOrFail($incidenciaId);

        $this->editingIncidenciaId = $incidencia->id;
        $this->editEmpleadoId = (string) $incidencia->empleado_id;
        $this->editTipo = $incidencia->tipo;
        $this->editAlcance = $incidencia->alcance ?: 'dia_completo';
        $this->editEstado = $incidencia->estado;
        $this->editFechaInicio = $incidencia->fecha_inicio?->toDateString() ?? '';
        $this->editFechaFin = $incidencia->fecha_fin?->toDateString() ?? '';
        $this->editHoraInicio = $incidencia->hora_inicio ? substr($incidencia->hora_inicio, 0, 5) : '';
        $this->editHoraFin = $incidencia->hora_fin ? substr($incidencia->hora_fin, 0, 5) : '';
        $this->editMotivo = $incidencia->motivo ?? '';
        $this->showEditModal = true;
        $this->resetValidation();
        $this->sincronizarReglaTipo($this->editTipo, true);
        $this->editEmpleadoSearch = $incidencia->empleado?->nombre_completo ?? '';
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingIncidenciaId = null;
        $this->editEmpleadoSearch = '';
        $this->resetValidation();
    }

    public function openDeleteModal(int $incidenciaId): void
    {
        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($incidenciaId);

        $this->pendingDeleteIncidenciaId = $incidencia->id;
        $this->pendingDeleteIncidenciaLabel = $incidencia->empleado?->nombre_completo
            ? $incidencia->empleado->nombre_completo.' - '.$incidencia->tipo_label
            : $incidencia->tipo_label;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteIncidenciaId = null;
        $this->pendingDeleteIncidenciaLabel = '';
    }

    public function saveIncidencia(): void
    {
        $data = $this->validate($this->rules(), $this->messages());
        $empleado = Empleado::query()->findOrFail((int) $data['empleadoId']);
        $minutos = $this->calcularMinutosContabilizados(
            $empleado,
            $data['tipo'],
            $data['alcance'],
            $data['fechaInicio'],
            $data['fechaFin'],
            $data['horaInicio'] ?? '',
            $data['horaFin'] ?? ''
        );

        $incidencia = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => $data['tipo'],
            'alcance' => $data['alcance'],
            'estado' => $data['estado'],
            'fecha_inicio' => $data['fechaInicio'],
            'fecha_fin' => $data['fechaFin'],
            'hora_inicio' => $this->normalizarHora($data['alcance'], $data['horaInicio'] ?? ''),
            'hora_fin' => $this->normalizarHora($data['alcance'], $data['horaFin'] ?? ''),
            'minutos_contabilizados' => $minutos,
            'motivo' => $data['motivo'] ?: null,
            'created_by' => auth()->id(),
        ]);

        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'crear',
            'Se registro una nueva incidencia laboral.',
            $incidencia,
            null,
            $this->snapshotIncidencia($incidencia->fresh('empleado'))
        );

        $this->closeCreateModal();
        $this->resetCreateForm();
        $this->resetPage();
        session()->flash('status', 'Incidencia registrada correctamente.');
    }

    public function updateIncidencia(): void
    {
        $data = $this->validate($this->editRules(), $this->messages());
        $empleado = Empleado::query()->findOrFail((int) $data['editEmpleadoId']);
        $minutos = $this->calcularMinutosContabilizados(
            $empleado,
            $data['editTipo'],
            $data['editAlcance'],
            $data['editFechaInicio'],
            $data['editFechaFin'],
            $data['editHoraInicio'] ?? '',
            $data['editHoraFin'] ?? ''
        );

        $incidencia = PermisoLaboral::query()->findOrFail((int) $data['editingIncidenciaId']);
        $antes = $this->snapshotIncidencia($incidencia->load('empleado'));
        $incidencia->update([
            'empleado_id' => $empleado->id,
            'tipo' => $data['editTipo'],
            'alcance' => $data['editAlcance'],
            'estado' => $data['editEstado'],
            'fecha_inicio' => $data['editFechaInicio'],
            'fecha_fin' => $data['editFechaFin'],
            'hora_inicio' => $this->normalizarHora($data['editAlcance'], $data['editHoraInicio'] ?? ''),
            'hora_fin' => $this->normalizarHora($data['editAlcance'], $data['editHoraFin'] ?? ''),
            'minutos_contabilizados' => $minutos,
            'motivo' => $data['editMotivo'] ?: null,
        ]);

        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'editar',
            'Se actualizo una incidencia laboral.',
            $incidencia->fresh('empleado'),
            $antes,
            $this->snapshotIncidencia($incidencia->fresh('empleado'))
        );

        $this->closeEditModal();
        $this->resetPage();
        session()->flash('status', 'Incidencia actualizada correctamente.');
    }

    public function deleteIncidencia(): void
    {
        if (! $this->pendingDeleteIncidenciaId) {
            return;
        }

        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($this->pendingDeleteIncidenciaId);
        $antes = $this->snapshotIncidencia($incidencia);
        $incidencia->delete();

        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'eliminar',
            'Se elimino una incidencia laboral.',
            $incidencia,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteModal();
        $this->resetPage();
        session()->flash('status', 'Incidencia eliminada correctamente.');
    }

    public function render()
    {
        $query = PermisoLaboral::query()->with('empleado');

        if (filled($this->search)) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->whereHas('empleado', function ($empleados) use ($term) {
                $empleados->whereRaw("LOWER(nombre || ' ' || COALESCE(apellido, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(codigo_biometrico, '')) LIKE ?", [$term]);
            });
        }

        if (filled($this->tipoFiltro)) {
            $query->where('tipo', $this->tipoFiltro);
        }

        if (filled($this->mesFiltro)) {
            $reference = Carbon::createFromFormat('Y-m', $this->mesFiltro)->startOfMonth();
            $query->whereDate('fecha_inicio', '<=', $reference->copy()->endOfMonth()->toDateString())
                ->whereDate('fecha_fin', '>=', $reference->copy()->startOfMonth()->toDateString());
        }

        $incidencias = $query->orderByDesc('fecha_inicio')->paginate(10);
        $empleados = Empleado::query()
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
        $empleadosFormulario = $this->filtrarEmpleadosFormulario($empleados, $this->empleadoSearch, $this->empleadoId);
        $empleadosEdicion = $this->filtrarEmpleadosFormulario($empleados, $this->editEmpleadoSearch, $this->editEmpleadoId);

        return view('livewire.incidencias', [
            'incidencias' => $incidencias,
            'empleados' => $empleados,
            'empleadosFormulario' => $empleadosFormulario,
            'empleadosEdicion' => $empleadosEdicion,
            'tipos' => $this->tiposDisponibles(),
            'alcances' => $this->alcancesDisponibles(),
        ])->layout('layouts.app', ['title' => 'Incidencias laborales']);
    }

    private function rules(): array
    {
        return [
            'empleadoId' => ['required', Rule::exists('empleados', 'id')],
            'tipo' => ['required', Rule::in(array_keys($this->tiposDisponibles()))],
            'alcance' => ['required', Rule::in(array_keys($this->alcancesDisponibles()))],
            'estado' => ['required', Rule::in(['aprobado', 'pendiente'])],
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['required', 'date', 'after_or_equal:fechaInicio'],
            'horaInicio' => ['nullable', 'date_format:H:i'],
            'horaFin' => ['nullable', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function editRules(): array
    {
        return [
            'editingIncidenciaId' => ['required', Rule::exists('permisos_laborales', 'id')],
            'editEmpleadoId' => ['required', Rule::exists('empleados', 'id')],
            'editTipo' => ['required', Rule::in(array_keys($this->tiposDisponibles()))],
            'editAlcance' => ['required', Rule::in(array_keys($this->alcancesDisponibles()))],
            'editEstado' => ['required', Rule::in(['aprobado', 'pendiente'])],
            'editFechaInicio' => ['required', 'date'],
            'editFechaFin' => ['required', 'date', 'after_or_equal:editFechaInicio'],
            'editHoraInicio' => ['nullable', 'date_format:H:i'],
            'editHoraFin' => ['nullable', 'date_format:H:i'],
            'editMotivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function messages(): array
    {
        return [
            'empleadoId.required' => 'Selecciona el personal.',
            'editEmpleadoId.required' => 'Selecciona el personal.',
            'fechaFin.after_or_equal' => 'La fecha final no puede ser menor a la inicial.',
            'editFechaFin.after_or_equal' => 'La fecha final no puede ser menor a la inicial.',
            'horaInicio.date_format' => 'La hora inicial debe tener formato HH:MM.',
            'horaFin.date_format' => 'La hora final debe tener formato HH:MM.',
            'editHoraInicio.date_format' => 'La hora inicial debe tener formato HH:MM.',
            'editHoraFin.date_format' => 'La hora final debe tener formato HH:MM.',
        ];
    }

    private function tiposDisponibles(): array
    {
        return [
            'permiso' => 'Permiso',
            'incidencia' => 'Incidencia',
            'cumpleanos' => 'Cumpleanos',
            'falta' => 'Falta',
        ];
    }

    private function alcancesDisponibles(): array
    {
        return [
            'manana' => 'Toda la manana',
            'tarde' => 'Toda la tarde',
            'dia_completo' => 'Todo el dia',
            'medio_dia' => 'Medio dia',
            'horas' => 'Por horas',
        ];
    }

    private function sincronizarReglaTipo(string $tipo, bool $editing): void
    {
        if ($tipo !== 'cumpleanos') {
            return;
        }

        if ($editing) {
            $this->editAlcance = 'medio_dia';
            $this->editFechaFin = $this->editFechaInicio ?: $this->editFechaFin;
            $this->editHoraInicio = '';
            $this->editHoraFin = '';

            return;
        }

        $this->alcance = 'medio_dia';
        $this->fechaFin = $this->fechaInicio ?: $this->fechaFin;
        $this->horaInicio = '';
        $this->horaFin = '';
    }

    private function sincronizarReglaAlcance(string $alcance, bool $editing): void
    {
        if ($alcance === 'horas') {
            return;
        }

        if ($editing) {
            $this->editHoraInicio = '';
            $this->editHoraFin = '';

            return;
        }

        $this->horaInicio = '';
        $this->horaFin = '';
    }

    private function normalizarHora(string $alcance, ?string $hora): ?string
    {
        if ($alcance !== 'horas' || blank($hora)) {
            return null;
        }

        return $hora.':00';
    }

    private function calcularMinutosContabilizados(
        Empleado $empleado,
        string $tipo,
        string $alcance,
        string $fechaInicio,
        string $fechaFin,
        string $horaInicio,
        string $horaFin
    ): int {
        if ($tipo === 'cumpleanos') {
            $alcance = 'medio_dia';
            $fechaFin = $fechaInicio;
            $horaInicio = '';
            $horaFin = '';
        }

        if ($alcance === 'horas' && (blank($horaInicio) || blank($horaFin) || $horaFin <= $horaInicio)) {
            $field = $this->showEditModal ? 'editHoraFin' : 'horaFin';

            throw ValidationException::withMessages([
                $field => 'Define un bloque horario valido para esta incidencia.',
            ]);
        }

        $programacion = app(ProgramacionLaboralService::class);
        $start = Carbon::parse($fechaInicio);
        $end = Carbon::parse($fechaFin);
        $minutes = 0;

        while ($start->lte($end)) {
            $minutes += $programacion->minutosIncidencia($empleado, $start, $alcance, $horaInicio, $horaFin);
            $start->addDay();
        }

        return $minutes;
    }

    private function resetCreateForm(): void
    {
        $this->empleadoId = '';
        $this->empleadoSearch = '';
        $this->tipo = 'permiso';
        $this->alcance = 'dia_completo';
        $this->estado = 'aprobado';
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();
        $this->horaInicio = '';
        $this->horaFin = '';
        $this->motivo = '';
    }

    private function snapshotIncidencia(PermisoLaboral $incidencia): array
    {
        return [
            'id' => $incidencia->id,
            'empleado_id' => $incidencia->empleado_id,
            'empleado' => $incidencia->empleado?->nombre_completo,
            'tipo' => $incidencia->tipo,
            'alcance' => $incidencia->alcance,
            'estado' => $incidencia->estado,
            'fecha_inicio' => $incidencia->fecha_inicio?->toDateString(),
            'fecha_fin' => $incidencia->fecha_fin?->toDateString(),
            'hora_inicio' => $incidencia->hora_inicio,
            'hora_fin' => $incidencia->hora_fin,
            'minutos_contabilizados' => $incidencia->minutos_contabilizados,
            'motivo' => $incidencia->motivo,
        ];
    }

    private function filtrarEmpleadosFormulario($empleados, string $search, string $selectedId)
    {
        $term = trim(mb_strtolower($search));

        if ($term === '') {
            return $empleados;
        }

        return $empleados
            ->filter(function (Empleado $empleado) use ($term, $selectedId) {
                if ($selectedId !== '' && (string) $empleado->id === $selectedId) {
                    return true;
                }

                $texto = mb_strtolower(
                    $empleado->nombre_completo.' '.
                    ($empleado->codigo_biometrico ?? '').' '.
                    ($empleado->sucursal ?? '')
                );

                return str_contains($texto, $term);
            })
            ->values();
    }
}
