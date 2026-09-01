<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Services\BiometricoAutoSyncService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Panel de inicio'])]
class InicioPage extends Component
{
    public function sincronizarBiometrico(): void
    {
        try {
            app(BiometricoAutoSyncService::class)->triggerNow();
            session()->flash('status', 'Sincronización iniciada en segundo plano. El proceso continúa sin bloquear la página.');
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('status', 'No se pudo iniciar la sincronización biométrica: ' . $exception->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $totalEmpleadosActivos = Empleado::query()->whereNull('fecha_despido')->count();
        if ($totalEmpleadosActivos === 0) {
            $totalEmpleadosActivos = Empleado::query()->count();
        }
        $sucursales = Empleado::query()
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->distinct()
            ->pluck('sucursal');
        $totalSucursales = count(SucursalNormalizer::optionsFromValues($sucursales));
        $hoy = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');

        return view('livewire.inicio', [
            'user' => $user,
            'totalEmpleadosActivos' => $totalEmpleadosActivos,
            'totalSucursales' => max($totalSucursales, 9),
            'hoy' => ucfirst($hoy),
        ]);
    }
}
