<?php

namespace Tests\Feature;

use App\Livewire\HorariosPage;
use App\Models\Empleado;
use App\Models\HorarioRegional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\AnalisisAsistenciaService;
use App\Services\ProgramacionLaboralService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ToleranciaDiariaTest extends TestCase
{
    use RefreshDatabase;

    public function test_retraso_se_cuenta_partir_de_la_tolerancia_diaria(): void
    {
        // Fecha 2026-08-10 (Lunes, no es día de inicio de mes 1-4)
        $this->travelTo(Carbon::parse('2026-08-10 12:00:00'));

        $empleado = Empleado::query()->create([
            'nombre' => 'Carlos',
            'apellido' => 'Perez',
            'codigo_biometrico' => 'CP-001',
            'area' => 'Sistemas',
            'sucursal' => 'La Paz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        // Crear HorarioRegional: Entrada 08:30, Tolerancia 08:35
        HorarioRegional::query()->create([
            'sucursal' => 'La Paz',
            'hora_entrada' => '08:30:00',
            'hora_tolerancia' => '08:35:00',
            'tolerancia_minutos' => 5,
            'hora_salida' => '16:30:00',
        ]);

        // 1. Caso A: Marcación a las 08:34 (dentro de tolerancia) -> 0 minutos de retraso
        RegistroAsistencia::query()->create([
            'empleado_id' => $empleado->id,
            'fecha' => '2026-08-10',
            'hora_entrada' => '08:34:00',
            'hora_salida' => '16:30:00',
        ]);

        $service = app(AnalisisAsistenciaService::class);
        $reporte = $service->reportePersonalizado(
            $empleado->id,
            Carbon::parse('2026-08-10'),
            Carbon::parse('2026-08-10')
        );

        $this->assertNotNull($reporte);
        $filaA = collect($reporte['rows'])->firstWhere('fecha', '10/08/2026');
        $this->assertSame(0, $filaA['retraso_minutos']);

        // 2. Caso B: Marcación a las 08:35 (justo en el límite de tolerancia) -> 0 minutos de retraso
        RegistroAsistencia::query()->where('empleado_id', $empleado->id)->update([
            'hora_entrada' => '08:35:00',
        ]);

        $reporteB = $service->reportePersonalizado(
            $empleado->id,
            Carbon::parse('2026-08-10'),
            Carbon::parse('2026-08-10')
        );
        $filaB = collect($reporteB['rows'])->firstWhere('fecha', '10/08/2026');
        $this->assertSame(0, $filaB['retraso_minutos']);

        // 3. Caso C: Marcación a las 08:37 (supera tolerancia por 2 minutos) -> 2 minutos de retraso
        RegistroAsistencia::query()->where('empleado_id', $empleado->id)->update([
            'hora_entrada' => '08:37:00',
        ]);

        $reporteC = $service->reportePersonalizado(
            $empleado->id,
            Carbon::parse('2026-08-10'),
            Carbon::parse('2026-08-10')
        );
        $filaC = collect($reporteC['rows'])->firstWhere('fecha', '10/08/2026');
        $this->assertSame(2, $filaC['retraso_minutos']);
        $this->assertSame('2 min', $filaC['retraso']);
        $this->assertSame(2, $reporteC['retraso_resumen']['total_minutos']);
    }

    public function test_edicion_y_validacion_de_horario_y_tolerancia_en_livewire(): void
    {
        Permission::findOrCreate('gestionar personal');
        $user = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin_tol_test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->givePermissionTo('gestionar personal');

        Empleado::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Gomez',
            'codigo_biometrico' => 'AG-002',
            'area' => 'Operaciones',
            'sucursal' => 'Santa Cruz',
            'hora_entrada_programada' => '08:30:00',
            'hora_salida_programada' => '16:30:00',
            'fecha_contratacion' => '2026-08-01',
        ]);

        // Validación: Tolerancia no puede ser anterior a entrada
        Livewire::actingAs($user)
            ->test(HorariosPage::class)
            ->call('openEditModal', 'Santa Cruz')
            ->set('editHoraEntrada', '08:30')
            ->set('editHoraTolerancia', '08:20') // Error: anterior
            ->call('saveHorario')
            ->assertHasErrors(['editHoraTolerancia']);

        // Guardado exitoso con tolerancia válida
        Livewire::actingAs($user)
            ->test(HorariosPage::class)
            ->call('openEditModal', 'Santa Cruz')
            ->set('editHoraEntrada', '08:00')
            ->set('editHoraTolerancia', '08:10')
            ->set('editHoraSalida', '16:00')
            ->call('saveHorario')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('horarios_regionales', [
            'sucursal' => 'Santa Cruz',
            'hora_entrada' => '08:00:00',
            'hora_tolerancia' => '08:10:00',
            'tolerancia_minutos' => 10,
            'hora_salida' => '16:00:00',
        ]);
    }
}
