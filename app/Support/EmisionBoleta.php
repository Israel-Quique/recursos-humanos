<?php

namespace App\Support;

use App\Models\Empleado;

class EmisionBoleta
{
    public static function fechaHora(): string
    {
        $momento = now('America/La_Paz')->locale('es');

        return $momento->format('d').' DE '.mb_strtoupper($momento->translatedFormat('F'))
            .' DE '.$momento->format('Y').' - '.$momento->format('H:i');
    }

    public static function datos(?Empleado $empleado): array
    {
        $sucursal = mb_strtoupper(trim($empleado?->sucursal ?? '') ?: 'SIN SUCURSAL REGISTRADA');
        $fechaHora = self::fechaHora();

        return [
            'ciudad' => $sucursal,
            'fecha_texto' => $fechaHora,
            'lugar_fecha' => $sucursal.', '.$fechaHora,
        ];
    }
}
