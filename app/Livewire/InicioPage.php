<?php

namespace App\Livewire;

use App\Models\BiometricoDispositivo;
use App\Models\Empleado;
use App\Services\SincronizacionBiometricoService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Panel de inicio'])]
class InicioPage extends Component
{
    public ?array $syncResult = null;
    public ?string $lastSyncTime = null;

    public function mount(): void
    {
        $lastDeviceSync = BiometricoDispositivo::query()
            ->whereNotNull('last_seen_at')
            ->orderByDesc('last_seen_at')
            ->value('last_seen_at');

        if ($lastDeviceSync) {
            $this->lastSyncTime = Carbon::parse($lastDeviceSync)->locale('es')->diffForHumans();
        }
    }

    public function sincronizarBiometrico(bool $force = true): void
    {
        if (auth()->check() && !auth()->user()->can('importar biometria')) {
            session()->flash('status_error', 'No cuentas con los permisos necesarios para sincronizar el biométrico.');
            return;
        }

        try {
            $service = app(SincronizacionBiometricoService::class);
            $results = $service->sincronizarTodos($force);

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
                'sincronizados' => $sincronizados,
                'total_devices' => count($results),
                'total_importados' => $totalImportados,
                'total_actualizados' => $totalActualizados,
                'total_generados' => $totalGenerados,
                'detalles' => $detalles,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ];

            $this->lastSyncTime = 'Hace un momento';

            $mensajeExito = "Sincronización completada exitosamente. Se procesaron {$totalImportados} marcaciones ({$totalGenerados} nuevas y {$totalActualizados} registros actualizados con su salida/entrada).";
            session()->flash('status_success', $mensajeExito);
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

    public function cerrarResumenSync(): void
    {
        $this->syncResult = null;
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
        ]);
    }
}
