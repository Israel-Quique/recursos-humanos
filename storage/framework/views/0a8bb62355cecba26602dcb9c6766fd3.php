<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Filtro de reportes</p>
        <h3 class="section-title">Consulta por mes, sucursal y personal</h3>
        <p class="section-copy-sm">Resumen mensual cargado: <?php echo e($monthLabel); ?>.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div>
        <label class="form-label" for="report-reference-month">Mes de reporte</label>
        <input id="report-reference-month" type="month" wire:model.live="referenceMonth" class="form-input">
      </div>
      <div>
        <label class="form-label" for="report-branch">Sucursal</label>
        <select id="report-branch" wire:model.live="selectedBranch" class="form-input">
          <option value="">Todas las sucursales</option>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch); ?>"><?php echo e($branch); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
      <div>
        <label class="form-label" for="report-employee">Personal</label>
        <select id="report-employee" wire:model.live="selectedEmployeeId" class="form-input">
          <option value="">Todos / sin seleccionar</option>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($employee['id']); ?>"><?php echo e($employee['nombre']); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
    </div>
  </section>

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
        <p class="section-kicker">Reporte mensual</p>
        <h3 class="section-title">Frecuencias y cierre del mes</h3>
      </div>
      <p class="section-copy-sm"><?php echo e($monthLabel); ?></p>
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

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthlyReport['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label"><?php echo e($metric['label']); ?></p>
          <p class="mt-3 text-2xl font-semibold text-slate-900"><?php echo e($metric['value']); ?></p>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-8 rounded-[1.4rem] border border-slate-200 bg-white px-5 py-5">
      <div class="flex items-center justify-between gap-4">
        <h4 class="text-lg font-semibold text-slate-900">Personal con mayor retraso del mes</h4>
        <span class="status-badge status-warning"><?php echo e($monthlyReport['late_days']); ?> dias tarde</span>
      </div>

      <div class="report-scroll-list mt-5 space-y-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $monthlyReport['top_employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="rounded-xl bg-slate-50 px-4 py-3">
            <p class="font-semibold text-slate-900"><?php echo e($employee['nombre']); ?></p>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($employee['sucursal']); ?> | <?php echo e($employee['dias_tarde']); ?> dias tarde | <?php echo e($employee['retraso']); ?></p>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <p class="text-sm text-slate-400">No hay retrasos acumulados en el mes seleccionado.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Incidencias filtradas</p>
        <h3 class="section-title">Incidencias del mes seleccionado</h3>
      </div>
      <p class="section-copy-sm"><?php echo e($monthLabel); ?></p>
    </div>

    <div class="diagnostic-grid mt-8">
      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Incidencias justificadas</h4>
          <span class="status-badge status-available"><?php echo e(count($incidents['permisos'])); ?></span>
        </div>
        <div class="report-scroll-list mt-4 space-y-3">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['permisos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No hay incidencias justificadas en el rango.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Ausencias injustificadas</h4>
          <span class="status-badge status-warning"><?php echo e(count($incidents['faltas'])); ?></span>
        </div>
        <div class="report-scroll-list mt-4 space-y-3">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['faltas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No hay faltas injustificadas en el rango.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      <div class="diagnostic-card xl:col-span-2">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Olvidos de marcar</h4>
          <span class="status-badge status-danger"><?php echo e(count($incidents['olvidos'])); ?></span>
        </div>
        <div class="report-scroll-list mt-4 grid gap-4 md:grid-cols-2">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidents['olvidos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900"><?php echo e($item['nombre']); ?></p>
              <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No existen olvidos de marcacion en el rango.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Reporte personalizado</p>
        <h3 class="section-title">Detalle por personal</h3>
      </div>
      <p class="section-copy-sm">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($personalReport): ?>
          <?php echo e($personalReport['empleado']['nombre']); ?> | <?php echo e($monthLabel); ?>

        <?php else: ?>
          Selecciona un personal para ver su reporte individual del mes.
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($personalReport): ?>
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Codigo</p>
          <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['codigo']); ?></p>
        </div>
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Sucursal</p>
          <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['sucursal']); ?></p>
        </div>
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4 md:col-span-2">
          <p class="metric-label">Horario programado</p>
          <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($personalReport['empleado']['horario']); ?></p>
        </div>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $personalReport['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="rounded-[1.2rem] border border-slate-200 bg-white px-5 py-4">
            <p class="metric-label"><?php echo e($metric['label']); ?></p>
            <p class="mt-3 text-xl font-semibold text-slate-900"><?php echo e($metric['value']); ?></p>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="6" class="text-center text-slate-400">No hay registros del personal en el rango seleccionado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="mt-8 rounded-[1.4rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-500">
        Elige un personal en el filtro superior para cargar su reporte individual por fechas.
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/reportes.blade.php ENDPATH**/ ?>