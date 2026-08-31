<div class="page-stack">
  
  {{-- 1. HERO BANNER DE BIENVENIDA --}}
  <article class="inicio-hero-card p-8 md:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="max-w-2xl z-10">
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-extrabold uppercase tracking-wider text-amber-300 bg-black/30 border border-amber-300/30 backdrop-blur-md">
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
        <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-4 py-2 text-xs font-bold text-white backdrop-blur-md">
          <svg class="h-4 w-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
          Rol: {{ ucfirst($user->getRoleNames()->first() ?? 'Usuario') }}
        </span>
        @if ($user->empleado?->sucursal)
          <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 border border-white/20 px-4 py-2 text-xs font-bold text-white backdrop-blur-md">
            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            Regional: {{ $user->empleado->sucursal }}
          </span>
        @endif
      </div>
    </div>

    {{-- Mini Estadísticas Rápidas del Banner --}}
    <div class="inicio-kpi-box flex items-center gap-6 p-6 shrink-0 z-10">
      <div class="text-center px-4 border-r border-white/15">
        <p class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-emerald-400">{{ $totalEmpleadosActivos }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">Personal Activo</span>
      </div>
      <div class="text-center px-4">
        <p class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-sky-300">{{ $totalSucursales }}</p>
        <span class="text-[11px] font-black uppercase tracking-wider text-slate-200 mt-1 block">Sucursales</span>
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
      <span class="text-xs text-slate-500 font-semibold hidden sm:inline-block">Selecciona una opción para comenzar</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      {{-- BOTÓN 1: MARCACIONES PERSONALES --}}
      <a wire:navigate href="{{ route('personal', ['vista' => 'marcaciones']) }}" class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-blue h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 text-[#0f67c0] px-3 py-1 text-xs font-black">
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

        <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-[#0f67c0]">
          <span>Ingresar al control individual</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

      {{-- BOTÓN 2: MARCACIONES POR SUCURSAL --}}
      <a wire:navigate href="{{ route('personal', ['vista' => 'control']) }}" class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-emerald h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 21h18"/>
              <path d="M19 21v-4"/>
              <path d="M19 13v-2"/>
              <path d="M19 7V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v17"/>
              <path d="M9 7h2M9 11h2M9 15h2M13 7h2M13 11h2M13 15h2"/>
            </svg>
          </div>
          <span class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1 text-xs font-black">
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

        <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-emerald-700">
          <span>Ver marcaciones regionales</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

      {{-- BOTÓN 3: REPORTES --}}
      <a wire:navigate href="{{ route('reportes') }}" class="inicio-action-card p-7 flex flex-col justify-between min-h-[240px] group cursor-pointer">
        <div class="flex items-start justify-between gap-4">
          <div class="inicio-icon-purple h-16 w-16 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
          </div>
          <span class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 text-purple-700 px-3 py-1 text-xs font-black">
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

        <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold text-purple-700">
          <span>Generar y descargar reportes</span>
          <span class="text-base group-hover:translate-x-1 transition duration-200">→</span>
        </div>
      </a>

    </div>
  </section>

  {{-- 3. ENLACES SECUNDARIOS Y OTRAS HERRAMIENTAS --}}
  <section class="mt-6">
    <div class="surface-card !p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div>
        <h4 class="text-sm font-bold text-slate-800">Otras herramientas operativas</h4>
        <p class="text-xs text-slate-500">Accede rápidamente a la configuración general o consulta el calendario</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <a wire:navigate href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
          <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span>Calendario laboral</span>
        </a>

        @can('gestionar personal')
          <a wire:navigate href="{{ route('horarios') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Horarios por sucursal</span>
          </a>
          <a wire:navigate href="{{ route('incidencias') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-xs">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Incidencias</span>
          </a>
        @endcan
      </div>
    </div>
  </section>

</div>
