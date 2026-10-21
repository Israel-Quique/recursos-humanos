<?php

namespace Tests\Concerns;

use App\Livewire\ConsultaCarnetPage;
use App\Livewire\PerfilHorasPage;
use App\Models\Empleado;
use App\Models\User;
use App\Services\AccesoPersonalService;
use Livewire\Livewire;

trait AccesoPersonalDePrueba
{
    private function cuentaPersonal(Empleado $empleado): User
    {
        $service = app(AccesoPersonalService::class);
        $service->guardar('persona', (string) $empleado->id, true);
        $user = $service->crearCuenta($empleado);
        $user->update(['password' => 'ClaveDePrueba123!', 'must_change_password' => false]);
        return $user;
    }

    private function consultaPersonal()
    {
        foreach (Empleado::all() as $empleado) {
            $this->cuentaPersonal($empleado);
        }
        auth()->logout();
        return Livewire::test(ConsultaCarnetPage::class)->set('password', 'ClaveDePrueba123!');
    }

    private function perfilPersonal(Empleado $empleado)
    {
        $this->actingAs($this->cuentaPersonal($empleado));
        return Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado]);
    }
}
