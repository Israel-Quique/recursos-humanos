<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\ImportacionBiometricaService;
use App\Services\UnificacionPersonalLaPazService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnificacionPersonalLaPazTest extends TestCase
{
    use RefreshDatabase;

    public function test_repara_fichas_antiguas_y_reimporta_ambos_relojes_sin_duplicar_personal_ni_jornadas(): void
    {
        $campos = [
            'area' => 'Operaciones', 'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => '2025-01-01',
        ];
        $cristian = Empleado::create([
            'nombre' => 'GONZALES G.', 'apellido' => 'CRISTIAN',
            'codigo_biometrico' => '6033581', 'sucursal' => 'La Paz',
        ] + $campos);
        $duplicado = Empleado::create([
            'nombre' => 'Cristiang', 'apellido' => 'Sin apellido',
            'codigo_biometrico' => 'el-alto:6033581', 'sucursal' => 'El Alto',
        ] + $campos);
        $otro = Empleado::create([
            'nombre' => 'Otra', 'apellido' => 'Persona',
            'codigo_biometrico' => 'oruro:6033581', 'sucursal' => 'Oruro',
        ] + $campos);
        $entrada = $cristian->asistencias()->create([
            'fecha' => '2026-10-01', 'hora_entrada' => '08:30:00', 'estado_marcacion' => 'Entrada',
            'sedes_biometricas' => ['08:30:00' => 'La Paz'],
        ]);
        $salida = $duplicado->asistencias()->create([
            'fecha' => '2026-10-01', 'hora_salida' => '16:30:00', 'estado_marcacion' => 'Salida',
            'sedes_biometricas' => ['16:30:00' => 'El Alto'],
        ]);
        $duplicado->asistencias()->create([
            'fecha' => '2026-10-02', 'hora_entrada' => '08:30:00',
            'hora_salida' => '16:30:00', 'estado_marcacion' => 'Completo',
        ]);
        $permiso = PermisoLaboral::create([
            'empleado_id' => $duplicado->id, 'tipo' => 'permiso', 'estado' => 'aprobado',
            'fecha_inicio' => '2026-10-05', 'fecha_fin' => '2026-10-05',
        ]);
        $cuenta = User::create([
            'name' => 'Cristian', 'email' => 'cristian@example.test', 'password' => 'secret',
            'empleado_id' => $duplicado->id,
        ]);
        DB::table('acceso_personal_reglas')->insert([
            'tipo' => 'persona', 'referencia' => (string) $duplicado->id, 'habilitado' => true,
        ]);

        $service = app(UnificacionPersonalLaPazService::class);
        $this->assertSame(1, $service->ejecutar());
        $this->assertSame(0, $service->ejecutar());
        $this->assertSame(2, Empleado::count());
        $this->assertTrue($duplicado->fresh()->trashed());
        $this->assertSame($cristian->id, $duplicado->fresh()->unificado_en_id);
        $this->assertNull($duplicado->fresh()->codigo_biometrico);
        $this->assertSame('6033581', $cristian->fresh()->codigo_biometrico);
        $this->assertSame($cristian->id, $cuenta->fresh()->empleado_id);
        $this->assertSame($cristian->id, $permiso->fresh()->empleado_id);
        $this->assertDatabaseHas('acceso_personal_reglas', ['referencia' => (string) $cristian->id, 'habilitado' => true]);
        $this->assertSame('Oruro', $otro->fresh()->sucursal);
        $this->assertSame('08:30:00', $entrada->fresh()->hora_entrada);
        $this->assertSame('16:30:00', $entrada->fresh()->hora_salida);
        $this->assertEquals(['08:30:00' => 'La Paz', '16:30:00' => 'El Alto'], $entrada->fresh()->sedes_biometricas);
        $this->assertTrue($salida->fresh()->trashed());
        $this->assertSame(1, Auditoria::where('accion', 'Unificar')->count());

        foreach (['El Alto' => 'Cristiang', 'La Paz' => 'GONZALES G. CRISTIAN'] as $sucursal => $nombre) {
            $codigo = $sucursal === 'El Alto' ? 'el-alto:6033581' : '6033581';
            app(ImportacionBiometricaService::class)->importarMarcacionesBiometrico(['branch' => $sucursal], [
                ['codigo' => $codigo, 'nombre_completo' => $nombre, 'fecha_hora' => '2026-10-01T08:30:00', 'punch' => '0'],
                ['codigo' => $codigo, 'nombre_completo' => $nombre, 'fecha_hora' => '2026-10-01T16:30:00', 'punch' => '1'],
            ]);
        }
        $this->assertSame(2, Empleado::count());
        $this->assertSame(2, $cristian->asistencias()->count());
    }
}
