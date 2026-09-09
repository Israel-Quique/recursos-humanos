<?php

namespace Database\Seeders;

use App\Models\ReglaSancion;
use Illuminate\Database\Seeder;

class ReglaSancionSeeder extends Seeder
{
    /**
     * Seed the default rules according to the agency's internal regulations (Art. 45 y Art. 48).
     */
    public function run(): void
    {
        $reglas = [
            // ==========================================
            // I. ATRASOS EN HORARIOS DE INGRESO (Art. 45.I)
            // ==========================================
            [
                'categoria' => 'atraso',
                'causal' => '1 a 30 minutos',
                'rango_min' => 1,
                'rango_max' => 30,
                'dias_sancion' => 0.00,
                'sancion_texto' => 'Sin sanción',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 1,
                'activo' => true,
                'observaciones' => 'Posteriores a los 5 minutos de tolerancia diaria.',
            ],
            [
                'categoria' => 'atraso',
                'causal' => '31 a 45 minutos',
                'rango_min' => 31,
                'rango_max' => 45,
                'dias_sancion' => 0.50,
                'sancion_texto' => 'Medio (1/2) día',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 2,
                'activo' => true,
                'observaciones' => 'Sanción económica deducible de la remuneración mensual.',
            ],
            [
                'categoria' => 'atraso',
                'causal' => '46 a 60 minutos',
                'rango_min' => 46,
                'rango_max' => 60,
                'dias_sancion' => 1.00,
                'sancion_texto' => 'Un (1) día',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 3,
                'activo' => true,
                'observaciones' => 'Sanción económica comunicada vía memorándum DAF.',
            ],
            [
                'categoria' => 'atraso',
                'causal' => '61 a 90 minutos',
                'rango_min' => 61,
                'rango_max' => 90,
                'dias_sancion' => 2.00,
                'sancion_texto' => 'Dos (2) días',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 4,
                'activo' => true,
                'observaciones' => 'Sanción económica comunicada vía memorándum DAF.',
            ],
            [
                'categoria' => 'atraso',
                'causal' => '91 a 120 minutos',
                'rango_min' => 91,
                'rango_max' => 120,
                'dias_sancion' => 3.00,
                'sancion_texto' => 'Tres (3) días',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 5,
                'activo' => true,
                'observaciones' => 'Sanción económica comunicada vía memorándum DAF.',
            ],
            [
                'categoria' => 'atraso',
                'causal' => '121 o más minutos por primera vez en la Gestión',
                'rango_min' => 121,
                'rango_max' => null,
                'dias_sancion' => 4.00,
                'sancion_texto' => 'Cuatro (4) días',
                'unidad' => 'minutos',
                'es_destitucion' => false,
                'orden' => 6,
                'activo' => true,
                'observaciones' => 'Aplica por primera vez en la gestión anual.',
            ],

            // ==========================================
            // II. INASISTENCIAS Y AUSENCIAS (Art. 45.II)
            // ==========================================
            [
                'categoria' => 'inasistencia',
                'causal' => 'Medio (1/2) día',
                'rango_min' => null,
                'rango_max' => null,
                'dias_sancion' => 1.00,
                'sancion_texto' => 'Un (1) día',
                'unidad' => 'dias',
                'es_destitucion' => false,
                'orden' => 1,
                'activo' => true,
                'observaciones' => 'Engloba el día no trabajado más infracción administrativa.',
            ],
            [
                'categoria' => 'inasistencia',
                'causal' => 'Un (1) día',
                'rango_min' => null,
                'rango_max' => null,
                'dias_sancion' => 2.00,
                'sancion_texto' => 'Dos (2) días',
                'unidad' => 'dias',
                'es_destitucion' => false,
                'orden' => 2,
                'activo' => true,
                'observaciones' => 'Engloba el día no trabajado más infracción administrativa.',
            ],

            // ==========================================
            // III. OMISIÓN EN REGISTRO DE ASISTENCIA (Art. 45.III)
            // ==========================================
            [
                'categoria' => 'omision',
                'causal' => 'Primera vez en el mes',
                'rango_min' => 1,
                'rango_max' => 1,
                'dias_sancion' => 0.50,
                'sancion_texto' => 'Medio (1/2) día',
                'unidad' => 'ocurrencias',
                'es_destitucion' => false,
                'orden' => 1,
                'activo' => true,
                'observaciones' => 'Sin regularizar mediante formulario respectivo.',
            ],
            [
                'categoria' => 'omision',
                'causal' => 'Segunda vez en el mes',
                'rango_min' => 2,
                'rango_max' => 2,
                'dias_sancion' => 1.00,
                'sancion_texto' => 'Un (1) día',
                'unidad' => 'ocurrencias',
                'es_destitucion' => false,
                'orden' => 2,
                'activo' => true,
                'observaciones' => 'Sin regularizar mediante formulario respectivo.',
            ],

            // ==========================================
            // IV. FALTAS GRAVÍSIMAS (Art. 48)
            // ==========================================
            [
                'categoria' => 'gravisima',
                'causal' => '121 minutos de atraso o más en el mes, por tercera vez en la Gestión',
                'rango_min' => 121,
                'rango_max' => null,
                'dias_sancion' => 0.00,
                'sancion_texto' => 'Destitución con proceso interno',
                'unidad' => 'minutos',
                'es_destitucion' => true,
                'orden' => 1,
                'activo' => true,
                'observaciones' => 'Falta gravísima por reincidencia en la gestión anual.',
            ],
            [
                'categoria' => 'gravisima',
                'causal' => 'Ausencia en el puesto de trabajo, por 3 días continuos en el mes',
                'rango_min' => 3,
                'rango_max' => null,
                'dias_sancion' => 0.00,
                'sancion_texto' => 'Destitución con proceso interno',
                'unidad' => 'dias',
                'es_destitucion' => true,
                'orden' => 2,
                'activo' => true,
                'observaciones' => 'Falta gravísima por ausencia injustificada continua.',
            ],
            [
                'categoria' => 'gravisima',
                'causal' => 'Ausencia en el puesto de trabajo, por 6 días discontinuos en el mes',
                'rango_min' => 6,
                'rango_max' => null,
                'dias_sancion' => 0.00,
                'sancion_texto' => 'Destitución con proceso interno',
                'unidad' => 'dias',
                'es_destitucion' => true,
                'orden' => 3,
                'activo' => true,
                'observaciones' => 'Falta gravísima por ausencia injustificada discontinua.',
            ],
            [
                'categoria' => 'gravisima',
                'causal' => 'Omisión en el Registro de Asistencia por cuarta vez en el mes',
                'rango_min' => 4,
                'rango_max' => null,
                'dias_sancion' => 0.00,
                'sancion_texto' => 'Destitución con proceso interno',
                'unidad' => 'ocurrencias',
                'es_destitucion' => true,
                'orden' => 4,
                'activo' => true,
                'observaciones' => 'Falta gravísima por omisión recurrente no regularizada.',
            ],
        ];

        foreach ($reglas as $regla) {
            ReglaSancion::updateOrCreate(
                [
                    'categoria' => $regla['categoria'],
                    'causal' => $regla['causal'],
                ],
                $regla
            );
        }
    }
}
