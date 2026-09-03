<?php

namespace Tests\Feature;

use App\Livewire\FechasEspecialesPage;
use App\Models\FechaEspecialLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FechasEspecialesPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('gestionar personal');
        $this->user = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin_fechas@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->user->givePermissionTo('gestionar personal');
    }

    public function test_permite_crear_fecha_especial(): void
    {
        Livewire::actingAs($this->user)
            ->test(FechasEspecialesPage::class)
            ->set('fecha', '2026-10-12')
            ->set('fechaFin', '2026-10-12')
            ->set('sucursal', 'TODAS')
            ->set('nombre', 'Día de la Descolonización')
            ->set('tipo', 'feriado')
            ->call('saveFechaEspecial')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fechas_especiales_laborales', [
            'sucursal' => 'TODAS',
            'nombre' => 'Día de la Descolonización',
            'deleted_at' => null,
        ]);
    }

    public function test_bloquea_duplicados_en_la_misma_sucursal_y_fecha(): void
    {
        FechaEspecialLaboral::query()->create([
            'fecha' => '2026-10-12',
            'sucursal' => 'TODAS',
            'nombre' => 'Feriado Existente',
            'tipo' => 'feriado',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(FechasEspecialesPage::class)
            ->set('fecha', '2026-10-12')
            ->set('fechaFin', '2026-10-12')
            ->set('sucursal', 'TODAS')
            ->set('nombre', 'Intento Duplicado')
            ->set('tipo', 'feriado')
            ->call('saveFechaEspecial')
            ->assertHasErrors(['fecha']);
    }

    public function test_permite_crear_fecha_especial_si_la_anterior_fue_eliminada_soft_delete(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('El índice parcial WHERE deleted_at IS NULL aplica en PostgreSQL de producción.');
        }

        $fecha = FechaEspecialLaboral::query()->create([
            'fecha' => '2026-10-12',
            'sucursal' => 'TODAS',
            'nombre' => 'Feriado Eliminado',
            'tipo' => 'feriado',
            'created_by' => $this->user->id,
        ]);

        // Se elimina lógicamente (soft delete)
        $fecha->delete();
        $this->assertSoftDeleted('fechas_especiales_laborales', ['id' => $fecha->id]);

        // Ahora se puede volver a crear sin conflicto de restricción unique
        Livewire::actingAs($this->user)
            ->test(FechasEspecialesPage::class)
            ->set('fecha', '2026-10-12')
            ->set('fechaFin', '2026-10-12')
            ->set('sucursal', 'TODAS')
            ->set('nombre', 'Nuevo Feriado en la misma fecha')
            ->set('tipo', 'feriado')
            ->call('saveFechaEspecial')
            ->assertHasNoErrors();

        $this->assertEquals(
            1,
            FechaEspecialLaboral::query()
                ->where('fecha', '2026-10-12')
                ->where('sucursal', 'TODAS')
                ->whereNull('deleted_at')
                ->count()
        );
    }
}
