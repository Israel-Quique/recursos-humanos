<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Recursos Humanos - Asistencia' }}</title>
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @livewireStyles
  </head>
  <body class="bg-offwhite text-slate-800">
    @php
      $routeName = request()->route()?->getName() ?? 'dashboard';
      $routeTitles = [
        'dashboard' => 'CALENDAR',
        'inicio' => 'INICIO',
        'importar' => 'IMPORT',
        'calendario' => 'CALENDAR',
        'reportes' => 'REPORTS',
        'mis-horas' => 'MY HOURS',
        'personal' => 'PERSONAL',
        'horarios' => 'SCHEDULE',
        'fechas-especiales' => 'SPECIAL DAYS',
        'incidencias' => 'INCIDENTS',
        'accesos' => 'ACCESS',
        'auditoria' => 'AUDIT',
        'consulta-carnet' => 'CARNET',
      ];
      $toastModule = $routeTitles[$routeName] ?? 'PANEL';
      $activeRole = auth()->user()?->getRoleNames()->first() ?? 'sin rol';
      $authUser = auth()->user()?->loadMissing('empleado');
      $personalVista = request()->query('vista', 'personal');
    @endphp
    @php
      $menuLogo = file_exists(public_path('images/menu-logo.png'))
        ? asset('images/menu-logo.png')
        : asset('images/correos-bolivia-brand.svg');
    @endphp

    <div class="app-shell">
      <aside class="app-sidebar">
        <div class="app-sidebar-panel">
          <div class="app-sidebar-brandmark">
            <img src="{{ $menuLogo }}" alt="Correos de Bolivia" class="app-sidebar-brandmark-image">
          </div>

          <nav class="app-sidebar-nav">
            @can('ver panel')
              <a wire:navigate href="{{ route('inicio') }}" class="app-sidebar-link {{ request()->routeIs('inicio') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                <span class="app-sidebar-link-label">Panel de inicio</span>
              </a>
              <a wire:navigate href="{{ route('dashboard') }}" class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7.5" height="9" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="5.5" rx="1.6"/><rect x="13.5" y="12" width="7.5" height="9" rx="1.6"/><rect x="3" y="16" width="7.5" height="5" rx="1.6"/></svg>
                </span>
                <span class="app-sidebar-link-label">Calendario laboral</span>
              </a>
            @endcan
            @can('gestionar personal')
              <div x-data="{ open: {{ (request()->routeIs('horarios') || request()->routeIs('fechas-especiales') || request()->routeIs('incidencias') || request()->routeIs('importar') || (request()->routeIs('personal') && in_array($personalVista, ['personal', 'inactivos'], true))) ? 'true' : 'false' }} }">
                <button type="button" x-on:click="open = ! open" class="app-sidebar-link w-full text-left {{ (request()->routeIs('horarios') || request()->routeIs('fechas-especiales') || request()->routeIs('incidencias') || request()->routeIs('importar') || (request()->routeIs('personal') && in_array($personalVista, ['personal', 'inactivos'], true))) ? 'app-sidebar-link-active' : '' }}" x-bind:aria-expanded="open.toString()">
                  <span class="app-sidebar-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                  </span>
                  <span class="app-sidebar-link-label">Operación Laboral</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="app-sidebar-chevron" :class="{ 'app-sidebar-chevron-open': open }"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="app-sidebar-submenu">
                  <a wire:navigate href="{{ route('personal', ['vista' => 'personal']) }}" class="app-sidebar-sublink {{ request()->routeIs('personal') && $personalVista === 'personal' ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Personal registrado</a>
                  <a wire:navigate href="{{ route('personal', ['vista' => 'inactivos']) }}" class="app-sidebar-sublink {{ request()->routeIs('personal') && $personalVista === 'inactivos' ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Inactivos</a>
                  <a wire:navigate href="{{ route('horarios') }}" class="app-sidebar-sublink {{ request()->routeIs('horarios') ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Horarios por sucursal</a>
                  <a wire:navigate href="{{ route('fechas-especiales') }}" class="app-sidebar-sublink {{ request()->routeIs('fechas-especiales') ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Fechas especiales</a>
                  <a wire:navigate href="{{ route('incidencias') }}" class="app-sidebar-sublink {{ request()->routeIs('incidencias') ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Incidencias y permisos</a>
                  @can('importar biometria')
                    <a wire:navigate href="{{ route('importar') }}" class="app-sidebar-sublink {{ request()->routeIs('importar') ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Importaciones</a>
                  @endcan
                </div>
              </div>
              <div x-data="{ open: {{ (request()->routeIs('personal') && in_array($personalVista, ['marcaciones', 'control'], true)) ? 'true' : 'false' }} }">
                <button type="button" x-on:click="open = ! open" class="app-sidebar-link w-full text-left {{ (request()->routeIs('personal') && in_array($personalVista, ['marcaciones', 'control'], true)) ? 'app-sidebar-link-active' : '' }}" x-bind:aria-expanded="open.toString()">
                  <span class="app-sidebar-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                      <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                      <path d="M9 12h6M9 16h6"/>
                    </svg>
                  </span>
                  <span class="app-sidebar-link-label">Registros de asistencias</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="app-sidebar-chevron" :class="{ 'app-sidebar-chevron-open': open }"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="app-sidebar-submenu">
                  <a wire:navigate href="{{ route('personal', ['vista' => 'marcaciones']) }}" class="app-sidebar-sublink {{ request()->routeIs('personal') && $personalVista === 'marcaciones' ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Marcaciones personales</a>
                  <a wire:navigate href="{{ route('personal', ['vista' => 'control']) }}" class="app-sidebar-sublink {{ request()->routeIs('personal') && $personalVista === 'control' ? 'app-sidebar-sublink-active' : '' }}"><span class="app-sidebar-subdot"></span>Marcaciones por sucursales</a>
                </div>
              </div>
            @endcan
            {{-- Calendario oculto del sidebar (la ruta sigue disponible)
            @can('ver calendario')
              <a wire:navigate href="{{ route('calendario') }}" class="app-sidebar-link {{ request()->routeIs('calendario') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M8 3v4M16 3v4M7.75 13.5h.01M12 13.5h.01M16.25 13.5h.01M7.75 17h.01M12 17h.01"/></svg>
                </span>
                <span class="app-sidebar-link-label">Calendario</span>
              </a>
            @endcan
            --}}
            @can('ver reportes')
              <a wire:navigate href="{{ route('reportes') }}" class="app-sidebar-link {{ request()->routeIs('reportes') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"/><path d="M14 3.5V8h4"/><path d="M9 17v-3M12 17v-5.5M15 17v-2"/></svg>
                </span>
                <span class="app-sidebar-link-label">Reportes</span>
              </a>
            @endcan
            @can('gestionar accesos')
              <a wire:navigate href="{{ route('accesos') }}" class="app-sidebar-link {{ request()->routeIs('accesos') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5 5 6v5.5c0 4.5 3 7.6 7 9 4-1.4 7-4.5 7-9V6Z"/><path d="m9.3 12 1.9 1.9 3.4-3.8"/></svg>
                </span>
                <span class="app-sidebar-link-label">Accesos</span>
              </a>
            @endcan
            @can('ver auditoria')
              <a wire:navigate href="{{ route('auditoria') }}" class="app-sidebar-link {{ request()->routeIs('auditoria') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="4" width="12" height="17" rx="1.6"/><path d="M9 3.5h6a1 1 0 0 1 1 1V6H8V4.5a1 1 0 0 1 1-1Z"/><path d="m9 13 1.9 1.9 3.6-4"/></svg>
                </span>
                <span class="app-sidebar-link-label">Auditoria</span>
              </a>
            @endcan
          </nav>

          <form method="POST" action="{{ route('logout') }}" class="app-sidebar-logout">
            @csrf
            <button type="submit" class="app-sidebar-logout-button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="app-sidebar-logout-icon"><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3"/><path d="M15 8l4 4-4 4"/><path d="M19 12H9"/></svg>
              <span>Salir</span>
            </button>
          </form>
        </div>
      </aside>

      <main class="app-main">
        <div class="app-main-header">
          <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
              <p class="app-main-kicker">Recursos Humanos</p>
              <h2 class="app-main-title">{{ $title ?? 'Panel de recursos humanos' }}</h2>
            </div>

            <div
              class="app-session-chip"
              x-data="{ copied: false, copyLink(url) { navigator.clipboard.writeText(url).then(() => { this.copied = true; setTimeout(() => this.copied = false, 1800); }); } }"
            >
              <span class="app-session-icon"></span>
              <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <span>Sesion activa: <strong>{{ $authUser?->name }} - [{{ \Illuminate\Support\Str::headline($activeRole) }}]</strong></span>

                @if($authUser?->empleado_id)
                  <div class="flex flex-wrap items-center gap-2">
                    <a
                      wire:navigate
                      href="{{ route('mis-horas') }}"
                      class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-4 w-4"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                      <span>Ver mis horas</span>
                    </a>

                    <button
                      type="button"
                      x-on:click="copyLink('{{ route('mis-horas') }}')"
                      class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                      title="Copiar enlace de mis horas"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-4 w-4"><rect x="9" y="9" width="10" height="10" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                      <span x-show="!copied">Copiar enlace</span>
                      <span x-show="copied" x-cloak>Copiado</span>
                    </button>
                  </div>
                @else
                  <span class="text-sm text-slate-500">Sin trabajador vinculado</span>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="app-toast" data-app-toast aria-hidden="true">
          <div class="app-toast-icon"></div>
          <div>
            <p class="app-toast-title">Navegacion</p>
            <p class="app-toast-copy">Modulo: {{ $toastModule }} cargado.</p>
          </div>
        </div>

        {{ $slot }}

        <footer class="app-footer">
          <p>&copy; 2026 Correos de Bolivia. Todos los derechos reservados.</p>
          <p>Terminal de Operacion Segura <span class="mx-3 hidden md:inline">•</span> Seguridad: AES-256</p>
        </footer>
      </main>
    </div>

    @livewireScripts
    <script src="{{ asset('vendor/d3/d3.min.js') }}?v={{ @filemtime(public_path('vendor/d3/d3.min.js')) }}"></script>
    <script src="{{ asset('vendor/topojson/topojson-client.min.js') }}?v={{ @filemtime(public_path('vendor/topojson/topojson-client.min.js')) }}"></script>
    <script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
  </body>
</html>
