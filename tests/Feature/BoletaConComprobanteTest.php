<?php

namespace Tests\Feature;

use App\Livewire\ConsultaCarnetPage;
use App\Livewire\IncidenciasPage;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BoletaConComprobanteTest extends TestCase
{
    use RefreshDatabase;

    private function crearEmpleado(?string $email = 'carlos.mendoza@correos.gob.bo', string $codigo = '1234567', string $nombre = 'Carlos', string $apellido = 'Mendoza'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'codigo_biometrico' => $codigo,
            'email' => $email,
            'cargo' => 'ANALISTA DE SISTEMAS',
            'area' => 'TECNOLOGÍA',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2025-01-01',
        ]);
    }

    private function crearAdmin(): User
    {
        $user = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        Permission::findOrCreate('gestionar personal');
        $user->givePermissionTo('gestionar personal');

        return $user;
    }

    public function test_solicitud_boleta_falla_si_no_se_sube_comprobante(): void
    {
        $empleado = $this->crearEmpleado();

        Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')
            ->call('abrirBoletaModal')
            ->set('boletaMotivo', 'Consulta medica en la CNS')
            ->set('boletaTipo', 'medico')
            ->set('comprobante', null)
            ->assertSee('Completa el motivo y sube la foto del comprobante para habilitar el envío')
            ->assertDontSee('Enviar a RR.HH. y Descargar Boleta PDF')
            ->call('descargarPdf')
            ->assertHasErrors(['comprobante' => 'required']);

        $this->assertSame(0, PermisoLaboral::query()->count());
        $this->assertSame(0, PermisoComprobante::query()->count());
    }

    public function test_empleado_sube_comprobante_guarda_en_bd_y_genera_pdf(): void
    {
        Storage::fake('public');
        $empleado = $this->crearEmpleado();

        $imagenFalsa = UploadedFile::fake()->image('certificado_medico.png', 600, 400);

        $response = Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')
            ->call('abrirBoletaModal')
            ->set('boletaMotivo', 'Baja medica de 1 dia')
            ->set('boletaTipo', 'medico')
            ->set('boletaDesdeFecha', '02/09/2026')
            ->set('boletaDesdeHora', '08:30')
            ->set('boletaHastaFecha', '02/09/2026')
            ->set('boletaHastaHora', '16:30')
            ->set('comprobante', $imagenFalsa)
            ->assertSee('Enviar a RR.HH. y Descargar Boleta PDF')
            ->call('descargarPdf');

        $response->assertHasNoErrors();

        // Verificar que se creó el PermisoLaboral
        $this->assertDatabaseHas('permisos_laborales', [
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'estado' => 'pendiente',
        ]);

        $permiso = PermisoLaboral::query()->first();
        $this->assertNotNull($permiso);

        // Verificar que se guardó el comprobante en la tabla relacional
        $this->assertDatabaseHas('permiso_comprobantes', [
            'permiso_laboral_id' => $permiso->id,
            'nombre_original' => 'certificado_medico.png',
        ]);

        $comprobante = PermisoComprobante::query()->first();
        $this->assertNotNull($comprobante);
        Storage::disk('public')->assertExists($comprobante->ruta_archivo);
        $this->assertNotEmpty($comprobante->archivo_base64);
        $this->assertStringStartsWith('data:image/', $comprobante->url);

        // Verificar registro de auditoría
        $this->assertDatabaseHas('auditorias', [
            'modulo' => 'Incidencias',
            'accion' => 'solicitar_boleta_empleado',
        ]);
    }

    public function test_administrador_descarga_boleta_pdf_desde_incidencias(): void
    {
        Storage::fake('public');
        $admin = $this->crearAdmin();
        $empleado = $this->crearEmpleado();

        $permiso = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'pendiente',
            'fecha_inicio' => '2026-09-02',
            'fecha_fin' => '2026-09-02',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '11:00:00',
            'minutos_contabilizados' => 120,
            'motivo' => 'MEDICO: Consulta especialista',
        ]);

        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $ruta = 'comprobantes/receta.png';
        Storage::disk('public')->put($ruta, $pngBytes);

        PermisoComprobante::query()->create([
            'permiso_laboral_id' => $permiso->id,
            'ruta_archivo' => $ruta,
            'nombre_original' => 'receta.png',
            'mime_type' => 'image/png',
            'tamano_bytes' => strlen($pngBytes),
        ]);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('descargarBoletaPdf', $permiso->id)
            ->assertFileDownloaded('Boleta_Oficial_carlos-mendoza_20260902.pdf');

        // Test ver comprobante en modal
        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('verComprobante', $permiso->id)
            ->assertSet('showComprobanteModal', true)
            ->assertSet('modalComprobanteUrl', fn ($url) => !empty($url));

        // Test aprobar solicitud
        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('cambiarEstado', $permiso->id, 'aprobado');

        $this->assertSame('aprobado', $permiso->fresh()->estado);

        // Bloqueo inmutable: ya no se puede cambiar a rechazado una vez aprobado
        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('cambiarEstado', $permiso->id, 'rechazado');

        $this->assertSame('aprobado', $permiso->fresh()->estado);

        // Test rechazar otra solicitud pendiente
        $permiso2 = PermisoLaboral::create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'pendiente',
            'fecha_inicio' => '2026-09-03',
            'fecha_fin' => '2026-09-03',
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'minutos_contabilizados' => 60,
            'motivo' => 'Asunto personal',
        ]);

        Livewire::actingAs($admin)
            ->test(IncidenciasPage::class)
            ->call('cambiarEstado', $permiso2->id, 'rechazado');

        $this->assertSame('rechazado', $permiso2->fresh()->estado);
    }

    public function test_empleado_justifica_omision_desde_perfil_horas_con_datos_precargados(): void
    {
        Storage::fake('public');
        $empleado = $this->crearEmpleado();

        $imagenFalsa = UploadedFile::fake()->image('justificativo_omision.jpg', 600, 400);

        $component = Livewire::test(\App\Livewire\PerfilHorasPage::class, ['empleado' => $empleado])
            ->call('abrirBoletaParaOmision', '15/08/2026', '--:--', '16:30', '08:30 - 16:30')
            ->assertSet('showBoletaModal', true)
            ->assertSet('boletaDesdeFecha', '2026-08-15')
            ->assertSet('boletaDesdeHora', '08:30')
            ->assertSet('boletaHastaFecha', '2026-08-15')
            ->assertSet('boletaNombre', $empleado->nombre_completo)
            ->assertSet('boletaCiudad', mb_strtoupper($empleado->sucursal))
            ->set('comprobante', $imagenFalsa)
            ->call('descargarPdf');

        $component->assertHasNoErrors();
        $component->assertFileDownloaded();

        $this->assertDatabaseHas('permisos_laborales', [
            'empleado_id' => $empleado->id,
            'estado' => 'pendiente',
        ]);

        $permisoCreado = PermisoLaboral::query()->where('empleado_id', $empleado->id)->latest('id')->first();
        $this->assertNotNull($permisoCreado);
        $this->assertSame('2026-08-15', $permisoCreado->fecha_inicio->format('Y-m-d'));
        $this->assertSame('08:30:00', $permisoCreado->hora_inicio);
    }

    public function test_solicitud_boleta_abre_popup_si_empleado_no_tiene_correo(): void
    {
        Storage::fake('public');
        $empleado = $this->crearEmpleado(email: null);
        $imagenFalsa = UploadedFile::fake()->image('justificativo.png', 600, 400);

        // 1. Al intentar descargar sin correo en BD ni en formulario, se abre el popup modal
        $component = Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')
            ->call('abrirBoletaModal')
            ->set('boletaMotivo', 'Comision de servicio')
            ->set('boletaTipo', 'comision')
            ->set('comprobante', $imagenFalsa)
            ->call('descargarPdf')
            ->assertSet('showPedirEmailModal', true)
            ->assertSee('¿Dónde te llegará el estado de tu boleta?');

        $this->assertSame(0, PermisoLaboral::query()->count());

        // 2. Pepito completa su correo en el popup y confirma
        $component->set('boletaEmail', 'pepito@correos.gob.bo')
            ->call('confirmarEmailYDescargar')
            ->assertHasNoErrors()
            ->assertSet('showPedirEmailModal', false);

        // El empleado ahora tiene el correo guardado permanentemente en la base de datos
        $this->assertSame('pepito@correos.gob.bo', $empleado->fresh()->email);
        $this->assertSame(1, PermisoLaboral::query()->count());
    }

    public function test_usuario_no_puede_cambiar_correo_ya_establecido(): void
    {
        Storage::fake('public');
        $empleado = $this->crearEmpleado(email: 'original@correos.gob.bo');
        $imagenFalsa = UploadedFile::fake()->image('justificativo.png', 600, 400);

        Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')
            ->call('abrirBoletaModal')
            ->set('boletaMotivo', 'Comision de prueba')
            ->set('boletaTipo', 'comision')
            ->set('boletaEmail', 'intento_cambio@correos.gob.bo')
            ->set('comprobante', $imagenFalsa)
            ->call('descargarPdf')
            ->assertHasNoErrors();

        // El correo del empleado se mantiene intacto
        $this->assertSame('original@correos.gob.bo', $empleado->fresh()->email);
    }

    public function test_consulta_carnet_muestra_datos_iniciales_con_correo_o_sin_correo(): void
    {
        $conCorreo = $this->crearEmpleado(email: 'empleado.con@correos.gob.bo');

        Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', $conCorreo->codigo_biometrico)
            ->assertSee($conCorreo->nombre_completo)
            ->assertSee('empleado.con@correos.gob.bo')
            ->assertSee('✓ Registrado');

        $this->crearEmpleado(email: null, codigo: '998877', nombre: 'JUAN', apellido: 'SIN CORREO');

        Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '998877')
            ->assertSee('JUAN SIN CORREO')
            ->assertSee('Sin correo registrado')
            ->assertSee('⚠️ Pendiente');
    }

    public function test_boleta_rango_varios_dias_bloquea_horas_y_calcula_dias(): void
    {
        Storage::fake('public');
        $empleado = $this->crearEmpleado();
        $imagenFalsa = UploadedFile::fake()->image('comprobante_comision.jpg', 600, 400);

        $component = Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')
            ->call('abrirBoletaModal')
            ->set('boletaDesdeFecha', '2026-09-10')
            ->set('boletaHastaFecha', '2026-09-12')
            ->assertSet('boletaTiempoSolicitado', '3 DÍAS')
            ->assertSet('boletaDesdeHora', '')
            ->assertSet('boletaHastaHora', '')
            ->assertSet('esRangoDias', true)
            ->set('boletaMotivo', 'Comisión departamental en Oruro')
            ->set('boletaTipo', 'comision')
            ->set('comprobante', $imagenFalsa)
            ->call('descargarPdf')
            ->assertHasNoErrors()
            ->assertFileDownloaded();

        $permiso = PermisoLaboral::query()->where('empleado_id', $empleado->id)->latest('id')->first();
        $this->assertNotNull($permiso);
        $this->assertSame('dias', $permiso->alcance);
        $this->assertNull($permiso->hora_inicio);
        $this->assertNull($permiso->hora_fin);
        $this->assertSame('2026-09-10', $permiso->fecha_inicio->format('Y-m-d'));
        $this->assertSame('2026-09-12', $permiso->fecha_fin->format('Y-m-d'));
    }
}

