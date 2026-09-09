<?php

namespace Tests\Feature;

use App\Livewire\PerfilHorasPage;
use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Models\RegistroAsistencia;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PerfilHorasAnunciosFaltasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ReglaSancionSeeder::class);

        HorarioRegional::query()->updateOrCreate(
            ['sucursal' => 'La Paz'],
            [
                'hora_entrada' => '08:30:00',
                'hora_entrada_tolerancia' => '08:35:00',
                'hora_salida' => '16:30:00',
                'tolerancia_mensual' => 30,
                'activo' => true,
            ]
        );
    }

    private function crearEmpleado(string $codigo = '10909669'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => 'JUAN CARLOS',
            'apellido' => 'MAMANI',
            'codigo_biometrico' => $codigo,
            'email' => 'juan.mamani@correos.gob.bo',
            'area' => 'OPERACIONES',
            'cargo' => 'ANALISTA',
            'sucursal' => 'La Paz',
            'activo' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
        ]);
    }

    public function test_perfil_horas_muestra_boton_y_aviso_institucional_articulo_45(): void
    {
        $empleado = $this->crearEmpleado('10909669');

        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->assertSee('Normativa y Faltas (Art. 45)')
            ->assertSee('Normativa Interna · Artículo 45')
            ->assertSee('Control de Asistencia, Atrasos y Régimen Disciplinario')
            ->assertSee('Plazo de 48 horas')
            ->assertSee('Ver Faltas y Reglamento (Art. 45)');
    }

    public function test_perfil_horas_abre_modal_con_reglamento_y_sin_montos_de_descuento(): void
    {
        $empleado = $this->crearEmpleado('10909669');

        $component = Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->call('abrirModalNormativa')
            ->assertSet('mostrarModalNormativa', true)
            ->assertSee('ARTÍCULO 45: Faltas, Infracciones y Consecuencias Disciplinarias')
            ->assertSee('Regla Improrrogable de 48 Horas para Boletas y Justificaciones')
            ->assertSee('Punto I · Artículo 45, Numeral I')
            ->assertSee('Atrasos en los Horarios de Ingreso')
            ->assertSee('Punto II · Artículo 45, Numeral II')
            ->assertSee('Inasistencias y Ausencias en el Puesto de Trabajo')
            ->assertSee('Punto III · Artículo 45, Numeral III')
            ->assertSee('Omisión en el Registro de Asistencia')
            // Validación clave solicitada por el usuario: NO debe mostrarse montos ni deducciones de sueldo
            ->assertDontSee('descuento de haber')
            ->assertDontSee('-0.5 día')
            ->assertDontSee('descuento de 1 día')
            ->assertDontSee('Bs.');

        // Filtrar por categoría
        $component->call('setCategoriaNormativa', 'omision')
            ->assertSet('categoriaNormativa', 'omision')
            ->assertSee('Omisión en el Registro de Asistencia')
            ->call('cerrarModalNormativa')
            ->assertSet('mostrarModalNormativa', false);
    }

    public function test_perfil_horas_elimina_tolerancia_mes_y_exceso_mensual_de_la_cuadricula_inferior(): void
    {
        $empleado = $this->crearEmpleado('10909669');
        $service = app(AnalisisAsistenciaService::class);
        $reporte = $service->reportePersonalizado(
            $empleado->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $labels = collect($reporte['metrics'])->pluck('label')->all();

        // Debe contener exactamente las 6 métricas limpias
        $this->assertCount(6, $reporte['metrics']);
        $this->assertContains('Dias con marcacion', $labels);
        $this->assertContains('Horas acumuladas', $labels);
        $this->assertContains('Dias tarde', $labels);
        $this->assertContains('Retraso acumulado', $labels);
        $this->assertContains('Omisiones', $labels);
        $this->assertContains('Faltas', $labels);

        // NO debe contener los duplicados que ya están arriba en los mini KPIs
        $this->assertNotContains('Tolerancia mes', $labels);
        $this->assertNotContains('Exceso mensual', $labels);
    }

    public function test_perfil_horas_alerta_cuando_empleado_supera_tolerancia(): void
    {
        $empleado = $this->crearEmpleado('10909669');
        $ahora = now();

        // 2 atrasos de 25 min cada uno = 50 min > 30 min tolerancia
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => $ahora->copy()->startOfMonth()->addDays(2)->toDateString(),
            'hora_entrada' => '09:00:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Completo',
            'estado' => 'Presente',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => $ahora->copy()->startOfMonth()->addDays(3)->toDateString(),
            'hora_entrada' => '09:00:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Completo',
            'estado' => 'Presente',
        ]);

        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->assertSee('Has superado el margen de tolerancia mensual de 30 minutos')
            ->assertSee('Retraso acumulado: 50 min')
            ->assertSee('Artículo 45, Numeral I')
            // Sin montos económicos al empleado
            ->assertDontSee('descuento de sueldo')
            ->assertDontSee('-0.5 día');
    }
}
