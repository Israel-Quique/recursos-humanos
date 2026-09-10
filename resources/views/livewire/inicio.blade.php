<div class="page-stack">
  @if (session('status_success'))
    <div
      class="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-emerald-300 bg-emerald-50/90 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-xs">
      <div class="flex items-center gap-2.5">
        <svg class="h-5 w-5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        <span>{{ session('status_success') }}</span>
      </div>
    </div>
  @endif

  @if (session('status_error'))
    <div
      class="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-rose-300 bg-rose-50/90 px-4 py-3 text-sm font-semibold text-rose-800 shadow-sm backdrop-blur-xs">
      <div class="flex items-center gap-2.5">
        <svg class="h-5 w-5 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>{{ session('status_error') }}</span>
      </div>
    </div>
  @endif

  @if (session('status'))
    <div
      class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">
      {{ session('status') }}
    </div>
  @endif

  {{-- 1. HERO BANNER DE BIENVENIDA --}}
  <article class="inicio-hero-card p-8 md:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="max-w-2xl z-10">
      <div class="flex items-center gap-2 mb-3">
        <span
          class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-extrabold uppercase tracking-wider text-amber-300 bg-black/30 border border-amber-300/30 backdrop-blur-md">
          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Panel de Inicio Oficial
        </span>
        <span class="text-xs font-semibold text-slate-200 hidden sm:inline-block">• {{ $hoy }}</span>
      </div>

      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
        ¡Bienvenido, <span class="text-amber-400 font-extrabold">{{ $user->name }}</span>!
      </h1>

      <p class="mt-3 text-sm md:text-base text-slate-100 font-medium leading-relaxed max-w-xl">
        Sistema integral de gestión de asistencia, marcaciones biométricas y recursos humanos de Correos de Bolivia.
      </p>

      <div class="mt-5 flex flex-wrap items-center gap-2.5">
        <span
          class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-4 py-2 text-xs font-bold text-white backdrop-blur-md">
          <svg class="h-4 w-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="8" r="5" />
            <path d="M20 21a8 8 0 0 0-16 0" />
          </svg>
          Rol: {{ ucfirst($user->getRoleNames()->first() ?? 'Usuario') }}
        </span>
        @if ($user->empleado?->sucursal)
          <span
            class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-4 py-2 text-xs font-bold text-white backdrop-blur-md">
            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2.5">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            Regional: {{ $user->empleado->sucursal }}
          </span>
        @endif
      </div>
    </div>

    {{-- Mini Estadísticas Rápidas del Banner --}}
    <div class="inicio-kpi-box flex flex-wrap items-center gap-4 sm:gap-6 p-4 sm:p-6 shrink-0 z-10">
      <div class="text-center px-3 sm:px-4 border-r border-white/15">
        <p class="text-2xl sm:text-4xl font-black font-mono tracking-tight text-emerald-400">
          {{ $totalEmpleadosActivos }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">Personal Activo</span>
        <span class="text-[10px] text-emerald-200/80 font-medium block">de {{ $totalEmpleadosPadron }} en padrón</span>
      </div>
      <div class="text-center px-3 sm:px-4 border-r border-white/15">
        <p class="text-2xl sm:text-4xl font-black font-mono tracking-tight text-amber-300">
          {{ $totalMarcacionesHoy }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">Marcaron Hoy</span>
        <span class="text-[10px] text-amber-200/80 font-medium block">{{ $porcentajeAsistenciaHoy }}% asistencia</span>
      </div>
      <div class="text-center px-3 sm:px-4 hidden md:block border-r border-white/15">
        <p class="text-2xl sm:text-4xl font-black font-mono tracking-tight text-emerald-300">
          {{ $totalEnPuestoHoy }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">En Puesto</span>
        <span class="text-[10px] text-emerald-200/80 font-medium block">en su agencia</span>
      </div>
      <div class="text-center px-3 sm:px-4">
        <p class="text-2xl sm:text-4xl font-black font-mono tracking-tight text-sky-300">{{ $totalSucursales }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">Sucursales</span>
        <span class="text-[10px] text-sky-200/80 font-medium block">a nivel nacional</span>
      </div>
    </div>
  </article>

  {{-- 2. SECCIÓN DE ACCESOS RÁPIDOS PRINCIPALES (3 BOTONES GRANDES HORIZONTALES) --}}
  <section class="mt-6">
    <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-200/80">
      <div>
        <p class="section-kicker">Acceso Directo</p>
        <h2 class="text-xl font-black text-slate-800 tracking-tight">Módulos de Control Principal</h2>
      </div>
      <span class="text-xs text-slate-500 font-semibold hidden sm:inline-block">Selecciona una opción para
        comenzar</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      {{-- BOTÓN 1: MARCACIONES PERSONALES --}}
      <a wire:navigate href="{{ route('personal', ['vista' => 'marcaciones']) }}"
        class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-blue h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
          </div>
          <span
            class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 text-[#0f67c0] px-3 py-1 text-xs font-black">
            Módulo 1
          </span>
        </div>

        <div class="mt-5">
          <p class="text-[11px] font-black uppercase tracking-wider text-[#0f67c0]">Control Individual</p>
          <h3 class="text-xl font-black text-slate-800 tracking-tight group-hover:text-[#0f67c0] transition">
            Marcaciones personales
          </h3>
          <p class="text-xs text-slate-500 mt-1.5 font-medium leading-relaxed">
            Consulta el detalle individual de atrasos, omisiones de marcación, faltas y horas laboradas por empleado.
          </p>
        </div>

        <div
          class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-[#0f67c0]">
          <span>Ingresar al control individual</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

      {{-- BOTÓN 2: MARCACIONES POR SUCURSAL --}}
      <a wire:navigate href="{{ route('personal', ['vista' => 'control']) }}"
        class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-emerald h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 21h18" />
              <path d="M19 21v-4" />
              <path d="M19 13v-2" />
              <path d="M19 7V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v17" />
              <path d="M9 7h2M9 11h2M9 15h2M13 7h2M13 11h2M13 15h2" />
            </svg>
          </div>
          <span
            class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1 text-xs font-black">
            Módulo 2
          </span>
        </div>

        <div class="mt-5">
          <p class="text-[11px] font-black uppercase tracking-wider text-emerald-700">Supervisión Regional</p>
          <h3 class="text-xl font-black text-slate-800 tracking-tight group-hover:text-emerald-700 transition">
            Marcaciones por sucursal
          </h3>
          <p class="text-xs text-slate-500 mt-1.5 font-medium leading-relaxed">
            Visualiza porcentajes de puntualidad, omisiones y excedentes de tolerancia clasificados por departamento.
          </p>
        </div>

        <div
          class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-emerald-700">
          <span>Ver marcaciones regionales</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

      {{-- BOTÓN 3: REPORTES --}}
      <a wire:navigate href="{{ route('reportes') }}"
        class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-purple h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
          </div>
          <span
            class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 text-purple-700 px-3 py-1 text-xs font-black">
            Módulo 3
          </span>
        </div>

        <div class="mt-5">
          <p class="text-[11px] font-black uppercase tracking-wider text-purple-700">Informes y Métricas</p>
          <h3 class="text-xl font-black text-slate-800 tracking-tight group-hover:text-purple-700 transition">
            Reportes
          </h3>
          <p class="text-xs text-slate-500 mt-1.5 font-medium leading-relaxed">
            Genera consolidados mensuales de asistencia, exporta a PDF/Excel y revisa resúmenes ejecutivos.
          </p>
        </div>

        <div
          class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-purple-700">
          <span>Generar y descargar reportes</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

    </div>
  </section>

  {{-- 3. SECCIÓN: MONITOREO DIARIO DE MARCACIONES POR SUCURSAL --}}
  <section class="mt-6">
    <article class="surface-card !p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <p class="section-kicker !mb-0">Control Diario en Tiempo Real</p>
          </div>
          <h2 class="text-xl font-black text-slate-800 tracking-tight mt-0.5">
            Marcaciones de Hoy por Sucursal
          </h2>
          <p class="text-xs text-slate-500 mt-1 font-medium">
            Seguimiento de asistencia del día entre todas las regionales activas ({{ $totalMarcacionesHoy }} de {{ $totalEmpleadosActivos }} colaboradores marcaron hoy).
          </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <a wire:navigate href="{{ route('personal', ['vista' => 'control']) }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
            <svg class="h-4 w-4 text-[#0f67c0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18" /><path d="M19 21v-4" /><path d="M19 13v-2" /><path d="M19 7V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v17" /></svg>
            <span>Ver reporte de control</span>
          </a>
          <a wire:navigate href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
            <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            <span>Mapa de Bolivia</span>
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
        @foreach ($departmentStats as $deptKey => $dept)
          @php
            $empCount = (int) ($dept['employees'] ?? 0);
            $markedCount = (int) ($dept['marked'] ?? 0);
            $workingCount = (int) ($dept['working'] ?? 0);
            $pct = $empCount > 0 ? (int) round(($markedCount / $empCount) * 100) : 0;
            $hasActivity = $markedCount > 0;
          @endphp
          <div class="rounded-2xl border p-4 transition-all duration-200 {{ $hasActivity ? 'border-slate-200 bg-white shadow-xs hover:border-blue-300 hover:shadow-sm' : 'border-slate-200/70 bg-slate-50/50' }}">
            <div class="flex items-start justify-between gap-3 mb-2.5">
              <div>
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 block">{{ $dept['branch'] ?? 'Regional' }}</span>
                <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $dept['name'] }}</h4>
              </div>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $pct >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($pct > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500 border border-slate-200') }}">
                {{ $pct }}%
              </span>
            </div>

            {{-- Barra de progreso --}}
            <div class="w-full bg-slate-100 rounded-full h-1.5 mb-3 overflow-hidden">
              <div class="h-1.5 rounded-full transition-all duration-500 {{ $pct >= 80 ? 'bg-emerald-500' : ($pct > 0 ? 'bg-amber-500' : 'bg-slate-300') }}" style="width: {{ min(100, max(0, $pct)) }}%"></div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-center">
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Marcaron</span>
                <strong class="text-sm font-black font-mono {{ $markedCount > 0 ? 'text-blue-700' : 'text-slate-400' }}">{{ $markedCount }}</strong>
              </div>
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">En puesto</span>
                <strong class="text-sm font-black font-mono {{ $workingCount > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $workingCount }}</strong>
              </div>
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Activos</span>
                <strong class="text-sm font-black font-mono text-slate-700">{{ $empCount }}</strong>
              </div>
            </div>

            <div class="mt-3 pt-2 border-t border-slate-100/70 flex items-center justify-between text-[11px] text-slate-400">
              <span class="truncate max-w-[170px]" title="{{ $dept['sync_label'] ?? '' }}">{{ $dept['sync_label'] ?? 'Sin sync' }}</span>
              <a wire:navigate href="{{ route('personal', ['vista' => 'control']) }}" class="text-[#0f67c0] font-bold hover:underline shrink-0">
                Detalle →
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </article>
  </section>

  {{-- 4. ENLACES SECUNDARIOS Y OTRAS HERRAMIENTAS --}}
  <section class="mt-6">
    <div class="surface-card !p-5">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h4 class="text-sm font-bold text-slate-800">Otras herramientas operativas</h4>
          <p class="text-xs text-slate-500">Accede rápidamente a la configuración general o sincroniza con el biométrico físico</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <a wire:navigate href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Calendario laboral</span>
          </a>

          @can('importar biometria')
            <div class="inline-flex items-center gap-2">
              <button type="button" wire:click="sincronizarBiometrico(true)" wire:loading.attr="disabled"
                wire:target="sincronizarBiometrico"
                title="Conecta con los relojes biométricos, extrae todas las marcaciones recientes y actualiza las entradas y salidas sin perder fidelidad"
                class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-500/10 px-4 py-2.5 text-xs font-bold text-amber-900 hover:bg-amber-500/20 hover:border-amber-400 transition shadow-xs disabled:opacity-60 cursor-pointer">
                <svg wire:loading.remove wire:target="sincronizarBiometrico" class="h-4 w-4 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 12a9 9 0 1 1-2.64-6.36" />
                  <path d="M21 3v6h-6" />
                </svg>
                <svg wire:loading wire:target="sincronizarBiometrico" class="h-4 w-4 text-amber-700 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="2" x2="12" y2="6" />
                  <line x1="12" y1="18" x2="12" y2="22" />
                  <line x1="4.93" y1="4.93" x2="7.76" y2="7.76" />
                  <line x1="16.24" y1="16.24" x2="19.07" y2="19.07" />
                  <line x1="2" y1="12" x2="6" y2="12" />
                  <line x1="18" y1="12" x2="22" y2="12" />
                  <line x1="4.93" y1="19.07" x2="7.76" y2="16.24" />
                  <line x1="16.24" y1="7.76" x2="19.07" y2="4.93" />
                </svg>
                <span wire:loading.remove wire:target="sincronizarBiometrico">Sincronizar biométrico</span>
                <span wire:loading wire:target="sincronizarBiometrico">Conectando y sincronizando…</span>
              </button>

              @if ($lastSyncTime)
                <span class="hidden md:inline-block text-[11px] text-slate-400 font-medium">
                  Sincronizado: {{ $lastSyncTime }}
                </span>
              @endif
            </div>
          @endcan

          @can('gestionar personal')
            <a wire:navigate href="{{ route('horarios') }}"
              class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
              <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              <span>Horarios por sucursal</span>
            </a>
            <a wire:navigate href="{{ route('incidencias') }}"
              class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
              <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span>Incidencias</span>
            </a>
          @endcan
        </div>
      </div>

      {{-- DETALLES DE SINCRONIZACIÓN BAJO DEMANDA --}}
      @if ($syncResult)
        <div class="mt-4 pt-4 border-t border-slate-100 animate-in fade-in slide-in-from-top-2 duration-200">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              @if ($syncResult['success'] ?? false)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                  Sincronización completada
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                  <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                  Error de sincronización
                </span>
              @endif
              <span class="text-xs text-slate-400">• {{ $syncResult['timestamp'] ?? '' }}</span>
            </div>

            <button type="button" wire:click="cerrarResumenSync" class="text-slate-400 hover:text-slate-600 text-xs font-semibold">
              Cerrar
            </button>
          </div>

          @if ($syncResult['success'] ?? false)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
              <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 text-center">
                <div class="text-lg font-black text-slate-800">{{ $syncResult['sincronizados'] }}/{{ $syncResult['total_devices'] }}</div>
                <div class="text-[11px] font-medium text-slate-500">Equipos conectados</div>
              </div>
              <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 text-center">
                <div class="text-lg font-black text-amber-600">{{ $syncResult['total_importados'] }}</div>
                <div class="text-[11px] font-medium text-slate-500">Marcaciones leídas</div>
              </div>
              <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 text-center">
                <div class="text-lg font-black text-emerald-600">{{ $syncResult['total_actualizados'] }}</div>
                <div class="text-[11px] font-medium text-slate-500">Salidas/Entradas actualizadas</div>
              </div>
              <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 text-center">
                <div class="text-lg font-black text-blue-600">{{ $syncResult['total_generados'] }}</div>
                <div class="text-[11px] font-medium text-slate-500">Registros nuevos</div>
              </div>
            </div>

            @if (!empty($syncResult['detalles']))
              <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                @foreach ($syncResult['detalles'] as $detalle)
                  <div class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs {{ $detalle['status'] === 'sincronizado' ? 'bg-emerald-50 text-emerald-900 border border-emerald-100' : ($detalle['status'] === 'sin-cambios' ? 'bg-slate-50 text-slate-700 border border-slate-100' : 'bg-slate-50 text-slate-400 border border-slate-100') }}">
                    <div class="flex items-center gap-2">
                      <span class="h-2 w-2 rounded-full {{ $detalle['status'] === 'sincronizado' ? 'bg-emerald-500' : ($detalle['status'] === 'sin-cambios' ? 'bg-blue-400' : 'bg-slate-300') }}"></span>
                      <span class="font-bold">{{ $detalle['device'] }}</span>
                    </div>
                    <span class="text-[11px] truncate max-w-xs">{{ $detalle['message'] }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          @else
            <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-800">
              {{ $syncResult['error'] ?? 'Ocurrió un error inesperado.' }}
            </div>
          @endif
        </div>
      @endif
    </div>
  </section>

</div>