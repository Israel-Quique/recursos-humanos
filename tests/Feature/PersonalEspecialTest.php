<?php

namespace Tests\Feature;

use App\Livewire\PersonalEspecialPage;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PersonalEspecialTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConPermiso(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $user->givePermissionTo($permission);

        return $user;
    }

    public function test_requiere_permiso_para_acceder_a_personal_especial(): void
    {
        $userSinPermiso = User::query()->create([
            'name' => 'Usuario Normal',
            'email' => 'normal_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($userSinPermiso)
            ->get('/personal-especial')
            ->assertForbidden();

        $userConPermiso = $this->crearUsuarioConPermiso();

        $this->actingAs($userConPermiso)
            ->get('/personal-especial')
            ->assertOk();
    }

    public function test_puede_crear_nuevo_personal_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->set('nuevoNombre', 'Carlos')
            ->set('nuevoApellido', 'Mamani')
            ->set('nuevoCodigoBiometrico', 'ESP-999')
            ->set('nuevaArea', 'Dirección')
            ->set('nuevaSucursal', 'La Paz')
            ->set('nuevaHoraEntrada', '09:00')
            ->set('nuevaHoraSalida', '18:00')
            ->call('saveNuevoEmpleadoEspecial')
            ->assertHasNoErrors()
            ->assertSee('Personal especial registrado exitosamente.');

        $this->assertDatabaseHas('empleados', [
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
            'es_especial' => true,
            'codigo_biometrico' => 'ESP-999',
            'sucursal' => 'La Paz',
        ]);
    }

    public function test_puede_vincular_empleado_existente_como_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Rodrigo',
            'apellido' => 'Vargas',
            'area' => 'Logística',
            'sucursal' => 'El Alto',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->set('vincularEmpleadoId', $empleado->id)
            ->call('vincularEmpleadoComoEspecial')
            ->assertHasNoErrors();

        $this->assertTrue($empleado->fresh()->es_especial);
    }

    public function test_puede_registrar_hora_entrada_y_salida_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Quispe',
            'codigo_biometrico' => null, // Sin código biométrico
            'area' => 'Chofer',
            'sucursal' => 'La Paz',
            'es_especial' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->subMonths(2)->toDateString(),
            'created_by' => $user->id,
        ]);

        $fechaPrueba = now()->toDateString();

        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openRegistroModal', null, $empleado->id)
            ->set('fecha', $fechaPrueba)
            ->set('horaEntrada', '08:45')
            ->set('horaSalida', '17:45')
            ->set('observacion', 'Ingreso y salida especial autorizada')
            ->call('saveRegistro')
            ->assertHasNoErrors()
            ->assertSee('Marcación especial guardada correctamente.');

        $registroCreado = RegistroAsistencia::query()->where('empleado_id', $empleado->id)->first();
        $this->assertNotNull($registroCreado);
        $this->assertEquals($fechaPrueba, $registroCreado->fecha?->toDateString());
        $this->assertEquals('08:45:00', $registroCreado->hora_entrada);
        $this->assertEquals('17:45:00', $registroCreado->hora_salida);
        $this->assertEquals('Especial', $registroCreado->tipo_verificacion);
        $this->assertEquals('Ingreso y salida especial autorizada', $registroCreado->observacion);
    }

    public function test_puede_editar_y_eliminar_marcacion_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Mariana',
            'apellido' => 'Rios',
            'area' => 'Gerencia',
            'sucursal' => 'El Alto',
            'es_especial' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $registro = RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '17:30:00',
            'tipo_verificacion' => 'Especial',
            'created_by' => $user->id,
        ]);

        // Editar
        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openRegistroModal', $registro->id)
            ->set('horaSalida', '18:15')
            ->call('saveRegistro')
            ->assertHasNoErrors();

        $this->assertEquals('18:15:00', $registro->fresh()->hora_salida);

        // Eliminar
        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openDeleteRegistroModal', $registro->id)
            ->call('deleteRegistro');

        $this->assertSoftDeleted('registros_asistencia', [
            'id' => $registro->id,
        ]);
    }

    public function test_marcacion_especial_se_refleja_en_reportes_de_asistencia(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Gonzalo',
            'apellido' => 'Arias',
            'codigo_biometrico' => null,
            'area' => 'Seguridad',
            'sucursal' => 'La Paz',
            'es_especial' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => '2026-08-01',
            'created_by' => $user->id,
        ]);

        // Registrar asistencia especial para agosto de 2026 en un día laboral
        // 2026-08-03 es lunes
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-03',
            'hora_entrada' => '08:30:00',
            'hora_salida' => '17:30:00',
            'tipo_verificacion' => 'Especial',
            'estado_marcacion' => 'Marcacion completa',
            'evento_biometrico' => 'Ingreso y salida especial RRHH',
            'observacion' => 'Turno especial',
            'created_by' => $user->id,
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $referenciaMes = Carbon::create(2026, 8, 1)->startOfMonth();

        // 1. Debe aparecer en empleadosParaReportes
        $empleadosDisponibles = $service->empleadosParaReportes(null, 'Gonzalo');
        $this->assertNotEmpty($empleadosDisponibles);
        $this->assertEquals($empleado->id, $empleadosDisponibles[0]['id']);

        // 2. Debe aparecer en detalleMensualPorEmpleado
        $detalle = $service->detalleMensualPorEmpleado($empleado->id, $referenciaMes);
        $this->assertNotNull($detalle);
        $this->assertEquals('Gonzalo Arias', $detalle['empleado']['nombre']);

        // 3. Debe calcular reportePersonalizado con las horas trabajadas
        $reporte = $service->reportePersonalizado($empleado->id, Carbon::create(2026, 8, 1), Carbon::create(2026, 8, 31));
        $this->assertNotNull($reporte);
        $this->assertNotEmpty($reporte['rows']);
        $this->assertEquals('03/08/2026', $reporte['rows'][0]['fecha']);
        $this->assertEquals('08:30', $reporte['rows'][0]['entrada']);
        $this->assertEquals('17:30', $reporte['rows'][0]['salida']);
    }

    public function test_buscador_encuentra_y_vincula_personal_directamente(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Bernardo',
            'apellido' => 'Rojas',
            'codigo_biometrico' => '7654321',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->set('search', '7654321')
            ->assertSee('Bernardo Rojas')
            ->call('vincularDirecto', $empleado->id)
            ->assertHasNoErrors()
            ->assertSee('ha sido vinculado como Personal Especial');

        $this->assertTrue($empleado->fresh()->es_especial);
    }
}
