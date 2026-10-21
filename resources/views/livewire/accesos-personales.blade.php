<section class="surface-card space-y-7 p-6 sm:p-8">
  {{-- Encabezado Principal y Explicación del Módulo --}}
  <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-200/80 pb-6">
    <div class="flex items-start gap-4">
      <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <path d="M9 12h6"/>
          <path d="M12 9v6"/>
        </svg>
      </div>
      <div>
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Acceso de empleados a Mis horas</h2>
          <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 border border-blue-200">
            Auto-consulta de personal
          </span>
        </div>
        <p class="mt-1 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-3xl">
          Control de acceso para que los empleados consulten sus propias horas y descarguen sus boletas. Usuario y contraseña inicial: <strong>código biométrico</strong> (cambio obligatorio al primer ingreso).
        </p>
      </div>
    </div>

    {{-- Insignia de Jerarquía de Acceso --}}
    <div class="flex flex-col gap-1 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-3.5 text-xs text-indigo-950 backdrop-blur-sm self-start lg:self-center">
      <span class="font-bold text-indigo-900 flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        Jerarquía de evaluación:
      </span>
      <div class="flex items-center gap-1.5 font-medium text-slate-700">
        <span class="rounded bg-white px-1.5 py-0.5 shadow-2xs font-bold text-indigo-700">1° Excepción individual</span>
        <span>➜</span>
        <span class="rounded bg-white px-1.5 py-0.5 shadow-2xs font-bold text-blue-700">2° Regional</span>
        <span>➜</span>
        <span class="rounded bg-white px-1.5 py-0.5 shadow-2xs font-bold text-slate-700">3° Regla general</span>
      </div>
    </div>
  </div>

  {{-- Notificación Flash / Mensajes de Estado --}}
  @if(session('accesos-personales'))
    <div role="status" class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-emerald-900 shadow-xs transition-all">
      <div class="flex items-center gap-3">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <p class="text-sm font-semibold">{{ session('accesos-personales') }}</p>
      </div>
      <button type="button" onclick="this.closest('[role=status]').remove()" class="rounded-lg p-1.5 text-emerald-700 hover:bg-emerald-100 transition" aria-label="Cerrar">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  @endif

  {{-- Errores de Validación --}}
  @if($errors->any())
    <div role="alert" class="space-y-1 rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-rose-900 shadow-xs">
      <div class="flex items-center gap-2 font-bold text-sm text-rose-950 mb-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Revisa los siguientes avisos:
      </div>
      @foreach($errors->all() as $error)
        <p class="text-xs text-rose-800 ml-6 list-disc">• {{ $error }}</p>
      @endforeach
    </div>
  @endif

  {{-- Tarjetas KPI: Regla General Nacional y Métricas del Sistema --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    {{-- Tarjeta 1: Regla General Nacional --}}
    <div class="relative overflow-hidden rounded-2xl border {{ $reglaGeneralHabilitada ? 'border-emerald-200/90 bg-gradient-to-br from-white to-emerald-50/40' : 'border-rose-200/90 bg-gradient-to-br from-white to-rose-50/40' }} p-5 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nivel Nacional</span>
          <h3 class="text-base font-bold text-slate-800 mt-0.5">Regla General</h3>
        </div>
        @if($reglaGeneralHabilitada)
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-200 shadow-2xs">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Habilitado
          </span>
        @else
          <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800 border border-rose-200 shadow-2xs">
            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
            Bloqueado
          </span>
        @endif
      </div>

      <p class="mt-2 text-xs text-slate-600 leading-relaxed">
        @if($reglaGeneralHabilitada)
          El acceso está abierto para todas las sedes que no tengan una regla de bloqueo específica.
        @else
          El acceso está cerrado a nivel país. Solo ingresan sedes o personas con excepción habilitada.
        @endif
      </p>

      <div class="mt-4 flex flex-wrap gap-2 pt-2 border-t border-slate-100">
        <button type="button"
          class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 transition disabled:opacity-50"
          wire:click="aplicar('todos', 'habilitar')"
          wire:confirm="¿Habilitar Mis horas para todas las sucursales, conservando las excepciones?"
          wire:loading.attr="disabled">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Habilitar todas
        </button>

        <button type="button"
          class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-2xs hover:bg-rose-700 transition disabled:opacity-50"
          wire:click="aplicar('todos', 'bloquear')"
          wire:confirm="¿Bloquear la regla general? Las excepciones habilitadas seguirán vigentes."
          wire:loading.attr="disabled">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
          Bloquear regla general
        </button>
      </div>
    </div>

    {{-- Tarjeta 2: Sedes con Regla Exclusiva --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Regionales</span>
          <h3 class="text-base font-bold text-slate-800 mt-0.5">Sedes con Regla Propia</h3>
          <p class="mt-2 text-2xl font-black text-slate-800">
            {{ $metricas['total_reglas_sucursales'] }} <span class="text-xs font-normal text-slate-400">de {{ count($sucursalesPersonales) }} sedes</span>
          </p>
          <p class="text-xs text-slate-500 mt-1">Configuraciones territoriales específicas</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
            <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
            <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
            <path d="M10 6h4"/>
            <path d="M10 10h4"/>
            <path d="M10 14h4"/>
            <path d="M10 18h4"/>
          </svg>
        </div>
      </div>
    </div>

    {{-- Tarjeta 3: Excepciones Individuales --}}
    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-400">Personal</span>
          <h3 class="text-base font-bold text-slate-800 mt-0.5">Excepciones Activas</h3>
          <p class="mt-2 text-2xl font-black text-indigo-950">
            {{ $metricas['total_excepciones'] }} <span class="text-xs font-normal text-slate-400">personas</span>
          </p>
          <p class="text-xs text-slate-500 mt-1">Permisos o bloqueos individuales asignados</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- SECCIÓN DESTACADA: Control por Regional / Sucursal (HUB VISUAL) --}}
  <div class="rounded-3xl border border-slate-200/80 bg-slate-50/50 p-5 sm:p-6 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-white shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </span>
          <h3 class="text-lg font-bold text-slate-900">Control de Acceso por Regional</h3>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Haz clic en cualquier regional para filtrar su personal o utiliza los botones de acción rápida para habilitar o bloquear sedes completas.
        </p>
      </div>

      @if($sucursal !== '')
        <button type="button" wire:click="$set('sucursal', '')" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-100 transition self-start sm:self-auto">
          <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Mostrar todas las regionales
        </button>
      @endif
    </div>

    {{-- Grid de Tarjetas de Regionales --}}
    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
      @foreach($regionalesData as $reg)
        @php($estaSeleccionada = $sucursal === $reg['nombre'])
        <div wire:key="card-regional-{{ $reg['nombre'] }}"
          class="group relative flex flex-col justify-between rounded-2xl border p-4 transition-all duration-150 {{ $estaSeleccionada ? 'border-blue-500 bg-blue-50/70 shadow-md ring-2 ring-blue-400/20' : 'border-slate-200/90 bg-white hover:border-blue-300 hover:shadow-md' }}">
          
          <div>
            {{-- Fila Superior: Nombre de la Regional y Estado --}}
            <div class="flex items-start justify-between gap-2">
              <button type="button" wire:click="toggleSucursal('{{ $reg['nombre'] }}')" class="flex items-center gap-2 text-left group-hover:text-blue-700 transition">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $estaSeleccionada ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 group-hover:bg-blue-100 group-hover:text-blue-700' }} transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                </span>
                <div>
                  <h4 class="font-bold text-sm text-slate-800 {{ $estaSeleccionada ? 'text-blue-900 font-black' : '' }}">
                    {{ $reg['nombre'] }}
                  </h4>
                  <span class="text-[11px] text-slate-500">
                    {{ $reg['total_empleados'] }} {{ $reg['total_empleados'] === 1 ? 'empleado' : 'empleados' }}
                  </span>
                </div>
              </button>

              {{-- Insignia de Estado de la Regional --}}
              <div>
                @if(! $reg['es_heredado'])
                  @if($reg['habilitado'])
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-300 shadow-2xs">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                      Habilitada
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-bold text-rose-800 border border-rose-300 shadow-2xs">
                      <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                      Bloqueada
                    </span>
                  @endif
                @else
                  <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 border border-slate-200" title="Sigue la regla nacional">
                    <span class="h-1.5 w-1.5 rounded-full {{ $reg['habilitado'] ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                    Hereda ({{ $reg['habilitado'] ? 'Habilitada' : 'Bloqueada' }})
                  </span>
                @endif
              </div>
            </div>

            {{-- Detalle de Excepciones --}}
            <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500 px-1">
              @if($reg['total_excepciones'] > 0)
                <span class="inline-flex items-center gap-1 text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded-md text-[11px] border border-indigo-200">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                  {{ $reg['total_excepciones'] }} excepción(es) individual(es)
                </span>
              @else
                <span class="text-slate-400 text-[11px]">Sin excepciones individuales</span>
              @endif

              @if($estaSeleccionada)
                <span class="text-xs font-bold text-blue-600">✓ Activa</span>
              @endif
            </div>
          </div>

          {{-- Botones de Acción Rápida para la Sede --}}
          <div class="mt-3.5 flex flex-wrap items-center justify-between gap-1.5 pt-2.5 border-t border-slate-100">
            <div class="flex items-center gap-1">
              @if(! (! $reg['es_heredado'] && $reg['habilitado']))
                <button type="button"
                  wire:click="aplicarSucursalDirecto('{{ $reg['nombre'] }}', 'habilitar')"
                  wire:loading.attr="disabled"
                  class="rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-600 hover:text-white transition border border-emerald-200"
                  title="Habilitar consulta para toda la sede {{ $reg['nombre'] }}">
                  Habilitar
                </button>
              @endif

              @if(! (! $reg['es_heredado'] && ! $reg['habilitado']))
                <button type="button"
                  wire:click="aplicarSucursalDirecto('{{ $reg['nombre'] }}', 'bloquear')"
                  wire:loading.attr="disabled"
                  class="rounded-lg bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-600 hover:text-white transition border border-rose-200"
                  title="Bloquear consulta para toda la sede {{ $reg['nombre'] }}">
                  Bloquear
                </button>
              @endif

              @if(! $reg['es_heredado'])
                <button type="button"
                  wire:click="aplicarSucursalDirecto('{{ $reg['nombre'] }}', 'heredar')"
                  wire:loading.attr="disabled"
                  class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-200 transition border border-slate-200"
                  title="Eliminar regla particular y volver a heredar la regla general">
                  Heredar
                </button>
              @endif
            </div>

            <button type="button"
              wire:click="toggleSucursal('{{ $reg['nombre'] }}')"
              class="rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $estaSeleccionada ? 'bg-blue-600 text-white shadow-2xs' : 'text-blue-600 hover:bg-blue-50 border border-blue-200/60' }} transition">
              {{ $estaSeleccionada ? 'Filtrando ✓' : 'Ver personal' }}
            </button>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Banner de Sede Activa (cuando hay una regional seleccionada) --}}
    @if($sucursal !== '')
      @php($reglaSucursal = $reglasPersonales->where('tipo', 'sucursal')->firstWhere('referencia', $sucursal))
      <div class="rounded-2xl border-2 border-blue-400/50 bg-blue-50/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Sede en Administración</span>
              @if($reglaSucursal)
                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $reglaSucursal->habilitado ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                  {{ $reglaSucursal->habilitado ? 'Habilitada' : 'Bloqueada' }}
                </span>
              @else
                <span class="rounded-full bg-slate-200/80 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                  Hereda regla general ({{ $reglaGeneralHabilitada ? 'Habilitada' : 'Bloqueada' }})
                </span>
              @endif
            </div>
            <h4 class="text-base font-black text-slate-900">{{ $sucursal }}</h4>
            <p class="text-xs text-slate-600">
              Estado actual: <strong>{{ $reglaSucursal ? ($reglaSucursal->habilitado ? 'Habilitada' : 'Bloqueada') : 'Hereda la regla general' }}</strong>.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button type="button"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 transition disabled:opacity-50"
            wire:click="aplicar('sucursal', 'habilitar')"
            wire:loading.attr="disabled">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Habilitar sucursal
          </button>

          <button type="button"
            class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-rose-700 transition disabled:opacity-50"
            wire:click="aplicar('sucursal', 'bloquear')"
            wire:loading.attr="disabled">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            Bloquear sucursal
          </button>

          <button type="button"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition disabled:opacity-50"
            wire:click="aplicar('sucursal', 'heredar')"
            wire:loading.attr="disabled">
            <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Heredar regla general
          </button>
        </div>
      </div>
    @endif
  </div>

  {{-- SECCIÓN: Personal y Excepciones Individuales --}}
  <div class="space-y-4 pt-2">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </span>
          <h3 class="text-lg font-bold text-slate-900">Personal y Excepciones Individuales</h3>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Grupo de prueba o excepciones: selecciona las personas y aplica una acción individual con máxima prioridad.
        </p>
      </div>

      {{-- Contador de Selección Activa --}}
      @if(count($personas) > 0)
        <div class="inline-flex items-center gap-2 rounded-xl bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-900 border border-blue-300 animate-fadeIn">
          <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[11px] text-white">
            {{ count($personas) }}
          </span>
          <span>{{ count($personas) === 1 ? 'persona seleccionada' : count($personas) . ' personas seleccionadas' }}</span>
        </div>
      @endif
    </div>

    {{-- Barra de Filtros y Búsqueda --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs">
      {{-- Buscador con icono y debounce --}}
      <div class="relative lg:col-span-5">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </div>
        <input
          id="acceso-busqueda"
          type="text"
          wire:model.live.debounce.300ms="busqueda"
          placeholder="Buscar persona o código..."
          class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-9 pr-8 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-100">
        @if($busqueda !== '')
          <button type="button" wire:click="$set('busqueda', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        @endif
      </div>

      {{-- Filtro de Sucursal / Regional sincronizado --}}
      <div class="lg:col-span-4">
        <select
          id="acceso-sucursal"
          wire:model.live="sucursal"
          class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 px-3 text-xs sm:text-sm text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-100">
          <option value="">Todas las sucursales (filtro)</option>
          @foreach($sucursalesPersonales as $opcion)
            <option value="{{ $opcion }}">{{ $opcion }}</option>
          @endforeach
        </select>
      </div>

      {{-- Filtro por Estado de Acceso --}}
      <div class="lg:col-span-3">
        <select
          wire:model.live="filtroEstado"
          class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 px-3 text-xs sm:text-sm text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-100">
          <option value="todos">Todos los estados</option>
          <option value="habilitados">Solo Habilitados</option>
          <option value="bloqueados">Solo Bloqueados</option>
          <option value="excepciones">Con Excepción Individual</option>
        </select>
      </div>
    </div>

    {{-- Barra Flotante / Sticky de Acciones Masivas (cuando hay seleccionados) --}}
    @if(count($personas) > 0)
      <div class="sticky top-4 z-20 rounded-2xl border border-blue-300 bg-white/95 p-3.5 shadow-xl backdrop-blur-md transition-all animate-fadeIn">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-xs font-bold text-white shadow-2xs">
              {{ count($personas) }}
            </span>
            <div>
              <p class="text-xs sm:text-sm font-bold text-slate-900">
                {{ count($personas) === 1 ? '1 empleado seleccionado' : count($personas) . ' empleados seleccionados' }}
              </p>
              <p class="text-[11px] text-slate-500">Aplica una acción directa a este grupo de personas:</p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 transition disabled:opacity-50"
              wire:click="aplicar('persona', 'habilitar')"
              wire:loading.attr="disabled">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Habilitar seleccionados
            </button>

            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-rose-700 transition disabled:opacity-50"
              wire:click="aplicar('persona', 'bloquear')"
              wire:loading.attr="disabled">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
              Bloquear seleccionados
            </button>

            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition disabled:opacity-50"
              wire:click="aplicar('persona', 'heredar')"
              wire:loading.attr="disabled">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
              Quitar excepción individual
            </button>

            <button type="button"
              wire:click="deseleccionarTodos"
              class="rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition"
              title="Deseleccionar todos">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>
      </div>
    @endif

    {{-- Tabla Moderna de Empleados y Accesos --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-slate-200/90 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
              <th scope="col" class="w-12 p-3.5 text-center">
                @php($paginaIds = $empleadosPersonales->pluck('id')->all())
                @php($todosSeleccionados = !empty($paginaIds) && empty(array_diff($paginaIds, $personas)))
                <input
                  type="checkbox"
                  wire:click="seleccionarTodosPagina({{ json_encode($paginaIds) }})"
                  {{ $todosSeleccionados ? 'checked' : '' }}
                  class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition cursor-pointer"
                  title="Seleccionar o deseleccionar toda la página actual">
              </th>
              <th scope="col" class="p-3.5">Persona / Código</th>
              <th scope="col" class="p-3.5">Sucursal / Regional</th>
              <th scope="col" class="p-3.5">Acceso efectivo</th>
              <th scope="col" class="p-3.5">Contraseña / Cuenta</th>
              <th scope="col" class="p-3.5 text-right">Acción Rápida</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($empleadosPersonales as $persona)
              @php($reglaPersona = $reglasPersonales->where('tipo', 'persona')->firstWhere('referencia', (string) $persona->id))
              @php($reglaSucursalEmp = $reglasPersonales->where('tipo', 'sucursal')->firstWhere('referencia', \App\Support\SucursalNormalizer::canonicalLabel($persona->sucursal)))
              @php($habilitadoEfectivo = $accesoService->habilitado($persona))
              @php($estaSeleccionado = in_array($persona->id, $personas))

              <tr wire:key="acceso-persona-{{ $persona->id }}"
                class="transition-colors hover:bg-slate-50/70 {{ $estaSeleccionado ? 'bg-blue-50/40 border-l-4 border-l-blue-600' : '' }}">
                
                {{-- Columna Checkbox --}}
                <td class="p-3.5 text-center">
                  <input
                    type="checkbox"
                    wire:model.live="personas"
                    value="{{ $persona->id }}"
                    aria-label="Seleccionar {{ $persona->nombre_completo }}"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition cursor-pointer">
                </td>

                {{-- Columna Persona / Código Biométrico --}}
                <td class="p-3.5">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-slate-100 to-slate-200 text-xs font-bold text-slate-700 border border-slate-300/60 shadow-2xs">
                      {{ strtoupper(substr($persona->nombre ?? 'E', 0, 1) . substr($persona->apellido ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 leading-snug">
                        {{ $persona->nombre_completo }}
                      </div>
                      <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-600 border border-slate-200">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                          </svg>
                          {{ $persona->codigo_biometrico }}
                        </span>
                        @if($persona->area)
                          <span class="text-[11px] text-slate-400">· {{ $persona->area }}</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </td>

                {{-- Columna Regional / Sucursal --}}
                <td class="p-3.5">
                  <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 border border-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                      <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $persona->sucursal }}
                  </span>
                </td>

                {{-- Columna Acceso Efectivo e Indicador de Fuente --}}
                <td class="p-3.5">
                  <div class="flex flex-col items-start gap-1">
                    @if($habilitadoEfectivo)
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-300 shadow-2xs">
                        <svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Habilitado
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 border border-rose-300 shadow-2xs">
                        <svg class="h-3.5 w-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        Bloqueado
                      </span>
                    @endif

                    {{-- Origen de la regla --}}
                    @if($reglaPersona)
                      <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-1.5 py-0.5 text-[11px] font-bold text-indigo-700 border border-indigo-200">
                        ⭐ Excepción individual
                      </span>
                    @elseif($reglaSucursalEmp)
                      <span class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-1.5 py-0.5 text-[11px] font-medium text-sky-700 border border-sky-200">
                        🏢 Regla de sede
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-500 border border-slate-200">
                        🌐 Regla general
                      </span>
                    @endif
                  </div>
                </td>

                {{-- Columna Contraseña / Estado de Cuenta --}}
                <td class="p-3.5">
                  @if($persona->user?->acceso_personal)
                    <div class="flex flex-col items-start gap-1">
                      @if($persona->user->must_change_password)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 border border-amber-200">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                          Debe cambiarla
                        </span>
                      @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-800 border border-emerald-200">
                          <svg class="h-3 w-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                          Personalizada
                        </span>
                      @endif

                      <button
                        type="button"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition"
                        wire:click="restablecer({{ $persona->id }})"
                        wire:confirm="¿Restablecer la contraseña al código biométrico y exigir un nuevo cambio?"
                        wire:loading.attr="disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>
                        </svg>
                        Restablecer
                      </button>
                    </div>
                  @elseif($persona->user)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800 border border-blue-200">
                      🛡️ Cuenta de RRHH existente
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 border border-slate-200">
                      🔒 Inicial: código biométrico
                    </span>
                  @endif
                </td>

                {{-- Columna Acciones Rápidas Individuales --}}
                <td class="p-3.5 text-right">
                  <div class="inline-flex items-center justify-end gap-1.5">
                    @if(! $habilitadoEfectivo)
                      <button
                        type="button"
                        wire:click="aplicarIndividual({{ $persona->id }}, 'habilitar')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1 rounded-xl bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-600 hover:text-white transition border border-emerald-200 shadow-2xs"
                        title="Habilitar acceso individual a esta persona">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Habilitar
                      </button>
                    @else
                      <button
                        type="button"
                        wire:click="aplicarIndividual({{ $persona->id }}, 'bloquear')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1 rounded-xl bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 hover:bg-rose-600 hover:text-white transition border border-rose-200 shadow-2xs"
                        title="Bloquear acceso individual a esta persona">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        Bloquear
                      </button>
                    @endif

                    @if($reglaPersona)
                      <button
                        type="button"
                        wire:click="aplicarIndividual({{ $persona->id }}, 'heredar')"
                        wire:loading.attr="disabled"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition shadow-2xs"
                        title="Quitar excepción individual y volver a heredar la regla regional o general">
                        Heredar
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="p-8 text-center">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                  </div>
                  <h4 class="font-bold text-slate-800 text-base">No se encontraron empleados</h4>
                  <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                    No hay resultados que coincidan con la búsqueda o con los filtros seleccionados.
                  </p>
                  <button type="button" wire:click="limpiarFiltros" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    Restablecer filtros
                  </button>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Paginación --}}
    <div class="pt-2">
      {{ $empleadosPersonales->links() }}
    </div>
  </div>
</section>
