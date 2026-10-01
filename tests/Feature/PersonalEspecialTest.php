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
    public function test_modal_especial_permite_buscar_personal_y_cargar_marcaciones_del_mes(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Valeria',
            'apellido' => 'Flores',
            'codigo_biometrico' => 'VAL-101',
            'area' => 'Atención al Cliente',
            'sucursal' => 'La Paz',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial')
            ->assertSet('showModalEspecial', true)
            ->set('modalSearch', 'VAL-101')
            ->assertSee('Valeria Flores')
            ->call('selectEmpleado', $empleado->id)
            ->assertSet('selectedEmpleadoId', $empleado->id);

        $diasMes = $component->get('diasMes');
        $this->assertIsArray($diasMes);
        $this->assertNotEmpty($diasMes);
    }

    public function test_modal_especial_permite_marcar_entrada_y_salida_manual_y_designa_como_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Mauricio',
            'apellido' => 'Paredes',
            'codigo_biometrico' => 'MAU-202',
            'area' => 'Ventas',
            'sucursal' => 'El Alto',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $fechaPrueba = now()->startOfMonth()->toDateString();

        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial', $empleado->id)
            ->set("diasMes.{$fechaPrueba}.hora_entrada", '08:35')
            ->set("diasMes.{$fechaPrueba}.hora_salida", '18:00')
            ->call('guardarMarcacionDia', $fechaPrueba)
            ->assertHasNoErrors();

        // El empleado debe quedar automáticamente con es_especial = true
        $this->assertTrue($empleado->fresh()->es_especial);

        // El registro de asistencia debe haberse creado como Especial
        $registro = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', $fechaPrueba)
            ->first();

        $this->assertNotNull($registro);
        $this->assertEquals('08:35:00', $registro->hora_entrada);
        $this->assertEquals('18:00:00', $registro->hora_salida);
        $this->assertEquals('Especial', $registro->tipo_verificacion);
    }

    public function test_modal_especial_puede_completar_salidas_pendientes_con_horario_habitual(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Javier',
            'apellido' => 'Siles',
            'codigo_biometrico' => 'JAV-303',
            'area' => 'Planta',
            'sucursal' => 'La Paz',
            'es_especial' => true,
            'hora_entrada_programada' => '08:00:00',
            'hora_salida_programada' => '17:00:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        // Simular que el empleado marcó entrada a las 08:02 pero salió sin marcar
        $fechaPrueba = now()->startOfMonth()->toDateString();
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => $fechaPrueba,
            'hora_entrada' => '08:02:00',
            'hora_salida' => null, // Sin salida
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Solo entrada',
            'created_by' => $user->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial', $empleado->id)
            ->call('aplicarSalidaHabitualPendientes');

        // La hora de salida en el array debe ser la habitual del empleado (17:00)
        $diasMes = $component->get('diasMes');
        $this->assertEquals('17:00', $diasMes[$fechaPrueba]['hora_salida']);

        // Guardar el día
        $component->call('guardarMarcacionDia', $fechaPrueba)
            ->assertHasNoErrors();

        $registroActualizado = RegistroAsistencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', $fechaPrueba)
            ->first();

        $this->assertEquals('08:02:00', $registroActualizado->hora_entrada);
        $this->assertEquals('17:00:00', $registroActualizado->hora_salida);
        $this->assertEquals('Especial', $registroActualizado->tipo_verificacion);
    }

    public function test_importacion_biometrica_no_actualiza_marcaciones_de_personal_especial(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Lucia',
            'apellido' => 'Mendoza',
            'codigo_biometrico' => 'LUC-404',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'es_especial' => true, // Personal especial
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $fechaOperativa = now()->toDateString();

        // RRHH le asignó manualmente su marcación especial: 08:30 a 18:30
        $registroEspecial = RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => $fechaOperativa,
            'hora_entrada' => '08:30:00',
            'hora_salida' => '18:30:00',
            'tipo_verificacion' => 'Especial',
            'estado_marcacion' => 'Marcacion completa',
            'evento_biometrico' => 'Ingreso y salida especial RRHH',
            'observacion' => 'Marcación manual autorizada RRHH - Personal Especial',
            'created_by' => $user->id,
        ]);

        // Ahora simulamos una importación biométrica del dispositivo para este empleado
        // con horas distintas (ej. entrada 09:15 y salida 16:00)
        $service = app(\App\Services\ImportacionBiometricaService::class);
        $device = [
            'ip' => '192.168.1.200',
            'branch' => 'La Paz',
            'department' => 'Operaciones',
        ];
        $rows = [
            [
                'codigo' => 'LUC-404',
                'nombre' => 'Lucia',
                'apellido' => 'Mendoza',
                'nombre_completo' => 'Lucia Mendoza',
                'fecha_hora' => $fechaOperativa . ' 09:15:00',
                'punch' => '0',
                'estado' => '0',
                'verificacion' => '1',
            ],
            [
                'codigo' => 'LUC-404',
                'nombre' => 'Lucia',
                'apellido' => 'Mendoza',
                'nombre_completo' => 'Lucia Mendoza',
                'fecha_hora' => $fechaOperativa . ' 16:00:00',
                'punch' => '1',
                'estado' => '1',
                'verificacion' => '1',
            ],
        ];

        $service->importarMarcacionesBiometrico($device, $rows, $user);

        // Verificamos que el registro de asistencia NO fue sobreescrito ni modificado por la importación
        $registroVerificado = RegistroAsistencia::query()->findOrFail($registroEspecial->id);
        $this->assertEquals('08:30:00', $registroVerificado->hora_entrada);
        $this->assertEquals('18:30:00', $registroVerificado->hora_salida);
        $this->assertEquals('Especial', $registroVerificado->tipo_verificacion);
        $this->assertEquals('Marcación manual autorizada RRHH - Personal Especial', $registroVerificado->observacion);
    }

    public function test_guardar_todo_el_mes_persiste_multiples_dias_correctamente(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Esteban',
            'apellido' => 'Quisbert',
            'codigo_biometrico' => 'EST-505',
            'area' => 'Mantenimiento',
            'sucursal' => 'La Paz',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $dia1 = now()->startOfMonth()->toDateString();
        $dia2 = now()->startOfMonth()->addDays(1)->toDateString();
        $dia3 = now()->startOfMonth()->addDays(2)->toDateString();

        $component = Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial', $empleado->id)
            ->set("diasMes.{$dia1}.hora_entrada", '08:30')
            ->set("diasMes.{$dia1}.hora_salida", '17:30')
            ->set("diasMes.{$dia2}.hora_entrada", '08:45')
            ->set("diasMes.{$dia2}.hora_salida", '18:00')
            ->set("diasMes.{$dia3}.hora_entrada", '09:00')
            ->set("diasMes.{$dia3}.hora_salida", '18:15')
            ->call('guardarTodoElMes')
            ->assertHasNoErrors();

        // Verificar que los 3 registros se crearon en la base de datos
        $r1 = RegistroAsistencia::query()->where('empleado_id', $empleado->id)->whereDate('fecha', $dia1)->first();
        $r2 = RegistroAsistencia::query()->where('empleado_id', $empleado->id)->whereDate('fecha', $dia2)->first();
        $r3 = RegistroAsistencia::query()->where('empleado_id', $empleado->id)->whereDate('fecha', $dia3)->first();

        $this->assertNotNull($r1, 'Día 1 no fue guardado');
        $this->assertNotNull($r2, 'Día 2 no fue guardado');
        $this->assertNotNull($r3, 'Día 3 no fue guardado');

        $this->assertEquals('08:30:00', $r1->hora_entrada);
        $this->assertEquals('17:30:00', $r1->hora_salida);
        $this->assertEquals('08:45:00', $r2->hora_entrada);
        $this->assertEquals('18:00:00', $r2->hora_salida);
        $this->assertEquals('09:00:00', $r3->hora_entrada);
        $this->assertEquals('18:15:00', $r3->hora_salida);

        // El empleado debe ser especial
        $this->assertTrue($empleado->fresh()->es_especial);

        // Al cerrar y volver a abrir, los datos deben seguir apareciendo en el modal
        $component->call('closeModalEspecial')
            ->assertSet('showModalEspecial', false)
            ->call('openModalEspecial')
            ->assertSet('showModalEspecial', true);

        $diasMesRecargado = $component->get('diasMes');
        $this->assertEquals('08:30', $diasMesRecargado[$dia1]['hora_entrada']);
        $this->assertEquals('17:30', $diasMesRecargado[$dia1]['hora_salida']);
        $this->assertEquals('08:45', $diasMesRecargado[$dia2]['hora_entrada']);
        $this->assertEquals('18:00', $diasMesRecargado[$dia2]['hora_salida']);
        $this->assertEquals('09:00', $diasMesRecargado[$dia3]['hora_entrada']);
        $this->assertEquals('18:15', $diasMesRecargado[$dia3]['hora_salida']);
    }

    public function test_modal_especial_busqueda_insensible_a_mayusculas_y_minusculas(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Marco',
            'apellido' => 'Quispe',
            'codigo_biometrico' => 'MQ-900',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'es_especial' => false,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        // 1. Buscar en minúsculas "marco"
        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial')
            ->set('modalSearch', 'marco')
            ->assertSee('Marco Quispe')
            ->assertSee('MQ-900');

        // 2. Buscar en mayúsculas "MARCO"
        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial')
            ->set('modalSearch', 'MARCO')
            ->assertSee('Marco Quispe');

        // 3. Buscar con nombre y apellido en minúsculas "marco quispe"
        Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial')
            ->set('modalSearch', 'marco quispe')
            ->assertSee('Marco Quispe');
    }

    public function test_modal_especial_excluye_sabados_y_domingos(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Susana',
            'apellido' => 'Torrez',
            'codigo_biometrico' => 'SUS-888',
            'area' => 'Ventas',
            'sucursal' => 'La Paz',
            'es_especial' => true,
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '17:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->call('openModalEspecial', $empleado->id);

        $diasMes = $component->get('diasMes');
        $this->assertNotEmpty($diasMes);

        // Ningún día en diasMes debe ser sábado o domingo
        foreach ($diasMes as $fecha => $dia) {
            $carbon = Carbon::parse($fecha);
            $this->assertFalse($carbon->isWeekend(), "El día {$fecha} ({$dia['dia_nombre']}) no debería estar incluido en la lista de días laborales.");
        }
    }

    public function test_modal_resumen_cambios_muestra_modificaciones_realizadas(): void
    {
        $user = $this->crearUsuarioConPermiso();

        $empleado = Empleado::query()->create([
            'nombre' => 'Roberto',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'RGB-777',
            'area' => 'Auditoría',
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
            'observacion' => 'Cambio manual especial RRHH',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Al entrar a la página, la pestaña por defecto es 'personal'
        $component = Livewire::actingAs($user)
            ->test(PersonalEspecialPage::class)
            ->assertSet('tab', 'personal')
            ->assertSee('Roberto Gomez')
            ->assertSee('RGB-777')
            ->call('openResumenCambiosModal', $empleado->id)
            ->assertSet('showResumenCambiosModal', true)
            ->assertSee('Resumen de Cambios y Marcaciones Registradas')
            ->assertSee('Cambio manual especial RRHH')
            ->call('closeResumenCambiosModal')
            ->assertSet('showResumenCambiosModal', false);
    }
}
