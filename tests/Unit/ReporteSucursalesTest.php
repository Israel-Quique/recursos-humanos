<?php

namespace Tests\Unit;

use App\Livewire\PersonalPage;
use App\Models\Empleado;
use App\Models\RegistroAsistencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ReporteSucursalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_coleccion_marcaciones_sucursales_estructura(): void
    {
        $component = new PersonalPage();
        $component->vista = 'sucursales';

        $data = $component->obtenerColeccionMarcacionesSucursales(false);

        $this->assertArrayHasKey('registros', $data);
        $this->assertArrayHasKey('allRows', $data);
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('total_omisiones', $data['stats']);
        $this->assertArrayHasKey('total_faltas', $data['stats']);
        $this->assertArrayHasKey('periodoLabel', $data);
        $this->assertArrayHasKey('sucursalLabel', $data);
        $this->assertArrayHasKey('sucursalesLista', $data);
    }

    public function test_omisiones_y_faltas_clasificacion(): void
    {
        $fechaPasada = Carbon::now()->subDays(5)->toDateString();

        $emp1 = Empleado::create([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'codigo_biometrico' => '1001',
            'sucursal' => 'La Paz',
            'area' => 'Operaciones',
            'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => '2024-01-01',
        ]);

        $emp2 = Empleado::create([
            'nombre' => 'Ana',
            'apellido' => 'Rojas',
            'codigo_biometrico' => '1002',
            'sucursal' => 'Santa Cruz',
            'area' => 'Finanzas',
            'hora_entrada_programada' => '08:30:00',
            'fecha_contratacion' => '2024-01-01',
        ]);

        // 1. Puntual y completo (Martes 2026-09-01)
        RegistroAsistencia::create([
            'empleado_id' => $emp1->id,
            'fecha' => '2026-09-01',
            'hora_entrada' => '08:25:00',
            'hora_salida' => '16:30:00',
        ]);

        // 2. Omisión de salida (Miércoles 2026-09-02)
        RegistroAsistencia::create([
            'empleado_id' => $emp1->id,
            'fecha' => '2026-09-02',
            'hora_entrada' => '08:30:00',
            'hora_salida' => null,
        ]);

        // 3. Omisión de entrada (Jueves 2026-09-03)
        RegistroAsistencia::create([
            'empleado_id' => $emp2->id,
            'fecha' => '2026-09-03',
            'hora_entrada' => null,
            'hora_salida' => '16:30:00',
        ]);

        // 4. Falta (Viernes 2026-09-04)
        RegistroAsistencia::create([
            'empleado_id' => $emp2->id,
            'fecha' => '2026-09-04',
            'hora_entrada' => null,
            'hora_salida' => null,
        ]);

        $component = new PersonalPage();
        $component->vista = 'sucursales';
        $component->appliedSucursalesPeriodoTipo = 'mes';
        $component->appliedSucursalesMes = '2026-09';

        $data = $component->obtenerColeccionMarcacionesSucursales(false);
        $rows = $data['allRows'];

        $this->assertCount(4, $rows);

        // Validar registro puntual
        $puntual = $rows->firstWhere('hora_entrada', '08:25');
        $this->assertNotNull($puntual);
        $this->assertEquals('--', $puntual->omision);
        $this->assertFalse($puntual->es_falta);
        $this->assertEquals('--', $puntual->falta);
        $this->assertEquals('La Paz', $puntual->sucursal);

        // Validar omisión salida
        $omisionSalida = $rows->firstWhere('tipo_omision', 'sin_salida');
        $this->assertNotNull($omisionSalida);
        $this->assertEquals('Sin salida', $omisionSalida->omision);
        $this->assertFalse($omisionSalida->es_falta);

        // Validar omisión entrada
        $omisionEntrada = $rows->firstWhere('tipo_omision', 'sin_entrada');
        $this->assertNotNull($omisionEntrada);
        $this->assertEquals('Sin entrada', $omisionEntrada->omision);
        $this->assertFalse($omisionEntrada->es_falta);

        // Validar falta
        $falta = $rows->firstWhere('es_falta', true);
        $this->assertNotNull($falta);
        $this->assertEquals('FALTA', $falta->falta);
        $this->assertEquals('--', $falta->omision);

        // Validar estadísticas
        $stats = $data['stats'];
        $this->assertEquals(2, $stats['total_omisiones']);
        $this->assertEquals(1, $stats['total_faltas']);
    }

    public function test_descargas_pdf_y_excel_retornan_streamed_response(): void
    {
        $component = new PersonalPage();
        $component->vista = 'sucursales';

        $excelResponse = $component->descargarExcelSucursales();
        $this->assertInstanceOf(StreamedResponse::class, $excelResponse);

        $pdfResponse = $component->descargarPdfSucursales();
        $this->assertInstanceOf(StreamedResponse::class, $pdfResponse);
    }
}
