<?php

namespace Tests\Feature;

use App\Livewire\PerfilHorasPage;
use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\PermisoComprobante;
use App\Models\PermisoLaboral;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoletaEdicionPersonalTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\AccesoPersonalDePrueba;

    private function empleado(string $codigo = '123456'): Empleado
    {
        return Empleado::query()->create([
            'nombre' => 'Ana', 'apellido' => 'Prueba', 'codigo_biometrico' => $codigo,
            'area' => 'Sistemas', 'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00', 'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-01-01',
        ]);
    }

    private function boleta(Empleado $empleado): PermisoLaboral
    {
        return PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id, 'tipo' => 'permiso', 'alcance' => 'horas',
            'estado' => 'aprobado', 'fecha_inicio' => '2026-10-28', 'fecha_fin' => '2026-10-28',
            'hora_inicio' => '09:00:00', 'hora_fin' => '10:00:00', 'minutos_contabilizados' => 60,
            'motivo' => 'PARTICULAR: Reunión', 'boleta_cargo' => 'Analista',
        ]);
    }

    public function test_edita_boleta_futura_y_conserva_fechas_cargo_y_comprobante_con_auditoria(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 10:00:00'));
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $adjunto = PermisoComprobante::query()->create([
            'permiso_laboral_id' => $boleta->id, 'ruta_archivo' => 'comprobantes/prueba.jpg',
            'nombre_original' => 'prueba.jpg', 'archivo_base64' => base64_encode('imagen'),
        ]);
        $this->travelTo(Carbon::parse('2026-10-28 09:59:59'));
        $component = $this->perfilPersonal($empleado)
            ->assertSee('Mis boletas guardadas')
            ->call('abrirEdicionBoleta', $boleta->id)
            ->assertSet('editarTipoBoleta', 'particular')
            ->assertSet('editarMotivo', 'Reunión')
            ->set('editarHoraFin', '12:00')
            ->set('editarTipoBoleta', 'comision')
            ->set('editarMotivo', 'Reunión institucional')
            ->set('boletaDesdeFecha', '2027-01-01')
            ->call('guardarEdicionBoleta')->assertHasNoErrors()
            ->assertSet('editandoBoletaId', null);

        $boleta->refresh();
        $this->assertSame('COMISION: Reunión institucional', $boleta->motivo);
        $this->assertSame('comision', $boleta->tipo_boleta);
        $this->assertSame(180, $boleta->minutos_contabilizados);
        $this->assertSame('pendiente', $boleta->estado);
        $this->assertSame('2026-10-28', $boleta->fecha_inicio->toDateString());
        $this->assertSame('Analista', $boleta->boleta_cargo);
        $this->assertSame('2026-10-27 10:00:00', $boleta->created_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('permiso_comprobantes', ['id' => $adjunto->id]);
        $this->assertDatabaseCount('permisos_laborales', 1);
        $evento = Auditoria::query()->where('accion', 'editar_boleta_empleado')->sole();
        $this->assertSame('PARTICULAR: Reunión', $evento->antes['motivo']);
        $this->assertSame('10:00:00', $evento->antes['hora_fin']);
        $this->assertSame($boleta->motivo, $evento->despues['motivo']);
        $component->call('descargarBoletaPdf', $boleta->id)->assertFileDownloaded();
    }

    public function test_el_plazo_se_valida_al_guardar_aunque_el_modal_se_abra_antes(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 10:00:00'));
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $component = $this->perfilPersonal($empleado)->call('abrirEdicionBoleta', $boleta->id);
        $this->travelTo(Carbon::parse('2026-10-28 10:00:00'));
        $component->set('editarHoraFin', '12:00')->call('guardarEdicionBoleta')->assertForbidden();
        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id, 'hora_fin' => '10:00:00']);
        $this->assertDatabaseCount('auditorias', 0);
    }

    public function test_no_abre_boletas_vencidas_ni_boletas_de_otra_persona(): void
    {
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $boleta->forceFill(['created_at' => now()->subHours(24)])->save();
        $this->perfilPersonal($empleado)->call('abrirEdicionBoleta', $boleta->id)->assertForbidden();

        $ajena = $this->boleta($this->empleado('654321'));
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->set('editandoBoletaId', $ajena->id)->call('guardarEdicionBoleta')->assertNotFound();
        Livewire::test(PerfilHorasPage::class, ['empleado' => $empleado])
            ->call('abrirEdicionBoleta', $ajena->id)->assertNotFound();
    }

    public function test_rechaza_horarios_invertidos_y_tipo_invalido(): void
    {
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $this->perfilPersonal($empleado)->call('abrirEdicionBoleta', $boleta->id)
            ->set('editarHoraFin', '08:00')->set('editarTipoBoleta', 'administrador')
            ->call('guardarEdicionBoleta')->assertHasErrors(['editarHoraFin', 'editarTipoBoleta']);
        $this->assertDatabaseHas('permisos_laborales', ['id' => $boleta->id, 'hora_fin' => '10:00:00']);
    }

    public function test_fallo_de_auditoria_no_deja_cambios_guardados(): void
    {
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $this->actingAs($this->cuentaPersonal($empleado));
        $component = new PerfilHorasPage;
        $component->empleado = $empleado;
        $component->abrirEdicionBoleta($boleta->id);
        $component->editarHoraFin = '12:00';
        $this->mock(AuditoriaService::class)->shouldReceive('registrar')->once()
            ->andThrow(new \RuntimeException('Fallo controlado'));
        try {
            $component->guardarEdicionBoleta();
            $this->fail('Debe revertirse la edición.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo controlado', $e->getMessage());
        }
        $this->assertDatabaseHas('permisos_laborales', [
            'id' => $boleta->id, 'hora_fin' => '10:00:00', 'estado' => 'aprobado',
        ]);
    }

    public function test_titulo_impreso_corresponde_a_correos_de_bolivia(): void
    {
        $html = view('pdf.boleta-permiso', ['boleta' => []])->render();
        $this->assertStringContainsString('CORREOS DE BOLIVIA', $html);
        $this->assertStringNotContainsString('AGENCIA BOLIVIANA DE CORREOS', $html);
    }

    public function test_generacion_guarda_cargo_y_boleta_original_para_posterior_edicion(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 10:00:00'));
        \App\Models\ConfiguracionBoleta::query()->create(['comprobantes_habilitados' => false]);
        $empleado = $this->empleado();
        $this->perfilPersonal($empleado)->call('abrirBoletaModal', '2026-10-28')
            ->set('boletaCargo', 'Analista de Sistemas')
            ->set('boletaMotivo', 'Reunión de coordinación')
            ->set('boletaDesdeHora', '09:00')->set('boletaHastaHora', '10:00')
            ->call('descargarPdf')->assertHasNoErrors()->assertFileDownloaded();

        $boleta = PermisoLaboral::query()->sole();
        $this->assertSame('Analista de Sistemas', $boleta->boleta_cargo);
        $this->assertSame('2026-10-28', $boleta->fecha_inicio->toDateString());
        $this->assertTrue($boleta->editablePorTitular());
        $original = Auditoria::query()->where('accion', 'solicitar_boleta_empleado')->sole()->despues['boleta'];
        $this->assertSame($boleta->motivo, $original['motivo']);
        $this->assertSame('Analista de Sistemas', $original['boleta_cargo']);
    }

    public function test_boleta_por_dias_conserva_modalidad_y_fechas_al_corregir_motivo(): void
    {
        $empleado = $this->empleado();
        $boleta = $this->boleta($empleado);
        $boleta->update(['alcance' => 'dias', 'hora_inicio' => null, 'hora_fin' => null]);
        $this->perfilPersonal($empleado)->call('abrirEdicionBoleta', $boleta->id)
            ->set('editarHoraInicio', '01:00')->set('editarHoraFin', '02:00')
            ->set('editarMotivo', 'Corrección del motivo')->call('guardarEdicionBoleta')->assertHasNoErrors();
        $this->assertDatabaseHas('permisos_laborales', [
            'id' => $boleta->id, 'alcance' => 'dias', 'hora_inicio' => null, 'hora_fin' => null,
            'motivo' => 'PARTICULAR: Corrección del motivo',
        ]);
    }

    public function test_solo_muestra_boletas_en_cards_del_mes_consultado_en_el_horario(): void
    {
        $empleado = $this->empleado();

        PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-10-15',
            'fecha_fin' => '2026-10-15',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '11:00:00',
            'minutos_contabilizados' => 120,
            'motivo' => 'PARTICULAR: Trámite de octubre',
        ]);

        PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'pendiente',
            'fecha_inicio' => '2026-09-10',
            'fecha_fin' => '2026-09-10',
            'hora_inicio' => '14:00:00',
            'hora_fin' => '16:00:00',
            'minutos_contabilizados' => 120,
            'motivo' => 'COMISION: Comisión de septiembre',
        ]);

        // Al consultar Octubre 2026, solo aparece la boleta de octubre en formato card y sin prefijo en el motivo
        $component = $this->perfilPersonal($empleado)
            ->set('referenceMonth', '2026-10')
            ->assertSee('ph-boleta-card', false)
            ->assertSee('Trámite de octubre')
            ->assertDontSee('PARTICULAR: Trámite de octubre')
            ->assertDontSee('Comisión de septiembre');

        // Al cambiar el mes de consulta a Septiembre 2026, solo aparece la boleta de septiembre
        $component->set('referenceMonth', '2026-09')
            ->assertSee('Comisión de septiembre')
            ->assertDontSee('COMISION: Comisión de septiembre')
            ->assertDontSee('Trámite de octubre');
    }

    public function test_editable_muestra_solo_texto_del_usuario_y_solo_los_cuatro_tipos_de_boleta(): void
    {
        $empleado = $this->empleado();
        $boleta = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'permiso',
            'alcance' => 'horas',
            'estado' => 'pendiente',
            'fecha_inicio' => '2026-10-28',
            'fecha_fin' => '2026-10-28',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:00:00',
            'minutos_contabilizados' => 60,
            'motivo' => 'RIP: Olvido de marcado en la mañana',
        ]);

        $component = $this->perfilPersonal($empleado)
            ->set('referenceMonth', '2026-10')
            ->assertSee('Olvido de marcado en la mañana')
            ->assertDontSee('RIP: Olvido de marcado en la mañana')
            ->call('abrirEdicionBoleta', $boleta->id)
            ->assertSet('editarTipoBoleta', 'rip')
            ->assertSet('editarMotivo', 'Olvido de marcado en la mañana')
            ->assertDontSee('<option value="omision">', false)
            ->assertDontSee('<option value="retraso">', false);

        // No acepta tipos fuera de los 4 de la boleta
        $component->set('editarTipoBoleta', 'omision')
            ->call('guardarEdicionBoleta')
            ->assertHasErrors(['editarTipoBoleta']);

        // Al cambiar a otro de los 4 tipos de la boleta, se actualiza el marcado y el motivo limpio
        $component->set('editarTipoBoleta', 'medico')
            ->set('editarMotivo', 'Consulta médica externa')
            ->call('guardarEdicionBoleta')
            ->assertHasNoErrors();

        $boleta->refresh();
        $this->assertSame('medico', $boleta->tipo_boleta);
        $this->assertSame('Consulta médica externa', $boleta->motivo_sin_prefijo);
    }
}
