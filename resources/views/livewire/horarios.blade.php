<div class="page-stack" @bolivia-department-clicked.window="$wire.selectSucursal($event.detail.name || $event.detail.key)">
  @if ($showSucursalEmployeesModal)
    <div class="app-modal-backdrop" wire:click="closeSucursalEmployeesModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeSucursalEmployeesModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
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
                <th>Codigo</th>
                <th>Area</th>
                <th>Sucursal</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($sucursalEmployees as $empleado)
                <tr>
                  <td>{{ $empleado->nombre_completo }}</td>
                  <td>{{ $empleado->codigo_biometrico ?: 'Sin asignar' }}</td>
                  <td>{{ $empleado->area ?: 'Sin area' }}</td>
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

  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Horario regional</p>
            <h3 class="section-title app-modal-title">Actualizar entrada y salida</h3>
            <p class="section-copy-sm">Configura el horario general que se aplicará a todo el personal de la sucursal seleccionada.</p>
          </div>
        </div>

        <form wire:submit="saveHorario" class="mt-8 grid gap-5 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label">Sucursal</label>
            <input type="text" value="{{ $editingSucursal }}" class="form-input font-bold" disabled>
          </div>
          <div>
            <label class="form-label">Hora de entrada</label>
            <input type="time" wire:model="editHoraEntrada" class="form-input">
            @error('editHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de salida</label>
            <input type="time" wire:model="editHoraSalida" class="form-input">
            @error('editHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar horario</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- 1. FILA SUPERIOR: 3 TARJETAS LADO A LADO (HORA DE ENTRADA, HORA DE SALIDA, TOLERANCIA) --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    
    {{-- Tarjeta 1: HORA DE ENTRADA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-4 transition hover:shadow-md">
      <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-blue-100/70 text-[#1e60c6]">
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
      </div>
      <div>
        <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE ENTRADA</p>
        <div class="mt-1 flex items-baseline gap-1.5">
          <span class="text-3xl font-black text-[#1e60c6] tracking-tight">{{ $generalHoraEntrada }}</span>
          <span class="text-xs font-bold text-[#1e60c6] uppercase">AM</span>
        </div>
      </div>
    </div>

    {{-- Tarjeta 2: HORA DE SALIDA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-4 transition hover:shadow-md">
      <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100/70 text-emerald-600">
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </div>
      <div>
        <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE SALIDA</p>
        <div class="mt-1 flex items-baseline gap-1.5">
          <span class="text-3xl font-black text-emerald-600 tracking-tight">{{ $generalHoraSalida }}</span>
          <span class="text-xs font-bold text-emerald-600 uppercase">PM</span>
        </div>
      </div>
    </div>

    {{-- Tarjeta 3: TOLERANCIA --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-4 transition hover:shadow-md">
      <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-amber-100/70 text-amber-600">
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div>
        <p class="text-xs font-black uppercase tracking-wider text-slate-800">TOLERANCIA</p>
        <div class="mt-1 flex items-baseline gap-1.5">
          <span class="text-3xl font-black text-amber-600 tracking-tight">{{ $globalTolerancia }}</span>
          <span class="text-xs font-bold text-amber-600">min</span>
        </div>
      </div>
    </div>

  </div>

  {{-- 2. SECCIÓN PRINCIPAL: MAPA DE BOLIVIA (IZQUIERDA) + 3 TARJETAS HORIZONTALES (DERECHA) --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch mt-4">
    
    {{-- COLUMNA IZQUIERDA: MAPA DE BOLIVIA --}}
    <div wire:ignore class="flex flex-col">
      <article class="surface-card !p-6 border border-slate-200/80 bg-white rounded-2xl shadow-xs flex flex-col items-center justify-between w-full h-full">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 text-center mb-2">MAPA DE BOLIVIA</h3>
        
        <div class="w-full flex-1 flex items-center justify-center p-1" data-bolivia-map-root data-active-department-key="{{ $activeSucursalData?->key ?? \Illuminate\Support\Str::slug($activeSucursalData?->sucursal ?? '') }}">
          <div class="bolivia-map-canvas !h-[420px] w-full" data-bolivia-map-canvas aria-label="Mapa interactivo de Bolivia por departamentos"></div>
          <script type="application/json" data-departments-json>@json($departmentStats ?? [])</script>
        </div>
      </article>
    </div>

    {{-- COLUMNA DERECHA: 3 TARJETAS DE DETALLE (ENTRADA, SALIDA, PERSONAL REGISTRADO) --}}
    <div class="flex flex-col justify-between gap-4">
      
      @php
        $displayEntrada = $activeSucursalData?->hora_entrada ?? $generalHoraEntrada;
        $displaySalida = $activeSucursalData?->hora_salida ?? $generalHoraSalida;
        $displayPersonal = $activeSucursalData?->empleados ?? array_sum(array_column($departmentStats ?? [], 'employees'));
      @endphp

      @if ($activeSucursalData)
        <div class="flex items-center justify-between px-1">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ciudad seleccionada:</span>
          <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-extrabold text-[#1e60c6]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $activeSucursalData->sucursal }}
          </span>
        </div>
      @endif

      {{-- 1. HORA DE ENTRADA --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-5 transition hover:shadow-md">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-blue-100/70 text-[#1e60c6]">
          <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 14 14"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE ENTRADA</p>
          <div class="mt-1 flex items-baseline gap-1.5">
            <span class="text-3xl font-black text-[#1e60c6] tracking-tight">{{ $displayEntrada }}</span>
            <span class="text-xs font-bold text-[#1e60c6] uppercase">AM</span>
          </div>
        </div>
      </div>

      {{-- 2. HORA DE SALIDA --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-5 transition hover:shadow-md">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100/70 text-emerald-600">
          <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 14 14"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-black uppercase tracking-wider text-slate-800">HORA DE SALIDA</p>
          <div class="mt-1 flex items-baseline gap-1.5">
            <span class="text-3xl font-black text-emerald-600 tracking-tight">{{ $displaySalida }}</span>
            <span class="text-xs font-bold text-emerald-600 uppercase">PM</span>
          </div>
        </div>
      </div>

      {{-- 3. PERSONAL REGISTRADO --}}
      <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center gap-5 transition hover:shadow-md">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-purple-100/70 text-purple-700">
          <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-black uppercase tracking-wider text-slate-800">PERSONAL REGISTRADO</p>
          <div class="mt-1 flex items-baseline gap-1.5">
            <span class="text-3xl font-black text-purple-700 tracking-tight">{{ $displayPersonal }}</span>
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
            <span>Modificar horario</span>
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
