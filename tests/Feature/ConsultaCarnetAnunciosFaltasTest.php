<?php

namespace Tests\Feature;

use App\Livewire\ConsultaCarnetPage;
use App\Models\Empleado;
use App\Models\HorarioRegional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultaCarnetAnunciosFaltasTest extends TestCase
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

    private function crearEmpleado(string $codigo = '10909669', ?string $email = 'trabajador@correos.gob.bo'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => 'CARLOS',
            'apellido' => 'MAMANI',
            'codigo_biometrico' => $codigo,
            'email' => $email,
            'area' => 'OPERACIONES',
            'cargo' => 'ANALISTA DE OPERACIONES',
            'sucursal' => 'La Paz',
            'activo' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
        ]);
    }

    public function test_consulta_carnet_renderiza_portal_limpio_y_busca_empleado(): void
    {
        $empleado = $this->crearEmpleado('554433');

        Livewire::test(ConsultaCarnetPage::class)
            ->assertSee('Buscar por carnet')
            ->assertSee('Consultar Asistencia')
            ->set('carnet', '554433')
            ->assertSee($empleado->nombre_completo)
            ->assertSee('Área de OPERACIONES')
            ->assertSee('trabajador@correos.gob.bo');
    }

    public function test_consulta_carnet_abre_modal_boleta_con_carnet_valido(): void
    {
        $empleado = $this->crearEmpleado('778899');

        Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '778899')
            ->call('abrirBoletaModal')
            ->assertSet('showBoletaModal', true)
            ->assertSee('Papeleta de Comisión - Permiso Particular')
            ->assertSee('Foto del Comprobante / Respaldo');
    }
}
