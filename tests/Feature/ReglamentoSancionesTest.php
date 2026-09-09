<?php

namespace Tests\Feature;

use App\Livewire\ReglamentoSancionesPage;
use App\Models\ReglaSancion;
use App\Models\User;
use App\Services\ReglamentoSancionService;
use Database\Seeders\ReglaSancionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReglamentoSancionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ReglaSancionSeeder::class);
    }

    public function test_reglas_sanciones_son_pobladas_con_los_articulos_oficiales(): void
    {
        $this->assertGreaterThanOrEqual(14, ReglaSancion::count());

        $atrasos = ReglaSancion::atrasos()->get();
        $this->assertCount(6, $atrasos);

        $primerTramo = $atrasos->firstWhere('rango_min', 1);
        $this->assertEquals(30, $primerTramo->rango_max);
        $this->assertEquals(0.00, $primerTramo->dias_sancion);
        $this->assertEquals('Sin sanción', $primerTramo->sancion_texto);

        $segundoTramo = $atrasos->firstWhere('rango_min', 31);
        $this->assertEquals(45, $segundoTramo->rango_max);
        $this->assertEquals(0.50, $segundoTramo->dias_sancion);

        $tercerTramo = $atrasos->firstWhere('rango_min', 46);
        $this->assertEquals(60, $tercerTramo->rango_max);
        $this->assertEquals(1.00, $tercerTramo->dias_sancion);

        $cuartoTramo = $atrasos->firstWhere('rango_min', 61);
        $this->assertEquals(90, $cuartoTramo->rango_max);
        $this->assertEquals(2.00, $cuartoTramo->dias_sancion);

        $quintoTramo = $atrasos->firstWhere('rango_min', 91);
        $this->assertEquals(120, $quintoTramo->rango_max);
        $this->assertEquals(3.00, $quintoTramo->dias_sancion);

        $sextoTramo = $atrasos->firstWhere('rango_min', 121);
        $this->assertNull($sextoTramo->rango_max);
        $this->assertEquals(4.00, $sextoTramo->dias_sancion);
    }

    public function test_servicio_evalua_correctamente_escala_de_atrasos(): void
    {
        $service = app(ReglamentoSancionService::class);

        // 1 a 30 min -> 0 días (Sin sanción)
        $regla = $service->evaluarAtraso(15);
        $this->assertNotNull($regla);
        $this->assertEquals(0.00, $regla->dias_sancion);
        $this->assertEquals('Sin sanción', $regla->sancion_texto);

        // 31 a 45 min -> 0.5 días
        $regla = $service->evaluarAtraso(40);
        $this->assertNotNull($regla);
        $this->assertEquals(0.50, $regla->dias_sancion);

        // 46 a 60 min -> 1 día
        $regla = $service->evaluarAtraso(55);
        $this->assertNotNull($regla);
        $this->assertEquals(1.00, $regla->dias_sancion);

        // 61 a 90 min -> 2 días
        $regla = $service->evaluarAtraso(80);
        $this->assertNotNull($regla);
        $this->assertEquals(2.00, $regla->dias_sancion);

        // 91 a 120 min -> 3 días
        $regla = $service->evaluarAtraso(110);
        $this->assertNotNull($regla);
        $this->assertEquals(3.00, $regla->dias_sancion);

        // 121 o más min (1ra vez) -> 4 días
        $regla = $service->evaluarAtraso(145, 1);
        $this->assertNotNull($regla);
        $this->assertEquals(4.00, $regla->dias_sancion);
        $this->assertFalse($regla->es_destitucion);

        // 121 o más min (3ra vez en gestión) -> Destitución con proceso interno
        $reglaGravisima = $service->evaluarAtraso(145, 3);
        $this->assertNotNull($reglaGravisima);
        $this->assertTrue($reglaGravisima->es_destitucion);
        $this->assertEquals('Destitución con proceso interno', $reglaGravisima->sancion_texto);
    }

    public function test_administrador_puede_acceder_a_vista_independiente_de_reglamento(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrador');

        $this->actingAs($admin)
            ->get(route('reglamento-sanciones'))
            ->assertOk()
            ->assertSee('Reglamento de sanciones');
    }

    public function test_administrador_puede_editar_regla_dinamicamente(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrador');

        $regla = ReglaSancion::where('rango_min', 31)->where('rango_max', 45)->first();
        $this->assertNotNull($regla);

        Livewire::actingAs($admin)
            ->test(ReglamentoSancionesPage::class)
            ->assertSee('Reglamento de sanciones')
            ->call('openEditReglaModal', $regla->id)
            ->set('reglaDiasSancion', 0.75)
            ->set('reglaSancionTexto', 'Tres cuartos (3/4) de día')
            ->call('saveRegla')
            ->assertHasNoErrors();

        $reglaActualizada = $regla->fresh();
        $this->assertEquals(0.75, $reglaActualizada->dias_sancion);
        $this->assertEquals('Tres cuartos (3/4) de día', $reglaActualizada->sancion_texto);

        // La evaluación debe tomar el nuevo valor de la base de datos
        $service = app(ReglamentoSancionService::class);
        $eval = $service->evaluarAtraso(35);
        $this->assertEquals(0.75, $eval->dias_sancion);
        $this->assertEquals('Tres cuartos (3/4) de día', $eval->sancion_texto);
    }

    public function test_simulador_en_vivo_de_reglamento_sanciones_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Test 2',
            'email' => 'admin2@test.local',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrador');

        Livewire::actingAs($admin)
            ->test(ReglamentoSancionesPage::class)
            ->set('simuladorMinutos', 75)
            ->set('simuladorVecesGestion', 1)
            ->assertSet('simuladorResultado.dias_sancion', 2.0)
            ->assertSet('simuladorResultado.sancion_texto', 'Dos (2) días');
    }

    public function test_incidencias_page_esta_desvinculada_del_reglamento_de_sanciones(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Test 3',
            'email' => 'admin3@test.local',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrador');

        $this->actingAs($admin)
            ->get(route('incidencias'))
            ->assertOk()
            ->assertSee('Incidencias, permisos y faltas')
            ->assertDontSee('ARTÍCULO 45')
            ->assertDontSee('Simulador de Sanción en Vivo');
    }

    public function test_toggle_categoria_activa_y_bloqueo_edicion(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Test 4',
            'email' => 'admin4@test.local',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('administrador');

        $reglaAtraso = ReglaSancion::where('categoria', 'atraso')->first();
        $this->assertNotNull($reglaAtraso);

        // Desactivar categoría completa de atrasos
        Livewire::actingAs($admin)
            ->test(ReglamentoSancionesPage::class)
            ->call('toggleCategoriaActiva', 'atraso')
            ->assertSet('showEditReglaModal', false);

        // Verificar que todas las reglas de atraso pasaron a activo = false
        $activas = ReglaSancion::where('categoria', 'atraso')->where('activo', true)->count();
        $this->assertEquals(0, $activas);

        // Intentar abrir modal de edición de una regla de categoría desactivada debe ser bloqueado
        Livewire::actingAs($admin)
            ->test(ReglamentoSancionesPage::class)
            ->call('openEditReglaModal', $reglaAtraso->id)
            ->assertSet('showEditReglaModal', false);

        // Reactivar la categoría
        Livewire::actingAs($admin)
            ->test(ReglamentoSancionesPage::class)
            ->call('toggleCategoriaActiva', 'atraso');

        $activasReactivadas = ReglaSancion::where('categoria', 'atraso')->where('activo', true)->count();
        $this->assertGreaterThan(0, $activasReactivadas);
    }
}

