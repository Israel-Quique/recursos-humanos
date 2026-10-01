<?php

namespace App\Livewire;

use App\Models\PlanillaRefrigerio;
use App\Models\RegistroAsistencia;
use App\Models\TipoPermiso;
use App\Services\AnalisisAsistenciaService;
use App\Services\PlanillaRefrigerioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlanillaRefrigerioPage extends Component
{
    public string $referenceMonth = '';
    public string $selectedBranch = '';
    public $tarifaDiaria = 20.00;
    public string $search = '';
    public string $vistaFormato = 'consolidado'; // 'consolidado' | 'vertical'
    public array $items = [];
    public array $diasMes = [];
    public bool $isDirty = false;
    public ?string $ultimaGuardada = null;
    /** Indica si la planilla cargada es más antigua que los últimos datos biométricos */
    public bool $datosDesactualizados = false;

    // Modal de detalle de incidencias y fechas
    public bool $showDetailModal = false;
    public ?array $selectedDetail = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar personal') || auth()->user()?->can('ver reportes'), 403);

        $this->referenceMonth = now()->format('Y-m');
        $this->selectedBranch = request()->query('branch', '');
        $this->cargarPlanilla();
    }

    public function updatingReferenceMonth(): void
    {
        $this->isDirty = false;
    }

    public function updatedReferenceMonth(): void
    {
        $this->cargarPlanilla();
    }

    public function updatingSelectedBranch(): void
    {
        $this->isDirty = false;
    }

    public function updatedSelectedBranch(): void
    {
        $this->cargarPlanilla();
    }

    public function updatedTarifaDiaria(): void
    {
        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));
        foreach ($this->items as &$item) {
            $item['tarifa_diaria'] = $tarifa;
            $item['total_monto'] = round(($item['total_dias'] ?? 0) * $tarifa, 2);
        }
        unset($item);
        $this->isDirty = true;
    }

    public function setVistaFormato(string $formato): void
    {
        if (in_array($formato, ['consolidado', 'vertical'], true)) {
            $this->vistaFormato = $formato;
        }
    }

    /**
     * Carga la planilla: si existe guardada en BD se usa esa, sino se calcula fresca.
     * Si llegaron registros biométricos más recientes que la planilla guardada,
     * recalcula automáticamente para reflejar las faltas y omisiones actualizadas.
     */
    public function cargarPlanilla(): void
    {
        $this->datosDesactualizados = false;

        try {
            $ref = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        } catch (\Throwable) {
            $ref = now()->startOfMonth();
        }

        $service = app(PlanillaRefrigerioService::class);
        $this->diasMes = $service->obtenerDiasHabilesMes($ref);

        $registro = PlanillaRefrigerio::query()
            ->where('periodo', $this->referenceMonth)
            ->where('sucursal', $this->selectedBranch ?: null)
            ->first();

        if ($registro && !empty($registro->datos['items'] ?? [])) {
            $itemsLoaded = $registro->datos['items'];
            $primerItem = $itemsLoaded[0] ?? [];

            // Si la planilla guardada no tiene matriz de días o está vacía, calcular fresco
            if (empty($primerItem['dias'] ?? [])) {
                $this->jalarDatos(false);
                return;
            }

            // Verificar si han llegado registros biométricos más nuevos que la planilla guardada.
            // Si es así, recalcular automáticamente para reflejar las faltas actualizadas.
            $guardadoEn = $registro->updated_at;
            if ($guardadoEn) {
                try {
                    $start = $ref->copy()->startOfMonth();
                    $end   = $ref->copy()->endOfMonth();

                    $ultimoBiometrico = RegistroAsistencia::whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
                        ->where('created_at', '>', $guardadoEn)
                        ->exists();

                    if ($ultimoBiometrico) {
                        // Hay datos biométricos más recientes que la planilla guardada → recalcular
                        $this->jalarDatos(false);
                        return;
                    }
                } catch (\Throwable) {
                    // Si falla la consulta, usar datos guardados igual
                }
            }

            $this->tarifaDiaria = (float) $registro->tarifa_diaria;
            $this->items = $itemsLoaded;
            $this->ultimaGuardada = $guardadoEn?->format('d/m/Y H:i');
            $this->isDirty = false;
            return;
        }

        $this->jalarDatos(false);
    }

    /**
     * Extrae/recalcula automáticamente los datos desde asistencias, incidencias y permisos.
     */
    public function jalarDatos(bool $mostrarMensaje = true): void
    {
        try {
            $ref = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        } catch (\Throwable) {
            $ref = now()->startOfMonth();
        }

        $service = app(PlanillaRefrigerioService::class);
        $resultado = $service->calcularPlanilla($ref, $this->selectedBranch, $this->tarifaDiaria);

        $this->items = $resultado['items'];
        $this->diasMes = $resultado['dias_mes'] ?? $service->obtenerDiasHabilesMes($ref);
        $this->tarifaDiaria = (float) $resultado['tarifa_diaria'];
        $this->isDirty = true;

        if ($mostrarMensaje) {
            session()->flash('status', 'Datos extraídos correctamente de asistencias y permisos autorizados.');
        }
    }

    /**
     * Alterna en ciclo el estado de un día:
     * (blanco) → A → F → O → (tipos dinámicos desde Incidencias) → (blanco)
     */
    public function alternarEstadoDia(int $index, string $fecha): void
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $tiposEstados = $this->obtenerTiposEstados();
        $claves = array_keys($tiposEstados); // ['a', 'f', 'o', ...dinamicos]

        $actual = strtolower($this->items[$index]['dias'][$fecha] ?? '');

        if ($actual === '' || $actual === 'p') {
            // Desde celda en blanco: ir a 'a'
            $siguiente = 'a';
        } else {
            $posActual = array_search($actual, $claves);
            if ($posActual === false) {
                $siguiente = 'a';
            } elseif ($posActual >= count($claves) - 1) {
                // Al final del ciclo: volver a blanco
                $siguiente = '';
            } else {
                $siguiente = $claves[$posActual + 1];
            }
        }

        $this->actualizarEstadoDia($index, $fecha, $siguiente);
    }

    /**
     * Actualiza el estado de un día específico y recalcula totales en vivo.
     * '' = sin dato biométrico (no penaliza)
     * 'a' = asistencia (no penaliza)
     * 'f'/'o'/permisos = se penaliza como 1 día no pagado.
     */
    public function actualizarEstadoDia(int $index, string $fecha, string $nuevoEstado): void
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $tiposValidos = array_keys($this->obtenerTiposEstados());
        $nuevoEstado = strtolower(trim($nuevoEstado));
        // Permitir '' (blanco) como estado válido
        if ($nuevoEstado !== '' && !in_array($nuevoEstado, $tiposValidos, true) && !in_array($nuevoEstado, ['bm', 'cv'], true)) {
            if (!preg_match('/^[a-z0-9_]{1,15}$/', $nuevoEstado)) {
                $nuevoEstado = '';
            }
        }

        $this->items[$index]['dias'][$fecha] = $nuevoEstado;

        // Recalcular conteos y desgloses
        $faltas = 0;
        $omisiones = 0;
        $permisos = 0;
        $bajas = 0;
        $comisiones = 0;

        $fechasFaltas = [];
        $fechasOmisiones = [];
        $fechasPermisos = [];
        $fechasBajas = [];
        $fechasComisiones = [];

        foreach ($this->items[$index]['dias'] as $fKey => $st) {
            $st = strtolower($st);
            try {
                $fFormatted = Carbon::parse($fKey)->format('d/m/Y');
            } catch (\Throwable) {
                $fFormatted = $fKey;
            }

            if ($st === 'f') {
                $faltas++;
                $fechasFaltas[] = ['fecha' => $fFormatted, 'detalle' => 'Inasistencia injustificada'];
            } elseif ($st === 'o') {
                $omisiones++;
                $fechasOmisiones[] = ['fecha' => $fFormatted, 'detalle' => 'Omisión de marcado'];
            } elseif ($st !== 'a' && $st !== '' && $st !== 'p') {
                // Cualquier permiso/incidencia desde Incidencias y Permisos
                $permisos++;
                $label = match($st) {
                    'bm' => 'Baja médica autorizada',
                    'cv' => 'Comisión de viaje laboral',
                    default => 'Permiso: ' . strtoupper($st),
                };
                $fechasPermisos[] = ['fecha' => $fFormatted, 'dias' => 1, 'motivo' => $label, 'tipo' => $st];
                if ($st === 'bm') {
                    $bajas++;
                    $fechasBajas[] = ['fecha' => $fFormatted, 'dias' => 1, 'motivo' => 'Baja médica autorizada'];
                } elseif ($st === 'cv') {
                    $comisiones++;
                    $fechasComisiones[] = ['fecha' => $fFormatted, 'dias' => 1, 'motivo' => 'Comisión de viaje laboral'];
                }
            }
        }

        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));
        $totalDias = $faltas + $omisiones + $permisos;

        $this->items[$index]['faltas'] = $faltas;
        $this->items[$index]['omisiones'] = $omisiones;
        $this->items[$index]['permisos'] = $permisos;
        $this->items[$index]['bajas_medicas'] = $bajas;
        $this->items[$index]['comisiones_viaje'] = $comisiones;
        $this->items[$index]['total_dias'] = $totalDias;
        $this->items[$index]['total_monto'] = round($totalDias * $tarifa, 2);
        $this->items[$index]['fechas_faltas'] = $fechasFaltas;
        $this->items[$index]['fechas_omisiones'] = $fechasOmisiones;
        $this->items[$index]['fechas_permisos'] = $fechasPermisos;
        $this->items[$index]['fechas_bajas'] = $fechasBajas;
        $this->items[$index]['fechas_comisiones'] = $fechasComisiones;

        $this->isDirty = true;
    }

    /**
     * Actualiza el valor acumulado de un día (Faltas, Omisiones, Permisos) directamente.
     */
    public function actualizarDia(int $index, string $campo, $valor): void
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $val = max(0, (int) $valor);
        $this->items[$index][$campo] = $val;

        // Recalcular total días
        $faltas = (int) ($this->items[$index]['faltas'] ?? 0);
        $omisiones = (int) ($this->items[$index]['omisiones'] ?? 0);
        $permisos = isset($this->items[$index]['permisos'])
            ? (int) $this->items[$index]['permisos']
            : ((int) ($this->items[$index]['bajas_medicas'] ?? 0) + (int) ($this->items[$index]['comisiones_viaje'] ?? 0));

        if ($campo === 'permisos') {
            $this->items[$index]['permisos'] = $val;
            $permisos = $val;
        } elseif ($campo === 'bajas_medicas') {
            $this->items[$index]['bajas_medicas'] = $val;
            $permisos = $val + (int) ($this->items[$index]['comisiones_viaje'] ?? 0);
            $this->items[$index]['permisos'] = $permisos;
        } elseif ($campo === 'comisiones_viaje') {
            $this->items[$index]['comisiones_viaje'] = $val;
            $permisos = (int) ($this->items[$index]['bajas_medicas'] ?? 0) + $val;
            $this->items[$index]['permisos'] = $permisos;
        }

        $totalDias = $faltas + $omisiones + $permisos;
        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));
        $this->items[$index]['total_dias'] = $totalDias;
        $this->items[$index]['total_monto'] = round($totalDias * $tarifa, 2);
        $this->isDirty = true;
    }

    /**
     * Actualiza la observación de una fila.
     */
    public function actualizarObservacion(int $index, string $obs): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['observaciones'] = trim($obs);
            $this->isDirty = true;
        }
    }

    /**
     * Guarda la planilla en la base de datos para preservar los cambios manuales.
     */
    public function guardarPlanilla(): void
    {
        $datos = [
            'items' => $this->items,
            'dias_mes' => $this->diasMes,
            'metricas' => $this->calcularMetricas(),
        ];

        PlanillaRefrigerio::query()->updateOrCreate(
            [
                'periodo' => $this->referenceMonth,
                'sucursal' => $this->selectedBranch ?: null,
            ],
            [
                'tarifa_diaria' => max(0, (float) ($this->tarifaDiaria ?: 0)),
                'datos' => $datos,
                'updated_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]
        );

        $this->isDirty = false;
        $this->ultimaGuardada = now()->format('d/m/Y H:i');
        session()->flash('status', 'Planilla de refrigerio guardada exitosamente.');
    }

    /**
     * Descarga el archivo Excel con ambas hojas (Consolidada y Vertical).
     */
    public function descargarExcel()
    {
        $service = app(PlanillaRefrigerioService::class);
        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));
        $planillaData = [
            'periodo' => $this->referenceMonth,
            'periodo_label' => ucfirst(Carbon::createFromFormat('Y-m', $this->referenceMonth)->locale('es')->translatedFormat('F Y')),
            'sucursal' => $this->selectedBranch ?: 'Todas las sucursales',
            'tarifa_diaria' => $tarifa,
            'dias_mes' => $this->diasMes,
            'items' => $this->items,
        ];

        $spreadsheet = $service->generarExcel($planillaData);
        $fileName = 'Planilla_Refrigerio_' . Str::slug($this->selectedBranch ?: 'General') . '_' . $this->referenceMonth . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Descarga la planilla en PDF oficial.
     */
    public function descargarPdf()
    {
        $periodoLabel = ucfirst(Carbon::createFromFormat('Y-m', $this->referenceMonth)->locale('es')->translatedFormat('F Y'));
        $sucursalLabel = $this->selectedBranch ?: 'Todas las sucursales';
        $metricas = $this->calcularMetricas();
        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));

        $pdf = Pdf::loadView('pdf.planilla-refrigerio', [
            'items' => $this->items,
            'diasMes' => $this->diasMes,
            'periodoLabel' => $periodoLabel,
            'sucursalLabel' => $sucursalLabel,
            'tarifaDiaria' => $tarifa,
            'metricas' => $metricas,
            'emision' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $fileName = 'Planilla_Refrigerio_' . Str::slug($sucursalLabel) . '_' . $this->referenceMonth . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Abre modal con el desglose de fechas del empleado.
     */
    public function abrirDetalleFechas(int $index): void
    {
        if (isset($this->items[$index])) {
            $this->selectedDetail = $this->items[$index];
            $this->showDetailModal = true;
        }
    }

    public function cerrarDetalleFechas(): void
    {
        $this->showDetailModal = false;
        $this->selectedDetail = null;
    }

    /**
     * Calcula métricas resumidas en tiempo real.
     */
    private function calcularMetricas(): array
    {
        $itemsCol = collect($this->items);
        $totalDias = (int) $itemsCol->sum('total_dias');
        $tarifa = max(0, (float) ($this->tarifaDiaria ?: 0));

        return [
            'total_personal' => $itemsCol->count(),
            'personal_con_descuento' => $itemsCol->filter(fn($i) => ($i['total_dias'] ?? 0) > 0)->count(),
            'total_faltas' => (int) $itemsCol->sum('faltas'),
            'total_omisiones' => (int) $itemsCol->sum('omisiones'),
            'total_permisos' => (int) $itemsCol->sum(fn($i) => ($i['permisos'] ?? (($i['bajas_medicas'] ?? 0) + ($i['comisiones_viaje'] ?? 0)))),
            'total_bajas_medicas' => (int) $itemsCol->sum('bajas_medicas'),
            'total_comisiones_viaje' => (int) $itemsCol->sum('comisiones_viaje'),
            'gran_total_dias' => $totalDias,
            'gran_total_monto' => round($totalDias * $tarifa, 2),
        ];
    }

    /**
     * Devuelve el mapa clave=>label de todos los estados válidos,
     * incluyendo los tipos de permisos dinámicos registrados en el sistema.
     */
    public function obtenerTiposEstados(): array
    {
        // Únicamente los 3 estados base provenientes del biométrico diario
        $base = [
            'a'  => 'Asistencia',
            'f'  => 'Falta',
            'o'  => 'Omisión',
        ];

        // Todos los demás estados provienen de Incidencias y Permisos (TipoPermiso)
        try {
            $dinamicos = TipoPermiso::obtenerTodos();
            foreach ($dinamicos as $clave => $nombre) {
                // Solo agregar si la clave no colisiona con las del biométrico
                if (!isset($base[$clave])) {
                    $base[$clave] = $nombre;
                }
            }
        } catch (\Throwable) {
            // En caso de fallo de BD, usar solo los base
        }

        return $base;
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $branches = $analysis->sucursalesParaReportes();
        $tiposEstados = $this->obtenerTiposEstados();

        // Filtrado por búsqueda de personal
        $filteredItems = $this->items;
        if (filled($this->search)) {
            $term = Str::ascii(Str::lower(trim($this->search)));
            $filteredItems = array_values(array_filter($filteredItems, function ($i) use ($term) {
                $nom = Str::ascii(Str::lower($i['nombre'] ?? ''));
                $cod = Str::ascii(Str::lower((string) ($i['codigo'] ?? '')));
                $suc = Str::ascii(Str::lower($i['sucursal'] ?? ''));
                return str_contains($nom, $term) || str_contains($cod, $term) || str_contains($suc, $term);
            }));
        }

        $metricas = $this->calcularMetricas();
        $periodoLabel = ucfirst(Carbon::createFromFormat('Y-m', $this->referenceMonth)->locale('es')->translatedFormat('F Y'));

        return view('livewire.planilla-refrigerio', [
            'branches'      => $branches,
            'filteredItems' => $filteredItems,
            'diasMes'       => $this->diasMes,
            'metricas'      => $metricas,
            'periodoLabel'  => $periodoLabel,
            'tiposEstados'  => $tiposEstados,
        ])->layout('layouts.app', ['title' => 'Planilla de Descuento de Refrigerio / Comida']);
    }
}
