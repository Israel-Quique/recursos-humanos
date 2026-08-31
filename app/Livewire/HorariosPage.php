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
    public ?string $activeSucursal = null;
    public int $globalTolerancia = 35;
    public bool $showEditModal = false;
    public bool $showSucursalEmployeesModal = false;
    public string $editHoraEntrada = '';
    public string $editHoraSalida = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);
        $this->globalTolerancia = (int) cache()->get('asistencia_tolerancia_min', config('asistencia.tolerancia_mensual_min', 35));
        if (empty($this->activeSucursal)) {
            $this->activeSucursal = 'La Paz';
        }
    }

    public function selectSucursal(string $sucursal): void
    {
        $this->activeSucursal = SucursalNormalizer::canonicalLabel($sucursal);
    }

    public function saveGlobalTolerancia(): void
    {
        $this->validate([
            'globalTolerancia' => ['required', 'integer', 'min:0', 'max:300'],
        ], [
            'globalTolerancia.required' => 'Ingresa los minutos de tolerancia.',
            'globalTolerancia.integer' => 'Los minutos deben ser un número entero.',
            'globalTolerancia.min' => 'La tolerancia no puede ser menor a 0.',
            'globalTolerancia.max' => 'La tolerancia no puede ser mayor a 300 minutos.',
        ]);

        cache()->forever('asistencia_tolerancia_min', $this->globalTolerancia);
        config(['asistencia.tolerancia_mensual_min' => $this->globalTolerancia]);
        config(['asistencia.tolerancia_mensual_minutos' => $this->globalTolerancia]);

        session()->flash('status', "Tolerancia mensual global actualizada a {$this->globalTolerancia} minutos para todas las sucursales.");
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openEditModal(string $sucursal): void
    {
        $canonical = SucursalNormalizer::canonicalLabel($sucursal);
        $horario = HorarioRegional::query()
            ->where(function ($query) use ($canonical) {
                SucursalNormalizer::applyFilter($query, 'sucursal', $canonical);
            })
            ->first();

        $this->editingSucursal = $canonical;
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
        $this->selectedSucursal = SucursalNormalizer::canonicalLabel($sucursal);
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

        $canonicalSucursal = SucursalNormalizer::canonicalLabel($data['editingSucursal']);

        $horario = HorarioRegional::query()
            ->where(function ($query) use ($canonicalSucursal) {
                SucursalNormalizer::applyFilter($query, 'sucursal', $canonicalSucursal);
            })
            ->first() ?? new HorarioRegional([
                'sucursal' => $canonicalSucursal,
            ]);
        $antes = $horario->exists ? $this->snapshotHorario($horario) : null;

        $horario->fill([
            'sucursal' => $canonicalSucursal,
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

        $this->activeSucursal = $canonicalSucursal;
        $this->closeEditModal();
        session()->flash('status', "Horario de {$canonicalSucursal} actualizado correctamente.");
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

        $activeSucursalData = null;
        if (filled($this->activeSucursal)) {
            $activeSucursalLabel = $this->activeSucursal;
            $activeHorario = HorarioRegional::query()
                ->where(function ($query) use ($activeSucursalLabel) {
                    SucursalNormalizer::applyFilter($query, 'sucursal', $activeSucursalLabel);
                })
                ->first();
            $activeEmployeesCount = Empleado::query()
                ->where(function ($query) use ($activeSucursalLabel) {
                    SucursalNormalizer::applyFilter($query, 'sucursal', $activeSucursalLabel);
                })
                ->count();

            $activeSucursalData = (object) [
                'sucursal' => $activeSucursalLabel,
                'key' => SucursalNormalizer::canonicalKey($activeSucursalLabel) ?? \Illuminate\Support\Str::slug($activeSucursalLabel),
                'empleados' => $activeEmployeesCount,
                'hora_entrada' => $activeHorario?->hora_entrada ? substr($activeHorario->hora_entrada, 0, 5) : substr((string) config('asistencia.hora_entrada'), 0, 5),
                'hora_salida' => $activeHorario?->hora_salida ? substr($activeHorario->hora_salida, 0, 5) : substr((string) config('asistencia.hora_salida'), 0, 5),
            ];
        }

        $departmentStats = app(\App\Services\AnalisisAsistenciaService::class)->asistenciaPorDepartamento();

        return view('livewire.horarios', [
            'horarios' => $paginated,
            'generalHoraEntrada' => substr((string) config('asistencia.hora_entrada', '08:30:00'), 0, 5),
            'generalHoraSalida' => substr((string) config('asistencia.hora_salida', '16:30:00'), 0, 5),
            'globalTolerancia' => $this->globalTolerancia,
            'activeSucursalData' => $activeSucursalData,
            'departmentStats' => $departmentStats,
            'allSucursales' => $sucursales,
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
