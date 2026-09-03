<div class="page-stack page-stack-calendar">
  <section class="surface-card surface-card-calendar">
    
    {{-- Encabezado Principal del Módulo --}}
    <div class="calendar-header">
      <div>
        <p class="section-kicker">Gestión Integral de Personal</p>
        <h3 class="section-title">Calendario de Asistencia</h3>
        <p class="section-copy-sm">Control diario de marcaciones, tardanzas, omisiones de entrada/salida y ausencias.</p>
      </div>

      <div class="calendar-controls">
        <button type="button" class="calendar-chip" wire:click="goToCurrentMonth">
          <svg class="h-4 w-4 mr-1.5 inline-block text-[#0f67c0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Mes Actual
        </button>
        <div class="calendar-month-box">
          <button type="button" class="calendar-arrow" wire:click="goToPreviousMonth" aria-label="Mes anterior">&lsaquo;</button>
          <span class="font-bold tracking-wide">{{ $calendar['month_label'] }}</span>
          <button type="button" class="calendar-arrow" wire:click="goToNextMonth" aria-label="Mes siguiente">&rsaquo;</button>
        </div>
      </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN SUPERIOR: DETALLE DEL DÍA (REORGANIZADO Y MEJORADO VISUALMENTE) --}}
    {{-- ========================================================================= --}}
    <div class="calendar-top-detail mt-6">
      
      {{-- Barra superior de la fecha seleccionada con selector rápido día a día --}}
      <div class="calendar-top-head">
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0f67c0] text-white shadow-sm">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h4 class="text-xl font-bold text-slate-900 md:text-2xl">{{ $selectedDay['day_label'] }}</h4>
              @if($selectedDay['is_today'])
                <span class="inline-flex items-center rounded-full bg-blue-600 px-3 py-0.5 text-xs font-bold text-white shadow-sm">HOY</span>
              @endif
              @if($selectedDay['is_weekend'])
                <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-0.5 text-xs font-semibold text-slate-700">FIN DE SEMANA</span>
              @endif
              @if(count($selectedDay['fechas_especiales']) > 0)
                <span class="inline-flex items-center rounded-full bg-purple-100 border border-purple-200 px-3 py-0.5 text-xs font-bold text-purple-700">FECHA ESPECIAL</span>
              @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Detalle del personal para la fecha seleccionada</p>
          </div>
        </div>

        {{-- Controles rápidos de día anterior / hoy / día siguiente --}}
        <div class="flex items-center gap-2">
          <button type="button" wire:click="previousDay" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            <span>Día anterior</span>
          </button>
          <button type="button" wire:click="goToCurrentMonth" class="inline-flex items-center rounded-xl bg-slate-100 border border-slate-200 px-3 py-2 text-xs font-bold text-[#18386c] transition hover:bg-slate-200">
            Hoy
          </button>
          <button type="button" wire:click="nextDay" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300">
            <span>Día siguiente</span>
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
          </button>
        </div>
      </div>

      {{-- Fechas especiales del día (feriado / paro / horario especial) --}}
      @if(count($selectedDay['fechas_especiales']) > 0)
        <div class="mt-4 rounded-2xl border border-purple-200 bg-purple-50/80 p-4 shadow-sm">
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-purple-800">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-purple-600"></span>
            <span>Feriado / Fecha Especial Programada</span>
          </div>
          <div class="mt-2 grid gap-2 sm:grid-cols-2">
            @foreach($selectedDay['fechas_especiales'] as $fe)
              <div class="rounded-xl bg-white/80 p-3 border border-purple-100">
                <p class="font-bold text-sm text-purple-900">📌 {{ $fe['nombre'] }}</p>
                <p class="text-xs text-purple-700 mt-0.5">
                  Sucursal: <strong>{{ $fe['sucursal'] }}</strong> 
                  @if($fe['horario']) | Horario especial: <strong>{{ $fe['horario'] }}</strong> @endif
                </p>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Fila de Tarjetas KPI Métricas --}}
      <div class="calendar-kpi-grid mt-4">
        
        {{-- Card Asistencias --}}
        <div class="calendar-kpi-card border-emerald-200 bg-gradient-to-br from-white to-emerald-50/30">
          <div class="flex items-center justify-between">
            <span class="calendar-kpi-label text-emerald-800">Marcaciones</span>
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
          </div>
          <strong class="calendar-kpi-value text-emerald-950">{{ $selectedDay['totals']['marcaciones'] }}</strong>
          <span class="calendar-kpi-sub text-emerald-700">
            <strong>{{ $selectedDay['totals']['puntuales'] }}</strong> puntuales a tiempo
          </span>
        </div>

        {{-- Card Tardanzas --}}
        <div class="calendar-kpi-card border-rose-200 bg-gradient-to-br from-white to-rose-50/30">
          <div class="flex items-center justify-between">
            <span class="calendar-kpi-label text-rose-800">Tardanzas</span>
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
          </div>
          <strong class="calendar-kpi-value text-rose-950">{{ $selectedDay['totals']['tardanzas'] }}</strong>
          <span class="calendar-kpi-sub text-rose-700">
            {{ $selectedDay['totals']['minutos_retraso_formateado'] }} de retraso
            @if($selectedDay['totals']['excedidos'] > 0)
              ({{ $selectedDay['totals']['excedidos'] }} excedieron tolerancia mensual)
            @else
              (dentro de tolerancia mensual de 30 min)
            @endif
          </span>
        </div>

        {{-- Card Omisiones --}}
        <div class="calendar-kpi-card border-amber-200 bg-gradient-to-br from-white to-amber-50/30">
          <div class="flex items-center justify-between">
            <span class="calendar-kpi-label text-amber-800">Omisiones</span>
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
          </div>
          <strong class="calendar-kpi-value text-amber-950">{{ $selectedDay['totals']['omisiones'] }}</strong>
          <span class="calendar-kpi-sub text-amber-700">Olvidos de marcar entrada o salida</span>
        </div>

        {{-- Card Faltas --}}
        <div class="calendar-kpi-card border-slate-200 bg-gradient-to-br from-white to-slate-50/60">
          <div class="flex items-center justify-between">
            <span class="calendar-kpi-label text-slate-800">Faltas / Ausencias</span>
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-200 text-slate-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
            </span>
          </div>
          <strong class="calendar-kpi-value text-slate-950">{{ $selectedDay['totals']['faltas'] }}</strong>
          <span class="calendar-kpi-sub text-slate-600">
            @if($selectedDay['is_weekend']) Fin de semana @else Sin justificación en la fecha @endif
          </span>
        </div>

      </div>

      {{-- ========================================================================= --}}
      {{-- FORMULARIO DE BÚSQUEDA EXPLICITA (FECHA -> CÓDIGO -> SUCURSAL -> BOTÓN BUSCAR) --}}
      {{-- ========================================================================= --}}
      <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form wire:submit.prevent="aplicarBusqueda" class="flex flex-col gap-3 lg:flex-row lg:items-end">
          
          {{-- 1. Filtro Fecha --}}
          <div class="flex-1 min-w-[160px]">
            <label for="search-fecha" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              1. Fecha
            </label>
            <input
              type="date"
              id="search-fecha"
              wire:model="inputFecha"
              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
            >
          </div>

          {{-- 2. Filtro Código de Empleado / Carnet --}}
          <div class="flex-1 min-w-[170px]">
            <label for="search-codigo" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              2. Código / Carnet
            </label>
            <input
              type="text"
              id="search-codigo"
              wire:model="inputCodigo"
              placeholder="Código o CI (opcional)..."
              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
            >
          </div>

          {{-- 3. Filtro Sucursal --}}
          <div class="flex-1 min-w-[190px]">
            <label for="search-sucursal" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              3. Sucursal
            </label>
            <select
              id="search-sucursal"
              wire:model="inputSucursal"
              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
            >
              <option value="">Todas las sucursales</option>
              @foreach($branches as $branch)
                <option value="{{ $branch }}">{{ $branch }}</option>
              @endforeach
            </select>
          </div>

          {{-- Botones Buscar y Limpiar --}}
          <div class="flex items-center gap-2">
            <button
              type="submit"
              class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0f67c0] px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0d59a7] focus:outline-none focus:ring-2 focus:ring-[#0f67c0]/40"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <span>Buscar</span>
            </button>
            
            @if(filled($appliedCodigo) || (filled($appliedSucursal) && $appliedSucursal !== 'todas') || $inputFecha !== now()->toDateString())
              <button
                type="button"
                wire:click="limpiarFiltros"
                class="inline-flex items-center justify-center gap-1 rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-200"
                title="Restablecer filtros"
              >
                <span>Limpiar</span>
              </button>
            @endif
          </div>

        </form>

        {{-- Indicador de filtros activos si los hay --}}
        @if(filled($appliedCodigo) || filled($appliedSucursal))
          <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-600">
            <span class="font-bold text-slate-400 uppercase text-[10px]">Filtros aplicados:</span>
            @if(filled($appliedCodigo))
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-blue-800 font-semibold">
                Código/CI: <strong>{{ $appliedCodigo }}</strong>
              </span>
            @endif
            @if(filled($appliedSucursal))
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-slate-800 font-semibold">
                Sucursal: <strong>{{ $appliedSucursal }}</strong>
              </span>
            @endif
          </div>
        @endif
      </div>

      {{-- Barra de Navegación de Pestañas en Detalle del Día --}}
      <div class="mt-5 flex flex-wrap items-center gap-1.5 border-b border-slate-200 pb-3">
        <button
          type="button"
          wire:click="setTab('todos')"
          class="calendar-tab-btn {{ $activeTab === 'todos' ? 'calendar-tab-btn-active' : '' }}"
        >
          <span>Todas las Marcaciones</span>
          <span class="calendar-tab-pill">{{ count($selectedDay['marcaciones']) }}</span>
        </button>

        <button
          type="button"
          wire:click="setTab('tardanzas')"
          class="calendar-tab-btn {{ $activeTab === 'tardanzas' ? 'calendar-tab-btn-active-rose' : '' }}"
        >
          <span class="inline-block h-2 w-2 rounded-full bg-rose-500 mr-1.5"></span>
          <span>Tardanzas</span>
          <span class="calendar-tab-pill bg-rose-100 text-rose-800">{{ count($selectedDay['tardanzas']) }}</span>
        </button>

        <button
          type="button"
          wire:click="setTab('omisiones')"
          class="calendar-tab-btn {{ $activeTab === 'omisiones' ? 'calendar-tab-btn-active-amber' : '' }}"
        >
          <span class="inline-block h-2 w-2 rounded-full bg-amber-500 mr-1.5"></span>
          <span>Omisiones</span>
          <span class="calendar-tab-pill bg-amber-100 text-amber-800">{{ count($selectedDay['omisiones']) }}</span>
        </button>

        <button
          type="button"
          wire:click="setTab('faltas')"
          class="calendar-tab-btn {{ $activeTab === 'faltas' ? 'calendar-tab-btn-active-dark' : '' }}"
        >
          <span class="inline-block h-2 w-2 rounded-full bg-slate-800 mr-1.5"></span>
          <span>Faltas</span>
          <span class="calendar-tab-pill bg-slate-200 text-slate-800">{{ count($selectedDay['faltas']) }}</span>
        </button>

        @if(count($selectedDay['permisos']) > 0)
          <button
            type="button"
            wire:click="setTab('permisos')"
            class="calendar-tab-btn {{ $activeTab === 'permisos' ? 'calendar-tab-btn-active-purple' : '' }}"
          >
            <span class="inline-block h-2 w-2 rounded-full bg-purple-500 mr-1.5"></span>
            <span>Permisos</span>
            <span class="calendar-tab-pill bg-purple-100 text-purple-800">{{ count($selectedDay['permisos']) }}</span>
          </button>
        @endif
      </div>

      {{-- Listado de Registros según la pestaña activa con scroll contenido --}}
      <div class="mt-4">
        
        {{-- Pestaña: TODAS LAS MARCACIONES (Tabla compacta con cabecera fija, ordenación por columnas y filtros) --}}
        @if($activeTab === 'todos')
          <div>
            {{-- Filtros y accesos rápidos de ordenación de la tabla --}}
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs">
              <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Ordenar:</span>
                
                {{-- Botón A-Z --}}
                <button
                  type="button"
                  wire:click="sortByColumn('nombre')"
                  class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ $sortBy === 'nombre' ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                  <span>Personal (A-Z)</span>
                  @if($sortBy === 'nombre')
                    <span>{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                  @endif
                </button>

                {{-- Botón Entrada Temprano / Tarde --}}
                <button
                  type="button"
                  wire:click="sortByColumn('entrada')"
                  class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ $sortBy === 'entrada' ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                  <span>Entrada {{ $sortBy === 'entrada' ? ($sortDirection === 'asc' ? '(Temprano)' : '(Tarde)') : '' }}</span>
                  @if($sortBy === 'entrada')
                    <span>{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                  @endif
                </button>

                {{-- Botón Salida Temprano / Tarde --}}
                <button
                  type="button"
                  wire:click="sortByColumn('salida')"
                  class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ $sortBy === 'salida' ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                  <span>Salida {{ $sortBy === 'salida' ? ($sortDirection === 'asc' ? '(Temprano)' : '(Tarde)') : '' }}</span>
                  @if($sortBy === 'salida')
                    <span>{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                  @endif
                </button>
              </div>

              {{-- Filtro de estado: Todos / Completos / Sin completar --}}
              <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                <button
                  type="button"
                  wire:click="setFilterEstadoInterno('todos')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoInterno === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Todos
                </button>
                <button
                  type="button"
                  wire:click="setFilterEstadoInterno('completo')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoInterno === 'completo' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Completos
                </button>
                <button
                  type="button"
                  wire:click="setFilterEstadoInterno('faltante')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoInterno === 'faltante' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Sin completar
                </button>
              </div>
            </div>

            {{-- Tabla con cabecera sticky y scroll contenido --}}
            <div class="calendar-scroll-box rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <table class="w-full text-left text-xs text-slate-700">
                <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-sm text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 select-none">
                  <tr>
                    <th class="py-2.5 px-4 cursor-pointer hover:bg-slate-100 transition" wire:click="sortByColumn('nombre')">
                      <div class="flex items-center gap-1.5">
                        <span>Personal / Código</span>
                        @if($sortBy === 'nombre')
                          <span class="text-[#0f67c0]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @else
                          <span class="text-slate-300">⇅</span>
                        @endif
                      </div>
                    </th>
                    <th class="py-2.5 px-3 cursor-pointer hover:bg-slate-100 transition" wire:click="sortByColumn('sucursal')">
                      <div class="flex items-center gap-1.5">
                        <span>Sucursal</span>
                        @if($sortBy === 'sucursal')
                          <span class="text-[#0f67c0]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @else
                          <span class="text-slate-300">⇅</span>
                        @endif
                      </div>
                    </th>
                    <th class="py-2.5 px-3 text-center cursor-pointer hover:bg-slate-100 transition" wire:click="sortByColumn('entrada')">
                      <div class="flex items-center justify-center gap-1.5">
                        <span>Hora Entrada</span>
                        @if($sortBy === 'entrada')
                          <span class="text-[#0f67c0]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @else
                          <span class="text-slate-300">⇅</span>
                        @endif
                      </div>
                    </th>
                    <th class="py-2.5 px-3 text-center cursor-pointer hover:bg-slate-100 transition" wire:click="sortByColumn('salida')">
                      <div class="flex items-center justify-center gap-1.5">
                        <span>Hora Salida</span>
                        @if($sortBy === 'salida')
                          <span class="text-[#0f67c0]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @else
                          <span class="text-slate-300">⇅</span>
                        @endif
                      </div>
                    </th>
                    <th class="py-2.5 px-4 text-right cursor-pointer hover:bg-slate-100 transition" wire:click="sortByColumn('estado')">
                      <div class="flex items-center justify-end gap-1.5">
                        <span>Estado</span>
                        @if($sortBy === 'estado')
                          <span class="text-[#0f67c0]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @else
                          <span class="text-slate-300">⇅</span>
                        @endif
                      </div>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  @forelse($selectedDay['marcaciones'] as $registro)
                    <tr class="transition hover:bg-slate-50/80">
                      <td class="py-2.5 px-4">
                        <strong class="text-xs font-bold text-slate-900 block truncate max-w-xs">{{ $registro['nombre'] }}</strong>
                        <span class="text-[11px] text-slate-400 font-mono">Código: {{ $registro['codigo'] ?? 'S/D' }}</span>
                      </td>
                      <td class="py-2.5 px-3 text-slate-600 font-medium truncate max-w-[160px]">
                        {{ $registro['sucursal'] }}
                      </td>
                      <td class="py-2.5 px-3 text-center whitespace-nowrap">
                        @if($registro['entrada'] !== '--:--')
                          @if(($registro['minutos_retraso'] ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 border border-rose-200 px-2 py-0.5 font-mono text-[11px] font-bold text-rose-800" title="{{ $registro['detalle_retraso'] ?? ($registro['minutos_retraso'].' min de atraso') }}">
                              <span>{{ $registro['entrada'] }}</span>
                              <span class="text-[10px] font-medium text-rose-600">(+{{ $registro['minutos_retraso'] }}m)</span>
                            </span>
                          @else
                            <span class="inline-block rounded-md bg-emerald-50 border border-emerald-200 px-2 py-0.5 font-mono text-[11px] font-bold text-emerald-800" title="Entrada puntual dentro de tolerancia">
                              {{ $registro['entrada'] }}
                            </span>
                          @endif
                        @else
                          <span class="inline-block rounded-md bg-slate-100 text-slate-400 px-2 py-0.5 font-mono text-[11px]">
                            --:--
                          </span>
                        @endif
                      </td>
                      <td class="py-2.5 px-3 text-center whitespace-nowrap">
                        @if($registro['salida'] !== '--:--')
                          <span class="inline-block rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 font-mono text-[11px] font-bold text-slate-700">
                            {{ $registro['salida'] }}
                          </span>
                        @else
                          <span class="inline-block rounded-md bg-slate-100 text-slate-400 px-2 py-0.5 font-mono text-[11px]">
                            --:--
                          </span>
                        @endif
                      </td>
                      <td class="py-2.5 px-4 text-right whitespace-nowrap">
                        {{-- Estado: Puntual, Atraso, Excedió tolerancia vs Sin completar / Faltante --}}
                        @if($registro['tipo_estado'] === 'completo')
                          @if(($registro['minutos_retraso'] ?? 0) > 0)
                            @if(($registro['tone'] ?? '') === 'black')
                              <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 text-white px-2.5 py-0.5 text-[11px] font-bold" title="Completo pero excedió los 30 min de tolerancia mensual">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                                <span>Excedió tolerancia</span>
                              </span>
                            @else
                              <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-[11px] font-bold text-rose-800" title="Completo con atraso diario de {{ $registro['minutos_retraso'] }} min (dentro de tolerancia mensual)">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                <span>Atraso (+{{ $registro['minutos_retraso'] }}m)</span>
                              </span>
                            @endif
                          @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800" title="Entrada y salida completas, en horario puntual">
                              <svg class="h-3 w-3 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                              <span>Puntual</span>
                            </span>
                          @endif
                        @elseif($registro['tipo_estado'] === 'faltante')
                          @if(($registro['minutos_retraso'] ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold text-amber-900" title="Atraso de {{ $registro['minutos_retraso'] }}m y falta salida">
                              <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                              <span>Sin salida (+{{ $registro['minutos_retraso'] }}m)</span>
                            </span>
                          @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold text-amber-800" title="{{ $registro['entrada'] === '--:--' ? 'Falta marcar entrada' : 'Falta marcar salida' }}">
                              <svg class="h-3 w-3 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                              <span>Sin completar</span>
                            </span>
                          @endif
                        @else
                          <span class="inline-flex items-center rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-[11px] font-bold text-rose-800">
                            Sin marcación
                          </span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="py-8 text-center text-slate-500">
                        <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700">No se encontraron marcaciones para los filtros seleccionados.</p>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        @endif

        {{-- Pestaña: TARDANZAS --}}
        @if($activeTab === 'tardanzas')
          <div class="calendar-scroll-box pr-1">
            <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
              @forelse($selectedDay['tardanzas'] as $tardanza)
                <div class="flex items-start justify-between gap-3 rounded-2xl border border-rose-200 bg-gradient-to-br from-white to-rose-50/40 p-3.5 shadow-sm">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <span class="calendar-event-dot calendar-event-dot-{{ $tardanza['tone'] }}"></span>
                      <strong class="truncate text-xs font-bold text-slate-900">{{ $tardanza['nombre'] }}</strong>
                    </div>
                    <p class="text-xs text-rose-800 mt-1 font-semibold">{{ $tardanza['detalle'] }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $tardanza['sucursal'] }} · Entrada {{ $tardanza['entrada'] }}</p>
                  </div>
                  <span class="inline-flex shrink-0 items-center rounded-full {{ $tardanza['tone'] === 'black' ? 'bg-slate-900 text-white' : 'bg-rose-100 text-rose-800' }} px-2 py-0.5 text-[11px] font-bold" title="{{ $tardanza['tone'] === 'black' ? 'Excedió la tolerancia mensual de 30 min' : 'Atraso diario (dentro de los 30 min mensuales)' }}">
                    {{ $tardanza['tone'] === 'black' ? 'Tolerancia Excedida' : 'Atraso (dentro de 30m)' }}
                  </span>
                </div>
              @empty
                <div class="col-span-full rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 text-center">
                  <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                  </div>
                  <p class="text-xs font-semibold text-emerald-800">¡Excelente! Cero tardanzas registradas en este día.</p>
                  <p class="text-[11px] text-emerald-600 mt-0.5">Todo el personal asistió puntualmente dentro de su tolerancia.</p>
                </div>
              @endforelse
            </div>
          </div>
        @endif

        {{-- Pestaña: OMISIONES --}}
        @if($activeTab === 'omisiones')
          <div class="calendar-scroll-box pr-1">
            <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
              @forelse($selectedDay['omisiones'] as $omision)
                <div class="flex items-start justify-between gap-3 rounded-2xl border border-amber-200 bg-gradient-to-br from-white to-amber-50/40 p-3.5 shadow-sm">
                  <div class="min-w-0 flex-1">
                    <strong class="truncate text-xs font-bold text-slate-900 block">{{ $omision['nombre'] }}</strong>
                    <p class="text-xs font-semibold text-amber-800 mt-1">⚠️ {{ $omision['detalle'] }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $omision['sucursal'] }} · CI: {{ $omision['carnet'] ?? 'S/D' }}</p>
                    <div class="mt-1.5 flex items-center gap-2 text-xs font-mono text-slate-600">
                      <span>Entrada: <strong>{{ $omision['entrada'] }}</strong></span>
                      <span>·</span>
                      <span>Salida: <strong>{{ $omision['salida'] }}</strong></span>
                    </div>
                  </div>
                  <span class="inline-flex shrink-0 items-center rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-bold">
                    Omisión
                  </span>
                </div>
              @empty
                <div class="col-span-full rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 text-center">
                  <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                  </div>
                  <p class="text-xs font-semibold text-emerald-800">No hay omisiones de marcación en esta fecha.</p>
                  <p class="text-[11px] text-emerald-600 mt-0.5">Todas las asistencias tienen sus marcas de entrada y salida completas.</p>
                </div>
              @endforelse
            </div>
          </div>
        @endif

        {{-- Pestaña: FALTAS --}}
        @if($activeTab === 'faltas')
          <div class="calendar-scroll-box pr-1">
            <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
              @forelse($selectedDay['faltas'] as $falta)
                <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-3.5 shadow-sm">
                  <div class="min-w-0 flex-1">
                    <strong class="truncate text-xs font-bold text-slate-900 block">{{ $falta['nombre'] }}</strong>
                    <p class="text-xs text-rose-700 font-semibold mt-1">{{ $falta['detalle'] }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $falta['sucursal'] }} · CI: {{ $falta['carnet'] ?? 'S/D' }}</p>
                  </div>
                  <span class="inline-flex shrink-0 items-center rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[11px] font-bold">
                    {{ $falta['badge'] ?? 'Falta' }}
                  </span>
                </div>
              @empty
                <div class="col-span-full rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 text-center">
                  <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                  </div>
                  <p class="text-xs font-semibold text-emerald-800">Sin faltas ni ausencias injustificadas para esta fecha.</p>
                  <p class="text-[11px] text-emerald-600 mt-0.5">Todo el personal programado cumplió con su presencia laboral o permiso.</p>
                </div>
              @endforelse
            </div>
          </div>
        @endif

        {{-- Pestaña: PERMISOS / LICENCIAS --}}
        @if($activeTab === 'permisos')
          <div class="calendar-scroll-box pr-1">
            <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
              @forelse($selectedDay['permisos'] as $permiso)
                <div class="flex items-start justify-between gap-3 rounded-2xl border border-purple-200 bg-gradient-to-br from-white to-purple-50/40 p-3.5 shadow-sm">
                  <div class="min-w-0 flex-1">
                    <strong class="truncate text-xs font-bold text-purple-950 block">{{ $permiso['nombre'] }}</strong>
                    <p class="text-xs text-purple-800 font-semibold mt-1">📌 {{ $permiso['detalle'] }}</p>
                    @if($permiso['motivo'])
                      <p class="text-[11px] text-purple-600 mt-0.5 italic">"{{ $permiso['motivo'] }}"</p>
                    @endif
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $permiso['sucursal'] }} · CI: {{ $permiso['carnet'] ?? 'S/D' }}</p>
                  </div>
                  <span class="inline-flex shrink-0 items-center rounded-full bg-purple-100 text-purple-800 px-2 py-0.5 text-[11px] font-bold">
                    {{ $permiso['tipo_label'] }}
                  </span>
                </div>
              @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center">
                  <p class="text-xs font-semibold text-slate-600">No hay permisos ni justificaciones registradas para este día.</p>
                </div>
              @endforelse
            </div>
          </div>
        @endif

      </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN INFERIOR: CALENDARIO MENSUAL INTERACTIVO                          --}}
    {{-- ========================================================================= --}}
    <div class="mt-8 pt-6 border-t border-slate-200">
      
      {{-- Cabecera del Mes y Leyenda --}}
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="section-kicker">Vista Mensual</p>
          <h4 class="text-xl font-bold text-slate-900 md:text-2xl">
            Calendario de {{ $calendar['month_label'] }}
          </h4>
          <p class="section-copy-sm">Haz clic en cualquier día para inspeccionar su detalle en el panel superior.</p>
        </div>

        {{-- Leyenda compacta y moderna --}}
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-700 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span>Marcaciones</span>
          </div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-800 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
            <span>Tardanza</span>
          </div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 bg-slate-100 px-3 py-1 text-slate-900 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
            <span>Excedió Tolerancia</span>
          </div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-800 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
            <span>Omisión</span>
          </div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-purple-800 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-purple-600"></span>
            <span>Feriado / Especial</span>
          </div>
        </div>
      </div>

      {{-- Grilla del Calendario --}}
      <div class="calendar-shell mt-4">
        {{-- Días de la semana --}}
        <div class="calendar-weekdays">
          @foreach($calendar['weekdays'] as $weekday)
            <div class="py-3 font-bold">{{ strtoupper($weekday) }}</div>
          @endforeach
        </div>

        {{-- Semanas y Días --}}
        <div class="calendar-weeks">
          @foreach($calendar['weeks'] as $week)
            <div class="calendar-week-row">
              @foreach($week as $day)
                <article
                  wire:click="selectDate('{{ $day['date'] }}')"
                  class="calendar-day-card calendar-day-card-interactive {{ $day['is_current_month'] ? '' : 'calendar-day-card-muted' }} {{ $day['is_today'] ? 'calendar-day-card-today' : '' }} {{ $selectedDay['date'] === $day['date'] ? 'calendar-day-card-selected' : '' }} {{ $day['is_holiday'] ? 'border-purple-300 bg-purple-50/40' : '' }}"
                >
                  <div class="calendar-day-top flex items-center justify-between w-full">
                    @if($day['is_today'])
                      <span class="rounded bg-[#0f67c0] px-1.5 py-0.5 text-[9px] font-extrabold uppercase text-white">HOY</span>
                    @elseif($day['is_holiday'])
                      <span class="inline-flex h-2 w-2 rounded-full bg-purple-600" title="{{ $day['holiday_name'] ?? 'Feriado' }}"></span>
                    @else
                      <span></span>
                    @endif
                    <span class="calendar-day-number {{ $day['is_holiday'] ? 'font-bold text-purple-700' : '' }} {{ $day['is_today'] ? 'text-[#0f67c0] font-black' : '' }}">
                      {{ $day['label'] }}
                    </span>
                  </div>

                  {{-- Nombre del feriado si aplica --}}
                  @if($day['is_holiday'] && filled($day['holiday_name']))
                    <p class="mt-1 truncate text-[10px] font-bold text-purple-700 leading-tight" title="{{ $day['holiday_name'] }}">
                      🎉 {{ $day['holiday_name'] }}
                    </p>
                  @endif

                  {{-- Micro-badges de conteo de eventos --}}
                  <div class="calendar-day-events mt-auto pt-1 flex flex-wrap items-center gap-1">
                    
                    {{-- Total marcaciones / asistencias --}}
                    @if(($day['summary']['marcaciones'] ?? 0) > 0)
                      <div class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-800" title="{{ $day['summary']['marcaciones'] }} marcaciones en el día">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>{{ $day['summary']['marcaciones'] }}</span>
                      </div>
                    @endif

                    {{-- Tardanzas normales --}}
                    @if(($day['summary']['red'] ?? 0) > 0)
                      <div class="calendar-day-counter" title="{{ $day['summary']['red'] }} llegadas tarde en el día">
                        <span class="calendar-event-dot calendar-event-dot-red"></span>
                        <span>{{ $day['summary']['red'] }}</span>
                      </div>
                    @endif

                    {{-- Tardanzas con tolerancia excedida --}}
                    @if(($day['summary']['black'] ?? 0) > 0)
                      <div class="calendar-day-counter calendar-day-counter-dark" title="{{ $day['summary']['black'] }} excedieron tolerancia mensual">
                        <span class="calendar-event-dot calendar-event-dot-black"></span>
                        <span>{{ $day['summary']['black'] }}</span>
                      </div>
                    @endif

                    {{-- Omisiones de marcación --}}
                    @if(($day['summary']['omisiones'] ?? 0) > 0)
                      <div class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold text-amber-800" title="{{ $day['summary']['omisiones'] }} omisiones de entrada/salida">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ $day['summary']['omisiones'] }}</span>
                      </div>
                    @endif

                    {{-- Faltas --}}
                    @if(($day['summary']['faltas'] ?? 0) > 0)
                      <div class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-800" title="{{ $day['summary']['faltas'] }} faltas / ausencias en la fecha">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-800"></span>
                        <span>{{ $day['summary']['faltas'] }}</span>
                      </div>
                    @endif

                  </div>
                </article>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>

    </div>

    {{-- Tarjetas de información y reglas de asistencia al pie --}}
    <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      @foreach($calendar['milestones'] as $milestone)
        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-xs text-slate-600 font-medium">
          {{ $milestone }}
        </div>
      @endforeach
    </div>

  </section>
</div>

