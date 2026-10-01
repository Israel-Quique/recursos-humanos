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

    // Pestaña activa: 'personal' (directorio y estado) o 'marcaciones' (detalle del mes)
    public string $tab = 'personal';

    // Filtros globales (enfoque mensual, sin días fragmentados)
    public string $search = '';
    public string $sucursalFiltro = '';
    public string $mesFiltroGlobal = ''; // 'Y-m'
    public string $tipoRangoFiltro = 'mes'; // 'mes', 'dia', 'rango' (retrocompatibilidad)
    public string $fechaDiaFiltro = '';
    public string $fechaInicioFiltro = '';
    public string $fechaFinFiltro = '';

    // Modal Resumen de Cambios / Modificaciones Realizadas
    public bool $showResumenCambiosModal = false;
    public ?int $resumenEmpleadoId = null;

    // Modal Registro Marcación Especial (Crear/Editar individual)
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

    // Modal Nuevo Enfoque: Marcación Entrada y Salida Especial (Mensual)
    public bool $showModalEspecial = false;
    public string $modalSearch = '';
    public ?int $selectedEmpleadoId = null;
    public string $mesSeleccionado = ''; // 'Y-m'
    public array $diasMes = [];
    public string $observacionGeneralMes = 'Autorizado por RRHH - Personal Especial';

    protected array $rules = [
        'diasMes.*.hora_entrada' => 'nullable',
        'diasMes.*.hora_salida' => 'nullable',
        'diasMes.*.observacion' => 'nullable',
    ];

    // Modales de compatibilidad backend
    public bool $showCreateEmpleadoModal = false;
    public string $nuevoNombre = '';
    public string $nuevoApellido = '';
    public string $nuevoCodigoBiometrico = '';
    public string $nuevaArea = '';
    public string $nuevaSucursal = '';
    public string $nuevaHoraEntrada = '';
    public string $nuevaHoraSalida = '';

    public bool $showVincularModal = false;
    public ?int $vincularEmpleadoId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $now = now();
        $this->mesFiltroGlobal = $now->format('Y-m');
        $this->fechaDiaFiltro = $now->toDateString();
        $this->fechaInicioFiltro = $now->copy()->startOfMonth()->toDateString();
        $this->fechaFinFiltro = $now->toDateString();
        $this->fecha = $now->toDateString();
        $this->mesSeleccionado = $now->format('Y-m');
    }

    public function openResumenCambiosModal(?int $empleadoId = null): void
    {
        $this->resumenEmpleadoId = $empleadoId;
        $this->showResumenCambiosModal = true;
    }

    public function closeResumenCambiosModal(): void
    {
        $this->showResumenCambiosModal = false;
        $this->resumenEmpleadoId = null;
    }

    public function updatingMesFiltroGlobal(): void
    {
        $this->resetPage();
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
    // GESTIÓN MENSUAL DE ENTRADAS Y SALIDAS ESPECIALES (NUEVO ENFOQUE)
    // ─────────────────────────────────────────────────────────────

    public function openModalEspecial(?int $empleadoId = null): void
    {
        $this->resetValidation();
        $this->showModalEspecial = true;
        $this->modalSearch = '';
        $this->observacionGeneralMes = 'Autorizado por RRHH - Personal Especial';
        if (blank($this->mesSeleccionado)) {
            $this->mesSeleccionado = now()->format('Y-m');
        }

        if ($empleadoId) {
            $this->selectEmpleado($empleadoId);
        } elseif ($this->selectedEmpleadoId) {
            $this->cargarMarcacionesMes();
        } else {
            $this->diasMes = [];
        }
    }

    public function closeModalEspecial(): void
    {
        $this->showModalEspecial = false;
        // No reseteamos selectedEmpleadoId para mantener la selección al reabrir
    }

    public function selectEmpleado(int $empleadoId): void
    {
        $this->selectedEmpleadoId = $empleadoId;
        $this->modalSearch = '';
        $this->cargarMarcacionesMes();
    }

    public function deseleccionarEmpleado(): void
    {
        $this->selectedEmpleadoId = null;
        $this->diasMes = [];
        $this->modalSearch = '';
    }

    public function cambiarMes(string $mes): void
    {
        $this->mesSeleccionado = $mes;
        $this->cargarMarcacionesMes();
    }

    public function irMesAnterior(): void
    {
        $carbon = Carbon::parse($this->mesSeleccionado . '-01')->subMonth();
        $this->mesSeleccionado = $carbon->format('Y-m');
        $this->cargarMarcacionesMes();
    }

    public function irMesSiguiente(): void
    {
        $carbon = Carbon::parse($this->mesSeleccionado . '-01')->addMonth();
        $this->mesSeleccionado = $carbon->format('Y-m');
        $this->cargarMarcacionesMes();
    }

    public function irMesActual(): void
    {
        $this->mesSeleccionado = now()->format('Y-m');
        $this->cargarMarcacionesMes();
    }

    public function cargarMarcacionesMes(): void
    {
        if (! $this->selectedEmpleadoId) {
            $this->diasMes = [];
            return;
        }

        $empleado = Empleado::query()->find($this->selectedEmpleadoId);
        if (! $empleado) {
            $this->diasMes = [];
            return;
        }

        $startOfMonth = Carbon::parse($this->mesSeleccionado . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $endOfMonth->day;

        $registrosExistentes = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereBetween('fecha', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($r) => $r->fecha?->toDateString());

        $horaEntradaHabitual = $empleado->hora_entrada_programada ? substr($empleado->hora_entrada_programada, 0, 5) : '08:30';
        $horaSalidaHabitual = $empleado->hora_salida_programada ? substr($empleado->hora_salida_programada, 0, 5) : '17:30';

        $dias = [];
        for ($dia = 1; $dia <= $daysInMonth; $dia++) {
            $currentDate = $startOfMonth->copy()->day($dia);

            // Quitar sábados y domingos: el enfoque es estrictamente días laborales
            if ($currentDate->isWeekend()) {
                continue;
            }

            $dateString = $currentDate->toDateString();
            $registro = $registrosExistentes->get($dateString);

            $entradaExistente = $registro?->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '';
            $salidaExistente = $registro?->hora_salida ? substr($registro->hora_salida, 0, 5) : '';

            $dias[$dateString] = [
                'fecha' => $dateString,
                'dia_numero' => $dia,
                'dia_nombre' => ucfirst($currentDate->locale('es')->isoFormat('dddd')),
                'dia_corto' => ucfirst($currentDate->locale('es')->isoFormat('ddd')),
                'es_fin_de_semana' => false,
                'es_hoy' => $currentDate->isToday(),
                'tiene_registro' => $registro !== null,
                'registro_id' => $registro?->id,
                'hora_entrada_original' => $entradaExistente,
                'hora_salida_original' => $salidaExistente,
                'hora_entrada' => $entradaExistente,
                'hora_salida' => $salidaExistente,
                'es_especial' => $registro?->tipo_verificacion === 'Especial',
                'estado_marcacion' => $registro?->estado_marcacion ?? 'Sin marcación',
                'observacion' => $registro?->observacion ?? '',
                'hora_entrada_habitual' => $horaEntradaHabitual,
                'hora_salida_habitual' => $horaSalidaHabitual,
            ];
        }

        $this->diasMes = $dias;
    }

    public function aplicarHorarioHabitualDia(string $fecha): void
    {
        if (! isset($this->diasMes[$fecha])) {
            return;
        }

        $habitualEntrada = $this->diasMes[$fecha]['hora_entrada_habitual'] ?? '08:30';
        $habitualSalida = $this->diasMes[$fecha]['hora_salida_habitual'] ?? '17:30';

        // Si ya tenía entrada y le faltaba salida, solo rellenar salida
        if (filled($this->diasMes[$fecha]['hora_entrada']) && blank($this->diasMes[$fecha]['hora_salida'])) {
            $this->diasMes[$fecha]['hora_salida'] = $habitualSalida;
        } else {
            $this->diasMes[$fecha]['hora_entrada'] = $habitualEntrada;
            $this->diasMes[$fecha]['hora_salida'] = $habitualSalida;
        }
    }

    public function aplicarSalidaHabitualPendientes(): void
    {
        $actualizados = 0;
        foreach ($this->diasMes as $fecha => $dia) {
            $tieneEntrada = filled($dia['hora_entrada']);
            $faltaSalida = blank($dia['hora_salida']);

            if ($tieneEntrada && $faltaSalida) {
                $this->diasMes[$fecha]['hora_salida'] = $dia['hora_salida_habitual'] ?? '17:30';
                $actualizados++;
            }
        }

        if ($actualizados > 0) {
            session()->flash('modal_status', "Se autocompletó la salida habitual en {$actualizados} días pendientes. Haz clic en 'Guardar todo el mes' para confirmar.");
        } else {
            session()->flash('modal_warning', 'No se encontraron días con entrada registrada y salida pendiente.');
        }
    }

    public function aplicarHorarioLaborablesMes(): void
    {
        $actualizados = 0;
        foreach ($this->diasMes as $fecha => $dia) {
            if (! $dia['es_fin_de_semana']) {
                if (blank($dia['hora_entrada'])) {
                    $this->diasMes[$fecha]['hora_entrada'] = $dia['hora_entrada_habitual'] ?? '08:30';
                }
                if (blank($dia['hora_salida'])) {
                    $this->diasMes[$fecha]['hora_salida'] = $dia['hora_salida_habitual'] ?? '17:30';
                }
                $actualizados++;
            }
        }

        if ($actualizados > 0) {
            session()->flash('modal_status', "Se asignó horario habitual a {$actualizados} días laborables del mes. Haz clic en 'Guardar todo el mes' para confirmar.");
        }
    }

    private function guardarMarcacionInterna(string $fecha, array $diaData, Empleado $empleado): bool
    {
        $horaEntrada = trim((string) ($diaData['hora_entrada'] ?? ''));
        $horaSalida = trim((string) ($diaData['hora_salida'] ?? ''));
        $observacion = trim((string) ($diaData['observacion'] ?? ''));

        // Si ambos están vacíos, no hay nada que guardar
        if ($horaEntrada === '' && $horaSalida === '') {
            return false;
        }

        // Si el empleado aún no es especial, activarlo
        if (! $empleado->es_especial) {
            $empleado->update(['es_especial' => true]);
        }

        // Buscar si ya existe registro en la base de datos
        $registroExistente = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', $fecha)
            ->first();

        // Normalizar entrada
        $formattedEntrada = null;
        if ($horaEntrada !== '') {
            $formattedEntrada = strlen($horaEntrada) === 5 ? $horaEntrada . ':00' : $horaEntrada;
        } elseif ($registroExistente?->hora_entrada) {
            $formattedEntrada = $registroExistente->hora_entrada;
        }

        // Normalizar salida
        $formattedSalida = null;
        if ($horaSalida !== '') {
            $formattedSalida = strlen($horaSalida) === 5 ? $horaSalida . ':00' : $horaSalida;
        } elseif ($registroExistente?->hora_salida) {
            $formattedSalida = $registroExistente->hora_salida;
        }

        $estadoMarcacion = ($formattedEntrada && $formattedSalida)
            ? 'Marcacion completa'
            : ($formattedEntrada ? 'Solo entrada' : 'Solo salida');

        $obsFinal = $observacion ?: ($this->observacionGeneralMes ?: 'Marcación manual autorizada RRHH - Personal Especial');

        if ($registroExistente) {
            $antes = $this->snapshotRegistro($registroExistente);

            $registroExistente->update([
                'hora_entrada' => $formattedEntrada,
                'hora_salida' => $formattedSalida,
                'tipo_verificacion' => 'Especial',
                'estado_marcacion' => $estadoMarcacion,
                'evento_biometrico' => 'Ingreso y salida especial RRHH',
                'observacion' => $obsFinal,
                'updated_by' => auth()->id(),
            ]);

            app(AuditoriaService::class)->registrar(
                'Personal Especial',
                'actualizar_marcacion_manual',
                "Marcación manual especial actualizada para {$empleado->nombre_completo} el {$fecha}.",
                $registroExistente,
                $antes,
                $this->snapshotRegistro($registroExistente)
            );
        } else {
            $nuevo = RegistroAsistencia::query()->create([
                'empleado_id' => $empleado->id,
                'fecha' => $fecha,
                'hora_entrada' => $formattedEntrada,
                'hora_salida' => $formattedSalida,
                'tipo_verificacion' => 'Especial',
                'estado_marcacion' => $estadoMarcacion,
                'evento_biometrico' => 'Ingreso y salida especial RRHH',
                'observacion' => $obsFinal,
                'created_by' => auth()->id(),
            ]);

            app(AuditoriaService::class)->registrar(
                'Personal Especial',
                'crear_marcacion_manual',
                "Marcación manual especial registrada para {$empleado->nombre_completo} el {$fecha}.",
                $nuevo,
                null,
                $this->snapshotRegistro($nuevo)
            );
        }

        return true;
    }

    public function guardarMarcacionDia(string $fecha): void
    {
        if (! $this->selectedEmpleadoId || ! isset($this->diasMes[$fecha])) {
            return;
        }

        $empleado = Empleado::query()->findOrFail($this->selectedEmpleadoId);
        $diaData = $this->diasMes[$fecha];

        $guardado = $this->guardarMarcacionInterna($fecha, $diaData, $empleado);

        if ($guardado) {
            $this->cargarMarcacionesMes();
            session()->flash('modal_status', "✓ Marcación guardada correctamente para el día {$fecha}.");
            session()->flash('status', "✓ Marcación guardada para {$empleado->nombre_completo} ({$fecha}).");
        } else {
            $this->addError("diasMes.{$fecha}.hora_salida", 'Ingresa al menos la hora de entrada o salida.');
        }
    }

    public function guardarTodoElMes(): void
    {
        if (! $this->selectedEmpleadoId) {
            return;
        }

        $empleado = Empleado::query()->findOrFail($this->selectedEmpleadoId);
        $copiaDias = $this->diasMes;
        $guardados = 0;

        foreach ($copiaDias as $fecha => $dia) {
            $horaEntrada = trim((string) ($dia['hora_entrada'] ?? ''));
            $horaSalida = trim((string) ($dia['hora_salida'] ?? ''));
            $origEntrada = trim((string) ($dia['hora_entrada_original'] ?? ''));
            $origSalida = trim((string) ($dia['hora_salida_original'] ?? ''));

            $hayCambio = ($horaEntrada !== $origEntrada) || ($horaSalida !== $origSalida);
            $tieneAlgunaHora = ($horaEntrada !== '' || $horaSalida !== '');

            if ($hayCambio && $tieneAlgunaHora) {
                if ($this->guardarMarcacionInterna($fecha, $dia, $empleado)) {
                    $guardados++;
                }
            }
        }

        // Se recarga UNA SOLA VEZ al final del proceso completo
        $this->cargarMarcacionesMes();

        if ($guardados > 0) {
            session()->flash('modal_status', "✓ Se guardaron {$guardados} marcaciones del mes exitosamente para {$empleado->nombre_completo}.");
            session()->flash('status', "✓ Se guardaron {$guardados} marcaciones especiales para {$empleado->nombre_completo}.");
        } else {
            session()->flash('modal_warning', 'No se detectaron cambios pendientes o datos para guardar en el mes.');
        }
    }

    public function limpiarMarcacionDia(string $fecha): void
    {
        if (! $this->selectedEmpleadoId) {
            return;
        }

        $registro = RegistroAsistencia::query()
            ->where('empleado_id', $this->selectedEmpleadoId)
            ->whereDate('fecha', $fecha)
            ->first();

        if ($registro) {
            $antes = $this->snapshotRegistro($registro);
            $registro->delete();

            app(AuditoriaService::class)->registrar(
                'Personal Especial',
                'eliminar_marcacion_manual',
                "Se eliminó marcación especial del día {$fecha}.",
                $registro,
                $antes,
                ['eliminado' => true]
            );

            $this->cargarMarcacionesMes();
            session()->flash('modal_status', "Marcación del día {$fecha} eliminada.");
        }
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
        $mesActivo = filled($this->mesFiltroGlobal) ? $this->mesFiltroGlobal : now()->format('Y-m');
        $startMes = Carbon::parse($mesActivo . '-01')->startOfMonth()->toDateString();
        $endMes = Carbon::parse($mesActivo . '-01')->endOfMonth()->toDateString();

        // Rango de fechas activo: por defecto el mes activo completo
        if ($this->tipoRangoFiltro === 'dia') {
            $startDate = filled($this->fechaDiaFiltro) ? $this->fechaDiaFiltro : now()->toDateString();
            $endDate = $startDate;
        } elseif ($this->tipoRangoFiltro === 'rango') {
            $startDate = filled($this->fechaInicioFiltro) ? $this->fechaInicioFiltro : now()->copy()->startOfWeek()->toDateString();
            $endDate = filled($this->fechaFinFiltro) ? $this->fechaFinFiltro : now()->toDateString();
            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }
        } else {
            // 'mes' (enfoque global mensual)
            $startDate = $startMes;
            $endDate = $endMes;
        }

        $searchOperator = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Candidatos sin régimen especial encontrados en el buscador para vincular directamente
        $candidatosVincular = collect();
        if (filled($this->search) && strlen(trim($this->search)) >= 2) {
            $termClean = trim($this->search);
            $term = '%' . $termClean . '%';
            $termLower = '%' . mb_strtolower($termClean) . '%';
            $candidatosVincular = Empleado::query()
                ->where('es_especial', false)
                ->where(function ($q) use ($term, $termLower, $searchOperator) {
                    $q->where('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhere('codigo_biometrico', $searchOperator, $term)
                        ->orWhereRaw("LOWER(COALESCE(nombre, '') || ' ' || COALESCE(apellido, '')) LIKE ?", [$termLower]);
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

        // Métricas Globales del Mes
        $totalEspeciales = Empleado::query()->where('es_especial', true)->count();
        $registrosMesCount = RegistroAsistencia::query()
            ->where(function ($q) {
                $q->where('tipo_verificacion', 'Especial')
                    ->orWhereHas('empleado', fn($eq) => $eq->where('es_especial', true));
            })
            ->whereBetween('fecha', [$startMes, $endMes])
            ->count();
        $salidasPendientesMesCount = RegistroAsistencia::query()
            ->where(function ($q) {
                $q->where('tipo_verificacion', 'Especial')
                    ->orWhereHas('empleado', fn($eq) => $eq->where('es_especial', true));
            })
            ->whereBetween('fecha', [$startMes, $endMes])
            ->whereNotNull('hora_entrada')
            ->whereNull('hora_salida')
            ->count();
        $registrosHoyCount = RegistroAsistencia::query()
            ->whereHas('empleado', fn($q) => $q->where('es_especial', true))
            ->whereDate('fecha', now()->toDateString())
            ->count();

        // Query principal de Registros de Marcaciones Especiales
        $registrosQuery = RegistroAsistencia::query()
            ->with(['empleado', 'creador', 'actualizadoPor'])
            ->where(function ($q) {
                $q->where('tipo_verificacion', 'Especial')
                    ->orWhereHas('empleado', fn($eq) => $eq->where('es_especial', true));
            })
            ->whereBetween('fecha', [$startDate, $endDate])
            ->when(filled($this->sucursalFiltro), function ($q) {
                $q->whereHas('empleado', fn($eq) => SucursalNormalizer::applyFilter($eq, 'sucursal', $this->sucursalFiltro));
            })
            ->when(filled($this->search), function ($q) use ($searchOperator) {
                $termClean = trim($this->search);
                $term = '%' . $termClean . '%';
                $termLower = '%' . mb_strtolower($termClean) . '%';
                $q->whereHas('empleado', function ($eq) use ($term, $termLower, $searchOperator) {
                    $eq->where('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhere('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('area', $searchOperator, $term)
                        ->orWhereRaw("LOWER(COALESCE(nombre, '') || ' ' || COALESCE(apellido, '')) LIKE ?", [$termLower]);
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora_entrada');

        $registrosPaginados = $registrosQuery->paginate(15, ['*'], 'registrosPage');

        // Query de Personal Especial (pestaña 'personal')
        $personalQuery = Empleado::query()
            ->where('es_especial', true)
            ->when(filled($this->sucursalFiltro), fn($q) => SucursalNormalizer::applyFilter($q, 'sucursal', $this->sucursalFiltro))
            ->when(filled($this->search), function ($q) use ($searchOperator) {
                $termClean = trim($this->search);
                $term = '%' . $termClean . '%';
                $termLower = '%' . mb_strtolower($termClean) . '%';
                $q->where(function ($inner) use ($term, $termLower, $searchOperator) {
                    $inner->where('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhere('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('area', $searchOperator, $term)
                        ->orWhereRaw("LOWER(COALESCE(nombre, '') || ' ' || COALESCE(apellido, '')) LIKE ?", [$termLower]);
                });
            })
            ->withCount([
                'asistencias' => function ($q) use ($startMes, $endMes) {
                    $q->whereBetween('fecha', [$startMes, $endMes]);
                },
                'asistencias as completas_mes_count' => function ($q) use ($startMes, $endMes) {
                    $q->whereBetween('fecha', [$startMes, $endMes])
                        ->whereNotNull('hora_entrada')
                        ->whereNotNull('hora_salida');
                },
                'asistencias as pendientes_salida_mes_count' => function ($q) use ($startMes, $endMes) {
                    $q->whereBetween('fecha', [$startMes, $endMes])
                        ->whereNotNull('hora_entrada')
                        ->whereNull('hora_salida');
                },
            ])
            ->orderBy('nombre')
            ->orderBy('apellido');

        $personalPaginado = $personalQuery->paginate(15, ['*'], 'personalPage');

        // Candidatos buscados en el modal de entrada/salida especial (Búsqueda inteligente insensible a mayúsculas)
        $candidatosModal = collect();
        if ($this->showModalEspecial) {
            $modalSearchClean = trim($this->modalSearch);
            if (filled($modalSearchClean)) {
                $term = '%' . $modalSearchClean . '%';
                $termLower = '%' . mb_strtolower($modalSearchClean) . '%';
                $words = preg_split('/\s+/', mb_strtolower($modalSearchClean), -1, PREG_SPLIT_NO_EMPTY);

                $candidatosModal = Empleado::query()
                    ->where(function ($q) use ($term, $termLower, $searchOperator, $words) {
                        $q->where('nombre', $searchOperator, $term)
                            ->orWhere('apellido', $searchOperator, $term)
                            ->orWhere('codigo_biometrico', $searchOperator, $term)
                            ->orWhereRaw("LOWER(COALESCE(nombre, '') || ' ' || COALESCE(apellido, '')) LIKE ?", [$termLower])
                            ->orWhereRaw("LOWER(COALESCE(apellido, '') || ' ' || COALESCE(nombre, '')) LIKE ?", [$termLower])
                            ->orWhereRaw("LOWER(COALESCE(codigo_biometrico, '')) LIKE ?", [$termLower]);

                        if (count($words) > 1) {
                            $q->orWhere(function ($sub) use ($words, $searchOperator) {
                                foreach ($words as $w) {
                                    $wt = '%' . $w . '%';
                                    $sub->where(function ($wQuery) use ($wt, $w, $searchOperator) {
                                        $wQuery->where('nombre', $searchOperator, $wt)
                                            ->orWhere('apellido', $searchOperator, $wt)
                                            ->orWhere('codigo_biometrico', $searchOperator, $wt)
                                            ->orWhereRaw("LOWER(COALESCE(nombre, '') || ' ' || COALESCE(apellido, '')) LIKE ?", ['%' . $w . '%']);
                                    });
                                }
                            });
                        }
                    })
                    ->orderBy('nombre')
                    ->take(15)
                    ->get();
            } else {
                // Sugerencias inmediatas de personal especial activo si no ha escrito nada
                $candidatosModal = Empleado::query()
                    ->where('es_especial', true)
                    ->orderBy('nombre')
                    ->take(8)
                    ->get();
            }
        }

        $selectedEmpleado = $this->selectedEmpleadoId ? Empleado::query()->find($this->selectedEmpleadoId) : null;

        $statsMesEmpleado = [
            'totalDias' => count($this->diasMes),
            'completas' => count(array_filter($this->diasMes, fn ($d) => filled($d['hora_entrada']) && filled($d['hora_salida']))),
            'soloEntrada' => count(array_filter($this->diasMes, fn ($d) => filled($d['hora_entrada']) && blank($d['hora_salida']))),
            'soloSalida' => count(array_filter($this->diasMes, fn ($d) => blank($d['hora_entrada']) && filled($d['hora_salida']))),
            'sinRegistro' => count(array_filter($this->diasMes, fn ($d) => blank($d['hora_entrada']) && blank($d['hora_salida']))),
        ];

        // Resumen de Cambios para el modal de auditoría/modificaciones
        $resumenCambios = collect();
        $empleadoResumen = null;
        if ($this->showResumenCambiosModal) {
            if ($this->resumenEmpleadoId) {
                $empleadoResumen = Empleado::find($this->resumenEmpleadoId);
            }
            $resumenCambios = RegistroAsistencia::query()
                ->with(['empleado', 'creador', 'actualizadoPor'])
                ->where(function ($q) {
                    $q->where('tipo_verificacion', 'Especial')
                        ->orWhereHas('empleado', fn($eq) => $eq->where('es_especial', true));
                })
                ->when($this->resumenEmpleadoId, fn($q) => $q->where('empleado_id', $this->resumenEmpleadoId))
                ->orderByDesc('updated_at')
                ->take(50)
                ->get();
        }

        return view('livewire.personal-especial', [
            'registros' => $registrosPaginados,
            'personalEspecial' => $personalPaginado,
            'empleadosEspecialesList' => $empleadosEspecialesList,
            'empleadosParaVincular' => $empleadosParaVincular,
            'candidatosVincular' => $candidatosVincular,
            'candidatosModal' => $candidatosModal,
            'selectedEmpleado' => $selectedEmpleado,
            'statsMesEmpleado' => $statsMesEmpleado,
            'resumenCambios' => $resumenCambios,
            'empleadoResumen' => $empleadoResumen,
            'sucursales' => $sucursales,
            'totalEspeciales' => $totalEspeciales,
            'registrosMesCount' => $registrosMesCount,
            'salidasPendientesMesCount' => $salidasPendientesMesCount,
            'registrosHoyCount' => $registrosHoyCount,
            'mesActivo' => $mesActivo,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->layout('layouts.app', ['title' => 'Personal Especial']);
    }
}
