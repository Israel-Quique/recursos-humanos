<?php

namespace Tests\Feature;

use App\Models\ReglaSancion;
use App\Services\ReglamentoSancionService;
use Carbon\Carbon;
use Database\Seeders\ReglaSancionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglamentoVigenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ReglaSancionSeeder::class);
    }

    public function test_regla_no_aplica_antes_del_mes_y_gestion_configurado(): void
    {
        $service = app(ReglamentoSancionService::class);

        // Configurar todas las reglas de atraso para que apliquen desde Septiembre 2026 (mes 9)
        $service->aplicarVigenciaMasiva('atraso', [
            'activo' => true,
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 9,
        ]);

        // En Agosto 2026 (mes 8, antes de vigencia):
        $fechaAgosto = Carbon::create(2026, 8, 15);
        $reglaAgosto = $service->evaluarAtraso(50, 1, $fechaAgosto);
        $this->assertNull($reglaAgosto, 'No debe aplicar sanción de atraso en un mes anterior a su vigencia.');

        // En Septiembre 2026 (mes 9, inicio de vigencia):
        $fechaSeptiembre = Carbon::create(2026, 9, 15);
        $reglaSeptiembre = $service->evaluarAtraso(50, 1, $fechaSeptiembre);
        $this->assertNotNull($reglaSeptiembre, 'Debe aplicar sanción de atraso en el mes de vigencia.');
        $this->assertEquals(1.0, (float) $reglaSeptiembre->dias_sancion);

        // En Octubre 2026 (mes 10, posterior a vigencia):
        $fechaOctubre = Carbon::create(2026, 10, 15);
        $reglaOctubre = $service->evaluarAtraso(50, 1, $fechaOctubre);
        $this->assertNotNull($reglaOctubre, 'Debe continuar vigente en meses posteriores.');
    }

    public function test_falta_gravisima_no_aplica_destitucion_antes_de_su_vigencia(): void
    {
        $service = app(ReglamentoSancionService::class);

        // Configurar la regla de falta gravísima para que aplique desde Septiembre 2026 (mes 9)
        $service->aplicarVigenciaMasiva('gravisima', [
            'activo' => true,
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 9,
        ]);

        // En Mayo 2026 (mes 5, previo a vigencia), incluso con 3ra vez de atraso:
        $fechaMayo = Carbon::create(2026, 5, 15);
        $reglaMayo = $service->evaluarAtraso(130, 3, $fechaMayo);
        // La falta gravísima con destitución no debe aplicar
        $this->assertTrue($reglaMayo === null || ! $reglaMayo->es_destitucion, 'No debe aplicar destitución en meses anteriores a la vigencia.');

        // En Septiembre 2026 (mes 9), con 3ra vez:
        $fechaSeptiembre = Carbon::create(2026, 9, 15);
        $reglaSeptiembre = $service->evaluarAtraso(130, 3, $fechaSeptiembre);
        $this->assertNotNull($reglaSeptiembre);
        $this->assertTrue($reglaSeptiembre->es_destitucion, 'Debe aplicar destitución cuando ya está vigente.');
    }
}
