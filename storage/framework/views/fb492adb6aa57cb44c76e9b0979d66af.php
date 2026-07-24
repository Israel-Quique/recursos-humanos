<div class="page-stack">
  <section class="surface-card">
    <p class="section-kicker">Modulo de integracion</p>
    <h3 class="section-title">Carga de Planilla Biometrica</h3>
    <p class="section-copy-sm max-w-3xl">
      Cargue el archivo de reporte generado por el reloj biometrico de Correos de Bolivia para sincronizar
      las marcas de ingreso y salida.
    </p>

    <form wire:submit="importFile" class="mt-8">
      <label class="upload-dropzone">
        <input
          type="file"
          wire:model="archivo"
          class="upload-dropzone-input"
          accept=".xls,.xlsx,.csv"
        >

        <div class="upload-badge">
          <svg viewBox="0 0 48 48" aria-hidden="true" class="upload-badge-icon">
            <path d="M14 6h14l10 10v22a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4Z" fill="currentColor" opacity=".14"/>
            <path d="M28 6v10h10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 30h14M17 24h7M17 18h7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>

        <h4 class="upload-title">Arrastra aqui tu archivo o haz clic para elegirlo</h4>
        <p class="upload-copy">Admite archivos `.xls`, `.xlsx` y `.csv` del biometrico para generar asistencias reales.</p>

        <div class="upload-actions">
          <span class="upload-action-button">Seleccionar archivo</span>
          <span class="upload-hint">Tambien puedes soltar el Excel directamente en esta zona.</span>
        </div>

        <span class="upload-format">
          <?php echo e($archivo ? $archivo->getClientOriginalName() : 'Formato sugerido: planilla_asistencia_2026.xlsx'); ?>

        </span>

        <span wire:loading wire:target="archivo" class="upload-loading">Cargando archivo...</span>
      </label>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['archivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error mt-3"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="login-submit max-w-[22rem]" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="importFile">Importar y generar registros</span>
          <span wire:loading wire:target="importFile">Procesando archivo...</span>
        </button>
      </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary): ?>
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="metric-card">
          <p class="metric-label">Registros creados</p>
          <strong class="metric-value"><?php echo e($summary['registros_generados'] ?? 0); ?></strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Registros actualizados</p>
          <strong class="metric-value"><?php echo e($summary['registros_actualizados'] ?? 0); ?></strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Empleados detectados</p>
          <strong class="metric-value"><?php echo e($summary['empleados_detectados'] ?? 0); ?></strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Olvidos de marcacion</p>
          <strong class="metric-value"><?php echo e($summary['olvidos_marcacion'] ?? 0); ?></strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Marcas omitidas</p>
          <strong class="metric-value"><?php echo e($summary['marcas_omitidas'] ?? 0); ?></strong>
        </div>
      </div>

      <p class="section-copy-sm mt-4">
        El sistema solo contabiliza asistencias de personal previamente registrado. Si llega una marca de alguien no registrado, se omite hasta que RRHH cree primero a esa persona.
      </p>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($summary['empleados_no_registrados'])): ?>
        <div class="device-alert-box mt-6">
          <p class="device-alert-title">Personal detectado que aun no fue registrado en RRHH</p>
          <div class="device-alert-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $summary['empleados_no_registrados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleadoNoRegistrado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span class="device-alert-pill"><?php echo e($empleadoNoRegistrado); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Monitoreo por IP</p>
        <h3 class="section-title">Estado de conexion de biometricos</h3>
        <p class="section-copy-sm">Cuando un biometrico este conectado, sus asistencias podran registrarse directo en el sistema.</p>
      </div>
      <div class="history-pill">
        <span class="hero-status-icon"></span>
        <span><?php echo e(collect($connections)->where('connected', true)->count()); ?> equipos conectados</span>
      </div>
    </div>

    <div class="device-grid">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $connections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="device-card <?php echo e($device['connected'] ? 'device-card-online' : 'device-card-offline'); ?>">
          <div class="device-card-head">
            <div>
              <p class="device-card-kicker"><?php echo e($device['department']); ?></p>
              <h4 class="device-card-title"><?php echo e($device['branch']); ?></h4>
            </div>
            <span class="status-badge <?php echo e($device['connected'] ? 'status-available' : 'status-danger'); ?>">
              <?php echo e($device['connected'] ? 'Conectado' : 'Desconectado'); ?>

            </span>
          </div>
          <div class="device-card-meta">
            <span>IP: <strong><?php echo e($device['ip']); ?></strong></span>
            <span><?php echo e($device['last_sync']); ?></span>
          </div>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="device-alert-box">
      <p class="device-alert-title">Sucursales sin conexion directa</p>
      <div class="device-alert-list">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($connections)->where('connected', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <span class="device-alert-pill"><?php echo e($device['branch']); ?> - <?php echo e($device['department']); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <h3 class="section-title">Historial de Importaciones</h3>
        <p class="section-copy-sm">Ultimas cargas procesadas por el modulo de sincronizacion.</p>
      </div>
      <div class="history-pill">
        <span class="hero-status-icon"></span>
        <span><?php echo e(count($history)); ?> archivos listos</span>
      </div>
    </div>

    <div class="history-table-shell">
      <table class="history-table">
        <thead>
          <tr>
            <th>Archivo</th>
            <th>Registros</th>
            <th>Fecha de carga</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($row['file']); ?></td>
              <td><?php echo e($row['records']); ?></td>
              <td><?php echo e($row['date']); ?></td>
              <td><span class="status-badge <?php echo e($row['status'] === 'Completado' ? 'status-available' : ($row['status'] === 'Error' ? 'status-danger' : 'status-info')); ?>"><?php echo e($row['status']); ?></span></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="4" class="text-center text-slate-400">Todavia no existen importaciones reales procesadas.</td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\importar-excel.blade.php ENDPATH**/ ?>