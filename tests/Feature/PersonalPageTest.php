<?php

namespace Tests\Feature;

use App\Mail\ComunicadoPersonalMailable;
use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PersonalPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_page_supports_search_on_sqlite(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        Empleado::query()->create([
            'nombre' => 'Marco',
            'apellido' => 'Rojas',
            'codigo_biometrico' => 'MARC-001',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Empleado::query()->create([
            'nombre' => 'Lucia',
            'apellido' => 'Fernandez',
            'codigo_biometrico' => 'LUC-002',
            'area' => 'Personal',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('search', 'marc')
            ->assertSee('Marco')
            ->assertDontSee('Lucia');
    }

    public function test_personal_page_groups_la_paz_aliases_under_one_filter(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Quispe',
            'codigo_biometrico' => 'LP-001',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Empleado::query()->create([
            'nombre' => 'Bruno',
            'apellido' => 'Mamani',
            'codigo_biometrico' => 'LP-002',
            'area' => 'Personal',
            'sucursal' => 'LaPaz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('sucursalFiltro', 'La Paz')
            ->assertSee('Ana')
            ->assertSee('Bruno')
            ->assertSet('sucursalFiltro', 'La Paz');
    }

    public function test_personal_page_does_not_count_today_open_shift_as_forgotten_exit(): void
    {
        $this->travelTo(Carbon::parse('2026-08-10 10:00:00'));

        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Yurguen',
            'apellido' => 'Terrazas',
            'codigo_biometrico' => '9066508',
            'area' => 'Personal',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:28:00',
            'hora_salida' => null,
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Entrada',
            'evento_biometrico' => 'Sin evento',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->call('openDetailModal', $empleado->id)
            ->assertSet('detailEmpleado.estado_hoy', 'En su puesto')
            ->assertSet('detailEmpleado.olvidos_marcacion', 0)
            ->assertSee('En su puesto');
    }

    public function test_personal_page_descarga_excel_marcaciones_correctamente(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'CG-100',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('personal-page')
            ->set('vista', 'marcaciones')
            ->call('descargarExcelMarcaciones');

        $component->assertFileDownloaded();
    }

    public function test_personal_page_descarga_pdf_marcaciones_resalta_omisiones(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'CG-100',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        // Registro con omisión de salida
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->subDay()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => null,
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Entrada',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('personal-page')
            ->set('vista', 'marcaciones')
            ->call('descargarPdfMarcaciones');

        $component->assertFileDownloaded();
    }

    public function test_personal_page_descarga_pdf_y_excel_control_correctamente(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        $empleado = Empleado::query()->create([
            'nombre' => 'Laura',
            'apellido' => 'Vargas',
            'codigo_biometrico' => 'LV-200',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('descargarPdfControl')
            ->assertFileDownloaded();

        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('descargarExcelControl')
            ->assertFileDownloaded();
    }

    public function test_control_vista_excluye_empleados_inactivos_de_las_metricas_y_listado(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();

        // Empleado ACTIVO
        $activo = Empleado::query()->create([
            'nombre' => 'Empleado',
            'apellido' => 'Activo',
            'codigo_biometrico' => 'ACT-001',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $activo->id,
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        // Empleado ANTIGUO INACTIVO (sin despido formal, pero sin marcaciones desde hace 120 días)
        $inactivo = Empleado::query()->create([
            'nombre' => 'Empleado',
            'apellido' => 'AntiguoInactivo',
            'codigo_biometrico' => 'INA-999',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->subMonths(6)->toDateString(),
            'created_by' => $user->id,
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $inactivo->id,
            'fecha' => now()->subDays(120)->toDateString(),
            'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00',
            'tipo_verificacion' => 'Huella',
            'estado_marcacion' => 'Normal',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        // Verificar que en vista control sólo se liste y contabilice al activo
        Livewire::test('personal-page')
            ->set('vista', 'control')
            ->call('seleccionarSucursal', 'La Paz')
            ->assertSee('Empleado Activo')
            ->assertDontSee('Empleado AntiguoInactivo')
            ->assertViewHas('sucursalKpis', function (array $kpis) {
                return ($kpis['total_empleados'] ?? 0) === 1;
            });
    }

    public function test_personal_page_can_create_edit_and_search_by_email(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        // 1. Crear personal con correo
        Livewire::test('personal-page')
            ->set('nombre', 'Carlos')
            ->set('apellido', 'Mamani')
            ->set('codigoBiometrico', 'BIO-999')
            ->set('email', 'carlos.mamani@correos.gob.bo')
            ->set('sucursal', 'La Paz')
            ->call('saveEmpleado')
            ->assertHasNoErrors();

        $empleado = Empleado::query()->where('codigo_biometrico', 'BIO-999')->firstOrFail();
        $this->assertSame('carlos.mamani@correos.gob.bo', $empleado->email);

        // 2. Buscar por correo
        Livewire::test('personal-page')
            ->set('search', 'carlos.mamani@correos.gob.bo')
            ->assertSee('Carlos')
            ->assertSee('BIO-999')
            ->assertSee('carlos.mamani@correos.gob.bo');

        // 3. Editar correo
        Livewire::test('personal-page')
            ->call('openEditModal', $empleado->id)
            ->assertSet('editEmail', 'carlos.mamani@correos.gob.bo')
            ->set('editEmail', 'carlos.nuevo@correos.gob.bo')
            ->call('updateEmpleado')
            ->assertHasNoErrors();

        $this->assertSame('carlos.nuevo@correos.gob.bo', $empleado->fresh()->email);
    }

    public function test_personal_page_opens_and_preselects_email_modal(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        // Sin filtro: abre en modo masivo
        Livewire::test('personal-page')
            ->call('openEmailModal')
            ->assertSet('showEmailModal', true)
            ->assertSet('emailTipoDestinatario', 'masivo')
            ->call('closeEmailModal')
            ->assertSet('showEmailModal', false);

        // Con sucursal pasada explícitamente o por filtro: abre en modo sucursal
        Livewire::test('personal-page')
            ->call('openEmailModal', 'Santa Cruz')
            ->assertSet('showEmailModal', true)
            ->assertSet('emailTipoDestinatario', 'sucursal')
            ->assertSet('emailSucursalSeleccionada', 'Santa Cruz');
    }

    public function test_personal_page_sends_bulk_emails_masivo(): void
    {
        Mail::fake();
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        $emp1 = Empleado::query()->create([
            'nombre' => 'Alicia',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'MAS-001',
            'email' => 'alicia@correos.gob.bo',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $emp2 = Empleado::query()->create([
            'nombre' => 'Bernardo',
            'apellido' => 'Sosa',
            'codigo_biometrico' => 'MAS-002',
            'email' => 'bernardo@correos.gob.bo',
            'area' => 'Operaciones',
            'sucursal' => 'Cochabamba',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        // Empleado sin correo
        Empleado::query()->create([
            'nombre' => 'Sin',
            'apellido' => 'Email',
            'codigo_biometrico' => 'MAS-003',
            'email' => null,
            'area' => 'Operaciones',
            'sucursal' => 'Oruro',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Livewire::test('personal-page')
            ->call('openEmailModal')
            ->set('emailTipoDestinatario', 'masivo')
            ->set('emailAsunto', 'Reunión General de Personal')
            ->set('emailMensaje', 'Estimados colegas, se les convoca a la reunión anual de personal este viernes.')
            ->call('enviarCorreosPersonal')
            ->assertHasNoErrors()
            ->assertSet('showEmailModal', false);

        Mail::assertSent(ComunicadoPersonalMailable::class, 2);
        Mail::assertSent(ComunicadoPersonalMailable::class, fn ($mail) => $mail->hasTo('alicia@correos.gob.bo'));
        Mail::assertSent(ComunicadoPersonalMailable::class, fn ($mail) => $mail->hasTo('bernardo@correos.gob.bo'));
    }

    public function test_personal_page_sends_emails_filtered_by_sucursal_only(): void
    {
        Mail::fake();
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        Empleado::query()->create([
            'nombre' => 'Daniel',
            'apellido' => 'Suarez',
            'codigo_biometrico' => 'SCZ-001',
            'email' => 'daniel.suarez@correos.gob.bo',
            'area' => 'Operaciones',
            'sucursal' => 'Santa Cruz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Empleado::query()->create([
            'nombre' => 'Elena',
            'apellido' => 'Mendoza',
            'codigo_biometrico' => 'LPZ-001',
            'email' => 'elena.mendoza@correos.gob.bo',
            'area' => 'Operaciones',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Livewire::test('personal-page')
            ->call('openEmailModal', 'Santa Cruz')
            ->set('emailAsunto', 'Mantenimiento en Sucursal Santa Cruz')
            ->set('emailMensaje', 'Informamos que la agencia Santa Cruz tendrá mantenimiento el fin de semana.')
            ->call('enviarCorreosPersonal')
            ->assertHasNoErrors()
            ->assertSet('showEmailModal', false);

        Mail::assertSent(ComunicadoPersonalMailable::class, 1);
        Mail::assertSent(ComunicadoPersonalMailable::class, fn ($mail) => $mail->hasTo('daniel.suarez@correos.gob.bo'));
        Mail::assertNotSent(ComunicadoPersonalMailable::class, fn ($mail) => $mail->hasTo('elena.mendoza@correos.gob.bo'));

        // Verificar auditoría
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'Personal',
            'accion' => 'comunicado_email',
        ]);
    }

    public function test_personal_page_email_modal_switches_tabs_and_loads_templates(): void
    {
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        Empleado::query()->create([
            'nombre' => 'Patricia',
            'apellido' => 'Vargas',
            'codigo_biometrico' => 'PV-001',
            'email' => 'patricia@correos.gob.bo',
            'area' => 'Recursos Humanos',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $component = Livewire::test('personal-page')
            ->call('openEmailModal')
            ->assertSet('emailModalTab', 'redactar')
            ->assertSet('emailPreviewDevice', 'desktop')
            ->call('cargarPlantillaEjemplo', 'invitacion_sistema')
            ->assertSet('emailAsunto', 'Invitación Oficial: Consulta de Asistencias, Atrasos y Faltas - Correos de Bolivia')
            ->call('setEmailModalTab', 'preview')
            ->assertSet('emailModalTab', 'preview')
            ->assertSee('Vista previa del correo')
            ->assertSee('zimbra')
            ->assertSee('Bandeja de Entrada')
            ->assertSee('http://172.65.10.55:8129/consulta-carnet');
    }

    public function test_personal_page_invitation_modal_opens_with_preview_and_sends_invitation(): void
    {
        Mail::fake();
        $user = $this->crearUsuarioConPermisoPersonal();
        $this->actingAs($user);

        Empleado::query()->create([
            'nombre' => 'Rodrigo',
            'apellido' => 'Perez',
            'codigo_biometrico' => 'RP-100',
            'email' => 'rodrigo.perez@correos.gob.bo',
            'area' => 'Distribución',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        Livewire::test('personal-page')
            ->call('openInvitacionModal')
            ->assertSet('showEmailModal', true)
            ->assertSet('emailModalTab', 'preview')
            ->assertSet('emailAsunto', 'Invitación Oficial: Consulta de Asistencias, Atrasos y Faltas - Correos de Bolivia')
            ->assertSee('http://172.65.10.55:8129/consulta-carnet')
            ->assertSee('Ingresar al Portal de Autoconsulta')
            ->call('enviarCorreosPersonal')
            ->assertHasNoErrors()
            ->assertSet('showEmailModal', false);

        Mail::assertSent(ComunicadoPersonalMailable::class, 1);
        Mail::assertSent(ComunicadoPersonalMailable::class, function ($mail) {
            return $mail->hasTo('rodrigo.perez@correos.gob.bo')
                && str_contains($mail->asunto, 'Invitación Oficial: Consulta de Asistencias')
                && str_contains($mail->mensaje, 'http://172.65.10.55:8129/consulta-carnet');
        });
    }

    private function crearUsuarioConPermisoPersonal(): User
    {
        $permission = Permission::findOrCreate('gestionar personal', 'web');

        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh@example.com',
            'password' => 'secret123',
        ]);

        $user->givePermissionTo($permission);

        return $user;
    }
}
