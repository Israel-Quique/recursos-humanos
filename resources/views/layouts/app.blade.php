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
        'dashboard' => 'PANEL',
        'importar' => 'IMPORT',
        'calendario' => 'CALENDAR',
        'reportes' => 'REPORTS',
        'personal' => 'PERSONAL',
        'horarios' => 'SCHEDULE',
        'fechas-especiales' => 'SPECIAL DAYS',
        'incidencias' => 'INCIDENTS',
        'accesos' => 'ACCESS',
        'auditoria' => 'AUDIT',
      ];
      $toastModule = $routeTitles[$routeName] ?? 'PANEL';
      $activeRole = auth()->user()?->getRoleNames()->first() ?? 'sin rol';
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
              <a wire:navigate href="{{ route('dashboard') }}" class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Panel</span>
              </a>
            @endcan
            @can('gestionar personal')
              <a wire:navigate href="{{ route('personal') }}" class="app-sidebar-link {{ request()->routeIs('personal') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Personal</span>
              </a>
              <a wire:navigate href="{{ route('horarios') }}" class="app-sidebar-link {{ request()->routeIs('horarios') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Horarios</span>
              </a>
              <a wire:navigate href="{{ route('fechas-especiales') }}" class="app-sidebar-link {{ request()->routeIs('fechas-especiales') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Fechas</span>
              </a>
              <a wire:navigate href="{{ route('incidencias') }}" class="app-sidebar-link {{ request()->routeIs('incidencias') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Incidencias</span>
              </a>
            @endcan
            @can('importar biometria')
              <a wire:navigate href="{{ route('importar') }}" class="app-sidebar-link {{ request()->routeIs('importar') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Importacion</span>
              </a>
            @endcan
            @can('ver calendario')
              <a wire:navigate href="{{ route('calendario') }}" class="app-sidebar-link {{ request()->routeIs('calendario') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Calendario</span>
              </a>
            @endcan
            @can('ver reportes')
              <a wire:navigate href="{{ route('reportes') }}" class="app-sidebar-link {{ request()->routeIs('reportes') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Reportes</span>
              </a>
            @endcan
            @can('gestionar accesos')
              <a wire:navigate href="{{ route('accesos') }}" class="app-sidebar-link {{ request()->routeIs('accesos') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Accesos</span>
              </a>
            @endcan
            @can('ver auditoria')
              <a wire:navigate href="{{ route('auditoria') }}" class="app-sidebar-link {{ request()->routeIs('auditoria') ? 'app-sidebar-link-active' : '' }}">
                <span class="app-sidebar-link-dot"></span>
                <span>Auditoria</span>
              </a>
            @endcan
          </nav>

          <form method="POST" action="{{ route('logout') }}" class="app-sidebar-logout">
            @csrf
            <button type="submit" class="app-sidebar-logout-button">
              <span class="app-sidebar-link-dot"></span>
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

            <div class="app-session-chip">
              <span class="app-session-icon"></span>
              <span>Sesion activa: <strong>{{ auth()->user()?->name }} - [{{ \Illuminate\Support\Str::headline($activeRole) }}]</strong></span>
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
