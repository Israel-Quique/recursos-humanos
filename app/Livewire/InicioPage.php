<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Services\ConexionBiometricoService;
use App\Services\ExtraccionBiometricoZkService;
use App\Services\SincronizacionBiometricoService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Panel de inicio'])]
class InicioPage extends Component
{
    public ?array $syncResult = null;

    public function sincronizarBiometrico(bool $force = false): void
    {
        abort_unless(auth()->user()?->can('importar biometria'), 403);

        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        try {
            $results = app(SincronizacionBiometricoService::class)->sincronizarTodos($force);

            $sincronizados = 0;
            $totalImportados = 0;
            $totalActualizados = 0;
            $totalGenerados = 0;
            $detalles = [];

            foreach ($results as $res) {
                $status = $res['status'] ?? 'desconocido';
                $device = $res['device'] ?? 'Biométrico';
                $msg = $res['message'] ?? '';
                $imported = (int) ($res['imported'] ?? 0);
                $updated = (int) ($res['updated'] ?? 0);
                $created = (int) ($res['created'] ?? 0);

                if ($status === 'sincronizado' || $status === 'sin-cambios') {
                    $sincronizados++;
                    $totalImportados += $imported;
                    $totalActualizados += $updated;
                    $totalGenerados += $created;
                }

                $detalles[] = [
                    'device' => $device,
                    'status' => $status,
                    'imported' => $imported,
                    'updated' => $updated,
                    'created' => $created,
                    'message' => $msg,
                ];
            }

            $this->syncResult = [
                'success' => true,
                'complete' => $sincronizados > 0 && $sincronizados === count($results),
                'sincronizados' => $sincronizados,
                'total_devices' => count($results),
                'total_importados' => $totalImportados,
                'total_actualizados' => $totalActualizados,
                'total_generados' => $totalGenerados,
                'detalles' => $detalles,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ];
        } catch (\Throwable $exception) {
            report($exception);
            $this->syncResult = [
                'success' => false,
                'error' => $exception->getMessage(),
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ];
            session()->flash('status_error', 'No se pudo completar la sincronización biométrica: ' . $exception->getMessage());
        }
    }

    public function sincronizarHoraTrasReinicio(string $equipo): void
    {
        abort_unless(auth()->user()?->can('importar biometria'), 403);
        $device = $this->dispositivosDelPanel()->get($equipo);
        abort_if($device === null, 404);

        try {
            app(ExtraccionBiometricoZkService::class)->sincronizarReloj($device);
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('status_error', 'No se pudo sincronizar la hora de '.$device['branch'].'. '.$exception->getMessage());
        }
    }

    public function cerrarResumenSync(): void
    {
        $this->syncResult = null;
    }

    private function dispositivosDelPanel(): Collection
    {
        return collect(app(ConexionBiometricoService::class)->dispositivosConfigurados())
            ->keyBy(fn (array $device) => hash('sha256', $device['ip'].':'.$device['port']));
    }

    public function render()
    {
        $user = auth()->user();
        $totalEmpleadosActivos = Empleado::query()->activosLaboralmente()->count();
        $totalEmpleadosPadron = Empleado::query()->count();
        $todayStr = Carbon::today()->toDateString();
        $totalMarcacionesHoy = \App\Models\RegistroAsistencia::query()
            ->whereDate('fecha', $todayStr)
            ->whereNotNull('empleado_id')
            ->distinct('empleado_id')
            ->count('empleado_id');

        $analisisService = app(\App\Services\AnalisisAsistenciaService::class);
        $departmentStats = $analisisService->asistenciaPorDepartamento();
        $totalEnPuestoHoy = collect($departmentStats)->sum('working');
        $porcentajeAsistenciaHoy = $totalEmpleadosActivos > 0
            ? (int) round(($totalMarcacionesHoy / $totalEmpleadosActivos) * 100)
            : 0;

        $sucursales = Empleado::query()
            ->whereNotNull('sucursal')
            ->where('sucursal', '!=', '')
            ->distinct()
            ->pluck('sucursal');
        $totalSucursales = count(SucursalNormalizer::optionsFromValues($sucursales));
        $hoy = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');

        $extractor = app(ExtraccionBiometricoZkService::class);
        $relojes = $this->dispositivosDelPanel()
            ->map(fn (array $device) => [
                'sucursal' => $device['branch'],
                'departamento' => $device['department'],
                'hora' => $extractor->estadoReloj($device),
            ]);

        return view('livewire.inicio', [
            'user' => $user,
            'totalEmpleadosActivos' => $totalEmpleadosActivos,
            'totalEmpleadosPadron' => $totalEmpleadosPadron,
            'totalMarcacionesHoy' => $totalMarcacionesHoy,
            'totalEnPuestoHoy' => $totalEnPuestoHoy,
            'porcentajeAsistenciaHoy' => $porcentajeAsistenciaHoy,
            'departmentStats' => $departmentStats,
            'totalSucursales' => max($totalSucursales, 9),
            'hoy' => ucfirst($hoy),
            'relojes' => $relojes,
        ]);
    }
}
