<?php

return [
    'hora_entrada' => env('ASISTENCIA_HORA_ENTRADA', '08:30:00'),
    'hora_salida' => env('ASISTENCIA_HORA_SALIDA', '16:30:00'),
    'hora_entrada_tolerancia_extendida' => env('ASISTENCIA_HORA_TOLERANCIA_EXTENDIDA', '09:00:00'),
    'dias_tolerancia_extendida' => [1, 2, 3, 4],
    'horas_jornada' => env('ASISTENCIA_HORAS_JORNADA', 8),
    'password_inicial' => env('ASISTENCIA_PASSWORD_INICIAL', 'changeme123'),

    // Requerir que los modelos usen created_by en auditoría
    'enforce_created_by' => true,

    // Política de tolerancia mensual en minutos
    'tolerancia_mensual_min' => env('ASISTENCIA_TOLERANCIA_MIN', 30),
];
