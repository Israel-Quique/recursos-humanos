<?php

namespace App\Services;

class ConexionBiometricoService
{
    public function diagnostico(): array
    {
        $driver = config('biometrico.driver', 'archivo');

        $supported = [
            'archivo' => 'Importacion manual desde Excel o TXT exportado por el reloj.',
            'api' => 'Consumo de una API o SDK expuesto por el proveedor del biometrico.',
            'sql' => 'Lectura directa de la base de datos donde el biometrico guarda marcaciones.',
            'zk' => 'Conexion directa a dispositivos ZKTeco compatibles por IP/puerto.',
        ];

        return [
            'driver' => $driver,
            'supported' => array_keys($supported),
            'description' => $supported[$driver] ?? 'Driver no reconocido.',
            'is_configured' => match ($driver) {
                'archivo' => true,
                'api', 'sql', 'zk' => filled(config('biometrico.host')),
                default => false,
            },
            'next_steps' => $this->nextSteps($driver),
        ];
    }

    private function nextSteps(string $driver): array
    {
        return match ($driver) {
            'api' => [
                'Confirmar marca y modelo del biometrico.',
                'Solicitar SDK, API o manual tecnico al proveedor.',
                'Validar autenticacion, formato de marcaciones y frecuencia de sincronizacion.',
            ],
            'sql' => [
                'Confirmar motor de base de datos del biometrico.',
                'Identificar tabla de marcaciones y columnas de empleado, fecha, hora y tipo.',
                'Configurar lectura incremental por ultima marcacion sincronizada.',
            ],
            'zk' => [
                'Confirmar que el equipo sea compatible con protocolo ZKTeco.',
                'Definir IP fija, puerto y credenciales del dispositivo.',
                'Probar extraccion de logs desde un servicio Python dedicado.',
            ],
            default => [
                'Seguir usando la importacion por archivo como respaldo.',
                'Recopilar datos tecnicos del equipo de huella para integrar conexion directa.',
            ],
        };
    }
}
