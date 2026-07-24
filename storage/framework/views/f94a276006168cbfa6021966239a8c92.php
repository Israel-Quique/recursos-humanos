<div class="page-stack">
  <section class="surface-card">
    <p class="section-kicker">Organizacion interna</p>
    <h3 class="section-title">Estructura de Unidades de Asistencia</h3>
    <p class="section-copy-sm">Nivel jerarquico del sistema integrado y los centros de control habilitados.</p>

    <div class="org-chart">
      <div class="org-node org-node-main">
        <p class="org-node-kicker"><?php echo e($structure['central']['label']); ?></p>
        <h4 class="org-node-title"><?php echo e($structure['central']['title']); ?></h4>
      </div>

      <div class="org-connector"></div>

      <div class="org-branches">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $structure['branches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <article class="org-node">
            <p class="org-node-kicker"><?php echo e($branch['label']); ?></p>
            <h4 class="org-node-title"><?php echo e($branch['title']); ?></h4>
            <p class="org-node-copy"><?php echo e($branch['detail']); ?></p>
          </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\estructura-codigo.blade.php ENDPATH**/ ?>