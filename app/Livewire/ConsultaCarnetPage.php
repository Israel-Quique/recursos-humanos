<?php

namespace App\Livewire;

use App\Models\Empleado;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ConsultaCarnetPage extends Component
{
    public string $carnet = '';

    public function buscar(): void
    {
        $data = $this->validate([
            'carnet' => ['required', 'string', 'max:50'],
        ], [
            'carnet.required' => 'Ingresa tu carnet o codigo.',
        ]);

        $carnet = trim($data['carnet']);

        $empleado = Empleado::query()
            ->where('codigo_biometrico', $carnet)
            ->first();

        if (! $empleado) {
            $this->addError('carnet', 'No encontramos un trabajador con ese carnet o codigo registrado.');

            return;
        }

        $signedPath = URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false);

        $this->redirect($signedPath, navigate: true);
    }

    public function render()
    {
        return view('livewire.consulta-carnet')
            ->layout('layouts.guest', ['title' => 'Consulta por carnet']);
    }
}
