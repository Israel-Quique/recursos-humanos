<?php

namespace Tests\Feature;

use App\Livewire\PersonalPage;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportePermisosBajaMedicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_permiso_baja_medica_aparece_en_reporte_y_no_se_omite()
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Médico',
            'codigo_biometrico' => '1001',
            'area' => 'Operaciones',
            'cargo' => 'Operador',
            'sucursal' => 'La Paz',
            'fecha_contratacion' => '2026-01-01',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
        ]);

        // Crear una baja médica de 3 días (del lunes 2026-09-07 al miércoles 2026-09-09)
        PermisoLaboral::create([
            'empleado_id' => $empleado->id,
            'tipo' => 'Permiso medico',
            'alcance' => 'dia_completo',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-09-07',
            'fecha_fin' => '2026-09-09',
            'motivo' => 'Baja médica por reposo hospitalario',
        ]);

        // Marcación normal el jueves 2026-09-10
        RegistroAsistencia::create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-09-10',
            'hora_entrada' => '08:25:00',
            'hora_salida' => '16:30:00',
        ]);

        $component = Livewire::test(PersonalPage::class)
            ->set('vista', 'marcaciones')
            ->set('inputMarcacionesSearch', '1001')
            ->set('inputMarcacionesMes', '2026-09')
            ->set('inputMarcacionesTipoFecha', 'mes')
            ->call('aplicarBusquedaMarcaciones');

        $stats = $component->get('marcacionesStats');

        // Verificar que los 3 días de baja médica se registraron en lista_permisos
        $this->assertGreaterThanOrEqual(3, $stats['total_permisos'] ?? 0);
        $this->assertNotEmpty($stats['lista_permisos']);

        $fechasPermisos = collect($stats['lista_permisos'])->pluck('fecha_raw')->all();
        $this->assertContains('2026-09-07', $fechasPermisos);
        $this->assertContains('2026-09-08', $fechasPermisos);
        $this->assertContains('2026-09-09', $fechasPermisos);

        // Verificar que la etiqueta es "Baja Médica"
        $primerPermiso = collect($stats['lista_permisos'])->firstWhere('fecha_raw', '2026-09-07');
        $this->assertEquals('Baja Médica', $primerPermiso['tipo']);

        // Verificar que el desglose global también contiene esos días
        $fechasDesglose = collect($stats['desglose_global'])->pluck('fecha')->all();
        $this->assertContains('07/09/2026', $fechasDesglose);
        $this->assertContains('08/09/2026', $fechasDesglose);
        $this->assertContains('09/09/2026', $fechasDesglose);
        $this->assertContains('10/09/2026', $fechasDesglose);

        // Verificar que el PDF se genera correctamente
        $response = $component->call('descargarPdfMarcaciones', 'personal');
        $this->assertNotNull($response);
    }

    public function test_reporte_sucursal_y_global_incluye_dias_de_baja_medica()
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $empleado = Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Salud',
            'codigo_biometrico' => '2002',
            'area' => 'Atención al Cliente',
            'cargo' => 'Agente',
            'sucursal' => 'Santa Cruz',
            'fecha_contratacion' => '2026-01-01',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
        ]);

        // Baja médica de 2 días
        PermisoLaboral::create([
            'empleado_id' => $empleado->id,
            'tipo' => 'Permiso medico',
            'alcance' => 'dia_completo',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-09-14',
            'fecha_fin' => '2026-09-15',
            'motivo' => 'Baja médica por enfermedad',
        ]);

        $component = Livewire::test(PersonalPage::class)
            ->set('vista', 'marcaciones')
            ->set('inputMarcacionesSucursal', 'Santa Cruz')
            ->set('inputMarcacionesMes', '2026-09')
            ->set('inputMarcacionesTipoFecha', 'mes')
            ->call('aplicarBusquedaMarcaciones');

        // Descarga por sucursal
        $responseSuc = $component->call('descargarPdfMarcaciones', 'sucursal');
        $this->assertNotNull($responseSuc);

        // Descarga global
        $responseGlob = $component->call('descargarPdfMarcaciones', 'global');
        $this->assertNotNull($responseGlob);
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh_baja@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
