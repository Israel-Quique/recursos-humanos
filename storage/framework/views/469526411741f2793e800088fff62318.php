<div class="page-stack" x-data="{ tab: 'resumen' }">

  
  
  
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEmployeeDetailModal): ?>
    <div class="app-modal-backdrop" wire:click="closeEmployeeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeEmployeeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Detalle mensual</p>
            <h3 class="section-title app-modal-title"><?php echo e($detailEmployeeReport['empleado']['nombre'] ?? 'Detalle del personal'); ?></h3>
            <p class="section-copy-sm">Revision puntual de tardanzas, no marcados y faltas del mes seleccionado.</p>
          </div>
          <div class="app-modal-actions">
            <button type="button" wire:click="descargarPdfDetalleEmpleado" class="table-action-button">
              <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/><path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
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
              <div class="pdf-export-card"><p class="pdf-export-label">Codigo</p><strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo'); ?></strong></div>
              <div class="pdf-export-card"><p class="pdf-export-label">Sucursal</p><strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal'); ?></strong></div>
              <div class="pdf-export-card"><p class="pdf-export-label">Horario</p><strong class="pdf-export-value"><?php echo e($detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--'); ?></strong></div>
            </div>
            <div class="pdf-export-grid pdf-export-grid-secondary">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($detailEmployeeReport['metrics'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pdf-export-card"><p class="pdf-export-label"><?php echo e($metric['label']); ?></p><p class="pdf-export-value-sm"><?php echo e($metric['value']); ?></p></div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head"><div><p class="section-kicker">Tardanzas</p><h3 class="section-title">Dias tarde</h3></div></div>
              <table class="history-table"><thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Retraso</th><th>Estado</th></tr></thead>
                <tbody>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmployeeReport['tardanzas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e($item['fecha']); ?></td><td><?php echo e($item['entrada']); ?></td><td><?php echo e($item['salida']); ?></td><td><?php echo e($item['retraso']); ?></td><td><?php echo e($item['estado']); ?></td></tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-slate-400">No tiene tardanzas en el mes.</td></tr>
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
          <div class="pdf-export-card"><p class="pdf-export-label"><?php echo e($metric['label']); ?></p><strong class="pdf-export-value"><?php echo e($metric['value']); ?></strong><p class="pdf-export-value-sm"><?php echo e($metric['detail']); ?></p></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
      <div class="pdf-export-table-shell">
        <div class="section-head-row pdf-export-section-head"><div><p class="section-kicker">Mayor retraso</p><h3 class="section-title">Personal con mayor retraso del mes</h3></div></div>
        <table class="history-table"><thead><tr><th>Personal</th><th>Sucursal</th><th>Dias tarde</th><th>Retraso</th></tr></thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $monthlyReport['top_employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr><td><?php echo e($employee['nombre']); ?></td><td><?php echo e($employee['sucursal']); ?></td><td><?php echo e($employee['dias_tarde']); ?></td><td><?php echo e($employee['retraso']); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="4" class="text-center text-slate-400">No hay retrasos acumulados en el mes seleccionado.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  
  
  
  <div class="report-hero">
    <div class="report-hero-content">
      <div>
        <p class="report-hero-kicker">Módulo de reportes</p>
        <h1 class="report-hero-title">Reportes de Asistencia</h1>
        <p class="report-hero-copy">Análisis mensual · Sucursal activa: <strong><?php echo e($selectedBranch ?: 'Todas'); ?></strong> · <span class="report-hero-month"><?php echo e($monthLabel); ?></span></p>
      </div>
      <div class="report-hero-actions">
        <button type="button" wire:click="descargarPdfReporte" class="report-hero-pdf-btn">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 16V4"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
          </svg>
          <span>Exportar PDF</span>
        </button>
      </div>
    </div>

    
    <div class="report-filter-bar">
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-reference-month">Periodo</label>
        <input id="hero-reference-month" type="month" wire:model.live="referenceMonth" class="report-filter-input">
      </div>
      <div class="report-filter-field">
        <label class="report-filter-label" for="hero-branch">Sucursal</label>
        <select id="hero-branch" wire:model.live="selectedBranch" class="report-filter-input">
          <option value="">Todas las sucursales</option>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch); ?>"><?php echo e($branch); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
    </div>
  </div>

  
  
  
  <div class="report-tab-nav" role="tablist">
    <button type="button" role="tab" :aria-selected="tab === 'resumen'" @click="tab = 'resumen'"
      :class="tab === 'resumen' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-resumen">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      <span>Resumen</span>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'atrasos'" @click="tab = 'atrasos'"
      :class="tab === 'atrasos' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-atrasos">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
      <span>Atrasos</span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detalleAtrasos) > 0): ?>
        <span class="report-tab-badge report-tab-badge-amber"><?php echo e(count($detalleAtrasos)); ?></span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'omisiones'" @click="tab = 'omisiones'"
      :class="tab === 'omisiones' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-omisiones">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/><circle cx="12" cy="16" r="1.5"/></svg>
      <span>Omisiones</span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detalleOmisiones) > 0): ?>
        <span class="report-tab-badge report-tab-badge-rose"><?php echo e(count($detalleOmisiones)); ?></span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'cumpleanos'" @click="tab = 'cumpleanos'"
      :class="tab === 'cumpleanos' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-cumpleanos">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 3c0 0 1-2 1-2"/></svg>
      <span>Cumpleaños</span>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($cumpleanos) > 0): ?>
        <span class="report-tab-badge report-tab-badge-emerald"><?php echo e(count($cumpleanos)); ?></span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>
    <button type="button" role="tab" :aria-selected="tab === 'ranking'" @click="tab = 'ranking'"
      :class="tab === 'ranking' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-ranking">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20V10"/><path d="M12 20V4"/><path d="M18 20v-6"/></svg>
      <span>Ranking</span>
    </button>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportePersonal): ?>
    <button type="button" role="tab" :aria-selected="tab === 'mi-reporte'" @click="tab = 'mi-reporte'"
      :class="tab === 'mi-reporte' ? 'report-tab-button-active' : ''"
      class="report-tab-button" id="tab-mi-reporte">
      <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      <span>Mi Reporte</span>
    </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  
  
  <div x-show="tab === 'resumen'" x-transition.opacity.duration.200ms role="tabpanel">

    
    <section class="report-kpi-grid">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="report-kpi-card">
          <div class="report-kpi-icon report-kpi-icon-<?php echo e($metric['tone']); ?>"></div>
          <div class="report-kpi-body">
            <p class="report-kpi-label"><?php echo e($metric['label']); ?></p>
            <strong class="report-kpi-value"><?php echo e($metric['value']); ?></strong>
            <p class="report-kpi-detail"><?php echo e($metric['detail']); ?></p>
          </div>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Frecuencias mensuales</p>
          <h2 class="section-title">Marcaciones por mes</h2>
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
                <button type="button" wire:click="selectReferenceMonth('<?php echo e($bar['value']); ?>')"
                  class="flex h-full w-full flex-col items-center justify-end text-left transition-transform duration-200 hover:-translate-y-1 focus:outline-none">
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
          <h3 class="text-lg font-semibold text-slate-900">Personal con mayor retraso del mes</h3>
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
          <h2 class="section-title">Incidencias del mes</h2>
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

  
  
  
  <div x-show="tab === 'atrasos'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Detalle de atrasos</p>
          <h2 class="section-title">Registro de atrasos del mes</h2>
          <p class="section-copy-sm">Todos los registros de llegada tarde en <?php echo e($monthLabel); ?>.</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="status-badge status-warning"><?php echo e(count($detalleAtrasos)); ?> atrasos</span>
          <button type="button" wire:click="descargarPdfReporte" class="section-action-button">PDF</button>
        </div>
      </div>
      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th>Hora prog.</th>
              <th>Hora real</th>
              <th>Retraso</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $detalleAtrasos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="report-atraso-row">
                <td><span class="font-semibold text-slate-800"><?php echo e($item['nombre']); ?></span></td>
                <td><?php echo e($item['sucursal']); ?></td>
                <td><?php echo e($item['fecha']); ?></td>
                <td><?php echo e($item['entrada_programada']); ?></td>
                <td class="font-medium text-rose-700"><?php echo e($item['entrada_real']); ?></td>
                <td><span class="status-badge status-warning"><?php echo e($item['retraso']); ?></span></td>
                <td class="text-xs text-slate-500"><?php echo e($item['estado']); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                  No hay atrasos registrados en el mes seleccionado.
                </td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  
  
  
  <div x-show="tab === 'omisiones'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Olvidos de marcación</p>
          <h2 class="section-title">Omisiones del mes</h2>
          <p class="section-copy-sm">Registros con entrada o salida faltante en <?php echo e($monthLabel); ?>.</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="status-badge status-danger"><?php echo e(count($detalleOmisiones)); ?> omisiones</span>
          <button type="button" wire:click="descargarPdfReporte" class="section-action-button">PDF</button>
        </div>
      </div>
      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
              <th>Detalle</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $detalleOmisiones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="report-omision-row">
                <td><span class="font-semibold text-slate-800"><?php echo e($item['nombre']); ?></span></td>
                <td><?php echo e($item['sucursal']); ?></td>
                <td><?php echo e($item['fecha']); ?></td>
                <td class="<?php echo e(blank($item['entrada'] ?? '') || ($item['entrada'] ?? '') === '--:--' ? 'text-rose-600 font-semibold' : ''); ?>"><?php echo e($item['entrada'] ?? '--:--'); ?></td>
                <td class="<?php echo e(blank($item['salida'] ?? '') || ($item['salida'] ?? '') === '--:--' ? 'text-rose-600 font-semibold' : ''); ?>"><?php echo e($item['salida'] ?? '--:--'); ?></td>
                <td><span class="status-badge status-danger"><?php echo e($item['estado'] ?? 'Sin estado'); ?></span></td>
                <td class="text-xs text-slate-500"><?php echo e($item['detalle'] ?? ''); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/></svg>
                  No hay omisiones de marcacion en el mes seleccionado.
                </td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  
  
  
  <div x-show="tab === 'cumpleanos'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Celebraciones del mes</p>
          <h2 class="section-title">Cumpleaños — <?php echo e($monthLabel); ?></h2>
          <p class="section-copy-sm">Personal que cumple años durante este mes.</p>
        </div>
        <span class="status-badge status-available"><?php echo e(count($cumpleanos)); ?> cumpleañeros</span>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($cumpleanos) > 0): ?>
        
        <?php $hoy = collect($cumpleanos)->where('es_hoy', true); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hoy->count() > 0): ?>
          <div class="birthday-today-banner mt-8">
            <div class="birthday-today-icon">🎂</div>
            <div>
              <p class="birthday-today-label">¡Hoy es el cumpleaños de!</p>
              <p class="birthday-today-names"><?php echo e($hoy->pluck('nombre')->join(', ')); ?></p>
            </div>
          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="birthday-grid mt-8">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cumpleanos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $persona): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="birthday-card <?php echo e($persona['es_hoy'] ? 'birthday-card-today' : ($persona['es_esta_semana'] ? 'birthday-card-week' : '')); ?>">
              <div class="birthday-avatar <?php echo e($persona['es_hoy'] ? 'birthday-avatar-today' : ''); ?>">
                <?php echo e($persona['inicial']); ?>

              </div>
              <div class="birthday-info">
                <p class="birthday-name"><?php echo e($persona['nombre']); ?></p>
                <p class="birthday-meta"><?php echo e($persona['area']); ?> · <?php echo e($persona['sucursal']); ?></p>
                <div class="birthday-date-row">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                  <span><?php echo e($persona['fecha_label']); ?> · <?php echo e($persona['edad']); ?> años</span>
                </div>
              </div>
              <div class="birthday-chips">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($persona['es_hoy']): ?>
                  <span class="birthday-chip birthday-chip-today">Hoy 🎉</span>
                <?php elseif($persona['es_esta_semana']): ?>
                  <span class="birthday-chip birthday-chip-week">Esta semana</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      <?php else: ?>
        <div class="py-16 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl">🎂</div>
          <p class="text-lg font-semibold text-slate-700">Sin cumpleaños este mes</p>
          <p class="mt-2 text-sm text-slate-400">No hay empleados que cumplan años en <?php echo e($monthLabel); ?>.</p>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
  </div>

  
  
  
  <div x-show="tab === 'ranking'" x-transition.opacity.duration.200ms role="tabpanel">

    
    <div class="grid gap-6 xl:grid-cols-2">
      <section class="surface-card">
        <div class="mb-6">
          <p class="section-kicker">Mes · <?php echo e($monthLabel); ?></p>
          <h2 class="section-title">Más puntuales del mes</h2>
          <p class="section-copy-sm">Empleados con menor retraso acumulado.</p>
        </div>
        <div class="ranking-list">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rankingMensual['mas_puntuales'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ranking-card ranking-<?php echo e($i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'))); ?>">
              <div class="ranking-position">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?> <span class="ranking-medal">🥇</span>
                <?php elseif($i === 1): ?> <span class="ranking-medal">🥈</span>
                <?php elseif($i === 2): ?> <span class="ranking-medal">🥉</span>
                <?php else: ?> <span class="ranking-pos-num"><?php echo e($i + 1); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <div class="ranking-avatar"><?php echo e($emp['inicial']); ?></div>
              <div class="ranking-info">
                <p class="ranking-name"><?php echo e($emp['nombre']); ?></p>
                <p class="ranking-meta"><?php echo e($emp['sucursal']); ?> · <?php echo e($emp['dias_marcados']); ?> días marcados</p>
              </div>
              <div class="ranking-stat ranking-stat-green">
                <p class="ranking-stat-label">Retraso</p>
                <strong class="ranking-stat-value"><?php echo e($emp['retraso_label']); ?></strong>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="py-6 text-center text-sm text-slate-400">Sin datos suficientes para el mes seleccionado.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </section>

      <section class="surface-card">
        <div class="mb-6">
          <p class="section-kicker">Mes · <?php echo e($monthLabel); ?></p>
          <h2 class="section-title">Más atrasados del mes</h2>
          <p class="section-copy-sm">Empleados con mayor retraso acumulado.</p>
        </div>
        <div class="ranking-list">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rankingMensual['mas_atrasados'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ranking-card ranking-<?php echo e($i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'))); ?> ranking-card-danger">
              <div class="ranking-position">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?> <span class="ranking-medal">🔴</span>
                <?php elseif($i === 1): ?> <span class="ranking-medal">🟠</span>
                <?php elseif($i === 2): ?> <span class="ranking-medal">🟡</span>
                <?php else: ?> <span class="ranking-pos-num"><?php echo e($i + 1); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <div class="ranking-avatar ranking-avatar-danger"><?php echo e($emp['inicial']); ?></div>
              <div class="ranking-info">
                <p class="ranking-name"><?php echo e($emp['nombre']); ?></p>
                <p class="ranking-meta"><?php echo e($emp['sucursal']); ?> · <?php echo e($emp['dias_tarde']); ?> días tarde</p>
              </div>
              <div class="ranking-stat ranking-stat-red">
                <p class="ranking-stat-label">Retraso</p>
                <strong class="ranking-stat-value"><?php echo e($emp['retraso_label']); ?></strong>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="py-6 text-center text-sm text-slate-400">Sin atrasos registrados en el mes seleccionado.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </section>
    </div>

    
    <div class="grid gap-6 xl:grid-cols-2">
      <section class="surface-card">
        <div class="mb-6">
          <p class="section-kicker">Semana actual</p>
          <h2 class="section-title">Más puntuales de la semana</h2>
          <p class="section-copy-sm">Empleados con menor retraso acumulado esta semana.</p>
        </div>
        <div class="ranking-list">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rankingSemanal['mas_puntuales'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ranking-card ranking-<?php echo e($i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'))); ?>">
              <div class="ranking-position">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?> <span class="ranking-medal">🥇</span>
                <?php elseif($i === 1): ?> <span class="ranking-medal">🥈</span>
                <?php elseif($i === 2): ?> <span class="ranking-medal">🥉</span>
                <?php else: ?> <span class="ranking-pos-num"><?php echo e($i + 1); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <div class="ranking-avatar"><?php echo e($emp['inicial']); ?></div>
              <div class="ranking-info">
                <p class="ranking-name"><?php echo e($emp['nombre']); ?></p>
                <p class="ranking-meta"><?php echo e($emp['sucursal']); ?></p>
              </div>
              <div class="ranking-stat ranking-stat-green">
                <p class="ranking-stat-label">Retraso</p>
                <strong class="ranking-stat-value"><?php echo e($emp['retraso_label']); ?></strong>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="py-6 text-center text-sm text-slate-400">Sin datos suficientes para la semana actual.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </section>

      <section class="surface-card">
        <div class="mb-6">
          <p class="section-kicker">Semana actual</p>
          <h2 class="section-title">Más atrasados de la semana</h2>
          <p class="section-copy-sm">Empleados con mayor retraso acumulado esta semana.</p>
        </div>
        <div class="ranking-list">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rankingSemanal['mas_atrasados'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ranking-card ranking-<?php echo e($i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default'))); ?> ranking-card-danger">
              <div class="ranking-position">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?> <span class="ranking-medal">🔴</span>
                <?php elseif($i === 1): ?> <span class="ranking-medal">🟠</span>
                <?php elseif($i === 2): ?> <span class="ranking-medal">🟡</span>
                <?php else: ?> <span class="ranking-pos-num"><?php echo e($i + 1); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </div>
              <div class="ranking-avatar ranking-avatar-danger"><?php echo e($emp['inicial']); ?></div>
              <div class="ranking-info">
                <p class="ranking-name"><?php echo e($emp['nombre']); ?></p>
                <p class="ranking-meta"><?php echo e($emp['sucursal']); ?> · <?php echo e($emp['dias_tarde']); ?> días tarde</p>
              </div>
              <div class="ranking-stat ranking-stat-red">
                <p class="ranking-stat-label">Retraso</p>
                <strong class="ranking-stat-value"><?php echo e($emp['retraso_label']); ?></strong>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="py-6 text-center text-sm text-slate-400">Sin atrasos registrados esta semana.</p>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </section>
    </div>
  </div>

  
  
  
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportePersonal): ?>
  <div x-show="tab === 'mi-reporte'" x-transition.opacity.duration.200ms role="tabpanel">
    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Reporte personal</p>
          <h2 class="section-title"><?php echo e($authEmpleadoNombre ?? $reportePersonal['empleado']['nombre']); ?></h2>
          <p class="section-copy-sm"><?php echo e($reportePersonal['empleado']['sucursal']); ?> · Horario: <?php echo e($reportePersonal['empleado']['horario']); ?></p>
        </div>
        <span class="status-badge status-info"><?php echo e($monthLabel); ?></span>
      </div>

      
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportePersonal['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="personal-kpi-card">
            <p class="metric-label"><?php echo e($metric['label']); ?></p>
            <p class="mt-3 text-2xl font-bold text-slate-900"><?php echo e($metric['value']); ?></p>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>

      
      <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="rounded-[1.3rem] border border-amber-200 bg-amber-50/40 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Mis atrasos del mes</h3>
            <span class="status-badge status-warning"><?php echo e(count($reportePersonal['tardanzas'])); ?></span>
          </div>
          <div class="report-scroll-list space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportePersonal['tardanzas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <div class="rounded-xl bg-white border border-amber-100 px-4 py-3">
                <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                <p class="mt-1 text-sm text-slate-500">Entrada: <?php echo e($item['entrada']); ?> · Retraso: <strong class="text-amber-700"><?php echo e($item['retraso']); ?></strong></p>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <p class="text-sm text-slate-400">Sin atrasos en el mes. ¡Excelente puntualidad!</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>

        <div class="rounded-[1.3rem] border border-rose-200 bg-rose-50/40 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Mis omisiones del mes</h3>
            <span class="status-badge status-danger"><?php echo e(count($reportePersonal['no_marcados'])); ?></span>
          </div>
          <div class="report-scroll-list space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportePersonal['no_marcados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <div class="rounded-xl bg-white border border-rose-100 px-4 py-3">
                <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                <p class="mt-1 text-sm text-slate-500">Entrada: <?php echo e($item['entrada']); ?> · Salida: <?php echo e($item['salida']); ?></p>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <p class="text-sm text-slate-400">Sin omisiones de marcación. ¡Perfecto registro!</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      </div>

      
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($reportePersonal['faltas']) > 0): ?>
        <div class="mt-6 rounded-[1.3rem] border border-slate-200 bg-slate-50 px-5 py-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-semibold text-slate-900">Faltas registradas</h3>
            <span class="status-badge status-danger"><?php echo e(count($reportePersonal['faltas'])); ?></span>
          </div>
          <div class="report-scroll-list grid gap-3 md:grid-cols-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportePersonal['faltas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="rounded-xl bg-white border border-slate-200 px-4 py-3">
                <p class="font-semibold text-slate-900"><?php echo e($item['fecha']); ?></p>
                <p class="mt-1 text-sm text-slate-500"><?php echo e($item['detalle']); ?></p>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
  </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH C:\Users\WILLIAMS\Desktop\recursos-humanos-master\resources\views/livewire/reportes.blade.php ENDPATH**/ ?>