<?php

namespace App\Livewire;

use App\Models\Empleado;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

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
        $layout = auth()->check() ? 'layouts.app' : 'layouts.guest';

        return view('livewire.consulta-carnet')
            ->layout($layout, ['title' => 'Consulta por carnet']);
    }
}
