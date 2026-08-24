<div class="page-stack">
  <section wire:poll.60s>
    <article class="surface-card surface-card-map">
      <p class="section-kicker">Mapa operativo</p>
      <h3 class="section-title">Asistencia por departamento</h3>
      <p class="section-copy-sm max-w-3xl">
        Haz clic sobre un departamento para ver cuantas personas ya marcaron y cuantas siguen en su puesto de trabajo.
      </p>

      <?php
        $initialDepartment = collect($departmentStats)->first();
      ?>

      <div class="bolivia-map-card" data-bolivia-map-root>
        <div class="bolivia-map-shell">
          <div class="bolivia-map-canvas" data-bolivia-map-canvas aria-label="Mapa de Bolivia por departamentos"></div>
          <script type="application/json" data-departments-json><?php echo json_encode($departmentStats, 15, 512) ?></script>
        </div>

        <aside class="department-bubble" data-department-bubble>
          <p class="department-bubble-kicker">Departamento seleccionado</p>
          <h4 class="department-bubble-title" data-department-name><?php echo e($initialDepartment['name']); ?></h4>
          <p class="department-bubble-copy" data-department-branch><?php echo e($initialDepartment['branch']); ?></p>
          <p class="department-bubble-copy mt-1">
            Actualizado a las <strong data-department-updated-at><?php echo e($initialDepartment['updated_at'] ?? now()->format('H:i')); ?></strong>
          </p>
          <p class="department-bubble-copy mt-1" data-department-sync-label><?php echo e($initialDepartment['sync_label'] ?? 'Sin sincronizacion automatica registrada'); ?></p>
          <div class="department-bubble-grid">
            <div class="department-bubble-stat">
              <span>Marcaron</span>
              <strong data-department-marked><?php echo e($initialDepartment['marked']); ?></strong>
            </div>
            <div class="department-bubble-stat">
              <span>En puesto</span>
              <strong data-department-working><?php echo e($initialDepartment['working']); ?></strong>
            </div>
            <div class="department-bubble-stat">
              <span>Personal activo</span>
              <strong data-department-employees><?php echo e($initialDepartment['employees'] ?? 0); ?></strong>
            </div>
            <div class="department-bubble-stat department-bubble-stat-alert">
              <span>Sin marcar</span>
              <strong data-department-missing><?php echo e($initialDepartment['missing']); ?></strong>
            </div>
          </div>

          <div class="department-presence-box">
            <div class="department-presence-head">
              <div>
                <p class="department-presence-kicker">Presencia actual</p>
                <h5 class="department-presence-title">Siguen en la agencia</h5>
              </div>
              <span class="department-presence-total" data-department-presence-total><?php echo e($initialDepartment['people_in_agency_total'] ?? 0); ?></span>
            </div>

            <div class="department-presence-list" data-department-presence-list>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($initialDepartment['people_in_agency'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="department-presence-item">
                  <strong><?php echo e($person['name']); ?></strong>
                  <span><?php echo e($person['area']); ?> | <?php echo e($person['status']); ?></span>
                </article>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="department-presence-empty">No hay personal dentro de la agencia en este momento.</p>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        </aside>
      </div>
    </article>
  </section>
</div>
<?php /**PATH C:\Users\WILLIAMS\Desktop\recursos-humanos-master\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>