<div class="page-stack" x-data="{ tab: 'resumen' }">

  {{-- ============================================================ --}}
  {{-- MODAL: DETALLE DE EMPLEADO                                   --}}
  {{-- ============================================================ --}}
  @if ($showEmployeeDetailModal)
    <div class="app-modal-backdrop" wire:click="closeEmployeeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeEmployeeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Detalle mensual</p>
            <h3 class="section-title app-modal-title">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Detalle del personal' }}</h3>
            <p class="section-copy-sm">Revision puntual de tardanzas, no marcados y faltas del mes seleccionado.</p>
          </div>
          <div class="app-modal-actions">
            <button type="button" wire:click="descargarPdfDetalleEmpleado" class="table-action-button">
              <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/><path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
              </svg>
              <span>PDF</span>
            </button>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Codigo</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo' }}</p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Sucursal</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal' }}</p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4 md:col-span-2">
            <p class="metric-label">Horario</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</p>
          </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
          @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
            <div class="rounded-[1.2rem] border border-slate-200 bg-white px-4 py-3 text-center">
              <p class="metric-label text-[11px]">{{ $metric['label'] }}</p>
              <p class="mt-2 text-lg font-bold text-slate-900">{{ $metric['value'] }}</p>
            </div>
          @endforeach
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
          {{-- Tardanzas --}}
          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
              <h4 class="text-base font-semibold text-slate-900">Días con Atraso</h4>
              <span class="status-badge status-warning">{{ count($detailEmployeeReport['tardanzas'] ?? []) }} atrasos</span>
            </div>
            <div class="report-scroll-list mt-4 space-y-3">
              @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $item)
                <div class="rounded-xl border border-amber-100 bg-amber-50/40 px-4 py-3">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                    <span class="font-bold text-amber-800 text-sm">{{ $item['retraso'] }}</span>
                  </div>
                  <p class="mt-1 text-xs text-slate-500">Entrada: <strong class="text-slate-700">{{ $item['entrada'] }}</strong> | Salida: {{ $item['salida'] }}</p>
                </div>
              @empty
                <p class="text-sm text-slate-400 py-4 text-center">No registra atrasos en el mes.</p>
              @endforelse
            </div>
          </div>

          {{-- Omisiones (olvidos de entrada/salida y días sin registro) --}}
          @php
            $omisionesEmpleado = array_merge(
              $detailEmployeeReport['no_marcados'] ?? [],
              $detailEmployeeReport['faltas'] ?? []
            );
          @endphp
          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
              <h4 class="text-base font-semibold text-slate-900">Registro de Omisiones</h4>
              <span class="status-badge status-danger">{{ count($omisionesEmpleado) }} omisiones</span>
            </div>
            <div class="report-scroll-list mt-4 space-y-3">
              @forelse($omisionesEmpleado as $item)
                <div class="rounded-xl border border-rose-100 bg-rose-50/40 px-4 py-3">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                    <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Omisión</span>
                  </div>
                  <p class="mt-1 text-xs text-slate-600">
                    Entrada: {{ $item['entrada'] ?? '--:--' }} | Salida: {{ $item['salida'] ?? '--:--' }}
                  </p>
                  <p class="mt-0.5 text-xs text-slate-500">
                    {{ $item['detalle'] ?? 'Marcación incompleta o día sin registro' }}
                  </p>
                </div>
              @empty
                <p class="text-sm text-slate-400 py-4 text-center">No registra omisiones en el mes.</p>
              @endforelse
            </div>
          </div>
        </div>

        {{-- PDF hidden content --}}
        <div id="reportes-detalle-empleado-pdf-content" class="hidden">
          <div class="pdf-export-sheet space-y-8">
            <header class="pdf-export-header">
              <div>
                <p class="pdf-export-kicker">Correos de Bolivia</p>
                <h2 class="pdf-export-title">Detalle mensual del personal</h2>
                <p class="pdf-export-copy">Tardanzas, no marcados y faltas del mes seleccionado.</p>
              </div>
              <div class="pdf-export-badge">
                <span class="pdf-export-badge-label">Mes</span>
                <strong class="pdf-export-badge-value">{{ $monthLabel }}</strong>
              </div>
            </header>
            <div class="pdf-export-grid pdf-export-grid-primary">
              <div class="pdf-export-card pdf-export-card-highlight">
                <p class="pdf-export-label">Nombre</p>
                <strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Sin nombre' }}</strong>
              </div>
              <div class="pdf-export-card"><p class="pdf-export-label">Codigo</p><strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo' }}</strong></div>
              <div class="pdf-export-card"><p class="pdf-export-label">Sucursal</p><strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal' }}</strong></div>
              <div class="pdf-export-card"><p class="pdf-export-label">Horario</p><strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</strong></div>
            </div>
            <div class="pdf-export-grid pdf-export-grid-secondary">
              @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
                <div class="pdf-export-card"><p class="pdf-export-label">{{ $metric['label'] }}</p><p class="pdf-export-value-sm">{{ $metric['value'] }}</p></div>
              @endforeach
            </div>
            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head"><div><p class="section-kicker">Tardanzas</p><h3 class="section-title">Dias tarde</h3></div></div>
              <table class="history-table"><thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Retraso</th><th>Estado</th></tr></thead>
                <tbody>
                  @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $item)
                    <tr><td>{{ $item['fecha'] }}</td><td>{{ $item['entrada'] }}</td><td>{{ $item['salida'] }}</td><td>{{ $item['retraso'] }}</td><td>{{ $item['estado'] }}</td></tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-slate-400">No tiene tardanzas en el mes.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ============================================================ --}}
  {{-- PDF HIDDEN EXPORT CONTENT                                    --}}
  {{-- ============================================================ --}}
  <div id="reportes-pdf-content" class="hidden">
    <div class="pdf-export-sheet space-y-8">
      <header class="pdf-export-header">
        <div>
          <p class="pdf-export-kicker">Correos de Bolivia</p>
          <h2 class="pdf-export-title">Reporte mensual de asistencia</h2>
          <p class="pdf-export-copy">Resumen ejecutivo con metricas, personal con mayor retraso e incidencias del mes.</p>
        </div>
        <div class="pdf-export-badge">
          <span class="pdf-export-badge-label">Mes</span>
          <strong class="pdf-export-badge-value">{{ $monthLabel }}</strong>
        </div>
      </header>
      <div class="pdf-export-grid pdf-export-grid-primary">
        @foreach($metrics as $metric)
          <div class="pdf-export-card"><p class="pdf-export-label">{{ $metric['label'] }}</p><strong class="pdf-export-value">{{ $metric['value'] }}</strong><p class="pdf-export-value-sm">{{ $metric['detail'] }}</p></div>
        @endforeach
      </div>
      <div class="pdf-export-table-shell">
        <div class="section-head-row pdf-export-section-head"><div><p class="section-kicker">Mayor retraso</p><h3 class="section-title">Personal con mayor retraso del mes</h3></div></div>
        <table class="history-table"><thead><tr><th>Personal</th><th>Sucursal</th><th>Dias tarde</th><th>Retraso</th></tr></thead>
          <tbody>
            @forelse($monthlyReport['top_employees'] as $employee)
              <tr><td>{{ $employee['nombre'] }}</td><td>{{ $employee['sucursal'] }}</td><td>{{ $employee['dias_tarde'] }}</td><td>{{ $employee['retraso'] }}</td></tr>
            @empty
              <tr><td colspan="4" class="text-center text-slate-400">No hay retrasos acumulados en el mes seleccionado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- HERO HEADER                                                  --}}
  {{-- ============================================================ --}}
  <div class="report-hero">
    <div class="report-hero-content">
      <div>
        <p class="report-hero-kicker">Módulo de reportes</p>
        <h1 class="report-hero-title">Reportes de Asistencia</h1>
        <p class="report-hero-copy">Análisis mensual · Sucursal activa: <strong>{{ $selectedBranch ?: 'Todas' }}</strong> · <span class="report-hero-month">{{ $monthLabel }}</span></p>
      </div>
      <div class="report-hero-actions flex items-center gap-2">
        <button type="button" onclick="window.print()" class="report-hero-pdf-btn !bg-white !text-slate-700 !border-slate-300 hover:!bg-slate-50 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
          <span>Imprimir</span>
        </button>
        <button type="button" wire:click="descargarPdfReporte" class="report-hero-pdf-btn">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 16V4"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
          </svg>
          <span>Exportar PDF</span>
        </button>
      </div>
    </div>

    {{-- Filtros Avanzados --}}
    <div class="report-filter-bar grid gap-4 md:grid-cols-2 xl:grid-cols-5">
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-reference-month">Periodo</label>
        <input id="hero-reference-month" type="month" wire:model.live="referenceMonth" class="report-filter-input">
      </div>
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-branch">Ciudad / Sucursal</label>
        <select id="hero-branch" wire:model.live="selectedBranch" class="report-filter-input">
          <option value="">Todas las sucursales</option>
          @foreach($branches as $branch)
            <option value="{{ $branch }}">{{ $branch }}</option>
          @endforeach
        </select>
      </div>
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-search">Buscar Personal / CI</label>
        <input id="hero-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre o Carnet..." class="report-filter-input">
      </div>
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-sort">Ordenamiento</label>
        <select id="hero-sort" wire:model.live="sortOrder" class="report-filter-input">
          <option value="fecha_desc">Fecha (más reciente)</option>
          <option value="fecha_asc">Fecha (más antigua)</option>
          <option value="nombre_asc">Nombre (A-Z)</option>
          <option value="retraso_desc">Mayor a menor retraso</option>
          <option value="retraso_asc">Menor a mayor retraso</option>
        </select>
      </div>
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-perpage">Mostrar por pág.</label>
        <select id="hero-perpage" wire:model.live="perPage" class="report-filter-input">
          <option value="10">10 por página</option>
          <option value="15">15 por página</option>
          <option value="20">20 por página</option>
          <option value="25">25 por página</option>
          <option value="30">30 por página</option>
        </select>
      </div>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB NAVIGATION                                               --}}
  {{-- ============================================================ --}}
  <div class="report-tab-nav" role="tablist">
    <button type="button" role="tab" :aria-selected="tab === 'resumen'" @click="tab = 'resumen'"
      :class="tab === 'resumen' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-resumen">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      <span>Resumen</span>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'atrasos'" @click="tab = 'atrasos'"
      :class="tab === 'atrasos' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-atrasos">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
      <span>Atrasos</span>
      @if(($totalAtrasos ?? 0) > 0)
        <span class="report-tab-badge report-tab-badge-amber">{{ $totalAtrasos }}</span>
      @endif
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'omisiones'" @click="tab = 'omisiones'"
      :class="tab === 'omisiones' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-omisiones">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/><circle cx="12" cy="16" r="1.5"/></svg>
      <span>Omisiones</span>
      @if(($totalOmisiones ?? 0) > 0)
        <span class="report-tab-badge report-tab-badge-rose">{{ $totalOmisiones }}</span>
      @endif
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'cumpleanos'" @click="tab = 'cumpleanos'"
      :class="tab === 'cumpleanos' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-cumpleanos">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 3c0 0 1-2 1-2"/></svg>
      <span>Cumpleaños</span>
      @if(count($cumpleanos) > 0)
        <span class="report-tab-badge report-tab-badge-emerald">{{ count($cumpleanos) }}</span>
      @endif
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'ranking'" @click="tab = 'ranking'"
      :class="tab === 'ranking' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-ranking">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20V10"/><path d="M12 20V4"/><path d="M18 20v-6"/></svg>
      <span>Ranking</span>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'antiguedad'" @click="tab = 'antiguedad'"
      :class="tab === 'antiguedad' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-antiguedad">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
      <span>Antigüedad</span>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'reglamento'" @click="tab = 'reglamento'"
      :class="tab === 'reglamento' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-reglamento">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Reglamento</span>
      @if((($reporteReglamento['metricas']['en_alerta_preventiva'] ?? 0) + ($reporteReglamento['metricas']['con_sancion_economica'] ?? 0) + ($reporteReglamento['metricas']['riesgo_critico'] ?? 0)) > 0)
        <span class="report-tab-badge report-tab-badge-amber">
          {{ ($reporteReglamento['metricas']['en_alerta_preventiva'] ?? 0) + ($reporteReglamento['metricas']['con_sancion_economica'] ?? 0) + ($reporteReglamento['metricas']['riesgo_critico'] ?? 0) }}
        </span>
      @endif
    </button>
    @if($reportePersonal)
    <button type="button" role="tab" :aria-selected="tab === 'mi-reporte'" @click="tab = 'mi-reporte'"
      :class="tab === 'mi-reporte' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-mi-reporte">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      <span>Mi Reporte</span>
    </button>
    @endif
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 1: RESUMEN                                               --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'resumen'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Encabezado solo para impresión física --}}
    <div class="hidden print:block mb-6 border-b-2 border-slate-900 pb-3">
      <p class="text-xs uppercase font-bold text-slate-500 tracking-wider">Agencia Boliviana de Correos · Recursos Humanos</p>
      <h2 class="text-xl font-bold text-slate-900 mt-1">Reporte Consolidado de Asistencia y Puntualidad</h2>
      <p class="text-xs text-slate-600">Periodo: {{ $monthLabel }} | Sucursal: {{ $selectedBranch ?: 'Todas las sucursales' }} | Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Resumen Ejecutivo Superior --}}
    <section class="mb-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Personal evaluado</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $reporteSucursales['total_empleados'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Activos en el periodo</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Atrasos acumulados</p>
        <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($reporteSucursales['total_minutos_atraso'] ?? 0) }} min</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">{{ $reporteSucursales['total_minutos_formato'] ?? '0 min' }}</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Días con atraso</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $reporteSucursales['total_dias_atraso'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Llegadas tardías totales</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Omisiones</p>
        <p class="mt-2 text-2xl font-bold text-rose-700">{{ $reporteSucursales['total_omisiones'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Días sin marcar y sin registro</p>
      </div>
    </section>

    {{-- Reporte Consolidado por Sucursal con títulos destacados --}}
    <section class="space-y-6 mb-8">
      @forelse(($reporteSucursales['sucursales'] ?? []) as $sucursal)
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs branch-print-section">
          <!-- Título de la Sucursal -->
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/90 px-5 py-3.5">
            <div class="flex items-center gap-3">
              <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-800 text-xs font-bold text-white uppercase">
                {{ substr($sucursal['sucursal'], 0, 2) }}
              </span>
              <h3 class="text-base font-bold text-slate-900 uppercase tracking-wide">
                {{ $sucursal['sucursal'] }}
              </h3>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-600">
              <span class="rounded-md bg-white border border-slate-200 px-2.5 py-1">
                <strong>{{ $sucursal['total_empleados'] }}</strong> personal
              </span>
              <span class="rounded-md bg-white border border-slate-200 px-2.5 py-1">
                Atrasos: <strong class="text-amber-800">{{ number_format($sucursal['total_minutos_atraso']) }} min</strong> ({{ $sucursal['total_minutos_formato'] }})
              </span>
              <span class="rounded-md bg-white border border-slate-200 px-2.5 py-1">
                Omisiones: <strong class="text-rose-700">{{ $sucursal['total_omisiones'] }}</strong>
              </span>
            </div>
          </div>

          <!-- Tabla del personal de la sucursal -->
          <div class="overflow-x-auto">
            <table class="history-table w-full text-left">
              <thead>
                <tr>
                  <th class="w-10 text-center">#</th>
                  <th class="w-24">CI / Código</th>
                  <th>Personal</th>
                  <th>Área / Cargo</th>
                  <th class="text-center">Atraso Sumado</th>
                  <th class="text-center">Días Tarde</th>
                  <th class="text-center">Omisiones</th>
                  <th class="text-center">Asistencia</th>
                  <th class="text-right no-print w-28">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @forelse($sucursal['empleados'] as $idx => $emp)
                  <tr class="hover:bg-slate-50/60 transition">
                    <td class="text-center font-semibold text-slate-400 text-xs">{{ $idx + 1 }}</td>
                    <td><span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $emp['codigo'] ?: '-' }}</span></td>
                    <td>
                      <span class="font-semibold text-slate-900">{{ $emp['nombre'] }}</span>
                    </td>
                    <td class="text-xs text-slate-600">{{ $emp['area'] }}</td>
                    <td class="text-center">
                      @if($emp['minutos_atraso'] > 0)
                        <span class="font-bold text-amber-900 text-sm">{{ $emp['minutos_atraso'] }} min</span>
                        <span class="block text-[11px] text-slate-400">({{ $emp['minutos_atraso_formato'] }})</span>
                      @else
                        <span class="text-slate-400 text-xs">0 min</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($emp['dias_atraso'] > 0)
                        <span class="inline-flex items-center justify-center rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                          {{ $emp['dias_atraso'] }}
                        </span>
                      @else
                        <span class="text-slate-400 text-xs">0</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($emp['omisiones'] > 0)
                        <span class="inline-flex items-center justify-center rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                          {{ $emp['omisiones'] }}
                        </span>
                      @else
                        <span class="text-slate-400 text-xs">0</span>
                      @endif
                    </td>
                    <td class="text-center text-xs text-slate-600">
                      {{ $emp['dias_asistidos'] }} / {{ $emp['dias_laborables'] }}
                    </td>
                    <td class="text-right no-print">
                      <button type="button" wire:click="openEmployeeDetailModal({{ $emp['id'] }})" class="table-action-button text-xs py-1 px-2.5">
                        Ver detalle
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="py-6 text-center text-slate-400 text-sm">
                      No hay registros que coincidan con la búsqueda en esta sucursal.
                    </td>
                  </tr>
                @endforelse

                @if(count($sucursal['empleados']) > 0)
                  <tr class="bg-slate-50/80 font-semibold text-slate-800 text-xs border-t-2 border-slate-200">
                    <td colspan="4" class="text-right py-2.5 px-4 uppercase tracking-wider text-slate-500">
                      Subtotal {{ $sucursal['sucursal'] }}:
                    </td>
                    <td class="text-center py-2.5">
                      <span class="text-amber-900 font-bold">{{ number_format($sucursal['total_minutos_atraso']) }} min</span>
                    </td>
                    <td class="text-center py-2.5">{{ $sucursal['total_dias_atraso'] }}</td>
                    <td class="text-center py-2.5 text-rose-700 font-bold">{{ $sucursal['total_omisiones'] }}</td>
                    <td class="text-center py-2.5">-</td>
                    <td class="no-print"></td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-400">
          No se encontraron sucursales ni personal para el periodo y filtros seleccionados.
        </div>
      @endforelse
    </section>

    {{-- Gráfico de frecuencia --}}
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Frecuencias mensuales</p>
          <h2 class="section-title">Marcaciones por mes</h2>
        </div>
        <p class="section-copy-sm">{{ $monthLabel }}</p>
      </div>

      <div class="attendance-chart-shell">
        <div class="attendance-chart-summary">
          <div class="attendance-chart-pill">
            <span class="attendance-chart-pill-label">Mes actual</span>
            <strong class="attendance-chart-pill-value">{{ $frequency['summary']['current_count'] }}</strong>
          </div>
          <div class="attendance-chart-pill">
            <span class="attendance-chart-pill-label">Pico del periodo</span>
            <strong class="attendance-chart-pill-value">{{ $frequency['summary']['peak_label'] }} · {{ $frequency['summary']['peak_count'] }}</strong>
          </div>
        </div>
        <div class="attendance-chart-frame">
          <div class="attendance-chart-scale" aria-hidden="true">
            <span>{{ $frequency['scale']['max'] }}</span>
            <span>{{ $frequency['scale']['mid'] }}</span>
            <span>{{ $frequency['scale']['min'] }}</span>
          </div>
          <div class="attendance-chart">
            @foreach($frequency['bars'] as $bar)
              <div class="attendance-bar-group">
                <button type="button" wire:click="selectReferenceMonth('{{ $bar['value'] }}')"
                  class="flex h-full w-full flex-col items-center justify-end text-left transition-transform duration-200 hover:-translate-y-1 focus:outline-none">
                  <div class="attendance-bar-track">
                    <div class="attendance-bar {{ $bar['active'] ? 'attendance-bar-active' : '' }} {{ $bar['is_peak'] ? 'attendance-bar-peak' : '' }}" style="height: {{ $bar['height'] }};"></div>
                  </div>
                  <span class="attendance-bar-label {{ $bar['active'] ? 'attendance-bar-label-active' : '' }} block">{{ $bar['label'] }}</span>
                  <span class="attendance-bar-count block">{{ $bar['count'] }}</span>
                </button>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Métricas del mes --}}
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($monthlyReport['metrics'] as $metric)
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">{{ $metric['label'] }}</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $metric['value'] }}</p>
          </div>
        @endforeach
      </div>

      {{-- Top atrasados --}}
      <div class="mt-8 rounded-[1.4rem] border border-slate-200 bg-white px-5 py-5">
        <div class="flex items-center justify-between gap-4">
          <h3 class="text-lg font-semibold text-slate-900">Personal con mayor retraso del mes</h3>
          <span class="status-badge status-warning">{{ $monthlyReport['late_days'] }} dias tarde</span>
        </div>
        <div class="report-scroll-list mt-5 space-y-3">
          @forelse($monthlyReport['top_employees'] as $employee)
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-900">{{ $employee['nombre'] }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ $employee['sucursal'] }} | {{ $employee['dias_tarde'] }} dias tarde | {{ $employee['retraso'] }}</p>
                </div>
                <button type="button" wire:click="openEmployeeDetailModal({{ $employee['empleado_id'] }})" class="table-action-button">Ver detalle</button>
              </div>
            </div>
          @empty
            <p class="text-sm text-slate-400">No hay retrasos acumulados en el mes seleccionado.</p>
          @endforelse
        </div>
      </div>
    </section>

    {{-- Incidencias --}}
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Incidencias filtradas</p>
          <h2 class="section-title">Incidencias del mes</h2>
        </div>
        <p class="section-copy-sm">{{ $monthLabel }}</p>
      </div>
      <div class="diagnostic-grid mt-8">
        <div class="diagnostic-card">
          <div class="flex items-center justify-between gap-3">
            <h4 class="text-base font-semibold text-slate-900">Incidencias justificadas</h4>
            <span class="status-badge status-available">{{ count($incidents['permisos']) }}</span>
          </div>
          <div class="report-scroll-list mt-4 space-y-3">
            @forelse($incidents['permisos'] as $item)
              <div class="rounded-xl bg-slate-50 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
              </div>
            @empty
              <p class="text-sm text-slate-400">No hay incidencias justificadas en el rango.</p>
            @endforelse
          </div>
        </div>
        <div class="diagnostic-card">
          <div class="flex items-center justify-between gap-3">
            <h4 class="text-base font-semibold text-slate-900">Ausencias injustificadas</h4>
            <span class="status-badge status-warning">{{ count($incidents['faltas']) }}</span>
          </div>
          <div class="report-scroll-list mt-4 space-y-3">
            @forelse($incidents['faltas'] as $item)
              <div class="rounded-xl bg-slate-50 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
              </div>
            @empty
              <p class="text-sm text-slate-400">No hay faltas injustificadas en el rango.</p>
            @endforelse
          </div>
        </div>
        <div class="diagnostic-card xl:col-span-2">
          <div class="flex items-center justify-between gap-3">
            <h4 class="text-base font-semibold text-slate-900">Olvidos de marcar</h4>
            <span class="status-badge status-danger">{{ count($incidents['olvidos']) }}</span>
          </div>
          <div class="report-scroll-list mt-4 grid gap-4 md:grid-cols-2">
            @forelse($incidents['olvidos'] as $item)
              <div class="rounded-xl bg-slate-50 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
              </div>
            @empty
              <p class="text-sm text-slate-400">No existen olvidos de marcacion en el rango.</p>
            @endforelse
          </div>
        </div>
      </div>
    </section>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 2: ATRASOS                                               --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'atrasos'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Resumen Ejecutivo Superior de Atrasos --}}
    <section class="mb-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total atrasos</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($atrasosStats['total_registros'] ?? 0) }}</p>
        <p class="text-xs text-slate-400 mt-1">Registros con llegada tarde</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Minutos acumulados</p>
        <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($atrasosStats['total_minutos'] ?? 0) }} min</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">{{ $atrasosStats['total_minutos_formato'] ?? '0 min' }}</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Personal con atraso</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $atrasosStats['personal_afectado'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Funcionarios observados</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Promedio por atraso</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $atrasosStats['promedio_minutos'] ?? 0 }} min</p>
        <p class="text-xs text-slate-400 mt-1">Máximo: {{ $atrasosStats['maximo_minutos'] ?? 0 }} min</p>
      </div>
    </section>

    {{-- Desglose rápido por Sucursal --}}
    @if(($atrasosStats['por_sucursal'] ?? null) && count($atrasosStats['por_sucursal']) > 0)
      <div class="mb-6 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600 flex flex-wrap items-center gap-2 shadow-xs">
        <span class="font-bold text-slate-700 uppercase tracking-wider mr-1">Por sucursal:</span>
        @foreach($atrasosStats['por_sucursal'] as $sucName => $sucData)
          <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-700">
            <strong class="uppercase text-slate-800">{{ $sucName }}:</strong>
            <span class="text-amber-800 font-bold">{{ $sucData['count'] }} tarde</span>
            <span class="text-slate-400">({{ $sucData['formato'] }})</span>
          </span>
        @endforeach
      </div>
    @endif

    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Detalle individual</p>
          <h2 class="section-title">Registro de Atrasos del Mes</h2>
          <p class="section-copy-sm">Marcaciones con ingreso posterior a la tolerancia en {{ $monthLabel }}.</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="status-badge status-warning font-bold">{{ $totalAtrasos }} registros</span>
          <button type="button" wire:click="descargarPdfReporte" class="section-action-button no-print">PDF</button>
        </div>
      </div>
      <div class="history-table-shell mt-6">
        <table class="history-table">
          <thead>
            <tr>
              <th class="w-12 text-center">#</th>
              <th>Personal</th>
              <th>CI / Código</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th>Hora prog.</th>
              <th>Hora real</th>
              <th class="text-center">Retraso</th>
              <th class="text-center">Estado</th>
              <th class="text-right no-print w-24">Acción</th>
            </tr>
          </thead>
          <tbody>
            @forelse($detalleAtrasos as $index => $item)
              <tr class="report-atraso-row hover:bg-slate-50/60 transition">
                <td class="text-center font-bold text-slate-400 text-xs">{{ ($detalleAtrasos->currentPage() - 1) * $detalleAtrasos->perPage() + $index + 1 }}</td>
                <td><span class="font-semibold text-slate-900">{{ $item['nombre'] }}</span></td>
                <td><span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $item['codigo'] ?: '-' }}</span></td>
                <td class="text-xs text-slate-600">{{ $item['sucursal'] }}</td>
                <td class="text-xs font-medium text-slate-700">{{ $item['fecha'] }}</td>
                <td class="text-xs text-slate-500">{{ $item['entrada_programada'] }}</td>
                <td class="text-xs font-bold text-slate-800">{{ $item['entrada_real'] }}</td>
                <td class="text-center">
                  <span class="inline-flex items-center rounded-md bg-amber-50 border border-amber-200 px-2 py-0.5 text-xs font-bold text-amber-800">
                    {{ $item['retraso'] }}
                  </span>
                </td>
                <td class="text-center text-xs text-slate-500">{{ $item['estado'] }}</td>
                <td class="text-right no-print">
                  @if(!empty($item['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $item['empleado_id'] }})" class="table-action-button text-xs py-1 px-2">
                      Ver detalle
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="py-12 text-center text-slate-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                  No hay atrasos registrados que coincidan con los filtros.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($detalleAtrasos->hasPages())
        <div class="mt-6">
          {{ $detalleAtrasos->links() }}
        </div>
      @endif
    </section>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 3: OMISIONES                                             --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'omisiones'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Resumen Ejecutivo Superior de Omisiones --}}
    <section class="mb-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Omisiones</p>
        <p class="mt-2 text-2xl font-bold text-rose-700">{{ number_format($omisionesStats['total_omisiones'] ?? 0) }}</p>
        <p class="text-xs text-slate-400 mt-1">Registros observados</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Días sin marcar</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($omisionesStats['dias_sin_marcar'] ?? 0) }}</p>
        <p class="text-xs text-slate-400 mt-1">Jornada completa sin asistencia</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Marcaciones incompletas</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($omisionesStats['marcas_incompletas'] ?? 0) }}</p>
        <p class="text-xs text-slate-400 mt-1">Olvidos de entrada o salida</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Personal observado</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $omisionesStats['personal_afectado'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Funcionarios con omisión</p>
      </div>
    </section>

    {{-- Desglose rápido por Sucursal --}}
    @if(($omisionesStats['por_sucursal'] ?? null) && count($omisionesStats['por_sucursal']) > 0)
      <div class="mb-6 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600 flex flex-wrap items-center gap-2 shadow-xs">
        <span class="font-bold text-slate-700 uppercase tracking-wider mr-1">Por sucursal:</span>
        @foreach($omisionesStats['por_sucursal'] as $sucName => $sucCount)
          <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-700">
            <strong class="uppercase text-slate-800">{{ $sucName }}:</strong>
            <span class="text-rose-700 font-bold">{{ $sucCount['count'] ?? $sucCount }} omisiones</span>
          </span>
        @endforeach
      </div>
    @endif

    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Control de asistencia</p>
          <h2 class="section-title">Registro de Omisiones</h2>
          <p class="section-copy-sm">Días laborables sin marcación y registros incompletos (olvidos de entrada/salida) en {{ $monthLabel }}.</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="status-badge status-danger font-bold">{{ $totalOmisiones }} omisiones</span>
          <button type="button" wire:click="descargarPdfReporte" class="section-action-button no-print">PDF</button>
        </div>
      </div>
      <div class="history-table-shell mt-6">
        <table class="history-table">
          <thead>
            <tr>
              <th class="w-12 text-center">#</th>
              <th>Personal</th>
              <th>CI / Código</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th class="text-center">Entrada</th>
              <th class="text-center">Salida</th>
              <th class="text-center">Estado</th>
              <th>Detalle / Observación</th>
              <th class="text-right no-print w-24">Acción</th>
            </tr>
          </thead>
          <tbody>
            @forelse($detalleOmisiones as $index => $item)
              <tr class="report-omision-row hover:bg-slate-50/60 transition">
                <td class="text-center font-bold text-slate-400 text-xs">{{ ($detalleOmisiones->currentPage() - 1) * $detalleOmisiones->perPage() + $index + 1 }}</td>
                <td><span class="font-semibold text-slate-900">{{ $item['nombre'] }}</span></td>
                <td><span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $item['codigo'] ?: '-' }}</span></td>
                <td class="text-xs text-slate-600">{{ $item['sucursal'] }}</td>
                <td class="text-xs font-medium text-slate-700">{{ $item['fecha'] }}</td>
                <td class="text-center text-xs {{ blank($item['entrada'] ?? '') || ($item['entrada'] ?? '') === '--:--' ? 'text-rose-600 font-semibold' : 'text-slate-700' }}">{{ $item['entrada'] ?? '--:--' }}</td>
                <td class="text-center text-xs {{ blank($item['salida'] ?? '') || ($item['salida'] ?? '') === '--:--' ? 'text-rose-600 font-semibold' : 'text-slate-700' }}">{{ $item['salida'] ?? '--:--' }}</td>
                <td class="text-center"><span class="status-badge status-danger font-bold">Omisión</span></td>
                <td class="text-xs text-slate-500">{{ $item['detalle'] ?? 'Marcación incompleta o día sin registro' }}</td>
                <td class="text-right no-print">
                  @if(!empty($item['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $item['empleado_id'] }})" class="table-action-button text-xs py-1 px-2">
                      Ver detalle
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="py-12 text-center text-slate-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/></svg>
                  No hay omisiones de marcacion que coincidan con los filtros.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($detalleOmisiones->hasPages())
        <div class="mt-6">
          {{ $detalleOmisiones->links() }}
        </div>
      @endif
    </section>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 4: CUMPLEAÑOS                                            --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'cumpleanos'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Celebraciones del mes</p>
          <h2 class="section-title">Cumpleaños — {{ $monthLabel }}</h2>
          <p class="section-copy-sm">Personal que cumple años durante este mes.</p>
        </div>
        <span class="status-badge status-available">{{ count($cumpleanos) }} cumpleañeros</span>
      </div>

      @if(count($cumpleanos) > 0)
        {{-- Hoy --}}
        @php $hoy = collect($cumpleanos)->where('es_hoy', true); @endphp
        @if($hoy->count() > 0)
          <div class="birthday-today-banner mt-8">
            <div class="birthday-today-icon">🎂</div>
            <div>
              <p class="birthday-today-label">¡Hoy es el cumpleaños de!</p>
              <p class="birthday-today-names">{{ $hoy->pluck('nombre')->join(', ') }}</p>
            </div>
          </div>
        @endif

        <div class="birthday-grid mt-8">
          @foreach($cumpleanos as $persona)
            <div class="birthday-card {{ $persona['es_hoy'] ? 'birthday-card-today' : ($persona['es_esta_semana'] ? 'birthday-card-week' : '') }}">
              <div class="birthday-avatar {{ $persona['es_hoy'] ? 'birthday-avatar-today' : '' }}">
                {{ $persona['inicial'] }}
              </div>
              <div class="birthday-info">
                <p class="birthday-name">{{ $persona['nombre'] }}</p>
                <p class="birthday-meta">{{ $persona['area'] }} · {{ $persona['sucursal'] }}</p>
                <div class="birthday-date-row">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                  <span>{{ $persona['fecha_label'] }} · {{ $persona['edad'] }} años</span>
                </div>
              </div>
              <div class="birthday-chips">
                @if($persona['es_hoy'])
                  <span class="birthday-chip birthday-chip-today">Hoy 🎉</span>
                @elseif($persona['es_esta_semana'])
                  <span class="birthday-chip birthday-chip-week">Esta semana</span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="py-16 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl">🎂</div>
          <p class="text-lg font-semibold text-slate-700">Sin cumpleaños este mes</p>
          <p class="mt-2 text-sm text-slate-400">No hay empleados que cumplan años en {{ $monthLabel }}.</p>
        </div>
      @endif
    </section>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 5: RANKING                                               --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'ranking'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Resumen Ejecutivo Superior de Ranking --}}
    <section class="mb-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Líder puntual mensual</p>
        <p class="mt-2 text-base font-bold text-slate-900 truncate">
          {{ $rankingMensual['mas_puntuales'][0]['nombre'] ?? 'Sin datos' }}
        </p>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          @if(!empty($rankingMensual['mas_puntuales']))
            {{ $rankingMensual['mas_puntuales'][0]['retraso_label'] }} · {{ $rankingMensual['mas_puntuales'][0]['dias_marcados'] }} días
          @else
            Sin registros en el mes
          @endif
        </p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Mayor demora mensual</p>
        <p class="mt-2 text-base font-bold text-slate-900 truncate">
          {{ $rankingMensual['mas_atrasados'][0]['nombre'] ?? 'Sin demoras' }}
        </p>
        <p class="text-xs text-amber-900 mt-1 font-semibold">
          @if(!empty($rankingMensual['mas_atrasados']))
            {{ $rankingMensual['mas_atrasados'][0]['retraso_label'] }} ({{ $rankingMensual['mas_atrasados'][0]['dias_tarde'] }} días tarde)
          @else
            0 min retraso
          @endif
        </p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Líder semanal</p>
        <p class="mt-2 text-base font-bold text-slate-900 truncate">
          {{ $rankingSemanal['mas_puntuales'][0]['nombre'] ?? 'Sin datos' }}
        </p>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          @if(!empty($rankingSemanal['mas_puntuales']))
            {{ $rankingSemanal['mas_puntuales'][0]['retraso_label'] }} esta semana
          @else
            Sin registros
          @endif
        </p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Mayor demora semanal</p>
        <p class="mt-2 text-base font-bold text-slate-900 truncate">
          {{ $rankingSemanal['mas_atrasados'][0]['nombre'] ?? 'Sin demoras' }}
        </p>
        <p class="text-xs text-amber-900 mt-1 font-semibold">
          @if(!empty($rankingSemanal['mas_atrasados']))
            {{ $rankingSemanal['mas_atrasados'][0]['retraso_label'] }} ({{ $rankingSemanal['mas_atrasados'][0]['dias_tarde'] }} días tarde)
          @else
            0 min retraso
          @endif
        </p>
      </div>
    </section>

    {{-- Ranking Mensual --}}
    <div class="mb-8">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Ranking Mensual — {{ $monthLabel }}</h3>
          <p class="text-xs text-slate-500">Evaluación consolidada del personal activo durante el mes calendario.</p>
        </div>
        <button type="button" wire:click="descargarPdfReporte" class="section-action-button no-print">Imprimir / PDF</button>
      </div>

      <div class="grid gap-6 xl:grid-cols-2">
        {{-- Más puntuales del mes --}}
        <section class="surface-card">
          <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Puntualidad Destacada</p>
              <h4 class="text-base font-bold text-slate-900">Personal más puntual</h4>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200">
              Top {{ count($rankingMensual['mas_puntuales'] ?? []) }}
            </span>
          </div>
          <div class="space-y-3">
            @forelse($rankingMensual['mas_puntuales'] ?? [] as $i => $emp)
              <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $i === 0 ? 'bg-slate-900 text-white' : ($i === 1 ? 'bg-slate-700 text-white' : ($i === 2 ? 'bg-slate-500 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200')) }} text-xs font-bold">
                  #{{ $i + 1 }}
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-700 border border-slate-200 text-sm">
                  {{ $emp['inicial'] }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                  <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">{{ $emp['dias_marcados'] }} días con registro</p>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                  <span class="inline-block rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-800">
                    {{ $emp['retraso_label'] }}
                  </span>
                  @if(!empty($emp['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $emp['empleado_id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                  @endif
                </div>
              </div>
            @empty
              <p class="py-8 text-center text-sm text-slate-400">Sin datos suficientes para el mes seleccionado.</p>
            @endforelse
          </div>
        </section>

        {{-- Más atrasados del mes --}}
        <section class="surface-card">
          <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Mayor Demora Acumulada</p>
              <h4 class="text-base font-bold text-slate-900">Personal con más retraso</h4>
            </div>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900 border border-amber-200">
              Top {{ count($rankingMensual['mas_atrasados'] ?? []) }}
            </span>
          </div>
          <div class="space-y-3">
            @forelse($rankingMensual['mas_atrasados'] ?? [] as $i => $emp)
              <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $i === 0 ? 'bg-amber-900 text-white' : ($i === 1 ? 'bg-amber-800 text-white' : ($i === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200')) }} text-xs font-bold">
                  #{{ $i + 1 }}
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 font-bold text-amber-900 border border-amber-200 text-sm">
                  {{ $emp['inicial'] }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                  <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                  <p class="text-[11px] text-amber-900 mt-0.5 font-medium">{{ $emp['dias_tarde'] }} días con retraso</p>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                  <span class="inline-block rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900">
                    {{ $emp['retraso_label'] }}
                  </span>
                  @if(!empty($emp['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $emp['empleado_id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                  @endif
                </div>
              </div>
            @empty
              <p class="py-8 text-center text-sm text-slate-400">Sin atrasos registrados en el mes seleccionado.</p>
            @endforelse
          </div>
        </section>
      </div>
    </div>

    {{-- Ranking Semanal --}}
    <div>
      <div class="mb-4">
        <h3 class="text-lg font-bold text-slate-900">Ranking Semanal</h3>
        <p class="text-xs text-slate-500">Comportamiento de asistencia durante la semana en curso.</p>
      </div>

      <div class="grid gap-6 xl:grid-cols-2">
        {{-- Más puntuales de la semana --}}
        <section class="surface-card">
          <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Semana en Curso</p>
              <h4 class="text-base font-bold text-slate-900">Más puntuales de la semana</h4>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200">
              Top {{ count($rankingSemanal['mas_puntuales'] ?? []) }}
            </span>
          </div>
          <div class="space-y-3">
            @forelse($rankingSemanal['mas_puntuales'] ?? [] as $i => $emp)
              <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-700 border border-slate-200 text-xs">
                  #{{ $i + 1 }}
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-700 border border-slate-200 text-sm">
                  {{ $emp['inicial'] }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                  <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                  <span class="inline-block rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-800">
                    {{ $emp['retraso_label'] }}
                  </span>
                  @if(!empty($emp['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $emp['empleado_id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                  @endif
                </div>
              </div>
            @empty
              <p class="py-8 text-center text-sm text-slate-400">Sin datos suficientes para la semana actual.</p>
            @endforelse
          </div>
        </section>

        {{-- Más atrasados de la semana --}}
        <section class="surface-card">
          <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Semana en Curso</p>
              <h4 class="text-base font-bold text-slate-900">Más atrasados de la semana</h4>
            </div>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900 border border-amber-200">
              Top {{ count($rankingSemanal['mas_atrasados'] ?? []) }}
            </span>
          </div>
          <div class="space-y-3">
            @forelse($rankingSemanal['mas_atrasados'] ?? [] as $i => $emp)
              <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 font-bold text-amber-900 border border-amber-200 text-xs">
                  #{{ $i + 1 }}
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 font-bold text-amber-900 border border-amber-200 text-sm">
                  {{ $emp['inicial'] }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                  <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                  <p class="text-[11px] text-amber-900 mt-0.5 font-medium">{{ $emp['dias_tarde'] }} días con retraso</p>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                  <span class="inline-block rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900">
                    {{ $emp['retraso_label'] }}
                  </span>
                  @if(!empty($emp['empleado_id']))
                    <button type="button" wire:click="openEmployeeDetailModal({{ $emp['empleado_id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                  @endif
                </div>
              </div>
            @empty
              <p class="py-8 text-center text-sm text-slate-400">Sin atrasos registrados esta semana.</p>
            @endforelse
          </div>
        </section>
      </div>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 6: ANTIGÜEDAD                                            --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'antiguedad'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Resumen Ejecutivo Superior de Antigüedad --}}
    <section class="mb-6 grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Personal evaluado</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($reportesAntiguedad['metricas']['total_registrados'] ?? 0) }}</p>
        <p class="text-xs text-slate-400 mt-1">Con fecha de contrato</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">+10 Años trayectoria</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $reportesAntiguedad['metricas']['veteranos_10_anios'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Personal consolidado</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ingresos recientes</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $reportesAntiguedad['metricas']['incorporaciones_recientes'] ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">Menos de 1 año de servicio</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Mayor antigüedad</p>
        <p class="mt-2 text-lg font-bold text-slate-900 truncate">{{ $reportesAntiguedad['metricas']['maxima_trayectoria'] ?? '0 años' }}</p>
        <p class="text-xs text-slate-500 mt-1 truncate">{{ $reportesAntiguedad['metricas']['maximo_veterano'] ?? 'Personal' }}</p>
      </div>
    </section>

    <div class="mb-4 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Reporte de Trayectoria Institucional</h3>
        <p class="text-xs text-slate-500">Clasificación de personal por tiempo de vinculación laboral.</p>
      </div>
      <button type="button" wire:click="descargarPdfReporte" class="section-action-button no-print">Imprimir / PDF</button>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
      {{-- Personal más antiguo --}}
      <section class="surface-card">
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Mayor Trayectoria</p>
            <h4 class="text-base font-bold text-slate-900">Personal más antiguo</h4>
          </div>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200">
            Top {{ count($reportesAntiguedad['mas_antiguos'] ?? []) }}
          </span>
        </div>
        <div class="space-y-3">
          @forelse($reportesAntiguedad['mas_antiguos'] ?? [] as $i => $emp)
            <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 font-bold text-white text-xs">
                #{{ $i + 1 }}
              </div>
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-800 border border-slate-200 text-sm">
                {{ $emp['inicial'] }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">CI: <span class="font-mono text-slate-600">{{ $emp['codigo'] }}</span> · Ingreso: {{ $emp['fecha_contratacion'] }}</p>
              </div>
              <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                <span class="inline-block rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-800">
                  {{ $emp['antiguedad_texto'] }}
                </span>
                @if(!empty($emp['id']))
                  <button type="button" wire:click="openEmployeeDetailModal({{ $emp['id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                @endif
              </div>
            </div>
          @empty
            <p class="py-8 text-center text-sm text-slate-400">No se registraron fechas de contratación para el personal.</p>
          @endforelse
        </div>
      </section>

      {{-- Personal más nuevo --}}
      <section class="surface-card">
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Reciente Ingreso</p>
            <h4 class="text-base font-bold text-slate-900">Personal más nuevo</h4>
          </div>
          <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800 border border-sky-200">
            Top {{ count($reportesAntiguedad['mas_nuevos'] ?? []) }}
          </span>
        </div>
        <div class="space-y-3">
          @forelse($reportesAntiguedad['mas_nuevos'] ?? [] as $i => $emp)
            <div class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-slate-300">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-100 font-bold text-sky-900 border border-sky-200 text-xs">
                #{{ $i + 1 }}
              </div>
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-800 border border-slate-200 text-sm">
                {{ $emp['inicial'] }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="truncate font-semibold text-slate-900 text-sm">{{ $emp['nombre'] }}</p>
                <p class="truncate text-xs text-slate-500">{{ $emp['area'] }} · {{ $emp['sucursal'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">CI: <span class="font-mono text-slate-600">{{ $emp['codigo'] }}</span> · Ingreso: {{ $emp['fecha_contratacion'] }}</p>
              </div>
              <div class="shrink-0 text-right flex flex-col items-end gap-1.5">
                <span class="inline-block rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-800">
                  {{ $emp['antiguedad_texto'] }}
                </span>
                @if(!empty($emp['id']))
                  <button type="button" wire:click="openEmployeeDetailModal({{ $emp['id'] }})" class="table-action-button text-[11px] py-0.5 px-2 no-print">Ver detalle</button>
                @endif
              </div>
            </div>
          @empty
            <p class="py-8 text-center text-sm text-slate-400">No se registraron fechas de contratación para el personal.</p>
          @endforelse
        </div>
      </section>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 7: REGLAMENTO Y SANCIONES                                --}}
  {{-- ============================================================ --}}
  <div x-show="tab === 'reglamento'" x-transition.opacity.duration.200ms role="tabpanel">

    {{-- Encabezado solo para impresión física --}}
    <div class="hidden print:block mb-6 border-b-2 border-slate-900 pb-3">
      <p class="text-xs uppercase font-bold text-slate-500 tracking-wider">Agencia Boliviana de Correos · Recursos Humanos</p>
      <h2 class="text-xl font-bold text-slate-900 mt-1">Reporte Disciplinario de Sanciones y Alertas de Reglamento</h2>
      <p class="text-xs text-slate-600">Periodo: {{ $monthLabel }} | Sucursal: {{ $selectedBranch ?: 'Todas las sucursales' }} | Normativa: Art. 45 y Art. 48 | Emisión: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Resumen Ejecutivo Superior (5 KPIs) --}}
    <section class="mb-6 grid gap-3.5 grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
      <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 text-center shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Zona de Alerta</p>
        <p class="mt-2 text-2xl font-black text-amber-900">{{ $reporteReglamento['metricas']['en_alerta_preventiva'] ?? 0 }}</p>
        <p class="text-[11px] text-amber-700 mt-1">A punto de ser sancionados</p>
      </div>

      <div class="rounded-2xl border border-orange-200 bg-orange-50/50 p-4 text-center shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wider text-orange-800">Con Sanción Económica</p>
        <p class="mt-2 text-2xl font-black text-orange-950">{{ $reporteReglamento['metricas']['con_sancion_economica'] ?? 0 }}</p>
        <p class="text-[11px] text-orange-700 mt-1">Descuento en planilla (Art. 45)</p>
      </div>

      <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 text-center shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Riesgo Crítico</p>
        <p class="mt-2 text-2xl font-black text-rose-950">{{ $reporteReglamento['metricas']['riesgo_critico'] ?? 0 }}</p>
        <p class="text-[11px] text-rose-700 mt-1">Causal de destitución (Art. 48)</p>
      </div>

      <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-4 text-center shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wider text-purple-800">Concurrencia de Leyes</p>
        <p class="mt-2 text-2xl font-black text-purple-950">{{ $reporteReglamento['metricas']['concurrencia_articulos'] ?? 0 }}</p>
        <p class="text-[11px] text-purple-700 mt-1">Infringen Art. 45 y 48 a la vez</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm col-span-2 md:col-span-1">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Días Totales a Deducir</p>
        <p class="mt-2 text-2xl font-black text-slate-900">{{ $reporteReglamento['metricas']['total_dias_sancion_formato'] ?? '0 días' }}</p>
        <p class="text-[11px] text-slate-400 mt-1">Cómputo general DAF</p>
      </div>
    </section>

    {{-- Desglose rápido por Sucursal --}}
    @if(($reporteReglamento['por_sucursal'] ?? null) && count($reporteReglamento['por_sucursal']) > 0)
      <div class="mb-5 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600 flex flex-wrap items-center gap-2 shadow-sm">
        <span class="font-bold text-slate-700 uppercase tracking-wider mr-1">Impacto por regional:</span>
        @foreach($reporteReglamento['por_sucursal'] as $sucName => $sucData)
          @if($sucData['sancionados'] > 0 || $sucData['en_alerta'] > 0 || $sucData['criticos'] > 0)
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-700">
              <strong class="uppercase text-slate-800">{{ $sucName }}:</strong>
              @if($sucData['en_alerta'] > 0)
                <span class="text-amber-800 font-bold">{{ $sucData['en_alerta'] }} en alerta</span>
              @endif
              @if($sucData['sancionados'] > 0)
                <span class="text-orange-900 font-bold">· {{ $sucData['sancionados'] }} sancionados</span>
              @endif
              @if($sucData['criticos'] > 0)
                <span class="text-rose-700 font-bold">· {{ $sucData['criticos'] }} crítico(s)</span>
              @endif
            </span>
          @endif
        @endforeach
      </div>
    @endif

    {{-- AVISO / PROPUESTA INSTITUCIONAL POR CONTINGENCIAS Y BLOQUEOS --}}
    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 shadow-sm">
      <div class="flex items-start gap-3">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-sm shadow-xs">
          🛡️
        </span>
        <div class="flex-1 text-xs leading-relaxed text-slate-700">
          <h4 class="font-bold text-slate-900 text-sm mb-0.5">Nota de Auditoría Normativa y Contingencias Administrativas</h4>
          <p class="text-slate-600">
            Debido a que los registros biométricos pueden no reflejar de inmediato boletas en trámite, así como afectaciones por 
            <strong class="text-slate-900">bloqueos de transporte, cortes de conectividad o feriados regionales no consolidados</strong>, 
            aquellos funcionarios que registren <strong class="text-purple-900">concurrencia simultánea de infracciones (Art. 45 y Art. 48)</strong> 
            o causales críticas deben ser objeto de <strong class="text-blue-900 underline">auditoría previa y verificación de boletas físicas</strong> 
            con su respectiva jefatura antes de la aplicación definitiva de memorándums de destitución o deducciones en planilla.
          </p>
        </div>
      </div>
    </div>

  <div x-data="{ filtroArticulo: 'todos' }">
    {{-- Título y Barra de Navegación por Artículos --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4 no-print border-b border-slate-200 pb-4">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Control de Cumplimiento del Reglamento Interno</h3>
        <p class="text-xs text-slate-500">Evaluación disciplinaria de atrasos, omisiones y ausencias según el Art. 45 y Art. 48 en {{ $monthLabel }}.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 shadow-sm">
          <button type="button" @click="filtroArticulo = 'todos'"
            :class="filtroArticulo === 'todos' ? 'bg-white text-slate-900 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs transition">
            <span>Todos los Casos</span>
            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-700">
              {{ count($reporteReglamento['personal_en_alerta'] ?? []) + count($reporteReglamento['mas_sancionados'] ?? []) + count($reporteReglamento['casos_criticos'] ?? []) }}
            </span>
          </button>

          <button type="button" @click="filtroArticulo = 'concurrente'"
            :class="filtroArticulo === 'concurrente' ? 'bg-white text-purple-950 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs transition">
            <span class="h-2 w-2 rounded-full bg-purple-600"></span>
            <span>Concurrencia (Art. 45 + 48)</span>
            <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-900">
              {{ $reporteReglamento['metricas']['concurrencia_articulos'] ?? 0 }}
            </span>
          </button>

          <button type="button" @click="filtroArticulo = 'art45'"
            :class="filtroArticulo === 'art45' ? 'bg-white text-amber-950 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs transition">
            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            <span>Art. 45 (Atrasos)</span>
            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">
              {{ ($reporteReglamento['art_45']['total_alertas'] ?? 0) + ($reporteReglamento['art_45']['total_sancionados'] ?? 0) }}
            </span>
          </button>

          <button type="button" @click="filtroArticulo = 'art48'"
            :class="filtroArticulo === 'art48' ? 'bg-white text-rose-950 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs transition">
            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
            <span>Art. 48 (Destitución)</span>
            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-900">
              {{ ($reporteReglamento['art_48']['total_alertas'] ?? 0) + ($reporteReglamento['art_48']['total_criticos'] ?? 0) }}
            </span>
          </button>
        </div>

        <button type="button" wire:click="descargarPdfReporteReglamento" class="report-hero-pdf-btn">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 16V4"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
          </svg>
          <span>Descargar PDF Reglamento</span>
        </button>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- BLOQUE ESPECIAL: CASOS CON CONCURRENCIA (ART. 45 Y ART. 48)  --}}
    {{-- ============================================================ --}}
    <div x-show="filtroArticulo === 'todos' || filtroArticulo === 'concurrente'" class="space-y-4 mb-8">
      @if(count($reporteReglamento['concurrentes'] ?? []) > 0)
        <section class="surface-card border-purple-200 bg-purple-50/20">
          <div class="mb-4 flex items-center justify-between border-b border-purple-100 pb-2.5">
            <div>
              <div class="flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-600 text-white text-xs font-bold">⚖️</span>
                <h5 class="text-sm font-bold text-purple-950">Casos con Concurrencia de Leyes (Art. 45 y Art. 48 Simultáneos)</h5>
              </div>
              <p class="text-xs text-purple-800 mt-0.5">Funcionarios que incurren a la vez en descuentos por atrasos y causales críticas de destitución/omisiones.</p>
            </div>
            <span class="rounded-full bg-purple-100 border border-purple-300 px-2.5 py-0.5 text-xs font-bold text-purple-900">
              {{ count($reporteReglamento['concurrentes']) }} caso(s) en revisión
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
            @foreach($reporteReglamento['concurrentes'] as $concurrente)
              <div class="flex flex-col justify-between rounded-xl border border-purple-200 bg-white p-3.5 shadow-sm hover:border-purple-300 transition">
                <div>
                  <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-purple-600 text-white font-bold text-sm shadow-xs">
                        {{ $concurrente['inicial'] ?? 'F' }}
                      </div>
                      <div class="min-w-0">
                        <h6 class="truncate font-bold text-slate-900 text-sm leading-tight">{{ $concurrente['nombre'] }}</h6>
                        <p class="truncate text-[11px] text-slate-500 mt-0.5">{{ $concurrente['sucursal'] }} · {{ $concurrente['area'] }}</p>
                      </div>
                    </div>
                    <span class="rounded-full bg-purple-100 border border-purple-200 px-2 py-0.5 text-[10px] font-bold text-purple-900 shrink-0">
                      Art. 45 + 48
                    </span>
                  </div>

                  {{-- Cajas métricas duales --}}
                  <div class="grid grid-cols-2 gap-2 text-center my-2.5">
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-800">Infracción Art. 45</span>
                      <span class="text-sm font-black text-amber-950">{{ $concurrente['minutos_atraso'] ?? 0 }} min</span>
                      <span class="block text-[9px] text-amber-700">{{ $concurrente['dias_sancion_atraso_texto'] ?? 'En alerta' }}</span>
                    </div>
                    <div class="rounded-lg bg-rose-50 border border-rose-200 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-800">Infracción Art. 48</span>
                      <span class="text-sm font-black text-rose-950">{{ $concurrente['omisiones'] ?? 0 }} omisiones</span>
                      <span class="block text-[9px] text-rose-700">Riesgo destitución</span>
                    </div>
                  </div>

                  {{-- Propuesta de Resolución Administrativa --}}
                  <div class="rounded-lg bg-purple-50/70 p-2.5 border border-purple-100 text-[11px] text-purple-950 leading-relaxed">
                    <strong class="text-purple-900 block font-bold mb-0.5">Propuesta Institucional:</strong>
                    {{ $concurrente['propuesta_resolucion'] }}
                  </div>
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                  <span>CI: <strong class="font-mono text-slate-700">{{ $concurrente['codigo'] }}</strong></span>
                  <button type="button" wire:click="openEmployeeDetailModal({{ $concurrente['id'] }})" class="table-action-button text-[10px] py-1 px-2.5">
                    Ver detalle completo
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif
    </div>

    {{-- ============================================================ --}}
    {{-- BLOQUE 1: ARTÍCULO 45 (ATRASOS, INASISTENCIAS Y DESCUENTOS)  --}}
    {{-- ============================================================ --}}
    <div x-show="filtroArticulo === 'todos' || filtroArticulo === 'art45'" class="space-y-6 mb-8">
      {{-- Banner explicativo Art. 45 --}}
      <div class="rounded-2xl border border-amber-200 bg-linear-to-r from-amber-50/70 via-white to-amber-50/40 p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-200/70 pb-2.5 mb-2.5">
          <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center justify-center rounded-lg bg-amber-500 text-white font-bold text-xs px-2.5 py-1 shadow-xs">
              Artículo 45
            </span>
            <div>
              <h4 class="text-sm font-bold text-slate-900">Régimen de Atrasos, Inasistencias y Sanciones Salariales</h4>
              <p class="text-xs text-slate-500">Sanciones automáticas con descuento de haberes (de 1/2 día a 4 días de sueldo) según minutos acumulados en {{ $monthLabel }}.</p>
            </div>
          </div>
        </div>
        {{-- Escala resumida muy amigable y clara --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-[11px] text-slate-600">
          <div class="rounded-lg bg-white p-2 border border-slate-200">
            <span class="block font-bold text-slate-800">Tolerancia:</span> 5 min al ingreso
          </div>
          <div class="rounded-lg bg-white p-2 border border-slate-200">
            <span class="block font-bold text-slate-800">6 a 30 min:</span> Sin descuento (acumula)
          </div>
          <div class="rounded-lg bg-amber-50 p-2 border border-amber-200">
            <span class="block font-bold text-amber-900">31 a 60 min:</span> 1/2 día de haber
          </div>
          <div class="rounded-lg bg-amber-50 p-2 border border-amber-200">
            <span class="block font-bold text-amber-900">61 a 90 min:</span> 1 día de haber
          </div>
          <div class="rounded-lg bg-orange-50 p-2 border border-orange-200">
            <span class="block font-bold text-orange-950">91 a 120 min:</span> 2 días | <strong class="text-orange-900">+120m:</strong> 3 a 4 días
          </div>
        </div>
      </div>

      {{-- Alertas Preventivas Art. 45 (Cards compactas y amigables) --}}
      <section class="surface-card">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-2.5">
          <div>
            <div class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-900 text-xs">⚠️</span>
              <h5 class="text-sm font-bold text-slate-900">Personal a punto de sanción económica (20 a 30 min)</h5>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Funcionarios que rozan los 31 minutos; una llegada tarde más activará el descuento de 1/2 día.</p>
          </div>
          <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900 border border-amber-200">
            {{ count($reporteReglamento['art_45']['alertas'] ?? []) }} en alerta
          </span>
        </div>

        @if(count($reporteReglamento['art_45']['alertas'] ?? []) > 0)
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
            @foreach($reporteReglamento['art_45']['alertas'] as $alerta)
              <div class="flex flex-col justify-between rounded-xl border border-amber-300 bg-linear-to-b from-amber-50/50 to-white p-3.5 shadow-sm hover:border-amber-400 transition">
                <div>
                  {{-- Encabezado con Nombre destacado --}}
                  <div class="flex items-start justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-sm shadow-xs">
                        {{ $alerta['inicial'] }}
                      </div>
                      <div class="min-w-0">
                        <h6 class="truncate font-bold text-slate-900 text-sm leading-tight">{{ $alerta['nombre'] }}</h6>
                        <p class="truncate text-[11px] text-slate-500 mt-0.5">{{ $alerta['sucursal'] }} · {{ $alerta['area'] }}</p>
                      </div>
                    </div>
                    @if(!empty($alerta['es_concurrente']))
                      <span class="inline-block rounded-full bg-purple-100 border border-purple-300 px-2 py-0.5 text-[10px] font-extrabold text-purple-950 shrink-0">
                        Art. 45 + 48
                      </span>
                    @else
                      <span class="inline-block rounded-full bg-amber-100 border border-amber-300 px-2 py-0.5 text-[10px] font-extrabold text-amber-950 shrink-0">
                        Alerta Tolerancia
                      </span>
                    @endif
                  </div>

                  {{-- Cajas métricas: Cuánto usó de tolerancia y cuánto le falta para la sanción --}}
                  <div class="grid grid-cols-2 gap-2 text-center my-2.5">
                    <div class="rounded-lg bg-amber-100/70 border border-amber-200 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-800">Tolerancia Usada</span>
                      <span class="text-lg font-black text-amber-950">{{ $alerta['tolerancia_usada_minutos'] ?? $alerta['minutos_atraso'] }} min</span>
                      <span class="block text-[9px] text-amber-700">de 30 min sin sanción</span>
                    </div>
                    <div class="rounded-lg bg-rose-50 border border-rose-200 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-800">Faltan para Sanción</span>
                      <span class="text-lg font-black text-rose-700">{{ $alerta['minutos_restantes_sancion'] ?? (31 - $alerta['minutos_atraso']) }} min</span>
                      <span class="block text-[9px] text-rose-600">para 1/2 día descuento</span>
                    </div>
                  </div>

                  {{-- Medidor visual de consumo de tolerancia --}}
                  <div class="mt-1 mb-2.5">
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mb-1">
                      <span>Consumo de margen libre:</span>
                      <strong class="text-amber-950 font-bold">{{ $alerta['porcentaje_tolerancia_usada'] ?? 80 }}%</strong>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                      <div class="h-full rounded-full bg-amber-500 transition-all duration-300" style="width: {{ $alerta['porcentaje_tolerancia_usada'] ?? 80 }}%"></div>
                    </div>
                  </div>

                  {{-- Explicación y propuesta --}}
                  <div class="rounded-lg bg-white p-2.5 border border-amber-200 text-[11px] text-amber-950 leading-relaxed shadow-xs">
                    ⚠️ <strong>{{ $alerta['nombre'] }}</strong>: ha consumido <strong>{{ $alerta['tolerancia_usada_minutos'] ?? $alerta['minutos_atraso'] }} min</strong>. A los 31 min se aplicará descuento de 1/2 día (Art. 45).
                    @if(!empty($alerta['propuesta_resolucion']))
                      <span class="block mt-1 text-slate-600 font-medium">💡 {{ $alerta['propuesta_resolucion'] }}</span>
                    @endif
                  </div>
                </div>

                <div class="mt-3 pt-2 border-t border-amber-200/60 flex items-center justify-between text-[10px] text-slate-500">
                  <span>CI: <strong class="font-mono text-slate-700">{{ $alerta['codigo'] }}</strong></span>
                  <button type="button" wire:click="openEmployeeDetailModal({{ $alerta['id'] }})" class="table-action-button text-[10px] py-1 px-2.5">
                    Ver detalle
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="py-6 text-center text-xs text-slate-400">
            Ningún funcionario activo se encuentra en zona de alerta de 20 a 30 minutos.
          </div>
        @endif
      </section>

      {{-- Sección: Personal Sancionado (Art. 45) con Tarjetas de Resumen y Tabla --}}
      <section class="surface-card">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-2.5">
          <div>
            <div class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-orange-100 text-orange-900 text-xs">📉</span>
              <h5 class="text-sm font-bold text-slate-900">Personal con Descuento Salarial Aplicable (Art. 45)</h5>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Minutos de atraso acumulados y total de días de haber que se descontarán en la planilla de {{ $monthLabel }}.</p>
          </div>
          <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-bold text-orange-900 border border-orange-200">
            {{ count($reporteReglamento['art_45']['sancionados'] ?? []) }} sancionados
          </span>
        </div>

        {{-- Tabla detallada de Personal Sancionado --}}
        <div class="overflow-x-auto">
          <table class="history-table w-full text-left">
            <thead>
              <tr>
                <th class="w-10 text-center">#</th>
                <th>Personal Sancionado</th>
                <th>CI / Código</th>
                <th>Sucursal / Área</th>
                <th class="text-center">Minutos Atraso</th>
                <th class="text-center">Omisiones</th>
                <th class="text-center">Días a Descontar</th>
                <th>Propuesta / Diagnóstico</th>
                <th class="text-right no-print w-28">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse($reporteReglamento['art_45']['sancionados'] ?? [] as $idx => $sancionado)
                <tr class="hover:bg-slate-50/60 transition">
                  <td class="text-center font-semibold text-slate-400 text-xs">{{ $idx + 1 }}</td>
                  <td>
                    <span class="font-bold text-slate-900">{{ $sancionado['nombre'] }}</span>
                    @if(!empty($sancionado['es_concurrente']))
                      <span class="ml-1.5 inline-block rounded bg-purple-100 text-purple-900 border border-purple-200 px-1.5 py-0.2 text-[10px] font-bold">Art. 45 + 48</span>
                    @elseif($sancionado['es_destitucion'])
                      <span class="ml-1.5 inline-block rounded bg-rose-100 px-1.5 py-0.2 text-[10px] font-bold text-rose-900">Art. 48</span>
                    @endif
                  </td>
                  <td><span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $sancionado['codigo'] }}</span></td>
                  <td class="text-xs text-slate-600">{{ $sancionado['sucursal'] }} · {{ $sancionado['area'] }}</td>
                  <td class="text-center">
                    @if($sancionado['minutos_atraso'] > 0)
                      <strong class="text-amber-950 text-xs font-bold">{{ $sancionado['minutos_atraso'] }} min</strong>
                      <span class="block text-[10px] text-slate-400">({{ $sancionado['dias_tarde'] }} días tarde)</span>
                    @else
                      <span class="text-slate-400 text-xs">0 min</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($sancionado['omisiones'] > 0)
                      <span class="inline-flex items-center justify-center rounded-full bg-rose-50 border border-rose-200 px-2 py-0.2 text-xs font-bold text-rose-800">
                        {{ $sancionado['omisiones'] }}
                      </span>
                    @else
                      <span class="text-slate-400 text-xs">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($sancionado['es_destitucion'])
                      <span class="inline-block rounded-lg bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-900">
                        Destitución
                      </span>
                    @else
                      <span class="inline-block rounded-lg bg-orange-100 border border-orange-300 px-2.5 py-0.5 text-xs font-black text-orange-950">
                        {{ $sancionado['total_dias_sancion_texto'] }}
                      </span>
                    @endif
                  </td>
                  <td class="text-xs text-slate-600">
                    <span class="block font-medium text-slate-800">{{ implode(' · ', $sancionado['desglose']) ?: 'Atrasos acumulados' }}</span>
                    @if(!empty($sancionado['propuesta_resolucion']))
                      <span class="block text-[10px] text-slate-500 mt-0.5">💡 {{ $sancionado['propuesta_resolucion'] }}</span>
                    @endif
                  </td>
                  <td class="text-right no-print">
                    <button type="button" wire:click="openEmployeeDetailModal({{ $sancionado['id'] }})" class="table-action-button text-xs py-1 px-2.5">
                      Ver detalle
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="py-8 text-center text-xs text-slate-400">
                    No se registran sanciones salariales aplicables en este periodo.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </div>

    {{-- ============================================================ --}}
    {{-- BLOQUE 2: ARTÍCULO 48 (CAUSALES GRAVES Y DESTITUCIÓN)        --}}
    {{-- ============================================================ --}}
    <div x-show="filtroArticulo === 'todos' || filtroArticulo === 'art48'" class="space-y-6 mb-8">
      {{-- Banner explicativo Art. 48 --}}
      <div class="rounded-2xl border border-rose-200 bg-linear-to-r from-rose-50/70 via-white to-red-50/40 p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-rose-200/70 pb-2.5 mb-2.5">
          <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-xs px-2.5 py-1 shadow-xs">
              Artículo 48
            </span>
            <div>
              <h4 class="text-sm font-bold text-slate-900">Causales Disciplinarias Graves y Destitución</h4>
              <p class="text-xs text-slate-500">Causales normativas que conllevan el retiro definitivo de funciones por reincidencia o abandono.</p>
            </div>
          </div>
        </div>
        {{-- Causales normativas clave --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-[11px] text-slate-600">
          <div class="rounded-lg bg-white p-2 border border-rose-200">
            <strong class="block text-rose-900">Inciso I (Reincidencia Atrasos):</strong> 3ra vez en la gestión anual con más de 120 min de atraso en un mes.
          </div>
          <div class="rounded-lg bg-white p-2 border border-rose-200">
            <strong class="block text-rose-900">Inciso IV (Omisiones de Marcación):</strong> 4 o más omisiones de asistencia (sin marcar entrada o salida) en un mes.
          </div>
          <div class="rounded-lg bg-white p-2 border border-rose-200">
            <strong class="block text-rose-900">Inasistencias Injustificadas:</strong> 3 días continuos o 6 días discontinuos de abandono de funciones.
          </div>
        </div>
      </div>

      {{-- Alertas Tempranas de Destitución (Cards compactas y amigables) --}}
      <section class="surface-card">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-2.5">
          <div>
            <div class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-rose-900 text-xs">⚠️</span>
              <h5 class="text-sm font-bold text-slate-900">Alertas Tempranas de Destitución (A 1 omisión o 1 mes de incurrir en Art. 48)</h5>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Funcionarios que con una sola falta adicional ingresarán en causal de destitución.</p>
          </div>
          <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-900 border border-rose-200">
            {{ count($reporteReglamento['art_48']['alertas'] ?? []) }} en riesgo
          </span>
        </div>

        @if(count($reporteReglamento['art_48']['alertas'] ?? []) > 0)
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
            @foreach($reporteReglamento['art_48']['alertas'] as $alerta48)
              <div class="flex flex-col justify-between rounded-xl border border-rose-300 bg-linear-to-b from-rose-50/50 to-white p-3.5 shadow-sm hover:border-rose-400 transition">
                <div>
                  {{-- Encabezado con Nombre destacado --}}
                  <div class="flex items-start justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white font-bold text-sm shadow-xs">
                        {{ $alerta48['inicial'] }}
                      </div>
                      <div class="min-w-0">
                        <h6 class="truncate font-bold text-slate-900 text-sm leading-tight">{{ $alerta48['nombre'] }}</h6>
                        <p class="truncate text-[11px] text-slate-500 mt-0.5">{{ $alerta48['sucursal'] }} · {{ $alerta48['area'] }}</p>
                      </div>
                    </div>
                    @if(!empty($alerta48['es_concurrente']))
                      <span class="inline-block rounded-full bg-purple-100 border border-purple-300 px-2 py-0.5 text-[10px] font-extrabold text-purple-950 shrink-0">
                        Art. 45 + 48
                      </span>
                    @else
                      <span class="inline-block rounded-full bg-rose-100 border border-rose-300 px-2 py-0.5 text-[10px] font-extrabold text-rose-950 shrink-0">
                        🛑 Riesgo Destitución
                      </span>
                    @endif
                  </div>

                  {{-- Cajas métricas --}}
                  <div class="grid grid-cols-2 gap-2 text-center my-2.5">
                    <div class="rounded-lg bg-rose-100/70 border border-rose-200 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-900">
                        {{ isset($alerta48['omisiones_usadas']) ? 'Omisiones Usadas' : 'Meses Reincidentes' }}
                      </span>
                      <span class="text-lg font-black text-rose-950">
                        {{ $alerta48['omisiones_usadas'] ?? ($alerta48['meses_graves_usados'] ?? 2) }}
                      </span>
                      <span class="block text-[9px] text-rose-700">
                        de {{ $alerta48['omisiones_limite'] ?? ($alerta48['meses_graves_limite'] ?? 4) }} límite
                      </span>
                    </div>
                    <div class="rounded-lg bg-red-100 border border-red-300 p-2">
                      <span class="block text-[10px] font-bold uppercase tracking-wider text-red-900">Falta para Destitución</span>
                      <span class="text-lg font-black text-red-700">
                        {{ $alerta48['omisiones_restantes'] ?? ($alerta48['meses_restantes'] ?? 1) }}
                      </span>
                      <span class="block text-[9px] text-red-600">para causal formal</span>
                    </div>
                  </div>

                  {{-- Medidor visual de cercanía a causal disciplinaria --}}
                  <div class="mt-1 mb-2.5">
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mb-1">
                      <span>Cercanía a proceso de destitución:</span>
                      <strong class="text-rose-950 font-bold">{{ $alerta48['porcentaje_usado'] ?? 75 }}%</strong>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                      <div class="h-full rounded-full bg-rose-600 transition-all duration-300" style="width: {{ $alerta48['porcentaje_usado'] ?? 75 }}%"></div>
                    </div>
                  </div>

                  {{-- Explicación y propuesta --}}
                  <div class="rounded-lg bg-white p-2.5 border border-rose-200 text-[11px] text-rose-950 leading-relaxed shadow-xs">
                    🛑 <strong>{{ $alerta48['nombre'] }}</strong>: {{ $alerta48['explicacion'] }}
                    @if(!empty($alerta48['propuesta_resolucion']))
                      <span class="block mt-1 text-slate-600 font-medium">💡 {{ $alerta48['propuesta_resolucion'] }}</span>
                    @endif
                  </div>
                </div>

                <div class="mt-3 pt-2 border-t border-rose-200/60 flex items-center justify-between text-[10px] text-slate-500">
                  <span>CI: <strong class="font-mono text-slate-700">{{ $alerta48['codigo'] }}</strong></span>
                  <button type="button" wire:click="openEmployeeDetailModal({{ $alerta48['id'] }})" class="table-action-button text-[10px] py-1 px-2.5">
                    Ver detalle
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="py-6 text-center text-xs text-slate-400">
            Ningún funcionario se encuentra al límite de incurrir en causales del Art. 48.
          </div>
        @endif
      </section>

      {{-- Casos Críticos Activos (Causal de Destitución Incurrida) --}}
      @if(count($reporteReglamento['art_48']['casos_criticos'] ?? []) > 0)
        <section class="surface-card border-rose-300 bg-rose-50/20">
          <div class="mb-4 flex items-center justify-between border-b border-rose-200 pb-2.5">
            <div>
              <div class="flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-xs">⛔</span>
                <h5 class="text-sm font-bold text-rose-950">Personal en Causal de Destitución Incurrida (Art. 48)</h5>
              </div>
              <p class="text-xs text-rose-800 mt-0.5">Casos que superaron los límites permitidos de omisiones, faltas continuas o reincidencia.</p>
            </div>
            <span class="rounded-full bg-rose-600 text-white px-2.5 py-0.5 text-xs font-bold">
              {{ count($reporteReglamento['art_48']['casos_criticos']) }} caso(s)
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($reporteReglamento['art_48']['casos_criticos'] as $critico)
              <div class="rounded-xl border border-rose-300 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-900 font-bold text-xs border border-rose-300">
                      ⛔
                    </div>
                    <div class="min-w-0">
                      <p class="truncate font-bold text-slate-900 text-xs">{{ $critico['nombre'] }}</p>
                      <p class="truncate text-[10px] text-slate-500">{{ $critico['sucursal'] }} · {{ $critico['area'] }} · CI: {{ $critico['codigo'] }}</p>
                    </div>
                  </div>
                  @if(!empty($critico['es_concurrente']))
                    <span class="rounded bg-purple-100 border border-purple-200 px-2 py-0.5 text-[10px] font-bold text-purple-900 shrink-0">
                      Art. 45 + 48
                    </span>
                  @else
                    <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-900 shrink-0">
                      Causal de Destitución
                    </span>
                  @endif
                </div>
                <div class="mt-2.5 rounded-lg bg-rose-50 p-2 border border-rose-200 text-xs font-semibold text-rose-900">
                  {{ $critico['causal_principal'] }}
                </div>
                @if(!empty($critico['propuesta_resolucion']))
                  <div class="mt-2 rounded-lg bg-slate-50 p-2 border border-slate-200 text-[11px] text-slate-700">
                    💡 <strong class="text-slate-900">Propuesta:</strong> {{ $critico['propuesta_resolucion'] }}
                  </div>
                @endif
                <div class="mt-2.5 flex items-center justify-end">
                  <button type="button" wire:click="openEmployeeDetailModal({{ $critico['id'] }})" class="table-action-button text-[10px] py-0.5 px-2.5">
                    Ver historial completo
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif
    </div>
  </div>
  </div>

  {{-- ============================================================ --}}
  {{-- TAB 8: MI REPORTE (solo usuarios con empleado vinculado)     --}}
  {{-- ============================================================ --}}
  @if($reportePersonal)
  <div x-show="tab === 'mi-reporte'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Reporte personal</p>
          <h2 class="section-title">{{ $authEmpleadoNombre ?? $reportePersonal['empleado']['nombre'] }}</h2>
          <p class="section-copy-sm">{{ $reportePersonal['empleado']['sucursal'] }} · Horario: {{ $reportePersonal['empleado']['horario'] }}</p>
        </div>
        <span class="status-badge status-info">{{ $monthLabel }}</span>
      </div>

      {{-- KPIs personales --}}
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($reportePersonal['metrics'] as $metric)
          <div class="personal-kpi-card">
            <p class="metric-label">{{ $metric['label'] }}</p>
            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $metric['value'] }}</p>
          </div>
        @endforeach
      </div>

      {{-- Listas de atrasos y omisiones personales --}}
      <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="rounded-[1.3rem] border border-amber-200 bg-amber-50/40 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Mis atrasos del mes</h3>
            <span class="status-badge status-warning">{{ count($reportePersonal['tardanzas']) }}</span>
          </div>
          <div class="report-scroll-list space-y-3">
            @forelse($reportePersonal['tardanzas'] as $item)
              <div class="rounded-xl bg-white border border-amber-100 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                <p class="mt-1 text-sm text-slate-500">Entrada: {{ $item['entrada'] }} · Retraso: <strong class="text-amber-700">{{ $item['retraso'] }}</strong></p>
              </div>
            @empty
              <p class="text-sm text-slate-400">Sin atrasos en el mes. ¡Excelente puntualidad!</p>
            @endforelse
          </div>
        </div>

        <div class="rounded-[1.3rem] border border-rose-200 bg-rose-50/40 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Mis omisiones del mes</h3>
            <span class="status-badge status-danger">{{ count($reportePersonal['no_marcados']) }}</span>
          </div>
          <div class="report-scroll-list space-y-3">
            @forelse($reportePersonal['no_marcados'] as $item)
              <div class="rounded-xl bg-white border border-rose-100 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                <p class="mt-1 text-sm text-slate-500">Entrada: {{ $item['entrada'] }} · Salida: {{ $item['salida'] }}</p>
              </div>
            @empty
              <p class="text-sm text-slate-400">Sin omisiones de marcación. ¡Perfecto registro!</p>
            @endforelse
          </div>
        </div>
      </div>

      {{-- Faltas personales --}}
      @if(count($reportePersonal['faltas']) > 0)
        <div class="mt-6 rounded-[1.3rem] border border-slate-200 bg-slate-50 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Faltas registradas</h3>
            <span class="status-badge status-danger">{{ count($reportePersonal['faltas']) }}</span>
          </div>
          <div class="report-scroll-list grid gap-3 md:grid-cols-2">
            @foreach($reportePersonal['faltas'] as $item)
              <div class="rounded-xl bg-white border border-slate-200 px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </section>
  </div>
  @endif

  {{-- Estilos de impresión limpia --}}
  <style>
    @media print {
      body {
        background: #ffffff !important;
        color: #0f172a !important;
      }
      .app-sidebar,
      .app-header,
      .report-hero,
      .report-tab-nav,
      .no-print,
      .app-modal-backdrop,
      button {
        display: none !important;
      }
      .page-stack {
        padding: 0 !important;
        margin: 0 !important;
      }
      .branch-print-section {
        page-break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        margin-bottom: 20px !important;
      }
      .history-table th, .history-table td {
        border: 1px solid #cbd5e1 !important;
        padding: 5px 8px !important;
      }
    }
  </style>

</div>
