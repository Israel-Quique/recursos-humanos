<?php

namespace Tests\Feature;

use App\Livewire\ConsultaCarnetPage;
use App\Models\ConfiguracionBoleta;
use App\Models\Empleado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BoletaPermisoTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\AccesoPersonalDePrueba;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Permission::findOrCreate('gestionar personal', 'web');
        $operador = \App\Models\User::query()->create(['name' => 'operador-prueba', 'email' => 'operador@example.test', 'password' => 'secreto123']);
        $operador->givePermissionTo('gestionar personal');
        $this->actingAs($operador);
        \Illuminate\Support\Facades\Mail::fake();
    }

    public function test_abrir_modal_boleta_precarga_datos_del_empleado(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Marco Antonio',
            'apellido' => 'Espinoza Rojas',
            'codigo_biometrico' => '10909669',
            'area' => 'Sistemas',
            'cargo' => 'Encargado del Area de Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        $this->consultaPersonal()
            ->set('carnet', '10909669')
            ->call('abrirBoletaModal')
            ->assertSet('showBoletaModal', true)
            ->assertSet('boletaNombre', 'Marco Antonio Espinoza Rojas')
            ->assertSet('boletaCi', '10909669')
            ->assertSet('boletaCargo', '')
            ->assertSet('boletaCiudad', 'LA PAZ');
    }

    public function test_descarga_pdf_boleta(): void
    {
        ConfiguracionBoleta::query()->create(['comprobantes_habilitados' => true]);
        $empleado = Empleado::query()->create([
            'nombre' => 'Marco Antonio',
            'apellido' => 'Espinoza Rojas',
            'codigo_biometrico' => '10909669',
            'area' => 'Sistemas',
            'cargo' => 'Encargado del Area de Sistemas',
            'email' => 'marco.espinoza@correos.gob.bo',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        Storage::fake('public');
        $imagen = UploadedFile::fake()->image('comprobante.jpg', 400, 400);

        $component = $this->consultaPersonal()
            ->set('carnet', '10909669')
            ->call('abrirBoletaModal')
            ->set('boletaCargo', 'Encargado de Sistemas')
            ->set('boletaMotivo', 'Reunion de coordinacion')
            ->set('comprobante', $imagen)
            ->call('descargarPdf');

        $component->assertFileDownloaded();
    }

    public function test_descarga_excel_boleta(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Marco Antonio',
            'apellido' => 'Espinoza Rojas',
            'codigo_biometrico' => '10909669',
            'email' => 'marco.espinoza@correos.gob.bo',
            'area' => 'Sistemas',
            'cargo' => 'Encargado del Area de Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        $component = $this->consultaPersonal()
            ->set('carnet', '10909669')
            ->call('abrirBoletaModal')
            ->set('boletaCargo', 'Encargado de Sistemas')
            ->set('boletaMotivo', 'Reunion AGETIC')
            ->call('descargarExcel');

        $component->assertFileDownloaded();
    }
}
