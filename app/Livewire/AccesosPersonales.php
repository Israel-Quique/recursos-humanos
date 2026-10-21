<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\User;
use App\Services\AccesoPersonalService;
use App\Services\AuditoriaService;
use App\Support\SucursalNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AccesosPersonales extends Component
{
    use WithPagination;

    public string $sucursal = '';
    public string $busqueda = '';
    public string $filtroEstado = 'todos';
    public array $personas = [];

    public function boot(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos') && ! auth()->user()->acceso_personal, 403);
    }

    public function updatedBusqueda(): void { $this->resetPage(); }
    public function updatedSucursal(): void { $this->personas = []; $this->resetPage(); }
    public function updatedFiltroEstado(): void { $this->personas = []; $this->resetPage(); }

    public function toggleSucursal(string $sucursalNombre): void
    {
        $this->sucursal = $this->sucursal === $sucursalNombre ? '' : $sucursalNombre;
        $this->personas = [];
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->sucursal = '';
        $this->busqueda = '';
        $this->filtroEstado = 'todos';
        $this->personas = [];
        $this->resetPage();
    }

    public function seleccionarTodosPagina(array $ids): void
    {
        $idInts = array_map('intval', $ids);
        $diff = array_diff($idInts, $this->personas);
        if (empty($diff)) {
            $this->personas = array_values(array_diff($this->personas, $idInts));
        } else {
            $this->personas = array_values(array_unique(array_merge($this->personas, $idInts)));
        }
    }

    public function deseleccionarTodos(): void
    {
        $this->personas = [];
    }

    public function aplicarIndividual(int $empleadoId, string $accion): void
    {
        $this->personas = [$empleadoId];
        $this->aplicar('persona', $accion);
    }

    public function aplicarSucursalDirecto(string $sucursalNombre, string $accion): void
    {
        $this->sucursal = $sucursalNombre;
        $this->aplicar('sucursal', $accion);
    }

    public function aplicar(string $tipo, string $accion): void
    {
        validator(compact('tipo', 'accion'), [
            'tipo' => ['required', Rule::in(['todos', 'sucursal', 'persona'])],
            'accion' => ['required', Rule::in(['habilitar', 'bloquear', 'heredar'])],
        ])->validate();
        if ($tipo === 'sucursal') {
            $this->validate(['sucursal' => ['required', Rule::in($this->sucursales())]]);
        }
        if ($tipo === 'persona') {
            $this->validate(['personas' => ['required', 'array', 'min:1', 'max:500'], 'personas.*' => ['integer', Rule::exists('empleados', 'id')->whereNull('deleted_at')]]);
        }
        $referencias = match ($tipo) {
            'todos' => ['*'],
            'sucursal' => [$this->sucursal],
            'persona' => array_map('strval', $this->personas),
        };
        DB::transaction(function () use ($tipo, $accion, $referencias) {
            foreach ($referencias as $referencia) {
                if ($accion === 'heredar') {
                    DB::table('acceso_personal_reglas')->where(compact('tipo', 'referencia'))->delete();
                } else {
                    app(AccesoPersonalService::class)->guardar($tipo, $referencia, $accion === 'habilitar');
                }
            }
            app(AuditoriaService::class)->registrar('Accesos personales', $accion, 'Se actualizó el acceso a Mis horas.', null, null, compact('tipo', 'referencias'));
        });
        $this->personas = [];
        session()->flash('accesos-personales', 'Accesos actualizados. Los bloqueos se aplican también a sesiones abiertas.');
    }

    public function restablecer(int $empleadoId): void
    {
        DB::transaction(function () use ($empleadoId) {
            $empleado = Empleado::query()->lockForUpdate()->findOrFail($empleadoId);
            $user = User::query()->where('empleado_id', $empleado->id)->where('acceso_personal', true)->firstOrFail();
            $user->forceFill(['password' => (string) $empleado->codigo_biometrico, 'must_change_password' => true, 'remember_token' => Str::random(60)])->save();
            app(AuditoriaService::class)->registrar('Accesos personales', 'restablecer', 'Se restableció la contraseña inicial del acceso personal.', $user);
        });
        session()->flash('accesos-personales', 'Contraseña restablecida al código biométrico. Se exigirá cambiarla al ingresar.');
    }

    private function sucursales(): array
    {
        return SucursalNormalizer::optionsFromValues(Empleado::query()->distinct()->pluck('sucursal'));
    }

    public function render()
    {
        $reglas = DB::table('acceso_personal_reglas')->get();
        $accesoService = app(AccesoPersonalService::class);
        $sucursales = $this->sucursales();

        $reglaGeneral = $reglas->firstWhere('tipo', 'todos');
        $reglaGeneralHabilitada = (bool) ($reglaGeneral?->habilitado ?? false);

        $reglasSucursalMap = $reglas->where('tipo', 'sucursal')->keyBy('referencia');
        $reglasPersonaMap = $reglas->where('tipo', 'persona')->keyBy('referencia');

        $empleadosAgrupados = Empleado::query()->select('id', 'sucursal')->get();

        $regionalesData = collect($sucursales)->map(function ($sucursalNombre) use ($reglasSucursalMap, $reglaGeneralHabilitada, $empleadosAgrupados, $reglasPersonaMap) {
            $regla = $reglasSucursalMap->get($sucursalNombre);
            $empleadosEnSucursal = $empleadosAgrupados->filter(fn ($e) => SucursalNormalizer::matches($e->sucursal, $sucursalNombre));
            $total = $empleadosEnSucursal->count();
            $excepcionesCount = $empleadosEnSucursal->filter(fn ($e) => $reglasPersonaMap->has((string) $e->id))->count();

            return [
                'nombre' => $sucursalNombre,
                'tiene_regla' => $regla !== null,
                'habilitado' => $regla ? (bool) $regla->habilitado : $reglaGeneralHabilitada,
                'es_heredado' => $regla === null,
                'total_empleados' => $total,
                'total_excepciones' => $excepcionesCount,
            ];
        })->all();

        $totalEmpleados = $empleadosAgrupados->count();
        $totalExcepciones = $reglasPersonaMap->count();
        $totalReglasSucursal = $reglasSucursalMap->count();

        $query = Empleado::query()->with('user')->orderBy('nombre')->orderBy('apellido');
        SucursalNormalizer::applyFilter($query, 'sucursal', $this->sucursal);

        if (trim($this->busqueda) !== '') {
            $term = '%'.trim($this->busqueda).'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $term)->orWhere('apellido', 'like', $term)->orWhere('codigo_biometrico', 'like', $term));
        }

        if ($this->filtroEstado === 'excepciones') {
            $query->whereIn('id', $reglasPersonaMap->keys()->map(fn ($id) => (int) $id)->all());
        } elseif ($this->filtroEstado === 'habilitados' || $this->filtroEstado === 'bloqueados') {
            $esperado = $this->filtroEstado === 'habilitados';
            $reglasSucursalArr = $reglasSucursalMap->map(fn ($r) => (bool) $r->habilitado)->all();
            $reglasPersonaArr = $reglasPersonaMap->map(fn ($r) => (bool) $r->habilitado)->all();

            $matchingIds = $empleadosAgrupados->filter(function ($emp) use ($reglasPersonaArr, $reglasSucursalArr, $reglaGeneralHabilitada, $esperado) {
                $idStr = (string) $emp->id;
                if (isset($reglasPersonaArr[$idStr])) {
                    $hab = $reglasPersonaArr[$idStr];
                } else {
                    $canon = SucursalNormalizer::canonicalLabel($emp->sucursal);
                    if (isset($reglasSucursalArr[$canon])) {
                        $hab = $reglasSucursalArr[$canon];
                    } else {
                        $hab = $reglaGeneralHabilitada;
                    }
                }
                return $hab === $esperado;
            })->pluck('id')->all();

            $query->whereIn('id', $matchingIds);
        }

        return view('livewire.accesos-personales', [
            'empleadosPersonales' => $query->paginate(20),
            'sucursalesPersonales' => $sucursales,
            'reglasPersonales' => $reglas,
            'accesoService' => $accesoService,
            'regionalesData' => $regionalesData,
            'reglaGeneralHabilitada' => $reglaGeneralHabilitada,
            'metricas' => [
                'total_empleados' => $totalEmpleados,
                'total_excepciones' => $totalExcepciones,
                'total_reglas_sucursales' => $totalReglasSucursal,
            ],
        ]);
    }
}
