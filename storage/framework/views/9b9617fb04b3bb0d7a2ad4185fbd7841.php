<div class="page-stack">
  <section class="metric-grid metric-grid-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <article class="metric-card metric-card-centered">
        <span class="metric-icon metric-icon-<?php echo e($metric['tone']); ?>"></span>
        <p class="metric-label mt-6"><?php echo e($metric['label']); ?></p>
        <strong class="metric-value"><?php echo e($metric['value']); ?></strong>
        <p class="metric-copy"><?php echo e($metric['detail']); ?></p>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <h3 class="section-title">Frecuencias de Asistencia</h3>
      </div>
      <p class="section-copy-sm">Ultimos 6 meses de gestion</p>
    </div>

    <div class="attendance-chart">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $frequency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="attendance-bar-group">
          <div class="attendance-bar <?php echo e($bar['active'] ? 'attendance-bar-active' : ''); ?>" style="height: <?php echo e($bar['height']); ?>;"></div>
          <span class="attendance-bar-label <?php echo e($bar['active'] ? 'attendance-bar-label-active' : ''); ?>"><?php echo e($bar['label']); ?></span>
          <span class="mt-2 text-xs text-slate-400"><?php echo e($bar['count']); ?></span>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </section>

  <section class="surface-card">
    <h3 class="section-title">Incidencias del dia</h3>
    <div class="diagnostic-grid mt-8">
      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Permisos aprobados</h4>
          <span class="status-badge status-available"><?php echo e(count($incidents['permisos'])); ?></span>
        </div>
        <div class="mt-4 space-y-3">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['permisos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No hay permisos activos en la fecha.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Ausencias injustificadas</h4>
          <span class="status-badge status-warning"><?php echo e(count($incidents['faltas'])); ?></span>
        </div>
        <div class="mt-4 space-y-3">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['faltas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No hay faltas injustificadas registradas hoy.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      <div class="diagnostic-card xl:col-span-2">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Olvidos de marcar</h4>
          <span class="status-badge status-danger"><?php echo e(count($incidents['olvidos'])); ?></span>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['olvidos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No existen olvidos de marcacion en la fecha.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\reportes.blade.php ENDPATH**/ ?>