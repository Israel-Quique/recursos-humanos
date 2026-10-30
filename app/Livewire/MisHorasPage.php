<?php

namespace App\Livewire;

use App\Models\PermisoLaboral;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Livewire\Component;

class MisHorasPage extends Component
{
    public string $referenceMonth = '';

    public function mount(): void
    {
        if (auth()->user()?->acceso_personal) {
            $this->redirect(app(\App\Services\AccesoPersonalService::class)->destino(auth()->user()));
        }
        $this->referenceMonth = now()->format('Y-m');
    }

    public function descargarBoletaPdf(int $id)
    {
        $empleado = auth()->user()?->empleado;
        abort_unless($empleado, 403);

        $incidencia = PermisoLaboral::query()->with('empleado')->findOrFail($id);
        abort_unless((int) $incidencia->empleado_id === (int) $empleado->id, 403);

        return (new IncidenciasPage)->descargarBoletaPdf($incidencia->id);
    }

    public function render()
    {
        $user = auth()->user()?->loadMissing('empleado');
        $empleado = $user?->empleado;
        if ($user?->acceso_personal) {
            abort_unless($empleado, 403);
            app(\App\Services\AccesoPersonalService::class)->autorizarEmpleado($empleado);
        }
        $referenceMonth = Carbon::createFromFormat('Y-m', $this->referenceMonth)->startOfMonth();
        $rangeStart = $referenceMonth->copy()->startOfMonth();
        $rangeEnd = $referenceMonth->copy()->endOfMonth();
        $analysis = app(AnalisisAsistenciaService::class);

        $boletasMes = $empleado
            ? PermisoLaboral::query()
                ->with('comprobantePrincipal')
                ->where('empleado_id', $empleado->id)
                ->where('tipo', 'permiso')
                ->where(function ($query) use ($rangeStart, $rangeEnd) {
                    $startDate = $rangeStart->toDateString();
                    $endDate = $rangeEnd->toDateString();
                    $query->whereBetween('fecha_inicio', [$startDate, $endDate])
                        ->orWhereBetween('fecha_fin', [$startDate, $endDate])
                        ->orWhere(function ($sub) use ($startDate, $endDate) {
                            $sub->whereDate('fecha_inicio', '<=', $startDate)
                                ->whereDate('fecha_fin', '>=', $endDate);
                        });
                })
                ->latest('id')
                ->get()
            : collect();

        return view('livewire.mis-horas', [
            'empleado' => $empleado,
            'monthOptions' => $this->monthOptions($empleado),
            'monthLabel' => ucfirst($referenceMonth->locale('es')->translatedFormat('F Y')),
            'personalReport' => $empleado
                ? $analysis->reportePersonalizado($empleado->id, $rangeStart, $rangeEnd)
                : null,
            'boletasMes' => $boletasMes,
        ])->layout('layouts.app', ['title' => 'Mis horas marcadas']);
    }

    private function monthOptions($empleado): array
    {
        if (! $empleado) {
            return [[
                'value' => now()->format('Y-m'),
                'label' => ucfirst(now()->locale('es')->translatedFormat('F Y')),
            ]];
        }

        $start = ($empleado->fecha_contratacion ?? now())->copy()->startOfMonth();
        $end = now()->copy()->startOfMonth();

        if ($start->greaterThan($end)) {
            $start = $end->copy();
        }

        $options = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('es')->translatedFormat('F Y')),
            ];
            $cursor->addMonthNoOverflow();
        }

        return array_reverse($options);
    }
}
