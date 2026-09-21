<?php

namespace App\Livewire;

use App\Models\PlanillaRefrigerio;
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
    public bool $isDirty = false;
    public ?string $ultimaGuardada = null;

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
     */
    public function cargarPlanilla(): void
    {
        $registro = PlanillaRefrigerio::query()
            ->where('periodo', $this->referenceMonth)
            ->where('sucursal', $this->selectedBranch ?: null)
            ->first();

        if ($registro && !empty($registro->datos['items'] ?? [])) {
            $this->tarifaDiaria = (float) $registro->tarifa_diaria;
            $this->items = $registro->datos['items'];
            $this->ultimaGuardada = $registro->updated_at?->format('d/m/Y H:i');
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
        $this->tarifaDiaria = (float) $resultado['tarifa_diaria'];
        $this->isDirty = true;

        if ($mostrarMensaje) {
            session()->flash('status', 'Datos extraídos correctamente de asistencias y permisos autorizados.');
        }
    }

    /**
     * Actualiza el valor de un día (Faltas, Omisiones, Bajas, Comisiones) en vivo.
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
        $bajas = (int) ($this->items[$index]['bajas_medicas'] ?? 0);
        $comisiones = (int) ($this->items[$index]['comisiones_viaje'] ?? 0);

        $totalDias = $faltas + $omisiones + $bajas + $comisiones;
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
            'total_bajas_medicas' => (int) $itemsCol->sum('bajas_medicas'),
            'total_comisiones_viaje' => (int) $itemsCol->sum('comisiones_viaje'),
            'gran_total_dias' => $totalDias,
            'gran_total_monto' => round($totalDias * $tarifa, 2),
        ];
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $branches = $analysis->sucursalesParaReportes();

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
            'branches' => $branches,
            'filteredItems' => $filteredItems,
            'metricas' => $metricas,
            'periodoLabel' => $periodoLabel,
        ])->layout('layouts.app', ['title' => 'Planilla de Descuento de Refrigerio / Comida']);
    }
}
