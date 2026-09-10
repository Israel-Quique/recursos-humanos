<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Services\AuditoriaService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class HorariosPage extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $editingSucursal = null;
    public ?string $selectedSucursal = null;
    public ?string $activeSucursal = null;
    public string $topCardsScope = 'sucursal'; // 'sucursal' or 'global'

    // Global settings
    public int $globalTolerancia = 35;
    public int $globalToleranciaDiaria = 5;

    // Sucursal edit modal
    public bool $showEditModal = false;
    public string $editHoraEntrada = '';
    public string $editHoraTolerancia = '';
    public string $editHoraSalida = '';
    public ?int $editToleranciaMensual = 35;

    // Global edit modal
    public bool $showGlobalModal = false;
    public string $globalEditHoraEntrada = '08:30';
    public string $globalEditHoraTolerancia = '08:35';
    public string $globalEditHoraSalida = '16:30';
    public int $globalEditToleranciaDiaria = 5;
    public int $globalEditToleranciaMensual = 35;
    public bool $aplicarATodasLasSucursales = false;

    // Sucursal employees modal
    public bool $showSucursalEmployeesModal = false;

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
        $this->topCardsScope = 'sucursal';
    }

    public function setTopCardsScope(string $scope): void
    {
        $this->topCardsScope = in_array($scope, ['sucursal', 'global'], true) ? $scope : 'sucursal';
    }

    public function openGlobalModal(): void
    {
        $this->globalEditHoraEntrada = substr((string) cache()->get('asistencia_hora_entrada', config('asistencia.hora_entrada', '08:30:00')), 0, 5);
        $this->globalEditHoraSalida = substr((string) cache()->get('asistencia_hora_salida', config('asistencia.hora_salida', '16:30:00')), 0, 5);
        $this->globalEditToleranciaDiaria = $this->globalToleranciaDiaria;
        $this->globalEditToleranciaMensual = $this->globalTolerancia;

        try {
            $entrada = Carbon::createFromFormat('H:i', $this->globalEditHoraEntrada);
            $this->globalEditHoraTolerancia = $entrada->addMinutes(max(0, $this->globalEditToleranciaDiaria))->format('H:i');
        } catch (\Throwable) {
            $this->globalEditHoraTolerancia = $this->globalEditHoraEntrada;
        }

        $this->aplicarATodasLasSucursales = false;
        $this->showGlobalModal = true;
        $this->resetValidation();
    }

    public function updatedGlobalEditHoraEntrada(): void
    {
        try {
            $entrada = Carbon::createFromFormat('H:i', $this->globalEditHoraEntrada);
            $this->globalEditHoraTolerancia = $entrada->addMinutes(max(0, $this->globalEditToleranciaDiaria))->format('H:i');
        } catch (\Throwable) {
        }
    }

    public function updatedGlobalEditToleranciaDiaria(): void
    {
        try {
            $entrada = Carbon::createFromFormat('H:i', $this->globalEditHoraEntrada);
            $this->globalEditHoraTolerancia = $entrada->addMinutes(max(0, (int) $this->globalEditToleranciaDiaria))->format('H:i');
        } catch (\Throwable) {
        }
    }

    public function closeGlobalModal(): void
    {
        $this->showGlobalModal = false;
        $this->resetValidation();
    }

    public function saveGlobalSettings(): void
    {
        $data = $this->validate([
            'globalEditHoraEntrada' => ['required', 'date_format:H:i'],
            'globalEditHoraTolerancia' => ['required', 'date_format:H:i', 'after_or_equal:globalEditHoraEntrada'],
            'globalEditHoraSalida' => ['nullable', 'date_format:H:i'],
            'globalEditToleranciaDiaria' => ['required', 'integer', 'min:0', 'max:120'],
            'globalEditToleranciaMensual' => ['required', 'integer', 'min:0', 'max:600'],
            'aplicarATodasLasSucursales' => ['boolean'],
        ], [
            'globalEditHoraEntrada.required' => 'Ingresa la hora de entrada estándar.',
            'globalEditHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'globalEditHoraTolerancia.required' => 'Ingresa la hora límite de tolerancia.',
            'globalEditHoraTolerancia.date_format' => 'La tolerancia debe tener formato HH:MM.',
            'globalEditHoraTolerancia.after_or_equal' => 'La hora de tolerancia debe ser igual o posterior a la entrada.',
            'globalEditHoraSalida.date_format' => 'La hora de salida debe tener formato HH:MM.',
            'globalEditToleranciaDiaria.required' => 'Ingresa los minutos de tolerancia diaria.',
            'globalEditToleranciaDiaria.integer' => 'La tolerancia diaria debe ser un número entero.',
            'globalEditToleranciaDiaria.min' => 'La tolerancia diaria no puede ser menor a 0.',
            'globalEditToleranciaDiaria.max' => 'La tolerancia diaria no puede superar 120 minutos.',
            'globalEditToleranciaMensual.required' => 'Ingresa los minutos de tolerancia mensual acumulativa.',
            'globalEditToleranciaMensual.integer' => 'La tolerancia mensual debe ser un número entero.',
            'globalEditToleranciaMensual.min' => 'La tolerancia mensual no puede ser menor a 0.',
            'globalEditToleranciaMensual.max' => 'La tolerancia mensual no puede superar 600 minutos.',
        ]);

        $entradaStr = $data['globalEditHoraEntrada'].':00';
        $salidaStr = filled($data['globalEditHoraSalida']) ? $data['globalEditHoraSalida'].':00' : '16:30:00';
        $toleranciaDiaria = (int) $data['globalEditToleranciaDiaria'];
        $toleranciaMensual = (int) $data['globalEditToleranciaMensual'];

        cache()->forever('asistencia_hora_entrada', $entradaStr);
        cache()->forever('asistencia_hora_salida', $salidaStr);
        cache()->forever('asistencia_tolerancia_diaria_min', $toleranciaDiaria);
        cache()->forever('asistencia_tolerancia_min', $toleranciaMensual);

        config(['asistencia.hora_entrada' => $entradaStr]);
        config(['asistencia.hora_salida' => $salidaStr]);
        config(['asistencia.tolerancia_diaria_min' => $toleranciaDiaria]);
        config(['asistencia.tolerancia_mensual_min' => $toleranciaMensual]);
        config(['asistencia.tolerancia_mensual_minutos' => $toleranciaMensual]);

        $this->globalTolerancia = $toleranciaMensual;
        $this->globalToleranciaDiaria = $toleranciaDiaria;

        $syncCount = 0;
        if (!empty($data['aplicarATodasLasSucursales'])) {
            $sucursalesRaw = Empleado::query()
                ->select('sucursal')
                ->whereNotNull('sucursal')
                ->where('sucursal', '!=', '')
                ->distinct()
                ->pluck('sucursal');
            $canonicalSucursales = SucursalNormalizer::optionsFromValues($sucursalesRaw);

            $entradaCarbon = Carbon::createFromFormat('H:i', $data['globalEditHoraEntrada']);
            $toleranciaCarbon = Carbon::createFromFormat('H:i', $data['globalEditHoraTolerancia']);
            $toleranciaMinutos = max(0, $entradaCarbon->diffInMinutes($toleranciaCarbon));

            foreach ($canonicalSucursales as $sucursal) {
                $horario = HorarioRegional::query()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->first() ?? new HorarioRegional(['sucursal' => $sucursal]);

                $horario->fill([
                    'sucursal' => $sucursal,
                    'hora_entrada' => $entradaStr,
                    'hora_tolerancia' => $data['globalEditHoraTolerancia'].':00',
                    'tolerancia_minutos' => $toleranciaMinutos,
                    'tolerancia_mensual_minutos' => $toleranciaMensual,
                    'hora_salida' => filled($data['globalEditHoraSalida']) ? $salidaStr : null,
                    'created_by' => $horario->exists ? $horario->created_by : auth()->id(),
                ]);
                $horario->save();
                $syncCount++;
            }
        }

        app(AuditoriaService::class)->registrar(
            'Horarios',
            'editar',
            'Se actualizó la configuración global de horario y tolerancias (Entrada: ' . $data['globalEditHoraEntrada'] . ', Salida: ' . ($data['globalEditHoraSalida'] ?: '16:30') . ', Tol. Diaria: +' . $toleranciaDiaria . 'm, Tol. Mensual: ' . $toleranciaMensual . 'm)' . ($syncCount > 0 ? " y se sincronizó a {$syncCount} sucursales." : '.'),
            null,
            null,
            [
                'hora_entrada' => $entradaStr,
                'hora_salida' => $salidaStr,
                'tolerancia_diaria_min' => $toleranciaDiaria,
                'tolerancia_mensual_min' => $toleranciaMensual,
                'sincronizado_sucursales' => $syncCount,
            ]
        );

        $this->closeGlobalModal();
        session()->flash('status', 'Estándar global de horarios y tolerancias guardado correctamente' . ($syncCount > 0 ? " y sincronizado con {$syncCount} sucursales." : '.'));
    }

    public function saveGlobalTolerancia(): void
    {
        $this->validate([
            'globalTolerancia' => ['required', 'integer', 'min:0', 'max:600'],
        ], [
            'globalTolerancia.required' => 'Ingresa los minutos de tolerancia mensual.',
            'globalTolerancia.integer' => 'Los minutos deben ser un número entero.',
            'globalTolerancia.min' => 'La tolerancia no puede ser menor a 0.',
            'globalTolerancia.max' => 'La tolerancia no puede ser mayor a 600 minutos.',
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
            : substr((string) cache()->get('asistencia_hora_entrada', config('asistencia.hora_entrada', '08:30:00')), 0, 5);

        if ($horario?->hora_tolerancia) {
            $this->editHoraTolerancia = substr((string) $horario->hora_tolerancia, 0, 5);
        } else {
            $minutosGracia = $horario?->tolerancia_minutos !== null
                ? (int) $horario->tolerancia_minutos
                : $this->globalToleranciaDiaria;

            try {
                $entradaCarbon = Carbon::createFromFormat('H:i', $this->editHoraEntrada);
                $this->editHoraTolerancia = $entradaCarbon->addMinutes($minutosGracia)->format('H:i');
            } catch (\Throwable) {
                $this->editHoraTolerancia = $this->editHoraEntrada;
            }
        }

        $this->editHoraSalida = $horario?->hora_salida
            ? substr((string) $horario->hora_salida, 0, 5)
            : substr((string) cache()->get('asistencia_hora_salida', config('asistencia.hora_salida', '16:30:00')), 0, 5);

        $this->editToleranciaMensual = $horario?->tolerancia_mensual_minutos !== null
            ? (int) $horario->tolerancia_mensual_minutos
            : $this->globalTolerancia;

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
            'editToleranciaMensual' => ['required', 'integer', 'min:0', 'max:600'],
        ], [
            'editHoraEntrada.required' => 'Ingresa la hora de entrada.',
            'editHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'editHoraTolerancia.required' => 'Ingresa el horario límite de tolerancia diaria.',
            'editHoraTolerancia.date_format' => 'La tolerancia debe tener formato HH:MM.',
            'editHoraTolerancia.after_or_equal' => 'La hora de tolerancia debe ser igual o posterior a la hora de entrada.',
            'editHoraSalida.date_format' => 'La hora de salida debe tener formato HH:MM.',
            'editToleranciaMensual.required' => 'Ingresa los minutos de tolerancia mensual acumulativa.',
            'editToleranciaMensual.integer' => 'La tolerancia mensual debe ser un número entero.',
            'editToleranciaMensual.min' => 'La tolerancia mensual no puede ser menor a 0.',
            'editToleranciaMensual.max' => 'La tolerancia mensual no puede superar 600 minutos.',
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

        $entradaCarbon = Carbon::createFromFormat('H:i', $data['editHoraEntrada']);
        $toleranciaCarbon = Carbon::createFromFormat('H:i', $data['editHoraTolerancia']);
        $toleranciaMinutos = max(0, $entradaCarbon->diffInMinutes($toleranciaCarbon));

        $horario->fill([
            'sucursal' => $canonicalSucursal,
            'hora_entrada' => $data['editHoraEntrada'].':00',
            'hora_tolerancia' => $data['editHoraTolerancia'].':00',
            'tolerancia_minutos' => $toleranciaMinutos,
            'tolerancia_mensual_minutos' => (int) $data['editToleranciaMensual'],
            'hora_salida' => filled($data['editHoraSalida']) ? $data['editHoraSalida'].':00' : null,
            'created_by' => $horario->exists ? $horario->created_by : auth()->id(),
        ]);
        $horario->save();

        app(AuditoriaService::class)->registrar(
            'Horarios',
            $antes ? 'editar' : 'crear',
            $antes ? "Se actualizó el horario y tolerancia (diaria: {$toleranciaMinutos}m, mensual: {$data['editToleranciaMensual']}m) de {$canonicalSucursal}." : "Se creó el horario y tolerancia de {$canonicalSucursal}.",
            $horario->fresh(),
            $antes,
            $this->snapshotHorario($horario->fresh())
        );

        $this->activeSucursal = $canonicalSucursal;
        $this->topCardsScope = 'sucursal';
        $this->closeEditModal();
        session()->flash('status', "Horario y tolerancias de {$canonicalSucursal} actualizados correctamente.");
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

        $generalEntrada = substr((string) cache()->get('asistencia_hora_entrada', config('asistencia.hora_entrada', '08:30:00')), 0, 5);
        $generalSalida = substr((string) cache()->get('asistencia_hora_salida', config('asistencia.hora_salida', '16:30:00')), 0, 5);
        $generalTolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $generalEntrada);

        $horarios = $sucursales
            ->map(function (string $sucursal) use ($programacionService, $generalEntrada, $generalSalida) {
                $horario = HorarioRegional::query()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->first();
                $cantidad = Empleado::query()
                    ->activosLaboralmente()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->count();
                $totalPadron = Empleado::query()
                    ->where(function ($query) use ($sucursal) {
                        SucursalNormalizer::applyFilter($query, 'sucursal', $sucursal);
                    })
                    ->count();

                $entrada = $horario?->hora_entrada ? substr($horario->hora_entrada, 0, 5) : $generalEntrada;
                $tolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $entrada, $horario);

                $toleranciaMensual = $horario?->tolerancia_mensual_minutos !== null
                    ? (int) $horario->tolerancia_mensual_minutos
                    : $this->globalTolerancia;

                return (object) [
                    'sucursal' => $sucursal,
                    'empleados' => $cantidad,
                    'total_padron' => $totalPadron,
                    'hora_entrada' => $entrada,
                    'hora_tolerancia' => $tolerancia ? substr($tolerancia, 0, 5) : $entrada,
                    'tolerancia_minutos' => $horario?->tolerancia_minutos ?? $this->globalToleranciaDiaria,
                    'tolerancia_mensual' => $toleranciaMensual,
                    'tolerancia_mensual_personalizada' => $horario?->tolerancia_mensual_minutos !== null,
                    'hora_salida' => $horario?->hora_salida ? substr($horario->hora_salida, 0, 5) : $generalSalida,
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
                ->activosLaboralmente()
                ->where(function ($query) use ($activeSucursalLabel) {
                    SucursalNormalizer::applyFilter($query, 'sucursal', $activeSucursalLabel);
                })
                ->count();
            $activeTotalPadron = Empleado::query()
                ->where(function ($query) use ($activeSucursalLabel) {
                    SucursalNormalizer::applyFilter($query, 'sucursal', $activeSucursalLabel);
                })
                ->count();

            $entrada = $activeHorario?->hora_entrada ? substr($activeHorario->hora_entrada, 0, 5) : $generalEntrada;
            $tolerancia = $programacionService->resolverHoraEntradaTolerancia(now(), $entrada, $activeHorario);

            $toleranciaMensual = $activeHorario?->tolerancia_mensual_minutos !== null
                ? (int) $activeHorario->tolerancia_mensual_minutos
                : $this->globalTolerancia;

            $activeSucursalData = (object) [
                'sucursal' => $activeSucursalLabel,
                'key' => SucursalNormalizer::canonicalKey($activeSucursalLabel) ?? \Illuminate\Support\Str::slug($activeSucursalLabel),
                'empleados' => $activeEmployeesCount,
                'hora_entrada' => $entrada,
                'hora_tolerancia' => $tolerancia ? substr($tolerancia, 0, 5) : $entrada,
                'tolerancia_minutos' => $activeHorario?->tolerancia_minutos ?? $this->globalToleranciaDiaria,
                'tolerancia_mensual' => $toleranciaMensual,
                'tolerancia_mensual_personalizada' => $activeHorario?->tolerancia_mensual_minutos !== null,
                'hora_salida' => $activeHorario?->hora_salida ? substr($activeHorario->hora_salida, 0, 5) : $generalSalida,
            ];
        }

        $departmentStats = app(\App\Services\AnalisisAsistenciaService::class)->asistenciaPorDepartamento();

        $isViewingSucursal = ($this->topCardsScope === 'sucursal' && $activeSucursalData !== null);

        $topCardsData = (object) [
            'scope' => $this->topCardsScope,
            'is_sucursal' => $isViewingSucursal,
            'scope_label' => $isViewingSucursal ? $activeSucursalData->sucursal : 'Estándar Global',
            'hora_entrada' => $isViewingSucursal ? $activeSucursalData->hora_entrada : $generalEntrada,
            'hora_tolerancia' => $isViewingSucursal ? $activeSucursalData->hora_tolerancia : ($generalTolerancia ? substr($generalTolerancia, 0, 5) : $generalEntrada),
            'tolerancia_diaria_min' => $isViewingSucursal ? $activeSucursalData->tolerancia_minutos : $this->globalToleranciaDiaria,
            'hora_salida' => $isViewingSucursal ? $activeSucursalData->hora_salida : $generalSalida,
            'tolerancia_mensual' => $isViewingSucursal ? $activeSucursalData->tolerancia_mensual : $this->globalTolerancia,
            'is_personalizada' => $isViewingSucursal && $activeSucursalData->tolerancia_mensual_personalizada,
        ];

        return view('livewire.horarios', [
            'horarios' => $paginated,
            'generalHoraEntrada' => $generalEntrada,
            'generalHoraTolerancia' => $generalTolerancia ? substr($generalTolerancia, 0, 5) : $generalEntrada,
            'generalHoraSalida' => $generalSalida,
            'globalTolerancia' => $this->globalTolerancia,
            'globalToleranciaDiaria' => $this->globalToleranciaDiaria,
            'activeSucursalData' => $activeSucursalData,
            'topCardsData' => $topCardsData,
            'departmentStats' => $departmentStats,
            'allSucursales' => $sucursales,
            'sucursalEmployees' => $this->selectedSucursal
                ? Empleado::query()
                    ->activosLaboralmente()
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
            'tolerancia_mensual_minutos' => $horario->tolerancia_mensual_minutos,
            'hora_salida' => $horario->hora_salida,
        ];
    }
}
