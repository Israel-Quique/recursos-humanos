<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Services\AuditoriaService;
use App\Support\SucursalNormalizer;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class HorariosPage extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $editingSucursal = null;
    public ?string $selectedSucursal = null;
    public bool $showEditModal = false;
    public bool $showSucursalEmployeesModal = false;
    public string $editHoraEntrada = '';
    public string $editHoraSalida = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openEditModal(string $sucursal): void
    {
        $horario = HorarioRegional::query()
            ->where(function ($query) use ($sucursal) {
                SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
            })
            ->first();

        $this->editingSucursal = $sucursal;
        $this->editHoraEntrada = $horario?->hora_entrada
            ? substr((string) $horario->hora_entrada, 0, 5)
            : substr((string) config('asistencia.hora_entrada'), 0, 5);
        $this->editHoraSalida = $horario?->hora_salida
            ? substr((string) $horario->hora_salida, 0, 5)
            : substr((string) config('asistencia.hora_salida'), 0, 5);
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingSucursal = null;
        $this->resetValidation();
    }

    public function openSucursalEmployeesModal(string $sucursal): void
    {
        $this->selectedSucursal = $sucursal;
        $this->showSucursalEmployeesModal = true;
    }

    public function closeSucursalEmployeesModal(): void
    {
        $this->showSucursalEmployeesModal = false;
        $this->selectedSucursal = null;
    }

    public function saveHorario(): void
    {
        $data = $this->validate([
            'editingSucursal' => ['required', 'string', 'max:120'],
            'editHoraEntrada' => ['required', 'date_format:H:i'],
            'editHoraSalida' => ['nullable', 'date_format:H:i'],
        ], [
            'editHoraEntrada.required' => 'Ingresa la hora de entrada.',
            'editHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'editHoraSalida.date_format' => 'La hora de salida debe tener formato HH:MM.',
        ]);

        $horario = HorarioRegional::query()
            ->where(function ($query) use ($data) {
                SucursalNormalizer::applyFilter($query, 'sucursal', $data['editingSucursal']);
            })
            ->first() ?? new HorarioRegional([
                'sucursal' => $data['editingSucursal'],
            ]);
        $antes = $horario->exists ? $this->snapshotHorario($horario) : null;

        $horario->fill([
            'hora_entrada' => $data['editHoraEntrada'].':00',
            'hora_salida' => filled($data['editHoraSalida']) ? $data['editHoraSalida'].':00' : null,
            'created_by' => $horario->exists ? $horario->created_by : auth()->id(),
        ]);
        $horario->save();

        app(AuditoriaService::class)->registrar(
            'Horarios',
            $antes ? 'editar' : 'crear',
            $antes ? 'Se actualizo el horario regional de una sucursal.' : 'Se creo el horario regional de una sucursal.',
            $horario->fresh(),
            $antes,
            $this->snapshotHorario($horario->fresh())
        );

        $this->closeEditModal();
        session()->flash('status', 'Horario regional actualizado correctamente.');
    }

    public function render()
    {
        $sucursales = Empleado::query()
            ->select('sucursal')
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->distinct()
            ->orderBy('sucursal')
            ->pluck('sucursal');

        $sucursales = collect(SucursalNormalizer::optionsFromValues($sucursales));

        if (filled($this->search)) {
            $sucursales = $sucursales->filter(fn (string $sucursal) => str_contains(mb_strtolower($sucursal), mb_strtolower($this->search)));
        }

        $horarios = $sucursales
            ->map(function (string $sucursal) {
                $horario = HorarioRegional::query()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->first();
                $cantidad = Empleado::query()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->count();

                return (object) [
                    'sucursal' => $sucursal,
                    'empleados' => $cantidad,
                    'hora_entrada' => $horario?->hora_entrada ? substr($horario->hora_entrada, 0, 5) : substr((string) config('asistencia.hora_entrada'), 0, 5),
                    'hora_salida' => $horario?->hora_salida ? substr($horario->hora_salida, 0, 5) : substr((string) config('asistencia.hora_salida'), 0, 5),
                ];
            })
            ->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $horarios->forPage($this->getPage(), 10)->values(),
            $horarios->count(),
            10,
            $this->getPage(),
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('livewire.horarios', [
            'horarios' => $paginated,
            'sucursalEmployees' => $this->selectedSucursal
                ? Empleado::query()
                    ->where(function ($query) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $this->selectedSucursal);
                    })
                    ->orderBy('apellido')
                    ->orderBy('nombre')
                    ->get()
                : collect(),
        ])->layout('layouts.app', ['title' => 'Gestion de horarios']);
    }

    private function getPage(): int
    {
        return (int) ($this->paginators['page'] ?? 1);
    }

    private function snapshotHorario(HorarioRegional $horario): array
    {
        return [
            'id' => $horario->id,
            'sucursal' => $horario->sucursal,
            'hora_entrada' => $horario->hora_entrada,
            'hora_salida' => $horario->hora_salida,
        ];
    }
}
