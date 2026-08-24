<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Ingreso - Recursos Humanos'); ?></title>
    <link href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(@filemtime(public_path('css/app.css'))); ?>" rel="stylesheet">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

  </head>
  <body class="guest-body text-slate-800">
    <main class="guest-shell">
      <?php echo e($slot); ?>

    </main>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script src="<?php echo e(asset('vendor/d3/d3.min.js')); ?>?v=<?php echo e(@filemtime(public_path('vendor/d3/d3.min.js'))); ?>"></script>
    <script src="<?php echo e(asset('vendor/topojson/topojson-client.min.js')); ?>?v=<?php echo e(@filemtime(public_path('vendor/topojson/topojson-client.min.js'))); ?>"></script>
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(@filemtime(public_path('js/app.js'))); ?>"></script>
  </body>
</html>
<?php /**PATH C:\Users\WILLIAMS\Desktop\recursos-humanos-master\resources\views/layouts/guest.blade.php ENDPATH**/ ?>