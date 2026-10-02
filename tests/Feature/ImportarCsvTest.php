<?php

namespace Tests\Feature;

use App\Livewire\ImportarExcelPage;
use App\Models\Empleado;
use App\Models\Importacion;
use App\Models\RegistroAsistencia;
use App\Models\User;
use App\Services\ImportacionBiometricaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ImportarCsvTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $permiso = Permission::findOrCreate('gestionar personal', 'web');
        $user = User::query()->create([
            'name' => 'Admin RRHH',
            'email' => 'admin_csv_' . uniqid() . '@correos.gob.bo',
            'password' => bcrypt('password123'),
        ]);
        $user->givePermissionTo($permiso);

        return $user;
    }

    public function test_importar_csv_con_cabeceras_estandar(): void
    {
        $user = $this->crearAdmin();

        $csvContent = implode("\n", [
            'Tiempo,"ID de Usuario",Nombre,Apellido,"Numero de tarjeta",Dispositivo,"Punto del evento",Verificacion,Estado,Evento,Notas',
            '"01/09/2026 08:00",1001,CARLOS,MAMANI,,"La Paz","Oficina Central La Paz",Huella,Entrada,"Check-in",',
            '"01/09/2026 17:00",1001,CARLOS,MAMANI,,"La Paz","Oficina Central La Paz",Huella,Salida,"Check-out",',
            '"01/09/2026 08:15",1002,MARIA,QUISPE,,"La Paz","Oficina Central La Paz",Tarjeta,Entrada,"Check-in",',
            '"01/09/2026 16:45",1002,MARIA,QUISPE,,"La Paz","Oficina Central La Paz",Tarjeta,Salida,"Check-out",',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $service = app(ImportacionBiometricaService::class);
        $importacion = $service->importarArchivo($tempFile, 'test_estandar.csv', $user);

        @unlink($tempFile);

        $this->assertEquals('completado', $importacion->estado);
        $this->assertEquals(4, $importacion->registros_total);
        $this->assertEquals(2, $importacion->registros_generados);
        $this->assertEquals(2, $importacion->empleados_detectados);

        $this->assertDatabaseHas('empleados', [
            'codigo_biometrico' => '1001',
        ]);
        $this->assertDatabaseHas('empleados', [
            'codigo_biometrico' => '1002',
        ]);

        $carlos = Empleado::query()->where('codigo_biometrico', '1001')->first();
        $this->assertNotNull($carlos);

        $registroCarlos = RegistroAsistencia::query()
            ->where('empleado_id', $carlos->id)
            ->whereDate('fecha', '2026-09-01')
            ->first();

        $this->assertNotNull($registroCarlos);
        $this->assertEquals('08:00:00', $registroCarlos->hora_entrada);
        $this->assertEquals('17:00:00', $registroCarlos->hora_salida);
    }

    public function test_importar_csv_sin_cabeceras_detecta_columnas_sinteticas(): void
    {
        $user = $this->crearAdmin();

        // Archivo que no tiene cabeceras (como el que causaba 0 registros generados)
        $csvContent = implode("\n", [
            '01/09/2026 7:01,6888223,LUCY,TINTA TORREZ,,La Paz,Oficina Central La Paz,Huella,Retorno de descanso,Boton de salida',
            '01/09/2026 16:30,6888223,LUCY,TINTA TORREZ,,La Paz,Oficina Central La Paz,Huella,Salida,Boton de salida',
            '01/09/2026 7:15,9878461,JUAN,QUENALLATA,,La Paz,Oficina Central La Paz,Huella,Retorno de descanso,Boton de salida',
            '01/09/2026 17:05,9878461,JUAN,QUENALLATA,,La Paz,Oficina Central La Paz,Huella,Salida,Boton de salida',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_no_head_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $service = app(ImportacionBiometricaService::class);
        $importacion = $service->importarArchivo($tempFile, 'test_sin_cabecera.csv', $user);

        @unlink($tempFile);

        $this->assertEquals('completado', $importacion->estado);
        $this->assertEquals(4, $importacion->registros_total);
        $this->assertEquals(2, $importacion->registros_generados);
        $this->assertEquals(2, $importacion->empleados_detectados);

        $lucy = Empleado::query()->where('codigo_biometrico', '6888223')->first();
        $this->assertNotNull($lucy);

        $regLucy = RegistroAsistencia::query()
            ->where('empleado_id', $lucy->id)
            ->whereDate('fecha', '2026-09-01')
            ->first();

        $this->assertNotNull($regLucy);
        $this->assertEquals('07:01:00', $regLucy->hora_entrada);
        $this->assertEquals('16:30:00', $regLucy->hora_salida);
    }

    public function test_importar_csv_con_utf8_bom_y_delimitador_punto_y_coma(): void
    {
        $user = $this->crearAdmin();

        $bom = "\xEF\xBB\xBF";
        $csvContent = $bom . implode("\n", [
            'Tiempo;ID de Usuario;Nombre;Apellido;Dispositivo;Punto del evento;Verificacion;Estado',
            '01/09/2026 08:30;5551;ROSA;MENDOZA;Cochabamba;Sucursal Cochabamba;Huella;Entrada',
            '01/09/2026 18:30;5551;ROSA;MENDOZA;Cochabamba;Sucursal Cochabamba;Huella;Salida',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_bom_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $service = app(ImportacionBiometricaService::class);
        $importacion = $service->importarArchivo($tempFile, 'test_bom.csv', $user);

        @unlink($tempFile);

        $this->assertEquals('completado', $importacion->estado);
        $this->assertEquals(2, $importacion->registros_total);
        $this->assertEquals(1, $importacion->registros_generados);
        $this->assertEquals(1, $importacion->empleados_detectados);

        $rosa = Empleado::query()->where('codigo_biometrico', '5551')->first();
        $this->assertNotNull($rosa);
        $this->assertEquals('ROSA', $rosa->nombre);
    }

    public function test_livewire_importar_excel_page_procesa_archivo_csv(): void
    {
        Storage::fake('local');
        $user = $this->crearAdmin();

        $csvContent = implode("\n", [
            'Tiempo,"ID de Usuario",Nombre,Apellido,"Numero de tarjeta",Dispositivo,"Punto del evento",Verificacion,Estado,Evento,Notas',
            '"02/09/2026 08:10",8881,PEDRO,GUTIERREZ,,"Santa Cruz","Sucursal Santa Cruz",Huella,Entrada,"Check-in",',
            '"02/09/2026 17:10",8881,PEDRO,GUTIERREZ,,"Santa Cruz","Sucursal Santa Cruz",Huella,Salida,"Check-out",',
        ]);

        $file = UploadedFile::fake()->createWithContent('marcaciones_scz.csv', $csvContent);

        $comp = Livewire::actingAs($user)
            ->test(ImportarExcelPage::class)
            ->set('archivos', [$file])
            ->call('importFiles')
            ->assertHasNoErrors();

        $batchStatus = $comp->get('uploadBatchStatus');
        $this->assertNotEmpty($batchStatus);
        $this->assertEquals('completed', $batchStatus[0]['status'] ?? '', $batchStatus[0]['message'] ?? '');

        $this->assertDatabaseHas('empleados', [
            'codigo_biometrico' => '8881',
        ]);

        $this->assertDatabaseHas('importaciones', [
            'nombre_archivo' => 'marcaciones_scz.csv',
            'estado' => 'completado',
            'registros_total' => 2,
            'registros_generados' => 1,
        ]);
    }
}
