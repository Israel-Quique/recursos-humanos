<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Modulo mensual</p>
        <h3 class="section-title">No marcados y personal atrasado</h3>
        <p class="section-copy-sm">Consulta consolidada del mes: <?php echo e($monthLabel); ?>.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
      <div>
        <label class="form-label" for="monthly-report-reference-month">Mes de reporte</label>
        <input id="monthly-report-reference-month" type="month" wire:model.live="referenceMonth" class="form-input">
      </div>
      <div>
        <label class="form-label" for="monthly-report-branch">Sucursal</label>
        <select id="monthly-report-branch" wire:model.live="selectedBranch" class="form-input">
          <option value="">Todas las sucursales</option>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch); ?>"><?php echo e($branch); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
    </div>
  </section>

  <section class="metric-grid metric-grid-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <article class="metric-card metric-card-centered">
        <p class="metric-label"><?php echo e($metric['label']); ?></p>
        <strong class="metric-value"><?php echo e($metric['value']); ?></strong>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>

  <section class="surface-card">
    <div class="flex flex-wrap gap-3">
      <button
        type="button"
        wire:click="showTable('resumen_atrasos')"
        class="rounded-full px-4 py-2 text-sm font-medium transition <?php echo e($activeTable === 'resumen_atrasos' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'); ?>"
      >
        Resumen de atrasos
      </button>
      <button
        type="button"
        wire:click="showTable('atrasos')"
        class="rounded-full px-4 py-2 text-sm font-medium transition <?php echo e($activeTable === 'atrasos' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'); ?>"
      >
        Detalle de atrasos
      </button>
      <button
        type="button"
        wire:click="showTable('no_marcados')"
        class="rounded-full px-4 py-2 text-sm font-medium transition <?php echo e($activeTable === 'no_marcados' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'); ?>"
      >
        No marcados
      </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTable === 'resumen_atrasos'): ?>
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Resumen de atrasos</p>
          <h3 class="section-title">Personal que se atraso en el mes</h3>
        </div>
        <p class="section-copy-sm">
          <?php echo e($monthLabel); ?> · <?php echo e($lateSummaryPagination['from']); ?>-<?php echo e($lateSummaryPagination['to']); ?> de <?php echo e($lateSummaryPagination['total']); ?>

        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Dias tarde</th>
              <th>Retraso acumulado</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $report['resumen_atrasos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($item['nombre']); ?></td>
                <td><?php echo e($item['sucursal']); ?></td>
                <td><?php echo e($item['dias_tarde']); ?></td>
                <td><?php echo e($item['retraso']); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="4" class="text-center text-slate-400">No hay atrasos registrados en el mes seleccionado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lateSummaryPagination['last_page'] > 1): ?>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina <?php echo e($lateSummaryPagination['page']); ?> de <?php echo e($lateSummaryPagination['last_page']); ?></span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToLateSummaryPage(<?php echo e($lateSummaryPagination['page'] - 1); ?>)" <?php if($lateSummaryPagination['page'] === 1): echo 'disabled'; endif; ?> class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToLateSummaryPage(<?php echo e($lateSummaryPagination['page'] + 1); ?>)" <?php if($lateSummaryPagination['page'] === $lateSummaryPagination['last_page']): echo 'disabled'; endif; ?> class="table-action-button">Siguiente</button>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTable === 'atrasos'): ?>
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Detalle de atrasos</p>
          <h3 class="section-title">Marcaciones tardias del mes</h3>
        </div>
        <p class="section-copy-sm">
          <?php echo e($lateDetailsPagination['from']); ?>-<?php echo e($lateDetailsPagination['to']); ?> de <?php echo e($lateDetailsPagination['total']); ?> registros
        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Hora programada</th>
              <th>Hora marcada</th>
              <th>Retraso</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $report['atrasos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($item['fecha']); ?></td>
                <td><?php echo e($item['nombre']); ?></td>
                <td><?php echo e($item['sucursal']); ?></td>
                <td><?php echo e($item['entrada_programada']); ?></td>
                <td><?php echo e($item['entrada_real']); ?></td>
                <td><?php echo e($item['retraso']); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="6" class="text-center text-slate-400">No existen atrasos en el rango seleccionado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lateDetailsPagination['last_page'] > 1): ?>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina <?php echo e($lateDetailsPagination['page']); ?> de <?php echo e($lateDetailsPagination['last_page']); ?></span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToLateDetailsPage(<?php echo e($lateDetailsPagination['page'] - 1); ?>)" <?php if($lateDetailsPagination['page'] === 1): echo 'disabled'; endif; ?> class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToLateDetailsPage(<?php echo e($lateDetailsPagination['page'] + 1); ?>)" <?php if($lateDetailsPagination['page'] === $lateDetailsPagination['last_page']): echo 'disabled'; endif; ?> class="table-action-button">Siguiente</button>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTable === 'no_marcados'): ?>
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Detalle de no marcados</p>
          <h3 class="section-title">Registros incompletos del mes</h3>
        </div>
        <p class="section-copy-sm">
          <?php echo e($forgotMarksPagination['from']); ?>-<?php echo e($forgotMarksPagination['to']); ?> de <?php echo e($forgotMarksPagination['total']); ?> registros
        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Observacion</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $report['no_marcados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($item['fecha']); ?></td>
                <td><?php echo e($item['nombre']); ?></td>
                <td><?php echo e($item['sucursal']); ?></td>
                <td><?php echo e($item['entrada']); ?></td>
                <td><?php echo e($item['salida']); ?></td>
                <td><?php echo e($item['detalle']); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="6" class="text-center text-slate-400">No hay registros no marcados en el mes seleccionado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forgotMarksPagination['last_page'] > 1): ?>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina <?php echo e($forgotMarksPagination['page']); ?> de <?php echo e($forgotMarksPagination['last_page']); ?></span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToForgotMarksPage(<?php echo e($forgotMarksPagination['page'] - 1); ?>)" <?php if($forgotMarksPagination['page'] === 1): echo 'disabled'; endif; ?> class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToForgotMarksPage(<?php echo e($forgotMarksPagination['page'] + 1); ?>)" <?php if($forgotMarksPagination['page'] === $forgotMarksPagination['last_page']): echo 'disabled'; endif; ?> class="table-action-button">Siguiente</button>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/reporte-mensual-no-marcados-atrasos.blade.php ENDPATH**/ ?>