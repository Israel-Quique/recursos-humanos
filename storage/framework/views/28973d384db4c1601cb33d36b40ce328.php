<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Perfil compartido</p>
        <h3 class="section-title">Horas marcadas del personal</h3>
        <p class="section-copy-sm">Consulta las marcaciones por mes y por dia.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div>
        <label class="form-label" for="shared-profile-month">Mes</label>
        <select id="shared-profile-month" wire:model.live="referenceMonth" class="form-input">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($option['value']); ?>"><?php echo e($option['label']); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>

      <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
        <p class="metric-label">Personal</p>
        <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['nombre']); ?></p>
      </div>

      <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
        <p class="metric-label">Codigo</p>
        <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['codigo']); ?></p>
      </div>

      <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
        <p class="metric-label">Sucursal</p>
        <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['sucursal']); ?></p>
      </div>
    </div>
  </section>

  <section class="metric-grid metric-grid-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $personalReport['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <article class="metric-card metric-card-centered">
        <p class="metric-label"><?php echo e($metric['label']); ?></p>
        <strong class="metric-value"><?php echo e($metric['value']); ?></strong>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Detalle del mes</p>
        <h3 class="section-title">Marcaciones de <?php echo e($monthLabel); ?></h3>
      </div>
      <p class="section-copy-sm"><?php echo e($personalReport['empleado']['horario']); ?></p>
    </div>

    <div class="history-table-shell mt-8">
      <table class="history-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Horas</th>
            <th>Retraso</th>
            <th>Estado</th>
            <th>Biometrico</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $personalReport['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($row['fecha']); ?></td>
              <td><?php echo e($row['entrada']); ?></td>
              <td><?php echo e($row['salida']); ?></td>
              <td><?php echo e($row['horas']); ?></td>
              <td><?php echo e($row['retraso']); ?></td>
              <td><?php echo e($row['estado']); ?></td>
              <td><?php echo e($row['estado_biometrico']); ?> | <?php echo e($row['evento_biometrico']); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="text-center text-slate-400">No hay marcaciones registradas en el mes seleccionado.</td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/perfil-horas.blade.php ENDPATH**/ ?>