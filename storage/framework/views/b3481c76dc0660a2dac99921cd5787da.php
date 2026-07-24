<div class="page-stack">
  <section class="surface-card">
    <div class="calendar-header">
      <div>
        <p class="section-kicker">Planificacion de turnos</p>
        <h3 class="section-title">Calendario de Asistencia</h3>
      </div>

      <div class="calendar-controls">
        <button type="button" class="calendar-chip" wire:click="goToCurrentMonth">Hoy</button>
        <div class="calendar-month-box">
          <button type="button" class="calendar-arrow" wire:click="goToPreviousMonth" aria-label="Mes anterior">&lsaquo;</button>
          <span><?php echo e($calendar['month_label']); ?></span>
          <button type="button" class="calendar-arrow" wire:click="goToNextMonth" aria-label="Mes siguiente">&rsaquo;</button>
        </div>
      </div>
    </div>

    <div class="calendar-shell">
      <div class="calendar-weekdays">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $calendar['weekdays']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div><?php echo e(strtoupper($weekday)); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>

      <div class="calendar-weeks">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $calendar['weeks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="calendar-week-row">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <article class="calendar-day-card <?php echo e($day['is_current_month'] ? '' : 'calendar-day-card-muted'); ?> <?php echo e($day['is_today'] ? 'calendar-day-card-today' : ''); ?>">
                <div class="calendar-day-top">
                  <span class="calendar-day-number"><?php echo e($day['label']); ?></span>
                </div>

                <div class="calendar-day-events">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $day['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="calendar-event-pill calendar-event-<?php echo e($event['tone']); ?>"><?php echo e($event['label']); ?></span>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
              </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\calendario.blade.php ENDPATH**/ ?>