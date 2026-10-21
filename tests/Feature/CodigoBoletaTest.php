<?php

namespace Tests\Feature;

use App\Livewire\ConsultaCarnetPage;
use App\Livewire\PerfilHorasPage;
use App\Mail\CodigoBoletaMailable;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Services\CodigoBoletaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CodigoBoletaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function empleado(string $codigo = '001234', ?string $email = 'ana@correos.gob.bo'): Empleado
    {
        return Empleado::create([
            'nombre' => 'Ana', 'apellido' => 'Prueba', 'codigo_biometrico' => $codigo,
            'email' => $email, 'area' => 'Operaciones', 'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30', 'hora_salida_programada' => '16:30',
            'fecha_contratacion' => now()->toDateString(),
        ]);
    }

    private function formulario(Empleado $empleado, bool $perfil = false)
    {
        $component = $perfil
            ? Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            : Livewire::test(ConsultaCarnetPage::class)->set('carnet', $empleado->codigo_biometrico);

        return $component->call('abrirBoletaModal')->set('boletaCargo', 'Analista')->set('boletaMotivo', 'Comisión de servicio');
    }

    private function enviado(): string
    {
        return Mail::sent(CodigoBoletaMailable::class)->last()->codigo;
    }

    private function rechaza(\Closure $action): void
    {
        try {
            $action();
            $this->fail('Debió rechazar la autenticación.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('codigoBoleta', $exception->errors());
        }
    }

    public function test_codigo_se_envia_al_correo_registrado_se_guarda_hasheado_y_solo_se_usa_una_vez(): void
    {
        \Illuminate\Support\Facades\Log::spy();
        $empleado = $this->empleado();
        $service = app(CodigoBoletaService::class);
        $service->enviar($empleado);
        $codigo = $this->enviado();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $codigo);
        Mail::assertSent(CodigoBoletaMailable::class, fn ($mail) => $mail->hasTo('ana@correos.gob.bo'));
        $registro = DB::table('boleta_codigos')->first();
        $this->assertNotSame($codigo, $registro->codigo_hash);
        $this->assertTrue(Hash::check($codigo, $registro->codigo_hash));
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')->once()->with(
            'Código de boleta aceptado por el servidor SMTP.',
            \Mockery::on(fn (array $context) => array_key_exists('message_id', $context)
                && ! str_contains(json_encode($context), $codigo))
        );
        $this->assertSame('generada', $service->consumir($empleado, $codigo, fn () => 'generada'));
        $this->rechaza(fn () => $service->consumir($empleado, $codigo, fn () => $this->fail('Se reutilizó el código.')));
    }

    public function test_codigo_no_sirve_para_otro_empleado_ni_otra_sesion_y_vence_a_los_diez_minutos(): void
    {
        $empleado = $this->empleado();
        $service = app(CodigoBoletaService::class);
        $service->enviar($empleado);
        $codigo = $this->enviado();
        $this->rechaza(fn () => $service->consumir($this->empleado('otro', 'otro@correos.gob.bo'), $codigo, fn () => true));
        $sessionId = session()->getId();
        session()->setId(str_repeat('x', 40));
        $this->rechaza(fn () => $service->consumir($empleado, $codigo, fn () => true));
        session()->setId($sessionId);
        $this->travel(10)->minutes();
        $this->rechaza(fn () => $service->consumir($empleado, $codigo, fn () => true));
    }

    public function test_cinco_intentos_incorrectos_bloquean_el_codigo_aunque_luego_se_escriba_correctamente(): void
    {
        $empleado = $this->empleado();
        $service = app(CodigoBoletaService::class);
        $service->enviar($empleado);
        for ($i = 0; $i < 5; $i++) {
            $this->rechaza(fn () => $service->consumir($empleado, 'incorrecto', fn () => true));
        }
        $this->assertSame(5, DB::table('boleta_codigos')->value('intentos'));
        $this->rechaza(fn () => $service->consumir($empleado, $this->enviado(), fn () => true));
    }

    public function test_reenvio_tiene_espera_e_invalida_codigo_anterior_y_un_cambio_de_correo_lo_invalida(): void
    {
        $empleado = $this->empleado();
        $service = app(CodigoBoletaService::class);
        $service->enviar($empleado);
        $anterior = $this->enviado();
        $this->rechaza(fn () => $service->enviar($empleado));
        Mail::assertSentCount(1);
        $this->travel(61)->seconds();
        $service->enviar($empleado);
        $this->assertSame(1, DB::table('boleta_codigos')->count());
        $nuevo = $this->enviado();
        $this->assertNotSame($anterior, $nuevo);
        $this->rechaza(fn () => $service->consumir($empleado, $anterior, fn () => true));
        $empleado->update(['email' => 'nuevo@correos.gob.bo']);
        $this->rechaza(fn () => $service->consumir($empleado, $nuevo, fn () => true));
    }

    public function test_sin_correo_rrhh_no_hay_envio_ni_autoregistro_desde_el_portal(): void
    {
        $empleado = $this->empleado(email: null);
        $component = $this->formulario($empleado)->set('boletaEmail', 'intruso@example.com')
            ->call('enviarCodigoBoleta')->assertHasErrors('codigoBoleta')
            ->call('confirmarEmailYDescargar')->assertHasErrors('codigoBoleta');
        $this->assertNull($empleado->fresh()->email);
        $this->assertDatabaseCount('permisos_laborales', 0);
        Mail::assertNothingSent();
    }

    public function test_error_smtp_no_deja_codigo_valido(): void
    {
        $empleado = $this->empleado();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Fallo SMTP'));
        $service = app(CodigoBoletaService::class);
        $this->rechaza(fn () => $service->enviar($empleado));
        $this->assertDatabaseCount('boleta_codigos', 0);
    }

    public function test_error_al_generar_no_consume_el_codigo(): void
    {
        $empleado = $this->empleado();
        $service = app(CodigoBoletaService::class);
        $service->enviar($empleado);
        $codigo = $this->enviado();
        try {
            $service->consumir($empleado, $codigo, function () { throw new \RuntimeException('Fallo PDF'); });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo PDF', $exception->getMessage());
        }
        $this->assertNull(DB::table('boleta_codigos')->value('used_at'));
        $this->assertTrue($service->consumir($empleado, $codigo, fn () => true));
    }

    public function test_certificado_invalido_muestra_la_causa_sin_registrar_secretos_ni_crear_codigo(): void
    {
        $empleado = $this->empleado();
        \Illuminate\Support\Facades\Log::spy();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('certificate verify failed; secreto-smtp'));
        try {
            app(CodigoBoletaService::class)->enviar($empleado);
            $this->fail('Debió rechazar el envío sin certificado válido.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('reiniciar el servidor PHP', $exception->errors()['codigoBoleta'][0]);
        }
        $this->assertDatabaseCount('boleta_codigos', 0);
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->with(
            'No se pudo enviar el código de boleta.',
            \Mockery::on(fn (array $context) => $context['motivo'] === 'certificado_tls'
                && ! str_contains(json_encode($context), 'secreto-smtp'))
        );
    }

    public function test_ambos_portales_exigen_codigo_para_pdf_y_el_pdf_consumido_no_puede_repetirse(): void
    {
        foreach ([false, true] as $perfil) {
            $empleado = $this->empleado($perfil ? 'perfil' : 'consulta', $perfil ? 'perfil@correos.gob.bo' : 'consulta@correos.gob.bo');
            $component = $this->formulario($empleado, $perfil)
                ->call('descargarPdf')->assertHasErrors('codigoBoleta');
            $component->set('boletaEmail', 'intruso@example.com')->call('enviarCodigoBoleta')->assertHasNoErrors();
            $codigo = $this->enviado();
            $component->set('codigoBoleta', $codigo)->call('descargarPdf')->assertHasNoErrors()->assertFileDownloaded();
            $component->set('codigoBoleta', $codigo)->call('descargarPdf')->assertHasErrors('codigoBoleta');
        }
        $this->assertDatabaseCount('permisos_laborales', 2);
        $this->assertSame(0, Mail::sent(CodigoBoletaMailable::class, fn ($mail) => $mail->hasTo('intruso@example.com'))->count());
    }

    public function test_excel_tambien_exige_codigo_y_no_permite_reutilizarlo_para_pdf(): void
    {
        $component = $this->formulario($this->empleado())
            ->call('descargarExcel')->assertHasErrors('codigoBoleta')->call('enviarCodigoBoleta');
        $codigo = $this->enviado();
        $component->set('codigoBoleta', $codigo)->call('descargarExcel')->assertHasNoErrors()->assertFileDownloaded();
        $component->set('codigoBoleta', $codigo)->call('descargarPdf')->assertHasErrors('codigoBoleta');
        $this->assertDatabaseCount('permisos_laborales', 0);
    }

    public function test_id_de_empleado_no_es_manipulable_y_otra_boleta_no_se_puede_descargar(): void
    {
        $empleado = $this->empleado();
        $otro = $this->empleado('otro', 'otro@correos.gob.bo');
        $incidencia = PermisoLaboral::create([
            'empleado_id' => $otro->id, 'tipo' => 'permiso', 'fecha_inicio' => now(), 'fecha_fin' => now(), 'motivo' => 'Personal',
        ]);
        $this->formulario($empleado)->call('descargarBoletaPdf', $incidencia->id)->assertForbidden();
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        $this->formulario($empleado)->set('empleadoId', $otro->id);
    }
}
