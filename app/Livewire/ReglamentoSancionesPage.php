<?php

namespace App\Livewire;

use App\Models\ReglaSancion;
use App\Services\AuditoriaService;
use App\Services\ReglamentoSancionService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Reglamento de Sanciones'])]
class ReglamentoSancionesPage extends Component
{
    public string $reglaCategoriaFiltro = 'todas';

    // Simulador interactivo de sanción con periodo (gestión y mes)
    public ?int $simuladorMinutos = null;
    public int $simuladorVecesGestion = 1;
    public int $simuladorGestion = 2026;
    public int $simuladorMes = 6;
    public ?array $simuladorResultado = null;

    // Modal para editar regla existente
    public bool $showEditReglaModal = false;
    public ?int $editingReglaId = null;
    public string $reglaCategoria = 'atraso';
    public string $reglaCausal = '';
    public ?int $reglaRangoMin = null;
    public ?int $reglaRangoMax = null;
    public $reglaDiasSancion = 0.0;
    public string $reglaSancionTexto = '';
    public string $reglaUnidad = 'minutos';
    public bool $reglaEsDestitucion = false;
    public bool $reglaActivo = true;
    public string $reglaTipoVigencia = 'siempre';
    public ?int $reglaDesdeGestion = null;
    public ?int $reglaDesdeMes = null;
    public ?int $reglaHastaGestion = null;
    public ?int $reglaHastaMes = null;
    public string $reglaExplicacionVigencia = '';
    public string $reglaObservaciones = '';

    // Modal para agregar nueva regla
    public bool $showCreateReglaModal = false;
    public string $newReglaCategoria = 'atraso';
    public string $newReglaCausal = '';
    public ?int $newReglaRangoMin = null;
    public ?int $newReglaRangoMax = null;
    public $newReglaDiasSancion = 0.0;
    public string $newReglaSancionTexto = '';
    public string $newReglaUnidad = 'minutos';
    public bool $newReglaEsDestitucion = false;
    public bool $newReglaActivo = true;
    public string $newReglaTipoVigencia = 'siempre';
    public ?int $newReglaDesdeGestion = null;
    public ?int $newReglaDesdeMes = null;
    public ?int $newReglaHastaGestion = null;
    public ?int $newReglaHastaMes = null;
    public string $newReglaExplicacionVigencia = '';
    public string $newReglaObservaciones = '';

    // Modal de vigencia masiva por categoría
    public bool $showModalVigenciaMasiva = false;
    public string $masivaCategoria = 'atraso';
    public bool $masivaActivo = true;
    public string $masivaTipoVigencia = 'siempre';
    public ?int $masivaDesdeGestion = 2026;
    public ?int $masivaDesdeMes = null;
    public string $masivaExplicacion = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal'), 403);
        $this->simuladorGestion = (int) date('Y');
        $this->simuladorMes = (int) date('n');
    }

    public function updatedSimuladorMinutos(): void
    {
        $this->ejecutarSimulador();
    }

    public function updatedSimuladorVecesGestion(): void
    {
        $this->ejecutarSimulador();
    }

    public function updatedSimuladorGestion(): void
    {
        $this->ejecutarSimulador();
    }

    public function updatedSimuladorMes(): void
    {
        $this->ejecutarSimulador();
    }

    public function ejecutarSimulador(): void
    {
        if ($this->simuladorMinutos === null || $this->simuladorMinutos <= 0) {
            $this->simuladorResultado = null;
            return;
        }

        $fechaSimulada = \Carbon\Carbon::create($this->simuladorGestion, $this->simuladorMes, 15);
        $service = app(ReglamentoSancionService::class);
        $regla = $service->evaluarAtraso((int) $this->simuladorMinutos, (int) $this->simuladorVecesGestion, $fechaSimulada);

        $meses = ReglaSancion::nombresMeses();
        $mesNombre = $meses[$this->simuladorMes] ?? 'Mes ' . $this->simuladorMes;

        if (!$regla) {
            // Comprobar si hay una regla que coincide por minutos pero no aplica por vigencia/inactiva
            $reglaInactiva = ReglaSancion::query()
                ->atrasos()
                ->get()
                ->first(fn(ReglaSancion $r) => $r->aplicaParaValor((int) $this->simuladorMinutos));

            if ($reglaInactiva && $reglaInactiva->dias_sancion > 0) {
                $this->simuladorResultado = [
                    'minutos' => $this->simuladorMinutos,
                    'veces' => $this->simuladorVecesGestion,
                    'gestion' => $this->simuladorGestion,
                    'mes_nombre' => $mesNombre,
                    'sancion_texto' => 'No aplica sanción (0 días de descuento)',
                    'dias_sancion' => 0,
                    'es_destitucion' => false,
                    'regla_id' => $reglaInactiva->id,
                    'causal' => $reglaInactiva->causal,
                    'observaciones' => "El tramo '{$reglaInactiva->causal}' está configurado como: {$reglaInactiva->descripcion_vigencia}. Por tanto, en {$mesNombre} {$this->simuladorGestion} NO se aplica deducción económica.",
                    'en_espera' => true,
                ];
                return;
            }

            $this->simuladorResultado = [
                'minutos' => $this->simuladorMinutos,
                'veces' => $this->simuladorVecesGestion,
                'gestion' => $this->simuladorGestion,
                'mes_nombre' => $mesNombre,
                'sancion_texto' => 'Sin sanción',
                'dias_sancion' => 0,
                'es_destitucion' => false,
                'regla_id' => null,
                'causal' => '1 a 30 minutos (Dentro del margen sin sanción)',
                'observaciones' => 'No corresponde sanción económica según escala.',
                'en_espera' => false,
            ];
            return;
        }

        $this->simuladorResultado = [
            'minutos' => $this->simuladorMinutos,
            'veces' => $this->simuladorVecesGestion,
            'gestion' => $this->simuladorGestion,
            'mes_nombre' => $mesNombre,
            'sancion_texto' => $regla->sancion_texto,
            'dias_sancion' => (float) $regla->dias_sancion,
            'es_destitucion' => (bool) $regla->es_destitucion,
            'regla_id' => $regla->id,
            'causal' => $regla->causal,
            'observaciones' => $regla->observaciones ?: $regla->descripcion_vigencia,
            'en_espera' => false,
        ];
    }

    public function isCategoriaActiva(string $categoria): bool
    {
        return ReglaSancion::query()
            ->where('categoria', $categoria)
            ->where('activo', true)
            ->exists();
    }

    public function toggleCategoriaActiva(string $categoria): void
    {
        $estaActiva = $this->isCategoriaActiva($categoria);
        $nuevoEstado = ! $estaActiva;

        ReglaSancion::query()
            ->where('categoria', $categoria)
            ->update([
                'activo' => $nuevoEstado,
                'tipo_vigencia' => $nuevoEstado ? 'siempre' : 'no_aplica',
            ]);

        $nombres = [
            'atraso' => 'I. Atrasos en los Horarios de Ingreso',
            'inasistencia' => 'II. Inasistencia y Ausencia en el Puesto de Trabajo',
            'omision' => 'III. Omisión en el Registro de Asistencia',
            'gravisima' => 'Faltas Gravísimas por Asistencia o Registro',
        ];
        $nombreCat = $nombres[$categoria] ?? $categoria;
        $estadoTexto = $nuevoEstado ? 'activada (aplica sanción)' : 'desactivada (no aplica sanción / edición bloqueada)';

        app(AuditoriaService::class)->registrar(
            'Reglamento Sanciones',
            'toggle_categoria',
            "Se actualizó la sección '{$nombreCat}' a {$estadoTexto}.",
            null,
            null,
            ['categoria' => $categoria, 'activo' => $nuevoEstado]
        );

        session()->flash('status', "Normativa '{$nombreCat}' {$estadoTexto}.");
        $this->ejecutarSimulador();
    }

    public function openEditReglaModal(int $reglaId): void
    {
        $regla = ReglaSancion::query()->findOrFail($reglaId);

        if (! $this->isCategoriaActiva($regla->categoria)) {
            session()->flash('warning', "La sección '{$regla->categoria}' se encuentra desactivada. Activa el interruptor en la cabecera para poder editar sus reglas.");
            return;
        }

        $this->editingReglaId = $regla->id;
        $this->reglaCategoria = $regla->categoria;
        $this->reglaCausal = $regla->causal;
        $this->reglaRangoMin = $regla->rango_min;
        $this->reglaRangoMax = $regla->rango_max;
        $this->reglaDiasSancion = (float) $regla->dias_sancion;
        $this->reglaSancionTexto = $regla->sancion_texto;
        $this->reglaUnidad = $regla->unidad;
        $this->reglaEsDestitucion = (bool) $regla->es_destitucion;
        $this->reglaActivo = (bool) $regla->activo;
        $this->reglaTipoVigencia = $regla->tipo_vigencia ?? 'siempre';
        $this->reglaDesdeGestion = $regla->aplica_desde_gestion;
        $this->reglaDesdeMes = $regla->aplica_desde_mes;
        $this->reglaHastaGestion = $regla->aplica_hasta_gestion;
        $this->reglaHastaMes = $regla->aplica_hasta_mes;
        $this->reglaExplicacionVigencia = $regla->explicacion_vigencia ?? '';
        $this->reglaObservaciones = $regla->observaciones ?? '';

        $this->resetValidation();
        $this->showEditReglaModal = true;
    }

    public function closeEditReglaModal(): void
    {
        $this->showEditReglaModal = false;
        $this->editingReglaId = null;
        $this->resetValidation();
    }

    public function saveRegla(): void
    {
        if (! $this->editingReglaId) {
            return;
        }

        $this->validate([
            'reglaCausal' => ['required', 'string', 'max:255'],
            'reglaRangoMin' => ['nullable', 'integer', 'min:0'],
            'reglaRangoMax' => ['nullable', 'integer', 'min:0'],
            'reglaDiasSancion' => ['required', 'numeric', 'min:0', 'max:30'],
            'reglaSancionTexto' => ['required', 'string', 'max:150'],
            'reglaTipoVigencia' => ['nullable', Rule::in(['siempre', 'desde_fecha', 'solo_gestion', 'no_aplica'])],
            'reglaDesdeGestion' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'reglaDesdeMes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'reglaHastaGestion' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'reglaHastaMes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'reglaExplicacionVigencia' => ['nullable', 'string', 'max:255'],
            'reglaObservaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'reglaCausal.required' => 'La causal es obligatoria.',
            'reglaDiasSancion.required' => 'Los días de sanción son obligatorios.',
            'reglaSancionTexto.required' => 'El texto oficial de la sanción es obligatorio.',
        ]);

        $regla = ReglaSancion::query()->findOrFail($this->editingReglaId);
        $antes = $regla->toArray();

        $regla->update([
            'causal' => trim($this->reglaCausal),
            'rango_min' => $this->reglaRangoMin !== null ? (int) $this->reglaRangoMin : null,
            'rango_max' => $this->reglaRangoMax !== null ? (int) $this->reglaRangoMax : null,
            'dias_sancion' => (float) $this->reglaDiasSancion,
            'sancion_texto' => trim($this->reglaSancionTexto),
            'es_destitucion' => (bool) $this->reglaEsDestitucion,
            'activo' => (bool) $this->reglaActivo,
            'tipo_vigencia' => $this->reglaTipoVigencia,
            'aplica_desde_gestion' => $this->reglaDesdeGestion ? (int) $this->reglaDesdeGestion : null,
            'aplica_desde_mes' => $this->reglaDesdeMes ? (int) $this->reglaDesdeMes : null,
            'aplica_hasta_gestion' => $this->reglaHastaGestion ? (int) $this->reglaHastaGestion : null,
            'aplica_hasta_mes' => $this->reglaHastaMes ? (int) $this->reglaHastaMes : null,
            'explicacion_vigencia' => trim($this->reglaExplicacionVigencia) ?: null,
            'observaciones' => trim($this->reglaObservaciones) ?: null,
        ]);

        app(AuditoriaService::class)->registrar(
            'Reglamento Sanciones',
            'editar',
            "Se actualizó la regla de sanción #{$regla->id} ({$regla->causal}) con vigencia: {$regla->descripcion_vigencia}.",
            $regla,
            $antes,
            $regla->fresh()->toArray()
        );

        $this->closeEditReglaModal();
        session()->flash('status', "Regla '{$regla->causal}' actualizada con éxito ({$regla->descripcion_vigencia}).");
        $this->ejecutarSimulador();
    }

    public function toggleReglaActivo(int $reglaId): void
    {
        $regla = ReglaSancion::query()->findOrFail($reglaId);
        $nuevoEstado = ! $regla->activo;
        $regla->update([
            'activo' => $nuevoEstado,
            'tipo_vigencia' => $nuevoEstado ? 'siempre' : 'no_aplica',
        ]);

        $estado = $nuevoEstado ? 'habilitada y en estado Aplica' : 'deshabilitada (No Aplica)';
        session()->flash('status', "Regla '{$regla->causal}' {$estado} correctamente.");
        $this->ejecutarSimulador();
    }

    public function openModalVigenciaMasiva(string $categoria = 'atraso'): void
    {
        $this->masivaCategoria = $categoria;
        $this->masivaActivo = true;
        $this->masivaTipoVigencia = 'desde_fecha';
        $this->masivaDesdeGestion = (int) date('Y');
        $this->masivaDesdeMes = (int) date('n');
        $this->masivaExplicacion = 'Reglamento entrando en vigencia progresiva';
        $this->showModalVigenciaMasiva = true;
    }

    public function closeModalVigenciaMasiva(): void
    {
        $this->showModalVigenciaMasiva = false;
    }

    public function guardarVigenciaMasiva(): void
    {
        $this->validate([
            'masivaCategoria' => ['required', 'string'],
            'masivaTipoVigencia' => ['required', Rule::in(['siempre', 'desde_fecha', 'solo_gestion', 'no_aplica'])],
            'masivaDesdeGestion' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'masivaDesdeMes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'masivaExplicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $service = app(ReglamentoSancionService::class);
        $afectadas = $service->aplicarVigenciaMasiva($this->masivaCategoria, [
            'activo' => $this->masivaActivo,
            'tipo_vigencia' => $this->masivaTipoVigencia,
            'aplica_desde_gestion' => $this->masivaDesdeGestion,
            'aplica_desde_mes' => $this->masivaDesdeMes,
            'explicacion_vigencia' => $this->masivaExplicacion,
        ]);

        app(AuditoriaService::class)->registrar(
            'Reglamento Sanciones',
            'vigencia_masiva',
            "Se actualizó la vigencia masiva de {$afectadas} reglas en categoría '{$this->masivaCategoria}'.",
            null,
            null,
            [
                'categoria' => $this->masivaCategoria,
                'afectadas' => $afectadas,
                'tipo_vigencia' => $this->masivaTipoVigencia,
                'desde_gestion' => $this->masivaDesdeGestion,
                'desde_mes' => $this->masivaDesdeMes,
            ]
        );

        $this->closeModalVigenciaMasiva();
        session()->flash('status', "Vigencia actualizada exitosamente para {$afectadas} reglas de la categoría.");
        $this->ejecutarSimulador();
    }

    public function openCreateReglaModal(): void
    {
        $this->newReglaCategoria = 'atraso';
        $this->newReglaCausal = '';
        $this->newReglaRangoMin = null;
        $this->newReglaRangoMax = null;
        $this->newReglaDiasSancion = 0.0;
        $this->newReglaSancionTexto = '';
        $this->newReglaUnidad = 'minutos';
        $this->newReglaEsDestitucion = false;
        $this->newReglaActivo = true;
        $this->newReglaTipoVigencia = 'siempre';
        $this->newReglaDesdeGestion = (int) date('Y');
        $this->newReglaDesdeMes = null;
        $this->newReglaHastaGestion = null;
        $this->newReglaHastaMes = null;
        $this->newReglaExplicacionVigencia = '';
        $this->newReglaObservaciones = '';

        $this->resetValidation();
        $this->showCreateReglaModal = true;
    }

    public function closeCreateReglaModal(): void
    {
        $this->showCreateReglaModal = false;
        $this->resetValidation();
    }

    public function saveNewRegla(): void
    {
        $this->validate([
            'newReglaCategoria' => ['required', Rule::in(['atraso', 'inasistencia', 'omision', 'gravisima'])],
            'newReglaCausal' => ['required', 'string', 'max:255'],
            'newReglaRangoMin' => ['nullable', 'integer', 'min:0'],
            'newReglaRangoMax' => ['nullable', 'integer', 'min:0'],
            'newReglaDiasSancion' => ['required', 'numeric', 'min:0', 'max:30'],
            'newReglaSancionTexto' => ['required', 'string', 'max:150'],
            'newReglaTipoVigencia' => ['nullable', Rule::in(['siempre', 'desde_fecha', 'solo_gestion', 'no_aplica'])],
            'newReglaDesdeGestion' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'newReglaDesdeMes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'newReglaHastaGestion' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'newReglaHastaMes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'newReglaExplicacionVigencia' => ['nullable', 'string', 'max:255'],
            'newReglaObservaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $maxOrden = (int) ReglaSancion::query()
            ->where('categoria', $this->newReglaCategoria)
            ->max('orden');

        $regla = ReglaSancion::query()->create([
            'categoria' => $this->newReglaCategoria,
            'causal' => trim($this->newReglaCausal),
            'rango_min' => $this->newReglaRangoMin !== null ? (int) $this->newReglaRangoMin : null,
            'rango_max' => $this->newReglaRangoMax !== null ? (int) $this->newReglaRangoMax : null,
            'dias_sancion' => (float) $this->newReglaDiasSancion,
            'sancion_texto' => trim($this->newReglaSancionTexto),
            'unidad' => $this->newReglaUnidad,
            'es_destitucion' => (bool) $this->newReglaEsDestitucion,
            'orden' => $maxOrden + 1,
            'activo' => $this->newReglaActivo ?? $this->isCategoriaActiva($this->newReglaCategoria),
            'tipo_vigencia' => $this->newReglaTipoVigencia ?: 'siempre',
            'aplica_desde_gestion' => $this->newReglaDesdeGestion ? (int) $this->newReglaDesdeGestion : null,
            'aplica_desde_mes' => $this->newReglaDesdeMes ? (int) $this->newReglaDesdeMes : null,
            'aplica_hasta_gestion' => $this->newReglaHastaGestion ? (int) $this->newReglaHastaGestion : null,
            'aplica_hasta_mes' => $this->newReglaHastaMes ? (int) $this->newReglaHastaMes : null,
            'explicacion_vigencia' => trim($this->newReglaExplicacionVigencia) ?: null,
            'observaciones' => trim($this->newReglaObservaciones) ?: null,
        ]);

        app(AuditoriaService::class)->registrar(
            'Reglamento Sanciones',
            'crear',
            "Se creó una nueva regla de sanción ({$regla->causal}) con vigencia: {$regla->descripcion_vigencia}.",
            $regla,
            null,
            $regla->toArray()
        );

        $this->closeCreateReglaModal();
        session()->flash('status', "Nueva regla '{$regla->causal}' agregada exitosamente.");
    }

    public function restaurarReglasPorDefecto(): void
    {
        app(ReglamentoSancionService::class)->restaurarPorDefecto();

        app(AuditoriaService::class)->registrar(
            'Reglamento Sanciones',
            'restaurar',
            "Se restauraron las reglas oficiales del reglamento institucional.",
            null,
            null,
            ['accion' => 'restaurar_defecto']
        );

        session()->flash('status', "Se han restablecido todas las escalas originales del reglamento institucional.");
        $this->ejecutarSimulador();
    }

    public function render()
    {
        $reglasSancionesQuery = ReglaSancion::query()->orderBy('categoria')->orderBy('orden');
        if ($this->reglaCategoriaFiltro !== 'todas') {
            $reglasSancionesQuery->where('categoria', $this->reglaCategoriaFiltro);
        }
        $reglasSanciones = $reglasSancionesQuery->get();
        $reglasAgrupadas = [
            'atraso' => $reglasSanciones->where('categoria', 'atraso')->values(),
            'inasistencia' => $reglasSanciones->where('categoria', 'inasistencia')->values(),
            'omision' => $reglasSanciones->where('categoria', 'omision')->values(),
            'gravisima' => $reglasSanciones->where('categoria', 'gravisima')->values(),
        ];

        $seccionesActivas = [
            'atraso' => $reglasAgrupadas['atraso']->contains(fn ($r) => (bool) $r->activo),
            'inasistencia' => $reglasAgrupadas['inasistencia']->contains(fn ($r) => (bool) $r->activo),
            'omision' => $reglasAgrupadas['omision']->contains(fn ($r) => (bool) $r->activo),
            'gravisima' => $reglasAgrupadas['gravisima']->contains(fn ($r) => (bool) $r->activo),
        ];

        return view('livewire.reglamento-sanciones', [
            'reglasSanciones' => $reglasSanciones,
            'reglasAgrupadas' => $reglasAgrupadas,
            'seccionesActivas' => $seccionesActivas,
        ]);
    }
}
