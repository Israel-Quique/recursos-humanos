<div class="page-stack">

  <style>
    /* Estilos explícitos de alta visibilidad para celdas de asistencia */
    .celda-cuadrito {
      padding: 0 !important;
      text-align: center !important;
      position: relative !important;
      height: 34px !important;
      min-width: 36px !important;
      max-width: 40px !important;
      transition: all 0.15s ease-in-out !important;
    }
    .celda-cuadrito-p {
      background-color: #f8fafc !important;
      border-right: 1px solid #cbd5e1 !important;
      border-bottom: 1px solid #cbd5e1 !important;
    }
    .celda-cuadrito-p:hover {
      background-color: #dcfce7 !important;
    }
    .celda-cuadrito-f {
      background-color: #fee2e2 !important;
      border: 2px solid #ef4444 !important;
      box-shadow: inset 0 0 0 1px #dc2626 !important;
    }
    .celda-cuadrito-f:hover {
      background-color: #fecdd3 !important;
    }
    .celda-cuadrito-o {
      background-color: #fef3c7 !important;
      border: 2px solid #f59e0b !important;
      box-shadow: inset 0 0 0 1px #d97706 !important;
    }
    .celda-cuadrito-o:hover {
      background-color: #fde68a !important;
    }
    .celda-cuadrito-bm {
      background-color: #dbeafe !important;
      border: 2px solid #3b82f6 !important;
      box-shadow: inset 0 0 0 1px #2563eb !important;
    }
    .celda-cuadrito-bm:hover {
      background-color: #bfdbfe !important;
    }
    .celda-cuadrito-cv {
      background-color: #ede9fe !important;
      border: 2px solid #8b5cf6 !important;
      box-shadow: inset 0 0 0 1px #7c3aed !important;
    }
    .celda-cuadrito-cv:hover {
      background-color: #ddd6fe !important;
    }

    .select-cuadrito {
      width: 100% !important;
      height: 34px !important;
      line-height: 34px !important;
      text-align: center !important;
      text-align-last: center !important;
      font-size: 12px !important;
      font-weight: 900 !important;
      cursor: pointer !important;
      border: none !important;
      outline: none !important;
      background-color: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      appearance: none !important;
      box-shadow: none !important;
    }
    .select-cuadrito option {
      background-color: #ffffff !important;
      color: #0f172a !important;
      font-weight: 700 !important;
      font-size: 13px !important;
      text-align: center !important;
      padding: 4px 8px !important;
    }
  </style>

  {{-- ============================================================ --}}
  {{-- MODAL: DESGLOSE DE FECHAS E INCIDENCIAS                      --}}
  {{-- ============================================================ --}}
  @if ($showDetailModal && $selectedDetail)
    <div class="app-modal-backdrop" wire:click="cerrarDetalleFechas">
      <div class="app-modal-card app-modal-card-detail max-w-2xl" x-on:click.stop>
        <button type="button" wire:click="cerrarDetalleFechas" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker text-xs font-bold text-sky-600 uppercase tracking-wide">Desglose de días no correspondidos</p>
            <h3 class="section-title text-xl font-bold text-slate-900">{{ $selectedDetail['nombre'] ?? 'Personal' }}</h3>
            <p class="section-copy-sm text-sm text-slate-500">
              CI/Código: <strong>{{ $selectedDetail['codigo'] }}</strong> · Sucursal: <strong>{{ $selectedDetail['sucursal'] }}</strong> · Cargo: {{ $selectedDetail['cargo'] }}
            </p>
          </div>
        </div>

        {{-- Resumen de días --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3 text-center">
            <span class="text-xs font-semibold text-rose-700 uppercase">Faltas</span>
            <p class="mt-1 text-xl font-extrabold text-rose-800">{{ $selectedDetail['faltas'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 text-center">
            <span class="text-xs font-semibold text-amber-700 uppercase">Omisiones</span>
            <p class="mt-1 text-xl font-extrabold text-amber-800">{{ $selectedDetail['omisiones'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-3 text-center">
            <span class="text-xs font-semibold text-blue-700 uppercase">Bajas Médicas</span>
            <p class="mt-1 text-xl font-extrabold text-blue-800">{{ $selectedDetail['bajas_medicas'] ?? 0 }}</p>
          </div>
          <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-3 text-center">
            <span class="text-xs font-semibold text-purple-700 uppercase">Comisión Viaje</span>
            <p class="mt-1 text-xl font-extrabold text-purple-800">{{ $selectedDetail['comisiones_viaje'] ?? 0 }}</p>
          </div>
        </div>

        <div class="mt-6 space-y-4 max-h-[400px] overflow-y-auto pr-1">
          {{-- Faltas --}}
          @if(!empty($selectedDetail['fechas_faltas']))
            <div class="rounded-xl border border-rose-200 bg-white p-4">
              <h4 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-rose-600"></span> Faltas injustificadas registradas ({{ count($selectedDetail['fechas_faltas']) }})
              </h4>
              <ul class="mt-2 space-y-1 text-xs text-slate-600">
                @foreach($selectedDetail['fechas_faltas'] as $f)
                  <li class="flex justify-between items-center bg-rose-50/50 px-3 py-1.5 rounded-md">
                    <span class="font-semibold text-rose-900">{{ $f['fecha'] }}</span>
                    <span class="text-slate-500">{{ $f['detalle'] }}</span>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Omisiones --}}
          @if(!empty($selectedDetail['fechas_omisiones']))
            <div class="rounded-xl border border-amber-200 bg-white p-4">
              <h4 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-amber-600"></span> Omisiones de marcado ({{ count($selectedDetail['fechas_omisiones']) }})
              </h4>
              <ul class="mt-2 space-y-1 text-xs text-slate-600">
                @foreach($selectedDetail['fechas_omisiones'] as $o)
                  <li class="flex justify-between items-center bg-amber-50/50 px-3 py-1.5 rounded-md">
                    <span class="font-semibold text-amber-900">{{ $o['fecha'] }}</span>
                    <span class="text-slate-500">{{ $o['detalle'] }}</span>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Bajas Médicas --}}
          @if(!empty($selectedDetail['fechas_bajas']))
            <div class="rounded-xl border border-blue-200 bg-white p-4">
              <h4 class="text-sm font-bold text-blue-800 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span> Bajas médicas autorizadas ({{ count($selectedDetail['fechas_bajas']) }})
              </h4>
              <ul class="mt-2 space-y-1.5 text-xs text-slate-600">
                @foreach($selectedDetail['fechas_bajas'] as $b)
                  <li class="bg-blue-50/50 p-2.5 rounded-md">
                    <div class="flex justify-between font-semibold text-blue-900">
                      <span>{{ $b['fecha'] }}</span>
                      <span class="rounded bg-blue-200 px-1.5 py-0.5 text-[10px] text-blue-900">{{ $b['dias'] }} día(s)</span>
                    </div>
                    <p class="mt-1 text-slate-500">{{ $b['motivo'] }}</p>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Comisiones de Viaje --}}
          @if(!empty($selectedDetail['fechas_comisiones']))
            <div class="rounded-xl border border-purple-200 bg-white p-4">
              <h4 class="text-sm font-bold text-purple-800 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-purple-600"></span> Comisiones de viaje autorizadas ({{ count($selectedDetail['fechas_comisiones']) }})
              </h4>
              <ul class="mt-2 space-y-1.5 text-xs text-slate-600">
                @foreach($selectedDetail['fechas_comisiones'] as $c)
                  <li class="bg-purple-50/50 p-2.5 rounded-md">
                    <div class="flex justify-between font-semibold text-purple-900">
                      <span>{{ $c['fecha'] }}</span>
                      <span class="rounded bg-purple-200 px-1.5 py-0.5 text-[10px] text-purple-900">{{ $c['dias'] }} día(s)</span>
                    </div>
                    <p class="mt-1 text-slate-500">{{ $c['motivo'] }}</p>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          @if(empty($selectedDetail['fechas_faltas']) && empty($selectedDetail['fechas_omisiones']) && empty($selectedDetail['fechas_bajas']) && empty($selectedDetail['fechas_comisiones']))
            <div class="text-center py-8 text-slate-400">
              <p>Este funcionario no tiene registros automáticos en el período.</p>
              <p class="text-xs mt-1">Los días pueden haber sido ingresados o ajustados de forma manual.</p>
            </div>
          @endif
        </div>

        <div class="mt-6 flex justify-between items-center border-t border-slate-200 pt-4">
          <div>
            <span class="text-xs text-slate-500">Total días descuento: <strong>{{ $selectedDetail['total_dias'] }}</strong></span>
            <span class="ml-3 text-xs text-rose-700 font-bold">A descontar: Bs. {{ number_format($selectedDetail['total_monto'] ?? 0, 2) }}</span>
          </div>
          <button type="button" wire:click="cerrarDetalleFechas" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
            Entendido
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- ============================================================ --}}
  {{-- HERO HEADER Y ACCIONES                                       --}}
  {{-- ============================================================ --}}
  <div class="report-hero">
    <div class="report-hero-content">
      <div>
        <p class="report-hero-kicker">Agencia Boliviana de Correos · Recursos Humanos</p>
        <h1 class="report-hero-title">Planilla de Descuento de Refrigerio / Comida</h1>
        <p class="report-hero-copy">
          Control de días no correspondidos (Faltas, Omisiones, Bajas Médicas y Comisiones de Viaje) · 
          <strong>{{ $periodoLabel }}</strong> · Sucursal: <strong>{{ $selectedBranch ?: 'Todas las sucursales' }}</strong>
          @if($ultimaGuardada)
            · <span class="text-emerald-700 font-medium">✓ Guardada el {{ $ultimaGuardada }}</span>
          @elseif($isDirty)
            · <span class="text-amber-700 font-medium font-semibold">● Cambios sin guardar</span>
          @endif
        </p>
      </div>

      <div class="report-hero-actions flex flex-wrap items-center gap-2">
        {{-- Botón Jalar / Recalcular datos --}}
        <button type="button" wire:click="jalarDatos" wire:loading.attr="disabled"
          class="flex items-center gap-2 rounded-xl border border-sky-300 bg-sky-50 px-3.5 py-2 text-sm font-semibold text-sky-800 shadow-sm hover:bg-sky-100 transition disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span wire:loading.remove wire:target="jalarDatos">Jalar datos del sistema</span>
          <span wire:loading wire:target="jalarDatos">Calculando...</span>
        </button>

        {{-- Botón Guardar Planilla --}}
        <button type="button" wire:click="guardarPlanilla" wire:loading.attr="disabled"
          class="flex items-center gap-2 rounded-xl {{ $isDirty ? 'bg-amber-600 text-white hover:bg-amber-700 shadow-md animate-pulse' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm' }} px-4 py-2 text-sm font-semibold transition disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          <span wire:loading.remove wire:target="guardarPlanilla">Guardar Planilla</span>
          <span wire:loading wire:target="guardarPlanilla">Guardando...</span>
        </button>

        {{-- Botón Descargar Excel --}}
        <button type="button" wire:click="descargarExcel" wire:loading.attr="disabled"
          class="flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-800 shadow-sm hover:bg-emerald-100 transition disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-700" viewBox="0 0 24 24" fill="currentColor">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
            <path d="m9.5 16.5 2-2.5-2-2.5h1.5l1.25 1.7 1.25-1.7H15l-2 2.5 2 2.5h-1.5l-1.25-1.7-1.25 1.7H9.5z"/>
          </svg>
          <span wire:loading.remove wire:target="descargarExcel">Excel (.xlsx)</span>
          <span wire:loading wire:target="descargarExcel">Generando...</span>
        </button>

        {{-- Botón Descargar PDF --}}
        <button type="button" wire:click="descargarPdf" wire:loading.attr="disabled"
          class="flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-800 shadow-sm hover:bg-rose-100 transition disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span wire:loading.remove wire:target="descargarPdf">PDF</span>
          <span wire:loading wire:target="descargarPdf">Generando...</span>
        </button>
      </div>
    </div>

    {{-- Filtros y Configuración de Tarifa --}}
    <div class="report-filter-bar grid gap-4 sm:grid-cols-2 lg:grid-cols-5 mt-4">
      <div class="report-filter-field">
        <label class="report-filter-label" for="ref-month">Período (Mes)</label>
        <input id="ref-month" type="month" wire:model.live="referenceMonth" class="report-filter-input font-semibold text-slate-800">
      </div>

      <div class="report-filter-field">
        <label class="report-filter-label" for="ref-branch">Ciudad / Sucursal</label>
        <select id="ref-branch" wire:model.live="selectedBranch" class="report-filter-input font-medium">
          <option value="">Todas las sucursales</option>
          @foreach($branches as $branch)
            <option value="{{ $branch }}">{{ $branch }}</option>
          @endforeach
        </select>
      </div>

      <div class="report-filter-field">
        <label class="report-filter-label" for="ref-tarifa">Tarifa Diaria (Bs./Día)</label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Bs.</span>
          <input id="ref-tarifa" type="number" step="0.50" min="0" wire:model.live.debounce.400ms="tarifaDiaria" class="report-filter-input pl-9 font-bold text-slate-900" placeholder="20.00">
        </div>
      </div>

      <div class="report-filter-field sm:col-span-2">
        <label class="report-filter-label" for="ref-search">Buscar Personal o CI</label>
        <div class="relative">
          <input id="ref-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Filtrar por nombre, apellido, código..." class="report-filter-input">
          @if(filled($search))
            <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600">✕</button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Mensajes de estado / alerta --}}
  @if(session()->has('status'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">✕</button>
    </div>
  @endif

  {{-- ============================================================ --}}
  {{-- TARJETAS DE MÉTRICAS EJECUTIVAS                              --}}
  {{-- ============================================================ --}}
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Personal Activo</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-slate-900">{{ $metricas['total_personal'] }}</p>
        <span class="text-xs font-semibold text-slate-500">{{ $metricas['personal_con_descuento'] }} afectados</span>
      </div>
    </div>

    <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Faltas</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-rose-900">{{ $metricas['total_faltas'] }}</p>
        <span class="text-xs font-medium text-rose-600">días</span>
      </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Omisiones</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-amber-900">{{ $metricas['total_omisiones'] }}</p>
        <span class="text-xs font-medium text-amber-600">días</span>
      </div>
    </div>

    <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Bajas Médicas</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-blue-900">{{ $metricas['total_bajas_medicas'] }}</p>
        <span class="text-xs font-medium text-blue-600">días</span>
      </div>
    </div>

    <div class="rounded-2xl border border-purple-200 bg-purple-50/40 p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Comisión Viaje</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-purple-900">{{ $metricas['total_comisiones_viaje'] }}</p>
        <span class="text-xs font-medium text-purple-600">días</span>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-300 bg-slate-900 text-white p-4 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Total Días Desc.</p>
      <div class="mt-2 flex items-baseline justify-between">
        <p class="text-2xl font-extrabold text-amber-400">{{ $metricas['gran_total_dias'] }}</p>
        <span class="text-xs text-slate-300">días a descontar</span>
      </div>
    </div>

    <div class="rounded-2xl border border-rose-300 bg-gradient-to-br from-rose-900 to-red-950 text-white p-4 shadow-md sm:col-span-2 lg:col-span-1">
      <p class="text-[11px] font-bold uppercase tracking-wider text-rose-200">Total a No Pagar</p>
      <div class="mt-1">
        <p class="text-2xl font-black text-white">Bs. {{ number_format($metricas['gran_total_monto'], 2) }}</p>
        <span class="text-[11px] text-rose-200">Tarifa Bs. {{ number_format((float) ($tarifaDiaria ?: 0), 2) }}/día</span>
      </div>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- BARRA DE HERRAMIENTAS Y CAMBIO DE VISTA                      --}}
  {{-- ============================================================ --}}
  <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
    <div class="flex items-center gap-2">
      <span class="text-xs font-bold uppercase text-slate-500 tracking-wider">Formato de visualización:</span>
      <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-xs">
        <button type="button" wire:click="setVistaFormato('consolidado')"
          class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $vistaFormato === 'consolidado' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <span>Tabla Consolidada</span>
        </button>

        <button type="button" wire:click="setVistaFormato('vertical')"
          class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $vistaFormato === 'vertical' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
          <span>Formato Vertical por Personal</span>
        </button>
      </div>
    </div>

    <div class="text-xs text-slate-500">
      Mostrando <strong>{{ count($filteredItems) }}</strong> de <strong>{{ count($items) }}</strong> funcionarios
      @if(filled($search))
        (filtrados por "{{ $search }}")
      @endif
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- VISTA 1: MATRIZ DE CONTROL DE ASISTENCIA DEL PERSONAL        --}}
  {{-- (IDÉNTICA A LA IMAGEN DE REFERENCIA)                         --}}
  {{-- ============================================================ --}}
  @if($vistaFormato === 'consolidado')
    <div class="rounded-2xl border border-slate-300 bg-white shadow-sm overflow-hidden mb-6">
      {{-- Cabecera azul principal según la imagen --}}
      <div class="bg-[#0f4c81] text-white px-5 py-3 flex flex-wrap items-center justify-between gap-2 shadow-xs">
        <h2 class="text-base sm:text-lg font-black tracking-wider uppercase flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sky-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          <span>CONTROL DE ASISTENCIA DEL PERSONAL</span>
        </h2>
        <div class="inline-flex items-center gap-2 rounded-md border border-white/25 bg-white/15 px-3 py-1 text-xs font-bold tracking-wide text-white backdrop-blur-xs">
          <span class="text-sky-200 font-semibold">Mes:</span>
          <span>{{ $periodoLabel }}</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-slate-300">
          <thead>
            <tr class="bg-[#e2e8f0] text-slate-800 text-[11px] font-bold border-b border-slate-300">
              <th class="py-2.5 px-2 text-center w-10 border-r border-slate-300">N°</th>
              <th class="py-2.5 px-3 text-left min-w-[200px] border-r border-slate-300">Nombre y Apellido</th>

              {{-- Columnas de días hábiles (Lunes a Viernes, quita sábados y domingos) --}}
              @foreach($diasMes as $dia)
                <th class="py-2 px-1 text-center w-9 min-w-[34px] max-w-[38px] border-r border-slate-300 bg-[#e0f2fe]/70 select-none" title="{{ $dia['fecha_corta'] }}">
                  <div class="flex flex-col items-center justify-end h-16 pb-1">
                    <span class="text-[9px] font-mono font-semibold text-slate-600 -rotate-90 whitespace-nowrap mb-2 origin-center tracking-tight">{{ $dia['dia'] }}/{{ substr($dia['fecha_corta'], 3, 2) }}</span>
                    <span class="text-[10.5px] font-extrabold text-slate-800 mt-auto">{{ $dia['dia_nombre'] }}</span>
                  </div>
                </th>
              @endforeach

              {{-- Columnas de días de descuento y montos a la derecha --}}
              <th class="py-2.5 px-2 text-center w-20 min-w-[75px] border-r border-slate-300 bg-amber-100/90 text-amber-950 font-black" title="Total de días a descontar">
                Días Desc.<br><span class="text-[8.5px] font-bold text-amber-800">(Total)</span>
              </th>
              <th class="py-2.5 px-2.5 text-right w-24 min-w-[95px] border-r border-slate-300 bg-rose-100/90 text-rose-950 font-black" title="Monto a no pagar">
                A No Pagar<br><span class="text-[8px] font-bold text-rose-800">(Bs. {{ number_format((float) ($tarifaDiaria ?: 0), 2) }}/d)</span>
              </th>
              <th class="py-2.5 px-1.5 text-center w-10 min-w-[40px] text-slate-700 font-bold" title="Detalle de incidencias">
                Info
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 text-slate-800">
            @forelse($filteredItems as $idx => $item)
              @php
                $origIdx = array_search($item['empleado_id'], array_column($items, 'empleado_id'));
                if ($origIdx === false) { $origIdx = $idx; }
                $hasDescuento = ($item['total_dias'] ?? 0) > 0;
                $diasItem = $item['dias'] ?? [];
              @endphp
              <tr class="hover:bg-sky-50/30 transition {{ $hasDescuento ? 'bg-amber-50/15' : '' }}">
                <td class="py-2 px-2 text-center text-slate-500 font-mono text-xs border-r border-slate-200">{{ $idx + 1 }}</td>
                <td class="py-2 px-3 border-r border-slate-200 whitespace-nowrap">
                  <div>
                    <span class="font-bold text-slate-900 text-xs">{{ $item['nombre'] }}</span>
                    <span class="text-[10px] text-slate-400 font-mono ml-1">({{ $item['codigo'] }})</span>
                  </div>
                </td>

                {{-- Celdas interactivas por cada día hábil con colores que resaltan --}}
                @foreach($diasMes as $dia)
                  @php
                    $st = strtolower($diasItem[$dia['fecha']] ?? 'p');
                    $infoColor = match($st) {
                      'f' => [
                        'class' => 'celda-cuadrito celda-cuadrito-f',
                        'td_style' => 'background-color: #fee2e2 !important; border: 2px solid #ef4444 !important;',
                        'sel_style' => 'background-color: #fee2e2 !important; color: #b91c1c !important; font-weight: 900 !important;',
                        'title' => 'Falta (F)',
                      ],
                      'o' => [
                        'class' => 'celda-cuadrito celda-cuadrito-o',
                        'td_style' => 'background-color: #fef3c7 !important; border: 2px solid #f59e0b !important;',
                        'sel_style' => 'background-color: #fef3c7 !important; color: #b45309 !important; font-weight: 900 !important;',
                        'title' => 'Omisión (O)',
                      ],
                      'bm' => [
                        'class' => 'celda-cuadrito celda-cuadrito-bm',
                        'td_style' => 'background-color: #dbeafe !important; border: 2px solid #3b82f6 !important;',
                        'sel_style' => 'background-color: #dbeafe !important; color: #1d4ed8 !important; font-weight: 900 !important;',
                        'title' => 'Baja Médica (Bm)',
                      ],
                      'cv' => [
                        'class' => 'celda-cuadrito celda-cuadrito-cv',
                        'td_style' => 'background-color: #ede9fe !important; border: 2px solid #8b5cf6 !important;',
                        'sel_style' => 'background-color: #ede9fe !important; color: #6d28d9 !important; font-weight: 900 !important;',
                        'title' => 'Comisión de Viaje (Cv)',
                      ],
                      default => [
                        'class' => 'celda-cuadrito celda-cuadrito-p',
                        'td_style' => 'background-color: #f8fafc !important; border-right: 1px solid #cbd5e1 !important; border-bottom: 1px solid #cbd5e1 !important;',
                        'sel_style' => 'background-color: transparent !important; color: #15803d !important; font-weight: 700 !important;',
                        'title' => 'Presente (P)',
                      ],
                    };
                  @endphp
                  <td class="{{ $infoColor['class'] }}"
                      style="{{ $infoColor['td_style'] }}"
                      title="{{ $dia['dia_nombre'] }} {{ $dia['fecha_corta'] }}: {{ $infoColor['title'] }} - Click para cambiar estado">
                    <select
                      wire:change="actualizarEstadoDia({{ $origIdx }}, '{{ $dia['fecha'] }}', $event.target.value)"
                      class="select-cuadrito"
                      style="{{ $infoColor['sel_style'] }}"
                      aria-label="Estado día {{ $dia['dia'] }}">
                      <option value="p" {{ $st === 'p' ? 'selected' : '' }}>P</option>
                      <option value="f" {{ $st === 'f' ? 'selected' : '' }}>F</option>
                      <option value="o" {{ $st === 'o' ? 'selected' : '' }}>O</option>
                      <option value="bm" {{ $st === 'bm' ? 'selected' : '' }}>Bm</option>
                      <option value="cv" {{ $st === 'cv' ? 'selected' : '' }}>Cv</option>
                    </select>
                  </td>
                @endforeach

                {{-- Columna Total Días Descuento --}}
                <td class="py-2 px-2 text-center border-r border-b border-slate-300 {{ $hasDescuento ? 'bg-amber-50/80 font-black' : 'text-slate-400' }}">
                  @if($hasDescuento)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[11px] font-black bg-amber-200/90 text-amber-950 border border-amber-400 shadow-2xs">
                      {{ $item['total_dias'] }} d
                    </span>
                  @else
                    <span class="text-slate-400 font-semibold text-xs">0</span>
                  @endif
                </td>

                {{-- Columna Monto A No Pagar (Bs.) --}}
                <td class="py-2 px-2.5 text-right font-mono text-xs border-r border-b border-slate-300 {{ $hasDescuento ? 'bg-rose-50/80 font-black text-rose-800' : 'text-slate-400' }}">
                  Bs. {{ number_format($item['total_monto'], 2) }}
                </td>

                {{-- Botón Info / Detalle de Fechas --}}
                <td class="py-2 px-1 text-center border-b border-slate-300">
                  <button type="button" wire:click="abrirDetalleFechas({{ $origIdx }})"
                    class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-sky-700 hover:bg-sky-50 hover:border-sky-300 transition"
                    title="Ver detalle de incidencias de {{ $item['nombre'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ count($diasMes) + 5 }}" class="py-12 text-center text-slate-400">
                  No se encontraron funcionarios para los filtros seleccionados.
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="bg-slate-100 text-slate-900 font-extrabold text-xs border-t-2 border-slate-300">
            <tr>
              <td colspan="{{ count($diasMes) + 2 }}" class="py-3 px-4 text-right uppercase tracking-wider text-slate-700 font-bold">
                Totales Generales:
              </td>
              <td class="py-3 px-2 text-center text-amber-950 border-l border-slate-300 bg-amber-100/90 text-xs font-black">
                {{ $metricas['gran_total_dias'] }} días
              </td>
              <td class="py-3 px-2.5 text-right text-rose-950 border-l border-slate-300 bg-rose-100/90 font-mono text-xs font-black">
                Bs. {{ number_format($metricas['gran_total_monto'], 2) }}
              </td>
              <td class="border-l border-slate-300 bg-slate-100"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- SECCIÓN SIMBOLOGÍA (según imagen de referencia) --}}
      <div class="border-t-2 border-slate-300">
        <div class="bg-[#0f4c81] text-white px-5 py-2.5 font-black text-xs uppercase tracking-wider flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-sky-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>SIMBOLOGÍA</span>
        </div>
        <div class="p-4 bg-slate-50 grid gap-3 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
          {{-- P: Presente --}}
          <div class="rounded-xl border border-emerald-200 bg-white p-3 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-8 h-8 rounded-lg bg-[#dcfce7] text-[#15803d] font-black text-sm flex items-center justify-center border border-emerald-300 shadow-xs">P</span>
              <span class="font-extrabold text-slate-900 text-xs uppercase">Presente</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">El colaborador realizó su jornada laboral.</p>
          </div>

          {{-- F: Falta --}}
          <div class="rounded-xl border border-rose-200 bg-white p-3 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-8 h-8 rounded-lg bg-[#fee2e2] text-[#b91c1c] font-black text-sm flex items-center justify-center border border-rose-300 shadow-xs">F</span>
              <span class="font-extrabold text-slate-900 text-xs uppercase">Falta</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">No asistió a su jornada.</p>
          </div>

          {{-- O: Omisión --}}
          <div class="rounded-xl border border-amber-200 bg-white p-3 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-8 h-8 rounded-lg bg-[#fef3c7] text-[#b45309] font-black text-sm flex items-center justify-center border border-amber-300 shadow-xs">O</span>
              <span class="font-extrabold text-slate-900 text-xs uppercase">Omisión</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">No registró entrada o salida.</p>
          </div>

          {{-- Bm: Baja médica --}}
          <div class="rounded-xl border border-sky-200 bg-white p-3 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-8 h-8 rounded-lg bg-[#dbeafe] text-[#1d4ed8] font-black text-xs flex items-center justify-center border border-sky-300 shadow-xs">Bm</span>
              <span class="font-extrabold text-slate-900 text-xs uppercase">Baja médica</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">Incapacidad médica o reposo.</p>
          </div>

          {{-- Cv: Comisión de viaje --}}
          <div class="rounded-xl border border-purple-200 bg-white p-3 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-8 h-8 rounded-lg bg-[#ede9fe] text-[#6d28d9] font-black text-xs flex items-center justify-center border border-purple-300 shadow-xs">Cv</span>
              <span class="font-extrabold text-slate-900 text-xs uppercase">Comisión viaje</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">En comisión de trabajo o viaje laboral.</p>
          </div>
        </div>
        <div class="px-5 py-2 bg-slate-100 border-t border-slate-200 text-[11px] text-slate-500 italic">
          Nota: La presente planilla es el control de asistencia para el cálculo y descuento de refrigerio / comida del personal institucional.
        </div>
      </div>
    </div>
  @endif

  {{-- ============================================================ --}}
  {{-- VISTA 2: FORMATO VERTICAL POR PERSONAL (COMO LO PIDIÓ EL USUARIO) --}}
  {{-- "poner el nombre en una columna y en esa misma columna poner --}}
  {{-- si tiene falta abajo 1 omision abajo 2 baja medica abajo 2... --}}
  {{-- y que haya una sumatoria y que diga cuanto no se debe pagar" --}}
  {{-- ============================================================ --}}
  @if($vistaFormato === 'vertical')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @forelse($filteredItems as $idx => $item)
        @php
          $origIdx = array_search($item['empleado_id'], array_column($items, 'empleado_id'));
          if ($origIdx === false) { $origIdx = $idx; }
          $hasDescuento = ($item['total_dias'] ?? 0) > 0;
        @endphp
        <div class="rounded-2xl border {{ $hasDescuento ? 'border-rose-300 shadow-md' : 'border-slate-200 shadow-xs' }} bg-white overflow-hidden flex flex-col justify-between">
          {{-- Cabecera del Personal --}}
          <div class="p-3.5 {{ $hasDescuento ? 'bg-gradient-to-r from-slate-900 to-slate-800 text-white' : 'bg-slate-100 text-slate-800' }}">
            <div class="flex items-center justify-between">
              <span class="font-mono text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-white/20">
                CI: {{ $item['codigo'] }}
              </span>
              <span class="text-[11px] font-semibold opacity-90">{{ $item['sucursal'] }}</span>
            </div>
            <h3 class="mt-2 text-sm font-extrabold leading-tight {{ $hasDescuento ? 'text-white' : 'text-slate-900' }}">
              {{ $item['nombre'] }}
            </h3>
          </div>

          {{-- Filas de Conceptos Verticales --}}
          <div class="p-3.5 space-y-2 text-xs flex-1">
            {{-- Falta --}}
            <div class="flex items-center justify-between rounded-xl bg-rose-50/60 p-2 border border-rose-100">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                <span class="font-bold text-rose-900">Falta</span>
              </div>
              <div class="flex items-center gap-1.5">
                <input type="number" min="0" max="31"
                  value="{{ $item['faltas'] ?? 0 }}"
                  wire:change="actualizarDia({{ $origIdx }}, 'faltas', $event.target.value)"
                  class="w-14 rounded-md border border-rose-200 bg-white px-1.5 py-0.5 text-center font-bold text-rose-900 shadow-2xs focus:outline-none">
                <span class="text-[11px] text-slate-500">días</span>
              </div>
            </div>

            {{-- Omisión --}}
            <div class="flex items-center justify-between rounded-xl bg-amber-50/60 p-2 border border-amber-100">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span class="font-bold text-amber-900">Omisión</span>
              </div>
              <div class="flex items-center gap-1.5">
                <input type="number" min="0" max="31"
                  value="{{ $item['omisiones'] ?? 0 }}"
                  wire:change="actualizarDia({{ $origIdx }}, 'omisiones', $event.target.value)"
                  class="w-14 rounded-md border border-amber-200 bg-white px-1.5 py-0.5 text-center font-bold text-amber-900 shadow-2xs focus:outline-none">
                <span class="text-[11px] text-slate-500">días</span>
              </div>
            </div>

            {{-- Baja médica --}}
            <div class="flex items-center justify-between rounded-xl bg-blue-50/60 p-2 border border-blue-100">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                <span class="font-bold text-blue-900">Baja médica</span>
              </div>
              <div class="flex items-center gap-1.5">
                <input type="number" min="0" max="31"
                  value="{{ $item['bajas_medicas'] ?? 0 }}"
                  wire:change="actualizarDia({{ $origIdx }}, 'bajas_medicas', $event.target.value)"
                  class="w-14 rounded-md border border-blue-200 bg-white px-1.5 py-0.5 text-center font-bold text-blue-900 shadow-2xs focus:outline-none">
                <span class="text-[11px] text-slate-500">días</span>
              </div>
            </div>

            {{-- Comisión de viaje --}}
            <div class="flex items-center justify-between rounded-xl bg-purple-50/60 p-2 border border-purple-100">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                <span class="font-bold text-purple-900">Comisión de viaje</span>
              </div>
              <div class="flex items-center gap-1.5">
                <input type="number" min="0" max="31"
                  value="{{ $item['comisiones_viaje'] ?? 0 }}"
                  wire:change="actualizarDia({{ $origIdx }}, 'comisiones_viaje', $event.target.value)"
                  class="w-14 rounded-md border border-purple-200 bg-white px-1.5 py-0.5 text-center font-bold text-purple-900 shadow-2xs focus:outline-none">
                <span class="text-[11px] text-slate-500">días</span>
              </div>
            </div>
          </div>

          {{-- Sumatoria y Cuánto No se Debe Pagar --}}
          <div class="p-3.5 bg-slate-50 border-t border-slate-200 space-y-2">
            <div class="flex justify-between items-center text-xs">
              <span class="font-bold text-slate-700">SUMATORIA:</span>
              <span class="font-black text-slate-900 px-2 py-0.5 rounded bg-slate-200">
                {{ $item['total_dias'] ?? 0 }} días
              </span>
            </div>

            <div class="flex justify-between items-center rounded-xl bg-rose-100/70 p-2 border border-rose-200">
              <span class="text-[11px] font-extrabold text-rose-900 uppercase">A no pagar:</span>
              <span class="font-mono font-black text-sm text-rose-800">
                Bs. {{ number_format($item['total_monto'] ?? 0, 2) }}
              </span>
            </div>

            <button type="button" wire:click="abrirDetalleFechas({{ $origIdx }})"
              class="w-full text-center text-[11px] font-semibold text-sky-700 hover:text-sky-900 pt-1">
              Ver detalle de fechas →
            </button>
          </div>
        </div>
      @empty
        <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
          No se encontraron funcionarios para los filtros seleccionados.
        </div>
      @endforelse
    </div>
  @endif

</div>
