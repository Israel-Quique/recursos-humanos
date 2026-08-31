<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\Marcacion;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Panel de inicio'])]
class InicioPage extends Component
{
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
