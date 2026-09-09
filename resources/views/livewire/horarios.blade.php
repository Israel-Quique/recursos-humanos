<div class="page-stack" @bolivia-department-clicked.window="$wire.selectSucursal($event.detail.name || $event.detail.key)">
  {{-- ALERTAS DE ESTADO --}}
  @if (session()->has('status'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base font-black text-emerald-600">✓</span>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
    </div>
  @endif

  {{-- MODAL PERSONAL POR SUCURSAL --}}
  @if ($showSucursalEmployeesModal)
    <div class="app-modal-backdrop" wire:click="closeSucursalEmployeesModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeSucursalEmployeesModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Personal por sucursal</p>
            <h3 class="section-title app-modal-title">{{ $selectedSucursal }}</h3>
            <p class="section-copy-sm">Listado completo del personal asignado a esta sucursal.</p>
          </div>
        </div>

        <div class="history-table-shell mt-8">
          <table class="history-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Código</th>
                <th>Área</th>
                <th>Sucursal</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($sucursalEmployees as $empleado)
                <tr>
                  <td>{{ $empleado->nombre_completo }}</td>
                  <td>{{ $empleado->codigo_biometrico ?: 'Sin asignar' }}</td>
                  <td>{{ $empleado->area ?: 'Sin área' }}</td>
                  <td>{{ $empleado->sucursal }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-slate-400">No hay personal registrado en esta sucursal.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  {{-- MODAL EDICIÓN HORARIO POR SUCURSAL --}}
  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop style="max-width: 38rem;">
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Horario regional y tolerancias</p>
            <h3 class="section-title app-modal-title">Actualizar horario de {{ $editingSucursal }}</h3>
            <p class="section-copy-sm">Ajusta los horarios y tolerancias diaria y mensual acumulativa para el personal de esta regional.</p>
          </div>
        </div>

        <form wire:submit="saveHorario" class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label">Sucursal / Regional</label>
            <input type="text" value="{{ $editingSucursal }}" class="form-input font-bold bg-slate-100 text-slate-800" disabled>
          </div>

          <div>
            <label class="form-label">Hora de entrada *</label>
            <input type="time" wire:model="editHoraEntrada" class="form-input font-bold">
            @error('editHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Límite tolerancia diaria *</label>
            <input type="time" wire:model="editHoraTolerancia" class="form-input font-bold">
            <p class="text-[11px] text-slate-500 mt-1">Los atrasos se computan a partir de esta hora.</p>
            @error('editHoraTolerancia') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Hora de salida</label>
            <input type="time" wire:model="editHoraSalida" class="form-input font-bold">
            @error('editHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Tolerancia mensual acumulativa *</label>
            <div class="relative">
              <input type="number" wire:model="editToleranciaMensual" min="0" max="600" class="form-input font-bold pr-16" placeholder="Ej: 35">
              <span class="absolute right-3 top-2.5 text-xs font-black text-slate-400">min/mes</span>
            </div>
            @error('editToleranciaMensual') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 rounded-xl border border-amber-200/80 bg-amber-50/70 p-3.5 text-xs text-amber-900 leading-relaxed">
            <div class="flex items-start gap-2">
              <span class="text-base">💡</span>
              <div>
                <strong class="font-extrabold block">Atención para casos de contingencia:</strong>
                Si la regional {{ $editingSucursal }} sufre paros cívicos, desastres naturales o problemas de transporte, puedes incrementar los minutos de tolerancia mensual acumulativa para que los funcionarios no sean penalizados injustamente.
              </div>
            </div>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeEditModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios regional</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL EDICIÓN GLOBAL ESTÁNDAR --}}
  @if ($showGlobalModal)
    <div class="app-modal-backdrop" wire:click="closeGlobalModal">
      <div class="app-modal-card" x-on:click.stop style="max-width: 40rem;">
        <button type="button" wire:click="closeGlobalModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-[#1e60c6] border border-blue-200 uppercase tracking-wider mb-2">
              🌐 Estándar Institucional
            </span>
            <h3 class="section-title app-modal-title">Configuración global de horarios y tolerancias</h3>
            <p class="section-copy-sm">Define el estándar general que se aplicará por defecto en toda la institución.</p>
          </div>
        </div>

        <form wire:submit="saveGlobalSettings" class="mt-6 grid gap-4 md:grid-cols-2">
          <div>
            <label class="form-label">Hora de entrada estándar *</label>
            <input type="time" wire:model.live="globalEditHoraEntrada" class="form-input font-bold">
            @error('globalEditHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Tolerancia diaria (minutos de gracia) *</label>
            <div class="relative">
              <input type="number" wire:model.live="globalEditToleranciaDiaria" min="0" max="120" class="form-input font-bold pr-16" placeholder="Ej: 5">
              <span class="absolute right-3 top-2.5 text-xs font-black text-slate-400">minutos</span>
            </div>
            @error('globalEditToleranciaDiaria') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Límite tolerancia diaria (calculado) *</label>
            <input type="time" wire:model="globalEditHoraTolerancia" class="form-input font-bold bg-slate-50">
            <p class="text-[11px] text-slate-500 mt-1">Hora límite para marcar sin contar minutos de retraso.</p>
            @error('globalEditHoraTolerancia') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Hora de salida estándar</label>
            <input type="time" wire:model="globalEditHoraSalida" class="form-input font-bold">
            @error('globalEditHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Tolerancia mensual acumulativa estándar *</label>
            <div class="relative">
              <input type="number" wire:model="globalEditToleranciaMensual" min="0" max="600" class="form-input font-bold pr-20" placeholder="Ej: 35">
              <span class="absolute right-3 top-2.5 text-xs font-black text-slate-400">min/mes</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Total de minutos de atraso que un empleado puede acumular al mes antes de generar descuentos o penalizaciones.</p>
            @error('globalEditToleranciaMensual') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 flex items-center justify-between p-4 bg-indigo-50/70 border border-indigo-200/80 rounded-xl">
            <div class="pr-3">
              <span class="text-xs font-black text-indigo-950 block">Sincronizar con todas las sucursales</span>
              <span class="text-[11px] text-indigo-700 font-medium">Actualiza y unifica todas las regionales registradas a este horario y tolerancias estándar.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
              <input type="checkbox" wire:model="aplicarATodasLasSucursales" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeGlobalModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar estándar global</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- BARRA DE CONTROL DE ÁMBITO Y EDICIÓN GLOBAL --}}
  <div class="mb-2 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
    <div class="flex items-center gap-2 flex-wrap">
      <span class="text-xs font-black uppercase tracking-wider text-slate-500">Visualizando horario de:</span>

      {{-- Selector de sucursal rápida --}}
      <div class="inline-flex items-center rounded-xl bg-white border border-slate-200 p-1 shadow-2xs">
        <button
          type="button"
          wire:click="setTopCardsScope('sucursal')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black transition cursor-pointer {{ $topCardsData->is_sucursal ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
        >
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          <span>Sucursal: {{ $activeSucursalData->sucursal ?? 'Seleccionada' }}</span>
        </button>

        <button
          type="button"
          wire:click="setTopCardsScope('global')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black transition cursor-pointer {{ !$topCardsData->is_sucursal ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
        >
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          <span>Estándar Global</span>
        </button>
      </div>

      {{-- Dropdown para cambiar de sucursal directamente --}}
      <select
        wire:change="selectSucursal($event.target.value)"
        class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-extrabold text-slate-700 shadow-2xs cursor-pointer hover:border-slate-300"
      >
        @foreach ($allSucursales as $sucursalOption)
          <option value="{{ $sucursalOption }}" @selected(($activeSucursalData?->sucursal ?? '') === $sucursalOption)>
            Regional {{ $sucursalOption }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Botón para abrir modal de Edición Global --}}
    <button
      type="button"
      wire:click="openGlobalModal"
      class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white shadow-xs hover:bg-slate-800 transition cursor-pointer"
      title="Configurar el horario y tolerancias estándar para todo el país"
    >
      <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
      <span>⚙️ Edición Global / Estándar</span>
    </button>
  </div>

  {{-- 1. FILA SUPERIOR: 4 TARJETAS DINÁMICAS LADO A LADO (ENTRADA, TOLERANCIA DIARIA, SALIDA, TOLERANCIA MENSUAL) --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    {{-- Tarjeta 1: HORA DE ENTRADA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-3.5 transition hover:shadow-md">
      <div class="flex items-center gap-3.5">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-blue-100/70 text-[#1e60c6]">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-black uppercase tracking-wider text-slate-700">HORA DE ENTRADA</p>
          <div class="mt-0.5 flex items-baseline gap-1">
            <span class="text-2xl font-black text-[#1e60c6] tracking-tight">{{ $topCardsData->hora_entrada }}</span>
            <span class="text-[10px] font-bold text-[#1e60c6] uppercase">AM</span>
          </div>
        </div>
      </div>
      <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $topCardsData->is_sucursal ? 'bg-blue-50 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
        {{ $topCardsData->scope_label }}
      </span>
    </div>

    {{-- Tarjeta 2: TOLERANCIA DIARIA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-3.5 transition hover:shadow-md">
      <div class="flex items-center gap-3.5">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-cyan-100/70 text-cyan-600">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 14 14"/>
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-black uppercase tracking-wider text-slate-700">TOLERANCIA DIARIA</p>
          <div class="mt-0.5 flex items-baseline gap-1">
            <span class="text-2xl font-black text-cyan-700 tracking-tight">{{ $topCardsData->hora_tolerancia }}</span>
            <span class="text-[10px] font-bold text-cyan-600">(+{{ $topCardsData->tolerancia_diaria_min }}m)</span>
          </div>
        </div>
      </div>
      <span class="text-[10px] font-bold text-slate-400">
        Gracia
      </span>
    </div>

    {{-- Tarjeta 3: HORA DE SALIDA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-3.5 transition hover:shadow-md">
      <div class="flex items-center gap-3.5">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-emerald-100/70 text-emerald-600">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-black uppercase tracking-wider text-slate-700">HORA DE SALIDA</p>
          <div class="mt-0.5 flex items-baseline gap-1">
            <span class="text-2xl font-black text-emerald-600 tracking-tight">{{ $topCardsData->hora_salida }}</span>
            <span class="text-[10px] font-bold text-emerald-600 uppercase">PM</span>
          </div>
        </div>
      </div>
      <span class="text-[10px] font-bold text-slate-400">
        Jornada
      </span>
    </div>

    {{-- Tarjeta 4: TOLERANCIA MENSUAL ACUMULATIVA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-3.5 transition hover:shadow-md">
      <div class="flex items-center gap-3.5">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-amber-100/70 text-amber-600">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-black uppercase tracking-wider text-slate-700">TOL. MENSUAL</p>
          <div class="mt-0.5 flex items-baseline gap-1">
            <span class="text-2xl font-black text-amber-600 tracking-tight">{{ $topCardsData->tolerancia_mensual }}</span>
            <span class="text-[10px] font-bold text-amber-600">min/mes</span>
          </div>
        </div>
      </div>
      @if ($topCardsData->is_personalizada)
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs" title="Tolerancia personalizada para esta sucursal">
          ⚡ Contingencia
        </span>
      @else
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 text-slate-700 border border-slate-200" title="Aplica la tolerancia mensual estándar">
          Estándar
        </span>
      @endif
    </div>

  </div>

  {{-- 2. SECCIÓN PRINCIPAL: MAPA DE BOLIVIA (IZQUIERDA) + TARJETAS DE DETALLE (DERECHA) --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch mt-4">
    
    {{-- COLUMNA IZQUIERDA: MAPA DE BOLIVIA --}}
    <div wire:ignore class="flex flex-col">
      <article class="surface-card !p-6 border border-slate-200/80 bg-white rounded-2xl shadow-xs flex flex-col items-center justify-between w-full h-full">
        <div class="w-full flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
          <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">MAPA DE BOLIVIA POR REGIONALES</h3>
          <span class="text-[11px] font-bold text-slate-400">Haz clic en una regional</span>
        </div>
        
        <div class="w-full flex-1 flex items-center justify-center p-1" data-bolivia-map-root data-active-department-key="{{ $activeSucursalData?->key ?? \Illuminate\Support\Str::slug($activeSucursalData?->sucursal ?? '') }}">
          <div class="bolivia-map-canvas !h-[420px] w-full" data-bolivia-map-canvas aria-label="Mapa interactivo de Bolivia por departamentos"></div>
          <script type="application/json" data-departments-json>@json($departmentStats ?? [])</script>
        </div>
      </article>
    </div>

    {{-- COLUMNA DERECHA: TARJETAS DE DETALLE DE LA SUCURSAL SELECCIONADA --}}
    <div class="flex flex-col justify-between gap-3.5">
      
      @php
        $displayEntrada = $activeSucursalData?->hora_entrada ?? $generalHoraEntrada;
        $displayTolerancia = $activeSucursalData?->hora_tolerancia ?? $generalHoraTolerancia;
        $displayToleranciaMin = $activeSucursalData?->tolerancia_minutos ?? $globalToleranciaDiaria;
        $displaySalida = $activeSucursalData?->hora_salida ?? $generalHoraSalida;
        $displayToleranciaMes = $activeSucursalData?->tolerancia_mensual ?? $globalTolerancia;
        $isMesPersonalizado = $activeSucursalData?->tolerancia_mensual_personalizada ?? false;
        $displayPersonal = $activeSucursalData?->empleados ?? array_sum(array_column($departmentStats ?? [], 'employees'));
      @endphp

      @if ($activeSucursalData)
        <div class="flex items-center justify-between px-1">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Regional en visualización:</span>
          <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-extrabold text-[#1e60c6]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $activeSucursalData->sucursal }}
          </span>
        </div>
      @endif

      {{-- 1. HORA DE ENTRADA Y TOLERANCIA DIARIA --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-4 transition hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-blue-100/70 text-[#1e60c6]">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 14 14"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE ENTRADA</p>
            <div class="mt-0.5 flex items-baseline gap-1.5">
              <span class="text-2xl font-black text-[#1e60c6] tracking-tight">{{ $displayEntrada }}</span>
              <span class="text-xs font-bold text-[#1e60c6] uppercase">AM</span>
            </div>
          </div>
        </div>
        <div class="text-right border-l border-slate-100 pl-4">
          <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Tolerancia diaria</span>
          <p class="text-sm font-black text-cyan-700 mt-0.5">{{ $displayTolerancia }} <span class="text-[11px] font-bold text-slate-500">(+{{ $displayToleranciaMin }}m)</span></p>
          <span class="text-[10px] text-slate-400">Atraso desde min {{ substr($displayTolerancia, 3, 2) }}</span>
        </div>
      </div>

      {{-- 2. HORA DE SALIDA --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center gap-4 transition hover:shadow-md">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-emerald-100/70 text-emerald-600">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 14 14"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE SALIDA</p>
          <div class="mt-0.5 flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-emerald-600 tracking-tight">{{ $displaySalida }}</span>
            <span class="text-xs font-bold text-emerald-600 uppercase">PM</span>
          </div>
        </div>
      </div>

      {{-- 3. TOLERANCIA MENSUAL ACUMULATIVA REGIONAL --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center justify-between gap-4 transition hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-amber-100/70 text-amber-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-black uppercase tracking-wider text-slate-800">TOLERANCIA MENSUAL ACUMULADA</p>
            <div class="mt-0.5 flex items-baseline gap-1.5">
              <span class="text-2xl font-black text-amber-600 tracking-tight">{{ $displayToleranciaMes }}</span>
              <span class="text-xs font-bold text-amber-600">minutos / mes</span>
            </div>
          </div>
        </div>
        <div class="text-right border-l border-slate-100 pl-4">
          @if ($isMesPersonalizado)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300">
              ⚡ Contingencia
            </span>
          @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-black bg-slate-100 text-slate-600 border border-slate-200">
              Estándar
            </span>
          @endif
        </div>
      </div>

      {{-- 4. PERSONAL REGISTRADO --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs flex items-center gap-4 transition hover:shadow-md">
        <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-purple-100/70 text-purple-700">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-black uppercase tracking-wider text-slate-800">PERSONAL REGISTRADO</p>
          <div class="mt-0.5 flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-purple-700 tracking-tight">{{ $displayPersonal }}</span>
            <span class="text-xs font-bold text-purple-700">personas</span>
          </div>
        </div>
      </div>

      {{-- Acciones de la ciudad --}}
      @if ($activeSucursalData)
        <div class="flex items-center gap-2 mt-1">
          <button
            type="button"
            wire:click="openEditModal('{{ str_replace("'", "\\'", $activeSucursalData->sucursal) }}')"
            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-[#0f67c0] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#0d59a7] transition cursor-pointer"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Modificar horario y tolerancias</span>
          </button>

          <button
            type="button"
            wire:click="openSucursalEmployeesModal('{{ str_replace("'", "\\'", $activeSucursalData->sucursal) }}')"
            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer"
          >
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Ver personal</span>
          </button>
        </div>
      @endif

    </div>

  </div>
</div>
