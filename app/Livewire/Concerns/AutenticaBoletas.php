<?php

namespace App\Livewire\Concerns;

use App\Models\Empleado;
use App\Services\CodigoBoletaService;
use Closure;

trait AutenticaBoletas
{
    public string $codigoBoleta = '';
    public string $estadoCodigoBoleta = '';

    abstract protected function empleadoParaCodigo(): Empleado;

    public function enviarCodigoBoleta(): void
    {
        $this->resetValidation('codigoBoleta');
        $this->estadoCodigoBoleta = '';
        app(CodigoBoletaService::class)->enviar($this->empleadoParaCodigo());
        $this->codigoBoleta = '';
        $this->estadoCodigoBoleta = 'Código solicitado a tu correo registrado. Revisa tu bandeja y Spam; si no llega, contacta a RRHH para revisar la entrega. Vence en 10 minutos. Un nuevo envío invalida el código anterior.';
    }

    protected function generarBoletaAutenticada(Empleado $empleado, Closure $generar): mixed
    {
        $response = app(CodigoBoletaService::class)->consumir($empleado, trim($this->codigoBoleta), $generar);
        $this->reset('codigoBoleta', 'estadoCodigoBoleta');

        return $response;
    }
}
