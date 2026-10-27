<?php

namespace App\Livewire;

use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\FechaEspecialLaboral;
use App\Models\HorarioRegional;
use App\Models\PermisoLaboral;
use App\Models\User as UserModel;
use App\Services\AuditoriaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AuditoriaPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $moduloFiltro = '';
    public string $accionFiltro = '';
    public string $actorFiltro = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('ver auditoria'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModuloFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingAccionFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingActorFiltro(): void
    {
        $this->resetPage();
    }

    public function deshacerAccion(int $auditoriaId): void
    {
        abort_unless(auth()->user()?->can('ver auditoria') && auth()->user()?->can('revertir auditoria'), 403);

        DB::transaction(function () use ($auditoriaId) {
            $auditoria = Auditoria::query()->lockForUpdate()->findOrFail($auditoriaId);

            if (! $this->canUndo($auditoria)) {
                session()->flash('status', 'Esta accion no se puede deshacer.');

                return;
            }

            $modelo = match ($auditoria->accion) {
                'crear' => $this->eliminarSegunAuditoria($auditoria),
                'editar', 'cambiar_rol' => $this->restaurarSnapshot($auditoria, $auditoria->antes ?? []),
                'eliminar' => $this->restaurarSnapshot($auditoria, $auditoria->antes ?? []),
                default => null,
            };

            app(AuditoriaService::class)->registrar(
                'Auditoria',
                'deshacer',
                'Se deshizo una accion registrada en auditoria.',
                $modelo,
                null,
                null,
                [
                    'auditoria_origen_id' => $auditoria->id,
                    'modulo_origen' => $auditoria->modulo,
                    'accion_origen' => $auditoria->accion,
                ]
            );

            $meta = $auditoria->meta ?? [];
            $meta['estado_reversion'] = 'deshecha';
            $meta['deshecha_en'] = now()->format('Y-m-d H:i:s');
            $meta['deshecha_por'] = auth()->id();
            $auditoria->meta = $meta;
            $auditoria->save();
            session()->flash('status', 'Accion deshecha correctamente.');
        });
    }

    public function rehacerAccion(int $auditoriaId): void
    {
        abort_unless(auth()->user()?->can('ver auditoria') && auth()->user()?->can('revertir auditoria'), 403);

        DB::transaction(function () use ($auditoriaId) {
            $auditoria = Auditoria::query()->lockForUpdate()->findOrFail($auditoriaId);

            if (! $this->canRedo($auditoria)) {
                session()->flash('status', 'Esta accion no se puede rehacer.');

                return;
            }

            $modelo = match ($auditoria->accion) {
                'crear' => $this->restaurarSnapshot($auditoria, $auditoria->despues ?? []),
                'editar', 'cambiar_rol' => $this->restaurarSnapshot($auditoria, $auditoria->despues ?? []),
                'eliminar' => $this->eliminarSegunAuditoria($auditoria),
                default => null,
            };

            app(AuditoriaService::class)->registrar(
                'Auditoria',
                'rehacer',
                'Se rehizo una accion registrada en auditoria.',
                $modelo,
                null,
                null,
                [
                    'auditoria_origen_id' => $auditoria->id,
                    'modulo_origen' => $auditoria->modulo,
                    'accion_origen' => $auditoria->accion,
                ]
            );

            $meta = $auditoria->meta ?? [];
            $meta['estado_reversion'] = 'activa';
            $meta['rehacer_en'] = now()->format('Y-m-d H:i:s');
            $meta['rehacer_por'] = auth()->id();
            $auditoria->meta = $meta;
            $auditoria->save();
            session()->flash('status', 'Accion rehecha correctamente.');
        });
    }

    public function render()
    {
        $auditorias = Auditoria::query()
            ->with('actor')
            ->when(filled($this->search), function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('descripcion', 'like', $term)
                        ->orWhere('modulo', 'like', $term)
                        ->orWhere('accion', 'like', $term)
                        ->orWhere('actor_nombre', 'like', $term)
                        ->orWhere('actor_email', 'like', $term)
                        ->orWhereHas('actor', function ($actorQuery) use ($term) {
                            $actorQuery->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when(filled($this->moduloFiltro), fn ($query) => $query->where('modulo', $this->moduloFiltro))
            ->when(filled($this->accionFiltro), fn ($query) => $query->where('accion', $this->accionFiltro))
            ->when(filled($this->actorFiltro), fn ($query) => $query->where('actor_id_original', $this->actorFiltro))
            ->latest()
            ->paginate(12);

        return view('livewire.auditoria', [
            'auditorias' => $auditorias,
            'modulos' => Auditoria::query()->select('modulo')->distinct()->orderBy('modulo')->pluck('modulo'),
            'acciones' => Auditoria::query()->select('accion')->distinct()->orderBy('accion')->pluck('accion'),
            'actores' => Auditoria::query()->whereNotNull('actor_id_original')->latest('id')
                ->get(['actor_id_original as id', 'actor_nombre as name', 'actor_email as email'])
                ->unique('id')->sortBy('name'),
        ])->layout('layouts.app', ['title' => 'Auditoria del sistema']);
    }

    public function canUndo(Auditoria $auditoria): bool
    {
        if (! auth()->user()?->can('revertir auditoria')) {
            return false;
        }
        if ($auditoria->auditable_type === PermisoLaboral::class && ! auth()->user()?->hasRole('administrador')) {
            return false;
        }

        if (in_array($auditoria->accion, ['deshacer', 'rehacer'], true)) {
            return false;
        }

        if (($auditoria->meta['estado_reversion'] ?? 'activa') === 'deshecha') {
            return false;
        }

        return $this->esAuditoriaReversible($auditoria);
    }

    public function canRedo(Auditoria $auditoria): bool
    {
        if (! auth()->user()?->can('revertir auditoria')) {
            return false;
        }
        if ($auditoria->auditable_type === PermisoLaboral::class && ! auth()->user()?->hasRole('administrador')) {
            return false;
        }

        if (in_array($auditoria->accion, ['deshacer', 'rehacer'], true)) {
            return false;
        }

        if (($auditoria->meta['estado_reversion'] ?? null) !== 'deshecha') {
            return false;
        }

        return $this->esAuditoriaReversible($auditoria);
    }

    private function esAuditoriaReversible(Auditoria $auditoria): bool
    {
        $class = $auditoria->auditable_type;

        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return false;
        }

        if ($class === UserModel::class && in_array($auditoria->accion, ['crear', 'eliminar'], true)) {
            return false;
        }

        return match ($auditoria->accion) {
            'crear' => filled($auditoria->despues),
            'editar', 'cambiar_rol' => filled($auditoria->antes) && filled($auditoria->despues),
            'eliminar' => filled($auditoria->antes),
            default => false,
        };
    }

    private function restaurarSnapshot(Auditoria $auditoria, array $snapshot): ?Model
    {
        $class = $auditoria->auditable_type;

        if (! is_string($class) || ! class_exists($class)) {
            return null;
        }

        $model = $this->resolverModeloParaEscritura($class, $snapshot['id'] ?? null);
        $respaldo = null;
        if ($class === PermisoLaboral::class && ! $model) {
            $respaldo = DB::table('boletas_eliminadas')
                ->where('permiso_laboral_id', $snapshot['id'] ?? $auditoria->auditable_id)
                ->latest('id')->first();
        }
        $payload = $this->payloadDesdeSnapshot($class, $snapshot);

        if ($payload === []) {
            return null;
        }

        if (! $model) {
            $model = new $class();
            if (isset($snapshot['id'])) {
                $model->setAttribute($model->getKeyName(), $snapshot['id']);
            }
        }

        $model->fill($payload);
        if ($respaldo) {
            $model->forceFill(json_decode($respaldo->datos_boleta, true, 512, JSON_THROW_ON_ERROR));
        }
        $model->save();

        if ($respaldo) {
            foreach (json_decode($respaldo->comprobantes, true, 512, JSON_THROW_ON_ERROR) as $datos) {
                unset($datos['id']);
                $datos['permiso_laboral_id'] = $model->id;
                // La cuenta que adjuntó el comprobante puede haber sido eliminada.
                if (! UserModel::query()->whereKey($datos['created_by'] ?? null)->exists()) {
                    $datos['created_by'] = null;
                }
                $model->comprobantes()->make()->forceFill($datos)->save();
            }
        }

        if ($this->usaSoftDeletes($class) && method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }

        if ($model instanceof UserModel && ! empty($snapshot['rol'])) {
            $model->syncRoles([$snapshot['rol']]);
        }

        return $model->fresh();
    }

    private function eliminarSegunAuditoria(Auditoria $auditoria): ?Model
    {
        $class = $auditoria->auditable_type;
        $model = $this->resolverModeloParaEscritura($class, $auditoria->auditable_id);

        if (! $model) {
            return null;
        }

        if ($model instanceof UserModel && (int) $model->id === (int) auth()->id()) {
            return null;
        }

        if ($model instanceof PermisoLaboral) {
            app(\App\Services\BoletaEliminacionService::class)->eliminar($model);
        } else {
            $model->delete();
        }

        return $model;
    }

    private function resolverModeloParaEscritura(?string $class, mixed $id): ?Model
    {
        if (! is_string($class) || ! class_exists($class) || blank($id)) {
            return null;
        }

        $query = $class::query();

        if ($this->usaSoftDeletes($class)) {
            $query = $query->withTrashed();
        }

        return $query->lockForUpdate()->find($id);
    }

    private function usaSoftDeletes(string $class): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($class), true);
    }

    private function payloadDesdeSnapshot(string $class, array $snapshot): array
    {
        return match ($class) {
            Empleado::class => array_filter([
                'nombre' => $snapshot['nombre'] ?? null,
                'apellido' => $snapshot['apellido'] ?? null,
                'codigo_biometrico' => $snapshot['codigo_biometrico'] ?? null,
                'area' => $snapshot['area'] ?? null,
                'sucursal' => $snapshot['sucursal'] ?? null,
                'fecha_nacimiento' => $snapshot['fecha_nacimiento'] ?? null,
                'fecha_contratacion' => $snapshot['fecha_contratacion'] ?? null,
                'fecha_despido' => $snapshot['fecha_despido'] ?? null,
            ], fn ($value) => $value !== null),
            FechaEspecialLaboral::class => array_filter([
                'fecha' => $snapshot['fecha'] ?? null,
                'sucursal' => $snapshot['sucursal'] ?? null,
                'nombre' => $snapshot['nombre'] ?? null,
                'descripcion' => $snapshot['descripcion'] ?? null,
                'tipo' => $snapshot['tipo'] ?? null,
                'hora_entrada' => $snapshot['hora_entrada'] ?? null,
                'hora_salida' => $snapshot['hora_salida'] ?? null,
            ], fn ($value) => $value !== null),
            HorarioRegional::class => array_filter([
                'sucursal' => $snapshot['sucursal'] ?? null,
                'hora_entrada' => $snapshot['hora_entrada'] ?? null,
                'hora_salida' => $snapshot['hora_salida'] ?? null,
            ], fn ($value) => $value !== null),
            PermisoLaboral::class => array_filter([
                'empleado_id' => $snapshot['empleado_id'] ?? null,
                'tipo' => $snapshot['tipo'] ?? null,
                'alcance' => $snapshot['alcance'] ?? null,
                'estado' => $snapshot['estado'] ?? null,
                'fecha_inicio' => $snapshot['fecha_inicio'] ?? null,
                'fecha_fin' => $snapshot['fecha_fin'] ?? null,
                'hora_inicio' => $snapshot['hora_inicio'] ?? null,
                'hora_fin' => $snapshot['hora_fin'] ?? null,
                'minutos_contabilizados' => $snapshot['minutos_contabilizados'] ?? null,
                'motivo' => $snapshot['motivo'] ?? null,
            ], fn ($value) => $value !== null),
            UserModel::class => array_filter([
                'name' => $snapshot['name'] ?? null,
                'email' => $snapshot['email'] ?? null,
            ], fn ($value) => $value !== null),
            default => [],
        };
    }
}
