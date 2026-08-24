<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Recursos Humanos - Asistencia'); ?></title>
    <link href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(@filemtime(public_path('css/app.css'))); ?>" rel="stylesheet">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

  </head>
  <body class="bg-offwhite text-slate-800">
    <?php
      $routeName = request()->route()?->getName() ?? 'dashboard';
      $routeTitles = [
        'dashboard' => 'PANEL',
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
    ?>
    <?php
      $menuLogo = file_exists(public_path('images/menu-logo.png'))
        ? asset('images/menu-logo.png')
        : asset('images/correos-bolivia-brand.svg');
    ?>

    <div class="app-shell">
      <aside class="app-sidebar">
        <div class="app-sidebar-panel">
          <div class="app-sidebar-brandmark">
            <img src="<?php echo e($menuLogo); ?>" alt="Correos de Bolivia" class="app-sidebar-brandmark-image">
          </div>

          <nav class="app-sidebar-nav">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver panel')): ?>
              <a wire:navigate href="<?php echo e(route('dashboard')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7.5" height="9" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="5.5" rx="1.6"/><rect x="13.5" y="12" width="7.5" height="9" rx="1.6"/><rect x="3" y="16" width="7.5" height="5" rx="1.6"/></svg>
                </span>
                <span class="app-sidebar-link-label">Panel</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar personal')): ?>
              <div x-data="{ open: <?php echo e(request()->routeIs('personal') ? 'true' : 'false'); ?> }">
                <button type="button" x-on:click="open = ! open" class="app-sidebar-link w-full text-left <?php echo e(request()->routeIs('personal') ? 'app-sidebar-link-active' : ''); ?>" x-bind:aria-expanded="open.toString()">
                  <span class="app-sidebar-link-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><circle cx="17" cy="8.3" r="2.3"/><path d="M15.7 14.3c2.4.5 4.1 2.7 4.1 5.7"/></svg>
                  </span>
                  <span class="app-sidebar-link-label">Personal</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="app-sidebar-chevron" :class="{ 'app-sidebar-chevron-open': open }"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="app-sidebar-submenu">
                  <a wire:navigate href="<?php echo e(route('personal', ['vista' => 'personal'])); ?>" class="app-sidebar-sublink <?php echo e(request()->routeIs('personal') && $personalVista === 'personal' ? 'app-sidebar-sublink-active' : ''); ?>"><span class="app-sidebar-subdot"></span>Personal registrado</a>
                  <a wire:navigate href="<?php echo e(route('personal', ['vista' => 'inactivos'])); ?>" class="app-sidebar-sublink <?php echo e(request()->routeIs('personal') && $personalVista === 'inactivos' ? 'app-sidebar-sublink-active' : ''); ?>"><span class="app-sidebar-subdot"></span>Inactivos</a>
                  <a wire:navigate href="<?php echo e(route('personal', ['vista' => 'marcaciones'])); ?>" class="app-sidebar-sublink <?php echo e(request()->routeIs('personal') && $personalVista === 'marcaciones' ? 'app-sidebar-sublink-active' : ''); ?>"><span class="app-sidebar-subdot"></span>Marcaciones</a>
                  <a wire:navigate href="<?php echo e(route('personal', ['vista' => 'control'])); ?>" class="app-sidebar-sublink <?php echo e(request()->routeIs('personal') && $personalVista === 'control' ? 'app-sidebar-sublink-active' : ''); ?>"><span class="app-sidebar-subdot"></span>Control mensual</a>
                </div>
              </div>
              <a wire:navigate href="<?php echo e(route('horarios')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('horarios') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.25"/><path d="M12 7.5V12l3 2"/></svg>
                </span>
                <span class="app-sidebar-link-label">Horarios</span>
              </a>
              <a wire:navigate href="<?php echo e(route('fechas-especiales')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('fechas-especiales') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M8 3v4M16 3v4"/><path d="m12 12.6 1 2 2.2.3-1.6 1.5.4 2.2-2-1.1-2 1.1.4-2.2-1.6-1.5 2.2-.3 1-2Z"/></svg>
                </span>
                <span class="app-sidebar-link-label">Fechas</span>
              </a>
              <a wire:navigate href="<?php echo e(route('incidencias')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('incidencias') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4 3 19h18Z"/><path d="M12 10v4M12 16.6h.01"/></svg>
                </span>
                <span class="app-sidebar-link-label">Incidencias</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importar biometria')): ?>
              <a wire:navigate href="<?php echo e(route('importar')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('importar') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V4M8 8l4-4 4 4"/><path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg>
                </span>
                <span class="app-sidebar-link-label">Importacion</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver calendario')): ?>
              <a wire:navigate href="<?php echo e(route('calendario')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('calendario') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M8 3v4M16 3v4M7.75 13.5h.01M12 13.5h.01M16.25 13.5h.01M7.75 17h.01M12 17h.01"/></svg>
                </span>
                <span class="app-sidebar-link-label">Calendario</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver reportes')): ?>
              <a wire:navigate href="<?php echo e(route('reportes')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('reportes') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z"/><path d="M14 3.5V8h4"/><path d="M9 17v-3M12 17v-5.5M15 17v-2"/></svg>
                </span>
                <span class="app-sidebar-link-label">Reportes</span>
              </a>
            <?php endif; ?>
            <a wire:navigate href="<?php echo e(route('consulta-carnet')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('consulta-carnet') ? 'app-sidebar-link-active' : ''); ?>">
              <span class="app-sidebar-link-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="M7.5 9.5h4.5"/><path d="M7.5 13h3"/><circle cx="16.5" cy="11.5" r="2.2"/><path d="M13.9 16.2c.8-1.4 2-2.1 3.1-2.1 1.2 0 2.4.7 3.1 2.1"/></svg>
              </span>
              <span class="app-sidebar-link-label">Consulta carnet</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar accesos')): ?>
              <a wire:navigate href="<?php echo e(route('accesos')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('accesos') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5 5 6v5.5c0 4.5 3 7.6 7 9 4-1.4 7-4.5 7-9V6Z"/><path d="m9.3 12 1.9 1.9 3.4-3.8"/></svg>
                </span>
                <span class="app-sidebar-link-label">Accesos</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver auditoria')): ?>
              <a wire:navigate href="<?php echo e(route('auditoria')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('auditoria') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="4" width="12" height="17" rx="1.6"/><path d="M9 3.5h6a1 1 0 0 1 1 1V6H8V4.5a1 1 0 0 1 1-1Z"/><path d="m9 13 1.9 1.9 3.6-4"/></svg>
                </span>
                <span class="app-sidebar-link-label">Auditoria</span>
              </a>
            <?php endif; ?>
          </nav>

          <form method="POST" action="<?php echo e(route('logout')); ?>" class="app-sidebar-logout">
            <?php echo csrf_field(); ?>
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
              <h2 class="app-main-title"><?php echo e($title ?? 'Panel de recursos humanos'); ?></h2>
            </div>

            <div
              class="app-session-chip"
              x-data="{ copied: false, copyLink(url) { navigator.clipboard.writeText(url).then(() => { this.copied = true; setTimeout(() => this.copied = false, 1800); }); } }"
            >
              <span class="app-session-icon"></span>
              <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <span>Sesion activa: <strong><?php echo e($authUser?->name); ?> - [<?php echo e(\Illuminate\Support\Str::headline($activeRole)); ?>]</strong></span>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($authUser?->empleado_id): ?>
                  <div class="flex flex-wrap items-center gap-2">
                    <a
                      wire:navigate
                      href="<?php echo e(route('mis-horas')); ?>"
                      class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-4 w-4"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                      <span>Ver mis horas</span>
                    </a>

                    <button
                      type="button"
                      x-on:click="copyLink('<?php echo e(route('mis-horas')); ?>')"
                      class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                      title="Copiar enlace de mis horas"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-4 w-4"><rect x="9" y="9" width="10" height="10" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                      <span x-show="!copied">Copiar enlace</span>
                      <span x-show="copied" x-cloak>Copiado</span>
                    </button>
                  </div>
                <?php else: ?>
                  <span class="text-sm text-slate-500">Sin trabajador vinculado</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="app-toast" data-app-toast aria-hidden="true">
          <div class="app-toast-icon"></div>
          <div>
            <p class="app-toast-title">Navegacion</p>
            <p class="app-toast-copy">Modulo: <?php echo e($toastModule); ?> cargado.</p>
          </div>
        </div>

        <?php echo e($slot); ?>


        <footer class="app-footer">
          <p>&copy; 2026 Correos de Bolivia. Todos los derechos reservados.</p>
          <p>Terminal de Operacion Segura <span class="mx-3 hidden md:inline">•</span> Seguridad: AES-256</p>
        </footer>
      </main>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script src="<?php echo e(asset('vendor/d3/d3.min.js')); ?>?v=<?php echo e(@filemtime(public_path('vendor/d3/d3.min.js'))); ?>"></script>
    <script src="<?php echo e(asset('vendor/topojson/topojson-client.min.js')); ?>?v=<?php echo e(@filemtime(public_path('vendor/topojson/topojson-client.min.js'))); ?>"></script>
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(@filemtime(public_path('js/app.js'))); ?>"></script>
  </body>
</html>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/layouts/app.blade.php ENDPATH**/ ?>