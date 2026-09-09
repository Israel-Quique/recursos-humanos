<div class="page-stack">
  {{-- ALERTAS DE ESTADO --}}
  @if (session()->has('status'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">✓</span>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs">✕</button>
    </div>
  @endif

  {{-- MODAL PARA EDITAR REGLA DE SANCIÓN --}}
  @if ($showEditReglaModal)
    <div class="app-modal-backdrop" wire:click="closeEditReglaModal">
      <div class="app-modal-card" x-on:click.stop style="max-width: 38rem;">
        <button type="button" wire:click="closeEditReglaModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Reglamento Institucional</p>
            <h3 class="section-title app-modal-title">Editar regla de sanción</h3>
            <p class="section-copy-sm">Modifica los rangos, días de deducción o el texto legal de esta sanción.</p>
          </div>
        </div>

        <form wire:submit="saveRegla" class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label">Causal / Descripción del tramo *</label>
            <input type="text" wire:model="reglaCausal" class="form-input font-bold" placeholder="Ej: 31 a 45 minutos">
            @error('reglaCausal') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Rango mínimo ({{ $reglaUnidad }})</label>
            <input type="number" wire:model="reglaRangoMin" class="form-input" placeholder="Ej: 31">
            @error('reglaRangoMin') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Rango máximo (dejar vacío si es sin límite)</label>
            <input type="number" wire:model="reglaRangoMax" class="form-input" placeholder="Ej: 45">
            @error('reglaRangoMax') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Sanción en texto oficial *</label>
            <input type="text" wire:model="reglaSancionTexto" class="form-input" placeholder="Ej: Medio (1/2) día">
            @error('reglaSancionTexto') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Días de remuneración mensual *</label>
            <input type="number" step="0.25" wire:model="reglaDiasSancion" class="form-input" placeholder="Ej: 0.50">
            @error('reglaDiasSancion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Observaciones / Marco normativo</label>
            <textarea wire:model="reglaObservaciones" rows="2" class="form-input" placeholder="Detalle adicional sobre esta causal..."></textarea>
            @error('reglaObservaciones') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div>
              <span class="text-xs font-bold text-slate-900 block">¿Es falta gravísima con destitución?</span>
              <span class="text-[11px] text-slate-500">Actívalo solo si causa proceso interno y retiro.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" wire:model="reglaEsDestitucion" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
            </label>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeEditReglaModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL PARA CREAR NUEVA REGLA --}}
  @if ($showCreateReglaModal)
    <div class="app-modal-backdrop" wire:click="closeCreateReglaModal">
      <div class="app-modal-card" x-on:click.stop style="max-width: 38rem;">
        <button type="button" wire:click="closeCreateReglaModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Nuevo tramo reglamentario</p>
            <h3 class="section-title app-modal-title">Agregar nueva regla de sanción</h3>
            <p class="section-copy-sm">Define un nuevo escalafón de sanción por atrasos, inasistencias u omisiones.</p>
          </div>
        </div>

        <form wire:submit="saveNewRegla" class="mt-6 grid gap-4 md:grid-cols-2">
          <div>
            <label class="form-label">Categoría *</label>
            <select wire:model="newReglaCategoria" class="form-input">
              <option value="atraso">Atrasos en horario de ingreso</option>
              <option value="inasistencia">Inasistencia o ausencia</option>
              <option value="omision">Omisión de registro</option>
              <option value="gravisima">Falta gravísima</option>
            </select>
          </div>

          <div>
            <label class="form-label">Unidad de medida</label>
            <select wire:model="newReglaUnidad" class="form-input">
              <option value="minutos">Minutos de atraso</option>
              <option value="dias">Días de ausencia</option>
              <option value="ocurrencias">Veces / Ocurrencias en el mes</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Causal / Descripción *</label>
            <input type="text" wire:model="newReglaCausal" class="form-input" placeholder="Ej: 121 o más minutos por segunda vez en la Gestión">
            @error('newReglaCausal') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Rango mínimo</label>
            <input type="number" wire:model="newReglaRangoMin" class="form-input" placeholder="Ej: 121">
            @error('newReglaRangoMin') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Rango máximo (opcional)</label>
            <input type="number" wire:model="newReglaRangoMax" class="form-input" placeholder="Ej: 150">
            @error('newReglaRangoMax') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Sanción en texto *</label>
            <input type="text" wire:model="newReglaSancionTexto" class="form-input" placeholder="Ej: Cinco (5) días">
            @error('newReglaSancionTexto') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Días de remuneración mensual *</label>
            <input type="number" step="0.25" wire:model="newReglaDiasSancion" class="form-input" placeholder="Ej: 5.00">
            @error('newReglaDiasSancion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Observaciones</label>
            <textarea wire:model="newReglaObservaciones" rows="2" class="form-input" placeholder="Fundamento legal o memorándum DAF..."></textarea>
          </div>

          <div class="md:col-span-2 flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
            <div>
              <span class="text-xs font-bold text-slate-900 block">¿Es causal de destitución?</span>
              <span class="text-[11px] text-slate-500">Aplica destitución inmediata mediante proceso interno.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" wire:model="newReglaEsDestitucion" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
            </label>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeCreateReglaModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Crear regla</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- ===================================================================== --}}
  {{-- VISTA: REGLAMENTO OFICIAL DE SANCIONES (ARTÍCULOS 45 Y 48)            --}}
  {{-- ===================================================================== --}}
  <div class="space-y-6">
    {{-- CABECERA INSTITUCIONAL DEL REGLAMENTO --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
              🏛️ Normativa Oficial DAF
            </span>
          </div>
          <h2 class="text-xl font-black text-slate-900 md:text-2xl tracking-tight mt-1.5">
            Reglamento de sanciones
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">
            Administración de escalas sancionatorias por atrasos, inasistencias y omisiones (Artículos 45 y 48).
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            wire:click="openCreateReglaModal"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-xs transition cursor-pointer"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Agregar Tramo o Regla</span>
          </button>

          <button
            type="button"
            wire:click="restaurarReglasPorDefecto"
            wire:confirm="¿Estás seguro de restablecer todas las tablas a los valores oficiales iniciales del reglamento? Cualquier regla personalizada se reajustará."
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-300 shadow-2xs transition cursor-pointer"
            title="Restaurar a las tablas iniciales según normativa oficial"
          >
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
            <span>Restaurar originales</span>
          </button>
        </div>
      </div>
    </div>

    {{-- SIMULADOR EN TIEMPO REAL CON GESTIÓN Y MES --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
      {{-- Header del simulador --}}
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2.5">
          <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700 font-black text-xs shadow-2xs">
            ⚡
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-900 tracking-tight">
              Simulador de Sanción en Vivo
            </h3>
            <p class="text-[11px] text-slate-500">
              Prueba cómo responde la escala reglamentaria según minutos, reincidencia y período (gestión y mes).
            </p>
          </div>
        </div>

        @if ($simuladorResultado)
          <button
            type="button"
            wire:click="$set('simuladorMinutos', null)"
            class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition cursor-pointer"
          >
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <span>Limpiar prueba</span>
          </button>
        @endif
      </div>

      {{-- Controles en 4 columnas limpias --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        {{-- Minutos de atraso --}}
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1.5">
            Minutos de atraso
          </label>
          <div class="relative flex items-center">
            <input
              type="number"
              min="0"
              wire:model.live.debounce.250ms="simuladorMinutos"
              class="w-full h-10 pl-3.5 pr-12 rounded-xl border border-slate-300 bg-slate-50 text-sm font-mono font-bold text-slate-900 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition"
              placeholder="Ej: 45"
            >
            <span class="pointer-events-none absolute right-3 text-xs font-bold text-slate-400 select-none">min</span>
          </div>
          <span class="text-[10px] text-slate-400 mt-1 block">Excedente a 5 min diarios</span>
        </div>

        {{-- Ocurrencia en la gestión --}}
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1.5">
            Ocurrencia en la Gestión
          </label>
          <select
            wire:model.live="simuladorVecesGestion"
            class="w-full h-10 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs font-bold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition cursor-pointer"
          >
            <option value="1">1ra vez en la Gestión</option>
            <option value="2">2da vez en la Gestión</option>
            <option value="3">3ra vez (Falta Gravísima)</option>
          </select>
          <span class="text-[10px] text-slate-400 mt-1 block">Reincidencia anual (Art. 45.I)</span>
        </div>

        {{-- Gestión a evaluar --}}
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1.5">
            Gestión (Año)
          </label>
          <select
            wire:model.live="simuladorGestion"
            class="w-full h-10 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs font-bold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition cursor-pointer"
          >
            @for ($g = 2024; $g <= 2030; $g++)
              <option value="{{ $g }}">Gestión {{ $g }}</option>
            @endfor
          </select>
          <span class="text-[10px] text-slate-400 mt-1 block">Período anual evaluado</span>
        </div>

        {{-- Mes a evaluar --}}
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1.5">
            Mes a evaluar
          </label>
          <select
            wire:model.live="simuladorMes"
            class="w-full h-10 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs font-bold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition cursor-pointer"
          >
            @foreach (\App\Models\ReglaSancion::nombresMeses() as $numMes => $nombreMes)
              <option value="{{ $numMes }}">{{ $nombreMes }}</option>
            @endforeach
          </select>
          <span class="text-[10px] text-slate-400 mt-1 block">Mes de cómputo</span>
        </div>
      </div>

      {{-- Resultado del cálculo en vivo --}}
      @if ($simuladorResultado)
        <div class="mt-4 rounded-xl border p-4 transition-all {{ $simuladorResultado['es_destitucion'] ? 'border-rose-300 bg-rose-50/90 text-rose-950' : (($simuladorResultado['dias_sancion'] ?? 0) > 0 ? 'border-amber-300 bg-amber-50/90 text-amber-950' : 'border-emerald-300 bg-emerald-50/90 text-emerald-950') }}">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-black uppercase tracking-wider">
                  Dictamen oficial • {{ $simuladorResultado['mes_nombre'] }} {{ $simuladorResultado['gestion'] }}
                </span>
                @if ($simuladorResultado['en_espera'] ?? false)
                  <span class="inline-flex items-center gap-1 rounded-full bg-amber-200 border border-amber-300 px-2 py-0.5 text-[10px] font-black text-amber-900">
                    ⏳ Marcha blanca (Sin sanción este período)
                  </span>
                @endif
              </div>
              <p class="text-sm font-black">
                {{ $simuladorResultado['sancion_texto'] }}
              </p>
              <p class="text-xs opacity-80">
                <strong>Causal:</strong> {{ $simuladorResultado['causal'] }}
                @if (!empty($simuladorResultado['observaciones']))
                  <span class="mx-1.5">•</span> <em>{{ $simuladorResultado['observaciones'] }}</em>
                @endif
              </p>
            </div>

            <div class="shrink-0">
              <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black shadow-xs {{ $simuladorResultado['es_destitucion'] ? 'bg-rose-600 text-white' : (($simuladorResultado['dias_sancion'] ?? 0) > 0 ? 'bg-amber-700 text-white' : 'bg-emerald-700 text-white') }}">
                {{ $simuladorResultado['es_destitucion'] ? '🚨 Destitución' : (($simuladorResultado['dias_sancion'] ?? 0) > 0 ? '⚠️ -' . $simuladorResultado['dias_sancion'] . ' Días' : '✓ 0 Días') }}
              </span>
            </div>
          </div>
        </div>
      @else
        <div class="mt-3.5 flex items-center gap-2 text-xs text-slate-500 bg-slate-50 rounded-xl px-3.5 py-2.5 border border-slate-100">
          <span class="text-sm">💡</span>
          <span>Ingresa los minutos de retraso acumulados arriba para previsualizar la sanción oficial en tiempo real según la gestión y mes seleccionados.</span>
        </div>
      @endif
    </div>

    {{-- TABLA I: ATRASOS EN HORARIOS DE INGRESO (ART. 45.I) --}}
    <div class="surface-card">
      <div class="section-head-row">
        <div>
          <span class="text-xs font-black uppercase tracking-wider text-indigo-600 block">Capítulo I · Escala de Atrasos</span>
          <h3 class="section-title">I. Atrasos en los Horarios de Ingreso</h3>
          <p class="section-copy-sm">
            Los minutos de atraso que se registren <strong>posteriores a los cinco (5) minutos de tolerancia</strong> en los horarios de ingreso a la entidad, generarán sanciones económicas a la remuneración mensual, debiendo ser comunicadas mediante Memorándum emitido por el Director Administrativo Financiero, de acuerdo a la siguiente escala:
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-block px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold text-xs border border-emerald-200">
            Tolerancia diaria: 5 min
          </span>

          {{-- Switch por punto para activar/desactivar la normativa de atrasos --}}
          <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
            <div class="text-right">
              <span class="text-xs font-black {{ ($seccionesActivas['atraso'] ?? true) ? 'text-emerald-700' : 'text-slate-400' }} block leading-tight">
                {{ ($seccionesActivas['atraso'] ?? true) ? '✓ Normativa Activa' : '✕ Desactivada' }}
              </span>
              <span class="text-[10px] text-slate-400 block leading-tight">
                {{ ($seccionesActivas['atraso'] ?? true) ? 'Aplica sanción' : 'Sin descuento (exenta)' }}
              </span>
            </div>
            <button
              type="button"
              wire:click="toggleCategoriaActiva('atraso')"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($seccionesActivas['atraso'] ?? true) ? 'bg-emerald-600' : 'bg-slate-300' }}"
              role="switch"
              aria-checked="{{ ($seccionesActivas['atraso'] ?? true) ? 'true' : 'false' }}"
              title="{{ ($seccionesActivas['atraso'] ?? true) ? 'Clic para desactivar normativa de atrasos' : 'Clic para activar normativa de atrasos' }}"
            >
              <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ ($seccionesActivas['atraso'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- Aviso cuando la sección está desactivada --}}
      @if (! ($seccionesActivas['atraso'] ?? true))
        <div class="mt-3.5 rounded-xl border border-slate-300 bg-slate-100 p-3 text-xs font-bold text-slate-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-base">🔒</span>
            <span>Esta sección de <strong>Atrasos</strong> se encuentra <strong>desactivada</strong>. No se computarán sanciones económicas por este concepto y la edición se encuentra bloqueada.</span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('atraso')"
            class="text-xs underline font-black text-slate-900 hover:text-black cursor-pointer"
          >
            Activar sección
          </button>
        </div>
      @endif

      <div class="history-table-shell mt-4 transition-all duration-300 {{ ! ($seccionesActivas['atraso'] ?? true) ? 'opacity-40 bg-slate-100/90 pointer-events-none select-none grayscale' : '' }}">
        <table class="history-table">
          <thead>
            <tr>
              <th style="min-width: 200px;">CAUSAL<br><span class="font-normal text-[11px] text-slate-400">En minutos de atraso acumulados en el mes</span></th>
              <th>RANGO MIN</th>
              <th>RANGO MAX</th>
              <th style="min-width: 220px;">SANCIÓN<br><span class="font-normal text-[11px] text-slate-400">En días de la remuneración mensual</span></th>
              <th>DÍAS</th>
              <th class="text-center" style="min-width: 100px;">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reglasAgrupadas['atraso'] as $regla)
              @php
                $isSimulado = $simuladorResultado && $simuladorResultado['regla_id'] === $regla->id;
              @endphp
              <tr wire:key="regla-row-{{ $regla->id }}" class="{{ $isSimulado ? 'bg-amber-100/80 font-bold' : ($regla->activo ? 'hover:bg-slate-50' : 'bg-slate-50/60 opacity-60') }} transition-colors">
                <td>
                  <strong class="font-black text-slate-900 block text-sm">{{ $regla->causal }}</strong>
                  @if($regla->observaciones)
                    <span class="text-[11px] text-slate-500 block">{{ $regla->observaciones }}</span>
                  @endif
                </td>
                <td class="font-mono text-xs font-bold text-slate-700">
                  {{ $regla->rango_min !== null ? $regla->rango_min . ' min' : '0' }}
                </td>
                <td class="font-mono text-xs font-bold text-slate-700">
                  {{ $regla->rango_max !== null ? $regla->rango_max . ' min' : 'O más' }}
                </td>
                <td>
                  @if ($regla->dias_sancion == 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                      ✓ {{ $regla->sancion_texto }}
                    </span>
                  @elseif ($regla->dias_sancion <= 1)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-amber-50 text-amber-900 border border-amber-200">
                      ⚠️ {{ $regla->sancion_texto }}
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 text-rose-900 border border-rose-200">
                      ⛔ {{ $regla->sancion_texto }}
                    </span>
                  @endif
                </td>
                <td>
                  <span class="font-mono font-extrabold text-xs px-2 py-0.5 rounded {{ $regla->dias_sancion > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($regla->dias_sancion, 2) }}
                  </span>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    @if($seccionesActivas['atraso'] ?? true) wire:click="openEditReglaModal({{ $regla->id }})" @endif
                    @disabled(! ($seccionesActivas['atraso'] ?? true))
                    class="h-8 px-2.5 rounded-lg bg-white text-slate-700 border border-slate-300 shadow-2xs font-extrabold text-xs inline-flex items-center gap-1 transition {{ ($seccionesActivas['atraso'] ?? true) ? 'hover:bg-slate-100 cursor-pointer hover:border-slate-400' : 'opacity-40 cursor-not-allowed' }}"
                    title="{{ ($seccionesActivas['atraso'] ?? true) ? 'Editar regla' : 'Sección desactivada' }}"
                  >
                    <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    <span>Editar</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400 py-6">No hay reglas de atraso registradas. Haz clic en "Restaurar originales" o agrega una nueva regla.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TABLA II: INASISTENCIA Y AUSENCIA EN EL PUESTO DE TRABAJO (ART. 45.II) --}}
    <div class="surface-card">
      <div class="section-head-row">
        <div>
          <span class="text-xs font-black uppercase tracking-wider text-rose-600 block">Capítulo II · Inasistencias y Ausencias</span>
          <h3 class="section-title">II. Inasistencia y Ausencia en el Puesto de Trabajo</h3>
          <p class="section-copy-sm">
            Se considera inasistencia cuando alguien del personal injustificadamente no asista todo el día o registre su asistencia pasada 30 minutos de la hora fijada para el ingreso sin justificación ante el DAF. Se considera ausencia cuando se ausente injustificadamente de su puesto.
          </p>
          <p class="text-[11px] text-slate-500 mt-1 italic">
            * Nota: Las sanciones disciplinarias engloban el día no trabajado, así como infracción administrativa. Las inasistencias y ausencias se computan por separado.
          </p>
        </div>
        <div class="flex items-center gap-2.5">
          <div class="text-right">
            <span class="text-xs font-black {{ ($seccionesActivas['inasistencia'] ?? true) ? 'text-emerald-700' : 'text-slate-400' }} block leading-tight">
              {{ ($seccionesActivas['inasistencia'] ?? true) ? '✓ Normativa Activa' : '✕ Desactivada' }}
            </span>
            <span class="text-[10px] text-slate-400 block leading-tight">
              {{ ($seccionesActivas['inasistencia'] ?? true) ? 'Aplica sanción' : 'Sin descuento (exenta)' }}
            </span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('inasistencia')"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($seccionesActivas['inasistencia'] ?? true) ? 'bg-emerald-600' : 'bg-slate-300' }}"
            role="switch"
            aria-checked="{{ ($seccionesActivas['inasistencia'] ?? true) ? 'true' : 'false' }}"
            title="{{ ($seccionesActivas['inasistencia'] ?? true) ? 'Clic para desactivar normativa de inasistencias' : 'Clic para activar normativa de inasistencias' }}"
          >
            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ ($seccionesActivas['inasistencia'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
          </button>
        </div>
      </div>

      {{-- Aviso cuando la sección está desactivada --}}
      @if (! ($seccionesActivas['inasistencia'] ?? true))
        <div class="mt-3.5 rounded-xl border border-slate-300 bg-slate-100 p-3 text-xs font-bold text-slate-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-base">🔒</span>
            <span>Esta sección de <strong>Inasistencias y Ausencias</strong> se encuentra <strong>desactivada</strong>. No se computarán sanciones económicas por este concepto y la edición se encuentra bloqueada.</span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('inasistencia')"
            class="text-xs underline font-black text-slate-900 hover:text-black cursor-pointer"
          >
            Activar sección
          </button>
        </div>
      @endif

      <div class="history-table-shell mt-4 transition-all duration-300 {{ ! ($seccionesActivas['inasistencia'] ?? true) ? 'opacity-40 bg-slate-100/90 pointer-events-none select-none grayscale' : '' }}">
        <table class="history-table">
          <thead>
            <tr>
              <th style="min-width: 250px;">CAUSAL<br><span class="font-normal text-[11px] text-slate-400">En días de inasistencia o ausencia en el puesto</span></th>
              <th style="min-width: 200px;">SANCIÓN<br><span class="font-normal text-[11px] text-slate-400">En días de la remuneración mensual</span></th>
              <th>DÍAS</th>
              <th class="text-center" style="min-width: 100px;">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reglasAgrupadas['inasistencia'] as $regla)
              <tr wire:key="regla-row-{{ $regla->id }}" class="{{ $regla->activo ? 'hover:bg-slate-50' : 'bg-slate-50/60 opacity-60' }} transition-colors">
                <td>
                  <strong class="font-black text-slate-900 block text-sm">{{ $regla->causal }}</strong>
                  @if($regla->observaciones)
                    <span class="text-[11px] text-slate-500 block">{{ $regla->observaciones }}</span>
                  @endif
                </td>
                <td>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 text-rose-900 border border-rose-200">
                    ⛔ {{ $regla->sancion_texto }}
                  </span>
                </td>
                <td>
                  <span class="font-mono font-extrabold text-xs px-2 py-0.5 rounded bg-rose-100 text-rose-800">
                    {{ number_format($regla->dias_sancion, 2) }}
                  </span>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    @if($seccionesActivas['inasistencia'] ?? true) wire:click="openEditReglaModal({{ $regla->id }})" @endif
                    @disabled(! ($seccionesActivas['inasistencia'] ?? true))
                    class="h-8 px-2.5 rounded-lg bg-white text-slate-700 border border-slate-300 shadow-2xs font-extrabold text-xs inline-flex items-center gap-1 transition {{ ($seccionesActivas['inasistencia'] ?? true) ? 'hover:bg-slate-100 cursor-pointer hover:border-slate-400' : 'opacity-40 cursor-not-allowed' }}"
                    title="{{ ($seccionesActivas['inasistencia'] ?? true) ? 'Editar regla' : 'Sección desactivada' }}"
                  >
                    <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    <span>Editar</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-slate-400 py-6">No hay reglas de inasistencia configuradas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TABLA III: OMISIÓN EN EL REGISTRO DE ASISTENCIA (ART. 45.III) --}}
    <div class="surface-card">
      <div class="section-head-row">
        <div>
          <span class="text-xs font-black uppercase tracking-wider text-amber-600 block">Capítulo III · Omisiones de Marcación</span>
          <h3 class="section-title">III. Omisión en el Registro de Asistencia</h3>
          <p class="section-copy-sm">
            El registro de asistencia en los horarios de ingreso y salida es obligatorio y personal. Toda omisión debe ser regularizada mediante el llenado del formulario respectivo. De no regularizarse, se aplican las siguientes sanciones económicas:
          </p>
        </div>
        <div class="flex items-center gap-2.5">
          <div class="text-right">
            <span class="text-xs font-black {{ ($seccionesActivas['omision'] ?? true) ? 'text-emerald-700' : 'text-slate-400' }} block leading-tight">
              {{ ($seccionesActivas['omision'] ?? true) ? '✓ Normativa Activa' : '✕ Desactivada' }}
            </span>
            <span class="text-[10px] text-slate-400 block leading-tight">
              {{ ($seccionesActivas['omision'] ?? true) ? 'Aplica sanción' : 'Sin descuento (exenta)' }}
            </span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('omision')"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($seccionesActivas['omision'] ?? true) ? 'bg-emerald-600' : 'bg-slate-300' }}"
            role="switch"
            aria-checked="{{ ($seccionesActivas['omision'] ?? true) ? 'true' : 'false' }}"
            title="{{ ($seccionesActivas['omision'] ?? true) ? 'Clic para desactivar normativa de omisiones' : 'Clic para activar normativa de omisiones' }}"
          >
            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ ($seccionesActivas['omision'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
          </button>
        </div>
      </div>

      {{-- Aviso cuando la sección está desactivada --}}
      @if (! ($seccionesActivas['omision'] ?? true))
        <div class="mt-3.5 rounded-xl border border-slate-300 bg-slate-100 p-3 text-xs font-bold text-slate-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-base">🔒</span>
            <span>Esta sección de <strong>Omisiones de Marcación</strong> se encuentra <strong>desactivada</strong>. No se computarán sanciones económicas por este concepto y la edición se encuentra bloqueada.</span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('omision')"
            class="text-xs underline font-black text-slate-900 hover:text-black cursor-pointer"
          >
            Activar sección
          </button>
        </div>
      @endif

      <div class="history-table-shell mt-4 transition-all duration-300 {{ ! ($seccionesActivas['omision'] ?? true) ? 'opacity-40 bg-slate-100/90 pointer-events-none select-none grayscale' : '' }}">
        <table class="history-table">
          <thead>
            <tr>
              <th style="min-width: 250px;">CAUSAL<br><span class="font-normal text-[11px] text-slate-400">Número de omisiones en el mes, en el Registro</span></th>
              <th style="min-width: 200px;">SANCIÓN<br><span class="font-normal text-[11px] text-slate-400">En días de la remuneración mensual</span></th>
              <th>DÍAS</th>
              <th class="text-center" style="min-width: 100px;">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reglasAgrupadas['omision'] as $regla)
              <tr wire:key="regla-row-{{ $regla->id }}" class="{{ $regla->activo ? 'hover:bg-slate-50' : 'bg-slate-50/60 opacity-60' }} transition-colors">
                <td>
                  <strong class="font-black text-slate-900 block text-sm">{{ $regla->causal }}</strong>
                  @if($regla->observaciones)
                    <span class="text-[11px] text-slate-500 block">{{ $regla->observaciones }}</span>
                  @endif
                </td>
                <td>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-amber-50 text-amber-900 border border-amber-200">
                    ⚠️ {{ $regla->sancion_texto }}
                  </span>
                </td>
                <td>
                  <span class="font-mono font-extrabold text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                    {{ number_format($regla->dias_sancion, 2) }}
                  </span>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    @if($seccionesActivas['omision'] ?? true) wire:click="openEditReglaModal({{ $regla->id }})" @endif
                    @disabled(! ($seccionesActivas['omision'] ?? true))
                    class="h-8 px-2.5 rounded-lg bg-white text-slate-700 border border-slate-300 shadow-2xs font-extrabold text-xs inline-flex items-center gap-1 transition {{ ($seccionesActivas['omision'] ?? true) ? 'hover:bg-slate-100 cursor-pointer hover:border-slate-400' : 'opacity-40 cursor-not-allowed' }}"
                    title="{{ ($seccionesActivas['omision'] ?? true) ? 'Editar regla' : 'Sección desactivada' }}"
                  >
                    <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    <span>Editar</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-slate-400 py-6">No hay reglas de omisión configuradas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TABLA IV: FALTAS GRAVÍSIMAS (ART. 48) --}}
    <div class="surface-card border-rose-200 bg-gradient-to-b from-white to-rose-50/30">
      <div class="section-head-row">
        <div>
          <span class="text-xs font-black uppercase tracking-wider text-rose-700 block">Capítulo IV · Procesos Internos Disciplinarios</span>
          <h3 class="section-title text-rose-950">IV. Faltas Gravísimas por Causas de Asistencia o Registro</h3>
          <p class="section-copy-sm text-rose-900/80">
            Conducen a <strong>Destitución inmediata con proceso interno administrativo</strong> según el Artículo 48 del reglamento:
          </p>
        </div>
        <div class="flex items-center gap-2.5">
          <div class="text-right">
            <span class="text-xs font-black {{ ($seccionesActivas['gravisima'] ?? true) ? 'text-rose-700' : 'text-slate-400' }} block leading-tight">
              {{ ($seccionesActivas['gravisima'] ?? true) ? '✓ Normativa Activa' : '✕ Desactivada' }}
            </span>
            <span class="text-[10px] text-slate-400 block leading-tight">
              {{ ($seccionesActivas['gravisima'] ?? true) ? 'Aplica destitución' : 'Sin proceso (exenta)' }}
            </span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('gravisima')"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($seccionesActivas['gravisima'] ?? true) ? 'bg-rose-600' : 'bg-slate-300' }}"
            role="switch"
            aria-checked="{{ ($seccionesActivas['gravisima'] ?? true) ? 'true' : 'false' }}"
            title="{{ ($seccionesActivas['gravisima'] ?? true) ? 'Clic para desactivar faltas gravísimas' : 'Clic para activar faltas gravísimas' }}"
          >
            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ ($seccionesActivas['gravisima'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
          </button>
        </div>
      </div>

      {{-- Aviso cuando la sección está desactivada --}}
      @if (! ($seccionesActivas['gravisima'] ?? true))
        <div class="mt-3.5 rounded-xl border border-slate-300 bg-slate-100 p-3 text-xs font-bold text-slate-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-base">🔒</span>
            <span>Esta sección de <strong>Faltas Gravísimas</strong> se encuentra <strong>desactivada</strong>. No se iniciarán procesos de destitución por este concepto y la edición se encuentra bloqueada.</span>
          </div>
          <button
            type="button"
            wire:click="toggleCategoriaActiva('gravisima')"
            class="text-xs underline font-black text-slate-900 hover:text-black cursor-pointer"
          >
            Activar sección
          </button>
        </div>
      @endif

      <div class="history-table-shell mt-4 border-rose-200 transition-all duration-300 {{ ! ($seccionesActivas['gravisima'] ?? true) ? 'opacity-40 bg-slate-100/90 pointer-events-none select-none grayscale' : '' }}">
        <table class="history-table">
          <thead>
            <tr>
              <th style="min-width: 320px;">CAUSAL</th>
              <th style="min-width: 220px;">SANCIÓN</th>
              <th class="text-center" style="min-width: 100px;">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reglasAgrupadas['gravisima'] as $regla)
              @php
                $isSimulado = $simuladorResultado && $simuladorResultado['regla_id'] === $regla->id;
              @endphp
              <tr wire:key="regla-row-{{ $regla->id }}" class="{{ $isSimulado ? 'bg-rose-200 font-bold' : ($regla->activo ? 'hover:bg-rose-50/50' : 'bg-slate-50/60 opacity-60') }} transition-colors">
                <td>
                  <strong class="font-black text-rose-950 block text-sm">{{ $regla->causal }}</strong>
                  @if($regla->observaciones)
                    <span class="text-[11px] text-rose-700 block">{{ $regla->observaciones }}</span>
                  @endif
                </td>
                <td>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-black bg-rose-600 text-white shadow-2xs">
                    ⚖️ {{ $regla->sancion_texto }}
                  </span>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    @if($seccionesActivas['gravisima'] ?? true) wire:click="openEditReglaModal({{ $regla->id }})" @endif
                    @disabled(! ($seccionesActivas['gravisima'] ?? true))
                    class="h-8 px-2.5 rounded-lg bg-white text-slate-700 border border-slate-300 shadow-2xs font-extrabold text-xs inline-flex items-center gap-1 transition {{ ($seccionesActivas['gravisima'] ?? true) ? 'hover:bg-slate-100 cursor-pointer hover:border-slate-400' : 'opacity-40 cursor-not-allowed' }}"
                    title="{{ ($seccionesActivas['gravisima'] ?? true) ? 'Editar regla' : 'Sección desactivada' }}"
                  >
                    <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    <span>Editar</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-slate-400 py-6">No hay faltas gravísimas configuradas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
