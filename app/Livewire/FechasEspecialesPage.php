<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Services\AuditoriaService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class FechasEspecialesPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tipoFiltro = '';
    public string $sucursalFiltro = '';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingFechaId = null;
    public ?int $pendingDeleteFechaId = null;
    public string $pendingDeleteFechaLabel = '';
    public string $fecha = '';
    public string $sucursal = 'TODAS';
    public string $nombre = '';
    public string $descripcion = '';
    public string $tipo = 'feriado';
    public string $horaEntrada = '';
    public string $horaSalida = '';
    public string $editFecha = '';
    public string $editSucursal = 'TODAS';
    public string $editNombre = '';
    public string $editDescripcion = '';
    public string $editTipo = 'feriado';
    public string $editHoraEntrada = '';
    public string $editHoraSalida = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $this->fecha = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingSucursalFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(string $value): void
    {
        if (in_array($value, ['feriado', 'paro'], true)) {
            $this->horaEntrada = '';
            $this->horaSalida = '';
        }
    }

    public function updatedEditTipo(string $value): void
    {
        if (in_array($value, ['feriado', 'paro'], true)) {
            $this->editHoraEntrada = '';
            $this->editHoraSalida = '';
        }
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

    public function openEditModal(int $fechaId): void
    {
        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail($fechaId);

        $this->editingFechaId = $fechaEspecial->id;
        $this->editFecha = $fechaEspecial->fecha?->toDateString() ?? '';
        $this->editSucursal = $fechaEspecial->sucursal ?: 'TODAS';
        $this->editNombre = $fechaEspecial->nombre;
        $this->editDescripcion = $fechaEspecial->descripcion ?? '';
        $this->editTipo = $fechaEspecial->tipo;
        $this->editHoraEntrada = $fechaEspecial->hora_entrada ? substr($fechaEspecial->hora_entrada, 0, 5) : '';
        $this->editHoraSalida = $fechaEspecial->hora_salida ? substr($fechaEspecial->hora_salida, 0, 5) : '';
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingFechaId = null;
        $this->resetValidation();
    }

    public function openDeleteModal(int $fechaId): void
    {
        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail($fechaId);

        $this->pendingDeleteFechaId = $fechaEspecial->id;
        $this->pendingDeleteFechaLabel = $fechaEspecial->nombre.' - '.($fechaEspecial->fecha?->format('d/m/Y') ?? 'Sin fecha');
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteFechaId = null;
        $this->pendingDeleteFechaLabel = '';
    }

    public function saveFechaEspecial(): void
    {
        $data = $this->validate($this->rules(), $this->messages());
        $this->validarDuplicado($data['fecha'], $data['sucursal']);

        if (! $this->validarHorarioEspecial($data['tipo'], $data['horaEntrada'] ?? '', $data['horaSalida'] ?? '', false)) {
            return;
        }

        $fechaEspecial = FechaEspecialLaboral::query()->create([
            'fecha' => $data['fecha'],
            'sucursal' => $data['sucursal'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?: null,
            'tipo' => $data['tipo'],
            'hora_entrada' => $this->normalizarHora($data['tipo'], $data['horaEntrada']),
            'hora_salida' => $this->normalizarHora($data['tipo'], $data['horaSalida']),
            'created_by' => auth()->id(),
        ]);

        app(AuditoriaService::class)->registrar(
            'Fechas especiales',
            'crear',
            'Se programo una nueva fecha especial.',
            $fechaEspecial,
            null,
            $this->snapshotFechaEspecial($fechaEspecial)
        );

        $this->closeCreateModal();
        $this->resetCreateForm();
        $this->resetPage();
        session()->flash('status', 'Fecha especial programada correctamente.');
    }

    public function updateFechaEspecial(): void
    {
        $data = $this->validate($this->editRules(), $this->messages());
        $this->validarDuplicado($data['editFecha'], $data['editSucursal'], (int) $data['editingFechaId']);

        if (! $this->validarHorarioEspecial($data['editTipo'], $data['editHoraEntrada'] ?? '', $data['editHoraSalida'] ?? '', true)) {
            return;
        }

        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail((int) $data['editingFechaId']);
        $antes = $this->snapshotFechaEspecial($fechaEspecial);
        $fechaEspecial->update([
            'fecha' => $data['editFecha'],
            'sucursal' => $data['editSucursal'],
            'nombre' => $data['editNombre'],
            'descripcion' => $data['editDescripcion'] ?: null,
            'tipo' => $data['editTipo'],
            'hora_entrada' => $this->normalizarHora($data['editTipo'], $data['editHoraEntrada']),
            'hora_salida' => $this->normalizarHora($data['editTipo'], $data['editHoraSalida']),
        ]);

        app(AuditoriaService::class)->registrar(
            'Fechas especiales',
            'editar',
            'Se actualizo una fecha especial.',
            $fechaEspecial->fresh(),
            $antes,
            $this->snapshotFechaEspecial($fechaEspecial->fresh())
        );

        $this->closeEditModal();
        $this->resetPage();
        session()->flash('status', 'Fecha especial actualizada correctamente.');
    }

    public function deleteFechaEspecial(): void
    {
        if (! $this->pendingDeleteFechaId) {
            return;
        }

        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail($this->pendingDeleteFechaId);
        $antes = $this->snapshotFechaEspecial($fechaEspecial);
        $fechaEspecial->delete();

        app(AuditoriaService::class)->registrar(
            'Fechas especiales',
            'eliminar',
            'Se elimino una fecha especial.',
            $fechaEspecial,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteModal();
        $this->resetPage();
        session()->flash('status', 'Fecha especial eliminada correctamente.');
    }

    public function render()
    {
        $fechasQuery = FechaEspecialLaboral::query();

        if (filled($this->search)) {
            $term = '%'.$this->search.'%';
            $fechasQuery->where(function ($query) use ($term) {
                $query->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhere('sucursal', 'like', $term);
            });
        }

        if (filled($this->tipoFiltro)) {
            $fechasQuery->where('tipo', $this->tipoFiltro);
        }

        if (filled($this->sucursalFiltro)) {
            $fechasQuery->where('sucursal', $this->sucursalFiltro);
        }

        $fechas = $fechasQuery
            ->orderBy('fecha')
            ->orderBy('sucursal')
            ->paginate(10);

        return view('livewire.fechas-especiales', [
            'fechas' => $fechas,
            'sucursales' => $this->sucursalesDisponibles(),
        ])->layout('layouts.app', ['title' => 'Programacion laboral']);
    }

    private function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'sucursal' => ['required', 'string', 'max:120'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['required', Rule::in(['feriado', 'horario_especial', 'paro'])],
            'horaEntrada' => ['nullable', 'date_format:H:i'],
            'horaSalida' => ['nullable', 'date_format:H:i'],
        ];
    }

    private function editRules(): array
    {
        return [
            'editingFechaId' => ['required', Rule::exists('fechas_especiales_laborales', 'id')],
            'editFecha' => ['required', 'date'],
            'editSucursal' => ['required', 'string', 'max:120'],
            'editNombre' => ['required', 'string', 'max:150'],
            'editDescripcion' => ['nullable', 'string', 'max:500'],
            'editTipo' => ['required', Rule::in(['feriado', 'horario_especial', 'paro'])],
            'editHoraEntrada' => ['nullable', 'date_format:H:i'],
            'editHoraSalida' => ['nullable', 'date_format:H:i'],
        ];
    }

    private function messages(): array
    {
        return [
            'fecha.required' => 'Selecciona la fecha a programar.',
            'sucursal.required' => 'Selecciona la sucursal afectada.',
            'nombre.required' => 'Ingresa un nombre para la fecha especial.',
            'tipo.required' => 'Selecciona el tipo de jornada.',
            'horaEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'horaSalida.date_format' => 'La hora de salida debe tener formato HH:MM.',
            'editFecha.required' => 'Selecciona la fecha a programar.',
            'editSucursal.required' => 'Selecciona la sucursal afectada.',
            'editNombre.required' => 'Ingresa un nombre para la fecha especial.',
            'editTipo.required' => 'Selecciona el tipo de jornada.',
            'editHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'editHoraSalida.date_format' => 'La hora de salida debe tener formato HH:MM.',
        ];
    }

    private function normalizarHora(string $tipo, ?string $hora): ?string
    {
        if (in_array($tipo, ['feriado', 'paro'], true) || blank($hora)) {
            return null;
        }

        return $hora.':00';
    }

    private function validarHorarioEspecial(string $tipo, string $horaEntrada, string $horaSalida, bool $editing): bool
    {
        if ($tipo !== 'horario_especial') {
            return true;
        }

        $campoSalida = $editing ? 'editHoraSalida' : 'horaSalida';

        if (blank($horaEntrada) && blank($horaSalida)) {
            $this->addError($campoSalida, 'Define al menos una hora especial para esta jornada.');

            return false;
        }

        if (filled($horaEntrada) && filled($horaSalida) && $horaSalida <= $horaEntrada) {
            $this->addError($campoSalida, 'La hora de salida especial debe ser posterior a la de entrada.');

            return false;
        }

        return true;
    }

    private function validarDuplicado(string $fecha, string $sucursal, ?int $ignoreId = null): void
    {
        $query = FechaEspecialLaboral::query()
            ->whereDate('fecha', $fecha)
            ->where('sucursal', $sucursal);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                $ignoreId ? 'editFecha' : 'fecha' => 'Ya existe una programacion para esa fecha y sucursal.',
            ]);
        }
    }

    private function resetCreateForm(): void
    {
        $this->fecha = now()->toDateString();
        $this->sucursal = 'TODAS';
        $this->nombre = '';
        $this->descripcion = '';
        $this->tipo = 'feriado';
        $this->horaEntrada = '';
        $this->horaSalida = '';
    }

    private function sucursalesDisponibles(): array
    {
        return collect(['TODAS'])
            ->merge(Empleado::query()->whereNotNull('sucursal')->distinct()->orderBy('sucursal')->pluck('sucursal'))
            ->merge(HorarioRegional::query()->distinct()->orderBy('sucursal')->pluck('sucursal'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function snapshotFechaEspecial(FechaEspecialLaboral $fechaEspecial): array
    {
        return [
            'id' => $fechaEspecial->id,
            'fecha' => $fechaEspecial->fecha?->toDateString(),
            'sucursal' => $fechaEspecial->sucursal,
            'nombre' => $fechaEspecial->nombre,
            'descripcion' => $fechaEspecial->descripcion,
            'tipo' => $fechaEspecial->tipo,
            'hora_entrada' => $fechaEspecial->hora_entrada,
            'hora_salida' => $fechaEspecial->hora_salida,
        ];
    }
}
