<?php

namespace Tests\Feature;

use App\Livewire\PlanillaRefrigerioPage;
use App\Models\Empleado;
use App\Models\PermisoLaboral;
use App\Models\PlanillaRefrigerio;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\PlanillaRefrigerioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlanillaRefrigerioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('gestionar personal', 'web');
        Permission::findOrCreate('ver reportes', 'web');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('planilla-refrigerio'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_with_permission_can_access_planilla_refrigerio(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test.user@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo('gestionar personal');

        $response = $this->actingAs($user)->get(route('planilla-refrigerio'));
        $response->assertStatus(200);
        $response->assertSee('Planilla de Descuento de Refrigerio / Comida');
    }

    public function test_calculo_correcto_de_faltas_omisiones_bajas_y_comisiones(): void
    {
        $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'codigo_biometrico' => '1001',
            'sucursal' => 'La Paz',
            'cargo' => 'Auxiliar',
            'area' => 'Operaciones',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-09-01',
        ]);

        // 1. Falta registrada explícita
        PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'falta',
            'alcance' => 'dias',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-09-02',
            'fecha_fin' => '2026-09-02',
            'motivo' => 'Inasistencia injustificada',
        ]);

        // 2. Omisiones de marcado (2 omisiones: una sin entrada y otra sin salida)
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-09-03',
            'hora_entrada' => null,
            'hora_salida' => '16:30:00',
            'estado_marcacion' => 'Salida',
        ]);

        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-09-04',
            'hora_entrada' => '08:30:00',
            'hora_salida' => null,
            'estado_marcacion' => 'Entrada',
        ]);

        // 3. Baja médica aprobada por 2 días hábiles (martes 8 y miércoles 9 de sep 2026)
        PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'medico',
            'alcance' => 'dias',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-09-08',
            'fecha_fin' => '2026-09-09',
            'motivo' => 'Baja médica por reposo 48h',
        ]);

        // 4. Comisión de viaje por 3 días hábiles (martes 15 a jueves 17 de sep 2026)
        PermisoLaboral::query()->create([
            'empleado_id' => $empleado->id,
            'tipo' => 'comision',
            'alcance' => 'dias',
            'estado' => 'aprobado',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-09-17',
            'motivo' => 'Comisión de viaje institucional a Oruro',
        ]);

        $service = app(PlanillaRefrigerioService::class);
        $resultado = $service->calcularPlanilla(Carbon::parse('2026-09-01'), null, 20.00);

        $itemEmp = collect($resultado['items'])->firstWhere('empleado_id', $empleado->id);

        $this->assertNotNull($itemEmp);
        $this->assertEquals(1, $itemEmp['faltas'], 'Faltas debe ser 1');
        $this->assertEquals(2, $itemEmp['omisiones'], 'Omisiones debe ser 2');
        $this->assertEquals(2, $itemEmp['bajas_medicas'], 'Bajas médicas debe ser 2');
        $this->assertEquals(3, $itemEmp['comisiones_viaje'], 'Comisiones de viaje debe ser 3');

        // Sumatoria esperada: 1 + 2 + 2 + 3 = 8 días
        $this->assertEquals(8, $itemEmp['total_dias'], 'Sumatoria de días debe ser 8');

        // Cuánto no se debe pagar: 8 días * Bs. 20 = Bs. 160.00
        $this->assertEquals(160.00, $itemEmp['total_monto'], 'Total a no pagar debe ser Bs. 160');
    }

    public function test_livewire_permite_editar_dias_y_guarda_planilla(): void
    {
        $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));

        $user = User::query()->create([
            'name' => 'Test User 2',
            'email' => 'test2@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo('gestionar personal');

        $empleado = Empleado::query()->create([
            'nombre' => 'Maria',
            'apellido' => 'Quispe',
            'codigo_biometrico' => '2002',
            'sucursal' => 'Cochabamba',
            'area' => 'Finanzas',
            'cargo' => 'Cajera',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-09-01',
        ]);

        Livewire::actingAs($user)
            ->test(PlanillaRefrigerioPage::class)
            ->assertSee('Maria Quispe')
            ->call('actualizarDia', 0, 'faltas', 2)
            ->call('actualizarDia', 0, 'omisiones', 1)
            ->call('actualizarDia', 0, 'bajas_medicas', 3)
            ->call('actualizarDia', 0, 'comisiones_viaje', 4)
            ->call('guardarPlanilla');

        // Total días: 2 + 1 + 3 + 4 = 10
        // Monto con tarifa 20: 10 * 20 = 200 Bs.
        $saved = PlanillaRefrigerio::query()
            ->where('periodo', '2026-09')
            ->first();

        $this->assertNotNull($saved);
        $savedEmp = collect($saved->datos['items'])->firstWhere('empleado_id', $empleado->id);
        $this->assertEquals(10, $savedEmp['total_dias']);
        $this->assertEquals(200.00, $savedEmp['total_monto']);
    }

    public function test_cambio_monto_tarifa_diaria_permite_strings_y_vacio_sin_error(): void
    {
        $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));

        $user = User::query()->create([
            'name' => 'Tarifa Tester',
            'email' => 'tarifa@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo('gestionar personal');

        Empleado::query()->create([
            'nombre' => 'Pedro',
            'apellido' => 'Gomez',
            'codigo_biometrico' => '3003',
            'sucursal' => 'La Paz',
            'area' => 'Operaciones',
            'cargo' => 'Chofer',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-09-01',
        ]);

        // Simular que el usuario borra el monto y escribe uno nuevo (ej. 25)
        Livewire::actingAs($user)
            ->test(PlanillaRefrigerioPage::class)
            ->set('tarifaDiaria', '')
            ->assertSet('tarifaDiaria', '')
            ->set('tarifaDiaria', '25')
            ->assertSet('tarifaDiaria', '25')
            ->set('tarifaDiaria', 25.50)
            ->assertSet('tarifaDiaria', 25.50);
    }

    public function test_descargas_excel_y_pdf_funcionan_correctamente(): void
    {
        $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));

        $user = User::query()->create([
            'name' => 'Test User 3',
            'email' => 'test3@correos.bo',
            'password' => bcrypt('password'),
        ]);
        $user->givePermissionTo('gestionar personal');

        Empleado::query()->create([
            'nombre' => 'Test',
            'apellido' => 'Download',
            'codigo_biometrico' => '9999',
            'sucursal' => 'La Paz',
            'area' => 'Sistemas',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-09-01',
        ]);

        // Test exportar Excel
        Livewire::actingAs($user)
            ->test(PlanillaRefrigerioPage::class)
            ->call('descargarExcel')
            ->assertFileDownloaded();

        // Test exportar PDF
        Livewire::actingAs($user)
            ->test(PlanillaRefrigerioPage::class)
            ->call('descargarPdf')
            ->assertFileDownloaded();
    }
}
