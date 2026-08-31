<?php

namespace App\Livewire;

use App\Services\AnalisisAsistenciaService;
use App\Support\SucursalNormalizer;
use Carbon\Carbon;
use Livewire\Component;

class CalendarioPage extends Component
{
    public string $referenceMonth = '';
    public string $selectedDate = '';

    // Inputs del formulario de búsqueda (se aplican al presionar "Buscar")
    public string $inputFecha = '';
    public string $inputCodigo = '';
    public string $inputSucursal = '';

    // Filtros aplicados tras presionar "Buscar"
    public string $appliedCodigo = '';
    public string $appliedSucursal = '';

    // Pestaña y ordenación dentro de la tabla
    public string $activeTab = 'todos';
    public string $sortBy = 'nombre';
    public string $sortDirection = 'asc';
    public string $filterEstadoInterno = 'todos'; // 'todos', 'completo', 'faltante'

    public function mount(): void
    {
        $today = now()->toDateString();
        $this->referenceMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate = $today;
        $this->inputFecha = $today;
        $this->inputCodigo = '';
        $this->inputSucursal = '';
        $this->appliedCodigo = '';
        $this->appliedSucursal = '';
    }

    public function aplicarBusqueda(): void
    {
        if (filled($this->inputFecha)) {
            $carbon = Carbon::parse($this->inputFecha);
            $this->selectedDate = $carbon->toDateString();
            $this->referenceMonth = $carbon->copy()->startOfMonth()->toDateString();
        }

        $this->appliedCodigo = trim($this->inputCodigo);
        $this->appliedSucursal = trim($this->inputSucursal);
    }

    public function limpiarFiltros(): void
    {
        $today = now()->toDateString();
        $this->inputFecha = $today;
        $this->inputCodigo = '';
        $this->inputSucursal = '';
        $this->appliedCodigo = '';
        $this->appliedSucursal = '';
        $this->selectedDate = $today;
        $this->referenceMonth = now()->startOfMonth()->toDateString();
        $this->sortBy = 'nombre';
        $this->sortDirection = 'asc';
        $this->filterEstadoInterno = 'todos';
    }

    public function goToCurrentMonth(): void
    {
        $today = now()->toDateString();
        $this->referenceMonth = now()->startOfMonth()->toDateString();
        $this->selectedDate = $today;
        $this->inputFecha = $today;
    }

    public function goToPreviousMonth(): void
    {
        $newReference = Carbon::parse($this->referenceMonth)
            ->subMonthNoOverflow()
            ->startOfMonth();

        $this->referenceMonth = $newReference->toDateString();
        $this->selectedDate = $newReference->toDateString();
        $this->inputFecha = $this->selectedDate;
    }

    public function goToNextMonth(): void
    {
        $newReference = Carbon::parse($this->referenceMonth)
            ->addMonthNoOverflow()
            ->startOfMonth();

        $this->referenceMonth = $newReference->toDateString();
        $this->selectedDate = $newReference->toDateString();
        $this->inputFecha = $this->selectedDate;
    }

    public function selectDate(string $date): void
    {
        $carbon = Carbon::parse($date);
        $this->selectedDate = $carbon->toDateString();
        $this->inputFecha = $this->selectedDate;
        $this->referenceMonth = $carbon->copy()->startOfMonth()->toDateString();
    }

    public function previousDay(): void
    {
        $prev = Carbon::parse($this->selectedDate)->subDay();
        $this->selectDate($prev->toDateString());
    }

    public function nextDay(): void
    {
        $next = Carbon::parse($this->selectedDate)->addDay();
        $this->selectDate($next->toDateString());
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function setFilterEstadoInterno(string $estado): void
    {
        $this->filterEstadoInterno = $estado;
    }

    public function render()
    {
        $analysis = app(AnalisisAsistenciaService::class);
        $referenceMonth = Carbon::parse($this->referenceMonth);
        $selectedDay = $analysis->detalleCalendarioDia($this->selectedDate);
        $branches = $analysis->sucursalesParaReportes();

        // 1. Filtrado por Código / Carnet / Nombre (tras presionar Buscar)
        $filterByCodeAndBranch = function (array $items) {
            if (filled($this->appliedCodigo)) {
                $codeSearch = strtolower($this->appliedCodigo);
                $items = array_values(array_filter($items, function ($item) use ($codeSearch) {
                    $codigo = strtolower((string) ($item['codigo'] ?? ''));
                    $carnet = strtolower((string) ($item['carnet'] ?? ''));
                    $nombre = strtolower((string) ($item['nombre'] ?? ''));
                    return str_contains($codigo, $codeSearch) || str_contains($carnet, $codeSearch) || str_contains($nombre, $codeSearch);
                }));
            }

            if (filled($this->appliedSucursal) && $this->appliedSucursal !== 'todas') {
                $targetKey = SucursalNormalizer::canonicalKey($this->appliedSucursal);
                $items = array_values(array_filter($items, function ($item) use ($targetKey) {
                    $itemBranch = $item['sucursal'] ?? '';
                    if ($targetKey !== null) {
                        return SucursalNormalizer::canonicalKey($itemBranch) === $targetKey;
                    }
                    return str_contains(strtolower($itemBranch), strtolower($this->appliedSucursal));
                }));
            }

            return $items;
        };

        $selectedDay['marcaciones'] = $filterByCodeAndBranch($selectedDay['marcaciones']);
        $selectedDay['tardanzas'] = $filterByCodeAndBranch($selectedDay['tardanzas']);
        $selectedDay['omisiones'] = $filterByCodeAndBranch($selectedDay['omisiones']);
        $selectedDay['faltas'] = $filterByCodeAndBranch($selectedDay['faltas']);
        $selectedDay['permisos'] = $filterByCodeAndBranch($selectedDay['permisos']);

        // 2. Filtro interno por estado (Todos, Completo, Sin completar)
        if ($this->filterEstadoInterno !== 'todos') {
            $selectedDay['marcaciones'] = array_values(array_filter($selectedDay['marcaciones'], function ($item) {
                return ($item['tipo_estado'] ?? '') === $this->filterEstadoInterno;
            }));
        }

        // 3. Ordenación de marcaciones
        usort($selectedDay['marcaciones'], function ($a, $b) {
            $valA = $a[$this->sortBy] ?? '';
            $valB = $b[$this->sortBy] ?? '';

            if (in_array($this->sortBy, ['entrada', 'salida'], true)) {
                $timeA = ($valA === '--:--' || blank($valA)) ? '99:99' : $valA;
                $timeB = ($valB === '--:--' || blank($valB)) ? '99:99' : $valB;
                $cmp = strcmp($timeA, $timeB);
            } else {
                $cmp = strnatcasecmp((string) $valA, (string) $valB);
            }

            return $this->sortDirection === 'asc' ? $cmp : -$cmp;
        });

        return view('livewire.calendario', [
            'calendar' => $analysis->calendarioLaboral($referenceMonth),
            'selectedDay' => $selectedDay,
            'branches' => $branches,
        ])->layout('layouts.app', ['title' => 'Calendario laboral y control de asistencia']);
    }
}


