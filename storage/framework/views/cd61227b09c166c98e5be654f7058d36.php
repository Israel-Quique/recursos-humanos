<div class="page-stack page-stack-calendar">
  <section class="surface-card surface-card-calendar">
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

    <div class="calendar-legend">
      <div class="calendar-legend-item">
        <span class="calendar-event-dot calendar-event-dot-red"></span>
        <span>Llegada tarde</span>
      </div>
      <div class="calendar-legend-item">
        <span class="calendar-event-dot calendar-event-dot-black"></span>
        <span>Excedio tolerancia mensual</span>
      </div>
    </div>

    <div class="calendar-layout">
      <div class="calendar-grid-main">
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
                  <article
                    wire:click="selectDate('<?php echo e($day['date']); ?>')"
                    class="calendar-day-card calendar-day-card-interactive <?php echo e($day['is_current_month'] ? '' : 'calendar-day-card-muted'); ?> <?php echo e($day['is_today'] ? 'calendar-day-card-today' : ''); ?> <?php echo e($selectedDay['date'] === $day['date'] ? 'calendar-day-card-selected' : ''); ?>"
                  >
                    <div class="calendar-day-top">
                      <span class="calendar-day-number"><?php echo e($day['label']); ?></span>
                    </div>

                    <div class="calendar-day-events">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['summary']['red'] > 0): ?>
                        <div class="calendar-day-counter" title="Llegadas tarde en el dia">
                          <span class="calendar-event-dot calendar-event-dot-red"></span>
                          <span><?php echo e($day['summary']['red']); ?></span>
                        </div>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['summary']['black'] > 0): ?>
                        <div class="calendar-day-counter calendar-day-counter-dark" title="Excedieron tolerancia mensual">
                          <span class="calendar-event-dot calendar-event-dot-black"></span>
                          <span><?php echo e($day['summary']['black']); ?></span>
                        </div>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      </div>

      <aside class="calendar-side-panel">
        <div class="calendar-side-head">
          <div>
            <p class="section-kicker">Detalle del dia</p>
            <h4 class="section-title calendar-side-day-title"><?php echo e($selectedDay['day_label']); ?></h4>
            <p class="section-copy-sm"><?php echo e($selectedDay['date_label']); ?></p>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedDay['is_today']): ?>
            <span class="calendar-side-badge">Hoy</span>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="calendar-side-body">
          <div class="calendar-side-metrics">
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Marcaciones</span>
              <strong class="calendar-side-metric-value"><?php echo e($selectedDay['totals']['marcaciones']); ?></strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Tardanzas</span>
              <strong class="calendar-side-metric-value"><?php echo e($selectedDay['totals']['tardanzas']); ?></strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Excedidos</span>
              <strong class="calendar-side-metric-value"><?php echo e($selectedDay['totals']['excedidos']); ?></strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Retraso total</span>
              <strong class="calendar-side-metric-value"><?php echo e($selectedDay['totals']['minutos_retraso_formateado']); ?></strong>
            </div>
          </div>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedDay['is_saturday']): ?>
            <div class="calendar-side-note">
              El dia seleccionado es sabado. Esa fecha no entra al conteo mensual de tardanzas.
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <div class="calendar-side-block">
            <button type="button" wire:click="toggleLateEmployees" class="calendar-side-toggle">
              <div class="calendar-side-block-head calendar-side-block-head-static">
                <h5 class="calendar-side-block-title">Personal con atraso</h5>
                <div class="calendar-side-toggle-meta">
                  <span class="calendar-side-block-count"><?php echo e(count($selectedDay['events'])); ?></span>
                  <span class="calendar-side-toggle-icon"><?php echo e($showLateEmployees ? '-' : '+'); ?></span>
                </div>
              </div>
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLateEmployees): ?>
              <div class="calendar-side-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selectedDay['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <div class="calendar-side-item">
                    <span class="calendar-event-dot calendar-event-dot-<?php echo e($event['tone']); ?>"></span>
                    <div class="calendar-side-item-copy">
                      <strong><?php echo e($event['nombre']); ?></strong>
                      <span><?php echo e($event['detalle']); ?> | Entrada <?php echo e($event['entrada']); ?></span>
                      <span><?php echo e($event['sucursal']); ?> | <?php echo e($event['estado']); ?></span>
                    </div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="calendar-side-empty">No se registraron tardanzas para esta fecha.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          <div class="calendar-side-block">
            <button type="button" wire:click="toggleMarkedEmployees" class="calendar-side-toggle">
              <div class="calendar-side-block-head calendar-side-block-head-static">
                <h5 class="calendar-side-block-title">Marcaciones del dia</h5>
                <div class="calendar-side-toggle-meta">
                  <span class="calendar-side-block-count"><?php echo e(count($selectedDay['marcaciones'])); ?></span>
                  <span class="calendar-side-toggle-icon"><?php echo e($showMarkedEmployees ? '-' : '+'); ?></span>
                </div>
              </div>
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showMarkedEmployees): ?>
              <div class="calendar-side-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selectedDay['marcaciones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <div class="calendar-side-item calendar-side-item-neutral">
                    <div class="calendar-side-item-copy">
                      <strong><?php echo e($registro['nombre']); ?></strong>
                      <span>Entrada <?php echo e($registro['entrada']); ?> | Salida <?php echo e($registro['salida']); ?></span>
                      <span><?php echo e($registro['sucursal']); ?> | <?php echo e($registro['estado']); ?></span>
                    </div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="calendar-side-empty">No hay marcaciones cargadas para este dia.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      </aside>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $calendar['milestones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $milestone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-[1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <?php echo e($milestone); ?>

        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/calendario.blade.php ENDPATH**/ ?>