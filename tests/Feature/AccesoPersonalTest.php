<?php

namespace Tests\Feature;

use App\Livewire\AccesosPersonales;
use App\Livewire\Auth\CambiarPasswordPage;
use App\Livewire\ConsultaCarnetPage;
use App\Livewire\GestionAccesosPage;
use App\Livewire\MisHorasPage;
use App\Livewire\PerfilHorasPage;
use App\Models\Empleado;
use App\Models\User;
use App\Services\AccesoPersonalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AccesoPersonalTest extends TestCase
{
    use RefreshDatabase;

    private function empleado(string $codigo = '1234567', string $sucursal = 'La Paz'): Empleado
    {
        return Empleado::create([
            'nombre' => 'Persona', 'apellido' => $codigo, 'codigo_biometrico' => $codigo,
            'area' => 'Sistemas', 'sucursal' => $sucursal, 'hora_entrada_programada' => '08:30',
            'hora_salida_programada' => '16:30', 'fecha_contratacion' => now()->startOfMonth(),
        ]);
    }

    private function service(): AccesoPersonalService { return app(AccesoPersonalService::class); }

    private function cuenta(Empleado $empleado, bool $cambiar = false): User
    {
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $user = $this->service()->crearCuenta($empleado);
        if (! $cambiar) {
            $user->update(['password' => 'MiNuevaClave123!', 'must_change_password' => false]);
        }
        return $user;
    }

    private function admin(): User
    {
        \Spatie\Permission\Models\Role::findOrCreate('administrador', 'web');
        \Spatie\Permission\Models\Role::findOrCreate('gestor', 'web');
        $user = User::create(['name' => 'rrhh', 'email' => 'rrhh@example.test', 'password' => 'admin1234']);
        foreach (['gestionar accesos', 'gestionar personal'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }
        return $user;
    }

    public function test_acceso_bloqueado_por_defecto_y_reglas_con_prioridad_y_aliases(): void
    {
        $empleado = $this->empleado(sucursal: 'Regional La Paz');
        $this->assertFalse($this->service()->habilitado($empleado));
        $this->service()->guardar('todos', '*', true);
        $this->assertTrue($this->service()->habilitado($empleado));
        $this->service()->guardar('sucursal', 'La Paz', false);
        $this->assertFalse($this->service()->habilitado($empleado));
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $this->assertTrue($this->service()->habilitado($empleado));
        $this->service()->guardar('persona', (string) $empleado->id, false);
        $this->service()->guardar('sucursal', 'La Paz', true);
        $this->assertFalse($this->service()->habilitado($empleado));
    }

    public function test_primer_ingreso_crea_cuenta_solo_si_habilitada_y_exige_cambio(): void
    {
        Mail::fake();
        $empleado = $this->empleado();
        Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')->set('password', '1234567')->call('buscar')->assertHasErrors('carnet');
        $this->assertDatabaseCount('users', 0);
        $this->service()->guardar('persona', (string) $empleado->id, true);
        Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')->set('password', '1234567')->call('buscar')->assertHasNoErrors()->assertRedirect(route('password.change'));
        $user = User::firstOrFail();
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('1234567', $user->password));
        $this->assertNotSame('1234567', $user->password);
        $this->assertCount(0, $user->roles);
        $this->get(route('mis-horas'))->assertRedirect(route('password.change'));
        $this->get(route('personal'))->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_cambio_obligatorio_valida_actual_confirmacion_y_rechaza_biometrico(): void
    {
        $empleado = $this->empleado('12345678');
        $user = $this->cuenta($empleado, true);
        $this->actingAs($user);
        $component = Livewire::test(CambiarPasswordPage::class)
            ->set('current_password', 'incorrecta')->set('password', 'ClaveNueva123!')->set('password_confirmation', 'ClaveNueva123!')
            ->call('guardar')->assertHasErrors('current_password');
        $component->set('current_password', '12345678')->set('password_confirmation', 'otra')->call('guardar')->assertHasErrors('password');
        $component->set('password', '12345678')->set('password_confirmation', '12345678')->call('guardar')->assertHasErrors('password');
        $component->set('password', 'ClaveNueva123!')->set('password_confirmation', 'ClaveNueva123!')->call('guardar')
            ->assertHasNoErrors()->assertRedirect(URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('ClaveNueva123!', $user->fresh()->password));
    }

    public function test_empleado_solo_ve_mis_horas_y_no_puede_abrir_otros_modulos_ni_otro_perfil(): void
    {
        $propio = $this->empleado();
        $ajeno = $this->empleado('7654321');
        $user = $this->cuenta($propio);
        $this->actingAs($user);
        $this->get(route('mis-horas'))->assertRedirect($this->service()->destino($user));
        $this->get($this->service()->destino($user))->assertOk()->assertSee('Generar Boleta')->assertSee('Cambiar contraseña')->assertDontSee('Administracion de accesos');
        foreach (['inicio', 'dashboard', 'personal', 'accesos', 'reportes', 'incidencias'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(URL::signedRoute('perfil-horas', ['empleado' => $ajeno], absolute: false))->assertForbidden();
        Livewire::test(PerfilHorasPage::class, ['empleado' => $propio])->call('buscarOtroCarnet')->assertForbidden();
        Livewire::test(AccesosPersonales::class)->assertForbidden();
        $this->assertFalse($user->can('gestionar accesos'));
    }

    public function test_bloqueo_corta_sesion_abierta_y_solicitud_livewire(): void
    {
        $empleado = $this->empleado();
        $user = $this->cuenta($empleado);
        $this->actingAs($user);
        $component = Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado]);
        $this->service()->guardar('persona', (string) $empleado->id, false);
        $this->get(route('mis-horas'))->assertForbidden();
        $component->call('abrirBoletaModal')->assertForbidden();
    }

    public function test_grupo_de_prueba_restaurar_herencia_y_restablecer_sin_alterar_password_al_rehabilitar(): void
    {
        $empleado = $this->empleado();
        $otro = $this->empleado('2222222', 'Pando');
        $admin = $this->admin();
        $this->actingAs($admin);
        Livewire::test(GestionAccesosPage::class)->assertSee('Acceso de empleados a Mis horas');
        $component = Livewire::test(AccesosPersonales::class)
            ->set('personas', [$empleado->id, $otro->id])->call('aplicar', 'persona', 'habilitar')->assertHasNoErrors();
        $this->assertTrue($this->service()->habilitado($empleado));
        $this->assertTrue($this->service()->habilitado($otro));
        $user = $this->service()->crearCuenta($empleado);
        $user->update(['password' => 'MiClaveElegida123', 'must_change_password' => false]);
        $hash = $user->password;
        $component->set('personas', [$empleado->id])->call('aplicar', 'persona', 'bloquear');
        $component->set('personas', [$empleado->id])->call('aplicar', 'persona', 'habilitar');
        $this->assertSame($hash, $user->fresh()->password);
        $component->call('restablecer', $empleado->id)->assertHasNoErrors();
        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('1234567', $user->fresh()->password));
        $component->set('personas', [$otro->id])->call('aplicar', 'persona', 'heredar')->assertHasNoErrors();
        $this->assertFalse($this->service()->habilitado($otro));
    }

    public function test_login_posterior_no_acepta_password_inicial_ni_recrea_cuenta_y_limita_intentos(): void
    {
        $empleado = $this->empleado();
        $user = $this->cuenta($empleado);
        $component = Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')->set('password', '1234567');
        for ($i = 0; $i < 5; $i++) {
            $component->set('password', '1234567')->call('buscar')->assertHasErrors('carnet');
        }
        $component->set('password', 'MiNuevaClave123!')->call('buscar')->assertSee('Demasiados intentos');
        $this->travel(61)->seconds();
        $component->set('password', 'MiNuevaClave123!')->call('buscar')->assertHasNoErrors()->assertRedirect(URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false));
        $this->assertDatabaseCount('users', 1);
        $this->assertAuthenticatedAs($user);
    }

    public function test_no_hay_tabla_otp_y_consulta_anonima_esta_protegida(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('boleta_codigos'));
        $this->get(route('consulta-carnet'))->assertOk()->assertSee('Contraseña');
        $this->get(route('mis-horas'))->assertRedirect(route('consulta-carnet'));
    }

    public function test_flujo_http_livewire_completo_y_bloqueo_de_formulario_abierto(): void
    {
        $empleado = $this->empleado();
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $snapshot = function ($response, string $component): string {
            preg_match_all('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
            foreach ($matches[1] as $encoded) {
                $value = html_entity_decode($encoded, ENT_QUOTES, 'UTF-8');
                if (json_decode($value, true)['memo']['name'] === $component) {
                    return $value;
                }
            }
            $this->fail('No se encontró el componente '.$component);
        };
        $update = function (string $state, array $updates, string $method) {
            return $this->postJson('/livewire/update', ['components' => [[
                'snapshot' => $state, 'updates' => $updates,
                'calls' => [['path' => '', 'method' => $method, 'params' => []]],
            ]]], ['X-Livewire' => 'true']);
        };
        $login = $this->get(route('consulta-carnet'))->assertOk();
        $update($snapshot($login, 'consulta-carnet-page'), ['carnet' => '1234567', 'password' => '1234567'], 'buscar')
            ->assertOk()->assertJsonPath('components.0.effects.redirect', route('password.change'));
        $password = $this->get(route('password.change'))->assertOk();
        $update($snapshot($password, 'auth.cambiar-password-page'), [
            'current_password' => '1234567', 'password' => 'ClavePersonal123!', 'password_confirmation' => 'ClavePersonal123!',
        ], 'guardar')->assertOk()->assertJsonPath('components.0.effects.redirect', URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false));
        $horas = $this->get(URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false))->assertOk();
        $state = $snapshot($horas, 'perfil-horas-page');
        $this->service()->guardar('persona', (string) $empleado->id, false);
        $update($state, [], 'abrirBoletaModal')->assertForbidden();
    }

    public function test_empleado_genera_boleta_propia_sin_correo_ni_codigo_y_no_abre_solicitud_ajena(): void
    {
        Mail::fake();
        $empleado = $this->empleado();
        $otro = $this->empleado('8765432');
        $this->actingAs($this->cuenta($empleado));
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->call('abrirBoletaModal')->set('boletaCargo', 'Operador')->set('boletaMotivo', 'Consulta médica')
            ->call('descargarPdf')->assertHasNoErrors()->assertFileDownloaded();
        $this->assertDatabaseHas('permisos_laborales', ['empleado_id' => $empleado->id, 'estado' => 'pendiente']);
        $permiso = \App\Models\PermisoLaboral::query()->firstOrFail();
        $permiso->update(['empleado_id' => $otro->id]);
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])->call('descargarBoletaPdf', $permiso->id)->assertForbidden();
        Mail::assertNothingSent();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])->call('verComprobante', $permiso->id);
    }

    public function test_restablecer_revoca_sesion_previa_y_token_recordarme(): void
    {
        $empleado = $this->empleado();
        $user = $this->cuenta($empleado);
        $user->forceFill(['remember_token' => 'token-anterior'])->save();
        $passwordAnterior = $user->password;
        $this->actingAs($this->admin());
        Livewire::test(AccesosPersonales::class)->call('restablecer', $empleado->id)->assertHasNoErrors();
        $this->assertNotSame('token-anterior', $user->fresh()->remember_token);
        $this->actingAs($user->fresh())->withSession(['password_hash_web' => $passwordAnterior]);
        $this->get(route('mis-horas'))->assertRedirect(route('consulta-carnet'));
        $this->assertGuest();
    }

    public function test_consulta_carnet_exige_password_y_no_revela_ficha_antes_del_ingreso(): void
    {
        $empleado = $this->empleado();
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $component = Livewire::test(ConsultaCarnetPage::class)
            ->set('carnet', '1234567')->assertSet('empleadoEncontrado', null)
            ->assertDontSee($empleado->nombre_completo)->call('buscar')->assertHasErrors('password');
        $component->set('password', 'incorrecta')->call('buscar')->assertHasErrors('carnet')->assertSet('password', '');
        $component->call('abrirBoletaModal')->assertHasErrors('password')->assertSet('showBoletaModal', false);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_portal_personal_no_admite_cuenta_rrhh_y_login_administrativo_no_admite_cuenta_personal(): void
    {
        $empleado = $this->empleado();
        $personal = $this->cuenta($empleado);
        Livewire::test(\App\Livewire\Auth\LoginPage::class)->set('email', '1234567')->set('password', 'MiNuevaClave123!')
            ->call('login')->assertHasErrors('email');
        $admin = $this->admin();
        $admin->update(['empleado_id' => $empleado->id]);
        // Otro empleado vinculado a RRHH conserva su acceso administrativo existente.
        $otro = $this->empleado('2222222');
        $admin->update(['empleado_id' => $otro->id, 'password' => '2222222']);
        $this->service()->guardar('persona', (string) $otro->id, true);
        Livewire::test(ConsultaCarnetPage::class)->set('carnet', '2222222')->set('password', '2222222')
            ->call('buscar')->assertHasErrors('carnet');
        $this->assertGuest();
        $this->assertFalse($admin->fresh()->acceso_personal);
    }

    public function test_sesion_rrhh_no_omite_habilitacion_password_ni_cambio_inicial_en_consulta(): void
    {
        $empleado = $this->empleado();
        $admin = $this->admin();
        $this->actingAs($admin);
        $component = Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')
            ->assertSet('empleadoEncontrado', null)->assertDontSee($empleado->nombre_completo)
            ->call('buscar')->assertHasErrors('password');
        $component->set('password', '1234567')->call('buscar')->assertHasErrors('carnet');
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseCount('users', 1);
        $this->get(URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false))->assertForbidden();
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $component->set('password', '1234567')->call('buscar')->assertHasNoErrors()->assertRedirect(route('password.change'));
        $this->assertTrue(auth()->user()->acceso_personal);
        $this->assertTrue(auth()->user()->must_change_password);
        $this->get(URL::signedRoute('perfil-horas', ['empleado' => $empleado->id], absolute: false))->assertRedirect(route('password.change'));
        $this->get(route('personal'))->assertForbidden();
    }

    public function test_generar_boleta_desde_consulta_tambien_exige_habilitacion_y_cambio_inicial(): void
    {
        $empleado = $this->empleado();
        $this->actingAs($this->admin());
        $component = Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')->set('password', '1234567')
            ->call('abrirBoletaModal')->assertHasErrors('carnet')->assertSet('showBoletaModal', false);
        $this->service()->guardar('persona', (string) $empleado->id, true);
        $component->set('password', '1234567')->call('abrirBoletaModal')->assertHasNoErrors()
            ->assertSet('showBoletaModal', false)->assertRedirect(route('password.change'));
        $this->assertDatabaseCount('permisos_laborales', 0);
    }

    public function test_password_igual_al_biometrico_siempre_exige_cambio_aunque_el_indicador_estuviera_desactivado(): void
    {
        $empleado = $this->empleado();
        $user = $this->cuenta($empleado);
        $user->update(['password' => '1234567', 'must_change_password' => false]);
        Livewire::test(ConsultaCarnetPage::class)->set('carnet', '1234567')->set('password', '1234567')->call('buscar')
            ->assertHasNoErrors()->assertRedirect(route('password.change'));
        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_acciones_intuitivas_regional_y_personal_en_accesos_personales(): void
    {
        $laPaz = $this->empleado('1001', 'La Paz');
        $santaCruz = $this->empleado('1002', 'Santa Cruz');
        $admin = $this->admin();
        $this->actingAs($admin);

        $component = Livewire::test(AccesosPersonales::class);

        // 1. Probar acción directa por sucursal (habilitar Santa Cruz)
        $component->call('aplicarSucursalDirecto', 'Santa Cruz', 'habilitar')->assertHasNoErrors();
        $this->assertTrue($this->service()->habilitado($santaCruz));
        $this->assertFalse($this->service()->habilitado($laPaz));

        // 2. Probar acción individual en 1 clic (habilitar La Paz individualmente)
        $component->call('aplicarIndividual', $laPaz->id, 'habilitar')->assertHasNoErrors();
        $this->assertTrue($this->service()->habilitado($laPaz));

        // 3. Probar selección de página y deselección
        $component->call('seleccionarTodosPagina', [$laPaz->id, $santaCruz->id]);
        $this->assertEqualsCanonicalizing([$laPaz->id, $santaCruz->id], $component->get('personas'));
        $component->call('deseleccionarTodos');
        $this->assertEmpty($component->get('personas'));

        // 4. Probar toggle de sucursal y limpieza de filtros
        $component->set('sucursal', '');
        $component->call('toggleSucursal', 'Santa Cruz');
        $this->assertSame('Santa Cruz', $component->get('sucursal'));
        $component->call('limpiarFiltros');
        $this->assertSame('', $component->get('sucursal'));
        $this->assertSame('todos', $component->get('filtroEstado'));

        // 5. Probar filtro por estado
        $component->set('filtroEstado', 'habilitados')->assertSee('1001')->assertSee('1002');
        $component->set('filtroEstado', 'excepciones')->assertSee('1001')->assertDontSee('1002');
    }
}

