<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Services\AuditoriaService;
use App\Services\ProgramacionLaboralService;
use App\Services\ReglamentoSancionService;
use App\Support\SucursalNormalizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PersonalPage extends Component
{
    use WithPagination;

    #[Url(as: 'vista')]
    public string $vista = 'personal';
    public string $search = '';
    public string $sucursalFiltro = '';
    public string $toleranciaFiltro = '';
    public string $ordenMarcaciones = 'fecha_reciente';
    public int $registrosPage = 1;

    // Búsqueda explícita para la vista de Marcaciones (personal?vista=marcaciones)
    public bool $marcacionesSearchPerformed = false;
    public string $inputMarcacionesSearch = '';
    public string $inputMarcacionesTipoFecha = 'rango'; // 'rango', 'mes'
    public string $inputMarcacionesFechaInicio = '';
    public string $inputMarcacionesFechaFin = '';
    public string $inputMarcacionesMes = '';
    public string $appliedMarcacionesSearch = '';
    public string $appliedMarcacionesTipoFecha = 'rango';
    public string $appliedMarcacionesFechaInicio = '';
    public string $appliedMarcacionesFechaFin = '';
    public string $appliedMarcacionesMes = '';
    public string $filterEstadoMarcaciones = 'todos'; // 'todos', 'completo', 'faltante'
    public ?int $selectedMarcacionesEmpleadoId = null;
    public array $matchingEmpleados = [];
    public ?array $marcacionesEmpleadoInfo = null;
    public array $marcacionesStats = [];
    public bool $showModalAtrasos = false;
    public bool $showModalOmisiones = false;
    public bool $showModalFaltas = false;
    public bool $showModalGlobal = false;
    public bool $showModalSancionados2Dias = false;
    public string $filtroModalSancion = 'acumulativo_2'; // 'acumulativo_2', 'todos'

    // Búsqueda explícita para la vista de Control / Marcaciones por Sucursal (personal?vista=control)
    public bool $controlSearchPerformed = false;
    public string $inputControlSearch = '';
    public string $inputControlSucursal = '';
    public string $inputControlMesNumero = '';
    public string $inputControlAnio = '';
    public string $appliedControlSearch = '';
    public string $appliedControlSucursal = '';
    public string $appliedControlMesNumero = '';
    public string $appliedControlAnio = '';
    public string $ordenControl = 'nombre_asc'; // nombre_asc, nombre_desc, horas_desc, horas_asc, retraso_desc, retraso_asc, excedido_primero

    // Búsqueda y filtros para la vista Registro mensual de sucursales (personal?vista=sucursales)
    public string $sucursalesPeriodoTipo = 'mes'; // 'mes', 'dia'
    public string $sucursalesFechaDia = '';
    public string $sucursalesMes = '';
    public string $sucursalesSucursal = '';
    public string $sucursalesSearch = '';
    public string $sucursalesEstadoFiltro = 'todos'; // 'todos', 'puntual', 'retraso', 'incompleto'
    public string $sucursalesOrden = 'fecha_desc'; // 'fecha_desc', 'fecha_asc', 'retraso_desc', 'nombre_asc'
    public int $sucursalesPerPage = 15;
    public string $appliedSucursalesPeriodoTipo = 'mes';
    public string $appliedSucursalesFechaDia = '';
    public string $appliedSucursalesMes = '';
    public string $appliedSucursalesSucursal = '';
    public string $appliedSucursalesSearch = '';
    public string $appliedSucursalesEstadoFiltro = 'todos';
    public string $appliedSucursalesOrden = 'fecha_desc';

    public ?int $detailEmpleadoId = null;
    public ?int $editingEmpleadoId = null;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDetailModal = false;
    public bool $showPdfModal = false;
    public bool $showDeleteModal = false;
    public bool $showDeleteRegistroModal = false;
    public ?int $pendingDeleteEmpleadoId = null;
    public ?int $pendingDeleteRegistroId = null;
    public string $pendingDeleteEmpleadoNombre = '';
    public string $pendingDeleteRegistroLabel = '';
    public string $nombre = '';
    public string $apellido = '';
    public string $codigoBiometrico = '';
    public string $email = '';
    public string $area = '';
    public string $sucursal = '';
    public string $fechaNacimiento = '';
    public string $fechaContratacion = '';
    public string $fechaDespido = '';
    public string $editNombre = '';
    public string $editApellido = '';
    public string $editCodigoBiometrico = '';
    public string $editEmail = '';
    public string $editArea = '';
    public string $editSucursal = '';
    public string $editFechaNacimiento = '';
    public string $editFechaContratacion = '';
    public string $editFechaDespido = '';
    public string $detailReferenceMonth = '';
    public string $pdfReferenceMonth = '';
    public string $detailMarkingFilter = 'todas';
    public array $detailEmpleado = [];
    public array $detailMonthOptions = [];
    public ?int $pdfEmpleadoId = null;
    public array $pdfMonthOptions = [];
    public array $pdfEmpleado = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);

        if (!in_array($this->vista, ['personal', 'inactivos', 'marcaciones', 'control', 'sucursales'], true)) {
            $this->vista = 'personal';
        }

        $refMonth = $this->referenceMonth();
        $this->detailReferenceMonth = $refMonth->format('Y-m');
        $this->pdfReferenceMonth = $refMonth->format('Y-m');
        $this->inputControlMesNumero = (string) $refMonth->month;
        $this->inputControlAnio = (string) $refMonth->year;
        $this->appliedControlMesNumero = (string) $refMonth->month;
        $this->appliedControlAnio = (string) $refMonth->year;

        $this->sucursalesFechaDia = now()->toDateString();
        $this->sucursalesMes = $refMonth->format('Y-m');
        $this->appliedSucursalesFechaDia = now()->toDateString();
        $this->appliedSucursalesMes = $refMonth->format('Y-m');
    }

    public function setVista(string $vista): void
    {
        if (!in_array($vista, ['personal', 'inactivos', 'marcaciones', 'control', 'sucursales'], true)) {
            return;
        }

        $this->vista = $vista;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function saveEmpleado(): void
    {
        $data = $this->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'codigoBiometrico' => ['nullable', 'string', 'max:50', 'unique:empleados,codigo_biometrico'],
            'email' => ['nullable', 'email', 'max:191', 'unique:empleados,email'],
            'area' => ['nullable', 'string', 'max:120'],
            'sucursal' => ['required', 'string', 'max:120'],
            'fechaNacimiento' => ['nullable', 'date'],
        ], [
            'nombre.required' => 'Ingresa el nombre del personal.',
            'apellido.required' => 'Ingresa el apellido del personal.',
            'sucursal.required' => 'Ingresa la sucursal.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'email.unique' => 'Ese correo ya esta asignado a otro personal.',
        ]);

        $horarioRegional = $this->programacionLaboral()->obtenerHorarioRegional($data['sucursal']);

        $empleado = Empleado::query()->create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'codigo_biometrico' => $data['codigoBiometrico'] ?: null,
            'email' => filled($data['email'] ?? null) ? strtolower(trim($data['email'])) : null,
            'area' => trim((string) ($data['area'] ?? '')),
            'sucursal' => $data['sucursal'],
            'fecha_nacimiento' => $data['fechaNacimiento'] ?: null,
            'hora_entrada_programada' => $horarioRegional?->hora_entrada ?: config('asistencia.hora_entrada'),
            'hora_salida_programada' => $horarioRegional?->hora_salida ?: config('asistencia.hora_salida'),
            'fecha_contratacion' => now()->toDateString(),
            'fecha_despido' => null,
            'created_by' => auth()->id(),
        ]);

        app(AuditoriaService::class)->registrar(
            'Personal',
            'crear',
            'Se registro un nuevo integrante del personal.',
            $empleado,
            null,
            $this->snapshotEmpleado($empleado)
        );

        $this->reset(['nombre', 'apellido', 'codigoBiometrico', 'email', 'area', 'sucursal', 'fechaNacimiento', 'fechaDespido']);
        $this->resetPage();
        $this->showCreateModal = false;

        session()->flash('status', 'Personal registrado correctamente.');
    }

    public function openEditModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->editingEmpleadoId = $empleado->id;
        $this->editNombre = $empleado->nombre;
        $this->editApellido = $empleado->apellido;
        $this->editCodigoBiometrico = $empleado->codigo_biometrico ?: '';
        $this->editEmail = $empleado->email ?: '';
        $this->editArea = $empleado->area;
        $this->editSucursal = $empleado->sucursal;
        $this->editFechaNacimiento = $empleado->fecha_nacimiento?->toDateString() ?? '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingEmpleadoId = null;
        $this->resetValidation();
        $this->reset(['editNombre', 'editApellido', 'editCodigoBiometrico', 'editEmail', 'editArea', 'editSucursal', 'editFechaNacimiento']);
    }

    public function openDetailModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->detailEmpleadoId = $empleado->id;
        $this->detailMonthOptions = $this->monthOptionsForEmpleado($empleado);
        $this->detailReferenceMonth = $this->resolveInitialDetailReferenceMonth($empleado);
        $this->detailMarkingFilter = 'todas';
        $this->detailEmpleado = $this->armarFichaEmpleado($empleadoId, $this->detailMonthCarbon());

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailEmpleadoId = null;
        $this->detailReferenceMonth = $this->referenceMonth()->format('Y-m');
        $this->detailMarkingFilter = 'todas';
        $this->detailEmpleado = [];
        $this->detailMonthOptions = [];
    }

    public function openPdfModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->pdfEmpleadoId = $empleado->id;
        $this->pdfMonthOptions = $this->monthOptionsForEmpleado($empleado);
        $this->pdfReferenceMonth = $this->resolveInitialMonthReference($empleado, $this->pdfMonthOptions);
        $this->pdfEmpleado = $this->armarFichaEmpleado($empleadoId, $this->pdfMonthCarbon());
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfEmpleadoId = null;
        $this->pdfReferenceMonth = $this->referenceMonth()->format('Y-m');
        $this->pdfMonthOptions = [];
        $this->pdfEmpleado = [];
    }

    public function openDeleteModal(int $empleadoId): void
    {
        $empleado = Empleado::query()->findOrFail($empleadoId);

        $this->pendingDeleteEmpleadoId = $empleado->id;
        $this->pendingDeleteEmpleadoNombre = $empleado->nombre_completo;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteEmpleadoId = null;
        $this->pendingDeleteEmpleadoNombre = '';
    }

    public function openDeleteRegistroModal(int $registroId): void
    {
        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($registroId);

        $this->pendingDeleteRegistroId = $registro->id;
        $this->pendingDeleteRegistroLabel = sprintf(
            '%s - %s',
            $registro->empleado?->nombre_completo ?? 'Sin empleado',
            $registro->fecha?->format('d/m/Y') ?? 'Sin fecha'
        );
        $this->showDeleteRegistroModal = true;
    }

    public function closeDeleteRegistroModal(): void
    {
        $this->showDeleteRegistroModal = false;
        $this->pendingDeleteRegistroId = null;
        $this->pendingDeleteRegistroLabel = '';
    }

    public function updatedDetailReferenceMonth(): void
    {
        $this->refreshDetailEmpleado();
    }

    public function updatedDetailMarkingFilter(): void
    {
        $this->refreshDetailEmpleado();
    }

    public function updatedPdfReferenceMonth(): void
    {
        $this->refreshPdfEmpleado();
    }

    public function setDetailMarkingFilter(string $filter): void
    {
        if (!in_array($filter, ['entrada', 'salida'], true)) {
            return;
        }

        $this->detailMarkingFilter = $this->detailMarkingFilter === $filter ? 'todas' : $filter;
        $this->refreshDetailEmpleado();
    }

    private function refreshDetailEmpleado(): void
    {
        if (!$this->showDetailModal || !$this->detailEmpleadoId) {
            return;
        }

        $this->detailEmpleado = $this->armarFichaEmpleado($this->detailEmpleadoId, $this->detailMonthCarbon());
    }

    private function refreshPdfEmpleado(): void
    {
        if (!$this->showPdfModal || !$this->pdfEmpleadoId) {
            return;
        }

        $this->pdfEmpleado = $this->armarFichaEmpleado($this->pdfEmpleadoId, $this->pdfMonthCarbon());
    }

    public function descargarPdfEmpleado(): void
    {
        if (blank($this->pdfEmpleado)) {
            return;
        }

        $this->dispatch('print-empleado-pdf');
    }

    public function updateEmpleado(): void
    {
        $this->validate([
            'editNombre' => ['required', 'string', 'max:120'],
            'editApellido' => ['required', 'string', 'max:120'],
            'editCodigoBiometrico' => ['nullable', 'string', 'max:50', Rule::unique('empleados', 'codigo_biometrico')->ignore($this->editingEmpleadoId)],
            'editEmail' => ['nullable', 'email', 'max:191', Rule::unique('empleados', 'email')->ignore($this->editingEmpleadoId)],
            'editArea' => ['nullable', 'string', 'max:120'],
            'editSucursal' => ['required', 'string', 'max:120'],
            'editFechaNacimiento' => ['nullable', 'date'],
        ], [
            'editNombre.required' => 'Ingresa el nombre del personal.',
            'editApellido.required' => 'Ingresa el apellido del personal.',
            'editSucursal.required' => 'Ingresa la sucursal.',
            'editEmail.email' => 'Ingresa un correo electronico valido.',
            'editEmail.unique' => 'Ese correo ya esta asignado a otro personal.',
        ]);

        $empleado = Empleado::query()->findOrFail($this->editingEmpleadoId);
        $antes = $this->snapshotEmpleado($empleado);

        $empleado->update([
            'nombre' => $this->editNombre,
            'apellido' => $this->editApellido,
            'codigo_biometrico' => $this->editCodigoBiometrico ?: null,
            'email' => filled($this->editEmail) ? strtolower(trim($this->editEmail)) : null,
            'area' => trim($this->editArea),
            'sucursal' => $this->editSucursal,
            'fecha_nacimiento' => $this->editFechaNacimiento ?: null,
            'created_by' => $empleado->created_by,
            'deleted_by' => $empleado->deleted_by,
        ]);

        app(AuditoriaService::class)->registrar(
            'Personal',
            'editar',
            'Se actualizaron los datos de un integrante del personal.',
            $empleado->fresh(),
            $antes,
            $this->snapshotEmpleado($empleado->fresh())
        );

        $this->showEditModal = false;
        $this->editingEmpleadoId = null;
        $this->resetValidation();
        $this->resetPage();

        session()->flash('status', 'Personal actualizado correctamente.');
    }

    public function deleteEmpleado(): void
    {
        if (!$this->pendingDeleteEmpleadoId) {
            return;
        }

        $empleado = Empleado::query()->findOrFail($this->pendingDeleteEmpleadoId);
        $antes = $this->snapshotEmpleado($empleado);
        $empleado->deleted_by = auth()->id();
        $empleado->save();
        $empleado->delete();

        app(AuditoriaService::class)->registrar(
            'Personal',
            'eliminar',
            'Se elimino un integrante del personal.',
            $empleado,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteModal();
        $this->resetPage();
        session()->flash('status', 'Personal marcado como eliminado.');
    }

    public function deleteRegistroAsistencia(): void
    {
        if (!$this->pendingDeleteRegistroId) {
            return;
        }

        $registro = RegistroAsistencia::query()->with('empleado')->findOrFail($this->pendingDeleteRegistroId);
        $antes = $this->snapshotRegistro($registro);
        $registro->delete();

        app(AuditoriaService::class)->registrar(
            'Marcaciones',
            'eliminar',
            'Se elimino una marcacion del historial reciente.',
            $registro,
            $antes,
            ['eliminado' => true]
        );

        $this->closeDeleteRegistroModal();
        $this->resetPage('registrosPage');
        session()->flash('status', 'Marcacion eliminada correctamente.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetPage('registrosPage');
    }

    public function updatingSucursalFiltro(): void
    {
        $this->resetPage();
        $this->resetPage('registrosPage');
    }

    public function updatingToleranciaFiltro(): void
    {
        $this->resetPage();
        $this->resetPage('registrosPage');
    }

    public function updatingOrdenMarcaciones(): void
    {
        $this->resetPage('registrosPage');
    }

    public function aplicarBusquedaControl(): void
    {
        $this->appliedControlSearch = trim($this->inputControlSearch);
        $this->appliedControlSucursal = trim($this->inputControlSucursal);
        $this->appliedControlMesNumero = trim($this->inputControlMesNumero) ?: (string) now()->month;
        $this->appliedControlAnio = trim($this->inputControlAnio) ?: (string) now()->year;
        $this->controlSearchPerformed = true;
        $this->resetPage();
    }

    public function seleccionarSucursal(string $sucursal): void
    {
        $this->inputControlSucursal = $sucursal;
        $this->appliedControlSucursal = $sucursal;
        $this->appliedControlSearch = trim($this->inputControlSearch);
        $this->appliedControlMesNumero = trim($this->inputControlMesNumero) ?: (string) now()->month;
        $this->appliedControlAnio = trim($this->inputControlAnio) ?: (string) now()->year;
        $this->controlSearchPerformed = true;
        $this->resetPage();
    }

    public function limpiarFiltrosControl(): void
    {
        $refMonth = $this->referenceMonth();
        $this->inputControlSearch = '';
        $this->inputControlSucursal = '';
        $this->inputControlMesNumero = (string) $refMonth->month;
        $this->inputControlAnio = (string) $refMonth->year;
        $this->appliedControlSearch = '';
        $this->appliedControlSucursal = '';
        $this->appliedControlMesNumero = (string) $refMonth->month;
        $this->appliedControlAnio = (string) $refMonth->year;
        $this->controlSearchPerformed = false;
        $this->ordenControl = 'nombre_asc';
        $this->resetPage();
    }

    public function sortByControl(string $column): void
    {
        $this->ordenControl = match ($column) {
            'nombre' => ($this->ordenControl === 'nombre_asc' ? 'nombre_desc' : 'nombre_asc'),
            'horas' => ($this->ordenControl === 'horas_desc' ? 'horas_asc' : 'horas_desc'),
            'retraso' => ($this->ordenControl === 'retraso_desc' ? 'retraso_asc' : 'retraso_desc'),
            'excedido' => 'excedido_primero',
            default => $column,
        };
        $this->resetPage();
    }

    public function aplicarBusquedaMarcaciones(): void
    {
        $this->appliedMarcacionesSearch = trim($this->inputMarcacionesSearch);
        $this->appliedMarcacionesTipoFecha = $this->inputMarcacionesTipoFecha;
        $this->appliedMarcacionesFechaInicio = trim($this->inputMarcacionesFechaInicio);
        $this->appliedMarcacionesFechaFin = trim($this->inputMarcacionesFechaFin);
        $this->appliedMarcacionesMes = trim($this->inputMarcacionesMes);
        $this->marcacionesSearchPerformed = true;

        if (filled($this->appliedMarcacionesSearch)) {
            $term = "%{$this->appliedMarcacionesSearch}%";
            $searchOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $matchingCollection = Empleado::query()
                ->withUltimaMarcacion()
                ->withTrashed()
                ->where(function ($q) use ($searchOperator, $term) {
                    $q->where('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhereRaw("nombre || ' ' || apellido " . ($searchOperator === 'ilike' ? 'ILIKE' : 'LIKE') . " ?", [$term]);
                })
                ->get();

            $this->matchingEmpleados = $matchingCollection->map(function (Empleado $emp) {
                $estado = $emp->estadoLaboral(now());

                return [
                    'id' => $emp->id,
                    'nombre_completo' => $emp->nombre_completo,
                    'codigo' => $emp->codigo_biometrico ?: 'Sin asignar',
                    'sucursal' => $emp->sucursal ?: 'Sin sucursal',
                    'area' => $emp->area ?: 'Sin área',
                    'estado_laboral' => $estado,
                    'es_activo' => ($estado === 'Activo'),
                ];
            })->sortByDesc(fn ($item) => $item['es_activo'] ? 1 : 0)->values()->all();

            $empleado = null;
            if ($this->selectedMarcacionesEmpleadoId) {
                $empleado = $matchingCollection->firstWhere('id', $this->selectedMarcacionesEmpleadoId);
            }

            if (!$empleado) {
                $exactCodeMatch = $matchingCollection->first(fn ($e) => strcasecmp((string)$e->codigo_biometrico, $this->appliedMarcacionesSearch) === 0);
                if ($exactCodeMatch) {
                    $empleado = $exactCodeMatch;
                } else {
                    $empleado = $matchingCollection->first(fn ($e) => $e->estadoLaboral(now()) === 'Activo')
                        ?? $matchingCollection->first();
                }
            }

            if ($empleado) {
                $this->selectedMarcacionesEmpleadoId = $empleado->id;
                $estadoLaboral = $empleado->estadoLaboral(now());

                $this->marcacionesEmpleadoInfo = [
                    'id' => $empleado->id,
                    'nombre_completo' => $empleado->nombre_completo,
                    'codigo' => $empleado->codigo_biometrico ?: 'Sin asignar',
                    'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
                    'area' => $empleado->area ?: 'Sin área',
                    'estado_laboral' => $estadoLaboral,
                ];

                // Determinar rango para estadísticas
                if ($this->appliedMarcacionesTipoFecha === 'mes' && filled($this->appliedMarcacionesMes)) {
                    try {
                        $cMes = Carbon::parse($this->appliedMarcacionesMes . '-01');
                        $statStart = $cMes->copy()->startOfMonth();
                        $statEnd = $cMes->copy()->endOfMonth();
                    } catch (\Exception $e) {
                        $statStart = now()->startOfMonth();
                        $statEnd = now()->endOfMonth();
                    }
                } elseif (filled($this->appliedMarcacionesFechaInicio) && filled($this->appliedMarcacionesFechaFin)) {
                    $statStart = Carbon::parse(min($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin));
                    $statEnd = Carbon::parse(max($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin));
                } elseif (filled($this->appliedMarcacionesFechaInicio)) {
                    $statStart = Carbon::parse($this->appliedMarcacionesFechaInicio);
                    $statEnd = now();
                } elseif (filled($this->appliedMarcacionesFechaFin)) {
                    $statStart = Carbon::parse($this->appliedMarcacionesFechaFin)->startOfMonth();
                    $statEnd = Carbon::parse($this->appliedMarcacionesFechaFin);
                } else {
                    $statStart = now()->startOfMonth();
                    $statEnd = now()->endOfMonth();
                }

                $this->marcacionesStats = $this->calcularEstadisticasMarcacionesEmpleado($empleado->id, $statStart, $statEnd);
            } else {
                $this->selectedMarcacionesEmpleadoId = null;
                $this->marcacionesEmpleadoInfo = null;
                $this->marcacionesStats = [];
            }
        } else {
            $this->matchingEmpleados = [];
            $this->selectedMarcacionesEmpleadoId = null;
            $this->marcacionesEmpleadoInfo = null;
            $this->marcacionesStats = [];
        }

        $this->resetPage('registrosPage');
    }

    public function seleccionarEmpleadoMarcaciones(?int $empleadoId): void
    {
        $this->selectedMarcacionesEmpleadoId = $empleadoId;
        if ($empleadoId === null) {
            $this->marcacionesEmpleadoInfo = null;
            $this->marcacionesStats = [];
            $this->resetPage('registrosPage');

            return;
        }

        $this->aplicarBusquedaMarcaciones();
    }

    public function limpiarFiltrosMarcaciones(): void
    {
        $this->inputMarcacionesSearch = '';
        $this->inputMarcacionesTipoFecha = 'rango';
        $this->inputMarcacionesFechaInicio = '';
        $this->inputMarcacionesFechaFin = '';
        $this->inputMarcacionesMes = '';
        $this->appliedMarcacionesSearch = '';
        $this->appliedMarcacionesTipoFecha = 'rango';
        $this->appliedMarcacionesFechaInicio = '';
        $this->appliedMarcacionesFechaFin = '';
        $this->appliedMarcacionesMes = '';
        $this->selectedMarcacionesEmpleadoId = null;
        $this->matchingEmpleados = [];
        $this->marcacionesEmpleadoInfo = null;
        $this->marcacionesStats = [];
        $this->showModalAtrasos = false;
        $this->showModalOmisiones = false;
        $this->showModalFaltas = false;
        $this->showModalGlobal = false;
        $this->marcacionesSearchPerformed = false;
        $this->ordenMarcaciones = 'fecha_reciente';
        $this->filterEstadoMarcaciones = 'todos';
        $this->resetPage('registrosPage');
    }

    public function openModalAtrasos(): void
    {
        $this->showModalAtrasos = true;
    }

    public function closeModalAtrasos(): void
    {
        $this->showModalAtrasos = false;
    }

    public function openModalOmisiones(): void
    {
        $this->showModalOmisiones = true;
    }

    public function closeModalOmisiones(): void
    {
        $this->showModalOmisiones = false;
    }

    public function openModalFaltas(): void
    {
        $this->showModalFaltas = true;
    }

    public function closeModalFaltas(): void
    {
        $this->showModalFaltas = false;
    }

    public function openModalGlobal(): void
    {
        $this->showModalGlobal = true;
    }

    public function closeModalGlobal(): void
    {
        $this->showModalGlobal = false;
    }

    public function openModalSancionados2Dias(string $filtro = 'acumulativo_2'): void
    {
        $this->filtroModalSancion = $filtro;
        $this->showModalSancionados2Dias = true;
    }

    public function closeModalSancionados2Dias(): void
    {
        $this->showModalSancionados2Dias = false;
    }

    public function setFiltroModalSancion(string $filtro): void
    {
        $this->filtroModalSancion = $filtro;
    }

    public function obtenerColeccionSancionadosModal(): array
    {
        $targetMonth = Carbon::create(
            (int) ($this->inputControlAnio ?: now()->year),
            (int) ($this->inputControlMesNumero ?: now()->month),
            1
        );

        $controlQuery = Empleado::query()
            ->with('asistencias')
            ->when(filled($this->appliedControlSucursal) && $this->appliedControlSucursal !== 'todas', function ($q) {
                SucursalNormalizer::applyFilter($q, 'sucursal', $this->appliedControlSucursal);
            })
            ->where('fecha_ingreso', '<=', $targetMonth->copy()->endOfMonth()->toDateString())
            ->get()
            ->filter(fn (Empleado $e) => $e->fecha_despido === null || $e->fecha_despido > now()->toDateString())
            ->filter(fn (Empleado $e) => $e->estaActivoLaboralmente(now()))
            ->values();

        $registrosPorNombre = $this->registrosPorNombre(
            $targetMonth->copy()->startOfMonth()->toDateString(),
            $targetMonth->copy()->endOfMonth()->toDateString()
        );

        $empleados = $controlQuery->map(function (Empleado $empleado) use ($registrosPorNombre, $targetMonth) {
            return $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $targetMonth);
        });

        $totalAcumulativo2 = $empleados->filter(fn (Empleado $e) => ($e->resumen_asistencia['sancion_regla']['es_acumulativo_2'] ?? false))->count();
        $totalGeneral = $empleados->filter(fn (Empleado $e) => (($e->resumen_asistencia['sancion_regla']['dias_sancion'] ?? 0) > 0 || ($e->resumen_asistencia['sancion_regla']['es_destitucion'] ?? false)))->count();

        $filtrados = $empleados->filter(function (Empleado $e) {
            $sancion = $e->resumen_asistencia['sancion_regla'] ?? null;
            if (!$sancion) {
                return false;
            }
            if ($this->filtroModalSancion === 'acumulativo_2') {
                return $sancion['es_acumulativo_2'] ?? false;
            }
            return (($sancion['dias_sancion'] ?? 0) > 0 || ($sancion['es_destitucion'] ?? false));
        })->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values();

        return [
            'empleados' => $filtrados,
            'total_acumulativo_2' => $totalAcumulativo2,
            'total_general' => $totalGeneral,
        ];
    }

    public function sortByMarcaciones(string $column): void
    {
        $this->ordenMarcaciones = match ($column) {
            'nombre' => ($this->ordenMarcaciones === 'nombre_asc' ? 'nombre_desc' : 'nombre_asc'),
            'sucursal' => ($this->ordenMarcaciones === 'sucursal_asc' ? 'sucursal_desc' : 'sucursal_asc'),
            'fecha' => ($this->ordenMarcaciones === 'fecha_reciente' ? 'fecha_antigua' : 'fecha_reciente'),
            'entrada' => ($this->ordenMarcaciones === 'hora_asc' ? 'hora_desc' : 'hora_asc'),
            'salida' => ($this->ordenMarcaciones === 'salida_asc' ? 'salida_desc' : 'salida_asc'),
            default => $column,
        };
        $this->resetPage('registrosPage');
    }

    public function setFilterEstadoMarcaciones(string $estado): void
    {
        $this->filterEstadoMarcaciones = $estado;
        $this->resetPage('registrosPage');
    }

    private function obtenerColeccionMarcacionesReporte(): array
    {
        $searchOperator = $this->caseInsensitiveLikeOperator();

        $registrosQuery = RegistroAsistencia::query()
            ->with('empleado')
            ->whereHas('empleado')
            ->where($this->excludeSaturdayRecords());

        $periodoLabel = 'Todas las fechas';

        if ($this->appliedMarcacionesTipoFecha === 'mes' && filled($this->appliedMarcacionesMes)) {
            try {
                $carbonMes = Carbon::parse($this->appliedMarcacionesMes . '-01');
                $registrosQuery->whereBetween('fecha', [
                    $carbonMes->copy()->startOfMonth()->toDateString(),
                    $carbonMes->copy()->endOfMonth()->toDateString(),
                ]);
                $periodoLabel = ucfirst($carbonMes->locale('es')->translatedFormat('F Y'));
            } catch (\Exception $e) {
                // ignorar
            }
        } elseif (filled($this->appliedMarcacionesFechaInicio) && filled($this->appliedMarcacionesFechaFin)) {
            $fInicio = min($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin);
            $fFin = max($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin);
            $registrosQuery->whereBetween('fecha', [$fInicio, $fFin]);
            $periodoLabel = Carbon::parse($fInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fFin)->format('d/m/Y');
        } elseif (filled($this->appliedMarcacionesFechaInicio)) {
            $registrosQuery->whereDate('fecha', '>=', $this->appliedMarcacionesFechaInicio);
            $periodoLabel = 'Desde ' . Carbon::parse($this->appliedMarcacionesFechaInicio)->format('d/m/Y');
        } elseif (filled($this->appliedMarcacionesFechaFin)) {
            $registrosQuery->whereDate('fecha', '<=', $this->appliedMarcacionesFechaFin);
            $periodoLabel = 'Hasta ' . Carbon::parse($this->appliedMarcacionesFechaFin)->format('d/m/Y');
        }

        if ($this->selectedMarcacionesEmpleadoId) {
            $registrosQuery->where('empleado_id', $this->selectedMarcacionesEmpleadoId);
        } elseif (filled($this->appliedMarcacionesSearch)) {
            $term = "%{$this->appliedMarcacionesSearch}%";
            $registrosQuery->whereHas('empleado', function ($empleadoQuery) use ($searchOperator, $term) {
                $empleadoQuery->where(function ($nestedQuery) use ($searchOperator, $term) {
                    $nestedQuery->where('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhereRaw("nombre || ' ' || apellido " . ($searchOperator === 'ilike' ? 'ILIKE' : 'LIKE') . " ?", [$term]);
                });
            });
        }

        if ($this->filterEstadoMarcaciones === 'completo') {
            $registrosQuery->whereNotNull('hora_entrada')->where('hora_entrada', '!=', '')
                ->whereNotNull('hora_salida')->where('hora_salida', '!=', '');
        } elseif ($this->filterEstadoMarcaciones === 'faltante') {
            $registrosQuery->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('hora_entrada')->where('hora_entrada', '!=', '')
                        ->where(function ($sub2) {
                            $sub2->whereNull('hora_salida')->orWhere('hora_salida', '');
                        });
                })->orWhere(function ($sub) {
                    $sub->where(function ($sub2) {
                        $sub2->whereNull('hora_entrada')->orWhere('hora_entrada', '');
                    })->whereNotNull('hora_salida')->where('hora_salida', '!=', '');
                });
            });
        }

        $allRegistros = $registrosQuery
            ->tap(fn($query) => $this->aplicarOrdenMarcaciones($query))
            ->get();

        $progService = $this->programacionLaboral();

        $rows = $allRegistros->map(function (RegistroAsistencia $registro) use ($progService) {
            $marcacion = $this->normalizarMarcacionAsistencia($registro);
            $entradaVal = filled($marcacion['entrada']) ? substr($marcacion['entrada'], 0, 5) : null;
            $salidaVal = filled($marcacion['salida']) ? substr($marcacion['salida'], 0, 5) : null;
            $tieneEntrada = filled($entradaVal) && $entradaVal !== '--:--';
            $tieneSalida = filled($salidaVal) && $salidaVal !== '--:--';

            $empleado = $registro->empleado;
            $horario = $empleado && $registro->fecha ? $progService->resolverHorario($empleado, $registro->fecha) : null;
            $horaEntradaProg = $horario['hora_entrada'] ?? config('asistencia.hora_entrada', '08:30:00');
            $horaLimite = $horario['hora_entrada_tolerancia'] ?? $horaEntradaProg;

            $minutosRetraso = 0;
            if ($tieneEntrada && $horaLimite) {
                $minutosRetraso = $this->calcularMinutosRetraso($entradaVal, $horaLimite);
            }

            $minutosTrabajados = 0;
            $horasTrabajadas = '--:--';
            if ($tieneEntrada && $tieneSalida) {
                $minutosTrabajados = $this->calcularMinutosTrabajados($entradaVal, $salidaVal);
                $horasTrabajadas = sprintf('%dh %02dm', intdiv($minutosTrabajados, 60), $minutosTrabajados % 60);
            }

            if ($tieneEntrada && $tieneSalida) {
                $estado = 'Completo';
                $tipoEstado = 'completo';
            } elseif ($tieneEntrada || $tieneSalida) {
                $estado = 'Sin completar';
                $tipoEstado = 'faltante';
            } else {
                $estado = 'Sin marcación';
                $tipoEstado = 'sin_marcacion';
            }

            $codigoBio = $registro->empleado?->codigo_biometrico ?: (string) $registro->empleado?->id;

            return (object) [
                'id' => $registro->id,
                'empleado' => $registro->empleado,
                'codigo' => $codigoBio,
                'fecha' => $registro->fecha,
                'fecha_formateada' => $registro->fecha?->format('d/m/Y'),
                'dia' => $registro->fecha?->locale('es')->isoFormat('dddd'),
                'hora_entrada' => $entradaVal ?: '--:--',
                'hora_salida' => $salidaVal ?: '--:--',
                'horas_trabajadas' => $horasTrabajadas,
                'minutos_retraso' => $minutosRetraso,
                'retraso_formateado' => $tieneEntrada ? ($minutosRetraso > 0 ? "+{$minutosRetraso} min" : 'Puntual') : '--',
                'estado_marcacion' => $estado,
                'tipo_estado' => $tipoEstado,
                'observacion' => $registro->observacion,
            ];
        });

        return [
            'periodoLabel' => $periodoLabel,
            'rows' => $rows,
        ];
    }

    public function descargarPdfMarcaciones()
    {
        $reporteData = $this->obtenerColeccionMarcacionesReporte();
        $periodoLabel = $reporteData['periodoLabel'];
        $rows = $reporteData['rows'];

        $pdf = Pdf::loadView('pdf.marcaciones-personal', [
            'periodoLabel' => $periodoLabel,
            'empleadoInfo' => $this->marcacionesEmpleadoInfo,
            'stats' => $this->marcacionesStats,
            'registros' => $rows,
            'filterEstado' => $this->filterEstadoMarcaciones,
        ])->setPaper('a4', 'portrait');

        $fileName = 'Reporte_Marcaciones_' . ($this->marcacionesEmpleadoInfo ? Str::slug($this->marcacionesEmpleadoInfo['nombre_completo']) : 'General') . '_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }

    public function descargarExcelMarcaciones()
    {
        $reporteData = $this->obtenerColeccionMarcacionesReporte();
        $periodoLabel = $reporteData['periodoLabel'];
        $rows = $reporteData['rows'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Marcaciones');

        $currentRow = 1;

        // Título del reporte
        $sheet->setCellValue("A{$currentRow}", 'EMPRESA DE CORREOS DE BOLIVIA - REPORTE DE MARCACIONES DE ASISTENCIA');
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
        $currentRow++;

        // Metadata de emisión
        $sheet->setCellValue("A{$currentRow}", "Período: {$periodoLabel} | Emisión: " . now()->format('d/m/Y H:i') . " | Filtro Estado: " . ucfirst($this->filterEstadoMarcaciones));
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9.5)->getColor()->setRGB('475569');
        $currentRow++;

        // Si hay empleado filtrado
        $isSingleEmployee = (bool) $this->marcacionesEmpleadoInfo;

        if ($isSingleEmployee) {
            $info = $this->marcacionesEmpleadoInfo;
            $sheet->setCellValue("A{$currentRow}", "Personal: {$info['nombre_completo']} | Código Biométrico: {$info['codigo']} | Sucursal: {$info['sucursal']} | Área: " . ($info['area'] ?: 'General'));
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
            $currentRow++;

            if (!empty($this->marcacionesStats)) {
                $st = $this->marcacionesStats;
                $sheet->setCellValue(
                    "A{$currentRow}",
                    "Horas Acumuladas: " . ($st['horas_trabajadas_formateado'] ?? '0h 00m') . " (" . ($st['dias_con_marcacion'] ?? 0) . " días) | " .
                    "Retraso Total: " . ($st['retraso_acumulado_formateado'] ?? '0 min') . " (" . ($st['total_atrasos'] ?? 0) . " días tarde) | " .
                    "Omisiones: " . ($st['total_omisiones'] ?? 0) . " | Faltas: " . ($st['total_faltas'] ?? 0)
                );
                $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9)->getColor()->setRGB('334155');
                $currentRow++;
            }
        }

        $currentRow++; // Línea en blanco antes de los encabezados de tabla

        $headerRow = $currentRow;

        if ($isSingleEmployee) {
            $headers = ['Fecha', 'Día', 'Hora Entrada', 'Hora Salida', 'Horas Trabajadas', 'Retraso'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        } else {
            $headers = ['Personal', 'Código', 'Sucursal', 'Fecha', 'Día', 'Hora Entrada', 'Hora Salida', 'Horas Trabajadas', 'Retraso'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        }

        foreach ($headers as $index => $headerText) {
            $colLetter = $cols[$index];
            $sheet->setCellValue("{$colLetter}{$headerRow}", $headerText);
        }

        $lastCol = end($cols);
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        if (!$isSingleEmployee) {
            $sheet->getStyle("A{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $currentRow++;

        foreach ($rows as $row) {
            $c = 0;
            if (!$isSingleEmployee) {
                $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->empleado?->nombre_completo ?? 'Sin nombre');
                $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->codigo ?? $row->empleado?->codigo_biometrico ?? '');
                $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->empleado?->sucursal ?? '');
            }

            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->fecha_formateada ?? (\Carbon\Carbon::parse($row->fecha)->format('d/m/Y')));
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", ucfirst($row->dia ?? ''));
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->hora_entrada);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->hora_salida);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->horas_trabajadas);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->retraso_formateado);

            // Centrar los datos numéricos y de fechas
            if ($isSingleEmployee) {
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } else {
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $currentRow++;
        }

        // Bordes finos en toda la tabla
        $dataEndRow = max($headerRow, $currentRow - 1);
        $tableRange = "A{$headerRow}:{$lastCol}{$dataEndRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Auto-ajustar ancho de columnas
        foreach ($cols as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = 'Reporte_Marcaciones_' . ($this->marcacionesEmpleadoInfo ? Str::slug($this->marcacionesEmpleadoInfo['nombre_completo']) : 'General') . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function obtenerColeccionControlReporte(): array
    {
        $referenceMonth = $this->referenceMonth();
        $targetYear = (int) ($this->appliedControlAnio ?: $referenceMonth->year);
        $targetMonthNum = (int) ($this->appliedControlMesNumero ?: $referenceMonth->month);
        $targetMonth = Carbon::createFromDate($targetYear, $targetMonthNum, 1);
        $controlMonthStart = $targetMonth->copy()->startOfMonth()->toDateString();
        $controlMonthEnd = $targetMonth->copy()->endOfMonth()->toDateString();
        $searchOperator = $this->caseInsensitiveLikeOperator();

        $controlQuery = Empleado::query()
            ->withUltimaMarcacion()
            ->with(['asistencias' => function ($query) use ($controlMonthStart, $controlMonthEnd) {
                $query->whereBetween('fecha', [$controlMonthStart, $controlMonthEnd])->orderByDesc('fecha');
            }]);

        if (filled($this->appliedControlSearch)) {
            $term = "%{$this->appliedControlSearch}%";
            $controlQuery->where(function ($q) use ($searchOperator, $term) {
                $q->where('codigo_biometrico', $searchOperator, $term)
                    ->orWhere('nombre', $searchOperator, $term)
                    ->orWhere('apellido', $searchOperator, $term);
            });
        }

        if (filled($this->appliedControlSucursal) && $this->appliedControlSucursal !== 'todas') {
            SucursalNormalizer::applyFilter($controlQuery, 'sucursal', $this->appliedControlSucursal);
        }

        $registrosPorNombre = $this->registrosPorNombre($controlMonthStart, $controlMonthEnd);

        $listaControl = $controlQuery->get()
            ->filter(fn (Empleado $e) => ! $e->trashed())
            ->filter(fn (Empleado $e) => $e->fecha_despido === null || $e->fecha_despido > now()->toDateString())
            ->filter(fn (Empleado $e) => $e->estaActivoLaboralmente(now()))
            ->values();

        $listaControl->transform(function (Empleado $empleado) use ($registrosPorNombre, $targetMonth) {
            return $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $targetMonth);
        });

        // KPIs y porcentajes de la sucursal
        $sucursalKpis = $this->calcularKpisSucursal($listaControl);

        // Ordenar la colección según $ordenControl
        $listaControl = match ($this->ordenControl) {
            'nombre_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->nombre . ' ' . $e->apellido)->values(),
            'horas_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0)->values(),
            'horas_asc' => $listaControl->sortBy(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0)->values(),
            'retraso_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values(),
            'retraso_asc' => $listaControl->sortBy(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values(),
            'excedido_primero' => $listaControl->sortByDesc(fn (Empleado $e) => ($e->resumen_asistencia['estado_retraso'] ?? '') === 'Excedido' ? 1 : 0)->values(),
            default => $listaControl->sortBy(fn (Empleado $e) => $e->nombre . ' ' . $e->apellido)->values(), // nombre_asc
        };

        $mesLabel = ucfirst($targetMonth->locale('es')->translatedFormat('F Y'));
        $sucursalLabel = filled($this->appliedControlSucursal) && $this->appliedControlSucursal !== 'todas'
            ? $this->appliedControlSucursal
            : 'Todas las sucursales';

        return [
            'mesLabel' => $mesLabel,
            'sucursalLabel' => $sucursalLabel,
            'sucursalKpis' => $sucursalKpis,
            'empleados' => $listaControl,
        ];
    }

    public function descargarPdfControl()
    {
        $reporteData = $this->obtenerColeccionControlReporte();

        $pdf = Pdf::loadView('pdf.control-sucursal', [
            'mesLabel' => $reporteData['mesLabel'],
            'sucursalLabel' => $reporteData['sucursalLabel'],
            'sucursalKpis' => $reporteData['sucursalKpis'],
            'empleados' => $reporteData['empleados'],
        ])->setPaper('a4', 'portrait');

        $fileName = 'Reporte_Control_' . Str::slug($reporteData['sucursalLabel']) . '_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }

    public function descargarExcelControl()
    {
        $reporteData = $this->obtenerColeccionControlReporte();
        $mesLabel = $reporteData['mesLabel'];
        $sucursalLabel = $reporteData['sucursalLabel'];
        $sucursalKpis = $reporteData['sucursalKpis'];
        $empleados = $reporteData['empleados'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Control Sucursal');

        $currentRow = 1;

        // Título del reporte
        $sheet->setCellValue("A{$currentRow}", 'EMPRESA DE CORREOS DE BOLIVIA - CONTROL MENSUAL DE ASISTENCIA');
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
        $currentRow++;

        // Metadata de emisión
        $sheet->setCellValue("A{$currentRow}", "Mes: {$mesLabel} | Sucursal: {$sucursalLabel} | Emisión: " . now()->format('d/m/Y H:i') . " | Total Personal: " . count($empleados));
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9.5)->getColor()->setRGB('475569');
        $currentRow++;

        // Resumen KPIs si existen
        if (!empty($sucursalKpis)) {
            $sheet->setCellValue(
                "A{$currentRow}",
                "Puntualidad: " . ($sucursalKpis['porcentaje_sin_atrasos'] ?? 0) . "% (" . ($sucursalKpis['sin_atrasos'] ?? 0) . "/" . ($sucursalKpis['total_empleados'] ?? 0) . " puntuales) | " .
                "Cumplimiento: " . ($sucursalKpis['porcentaje_sin_omisiones'] ?? 0) . "% | " .
                "En Tolerancia: " . ($sucursalKpis['porcentaje_dentro_tolerancia'] ?? 0) . "% | " .
                "Horas Totales: " . ($sucursalKpis['total_horas_trabajadas'] ?? '0h 0m') . " (Promedio: " . ($sucursalKpis['promedio_horas_empleado'] ?? '0h 0m') . ")"
            );
            $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9)->getColor()->setRGB('334155');
            $currentRow++;
        }

        $currentRow++; // Línea en blanco antes de los encabezados de tabla

        $headerRow = $currentRow;
        $headers = ['Personal', 'Código', 'Sucursal', 'Horas Trabajadas', 'Retraso Mes', 'Días Tarde', 'Omisiones', 'Saldo Tolerancia', 'Estado'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headers as $index => $headerText) {
            $colLetter = $cols[$index];
            $sheet->setCellValue("{$colLetter}{$headerRow}", $headerText);
        }

        $lastCol = end($cols);
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $currentRow++;

        foreach ($empleados as $emp) {
            $res = $emp->resumen_asistencia ?? [];
            $retrasoMin = $res['retraso_mes'] ?? 0;
            $retrasoFormateado = $res['retraso_mes_formateado'] ?? ($retrasoMin . ' min');
            $diasTarde = $res['dias_tarde'] ?? 0;
            $olvidos = $res['olvidos_marcacion'] ?? 0;
            $horasMes = $res['horas_mes'] ?? '0h 0m';
            $saldoMin = $res['saldo_retraso_formateado'] ?? '0 min';
            $estadoRetraso = $res['estado_retraso'] ?? 'Dentro de tolerancia';

            $c = 0;
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $emp->nombre_completo);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $emp->codigo_biometrico ?: '—');
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $emp->sucursal ?? '—');
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $horasMes);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $retrasoFormateado);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $diasTarde);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $olvidos);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $saldoMin);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $estadoRetraso);

            // Alineaciones
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$currentRow}:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        // Bordes finos en toda la tabla
        $dataEndRow = max($headerRow, $currentRow - 1);
        $tableRange = "A{$headerRow}:{$lastCol}{$dataEndRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Auto-ajustar ancho de columnas
        foreach ($cols as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = 'Reporte_Control_' . Str::slug($sucursalLabel) . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function aplicarFiltroSucursales(): void
    {
        $this->appliedSucursalesPeriodoTipo = $this->sucursalesPeriodoTipo;
        $this->appliedSucursalesFechaDia = $this->sucursalesFechaDia ?: now()->toDateString();
        $this->appliedSucursalesMes = $this->sucursalesMes ?: now()->format('Y-m');
        $this->appliedSucursalesSucursal = $this->sucursalesSucursal;
        $this->appliedSucursalesSearch = trim($this->sucursalesSearch);
        $this->appliedSucursalesEstadoFiltro = $this->sucursalesEstadoFiltro;
        $this->appliedSucursalesOrden = $this->sucursalesOrden;

        $this->resetPage('sucursalesPage');
    }

    public function limpiarFiltrosSucursales(): void
    {
        $refMonth = $this->referenceMonth();
        $this->sucursalesPeriodoTipo = 'mes';
        $this->sucursalesFechaDia = now()->toDateString();
        $this->sucursalesMes = $refMonth->format('Y-m');
        $this->sucursalesSucursal = '';
        $this->sucursalesSearch = '';
        $this->sucursalesEstadoFiltro = 'todos';
        $this->sucursalesOrden = 'fecha_desc';

        $this->aplicarFiltroSucursales();
    }

    public function setSucursalesPeriodoTipo(string $tipo): void
    {
        if (in_array($tipo, ['dia', 'mes'], true)) {
            $this->sucursalesPeriodoTipo = $tipo;
            $this->appliedSucursalesPeriodoTipo = $tipo;
            $this->resetPage('sucursalesPage');
        }
    }

    public function obtenerColeccionMarcacionesSucursales(bool $paginated = false): array
    {
        $searchOperator = $this->caseInsensitiveLikeOperator();

        // Opciones de sucursales
        $sucursalesLista = SucursalNormalizer::optionsFromValues(
            Empleado::query()
                ->select('sucursal')
                ->whereNotNull('sucursal')
                ->where('sucursal', '!=', '')
                ->distinct()
                ->orderBy('sucursal')
                ->pluck('sucursal')
        );

        $query = RegistroAsistencia::query()
            ->with(['empleado'])
            ->whereHas('empleado')
            ->where($this->excludeSaturdayRecords());

        // Filtro de temporalidad
        $tipo = $this->appliedSucursalesPeriodoTipo ?: 'mes';
        if ($tipo === 'dia') {
            $fechaDia = $this->appliedSucursalesFechaDia ?: now()->toDateString();
            $query->whereDate('fecha', $fechaDia);
            $carbonDia = Carbon::parse($fechaDia);
            $periodoLabel = ucfirst($carbonDia->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
        } else {
            $mesStr = $this->appliedSucursalesMes ?: now()->format('Y-m');
            try {
                $carbonMes = Carbon::parse($mesStr . '-01');
            } catch (\Throwable $e) {
                $carbonMes = now()->startOfMonth();
            }
            $query->whereBetween('fecha', [
                $carbonMes->copy()->startOfMonth()->toDateString(),
                $carbonMes->copy()->endOfMonth()->toDateString(),
            ]);
            $periodoLabel = ucfirst($carbonMes->locale('es')->translatedFormat('F Y'));
        }

        // Filtro por sucursal
        if (filled($this->appliedSucursalesSucursal) && $this->appliedSucursalesSucursal !== 'todas') {
            $query->whereHas('empleado', function ($q) {
                SucursalNormalizer::applyFilter($q, 'sucursal', $this->appliedSucursalesSucursal);
            });
            $sucursalLabel = SucursalNormalizer::canonicalLabel($this->appliedSucursalesSucursal);
        } else {
            $sucursalLabel = 'Todas las sucursales';
        }

        // Filtro por texto (código, nombre, apellido)
        if (filled($this->appliedSucursalesSearch)) {
            $term = "%{$this->appliedSucursalesSearch}%";
            $query->whereHas('empleado', function ($q) use ($searchOperator, $term) {
                $q->where(function ($sub) use ($searchOperator, $term) {
                    $sub->where('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term)
                        ->orWhereRaw("nombre || ' ' || apellido " . ($searchOperator === 'ilike' ? 'ILIKE' : 'LIKE') . " ?", [$term]);
                });
            });
        }

        // Orden básico de base de datos
        $query->orderBy('fecha', 'desc')->orderBy('hora_entrada', 'asc');

        $progService = $this->programacionLaboral();
        $todosLosRegistros = $query->get();

        // Transformación y cálculo
        $rows = $todosLosRegistros->map(function (RegistroAsistencia $registro) use ($progService) {
            $marcacion = $this->normalizarMarcacionAsistencia($registro);
            $entradaVal = filled($marcacion['entrada']) ? substr($marcacion['entrada'], 0, 5) : null;
            $salidaVal = filled($marcacion['salida']) ? substr($marcacion['salida'], 0, 5) : null;
            $tieneEntrada = filled($entradaVal) && $entradaVal !== '--:--';
            $tieneSalida = filled($salidaVal) && $salidaVal !== '--:--';

            $empleado = $registro->empleado;
            $horario = $empleado && $registro->fecha ? $progService->resolverHorario($empleado, $registro->fecha) : null;
            $horaEntradaProg = $horario['hora_entrada'] ?? config('asistencia.hora_entrada', '08:30:00');
            $horaSalidaProg = $horario['hora_salida'] ?? config('asistencia.hora_salida', '16:30:00');
            $horaLimite = $horario['hora_entrada_tolerancia'] ?? $horaEntradaProg;

            $minutosRetraso = 0;
            if ($tieneEntrada && $horaLimite) {
                $minutosRetraso = $this->calcularMinutosRetraso($entradaVal, $horaLimite);
            }

            $minutosTrabajados = 0;
            $horasTrabajadas = '--:--';
            if ($tieneEntrada && $tieneSalida) {
                $minutosTrabajados = $this->calcularMinutosTrabajados($entradaVal, $salidaVal);
                $horasTrabajadas = sprintf('%dh %02dm', intdiv($minutosTrabajados, 60), $minutosTrabajados % 60);
            }

            $codigoBio = $empleado?->codigo_biometrico ?: (string) ($empleado?->id ?? '');

            if ($tieneEntrada && $tieneSalida) {
                if ($minutosRetraso > 0) {
                    $estado = "Retraso (+{$minutosRetraso} min)";
                    $tipoEstado = 'retraso';
                } else {
                    $estado = 'Puntual';
                    $tipoEstado = 'puntual';
                }
            } elseif ($tieneEntrada && ! $tieneSalida) {
                if ($registro->fecha?->isToday()) {
                    $estado = 'En jornada (sin salida)';
                    $tipoEstado = 'en_curso';
                } else {
                    $estado = 'Falta marcar salida';
                    $tipoEstado = 'incompleto';
                }
            } elseif (! $tieneEntrada && $tieneSalida) {
                $estado = 'Falta marcar entrada';
                $tipoEstado = 'incompleto';
            } else {
                $estado = 'Sin marcación';
                $tipoEstado = 'sin_marcacion';
            }

            $horarioProgLabel = substr((string) $horaEntradaProg, 0, 5) . ' - ' . substr((string) $horaSalidaProg, 0, 5);

            return (object) [
                'id' => $registro->id,
                'empleado' => $empleado,
                'empleado_id' => $empleado?->id,
                'codigo' => $codigoBio,
                'fecha' => $registro->fecha,
                'fecha_formateada' => $registro->fecha?->format('d/m/Y'),
                'dia' => $registro->fecha?->locale('es')->isoFormat('dddd'),
                'horario_programado' => $horarioProgLabel,
                'hora_entrada' => $entradaVal ?: '--:--',
                'hora_salida' => $salidaVal ?: '--:--',
                'horas_trabajadas' => $horasTrabajadas,
                'minutos_retraso' => $minutosRetraso,
                'retraso_formateado' => $tieneEntrada ? ($minutosRetraso > 0 ? "+{$minutosRetraso} min" : 'Puntual') : '--',
                'estado_marcacion' => $estado,
                'tipo_estado' => $tipoEstado,
                'observacion' => $registro->observacion,
            ];
        });

        // Filtrar por estado si se especificó
        if ($this->appliedSucursalesEstadoFiltro === 'puntual') {
            $rows = $rows->filter(fn ($r) => $r->tipo_estado === 'puntual')->values();
        } elseif ($this->appliedSucursalesEstadoFiltro === 'retraso') {
            $rows = $rows->filter(fn ($r) => $r->tipo_estado === 'retraso' || $r->minutos_retraso > 0)->values();
        } elseif ($this->appliedSucursalesEstadoFiltro === 'incompleto') {
            $rows = $rows->filter(fn ($r) => in_array($r->tipo_estado, ['incompleto', 'en_curso', 'sin_marcacion'], true))->values();
        }

        // Ordenamiento
        $rows = match ($this->appliedSucursalesOrden) {
            'fecha_asc' => $rows->sortBy(fn ($r) => ($r->fecha?->toDateString() ?? '') . ' ' . $r->hora_entrada)->values(),
            'retraso_desc' => $rows->sortByDesc(fn ($r) => $r->minutos_retraso)->values(),
            'nombre_asc' => $rows->sortBy(fn ($r) => $r->empleado?->nombre_completo ?? '')->values(),
            default => $rows->sortByDesc(fn ($r) => ($r->fecha?->toDateString() ?? '') . ' ' . $r->hora_entrada)->values(), // fecha_desc
        };

        // Estadísticas / KPIs
        $totalMarcaciones = $rows->count();
        $totalPuntuales = $rows->filter(fn ($r) => $r->tipo_estado === 'puntual')->count();
        $pctPuntual = $totalMarcaciones > 0 ? round(($totalPuntuales / $totalMarcaciones) * 100, 1) : 0;
        $totalRetrasos = $rows->filter(fn ($r) => $r->tipo_estado === 'retraso' || $r->minutos_retraso > 0)->count();
        $totalMinutosRetraso = (int) $rows->sum(fn ($r) => $r->minutos_retraso);
        $totalIncompletas = $rows->filter(fn ($r) => in_array($r->tipo_estado, ['incompleto', 'sin_marcacion'], true))->count();
        $totalEmpleadosUnicos = $rows->pluck('empleado_id')->filter()->unique()->count();

        $stats = [
            'total_marcaciones' => $totalMarcaciones,
            'total_puntuales' => $totalPuntuales,
            'pct_puntual' => $pctPuntual,
            'total_retrasos' => $totalRetrasos,
            'total_minutos_retraso' => $totalMinutosRetraso,
            'total_incompletas' => $totalIncompletas,
            'total_empleados_unicos' => $totalEmpleadosUnicos,
        ];

        $paginatedResults = $paginated
            ? $this->paginarColeccionConNombre($rows, $this->sucursalesPerPage, 'sucursalesPage')
            : $rows;

        return [
            'registros' => $paginatedResults,
            'allRows' => $rows,
            'stats' => $stats,
            'periodoLabel' => $periodoLabel,
            'sucursalLabel' => $sucursalLabel,
            'sucursalesLista' => $sucursalesLista,
        ];
    }

    public function descargarPdfSucursales()
    {
        $data = $this->obtenerColeccionMarcacionesSucursales(false);
        $periodoLabel = $data['periodoLabel'];
        $sucursalLabel = $data['sucursalLabel'];
        $stats = $data['stats'];
        $rows = $data['allRows'];

        $pdf = Pdf::loadView('pdf.marcaciones-sucursales', [
            'periodoLabel' => $periodoLabel,
            'sucursalLabel' => $sucursalLabel,
            'stats' => $stats,
            'registros' => $rows,
            'filtroEstado' => $this->appliedSucursalesEstadoFiltro,
        ])->setPaper('letter', 'landscape');

        $slugSucursal = Str::slug($sucursalLabel);
        $fileName = "Marcaciones_Sucursal_{$slugSucursal}_" . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }

    public function descargarExcelSucursales()
    {
        $data = $this->obtenerColeccionMarcacionesSucursales(false);
        $periodoLabel = $data['periodoLabel'];
        $sucursalLabel = $data['sucursalLabel'];
        $rows = $data['allRows'];
        $stats = $data['stats'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Marcaciones Sucursales');

        $currentRow = 1;

        // Título
        $sheet->setCellValue("A{$currentRow}", 'EMPRESA DE CORREOS DE BOLIVIA - REPORTE DE MARCACIONES POR SUCURSAL');
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
        $currentRow++;

        // Metadata
        $sheet->setCellValue("A{$currentRow}", "Sucursal: {$sucursalLabel} | Período: {$periodoLabel} | Emisión: " . now()->format('d/m/Y H:i'));
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9.5)->getColor()->setRGB('475569');
        $currentRow++;

        // Resumen métrico
        $sheet->setCellValue(
            "A{$currentRow}",
            "Total Marcaciones: " . ($stats['total_marcaciones'] ?? 0) .
            " | Puntuales: " . ($stats['total_puntuales'] ?? 0) . " ({$stats['pct_puntual']}%)" .
            " | Con Retraso: " . ($stats['total_retrasos'] ?? 0) . " ({$stats['total_minutos_retraso']} min)" .
            " | Incompletas: " . ($stats['total_incompletas'] ?? 0) .
            " | Personal Único: " . ($stats['total_empleados_unicos'] ?? 0)
        );
        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9)->getColor()->setRGB('334155');
        $currentRow += 2;

        $headerRow = $currentRow;
        $headers = ['Fecha', 'Día', 'Personal', 'Código', 'Cargo / Área', 'Sucursal', 'Horario Prog.', 'Hora Entrada', 'Hora Salida', 'Horas Trab.', 'Minutos Retraso', 'Estado'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($headers as $idx => $headerText) {
            $colLetter = $cols[$idx];
            $sheet->setCellValue("{$colLetter}{$headerRow}", $headerText);
        }

        $lastCol = end($cols);
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $currentRow++;

        foreach ($rows as $row) {
            $c = 0;
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->fecha_formateada);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", ucfirst($row->dia ?? ''));
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->empleado?->nombre_completo ?? 'N/D');
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->codigo);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->empleado?->area ?? '');
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->empleado?->sucursal ?? '');
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->horario_programado);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->hora_entrada);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->hora_salida);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->horas_trabajadas);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->minutos_retraso);
            $sheet->setCellValue("{$cols[$c++]}{$currentRow}", $row->estado_marcacion);

            $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$currentRow}:L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        $dataEndRow = max($headerRow, $currentRow - 1);
        $tableRange = "A{$headerRow}:{$lastCol}{$dataEndRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        foreach ($cols as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $slugSucursal = Str::slug($sucursalLabel);
        $fileName = "Marcaciones_Sucursal_{$slugSucursal}_" . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    public function render()
    {
        $referenceMonth = $this->referenceMonth();
        $monthStart = $referenceMonth->copy()->startOfMonth()->toDateString();
        $monthEnd = $referenceMonth->copy()->endOfMonth()->toDateString();

        $searchOperator = $this->caseInsensitiveLikeOperator();

        // ─── Vista Registro mensual de sucursales ───
        if ($this->vista === 'sucursales') {
            $dataSucursales = $this->obtenerColeccionMarcacionesSucursales(true);

            return view('livewire.personal', [
                'sucursalesRegistros' => $dataSucursales['registros'],
                'sucursalesStats' => $dataSucursales['stats'],
                'sucursalesPeriodoLabel' => $dataSucursales['periodoLabel'],
                'sucursalesLista' => $dataSucursales['sucursalesLista'],
                'sucursalSeleccionadaLabel' => $dataSucursales['sucursalLabel'],
                'empleados' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'page']),
                'registros' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'registrosPage']),
                'mes_resumen' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
                'sucursales' => $dataSucursales['sucursalesLista'],
                'totalHorasMes' => '0h 0m',
                'empleadosSancionadosModal' => collect([]),
                'totalSancionadosAcumulativo2' => 0,
                'totalSancionadosGeneral' => 0,
                'totalActivosSistema' => Empleado::query()->activosLaboralmente()->count(),
                'totalInactivosSistema' => Empleado::query()->inactivosLaboralmente()->count(),
                'totalPadronSistema' => Empleado::query()->count(),
                'totalMarcacionesHoySistema' => RegistroAsistencia::query()->whereDate('fecha', now()->toDateString())->whereNotNull('empleado_id')->distinct('empleado_id')->count('empleado_id'),
            ])->layout('layouts.app', ['title' => $this->pageTitle()]);
        }

        $empleadosBaseQuery = Empleado::query()
            ->withUltimaMarcacion();

        // ─── Vista Control / Marcaciones por Sucursal ───
        if ($this->vista === 'control') {
            $sucursales = SucursalNormalizer::optionsFromValues(Empleado::query()
                ->select('sucursal')->whereNotNull('sucursal')->where('sucursal', '!=', '')
                ->distinct()->orderBy('sucursal')->pluck('sucursal'));

            $resumenSucursalesConteo = Empleado::query()
                ->activosLaboralmente()
                ->whereNotNull('sucursal')->where('sucursal', '!=', '')
                ->get()
                ->groupBy(fn ($e) => SucursalNormalizer::normalize($e->sucursal))
                ->map(fn ($group) => $group->count())
                ->all();

            $targetYear = (int) ($this->appliedControlAnio ?: $referenceMonth->year);
            $targetMonthNum = (int) ($this->appliedControlMesNumero ?: $referenceMonth->month);
            $targetMonth = Carbon::createFromDate($targetYear, $targetMonthNum, 1);
            $controlMonthStart = $targetMonth->copy()->startOfMonth()->toDateString();
            $controlMonthEnd = $targetMonth->copy()->endOfMonth()->toDateString();

            $departmentStats = app(\App\Services\AnalisisAsistenciaService::class)->asistenciaPorDepartamento();

            if (! $this->controlSearchPerformed) {
                $empleados = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]);
                $totalHorasMes = '0h 0m';
                $sucursalKpis = [];

                $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(), 'pageName' => 'registrosPage',
                ]);

                return view('livewire.personal', [
                    'empleados' => $empleados,
                    'registros' => $registros,
                    'mes_resumen' => ucfirst($targetMonth->locale('es')->translatedFormat('F Y')),
                    'sucursales' => $sucursales,
                    'resumenSucursalesConteo' => $resumenSucursalesConteo,
                    'departmentStats' => $departmentStats,
                    'sucursalKpis' => $sucursalKpis,
                    'totalHorasMes' => $totalHorasMes,
                ])->layout('layouts.app', ['title' => $this->pageTitle()]);
            }

            // Búsqueda activa: aplicar filtros y calcular resúmenes
            $controlQuery = Empleado::query()
                ->withUltimaMarcacion()
                ->with(['asistencias' => function ($query) use ($controlMonthStart, $controlMonthEnd) {
                    $query->whereBetween('fecha', [$controlMonthStart, $controlMonthEnd])->orderByDesc('fecha');
                }]);

            if (filled($this->appliedControlSearch)) {
                $term = "%{$this->appliedControlSearch}%";
                $controlQuery->where(function ($q) use ($searchOperator, $term) {
                    $q->where('codigo_biometrico', $searchOperator, $term)
                        ->orWhere('nombre', $searchOperator, $term)
                        ->orWhere('apellido', $searchOperator, $term);
                });
            }

            if (filled($this->appliedControlSucursal) && $this->appliedControlSucursal !== 'todas') {
                SucursalNormalizer::applyFilter($controlQuery, 'sucursal', $this->appliedControlSucursal);
            }

            $registrosPorNombre = $this->registrosPorNombre($controlMonthStart, $controlMonthEnd);

            $listaControl = $controlQuery->get()
                ->filter(fn (Empleado $e) => ! $e->trashed())
                ->filter(fn (Empleado $e) => $e->fecha_despido === null || $e->fecha_despido > now()->toDateString())
                ->filter(fn (Empleado $e) => $e->estaActivoLaboralmente(now()))
                ->values();

            $listaControl->transform(function (Empleado $empleado) use ($registrosPorNombre, $targetMonth) {
                return $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $targetMonth);
            });

            // KPIs y porcentajes de la sucursal
            $sucursalKpis = $this->calcularKpisSucursal($listaControl);

            // Ordenar la colección según $ordenControl
            $listaControl = match ($this->ordenControl) {
                'nombre_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->nombre . ' ' . $e->apellido)->values(),
                'horas_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0)->values(),
                'horas_asc' => $listaControl->sortBy(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0)->values(),
                'retraso_desc' => $listaControl->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values(),
                'retraso_asc' => $listaControl->sortBy(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values(),
                'excedido_primero' => $listaControl->sortByDesc(fn (Empleado $e) => ($e->resumen_asistencia['estado_retraso'] ?? '') === 'Excedido' ? 1 : 0)->values(),
                default => $listaControl->sortBy(fn (Empleado $e) => $e->nombre . ' ' . $e->apellido)->values(), // nombre_asc
            };

            $totalMinutosMes = $listaControl->sum(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0);
            $totalHorasMes = $this->formatearMinutos((int) $totalMinutosMes);
            $empleados = $this->paginarColeccion($listaControl, 10);

            $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(), 'pageName' => 'registrosPage',
            ]);

            $empleadosSancionadosModal = $listaControl->filter(function (Empleado $e) {
                $sancion = $e->resumen_asistencia['sancion_regla'] ?? null;
                if (!$sancion) {
                    return false;
                }
                if ($this->filtroModalSancion === 'acumulativo_2') {
                    return $sancion['es_acumulativo_2'] ?? false;
                }
                return (($sancion['dias_sancion'] ?? 0) > 0 || ($sancion['es_destitucion'] ?? false));
            })->sortByDesc(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0)->values();

            $totalSancionadosAcumulativo2 = $listaControl->filter(fn (Empleado $e) => ($e->resumen_asistencia['sancion_regla']['es_acumulativo_2'] ?? false))->count();
            $totalSancionadosGeneral = $listaControl->filter(fn (Empleado $e) => (($e->resumen_asistencia['sancion_regla']['dias_sancion'] ?? 0) > 0 || ($e->resumen_asistencia['sancion_regla']['es_destitucion'] ?? false)))->count();

            return view('livewire.personal', [
                'empleados' => $empleados,
                'registros' => $registros,
                'mes_resumen' => ucfirst($targetMonth->locale('es')->translatedFormat('F Y')),
                'sucursales' => $sucursales,
                'resumenSucursalesConteo' => $resumenSucursalesConteo,
                'departmentStats' => $departmentStats,
                'sucursalKpis' => $sucursalKpis,
                'totalHorasMes' => $totalHorasMes,
                'empleadosSancionadosModal' => $empleadosSancionadosModal,
                'totalSancionadosAcumulativo2' => $totalSancionadosAcumulativo2,
                'totalSancionadosGeneral' => $totalSancionadosGeneral,
                'totalActivosSistema' => Empleado::query()->activosLaboralmente()->count(),
                'totalInactivosSistema' => Empleado::query()->inactivosLaboralmente()->count(),
                'totalPadronSistema' => Empleado::query()->count(),
                'totalMarcacionesHoySistema' => RegistroAsistencia::query()->whereDate('fecha', now()->toDateString())->whereNotNull('empleado_id')->distinct('empleado_id')->count('empleado_id'),
            ])->layout('layouts.app', ['title' => $this->pageTitle()]);
        }

        // ─── Resto de vistas (personal, inactivos, marcaciones) ───
        $calcularResumen = false; // No se usa resumen en estas vistas

        if (filled($this->search)) {
            $empleadosBaseQuery->where(function ($query) use ($searchOperator) {
                $query->where('codigo_biometrico', $searchOperator, "%{$this->search}%")
                    ->orWhere('nombre', $searchOperator, "%{$this->search}%")
                    ->orWhere('apellido', $searchOperator, "%{$this->search}%")
                    ->orWhere('email', $searchOperator, "%{$this->search}%");
            });
        }

        if (filled($this->sucursalFiltro)) {
            SucursalNormalizer::applyFilter($empleadosBaseQuery, 'sucursal', $this->sucursalFiltro);
        }

        $sucursales = SucursalNormalizer::optionsFromValues(Empleado::query()
            ->select('sucursal')
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->distinct()
            ->orderBy('sucursal')
            ->pluck('sucursal'));

        $empleadosResumen = (clone $empleadosBaseQuery)
            ->orderByDesc('created_at')
            ->get();

        $empleadosResumen->each(function (Empleado $empleado) {
            $empleado->estado_laboral = $empleado->estadoLaboral(now());
            $empleado->ultima_marcacion_label = $empleado->ultimaMarcacion()?->format('d/m/Y') ?? 'Sin marcaciones';
        });

        $empleadosFiltrados = $empleadosResumen
            ->filter(fn (Empleado $empleado) => $this->employeeMatchesVista($empleado))
            ->when($this->vista === 'control' && filled($this->toleranciaFiltro), function ($items) {
                return $items->filter(function (Empleado $empleado) {
                    $estadoTolerancia = $empleado->resumen_asistencia['estado_retraso'] ?? 'Dentro de tolerancia';

                    return match ($this->toleranciaFiltro) {
                        'dentro' => $estadoTolerancia === 'Dentro de tolerancia',
                        'excedido' => $estadoTolerancia === 'Excedido',
                        default => true,
                    };
                });
            })
            ->when($this->vista === 'control', fn ($items) => $items->sortByDesc(fn (Empleado $empleado) => $empleado->resumen_asistencia['retraso_mes'] ?? 0))
            ->values();

        $totalMinutosMes = 0;
        $empleados = $this->paginarColeccion($empleadosFiltrados, 10);
        $totalHorasMes = '0h 0m';

        // Lógica de registros de marcaciones:
        if ($this->vista === 'marcaciones') {
            if (!$this->marcacionesSearchPerformed) {
                $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(),
                    'pageName' => 'registrosPage',
                ]);
            } else {
                $registrosQuery = RegistroAsistencia::query()
                    ->with('empleado')
                    ->whereHas('empleado')
                    ->where($this->excludeSaturdayRecords());

                if ($this->appliedMarcacionesTipoFecha === 'mes' && filled($this->appliedMarcacionesMes)) {
                    try {
                        $carbonMes = Carbon::parse($this->appliedMarcacionesMes . '-01');
                        $registrosQuery->whereBetween('fecha', [
                            $carbonMes->copy()->startOfMonth()->toDateString(),
                            $carbonMes->copy()->endOfMonth()->toDateString(),
                        ]);
                    } catch (\Exception $e) {
                        // ignorar formato inválido
                    }
                } elseif (filled($this->appliedMarcacionesFechaInicio) && filled($this->appliedMarcacionesFechaFin)) {
                    $registrosQuery->whereBetween('fecha', [
                        min($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin),
                        max($this->appliedMarcacionesFechaInicio, $this->appliedMarcacionesFechaFin),
                    ]);
                } elseif (filled($this->appliedMarcacionesFechaInicio)) {
                    $registrosQuery->whereDate('fecha', '>=', $this->appliedMarcacionesFechaInicio);
                } elseif (filled($this->appliedMarcacionesFechaFin)) {
                    $registrosQuery->whereDate('fecha', '<=', $this->appliedMarcacionesFechaFin);
                }

                if ($this->selectedMarcacionesEmpleadoId) {
                    $registrosQuery->where('empleado_id', $this->selectedMarcacionesEmpleadoId);
                } elseif (filled($this->appliedMarcacionesSearch)) {
                    $term = "%{$this->appliedMarcacionesSearch}%";
                    $registrosQuery->whereHas('empleado', function ($empleadoQuery) use ($searchOperator, $term) {
                        $empleadoQuery->where(function ($nestedQuery) use ($searchOperator, $term) {
                            $nestedQuery->where('codigo_biometrico', $searchOperator, $term)
                                ->orWhere('nombre', $searchOperator, $term)
                                ->orWhere('apellido', $searchOperator, $term)
                                ->orWhereRaw("nombre || ' ' || apellido " . ($searchOperator === 'ilike' ? 'ILIKE' : 'LIKE') . " ?", [$term]);
                        });
                    });
                }

                if ($this->filterEstadoMarcaciones === 'completo') {
                    $registrosQuery->whereNotNull('hora_entrada')->where('hora_entrada', '!=', '')
                        ->whereNotNull('hora_salida')->where('hora_salida', '!=', '');
                } elseif ($this->filterEstadoMarcaciones === 'faltante') {
                    $registrosQuery->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNotNull('hora_entrada')->where('hora_entrada', '!=', '')
                                ->where(function ($sub2) {
                                    $sub2->whereNull('hora_salida')->orWhere('hora_salida', '');
                                });
                        })->orWhere(function ($sub) {
                            $sub->where(function ($sub2) {
                                $sub2->whereNull('hora_entrada')->orWhere('hora_entrada', '');
                            })->whereNotNull('hora_salida')->where('hora_salida', '!=', '');
                        });
                    });
                }

                $registros = $registrosQuery
                    ->tap(fn($query) => $this->aplicarOrdenMarcaciones($query))
                    ->paginate(10, ['*'], 'registrosPage');

                $registros->getCollection()->transform(function (RegistroAsistencia $registro) {
                    $marcacion = $this->normalizarMarcacionAsistencia($registro);
                    $entradaVal = filled($marcacion['entrada']) ? substr($marcacion['entrada'], 0, 5) : null;
                    $salidaVal = filled($marcacion['salida']) ? substr($marcacion['salida'], 0, 5) : null;
                    $tieneEntrada = filled($entradaVal) && $entradaVal !== '--:--';
                    $tieneSalida = filled($salidaVal) && $salidaVal !== '--:--';

                    if ($tieneEntrada && $tieneSalida) {
                        $estado = 'Completo';
                        $tipoEstado = 'completo';
                    } elseif ($tieneEntrada || $tieneSalida) {
                        $estado = 'Sin completar';
                        $tipoEstado = 'faltante';
                    } else {
                        $estado = 'Sin marcación';
                        $tipoEstado = 'sin_marcacion';
                    }

                    $codigoBio = $registro->empleado?->codigo_biometrico ?: (string) $registro->empleado?->id;

                    return (object) [
                        'id' => $registro->id,
                        'empleado' => $registro->empleado,
                        'codigo' => $codigoBio,
                        'fecha' => $registro->fecha,
                        'fecha_formateada' => $registro->fecha?->format('d/m/Y'),
                        'dia' => $registro->fecha?->locale('es')->isoFormat('dddd'),
                        'hora_entrada' => $entradaVal ?: '--:--',
                        'hora_salida' => $salidaVal ?: '--:--',
                        'estado_marcacion' => $estado,
                        'tipo_estado' => $tipoEstado,
                    ];
                });
            }
        } else {
            $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'pageName' => 'registrosPage',
            ]);
        }

        $empleadosSancionadosModal = collect([]);
        $totalSancionadosAcumulativo2 = 0;
        $totalSancionadosGeneral = 0;

        if ($this->showModalSancionados2Dias) {
            $modalData = $this->obtenerColeccionSancionadosModal();
            $empleadosSancionadosModal = $modalData['empleados'];
            $totalSancionadosAcumulativo2 = $modalData['total_acumulativo_2'];
            $totalSancionadosGeneral = $modalData['total_general'];
        }

        return view('livewire.personal', [
            'empleados' => $empleados,
            'registros' => $registros,
            'mes_resumen' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'sucursales' => $sucursales,
            'totalHorasMes' => $totalHorasMes,
            'empleadosSancionadosModal' => $empleadosSancionadosModal,
            'totalSancionadosAcumulativo2' => $totalSancionadosAcumulativo2,
            'totalSancionadosGeneral' => $totalSancionadosGeneral,
            'totalActivosSistema' => Empleado::query()->activosLaboralmente()->count(),
            'totalInactivosSistema' => Empleado::query()->inactivosLaboralmente()->count(),
            'totalPadronSistema' => Empleado::query()->count(),
            'totalMarcacionesHoySistema' => RegistroAsistencia::query()->whereDate('fecha', now()->toDateString())->whereNotNull('empleado_id')->distinct('empleado_id')->count('empleado_id'),
        ])->layout('layouts.app', ['title' => $this->pageTitle()]);
    }

    private function aplicarOrdenMarcaciones(Builder $query): Builder
    {
        return match ($this->ordenMarcaciones) {
            'fecha_antigua' => $query
                ->orderBy('fecha', 'asc')
                ->orderBy('hora_entrada', 'asc')
                ->orderBy('created_at', 'asc'),
            'hora_asc' => $query
                ->orderByRaw('CASE WHEN hora_entrada IS NULL OR hora_entrada = "" THEN 1 ELSE 0 END, hora_entrada ASC')
                ->orderByDesc('fecha'),
            'hora_desc' => $query
                ->orderByRaw('CASE WHEN hora_entrada IS NULL OR hora_entrada = "" THEN 1 ELSE 0 END, hora_entrada DESC')
                ->orderByDesc('fecha'),
            'salida_asc' => $query
                ->orderByRaw('CASE WHEN hora_salida IS NULL OR hora_salida = "" THEN 1 ELSE 0 END, hora_salida ASC')
                ->orderByDesc('fecha'),
            'salida_desc' => $query
                ->orderByRaw('CASE WHEN hora_salida IS NULL OR hora_salida = "" THEN 1 ELSE 0 END, hora_salida DESC')
                ->orderByDesc('fecha'),
            'nombre_asc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderBy('empleados.nombre', 'asc')
                ->orderBy('empleados.apellido', 'asc')
                ->orderByDesc('registros_asistencia.fecha'),
            'nombre_desc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderByDesc('empleados.nombre', 'desc')
                ->orderByDesc('empleados.apellido', 'desc')
                ->orderByDesc('registros_asistencia.fecha'),
            'sucursal_asc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderBy('empleados.sucursal', 'asc')
                ->orderByDesc('registros_asistencia.fecha'),
            'sucursal_desc' => $query
                ->join('empleados', 'empleados.id', '=', 'registros_asistencia.empleado_id')
                ->select('registros_asistencia.*')
                ->orderBy('empleados.sucursal', 'desc')
                ->orderByDesc('registros_asistencia.fecha'),
            default => $query
                ->orderByDesc('fecha')
                ->orderByDesc('created_at'),
        };
    }

    private function resumenMensualEmpleado(Empleado $empleado, EloquentCollection $registrosAsistencia, Carbon $referenceMonth): array
    {
        $toleranciaMensual = $this->programacionLaboral()->resolverToleranciaMensual($empleado->sucursal);
        $fechaReferencia = $referenceMonth->isSameMonth(now())
            ? now()->toDateString()
            : null;

        $minutosTrabajados = 0;
        $minutosRetraso = 0;
        $diasTarde = 0;
        $olvidosMarcacion = 0;
        $registroHoy = null;

        foreach ($registrosAsistencia as $asistencia) {
            $horario = $this->programacionLaboral()->resolverHorario($empleado, $asistencia->fecha);

            if (!$horario['laborable']) {
                continue;
            }

            if ($fechaReferencia && $asistencia->fecha?->toDateString() === $fechaReferencia) {
                $registroHoy = $asistencia;
            }

            $marcacion = $this->normalizarMarcacionAsistencia($asistencia);
            $horaSalidaReal = $marcacion['salida'];

            $minutosTrabajados += $this->calcularMinutosTrabajados($marcacion['entrada'], $horaSalidaReal);

            if ($this->debeContarComoOlvidoMarcacion($asistencia, $empleado)) {
                $olvidosMarcacion++;
            }

            $min = $this->calcularMinutosRetraso(
                $marcacion['entrada'],
                $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
            );
            $minutosRetraso += $min;
            if ($min > 0) {
                $diasTarde++;
            }
        }

        $sancionAtraso = app(ReglamentoSancionService::class)->evaluarAtraso($minutosRetraso, 1, $referenceMonth);
        $reglaSinFiltro = app(ReglamentoSancionService::class)->evaluarAtrasoSinFiltroFecha($minutosRetraso);
        $enPeriodoTransicion = (! $sancionAtraso && $reglaSinFiltro && $minutosRetraso > $toleranciaMensual);

        $porcentajeTolerancia = $toleranciaMensual > 0
            ? (int) min(100, round(($minutosRetraso / $toleranciaMensual) * 100))
            : 100;

        return [
            'entrada_hoy' => ($registroHoy && ($marcacionHoy = $this->normalizarMarcacionAsistencia($registroHoy)) && $marcacionHoy['entrada'])
                ? substr($marcacionHoy['entrada'], 0, 5)
                : '--:--',
            'salida_hoy' => ($registroHoy && ($marcacionHoy = $this->normalizarMarcacionAsistencia($registroHoy)) && $marcacionHoy['salida'])
                ? substr($marcacionHoy['salida'], 0, 5)
                : '--:--',
            'verificacion_hoy' => $registroHoy?->tipo_verificacion ?: 'Sin registro',
            'estado_hoy' => $registroHoy
                ? ($this->normalizarMarcacionAsistencia($registroHoy)['solo_entrada'] ? 'En su puesto' : ($registroHoy->estado_marcacion ?: 'Sin registro'))
                : 'Sin registro',
            'evento_hoy' => $registroHoy?->evento_biometrico ?: 'Sin evento',
            'horas_mes' => $this->formatearMinutos($minutosTrabajados),
            'minutos_mes' => $minutosTrabajados,
            'retraso_mes' => $minutosRetraso,
            'retraso_mes_formateado' => $this->formatearMinutosEtiqueta($minutosRetraso),
            'dias_tarde' => $diasTarde,
            'saldo_retraso' => max($toleranciaMensual - $minutosRetraso, 0),
            'saldo_retraso_formateado' => $this->formatearMinutosEtiqueta(max($toleranciaMensual - $minutosRetraso, 0)),
            'tolerancia_mensual' => $toleranciaMensual,
            'tolerancia_mensual_formateada' => $this->formatearMinutosEtiqueta($toleranciaMensual),
            'exceso_retraso' => max($minutosRetraso - $toleranciaMensual, 0),
            'estado_retraso' => $minutosRetraso > $toleranciaMensual ? 'Excedido' : 'Dentro de tolerancia',
            'olvidos_marcacion' => $olvidosMarcacion,
            'porcentaje_tolerancia' => $porcentajeTolerancia,
            'en_periodo_transicion' => $enPeriodoTransicion,
            'vigencia_reglamento_detalle' => $enPeriodoTransicion ? ($reglaSinFiltro->descripcion_vigencia ?? 'No aplica en este periodo') : null,
            'sancion_regla' => $sancionAtraso ? [
                'id' => $sancionAtraso->id,
                'dias_sancion' => (float) $sancionAtraso->dias_sancion,
                'sancion_texto' => $sancionAtraso->sancion_texto,
                'causal' => $sancionAtraso->causal,
                'es_destitucion' => (bool) $sancionAtraso->es_destitucion,
                'es_acumulativo_2' => ((float) $sancionAtraso->dias_sancion >= 2.0 || $sancionAtraso->es_destitucion),
            ] : null,
        ];
    }

    private function hidratarResumenEmpleado(Empleado $empleado, \Illuminate\Support\Collection $registrosPorNombre, Carbon $referenceMonth): Empleado
    {
        $empleado->area = $this->normalizarArea($empleado->area);
        $registrosUnificados = $this->registrosUnificadosEmpleado($empleado, $registrosPorNombre);

        $empleado->resumen_asistencia = $this->resumenMensualEmpleado(
            $empleado,
            new EloquentCollection($registrosUnificados->all()),
            $referenceMonth
        );
        $empleado->estado_laboral = $empleado->estadoLaboral(now());
        $empleado->ultima_marcacion_label = $empleado->ultimaMarcacion()?->format('d/m/Y') ?? 'Sin marcaciones';

        return $empleado;
    }

    private function armarFichaEmpleado(int $empleadoId, ?Carbon $referenceMonth = null): array
    {
        $referenceMonth ??= $this->referenceMonth();
        $registrosPorNombre = $this->registrosPorNombre(
            $referenceMonth->copy()->startOfMonth()->toDateString(),
            $referenceMonth->copy()->endOfMonth()->toDateString()
        );

        $empleado = Empleado::query()
            ->with([
                'asistencias' => function ($query) use ($referenceMonth) {
                    $query->whereBetween('fecha', [
                        $referenceMonth->copy()->startOfMonth()->toDateString(),
                        $referenceMonth->copy()->endOfMonth()->toDateString(),
                    ])->orderByDesc('fecha');
                }
            ])
            ->findOrFail($empleadoId);

        $empleado = $this->hidratarResumenEmpleado($empleado, $registrosPorNombre, $referenceMonth);
        $horarioReferencia = $this->programacionLaboral()->resolverHorario($empleado, $referenceMonth);
        $registrosDetalle = $this->registrosUnificadosEmpleado($empleado, $registrosPorNombre)
            ->filter(function (RegistroAsistencia $registro) use ($empleado) {
                $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);

                return $horario['laborable'];
            })
            ->map(function (RegistroAsistencia $registro) use ($empleado) {
                $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);
                $marcacion = $this->normalizarMarcacionAsistencia($registro);
                $minutosRetraso = $this->calcularMinutosRetraso(
                    $marcacion['entrada'],
                    $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada']
                );
                $olvidoEntrada = blank($marcacion['entrada']);
                $soloEntrada = $marcacion['solo_entrada'];
                $horaSalidaReal = $marcacion['salida'];
                $olvidoSalida = blank($horaSalidaReal);

                if ($olvidoEntrada && $olvidoSalida) {
                    $estadoRegistro = 'Sin entrada ni salida';
                } elseif ($olvidoEntrada) {
                    $estadoRegistro = 'Olvido de entrada';
                } elseif ($soloEntrada) {
                    $estadoRegistro = 'En su puesto';
                } elseif ($olvidoSalida) {
                    $estadoRegistro = 'Olvido de salida';
                } elseif ($minutosRetraso > 0) {
                    $estadoRegistro = 'Ingreso con retraso';
                } else {
                    $estadoRegistro = 'Marcacion completa';
                }

                return [
                    'fecha' => $registro->fecha?->format('d/m/Y') ?? 'Sin fecha',
                    'dia' => ucfirst($registro->fecha?->locale('es')->isoFormat('dddd') ?? 'Sin dia'),
                    'entrada' => $marcacion['entrada'] ? substr($marcacion['entrada'], 0, 5) : '--:--',
                    'salida' => $horaSalidaReal ? substr($horaSalidaReal, 0, 5) : '--:--',
                    'retraso' => $this->formatearMinutosEtiqueta($minutosRetraso),
                    'estado' => $estadoRegistro,
                    'estado_biometrico' => $soloEntrada ? 'En su puesto' : $this->resolverEstadoMarcacionVisible($registro, $marcacion),
                ];
            })
            ->filter(function (array $registro) {
                return match ($this->detailMarkingFilter) {
                    'entrada' => $registro['entrada'] !== '--:--',
                    'salida' => $registro['salida'] !== '--:--',
                    default => true,
                };
            })
            ->values()
            ->all();

        return [
            'nombre_completo' => $empleado->nombre_completo,
            'codigo_biometrico' => $empleado->codigo_biometrico ?: 'Sin asignar',
            'email' => $empleado->email ?: 'Sin correo asignado',
            'estado_laboral' => $empleado->estado_laboral ?? $empleado->estadoLaboral(now()),
            'ultima_marcacion' => $empleado->ultima_marcacion_label ?? ($empleado->ultimaMarcacion()?->format('d/m/Y') ?? 'Sin marcaciones'),
            'area' => $empleado->area ?: 'Sin area',
            'sucursal' => $empleado->sucursal ?: 'Sin sucursal',
            'fecha_nacimiento' => $empleado->fecha_nacimiento?->format('d/m/Y'),
            'fecha_contratacion' => $empleado->fecha_contratacion?->format('d/m/Y') ?? 'Sin fecha',
            'fecha_despido' => $empleado->fecha_despido?->format('d/m/Y'),
            'hora_entrada_programada' => $horarioReferencia['hora_entrada']
                ? substr($horarioReferencia['hora_entrada'], 0, 5)
                : substr((string) config('asistencia.hora_entrada'), 0, 5),
            'hora_salida_programada' => $horarioReferencia['hora_salida']
                ? substr($horarioReferencia['hora_salida'], 0, 5)
                : substr((string) config('asistencia.hora_salida'), 0, 5),
            'horas_mes' => $empleado->resumen_asistencia['horas_mes'],
            'retraso_mes' => $empleado->resumen_asistencia['retraso_mes_formateado'],
            'saldo_mes' => $empleado->resumen_asistencia['saldo_retraso_formateado'],
            'dias_tarde' => $empleado->resumen_asistencia['dias_tarde'],
            'olvidos_marcacion' => $empleado->resumen_asistencia['olvidos_marcacion'],
            'estado_retraso' => $empleado->resumen_asistencia['estado_retraso'],
            'tolerancia_mensual' => $empleado->resumen_asistencia['tolerancia_mensual_formateada'],
            'entrada_hoy' => $empleado->resumen_asistencia['entrada_hoy'],
            'salida_hoy' => $empleado->resumen_asistencia['salida_hoy'],
            'verificacion_hoy' => $empleado->resumen_asistencia['verificacion_hoy'],
            'estado_hoy' => $empleado->resumen_asistencia['estado_hoy'],
            'marcaciones_mes' => $registrosDetalle,
            'mes_referencia' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
        ];
    }

    private function monthOptionsForEmpleado(Empleado $empleado): array
    {
        $inicio = ($empleado->fecha_contratacion ?? now())->copy()->startOfMonth();
        $fin = $this->referenceMonth()->copy()->startOfMonth();

        if ($inicio->greaterThan($fin)) {
            $inicio = $fin->copy();
        }

        $options = [];
        $cursor = $inicio->copy();

        while ($cursor->lte($fin)) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('es')->translatedFormat('F Y')),
            ];
            $cursor->addMonthNoOverflow();
        }

        return array_reverse($options);
    }

    private function resolveInitialDetailReferenceMonth(Empleado $empleado): string
    {
        $options = $this->monthOptionsForEmpleado($empleado);

        return $this->resolveInitialMonthReference($empleado, $options);
    }

    private function resolveInitialMonthReference(Empleado $empleado, ?array $options = null): string
    {
        $options ??= $this->monthOptionsForEmpleado($empleado);

        return $options[0]['value'] ?? $this->referenceMonth()->format('Y-m');
    }

    private function detailMonthCarbon(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->detailReferenceMonth)->startOfMonth();
    }

    private function pdfMonthCarbon(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->pdfReferenceMonth)->startOfMonth();
    }

    private function calcularMinutosTrabajados(?string $horaEntrada, ?string $horaSalida): int
    {
        if (blank($horaEntrada) || blank($horaSalida)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $salida = $this->parseTimeToCarbon($horaSalida);

        if (!$entrada || !$salida) {
            return 0;
        }

        if ($salida->lessThanOrEqualTo($entrada)) {
            return 0;
        }

        return $entrada->diffInMinutes($salida);
    }

    private function tieneSoloEntradaMarcada(RegistroAsistencia $registro): bool
    {
        return $this->normalizarMarcacionAsistencia($registro)['solo_entrada'];
    }

    private function horaSalidaReal(RegistroAsistencia $registro): ?string
    {
        return $this->normalizarMarcacionAsistencia($registro)['salida'];
    }

    private function debeContarComoOlvidoMarcacion(RegistroAsistencia $registro, Empleado $empleado): bool
    {
        $marcacion = $this->normalizarMarcacionAsistencia($registro);

        if (blank($marcacion['entrada'])) {
            return true;
        }

        if (!blank($marcacion['salida'])) {
            return false;
        }

        return !$this->salidaSiguePendienteDentroDeJornada($registro, $empleado);
    }

    private function salidaSiguePendienteDentroDeJornada(RegistroAsistencia $registro, Empleado $empleado): bool
    {
        if (!$this->tieneSoloEntradaMarcada($registro)) {
            return false;
        }

        if (!$registro->fecha?->isToday()) {
            return false;
        }

        $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);

        if (($horario['laborable'] ?? true) === false) {
            return false;
        }

        $salidaProgramada = $this->combinarFechaYHora(
            $registro->fecha,
            $horario['hora_salida'] ?? config('asistencia.hora_salida')
        );

        if (!$salidaProgramada) {
            return true;
        }

        return now()->lessThanOrEqualTo($salidaProgramada);
    }

    private function normalizarHoraComparable(?string $hora): ?string
    {
        if (blank($hora)) {
            return null;
        }

        $parsed = $this->parseTimeToCarbon($hora);

        return $parsed?->format('H:i:s') ?: trim((string) $hora);
    }

    private function normalizarMarcacionAsistencia(RegistroAsistencia $registro): array
    {
        $entrada = $this->normalizarHoraComparable($registro->hora_entrada);
        $salida = $this->normalizarHoraComparable($registro->hora_salida);
        $estado = $this->normalizarTextoPerfil((string) ($registro->estado_marcacion ?? ''));
        $evento = $this->normalizarTextoPerfil((string) ($registro->evento_biometrico ?? ''));

        $entradaExplicita = $this->marcacionEsEntradaExplicita($estado, $evento);
        $salidaExplicita = $this->marcacionEsSalidaExplicita($estado, $evento);

        if ($salidaExplicita && !$entradaExplicita && filled($entrada) && blank($salida)) {
            $salida = $entrada;
            $entrada = null;
        }

        if (filled($entrada) xor filled($salida)) {
            [$entrada, $salida] = $this->inferirMarcacionPorHorario($registro, $entrada, $salida);
        }

        if (filled($entrada) && filled($salida) && $entrada === $salida) {
            $salida = null;
        }

        return [
            'entrada' => $entrada,
            'salida' => $salida,
            'solo_entrada' => filled($entrada) && blank($salida),
        ];
    }

    private function marcacionEsEntradaExplicita(string $estado, string $evento): bool
    {
        return str_contains($estado, 'entrada')
            || str_contains($estado, 'retorno')
            || str_contains($estado, 'ingreso')
            || str_contains($evento, 'retorno');
    }

    private function marcacionEsSalidaExplicita(string $estado, string $evento): bool
    {
        return str_contains($estado, 'salida')
            || str_contains($evento, 'boton de salida');
    }

    private function inferirMarcacionPorHorario(RegistroAsistencia $registro, ?string $entrada, ?string $salida): array
    {
        $empleado = $registro->empleado;

        if (!$empleado || !$registro->fecha) {
            return [$entrada, $salida];
        }

        $horario = $this->programacionLaboral()->resolverHorario($empleado, $registro->fecha);

        if (($horario['laborable'] ?? true) === false) {
            return [$entrada, $salida];
        }

        $horaEntrada = $this->parseTimeToCarbon((string) ($horario['hora_entrada'] ?? ''));
        $horaSalida = $this->parseTimeToCarbon((string) ($horario['hora_salida'] ?? ''));

        if (!$horaEntrada || !$horaSalida) {
            return [$entrada, $salida];
        }

        $entradaReferencia = ((int) $horaEntrada->format('H')) * 60 + (int) $horaEntrada->format('i');
        $salidaReferencia = ((int) $horaSalida->format('H')) * 60 + (int) $horaSalida->format('i');
        $puntoMedio = (int) floor(($entradaReferencia + $salidaReferencia) / 2);

        if (filled($entrada) && blank($salida)) {
            $marca = $this->parseTimeToCarbon($entrada);

            if ($marca && (((int) $marca->format('H')) * 60 + (int) $marca->format('i')) >= $puntoMedio) {
                return [null, $entrada];
            }
        }

        if (blank($entrada) && filled($salida)) {
            $marca = $this->parseTimeToCarbon($salida);

            if ($marca && (((int) $marca->format('H')) * 60 + (int) $marca->format('i')) < $puntoMedio) {
                return [$salida, null];
            }
        }

        return [$entrada, $salida];
    }

    private function resolverEstadoMarcacionVisible(RegistroAsistencia $registro, ?array $marcacion = null): string
    {
        $marcacion ??= $this->normalizarMarcacionAsistencia($registro);
        $estado = $this->normalizarTextoPerfil((string) ($registro->estado_marcacion ?? ''));
        $evento = $this->normalizarTextoPerfil((string) ($registro->evento_biometrico ?? ''));

        if ($this->marcacionEsSalidaExplicita($estado, $evento) && !$this->marcacionEsEntradaExplicita($estado, $evento)) {
            return 'Salida';
        }

        if ($this->marcacionEsEntradaExplicita($estado, $evento) && !$this->marcacionEsSalidaExplicita($estado, $evento)) {
            return 'Entrada';
        }

        if ($marcacion['solo_entrada'] ?? false) {
            return 'Entrada';
        }

        return $registro->estado_marcacion ?: 'Sin estado';
    }

    private function calcularMinutosRetraso(?string $horaEntrada, ?string $horaProgramada): int
    {
        if (blank($horaEntrada) || blank($horaProgramada)) {
            return 0;
        }

        $entrada = $this->parseTimeToCarbon($horaEntrada);
        $programada = $this->parseTimeToCarbon($horaProgramada);

        if (!$entrada || !$programada) {
            return 0;
        }

        if ($entrada->lessThanOrEqualTo($programada)) {
            return 0;
        }

        return $programada->diffInMinutes($entrada);
    }

    private function parseTimeToCarbon(string $time): ?Carbon
    {
        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $time);
            } catch (\Exception $e) {
                // seguir intentando con otros formatos
            }
        }

        return null;
    }

    private function combinarFechaYHora(Carbon|string|null $fecha, ?string $hora): ?Carbon
    {
        if (blank($fecha) || blank($hora)) {
            return null;
        }

        $horaCarbon = $this->parseTimeToCarbon($hora);

        if (!$horaCarbon) {
            return null;
        }

        try {
            return Carbon::parse($fecha instanceof Carbon ? $fecha->toDateString() : $fecha)
                ->setTime($horaCarbon->hour, $horaCarbon->minute, $horaCarbon->second);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function formatearMinutos(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $restantes = $minutos % 60;

        return sprintf('%02d:%02d', $horas, $restantes);
    }

    private function formatearMinutosEtiqueta(int $minutos): string
    {
        if ($minutos <= 0) {
            return '0 min';
        }

        $horas = intdiv($minutos, 60);
        $restantes = $minutos % 60;

        if ($horas === 0) {
            return $restantes . ' min';
        }

        if ($restantes === 0) {
            return $horas . ' h';
        }

        return $horas . ' h ' . $restantes . ' min';
    }

    private function normalizarArea(?string $area): string
    {
        $texto = trim((string) $area);
        $normalizado = $this->normalizarTextoPerfil($texto);

        if ($normalizado === 'sin area asignada') {
            return '';
        }

        return $texto;
    }

    private function normalizarTextoPerfil(?string $texto): string
    {
        return Str::of((string) $texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function esSabado(Carbon|string|null $fecha): bool
    {
        if ($fecha instanceof Carbon) {
            return $fecha->isSaturday();
        }

        if (blank($fecha)) {
            return false;
        }

        return Carbon::parse((string) $fecha)->isSaturday();
    }

    private function excludeSaturdayRecords(): \Closure
    {
        return function (Builder $query) {
            $driver = $query->getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                $query->whereRaw('EXTRACT(DOW FROM fecha) <> 6');

                return;
            }

            if ($driver === 'sqlite') {
                $query->whereRaw("strftime('%w', fecha) <> '6'");

                return;
            }

            $query->whereRaw('DAYOFWEEK(fecha) <> 7');
        };
    }

    private function caseInsensitiveLikeOperator(): string
    {
        return Empleado::query()->getConnection()->getDriverName() === 'pgsql'
            ? 'ilike'
            : 'like';
    }

    private function programacionLaboral(): ProgramacionLaboralService
    {
        return app(ProgramacionLaboralService::class);
    }

    private function referenceMonth(): Carbon
    {
        $referenceDate = RegistroAsistencia::query()->max('fecha');

        $dt = $referenceDate ? Carbon::parse($referenceDate) : now();

        // No permitir que la referencia sea una fecha futura. Limitar a "ahora".
        if ($dt->greaterThan(Carbon::now())) {
            $dt = Carbon::now();
        }

        return $dt;
    }

    private function registrosPorNombre(string $monthStart, string $monthEnd): \Illuminate\Support\Collection
    {
        return RegistroAsistencia::query()
            ->with('empleado')
            ->whereHas('empleado')
            ->where($this->excludeSaturdayRecords())
            ->whereBetween('fecha', [$monthStart, $monthEnd])
            ->orderByDesc('fecha')
            ->get()
            ->filter(fn(RegistroAsistencia $registro) => filled($registro->empleado?->nombre_completo))
            ->groupBy(fn(RegistroAsistencia $registro) => $this->normalizarTextoPerfil($registro->empleado?->nombre_completo));
    }

    private function registrosUnificadosEmpleado(Empleado $empleado, \Illuminate\Support\Collection $registrosPorNombre): \Illuminate\Support\Collection
    {
        $nombrePerfil = $this->normalizarTextoPerfil($empleado->nombre_completo);
        $registrosPorRelacion = $empleado->asistencias instanceof EloquentCollection
            ? $empleado->asistencias
            : new EloquentCollection($empleado->asistencias?->all() ?? []);
        $registrosPorNombrePerfil = $registrosPorNombre->get($nombrePerfil, collect());

        return $registrosPorRelacion
            ->concat($registrosPorNombrePerfil)
            ->unique(fn(RegistroAsistencia $registro) => $registro->id ?: md5(
                ($registro->fecha?->toDateString() ?? '') .
                '|' . ($registro->hora_entrada ?? '') .
                '|' . ($registro->hora_salida ?? '')
            ))
            ->sortByDesc(fn(RegistroAsistencia $registro) => optional($registro->fecha)->timestamp ?? 0)
            ->values();
    }

    private function snapshotEmpleado(Empleado $empleado): array
    {
        return [
            'id' => $empleado->id,
            'nombre' => $empleado->nombre,
            'apellido' => $empleado->apellido,
            'codigo_biometrico' => $empleado->codigo_biometrico,
            'email' => $empleado->email,
            'area' => $empleado->area,
            'sucursal' => $empleado->sucursal,
            'fecha_nacimiento' => $empleado->fecha_nacimiento?->toDateString(),
            'fecha_contratacion' => $empleado->fecha_contratacion?->toDateString(),
            'fecha_despido' => $empleado->fecha_despido?->toDateString(),
        ];
    }

    private function employeeMatchesVista(Empleado $empleado): bool
    {
        return match ($this->vista) {
            'inactivos' => $empleado->estado_laboral === 'Inactivo',
            'personal', 'control' => $empleado->estado_laboral === 'Activo',
            default => true,
        };
    }

    private function paginarColeccion(\Illuminate\Support\Collection $items, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->paginarColeccionConNombre($items, $perPage, 'page');
    }

    private function paginarColeccionConNombre(\Illuminate\Support\Collection $items, int $perPage, string $pageName = 'page'): \Illuminate\Pagination\LengthAwarePaginator
    {
        $page = $this->getPage($pageName);
        $total = $items->count();
        $results = $items->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }

    private function pageTitle(): string
    {
        return match ($this->vista) {
            'inactivos' => 'Personal inactivo',
            'marcaciones' => 'Marcaciones del personal',
            'control' => 'Marcaciones por sucursales',
            'sucursales' => 'Registro mensual de sucursales',
            default => 'Registro de personal',
        };
    }
    private function calcularEstadisticasMarcacionesEmpleado(int $empleadoId, Carbon $start, Carbon $end): array
    {
        $empleado = Empleado::query()->find($empleadoId);
        if (!$empleado) {
            return [];
        }

        $effectiveEnd = $end->copy();
        if ($effectiveEnd->isFuture()) {
            $effectiveEnd = now()->endOfDay();
        }

        $asistencias = RegistroAsistencia::query()
            ->where('empleado_id', $empleadoId)
            ->whereDate('fecha', '>=', $start->toDateString())
            ->whereDate('fecha', '<=', $effectiveEnd->toDateString())
            ->orderBy('fecha')
            ->get();

        $permisos = \App\Models\PermisoLaboral::query()
            ->where('empleado_id', $empleadoId)
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $effectiveEnd->toDateString())
            ->whereDate('fecha_fin', '>=', $start->toDateString())
            ->get();

        $asistenciaPorFecha = [];
        foreach ($asistencias as $reg) {
            $key = $reg->fecha?->toDateString();
            if ($key) {
                $asistenciaPorFecha[$key] = $reg;
            }
        }

        $listaAtrasos = [];
        $listaOmisiones = [];
        $listaFaltas = [];
        $desgloseGlobal = [];

        $minutosTrabajadosTotales = 0;
        $minutosRetrasoTotales = 0;
        $diasConMarcacion = 0;

        $current = $start->copy();
        $progService = $this->programacionLaboral();

        while ($current->lte($effectiveEnd)) {
            $dateStr = $current->toDateString();
            $horario = $progService->resolverHorario($empleado, $current);

            if ($horario['laborable']) {
                $tieneAsistencia = isset($asistenciaPorFecha[$dateStr]);
                $tienePermiso = $permisos->contains(function ($p) use ($current) {
                    return $current->betweenIncluded($p->fecha_inicio, $p->fecha_fin);
                });

                $diaNombre = ucfirst($current->locale('es')->isoFormat('dddd'));
                $fechaFmt = $current->format('d/m/Y');

                if ($tieneAsistencia) {
                    $reg = $asistenciaPorFecha[$dateStr];
                    $norm = $this->normalizarMarcacionAsistencia($reg);
                    $entrada = filled($norm['entrada']) ? substr($norm['entrada'], 0, 5) : '--:--';
                    $salida = filled($norm['salida']) ? substr($norm['salida'], 0, 5) : '--:--';
                    $minutosRetraso = 0;

                    if ($entrada !== '--:--') {
                        $horaLimite = $horario['hora_entrada_tolerancia'] ?? $horario['hora_entrada'];
                        $minutosRetraso = $this->calcularMinutosRetraso($norm['entrada'], $horaLimite);
                    }

                    $minutosDiaTrabajados = 0;
                    if ($entrada !== '--:--' && $salida !== '--:--') {
                        $minutosDiaTrabajados = $this->calcularMinutosTrabajados($norm['entrada'], $norm['salida']);
                    }

                    $minutosTrabajadosTotales += $minutosDiaTrabajados;
                    $minutosRetrasoTotales += $minutosRetraso;
                    $diasConMarcacion++;

                    // Atrasos
                    if ($minutosRetraso > 0) {
                        $listaAtrasos[] = [
                            'fecha' => $fechaFmt,
                            'dia' => $diaNombre,
                            'entrada' => $entrada,
                            'salida' => $salida,
                            'minutos_retraso' => $minutosRetraso,
                            'retraso_formateado' => $this->formatearMinutosEtiqueta($minutosRetraso),
                            'hora_programada' => substr((string)$horario['hora_entrada'], 0, 5),
                        ];
                    }

                    // Omisiones
                    $olvidoEntrada = ($entrada === '--:--');
                    $olvidoSalida = ($salida === '--:--');
                    if ($olvidoEntrada || $olvidoSalida) {
                        $tipoOmision = ($olvidoEntrada && $olvidoSalida) ? 'Sin entrada ni salida' : ($olvidoEntrada ? 'Falta marcar entrada' : 'Falta marcar salida');
                        $listaOmisiones[] = [
                            'fecha' => $fechaFmt,
                            'dia' => $diaNombre,
                            'entrada' => $entrada,
                            'salida' => $salida,
                            'tipo_omision' => $tipoOmision,
                        ];
                    }

                    $desgloseGlobal[] = [
                        'fecha' => $fechaFmt,
                        'dia' => $diaNombre,
                        'entrada' => $entrada,
                        'salida' => $salida,
                        'horas_trabajadas' => sprintf('%dh %02dm', intdiv($minutosDiaTrabajados, 60), $minutosDiaTrabajados % 60),
                        'retraso' => $minutosRetraso > 0 ? $this->formatearMinutosEtiqueta($minutosRetraso) : 'Puntual',
                        'estado' => ($entrada !== '--:--' && $salida !== '--:--') ? 'Completo' : 'Sin completar',
                    ];
                } else {
                    // No marcó
                    if (!$tienePermiso && !$current->isFuture() && !$current->isToday()) {
                        $listaFaltas[] = [
                            'fecha' => $fechaFmt,
                            'dia' => $diaNombre,
                            'horario_esperado' => substr((string)$horario['hora_entrada'], 0, 5) . ' - ' . substr((string)$horario['hora_salida'], 0, 5),
                            'estado' => 'Falta no justificada',
                        ];

                        $desgloseGlobal[] = [
                            'fecha' => $fechaFmt,
                            'dia' => $diaNombre,
                            'entrada' => '--:--',
                            'salida' => '--:--',
                            'horas_trabajadas' => '0h 00m',
                            'retraso' => '—',
                            'estado' => 'Falta',
                        ];
                    } elseif ($tienePermiso) {
                        $desgloseGlobal[] = [
                            'fecha' => $fechaFmt,
                            'dia' => $diaNombre,
                            'entrada' => '--:--',
                            'salida' => '--:--',
                            'horas_trabajadas' => 'Permiso',
                            'retraso' => '—',
                            'estado' => 'Permiso justificado',
                        ];
                    }
                }
            }

            $current->addDay();
        }

        $toleranciaMesMinutos = $this->programacionLaboral()->resolverToleranciaMensual($empleado->sucursal);
        $excedido = $minutosRetrasoTotales > $toleranciaMesMinutos;

        $sancionAtraso = app(ReglamentoSancionService::class)->evaluarAtraso($minutosRetrasoTotales, 1, $start);
        $reglaSinFiltro = app(ReglamentoSancionService::class)->evaluarAtrasoSinFiltroFecha($minutosRetrasoTotales);
        $enPeriodoTransicion = (! $sancionAtraso && $reglaSinFiltro && $minutosRetrasoTotales > $toleranciaMesMinutos);

        $porcentajeTolerancia = $toleranciaMesMinutos > 0
            ? (int) min(100, round(($minutosRetrasoTotales / $toleranciaMesMinutos) * 100))
            : 100;

        return [
            'total_atrasos' => count($listaAtrasos),
            'lista_atrasos' => $listaAtrasos,
            'total_omisiones' => count($listaOmisiones),
            'lista_omisiones' => $listaOmisiones,
            'total_faltas' => count($listaFaltas),
            'lista_faltas' => $listaFaltas,
            'desglose_global' => $desgloseGlobal,
            'horas_trabajadas_formateado' => sprintf('%dh %02dm', intdiv($minutosTrabajadosTotales, 60), $minutosTrabajadosTotales % 60),
            'minutos_atraso_totales' => $minutosRetrasoTotales,
            'retraso_acumulado_formateado' => $this->formatearMinutosEtiqueta($minutosRetrasoTotales),
            'dias_con_marcacion' => $diasConMarcacion,
            'tolerancia_mensual' => $toleranciaMesMinutos,
            'estado_tolerancia' => $excedido ? 'Excedido' : 'Dentro de tolerancia',
            'saldo_tolerancia' => $excedido
                ? 'Excedido por ' . ($minutosRetrasoTotales - $toleranciaMesMinutos) . ' min'
                : max(0, $toleranciaMesMinutos - $minutosRetrasoTotales) . ' min disponibles',
            'porcentaje_tolerancia' => $porcentajeTolerancia,
            'en_periodo_transicion' => $enPeriodoTransicion,
            'vigencia_reglamento_detalle' => $enPeriodoTransicion ? ($reglaSinFiltro->descripcion_vigencia ?? 'No aplica en este periodo') : null,
            'sancion_regla' => $sancionAtraso ? [
                'id' => $sancionAtraso->id,
                'dias_sancion' => (float) $sancionAtraso->dias_sancion,
                'sancion_texto' => $sancionAtraso->sancion_texto,
                'causal' => $sancionAtraso->causal,
                'es_destitucion' => (bool) $sancionAtraso->es_destitucion,
                'es_acumulativo_2' => ((float) $sancionAtraso->dias_sancion >= 2.0 || $sancionAtraso->es_destitucion),
            ] : null,
        ];
    }

    private function calcularKpisSucursal(\Illuminate\Support\Collection $empleadosHydrated): array
    {
        $total = $empleadosHydrated->count();
        if ($total === 0) {
            return [
                'total_empleados' => 0,
                'sin_atrasos' => 0,
                'porcentaje_sin_atrasos' => 100,
                'con_atrasos' => 0,
                'porcentaje_con_atrasos' => 0,
                'sin_omisiones' => 0,
                'porcentaje_sin_omisiones' => 100,
                'con_omisiones' => 0,
                'porcentaje_con_omisiones' => 0,
                'dentro_tolerancia' => 0,
                'porcentaje_dentro_tolerancia' => 100,
                'excedidos_tolerancia' => 0,
                'porcentaje_excedidos' => 0,
                'sancionados_acumulativo_2' => 0,
                'sancionados_con_descuento' => 0,
                'total_minutos_trabajados' => 0,
                'total_horas_trabajadas' => '0h 0m',
                'promedio_horas_empleado' => '0h 0m',
                'total_minutos_retraso' => 0,
                'total_retraso_formateado' => '0 min',
            ];
        }

        $sinAtrasos = $empleadosHydrated->filter(fn (Empleado $e) => ($e->resumen_asistencia['dias_tarde'] ?? 0) === 0 && ($e->resumen_asistencia['minutos_retraso'] ?? 0) === 0)->count();
        $conAtrasos = $total - $sinAtrasos;

        $sinOmisiones = $empleadosHydrated->filter(fn (Empleado $e) => ($e->resumen_asistencia['olvidos_marcacion'] ?? 0) === 0)->count();
        $conOmisiones = $total - $sinOmisiones;

        $excedidosTolerancia = $empleadosHydrated->filter(fn (Empleado $e) => ($e->resumen_asistencia['estado_retraso'] ?? '') === 'Excedido')->count();
        $dentroTolerancia = $total - $excedidosTolerancia;

        $sancionadosAcumulativo2 = $empleadosHydrated->filter(function (Empleado $e) {
            $sancion = $e->resumen_asistencia['sancion_regla'] ?? null;
            return $sancion && ($sancion['es_acumulativo_2'] ?? false);
        })->count();

        $sancionadosConDescuento = $empleadosHydrated->filter(function (Empleado $e) {
            $sancion = $e->resumen_asistencia['sancion_regla'] ?? null;
            return $sancion && (($sancion['dias_sancion'] ?? 0) > 0 || ($sancion['es_destitucion'] ?? false));
        })->count();

        $totalMinutosTrabajados = $empleadosHydrated->sum(fn (Empleado $e) => $e->resumen_asistencia['minutos_mes'] ?? 0);
        $totalMinutosRetraso = $empleadosHydrated->sum(fn (Empleado $e) => $e->resumen_asistencia['retraso_mes'] ?? 0);
        $promedioMinutos = $total > 0 ? (int) round($totalMinutosTrabajados / $total) : 0;

        return [
            'total_empleados' => $total,
            'sin_atrasos' => $sinAtrasos,
            'porcentaje_sin_atrasos' => round(($sinAtrasos / $total) * 100, 1),
            'con_atrasos' => $conAtrasos,
            'porcentaje_con_atrasos' => round(($conAtrasos / $total) * 100, 1),
            'sin_omisiones' => $sinOmisiones,
            'porcentaje_sin_omisiones' => round(($sinOmisiones / $total) * 100, 1),
            'con_omisiones' => $conOmisiones,
            'porcentaje_con_omisiones' => round(($conOmisiones / $total) * 100, 1),
            'dentro_tolerancia' => $dentroTolerancia,
            'porcentaje_dentro_tolerancia' => round(($dentroTolerancia / $total) * 100, 1),
            'excedidos_tolerancia' => $excedidosTolerancia,
            'porcentaje_excedidos' => round(($excedidosTolerancia / $total) * 100, 1),
            'sancionados_acumulativo_2' => $sancionadosAcumulativo2,
            'sancionados_con_descuento' => $sancionadosConDescuento,
            'total_minutos_trabajados' => $totalMinutosTrabajados,
            'total_horas_trabajadas' => $this->formatearMinutos((int) $totalMinutosTrabajados),
            'promedio_horas_empleado' => sprintf('%dh %02dm', intdiv($promedioMinutos, 60), $promedioMinutos % 60),
            'total_minutos_retraso' => $totalMinutosRetraso,
            'total_retraso_formateado' => $this->formatearMinutosEtiqueta((int) $totalMinutosRetraso),
        ];
    }

    private function snapshotRegistro(RegistroAsistencia $registro): array
    {
        return [
            'id' => $registro->id,
            'empleado_id' => $registro->empleado_id,
            'empleado' => $registro->empleado?->nombre_completo,
            'importacion_id' => $registro->importacion_id,
            'fecha' => $registro->fecha?->toDateString(),
            'hora_entrada' => $registro->hora_entrada,
            'hora_salida' => $registro->hora_salida,
            'tipo_verificacion' => $registro->tipo_verificacion,
            'estado_marcacion' => $registro->estado_marcacion,
            'evento_biometrico' => $registro->evento_biometrico,
            'observacion' => $registro->observacion,
        ];
    }
}
