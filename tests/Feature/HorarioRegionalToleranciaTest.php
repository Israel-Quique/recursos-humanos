<?php

namespace Tests\Feature;

use App\Livewire\HorariosPage;
use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use App\Services\ProgramacionLaboralService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HorarioRegionalToleranciaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::query()->create([
            'name' => 'Admin Horarios',
            'email' => 'admin.horarios@test.local',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('administrador');
    }

    public function test_horario_regional_soporta_tolerancia_mensual_personalizada(): void
    {
        $horario = HorarioRegional::create([
            'sucursal' => 'Cochabamba',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tolerancia_minutos' => 10,
            'hora_tolerancia' => '08:40:00',
            'tolerancia_mensual_minutos' => 60,
        ]);

        $this->assertDatabaseHas('horarios_regionales', [
            'sucursal' => 'Cochabamba',
            'tolerancia_mensual_minutos' => 60,
            'tolerancia_minutos' => 10,
        ]);
    }

    public function test_programacion_laboral_resuelve_tolerancia_mensual_regional_y_global(): void
    {
        cache()->forever('asistencia_tolerancia_min', 35);

        // Sin horario regional configurado -> retorna el global (35 min)
        $service = app(ProgramacionLaboralService::class);
        $this->assertEquals(35, $service->resolverToleranciaMensual('La Paz'));

        // Con horario regional específico de contingencia (60 min)
        HorarioRegional::create([
            'sucursal' => 'Cochabamba',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tolerancia_minutos' => 5,
            'tolerancia_mensual_minutos' => 60,
        ]);

        $this->assertEquals(60, $service->resolverToleranciaMensual('Cochabamba'));
        // Otra regional sigue teniendo el estándar global
        $this->assertEquals(35, $service->resolverToleranciaMensual('Santa Cruz'));
    }

    public function test_edicion_global_estandar_actualiza_cache_y_sincroniza_sucursales(): void
    {
        Empleado::query()->create([
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'area' => 'Operaciones',
            'sucursal' => 'Tarija',
            'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => now()->subYear()->toDateString(),
            'codigo_biometrico' => '1001',
        ]);

        Livewire::actingAs($this->admin)
            ->test(HorariosPage::class)
            ->call('openGlobalModal')
            ->set('globalEditHoraEntrada', '08:00')
            ->set('globalEditHoraTolerancia', '08:10')
            ->set('globalEditToleranciaDiaria', 10)
            ->set('globalEditHoraSalida', '16:00')
            ->set('globalEditToleranciaMensual', 45)
            ->set('aplicarATodasLasSucursales', true)
            ->call('saveGlobalSettings')
            ->assertHasNoErrors()
            ->assertSet('globalTolerancia', 45)
            ->assertSet('globalToleranciaDiaria', 10);

        $this->assertEquals(45, cache()->get('asistencia_tolerancia_min'));
        $this->assertEquals(10, cache()->get('asistencia_tolerancia_diaria_min'));

        // Se sincronizó con Tarija
        $horarioTarija = HorarioRegional::where('sucursal', 'Tarija')->first();
        $this->assertNotNull($horarioTarija);
        $this->assertEquals('08:00:00', $horarioTarija->hora_entrada);
        $this->assertEquals(45, $horarioTarija->tolerancia_mensual_minutos);
    }

    public function test_edicion_por_sucursal_permite_ampliar_tolerancia_mensual_por_contingencia(): void
    {
        Livewire::actingAs($this->admin)
            ->test(HorariosPage::class)
            ->call('openEditModal', 'Oruro')
            ->set('editHoraEntrada', '08:30')
            ->set('editHoraTolerancia', '08:45')
            ->set('editHoraSalida', '16:30')
            ->set('editToleranciaMensual', 75)
            ->call('saveHorario')
            ->assertHasNoErrors();

        $horarioOruro = HorarioRegional::where('sucursal', 'Oruro')->first();
        $this->assertNotNull($horarioOruro);
        $this->assertEquals(75, $horarioOruro->tolerancia_mensual_minutos);
        $this->assertEquals(15, $horarioOruro->tolerancia_minutos);
    }

    public function test_tarjetas_superiores_reaccionan_a_la_sucursal_seleccionada(): void
    {
        HorarioRegional::create([
            'sucursal' => 'Beni',
            'hora_entrada' => '07:30:00',
            'hora_salida' => '15:30:00',
            'tolerancia_minutos' => 15,
            'hora_tolerancia' => '07:45:00',
            'tolerancia_mensual_minutos' => 50,
        ]);

        Livewire::actingAs($this->admin)
            ->test(HorariosPage::class)
            ->call('selectSucursal', 'Beni')
            ->assertSet('topCardsScope', 'sucursal')
            ->assertSet('activeSucursal', 'Beni')
            ->assertViewHas('topCardsData', function ($data) {
                return $data->is_sucursal === true
                    && $data->scope_label === 'Beni'
                    && $data->hora_entrada === '07:30'
                    && $data->hora_tolerancia === '07:45'
                    && $data->tolerancia_diaria_min === 15
                    && $data->tolerancia_mensual === 50;
            });
    }

    public function test_analisis_asistencia_aplica_tolerancia_regional_en_reporte_empleado(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'area' => 'Distribución',
            'sucursal' => 'Pando',
            'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => now()->subYear()->toDateString(),
            'codigo_biometrico' => '2001',
        ]);

        // Pando tiene 60 minutos de tolerancia mensual por contingencia climática
        HorarioRegional::create([
            'sucursal' => 'Pando',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tolerancia_minutos' => 5,
            'hora_tolerancia' => '08:35:00',
            'tolerancia_mensual_minutos' => 60,
        ]);

        // Marcación con 40 minutos de retraso
        $fecha = now()->startOfMonth()->toDateString();
        // Si el día 1 es fin de semana, buscamos un día entre semana
        $fechaCarbon = now()->startOfMonth();
        while ($fechaCarbon->isWeekend()) {
            $fechaCarbon->addDay();
        }

        RegistroAsistencia::create([
            'empleado_id' => $empleado->id,
            'fecha' => $fechaCarbon->toDateString(),
            'hora_entrada' => '09:15:00', // 40 min retraso sobre 08:35
            'hora_salida' => '16:30:00',
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $detalle = $service->detalleMensualPorEmpleado($empleado->id, now());

        $this->assertEquals(60, $detalle['retraso_resumen']['tolerancia_minutos']);
        // 40 min de atraso con 60 min de tolerancia: NO excedió tolerancia
        $this->assertFalse($detalle['retraso_resumen']['excedio_tolerancia']);
        $this->assertEquals(0, $detalle['retraso_resumen']['exceso_minutos']);
    }
}
