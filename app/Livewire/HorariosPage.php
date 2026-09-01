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
    public int $globalToleranciaDiaria = 5;
    public bool $showEditModal = false;
    public bool $showSucursalEmployeesModal = false;
    public string $editHoraEntrada = '';
    public string $editHoraTolerancia = '';
    public string $editHoraSalida = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);
        $this->globalTolerancia = (int) cache()->get('asistencia_tolerancia_min', config('asistencia.tolerancia_mensual_min', 35));
        $this->globalToleranciaDiaria = (int) cache()->get('asistencia_tolerancia_diaria_min', config('asistencia.tolerancia_diaria_min', 5));
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
            'globalTolerancia.required' => 'Ingresa los minutos de tolerancia mensual.',
            'globalTolerancia.integer' => 'Los minutos deben ser un número entero.',
            'globalTolerancia.min' => 'La tolerancia no puede ser menor a 0.',
            'globalTolerancia.max' => 'La tolerancia no puede ser mayor a 300 minutos.',
        ]);

        cache()->forever('asistencia_tolerancia_min', $this->globalTolerancia);
        config(['asistencia.tolerancia_mensual_min' => $this->globalTolerancia]);
        config(['asistencia.tolerancia_mensual_minutos' => $this->globalTolerancia]);

        session()->flash('status', "Tolerancia mensual global actualizada a {$this->globalTolerancia} minutos para todas las sucursales.");
    }

    public function saveGlobalToleranciaDiaria(): void
    {
        $this->validate([
            'globalToleranciaDiaria' => ['required', 'integer', 'min:0', 'max:120'],
        ], [
            'globalToleranciaDiaria.required' => 'Ingresa los minutos de tolerancia diaria.',
            'globalToleranciaDiaria.integer' => 'Los minutos deben ser un número entero.',
            'globalToleranciaDiaria.min' => 'La tolerancia no puede ser menor a 0.',
            'globalToleranciaDiaria.max' => 'La tolerancia diaria no puede superar 120 minutos.',
        ]);

        cache()->forever('asistencia_tolerancia_diaria_min', $this->globalToleranciaDiaria);
        config(['asistencia.tolerancia_diaria_min' => $this->globalToleranciaDiaria]);

        session()->flash('status', "Tolerancia diaria global actualizada a {$this->globalToleranciaDiaria} minutos para todas las sucursales.");
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

        if ($horario?->hora_tolerancia) {
            $this->editHoraTolerancia = substr((string) $horario->hora_tolerancia, 0, 5);
        } else {
            $minutosGracia = $horario?->tolerancia_minutos !== null
                ? (int) $horario->tolerancia_minutos
                : $this->globalToleranciaDiaria;

            try {
                $entradaCarbon = \Carbon\Carbon::createFromFormat('H:i', $this->editHoraEntrada);
                $this->editHoraTolerancia = $entradaCarbon->addMinutes($minutosGracia)->format('H:i');
            } catch (\Throwable) {
                $this->editHoraTolerancia = $this->editHoraEntrada;
            }
        }

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
            'editHoraTolerancia' => ['required', 'date_format:H:i', 'after_or_equal:editHoraEntrada'],
            'editHoraSalida' => ['nullable', 'date_format:H:i'],
        ], [
            'editHoraEntrada.required' => 'Ingresa la hora de entrada.',
            'editHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'editHoraTolerancia.required' => 'Ingresa el horario límite de tolerancia diaria.',
            'editHoraTolerancia.date_format' => 'La tolerancia debe tener formato HH:MM.',
            'editHoraTolerancia.after_or_equal' => 'La hora de tolerancia debe ser igual o posterior a la hora de entrada.',
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

        $entradaCarbon = \Carbon\Carbon::createFromFormat('H:i', $data['editHoraEntrada']);
        $toleranciaCarbon = \Carbon\Carbon::createFromFormat('H:i', $data['editHoraTolerancia']);
        $toleranciaMinutos = max(0, $entradaCarbon->diffInMinutes($toleranciaCarbon));

        $horario->fill([
            'sucursal' => $canonicalSucursal,
            'hora_entrada' => $data['editHoraEntrada'].':00',
            'hora_tolerancia' => $data['editHoraTolerancia'].':00',
            'tolerancia_minutos' => $toleranciaMinutos,
            'hora_salida' => filled($data['editHoraSalida']) ? $data['editHoraSalida'].':00' : null,
            'created_by' => $horario->exists ? $horario->created_by : auth()->id(),
        ]);
        $horario->save();

        app(AuditoriaService::class)->registrar(
            'Horarios',
            $antes ? 'editar' : 'crear',
            $antes ? 'Se actualizo el horario regional y tolerancia diaria de una sucursal.' : 'Se creo el horario regional y tolerancia diaria de una sucursal.',
            $horario->fresh(),
            $antes,
            $this->snapshotHorario($horario->fresh())
        );

        $this->activeSucursal = $canonicalSucursal;
        $this->closeEditModal();
        session()->flash('status', "Horario y tolerancia de {$canonicalSucursal} actualizados correctamente.");
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

        $programacionService = app(\App\Services\ProgramacionLaboralService::class);

        $horarios = $sucursales
            ->map(function (string $sucursal) use ($programacionService) {
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

                $entrada = $horario?->hora_entrada ? substr($horario->hora_entrada, 0, 5) : substr((string) config('asistencia.hora_entrada'), 0, 5);
                $tolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $entrada, $horario);

                return (object) [
                    'sucursal' => $sucursal,
                    'empleados' => $cantidad,
                    'hora_entrada' => $entrada,
                    'hora_tolerancia' => $tolerancia ? substr($tolerancia, 0, 5) : $entrada,
                    'tolerancia_minutos' => $horario?->tolerancia_minutos ?? $this->globalToleranciaDiaria,
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

            $entrada = $activeHorario?->hora_entrada ? substr($activeHorario->hora_entrada, 0, 5) : substr((string) config('asistencia.hora_entrada'), 0, 5);
            $tolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $entrada, $activeHorario);

            $activeSucursalData = (object) [
                'sucursal' => $activeSucursalLabel,
                'key' => SucursalNormalizer::canonicalKey($activeSucursalLabel) ?? \Illuminate\Support\Str::slug($activeSucursalLabel),
                'empleados' => $activeEmployeesCount,
                'hora_entrada' => $entrada,
                'hora_tolerancia' => $tolerancia ? substr($tolerancia, 0, 5) : $entrada,
                'tolerancia_minutos' => $activeHorario?->tolerancia_minutos ?? $this->globalToleranciaDiaria,
                'hora_salida' => $activeHorario?->hora_salida ? substr($activeHorario->hora_salida, 0, 5) : substr((string) config('asistencia.hora_salida'), 0, 5),
            ];
        }

        $departmentStats = app(\App\Services\AnalisisAsistenciaService::class)->asistenciaPorDepartamento();

        $generalEntrada = substr((string) config('asistencia.hora_entrada', '08:30:00'), 0, 5);
        $generalTolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $generalEntrada);

        return view('livewire.horarios', [
            'horarios' => $paginated,
            'generalHoraEntrada' => $generalEntrada,
            'generalHoraTolerancia' => $generalTolerancia ? substr($generalTolerancia, 0, 5) : $generalEntrada,
            'generalHoraSalida' => substr((string) config('asistencia.hora_salida', '16:30:00'), 0, 5),
            'globalTolerancia' => $this->globalTolerancia,
            'globalToleranciaDiaria' => $this->globalToleranciaDiaria,
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
            'hora_tolerancia' => $horario->hora_tolerancia,
            'tolerancia_minutos' => $horario->tolerancia_minutos,
            'hora_salida' => $horario->hora_salida,
        ];
    }
}
