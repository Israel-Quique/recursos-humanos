<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\ReglaSancion;
use App\Models\User;
use App\Services\ReglamentoSancionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReglamentoVigenciaAplicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ReglaSancionSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $user->givePermissionTo($permission);
        return $user;
    }

    public function test_regla_inactiva_o_no_aplica_nunca_se_evalua(): void
    {
        $service = app(ReglamentoSancionService::class);

        // Desactivar todas las reglas de atraso
        ReglaSancion::query()->atrasos()->update(['activo' => false, 'tipo_vigencia' => 'no_aplica']);

        $resultado = $service->evaluarAtraso(45);
        $this->assertNull($resultado, 'Reglas inactivas no deben generar sanción');
    }

    public function test_regla_con_vigencia_desde_mes_y_gestion_solo_aplica_en_periodo_valido(): void
    {
        $service = app(ReglamentoSancionService::class);

        // Configurar la regla de 31 a 45 min para que entre en vigencia en Septiembre 2026
        $regla = ReglaSancion::query()->atrasos()->where('rango_min', 31)->firstOrFail();
        $regla->update([
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 9, // Septiembre
        ]);

        // 1. Evaluación en Junio 2026 (mes 6 < 9): NO debe aplicar
        $fechaJunio = Carbon::create(2026, 6, 15);
        $this->assertFalse($regla->fresh()->aplicaEnPeriodo($fechaJunio));
        $resJunio = $service->evaluarAtraso(40, 1, $fechaJunio);
        $this->assertNull($resJunio, 'En junio no debe aplicar la regla que entra en septiembre');

        // 2. Evaluación en Septiembre 2026 (mes 9 >= 9): SÍ debe aplicar
        $fechaSeptiembre = Carbon::create(2026, 9, 15);
        $this->assertTrue($regla->fresh()->aplicaEnPeriodo($fechaSeptiembre));
        $resSeptiembre = $service->evaluarAtraso(40, 1, $fechaSeptiembre);
        $this->assertNotNull($resSeptiembre);
        $this->assertEquals($regla->id, $resSeptiembre->id);

        // 3. Evaluación en Gestión 2025 (año anterior): NO debe aplicar
        $fecha2025 = Carbon::create(2025, 10, 15);
        $this->assertFalse($regla->fresh()->aplicaEnPeriodo($fecha2025));
        $res2025 = $service->evaluarAtraso(40, 1, $fecha2025);
        $this->assertNull($res2025);
    }

    public function test_vigencia_masiva_actualiza_todas_las_reglas_de_categoria(): void
    {
        $service = app(ReglamentoSancionService::class);

        $afectadas = $service->aplicarVigenciaMasiva('atraso', [
            'activo' => true,
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 8, // Agosto
            'explicacion_vigencia' => 'Nuevo reglamento entra en agosto',
        ]);

        $this->assertGreaterThan(0, $afectadas);

        $reglasAtraso = ReglaSancion::query()->atrasos()->get();
        foreach ($reglasAtraso as $regla) {
            $this->assertEquals('desde_fecha', $regla->tipo_vigencia);
            $this->assertEquals(2026, $regla->aplica_desde_gestion);
            $this->assertEquals(8, $regla->aplica_desde_mes);
            $this->assertStringContainsString('Agosto', $regla->descripcion_vigencia);
        }
    }

    public function test_simulador_reglamento_muestra_estado_de_espera_para_mes_previo_a_vigencia(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        // Reglas aplican desde Septiembre 2026
        app(ReglamentoSancionService::class)->aplicarVigenciaMasiva('atraso', [
            'activo' => true,
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 9,
            'explicacion_vigencia' => 'Entra en vigencia en Septiembre',
        ]);

        Livewire::test(\App\Livewire\ReglamentoSancionesPage::class)
            ->set('simuladorMinutos', 50)
            ->set('simuladorGestion', 2026)
            ->set('simuladorMes', 6) // Junio (anterior a septiembre)
            ->assertSet('simuladorResultado.en_espera', true)
            ->assertSet('simuladorResultado.dias_sancion', 0)
            ->assertSee('No aplica sanción (0 días de descuento)')
            // Ahora evaluar en Septiembre 2026 (vigente)
            ->set('simuladorMes', 9)
            ->assertSet('simuladorResultado.en_espera', false)
            ->assertSet('simuladorResultado.dias_sancion', 1.0);
    }

    public function test_personal_page_muestra_marcha_blanca_cuando_reglamento_aun_no_aplica(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');

        $user = $this->crearUsuarioConPermisoPersonal();

        // Configurar reglamento para que entre en vigencia en Agosto 2026 (después de junio)
        app(ReglamentoSancionService::class)->aplicarVigenciaMasiva('atraso', [
            'activo' => true,
            'tipo_vigencia' => 'desde_fecha',
            'aplica_desde_gestion' => 2026,
            'aplica_desde_mes' => 8,
            'explicacion_vigencia' => 'Aplica desde Agosto',
        ]);

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Vargas',
            'codigo_biometrico' => '4001',
            'area' => 'Operaciones',
            'sucursal' => 'Cochabamba',
            'fecha_contratacion' => '2025-01-01',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
        ]);

        // Registrar atraso de 50 minutos en Junio 2026
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-06-10',
            'hora_entrada' => '09:25:00', // 08:35 + 50 min
            'hora_salida' => '16:30:00',
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page', ['vista' => 'control'])
            ->call('seleccionarSucursal', 'Cochabamba')
            ->assertSee('Carlos Vargas')
            ->assertSee('Marcha blanca');
    }
}
