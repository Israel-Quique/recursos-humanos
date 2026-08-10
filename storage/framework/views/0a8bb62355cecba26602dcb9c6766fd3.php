<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEmployeeDetailModal): ?>
    <div class="app-modal-backdrop" wire:click="closeEmployeeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeEmployeeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Detalle mensual</p>
            <h3 class="section-title app-modal-title"><?php echo e($detailEmployeeReport['empleado']['nombre'] ?? 'Detalle del personal'); ?></h3>
            <p class="section-copy-sm">Revision puntual de tardanzas, no marcados y faltas del mes seleccionado.</p>
          </div>
          <div class="app-modal-actions">
            <button type="button" wire:click="descargarPdfDetalleEmpleado" class="table-action-button">
              <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
              </svg>
              <span>PDF</span>
            </button>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Codigo</p>
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo'); ?></p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Sucursal</p>
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal'); ?></p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4 md:col-span-2">
            <p class="metric-label">Horario</p>
            <p class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--'); ?></p>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($detailEmployeeReport['metrics'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-[1.2rem] border border-slate-200 bg-white px-5 py-4">
              <p class="metric-label"><?php echo e($metric['label']); ?></p>
              <p class="mt-3 text-xl font-semibold text-slate-900"><?php echo e($metric['value']); ?></p>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-3">
          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">Dias tarde</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['tardanzas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                  <p class="mt-1 text-sm text-slate-500">Entrada <?php echo e($item['entrada']); ?> | Retraso <?php echo e($item['retraso']); ?></p>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-400">No tiene tardanzas en el mes.</p>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>

          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">No marcados</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['no_marcados'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                  <p class="mt-1 text-sm text-slate-500">Entrada <?php echo e($item['entrada']); ?> | Salida <?php echo e($item['salida']); ?></p>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-400">No tiene no marcados en el mes.</p>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>

          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">Faltas</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['faltas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                  <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-400">No tiene faltas en el mes.</p>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        </div>

        <div id="reportes-detalle-empleado-pdf-content" class="hidden">
          <div class="pdf-export-sheet space-y-8">
            <header class="pdf-export-header">
              <div>
                <p class="pdf-export-kicker">Correos de Bolivia</p>
                <h2 class="pdf-export-title">Detalle mensual del personal</h2>
                <p class="pdf-export-copy">Tardanzas, no marcados y faltas del mes seleccionado.</p>
              </div>
              <div class="pdf-export-badge">
                <span class="pdf-export-badge-label">Mes</span>
                <strong class="pdf-export-badge-value"><?php echo e($monthLabel); ?></strong>
              </div>
            </header>

            <div class="pdf-export-grid pdf-export-grid-primary">
              <div class="pdf-export-card pdf-export-card-highlight">
                <p class="pdf-export-label">Nombre</p>
                <strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['nombre'] ?? 'Sin nombre'); ?></strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Codigo</p>
                <strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo'); ?></strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Sucursal</p>
                <strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal'); ?></strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Horario</p>
                <strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--'); ?></strong>
              </div>
            </div>

            <div class="pdf-export-grid pdf-export-grid-secondary">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($detailEmployeeReport['metrics'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pdf-export-card">
                  <p class="pdf-export-label"><?php echo e($metric['label']); ?></p>
                  <p class="pdf-export-value-sm"><?php echo e($metric['value']); ?></p>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Tardanzas</p>
                  <h3 class="section-title">Dias tarde</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Retraso</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['tardanzas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                      <td><?php echo e($item['fecha']); ?></td>
                      <td><?php echo e($item['entrada']); ?></td>
                      <td><?php echo e($item['salida']); ?></td>
                      <td><?php echo e($item['retraso']); ?></td>
                      <td><?php echo e($item['estado']); ?></td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-slate-400">No tiene tardanzas en el mes.</td></tr>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">No marcados</p>
                  <h3 class="section-title">Registros incompletos</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['no_marcados'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                      <td><?php echo e($item['fecha']); ?></td>
                      <td><?php echo e($item['entrada']); ?></td>
                      <td><?php echo e($item['salida']); ?></td>
                      <td><?php echo e($item['estado']); ?></td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-slate-400">No tiene no marcados en el mes.</td></tr>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Faltas</p>
                  <h3 class="section-title">Ausencias del mes</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Detalle</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['faltas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                      <td><?php echo e($item['fecha']); ?></td>
                      <td><?php echo e($item['detalle']); ?></td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="2" class="text-center text-slate-400">No tiene faltas en el mes.</td></tr>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <div id="reportes-pdf-content" class="hidden">
    <div class="pdf-export-sheet space-y-8">
      <header class="pdf-export-header">
        <div>
          <p class="pdf-export-kicker">Correos de Bolivia</p>
          <h2 class="pdf-export-title">Reporte mensual de asistencia</h2>
          <p class="pdf-export-copy">Resumen ejecutivo con metricas, personal con mayor retraso e incidencias del mes.</p>
        </div>
        <div class="pdf-export-badge">
          <span class="pdf-export-badge-label">Mes</span>
          <strong class="pdf-export-badge-value"><?php echo e($monthLabel); ?></strong>
        </div>
      </header>

      <div class="pdf-export-grid pdf-export-grid-primary">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="pdf-export-card">
            <p class="pdf-export-label"><?php echo e($metric['label']); ?></p>
            <strong class="pdf-export-value"><?php echo e($metric['value']); ?></strong>
            <p class="pdf-export-value-sm"><?php echo e($metric['detail']); ?></p>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>

      <div class="pdf-export-table-shell">
        <div class="section-head-row pdf-export-section-head">
          <div>
            <p class="section-kicker">Mayor retraso</p>
            <h3 class="section-title">Personal con mayor retraso del mes</h3>
          </div>
        </div>
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Dias tarde</th>
              <th>Retraso</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $monthlyReport['top_employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($employee['nombre']); ?></td>
                <td><?php echo e($employee['sucursal']); ?></td>
                <td><?php echo e($employee['dias_tarde']); ?></td>
                <td><?php echo e($employee['retraso']); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="4" class="text-center text-slate-400">No hay retrasos acumulados en el mes seleccionado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Filtro de reportes</p>
        <h3 class="section-title">Consulta por mes, sucursal y personal</h3>
        <p class="section-copy-sm">Resumen mensual cargado: <?php echo e($monthLabel); ?>.</p>
      </div>
      <button type="button" wire:click="descargarPdfReporte" class="section-action-button">PDF</button>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
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

    <div class="attendance-chart-shell">
      <div class="attendance-chart-summary">
        <div class="attendance-chart-pill">
          <span class="attendance-chart-pill-label">Mes actual</span>
          <strong class="attendance-chart-pill-value"><?php echo e($frequency['summary']['current_count']); ?></strong>
        </div>
        <div class="attendance-chart-pill">
          <span class="attendance-chart-pill-label">Pico del periodo</span>
          <strong class="attendance-chart-pill-value"><?php echo e($frequency['summary']['peak_label']); ?> · <?php echo e($frequency['summary']['peak_count']); ?></strong>
        </div>
      </div>

      <div class="attendance-chart-frame">
        <div class="attendance-chart-scale" aria-hidden="true">
          <span><?php echo e($frequency['scale']['max']); ?></span>
          <span><?php echo e($frequency['scale']['mid']); ?></span>
          <span><?php echo e($frequency['scale']['min']); ?></span>
        </div>

        <div class="attendance-chart">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $frequency['bars']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="attendance-bar-group">
              <button
                type="button"
                wire:click="selectReferenceMonth('<?php echo e($bar['value']); ?>')"
                class="flex h-full w-full flex-col items-center justify-end text-left transition-transform duration-200 hover:-translate-y-1 focus:outline-none"
              >
                <div class="attendance-bar-track">
                  <div class="attendance-bar <?php echo e($bar['active'] ? 'attendance-bar-active' : ''); ?> <?php echo e($bar['is_peak'] ? 'attendance-bar-peak' : ''); ?>" style="height: <?php echo e($bar['height']); ?>;"></div>
                </div>
                <span class="attendance-bar-label <?php echo e($bar['active'] ? 'attendance-bar-label-active' : ''); ?> block"><?php echo e($bar['label']); ?></span>
                <span class="attendance-bar-count block"><?php echo e($bar['count']); ?></span>
              </button>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
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
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="font-semibold text-slate-900"><?php echo e($employee['nombre']); ?></p>
                <p class="mt-1 text-sm text-slate-500"><?php echo e($employee['sucursal']); ?> | <?php echo e($employee['dias_tarde']); ?> dias tarde | <?php echo e($employee['retraso']); ?></p>
              </div>
              <button type="button" wire:click="openEmployeeDetailModal(<?php echo e($employee['empleado_id']); ?>)" class="table-action-button">Ver detalle</button>
            </div>
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

</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/reportes.blade.php ENDPATH**/ ?>