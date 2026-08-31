<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Services\AuditoriaService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class FechasEspecialesPage extends Component
{
    // Navegación del calendario
    public string $calendarMonth = '';

    // Día seleccionado en el calendario
    public string $selectedDate = '';

    // Modales
    public bool $showCreateModal = false;
    public bool $showEditModal   = false;
    public bool $showDeleteModal = false;

    public ?int $editingFechaId        = null;
    public ?int $pendingDeleteFechaId  = null;
    public string $pendingDeleteFechaLabel = '';

    // Campos formulario (crear)
    public string $fecha       = '';
    public string $fechaFin    = '';
    public string $sucursal    = 'TODAS';
    public string $nombre      = '';
    public string $descripcion = '';
    public string $tipo        = 'feriado';
    public string $horaEntrada = '';
    public string $horaSalida  = '';
    public bool   $incluirSabados = true;   // ← sábados SÍ, domingos NO

    // Campos formulario (editar)
    public string $editFecha       = '';
    public string $editFechaFin    = '';
    public string $editSucursal    = 'TODAS';
    public string $editNombre      = '';
    public string $editDescripcion = '';
    public string $editTipo        = 'feriado';
    public string $editHoraEntrada = '';
    public string $editHoraSalida  = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $this->calendarMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate  = now()->toDateString();
        $this->fecha         = now()->toDateString();
    }

    // ── Navegación del calendario ─────────────────────────────────────────────

    public function goToPreviousMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth)
            ->subMonthNoOverflow()
            ->startOfMonth()
            ->toDateString();
    }

    public function goToNextMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth)
            ->addMonthNoOverflow()
            ->startOfMonth()
            ->toDateString();
    }

    public function goToCurrentMonth(): void
    {
        $this->calendarMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate  = now()->toDateString();
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
    }

    // ── Apertura de modales ───────────────────────────────────────────────────

    public function openCreateModal(?string $date = null): void
    {
        $this->resetValidation();
        $this->resetCreateForm();
        if ($date) {
            $this->fecha    = $date;
            $this->fechaFin = $date;
        }
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

        $this->editingFechaId   = $fechaEspecial->id;
        $this->editFecha        = $fechaEspecial->fecha?->toDateString() ?? '';
        $this->editFechaFin     = $fechaEspecial->fecha?->toDateString() ?? '';
        $this->editSucursal     = $fechaEspecial->sucursal ?: 'TODAS';
        $this->editNombre       = $fechaEspecial->nombre;
        $this->editDescripcion  = $fechaEspecial->descripcion ?? '';
        $this->editTipo         = $fechaEspecial->tipo;
        $this->editHoraEntrada  = $fechaEspecial->hora_entrada ? substr($fechaEspecial->hora_entrada, 0, 5) : '';
        $this->editHoraSalida   = $fechaEspecial->hora_salida  ? substr($fechaEspecial->hora_salida, 0, 5)  : '';

        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal  = false;
        $this->editingFechaId = null;
        $this->resetValidation();
    }

    public function openDeleteModal(int $fechaId): void
    {
        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail($fechaId);
        $this->pendingDeleteFechaId    = $fechaEspecial->id;
        $this->pendingDeleteFechaLabel = $fechaEspecial->nombre.' - '.($fechaEspecial->fecha?->format('d/m/Y') ?? 'Sin fecha');
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal     = false;
        $this->pendingDeleteFechaId  = null;
        $this->pendingDeleteFechaLabel = '';
    }

    // ── Watchers ──────────────────────────────────────────────────────────────

    public function updatedTipo(string $value): void
    {
        if (in_array($value, ['feriado', 'paro'], true)) {
            $this->horaEntrada = '';
            $this->horaSalida  = '';
        }

        // Si cambia a paro u horario_especial, forzar fechaFin = fecha (solo 1 día)
        if (in_array($value, ['paro', 'horario_especial'], true)) {
            $this->fechaFin = $this->fecha;
        }
    }

    public function updatedEditTipo(string $value): void
    {
        if (in_array($value, ['feriado', 'paro'], true)) {
            $this->editHoraEntrada = '';
            $this->editHoraSalida  = '';
        }

        // Si cambia a paro u horario_especial, forzar editFechaFin = editFecha (solo 1 día)
        if (in_array($value, ['paro', 'horario_especial'], true)) {
            $this->editFechaFin = $this->editFecha;
        }
    }

    public function updatedFecha(string $value): void
    {
        // Para paro u horario_especial, fechaFin siempre se iguala a fecha
        if (in_array($this->tipo, ['paro', 'horario_especial'], true)) {
            $this->fechaFin = $value;
        } elseif (blank($this->fechaFin) || $this->fechaFin < $value) {
            $this->fechaFin = $value;
        }
    }

    public function updatedEditFecha(string $value): void
    {
        // Para paro u horario_especial, editFechaFin siempre se iguala a editFecha
        if (in_array($this->editTipo, ['paro', 'horario_especial'], true)) {
            $this->editFechaFin = $value;
        } elseif (blank($this->editFechaFin) || $this->editFechaFin < $value) {
            $this->editFechaFin = $value;
        }
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function saveFechaEspecial(): void
    {
        $data = $this->validate($this->rules(), $this->messages());

        if (! $this->validarHorarioEspecial($data['tipo'], $data['horaEntrada'] ?? '', $data['horaSalida'] ?? '', false)) {
            return;
        }

        $fechaFinReal = filled($data['fechaFin']) ? $data['fechaFin'] : $data['fecha'];

        $fechasACrear = $this->resolverFechas(
            $data['fecha'],
            $fechaFinReal,
            $this->incluirSabados
        );

        $auditoria = app(AuditoriaService::class);

        foreach ($fechasACrear as $fechaDia) {
            $fechaEspecial = FechaEspecialLaboral::query()->create([
                'fecha'       => $fechaDia,
                'sucursal'    => $data['sucursal'],
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'] ?: null,
                'tipo'        => $data['tipo'],
                'hora_entrada' => $this->normalizarHora($data['tipo'], $data['horaEntrada']),
                'hora_salida'  => $this->normalizarHora($data['tipo'], $data['horaSalida']),
                'created_by'  => auth()->id(),
            ]);

            $auditoria->registrar(
                'Fechas especiales',
                'crear',
                'Se programo una nueva fecha especial.',
                $fechaEspecial,
                null,
                $this->snapshotFechaEspecial($fechaEspecial)
            );
        }

        $this->closeCreateModal();
        $this->resetCreateForm();

        $creados = count($fechasACrear);
        session()->flash('status', $creados === 1
            ? 'Fecha especial programada correctamente.'
            : "Se programaron {$creados} fechas especiales correctamente.");
    }

    public function updateFechaEspecial(): void
    {
        $data = $this->validate($this->editRules(), $this->messages());

        if (! $this->validarHorarioEspecial($data['editTipo'], $data['editHoraEntrada'] ?? '', $data['editHoraSalida'] ?? '', true)) {
            return;
        }

        $fechaEspecial = FechaEspecialLaboral::query()->findOrFail((int) $data['editingFechaId']);
        $antes = $this->snapshotFechaEspecial($fechaEspecial);

        $fechaFinReal = filled($data['editFechaFin']) ? $data['editFechaFin'] : $data['editFecha'];

        // Si el rango cubre múltiples días, creamos registros adicionales
        if ($fechaFinReal > $data['editFecha']) {
            $fechasExtra = $this->resolverFechas(
                $data['editFecha'],
                $fechaFinReal,
                $this->incluirSabados
            );

            $auditoria = app(AuditoriaService::class);

            foreach ($fechasExtra as $fechaDia) {
                if ($fechaDia === $data['editFecha']) {
                    continue; // el día base se actualiza abajo
                }
                $nuevo = FechaEspecialLaboral::query()->create([
                    'fecha'       => $fechaDia,
                    'sucursal'    => $data['editSucursal'],
                    'nombre'      => $data['editNombre'],
                    'descripcion' => $data['editDescripcion'] ?: null,
                    'tipo'        => $data['editTipo'],
                    'hora_entrada' => $this->normalizarHora($data['editTipo'], $data['editHoraEntrada']),
                    'hora_salida'  => $this->normalizarHora($data['editTipo'], $data['editHoraSalida']),
                    'created_by'  => auth()->id(),
                ]);

                $auditoria->registrar('Fechas especiales', 'crear', 'Extensión de feriado.', $nuevo, null, $this->snapshotFechaEspecial($nuevo));
            }
        }

        $fechaEspecial->update([
            'fecha'       => $data['editFecha'],
            'sucursal'    => $data['editSucursal'],
            'nombre'      => $data['editNombre'],
            'descripcion' => $data['editDescripcion'] ?: null,
            'tipo'        => $data['editTipo'],
            'hora_entrada' => $this->normalizarHora($data['editTipo'], $data['editHoraEntrada']),
            'hora_salida'  => $this->normalizarHora($data['editTipo'], $data['editHoraSalida']),
        ]);

        app(AuditoriaService::class)->registrar(
            'Fechas especiales', 'editar', 'Se actualizo una fecha especial.',
            $fechaEspecial->fresh(), $antes, $this->snapshotFechaEspecial($fechaEspecial->fresh())
        );

        $this->closeEditModal();
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
            'Fechas especiales', 'eliminar', 'Se elimino una fecha especial.',
            $fechaEspecial, $antes, ['eliminado' => true]
        );

        $this->closeDeleteModal();
        session()->flash('status', 'Fecha especial eliminada correctamente.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $reference  = Carbon::parse($this->calendarMonth);
        $calDays    = $this->calendarDays($reference);
        $dayRecords = $this->registrosDelDia($this->selectedDate);

        return view('livewire.fechas-especiales', [
            'calendar'   => $calDays,
            'dayRecords' => $dayRecords,
            'sucursales' => $this->sucursalesDisponibles(),
            'monthLabel' => ucfirst($reference->locale('es')->translatedFormat('F Y')),
            'weekdays'   => ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            'prevLabel'  => ucfirst($reference->copy()->subMonth()->locale('es')->translatedFormat('F Y')),
            'nextLabel'  => ucfirst($reference->copy()->addMonth()->locale('es')->translatedFormat('F Y')),
        ])->layout('layouts.app', ['title' => 'Programación laboral']);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function calendarDays(Carbon $reference): array
    {
        $start   = $reference->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end     = $reference->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $current = $start->copy();

        // Cargar todas las fechas especiales del rango de una sola vez
        $especiales = FechaEspecialLaboral::query()
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $end->toDateString())
            ->get()
            ->groupBy(fn ($f) => $f->fecha?->format('Y-m-d'));

        $weeks = [];

        while ($current->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day     = $current->copy();
                $dateStr = $day->format('Y-m-d');
                $items   = $especiales->get($dateStr, collect());

                $tipos   = $items->pluck('tipo')->unique()->all();
                $esFeriado  = in_array('feriado', $tipos, true);
                $esParo     = in_array('paro', $tipos, true);
                $esHorario  = in_array('horario_especial', $tipos, true);

                $week[] = [
                    'date'             => $dateStr,
                    'label'            => $day->day,
                    'is_current_month' => $day->month === $reference->month,
                    'is_today'         => $day->isToday(),
                    'is_sunday'        => $day->dayOfWeek === Carbon::SUNDAY,
                    'is_saturday'      => $day->dayOfWeek === Carbon::SATURDAY,
                    'is_holiday'       => $esFeriado,
                    'is_paro'          => $esParo,
                    'has_special'      => $esHorario,
                    'has_any'          => $items->isNotEmpty(),
                    'items_count'      => $items->count(),
                    'is_selected'      => $dateStr === $this->selectedDate,
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function registrosDelDia(string $date): array
    {
        return FechaEspecialLaboral::query()
            ->whereDate('fecha', $date)
            ->orderBy('sucursal')
            ->get()
            ->map(fn ($f) => [
                'id'          => $f->id,
                'nombre'      => $f->nombre,
                'tipo'        => $f->tipo,
                'sucursal'    => $f->sucursal_label,
                'hora_entrada' => $f->hora_entrada ? substr($f->hora_entrada, 0, 5) : null,
                'hora_salida'  => $f->hora_salida  ? substr($f->hora_salida, 0, 5)  : null,
                'descripcion' => $f->descripcion,
            ])
            ->all();
    }

    /**
     * Genera la lista de fechas en el rango [fechaInicio, fechaFin]
     * - Incluye sábados si $incluirSabados = true
     * - Siempre excluye domingos
     */
    private function resolverFechas(string $fechaInicio, string $fechaFin, bool $incluirSabados = true, array $ignoreIds = []): array
    {
        $start  = Carbon::parse($fechaInicio)->startOfDay();
        $end    = Carbon::parse($fechaFin)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $fechas = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            // Excluir domingos siempre
            if ($current->dayOfWeek === Carbon::SUNDAY) {
                $current->addDay();
                continue;
            }

            // Excluir sábados si no se quieren incluir
            if (! $incluirSabados && $current->dayOfWeek === Carbon::SATURDAY) {
                $current->addDay();
                continue;
            }

            $fechas[] = $current->toDateString();
            $current->addDay();
        }

        return $fechas;
    }

    private function rules(): array
    {
        return [
            'fecha'       => ['required', 'date'],
            'fechaFin'    => ['nullable', 'date', 'after_or_equal:fecha'],
            'sucursal'    => ['required', 'string', 'max:120'],
            'nombre'      => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo'        => ['required', Rule::in(['feriado', 'horario_especial', 'paro'])],
            'horaEntrada' => ['nullable', 'date_format:H:i'],
            'horaSalida'  => ['nullable', 'date_format:H:i'],
        ];
    }

    private function editRules(): array
    {
        return [
            'editingFechaId'  => ['required', Rule::exists('fechas_especiales_laborales', 'id')],
            'editFecha'       => ['required', 'date'],
            'editFechaFin'    => ['nullable', 'date', 'after_or_equal:editFecha'],
            'editSucursal'    => ['required', 'string', 'max:120'],
            'editNombre'      => ['required', 'string', 'max:150'],
            'editDescripcion' => ['nullable', 'string', 'max:500'],
            'editTipo'        => ['required', Rule::in(['feriado', 'horario_especial', 'paro'])],
            'editHoraEntrada' => ['nullable', 'date_format:H:i'],
            'editHoraSalida'  => ['nullable', 'date_format:H:i'],
        ];
    }

    private function messages(): array
    {
        return [
            'fecha.required'          => 'Selecciona la fecha a programar.',
            'fechaFin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'sucursal.required'       => 'Selecciona la sucursal afectada.',
            'nombre.required'         => 'Ingresa un nombre para la fecha especial.',
            'tipo.required'           => 'Selecciona el tipo de jornada.',
            'horaEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'horaSalida.date_format'  => 'La hora de salida debe tener formato HH:MM.',
            'editFecha.required'      => 'Selecciona la fecha a programar.',
            'editFechaFin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'editSucursal.required'   => 'Selecciona la sucursal afectada.',
            'editNombre.required'     => 'Ingresa un nombre para la fecha especial.',
            'editTipo.required'       => 'Selecciona el tipo de jornada.',
            'editHoraEntrada.date_format' => 'La hora de entrada debe tener formato HH:MM.',
            'editHoraSalida.date_format'  => 'La hora de salida debe tener formato HH:MM.',
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

    private function resetCreateForm(): void
    {
        $this->fecha          = $this->selectedDate ?: now()->toDateString();
        $this->fechaFin       = $this->selectedDate ?: now()->toDateString();
        $this->sucursal       = 'TODAS';
        $this->nombre         = '';
        $this->descripcion    = '';
        $this->tipo           = 'feriado';
        $this->horaEntrada    = '';
        $this->horaSalida     = '';
        $this->incluirSabados = true;
    }


    private function sucursalesDisponibles(): array
    {
        return SucursalNormalizer::optionsFromValues(
            collect()
                ->merge(Empleado::query()->whereNotNull('sucursal')->distinct()->orderBy('sucursal')->pluck('sucursal'))
                ->merge(HorarioRegional::query()->distinct()->orderBy('sucursal')->pluck('sucursal')),
            true
        );
    }

    private function snapshotFechaEspecial(FechaEspecialLaboral $f): array
    {
        return [
            'id'          => $f->id,
            'fecha'       => $f->fecha?->toDateString(),
            'sucursal'    => $f->sucursal,
            'nombre'      => $f->nombre,
            'descripcion' => $f->descripcion,
            'tipo'        => $f->tipo,
            'hora_entrada' => $f->hora_entrada,
            'hora_salida'  => $f->hora_salida,
        ];
    }
}
