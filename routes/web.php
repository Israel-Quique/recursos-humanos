<?php

use App\Livewire\Auth\LoginPage;
use App\Livewire\CalendarioPage;
use App\Livewire\ConsultaCarnetPage;
use App\Livewire\DashboardPage;
use App\Livewire\InicioPage;
use App\Livewire\AuditoriaPage;
use App\Livewire\FechasEspecialesPage;
use App\Livewire\GestionAccesosPage;
use App\Livewire\HorariosPage;
use App\Livewire\IncidenciasPage;
use App\Livewire\ImportarExcelPage;
use App\Livewire\MisHorasPage;
use App\Livewire\PerfilHorasPage;
use App\Livewire\PersonalPage;
use App\Livewire\ReportesPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', LoginPage::class)->name('login');
});

Route::get('/consulta-carnet', ConsultaCarnetPage::class)->name('consulta-carnet');

Route::post('/cerrar-sesion', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/perfil-horas/{empleado}', PerfilHorasPage::class)
    ->middleware('signed:relative')
    ->name('perfil-horas');

Route::middleware('auth')->group(function () {
    Route::get('/inicio', InicioPage::class)->middleware('permission:ver panel')->name('inicio');
    Route::get('/panel', DashboardPage::class)->middleware('permission:ver panel')->name('dashboard');
    Route::get('/importar', ImportarExcelPage::class)->middleware('permission:importar biometria')->name('importar');
    Route::get('/calendario', CalendarioPage::class)->middleware('permission:ver calendario')->name('calendario');
    Route::get('/reportes', ReportesPage::class)->middleware('permission:ver reportes')->name('reportes');
    Route::get('/mis-horas', MisHorasPage::class)->name('mis-horas');
    Route::get('/personal', PersonalPage::class)->middleware('permission:gestionar personal')->name('personal');
    Route::get('/horarios', HorariosPage::class)->middleware('permission:gestionar personal')->name('horarios');
    Route::get('/fechas-especiales', FechasEspecialesPage::class)->middleware('permission:gestionar personal')->name('fechas-especiales');
    Route::get('/incidencias', IncidenciasPage::class)->middleware('permission:gestionar personal')->name('incidencias');
    Route::get('/accesos', GestionAccesosPage::class)->middleware('permission:gestionar accesos')->name('accesos');
    Route::get('/auditoria', AuditoriaPage::class)->middleware('permission:ver auditoria')->name('auditoria');
});
