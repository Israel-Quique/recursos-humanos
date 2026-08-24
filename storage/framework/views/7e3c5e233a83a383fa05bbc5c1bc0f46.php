<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte general de asistencia</title>
    <style>
      body { font-family: DejaVu Sans, sans-serif; color: #0f172a; margin: 28px; font-size: 12px; }
      h1, h2, h3, h4, p { margin: 0; }
      .header { border-bottom: 2px solid #dbe4f0; padding-bottom: 14px; margin-bottom: 18px; }
      .kicker { font-size: 10px; letter-spacing: 0.28em; text-transform: uppercase; color: #2563eb; font-weight: bold; }
      .title { margin-top: 8px; font-size: 26px; font-weight: bold; }
      .copy { margin-top: 8px; color: #475569; line-height: 1.6; }
      .meta { margin-top: 12px; }
      .meta strong { display: inline-block; min-width: 90px; }
      .metrics { width: 100%; border-collapse: separate; border-spacing: 10px; margin: 0 -10px 12px -10px; }
      .card { border: 1px solid #dbe4f0; border-radius: 14px; padding: 12px 14px; background: #fff; vertical-align: top; }
      .label { font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase; color: #64748b; font-weight: bold; }
      .value { margin-top: 8px; font-size: 16px; font-weight: bold; }
      .section { margin-top: 18px; border: 1px solid #dbe4f0; border-radius: 16px; padding: 14px; }
      .section-title { margin-top: 4px; margin-bottom: 12px; font-size: 18px; font-weight: bold; }
      table.data-table { width: 100%; border-collapse: collapse; }
      .data-table th, .data-table td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; vertical-align: top; }
      .data-table th { background: #f8fafc; color: #475569; font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; }
      .data-table tr:last-child td { border-bottom: none; }
      .empty { color: #94a3b8; text-align: center; }
    </style>
  </head>
  <body>
    <div class="header">
      <p class="kicker">Correos de Bolivia</p>
      <h1 class="title">Reporte general de asistencia</h1>
      <p class="copy">Resumen mensual con personal que llego tarde, registros no marcados e incidencias detectadas.</p>
      <div class="meta">
        <p><strong>Mes:</strong> <?php echo e($monthLabel); ?></p>
        <p><strong>Sucursal:</strong> <?php echo e($branchLabel); ?></p>
      </div>
    </div>

    <table class="metrics">
      <tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($report['metrics'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <td class="card">
            <div class="label"><?php echo e($metric['label']); ?></div>
            <div class="value"><?php echo e($metric['value']); ?></div>
          </td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </tr>
    </table>

    <div class="section">
      <p class="kicker">Resumen de atrasos</p>
      <h3 class="section-title">Personal que llego tarde</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Personal</th>
            <th>Sucursal</th>
            <th>Dias tarde</th>
            <th>Retraso acumulado</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($report['resumen_atrasos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($item['nombre']); ?></td>
              <td><?php echo e($item['sucursal']); ?></td>
              <td><?php echo e($item['dias_tarde']); ?></td>
              <td><?php echo e($item['retraso']); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="empty">No hay atrasos registrados en el mes seleccionado.</td></tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <p class="kicker">Detalle de atrasos</p>
      <h3 class="section-title">Marcaciones tardias del mes</h3>
      <table class="data-table">
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
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($report['atrasos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($item['fecha']); ?></td>
              <td><?php echo e($item['nombre']); ?></td>
              <td><?php echo e($item['sucursal']); ?></td>
              <td><?php echo e($item['entrada_programada']); ?></td>
              <td><?php echo e($item['entrada_real']); ?></td>
              <td><?php echo e($item['retraso']); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="empty">No existen atrasos en el rango seleccionado.</td></tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <p class="kicker">No marcados</p>
      <h3 class="section-title">Personal que no marco completamente</h3>
      <table class="data-table">
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
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($report['no_marcados'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($item['fecha']); ?></td>
              <td><?php echo e($item['nombre']); ?></td>
              <td><?php echo e($item['sucursal']); ?></td>
              <td><?php echo e($item['entrada']); ?></td>
              <td><?php echo e($item['salida']); ?></td>
              <td><?php echo e($item['detalle']); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="empty">No hay registros no marcados en el mes seleccionado.</td></tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/pdf/reportes-general.blade.php ENDPATH**/ ?>