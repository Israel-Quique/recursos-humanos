<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Support\EmisionBoleta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletaEmisionTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\AccesoPersonalDePrueba;

    public function test_fecha_hora_local_y_sucursal_sin_interpretar_literales_como_formato(): void
    {
        config(['app.timezone' => 'UTC']);
        $this->travelTo(Carbon::parse('2026-11-03 02:30:00', 'UTC'));
        $datos = EmisionBoleta::datos(new Empleado(['sucursal' => 'Sucursal Cochabamba']));
        $this->assertSame('SUCURSAL COCHABAMBA, 02 DE NOVIEMBRE DE 2026 - 22:30', $datos['lugar_fecha']);
    }

    public function test_reimpresion_usa_hora_actual_y_conserva_fechas_del_permiso_y_creacion(): void
    {
        $empleado = Empleado::query()->create([
            'nombre' => 'Ana', 'apellido' => 'Prueba', 'codigo_biometrico' => '123456',
            'sucursal' => 'Sucursal Pando', 'area' => 'Sistemas',
            'hora_entrada_programada' => '08:30:00', 'fecha_contratacion' => '2026-01-01',
        ]);
        $this->travelTo(Carbon::parse('2026-10-27 09:00:00', 'America/La_Paz'));
        $permiso = PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id, 'tipo' => 'permiso', 'alcance' => 'horas',
            'estado' => 'pendiente', 'fecha_inicio' => '2026-10-28', 'fecha_fin' => '2026-10-28',
            'hora_inicio' => '08:30:00', 'hora_fin' => '08:45:00',
            'minutos_contabilizados' => 15, 'motivo' => 'PARTICULAR: Trámite',
        ]);
        $creada = $permiso->created_at->toDateTimeString();
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->twice()->with('letter', 'portrait')->andReturnSelf();
        $pdf->shouldReceive('output')->twice()->andReturn('%PDF-prueba');
        $impresiones = [];
        Pdf::shouldReceive('loadView')->twice()->withArgs(function ($vista, $datos) use (&$impresiones) {
            $this->assertSame('pdf.boleta-permiso', $vista);
            $this->assertSame('28/10/2026', $datos['boleta']['desde_fecha']);
            $this->assertSame('28/10/2026', $datos['boleta']['hasta_fecha']);
            $this->assertSame('08:30', $datos['boleta']['desde_hora']);
            $impresiones[] = $datos['boleta']['lugar_fecha'];

            return true;
        })->andReturn($pdf);

        $this->travelTo(Carbon::parse('2026-11-03 15:42:00', 'America/La_Paz'));
        $component = $this->perfilPersonal($empleado)
            ->call('descargarBoletaPdf', $permiso->id)->assertFileDownloaded();
        $this->travelTo(Carbon::parse('2026-11-04 10:05:00', 'America/La_Paz'));
        $component->call('descargarBoletaPdf', $permiso->id)->assertFileDownloaded();

        $this->assertSame([
            'SUCURSAL PANDO, 03 DE NOVIEMBRE DE 2026 - 15:42',
            'SUCURSAL PANDO, 04 DE NOVIEMBRE DE 2026 - 10:05',
        ], $impresiones);
        $this->assertSame($creada, $permiso->fresh()->created_at->toDateTimeString());
    }
}
