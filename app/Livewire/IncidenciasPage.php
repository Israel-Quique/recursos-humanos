<?php

namespace App\Livewire;

use App\Mail\BoletaEstadoMailable;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Services\AuditoriaService;
use App\Services\ProgramacionLaboralService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class IncidenciasPage extends Component
{
    use WithPagination;
    use WithFileUploads;

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
    public string $alcance = 'dias';
    public string $estado = 'aprobado';
    public string $fechaInicio = '';
    public string $fechaFin = '';
    public string $horaInicio = '';
    public string $horaFin = '';
    public string $motivo = '';
    public string $tipoPermiso = '';
    public string $empleadoSearch = '';
    public string $editEmpleadoId = '';
    public string $editEmpleadoSearch = '';
    public string $editTipo = 'permiso';
    public string $editAlcance = 'dias';
    public string $editEstado = 'aprobado';
    public string $editFechaInicio = '';
    public string $editFechaFin = '';
    public string $editHoraInicio = '';
    public string $editHoraFin = '';
    public string $editMotivo = '';
    public string $editTipoPermiso = '';

    // Modal para ver imagen del comprobante
    public bool $showComprobanteModal = false;
    public ?string $modalComprobanteUrl = null;
    public ?string $modalComprobanteTitulo = null;
    public ?string $modalComprobanteDetalle = null;

    // Popup modal de confirmación para aprobar / rechazar
    public bool $showConfirmModal = false;
    public ?int $confirmandoIncidenciaId = null;
    public ?string $confirmandoNuevoEstado = null;
    public ?string $confirmandoEmpleadoNombre = null;
    public ?string $confirmandoDetalle = null;
    public string $motivoRechazo = '';

    // Carga opcional de comprobante desde panel RRHH
    public $comprobante = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        $this->mesFiltro = ''; // Por defecto muestra lo más reciente
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

    public function updatedTipoPermiso(string $value): void
    {
        if ($value !== '') {
            $this->motivo = $this->tiposPermisoDisponibles()[$value] ?? $this->motivo;
        }
    }

    public function updatedEditTipo(string $value): void
    {
        $this->sincronizarReglaTipo($value, true);
    }

    public function updatedEditAlcance(string $value): void
    {
        $this->sincronizarReglaAlcance($value, true);
    }

    public function updatedEditTipoPermiso(string $value): void
    {
        if ($value !== '') {
            $this->editMotivo = $this->tiposPermisoDisponibles()[$value] ?? $this->editMotivo;
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

    public function openEditModal(int $incidenciaId): void
    {
        $incidencia = PermisoLaboral::query()->findOrFail($incidenciaId);

        $this->editingIncidenciaId = $incidencia->id;
        $this->editEmpleadoId = (string) $incidencia->empleado_id;
        $this->editTipo = $incidencia->tipo;
        $this->editAlcance = in_array($incidencia->alcance, ['dias', 'horas'], true) ? $incidencia->alcance : 'dias';
        $this->editEstado = $incidencia->estado;
        $this->editFechaInicio = $incidencia->fecha_inicio?->toDateString() ?? '';
        $this->editFechaFin = $incidencia->fecha_fin?->toDateString() ?? '';
        $this->editHoraInicio = $incidencia->hora_inicio ? substr($incidencia->hora_inicio, 0, 5) : '';
        $this->editHoraFin = $incidencia->hora_fin ? substr($incidencia->hora_fin, 0, 5) : '';
        $this->editMotivo = $incidencia->motivo ?? '';
        $this->editTipoPermiso = $this->resolverTipoPermisoDesdeMotivo($this->editMotivo);
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
            ? $incidencia->empleado->nombre_completo . ' - ' . $incidencia->tipo_label
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
        if (!$this->pendingDeleteIncidenciaId) {
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

    public function verComprobante(int $incidenciaId): void
    {
        $incidencia = PermisoLaboral::query()
            ->with(['empleado', 'comprobantePrincipal'])
            ->findOrFail($incidenciaId);

        $comprobante = $incidencia->comprobantePrincipal;
        if (! $comprobante) {
            $this->modalComprobanteUrl = null;
            $this->modalComprobanteTitulo = 'Detalle de Justificación: ' . ($incidencia->empleado?->nombre_completo ?? 'Personal');
            $this->modalComprobanteDetalle = ($incidencia->tipo_label) . ' · ' . ($incidencia->fecha_inicio?->format('d/m/Y') ?? '') . ' · ' . ($incidencia->motivo ?: 'Sin motivo redactado');
            $this->showComprobanteModal = true;
            return;
        }

        $this->modalComprobanteUrl = $comprobante->url;
        $this->modalComprobanteTitulo = 'Comprobante de Justificación: ' . ($incidencia->empleado?->nombre_completo ?? 'Personal');
        $this->modalComprobanteDetalle = ($incidencia->tipo_label) . ' · ' . ($incidencia->fecha_inicio?->format('d/m/Y') ?? '') . ' · ' . ($incidencia->motivo ?: 'Sin motivo');
        $this->showComprobanteModal = true;
    }

    public function cerrarComprobanteModal(): void
    {
        $this->showComprobanteModal = false;
        $this->modalComprobanteUrl = null;
        $this->modalComprobanteTitulo = null;
        $this->modalComprobanteDetalle = null;
    }

    public function abrirConfirmacion(int $incidenciaId, string $nuevoEstado): void
    {
        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($incidenciaId);

        if (in_array($incidencia->estado, ['aprobado', 'rechazado'], true)) {
            session()->flash('warning', 'Esta solicitud ya fue ' . $incidencia->estado . ' y no se puede volver a modificar.');
            return;
        }

        $this->confirmandoIncidenciaId = $incidencia->id;
        $this->confirmandoNuevoEstado = $nuevoEstado;
        $this->confirmandoEmpleadoNombre = $incidencia->empleado?->nombre_completo ?? 'Personal';
        $this->confirmandoDetalle = ($incidencia->tipo_label) . ' · ' . ($incidencia->fecha_inicio?->format('d/m/Y') ?? '') . ($incidencia->motivo ? ' · ' . $incidencia->motivo : '');
        $this->motivoRechazo = '';
        $this->resetValidation('motivoRechazo');
        $this->showConfirmModal = true;
    }

    public function cancelarConfirmacion(): void
    {
        $this->showConfirmModal = false;
        $this->confirmandoIncidenciaId = null;
        $this->confirmandoNuevoEstado = null;
        $this->confirmandoEmpleadoNombre = null;
        $this->confirmandoDetalle = null;
        $this->motivoRechazo = '';
        $this->resetValidation('motivoRechazo');
    }

    public function confirmarAccion(): void
    {
        if (! $this->confirmandoIncidenciaId || ! $this->confirmandoNuevoEstado) {
            $this->cancelarConfirmacion();
            return;
        }

        if ($this->confirmandoNuevoEstado === 'rechazado') {
            $this->validate([
                'motivoRechazo' => ['required', 'string', 'min:3', 'max:500'],
            ], [
                'motivoRechazo.required' => 'Debes ingresar el motivo o justificación del rechazo para notificar al funcionario.',
                'motivoRechazo.min' => 'El motivo del rechazo debe tener al menos 3 caracteres.',
                'motivoRechazo.max' => 'El motivo del rechazo no puede exceder los 500 caracteres.',
            ]);
        }

        $id = $this->confirmandoIncidenciaId;
        $estado = $this->confirmandoNuevoEstado;
        $motivo = trim($this->motivoRechazo);

        $this->cancelarConfirmacion();
        $this->cambiarEstado($id, $estado, $motivo);
    }

    public function cambiarEstado(int $incidenciaId, string $nuevoEstado, ?string $motivoRechazo = null): void
    {
        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($incidenciaId);

        // Bloqueo estricto: una vez aprobado o rechazado, ya no se puede modificar
        if (in_array($incidencia->estado, ['aprobado', 'rechazado'], true)) {
            session()->flash('warning', 'Esta solicitud ya está ' . $incidencia->estado . ' y no se puede volver a modificar.');
            return;
        }

        $antes = $this->snapshotIncidencia($incidencia);
        
        $updateData = ['estado' => $nuevoEstado];
        if ($nuevoEstado === 'rechazado' && filled($motivoRechazo)) {
            $updateData['motivo_rechazo'] = $motivoRechazo;
        }

        $incidencia->update($updateData);

        app(AuditoriaService::class)->registrar(
            'Incidencias',
            'cambiar_estado',
            "Se cambio el estado de la incidencia a {$nuevoEstado}" . (filled($motivoRechazo) ? ". Motivo rechazo: {$motivoRechazo}" : '.'),
            $incidencia,
            $antes,
            $this->snapshotIncidencia($incidencia)
        );

        // Envío de correo electrónico de notificación al funcionario
        $empleado = $incidencia->empleado;
        $correoEnviado = false;

        if ($empleado && filled($empleado->email)) {
            try {
                Mail::to($empleado->email)->send(
                    new BoletaEstadoMailable($incidencia, $nuevoEstado, $motivoRechazo)
                );
                $correoEnviado = true;
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar correo de estado de boleta a {$empleado->email}: " . $e->getMessage());
            }
        }

        $msg = 'Solicitud de ' . ($empleado?->nombre_completo ?? 'personal') . ' marcada como ' . strtoupper($nuevoEstado) . ' exitosamente.';
        if ($correoEnviado) {
            $msg .= " Se envió notificación por correo a: {$empleado->email}.";
        } elseif ($empleado && blank($empleado->email)) {
            $msg .= " (El funcionario no tiene correo registrado, por lo que no se envió correo).";
        }

        session()->flash('status', $msg);
    }

    public function descargarBoletaPdf(int $incidenciaId)
    {
        $incidencia = PermisoLaboral::query()
            ->with(['empleado', 'comprobantePrincipal'])
            ->findOrFail($incidenciaId);

        $empleado = $incidencia->empleado;
        $motivoCompleto = $incidencia->motivo ?: $incidencia->tipo_label;

        // Determinar tipo de boleta (comision, particular, medico)
        $tipoBoleta = 'particular';
        $motivoLower = mb_strtolower($motivoCompleto);
        if (str_contains($motivoLower, 'medico') || str_contains($motivoLower, 'médico')) {
            $tipoBoleta = 'medico';
        } elseif (str_contains($motivoLower, 'comision') || str_contains($motivoLower, 'comisión')) {
            $tipoBoleta = 'comision';
        }

        $desdeFecha = $incidencia->fecha_inicio?->format('d/m/Y') ?? now()->format('d/m/Y');
        $hastaFecha = $incidencia->fecha_fin?->format('d/m/Y') ?? $desdeFecha;
        $desdeHora = $incidencia->hora_inicio ? substr($incidencia->hora_inicio, 0, 5) : '08:30';
        $hastaHora = $incidencia->hora_fin ? substr($incidencia->hora_fin, 0, 5) : '16:30';

        $minutos = (int) ($incidencia->minutos_contabilizados ?? 0);
        if ($minutos > 0) {
            $horas = intdiv($minutos, 60);
            $mins = $minutos % 60;
            $tiempoSolicitado = $horas > 0 ? "{$horas} H {$mins} MIN" : "{$mins} MIN";
        } else {
            $tiempoSolicitado = $incidencia->alcance_label;
        }

        $ciudad = !empty($empleado?->sucursal) ? mb_strtoupper($empleado->sucursal) : 'LA PAZ';
        $fechaTexto = ($incidencia->fecha_inicio ?? now())->locale('es')->translatedFormat('d \de F \de Y');

        $boleta = [
            'nombre' => $empleado?->nombre_completo ?? 'PERSONAL',
            'ci' => (string) ($empleado?->codigo_biometrico ?: $empleado?->id ?: 'S/D'),
            'cargo' => (string) ($empleado?->cargo ?: 'PERSONAL'),
            'motivo' => $motivoCompleto,
            'tipo' => $tipoBoleta,
            'desde_fecha' => $desdeFecha,
            'desde_hora' => $desdeHora,
            'hasta_fecha' => $hastaFecha,
            'hasta_hora' => $hastaHora,
            'tiempo_solicitado' => $tiempoSolicitado,
            'ciudad' => $ciudad,
            'fecha_texto' => $fechaTexto,
            'lugar_fecha' => mb_strtoupper($ciudad) . ', ' . mb_strtoupper($fechaTexto),
        ];

        $pdf = Pdf::loadView('pdf.boleta-permiso', [
            'boleta' => $boleta,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Boleta_Oficial_' . Str::slug($boleta['nombre']) . '_' . ($incidencia->fecha_inicio?->format('Ymd') ?? now()->format('Ymd')) . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        // Optimización de velocidad: no cargar campos binarios pesados en el listado
        $query = PermisoLaboral::query()->with([
            'empleado:id,nombre,apellido,codigo_biometrico,sucursal,email',
        ]);

        if (filled($this->search)) {
            $term = '%' . mb_strtolower($this->search) . '%';
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

        // Ordenar por lo más reciente por defecto
        $incidencias = $query->latest('id')->paginate(10);

        $empleados = Empleado::query()
            ->select(['id', 'nombre', 'apellido', 'codigo_biometrico', 'sucursal'])
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
            'alcancesCumpleanos' => $this->alcancesCumpleanosDisponibles(),
            'tiposPermiso' => $this->tiposPermisoDisponibles(),
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

    public function alcancesDisponibles(): array
    {
        return [
            'dias' => 'Por días (Jornada completa / Licencias)',
            'horas' => 'Por horas (Entrada/Salida personalizada)',
        ];
    }

    public function alcancesCumpleanosDisponibles(): array
    {
        return [
            'manana' => 'Salida en la mañana (Medio día)',
            'tarde' => 'Salida en la tarde (Medio día)',
        ];
    }

    private function tiposPermisoDisponibles(): array
    {
        return [
            'salud' => 'Permiso por salud',
            'consulta_medica' => 'Consulta medica',
            'tramite_personal' => 'Tramite personal',
            'comision_laboral' => 'Comision laboral',
            'estudio' => 'Permiso por estudio',
            'asunto_familiar' => 'Asunto familiar',
        ];
    }

    private function sincronizarReglaTipo(string $tipo, bool $editing): void
    {
        if ($tipo !== 'permiso') {
            if ($editing) {
                $this->editTipoPermiso = '';
            } else {
                $this->tipoPermiso = '';
            }
        }

        if ($tipo !== 'cumpleanos') {
            return;
        }

        if ($editing) {
            $this->editAlcance = 'tarde';
            $this->editFechaFin = $this->editFechaInicio ?: $this->editFechaFin;
            $this->editHoraInicio = '';
            $this->editHoraFin = '';

            return;
        }

        $this->alcance = 'tarde';
        $this->fechaFin = $this->fechaInicio ?: $this->fechaFin;
        $this->horaInicio = '';
        $this->horaFin = '';
    }

    private function sincronizarReglaAlcance(string $alcance, bool $editing): void
    {
        $empleadoId = $editing ? $this->editEmpleadoId : $this->empleadoId;
        $fecha = $editing ? $this->editFechaInicio : $this->fechaInicio;

        if (blank($empleadoId) || blank($fecha)) {
            if ($alcance !== 'horas') {
                if ($editing) {
                    $this->editHoraInicio = '';
                    $this->editHoraFin = '';
                } else {
                    $this->horaInicio = '';
                    $this->horaFin = '';
                }
            }

            return;
        }

        if ($alcance !== 'horas') {
            if ($editing) {
                $this->editHoraInicio = '';
                $this->editHoraFin = '';
            } else {
                $this->horaInicio = '';
                $this->horaFin = '';
            }
        }
    }

    public function updatedEmpleadoId(): void
    {
        $this->sincronizarReglaAlcance($this->alcance, false);
    }

    public function updatedEditEmpleadoId(): void
    {
        $this->sincronizarReglaAlcance($this->editAlcance, true);
    }

    public function updatedEmpleadoSearch(): void
    {
        $this->empleadoId = '';
    }

    public function updatedEditEmpleadoSearch(): void
    {
        $this->editEmpleadoId = '';
    }

    public function seleccionarEmpleado(int $empleadoId): void
    {
        $empleado = Empleado::query()->find($empleadoId);

        if (!$empleado) {
            return;
        }

        $this->empleadoId = (string) $empleado->id;
        $this->empleadoSearch = $this->etiquetaEmpleado($empleado);
        $this->sincronizarReglaAlcance($this->alcance, false);
    }

    public function seleccionarEmpleadoEdicion(int $empleadoId): void
    {
        $empleado = Empleado::query()->find($empleadoId);

        if (!$empleado) {
            return;
        }

        $this->editEmpleadoId = (string) $empleado->id;
        $this->editEmpleadoSearch = $this->etiquetaEmpleado($empleado);
        $this->sincronizarReglaAlcance($this->editAlcance, true);
    }

    public function updatedFechaInicio(string $value): void
    {
        $this->sincronizarReglaAlcance($this->alcance, false);
    }

    public function updatedEditFechaInicio(string $value): void
    {
        $this->sincronizarReglaAlcance($this->editAlcance, true);
    }

    private function normalizarHora(string $alcance, ?string $hora): ?string
    {
        if ($alcance !== 'horas' || blank($hora)) {
            return null;
        }

        return $hora . ':00';
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
        $this->alcance = 'dias';
        $this->estado = 'aprobado';
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();
        $this->horaInicio = '';
        $this->horaFin = '';
        $this->motivo = '';
        $this->tipoPermiso = '';
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
            'motivo_rechazo' => $incidencia->motivo_rechazo,
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
                    $empleado->nombre_completo . ' ' .
                    ($empleado->codigo_biometrico ?? '') . ' ' .
                    ($empleado->sucursal ?? '')
                );

                return str_contains($texto, $term);
            })
            ->values();
    }

    private function etiquetaEmpleado(Empleado $empleado): string
    {
        $codigo = trim((string) ($empleado->codigo_biometrico ?? ''));

        return $empleado->nombre_completo . ($codigo !== '' ? ' | ' . $codigo : '');
    }

    private function resolverTipoPermisoDesdeMotivo(string $motivo): string
    {
        foreach ($this->tiposPermisoDisponibles() as $key => $label) {
            if ($motivo === $label) {
                return $key;
            }
        }

        return '';
    }
}
