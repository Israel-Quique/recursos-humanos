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
        'personal' => 'PERSONAL',
        'horarios' => 'SCHEDULE',
        'fechas-especiales' => 'SPECIAL DAYS',
        'incidencias' => 'INCIDENTS',
        'accesos' => 'ACCESS',
        'auditoria' => 'AUDIT',
      ];
      $toastModule = $routeTitles[$routeName] ?? 'PANEL';
      $activeRole = auth()->user()?->getRoleNames()->first() ?? 'sin rol';
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
                <span class="app-sidebar-link-dot"></span>
                <span>Panel</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar personal')): ?>
              <a wire:navigate href="<?php echo e(route('personal')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('personal') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Personal</span>
              </a>
              <a wire:navigate href="<?php echo e(route('horarios')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('horarios') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Horarios</span>
              </a>
              <a wire:navigate href="<?php echo e(route('fechas-especiales')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('fechas-especiales') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Fechas</span>
              </a>
              <a wire:navigate href="<?php echo e(route('incidencias')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('incidencias') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Incidencias</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importar biometria')): ?>
              <a wire:navigate href="<?php echo e(route('importar')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('importar') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Importacion</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver calendario')): ?>
              <a wire:navigate href="<?php echo e(route('calendario')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('calendario') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Calendario</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver reportes')): ?>
              <a wire:navigate href="<?php echo e(route('reportes')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('reportes') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Reportes</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar accesos')): ?>
              <a wire:navigate href="<?php echo e(route('accesos')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('accesos') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Accesos</span>
              </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver auditoria')): ?>
              <a wire:navigate href="<?php echo e(route('auditoria')); ?>" class="app-sidebar-link <?php echo e(request()->routeIs('auditoria') ? 'app-sidebar-link-active' : ''); ?>">
                <span class="app-sidebar-link-dot"></span>
                <span>Auditoria</span>
              </a>
            <?php endif; ?>
          </nav>

          <form method="POST" action="<?php echo e(route('logout')); ?>" class="app-sidebar-logout">
            <?php echo csrf_field(); ?>
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
              <h2 class="app-main-title"><?php echo e($title ?? 'Panel de recursos humanos'); ?></h2>
            </div>

            <div class="app-session-chip">
              <span class="app-session-icon"></span>
              <span>Sesion activa: <strong><?php echo e(auth()->user()?->name); ?> - [<?php echo e(\Illuminate\Support\Str::headline($activeRole)); ?>]</strong></span>
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