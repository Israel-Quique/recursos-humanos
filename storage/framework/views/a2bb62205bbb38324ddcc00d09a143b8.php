<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
    <div class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
      <?php echo e(session('status')); ?>

    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBiometricoModal): ?>
    <div class="app-modal-backdrop" wire:click="closeBiometricoModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeBiometricoModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Registro de equipos</p>
            <h3 class="section-title app-modal-title"><?php echo e($editingBiometricoId ? 'Editar biometrico' : 'Agregar biometrico'); ?></h3>
            <p class="section-copy-sm">
              <?php echo e($editingBiometricoId ? 'Actualiza la IP, puerto o modo de conexion del biometrico seleccionado.' : 'Registra las IPs, puertos y modo de conexion de La Paz y de los demas departamentos.'); ?>

            </p>
          </div>
        </div>

        <form wire:submit="saveBiometrico" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Departamento</label>
            <input type="text" wire:model="deviceDepartment" class="form-input" placeholder="Ej. La Paz">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deviceDepartment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Sucursal o biometrico</label>
            <input type="text" wire:model="deviceBranch" class="form-input" placeholder="Ej. Oficina Central La Paz">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deviceBranch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">IP</label>
            <input type="text" wire:model="deviceIp" class="form-input" placeholder="Ej. 172.65.14.108">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deviceIp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Puerto</label>
            <input type="number" wire:model="devicePort" class="form-input" min="1" max="65535" placeholder="4370">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['devicePort'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Modo de conexion</label>
            <select wire:model="deviceConnectionMode" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $connectionModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $connectionMode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($connectionMode); ?>"><?php echo e($connectionMode); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deviceConnectionMode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Contrasena de comunicacion</label>
            <input type="text" wire:model="deviceCommunicationPassword" class="form-input" placeholder="Opcional">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deviceCommunicationPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeBiometricoModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">
              <?php echo e($editingBiometricoId ? 'Guardar cambios' : 'Guardar biometrico'); ?>

            </button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteModal): ?>
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar importacion</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta importacion y todos sus registros asociados?</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong><?php echo e($pendingDeleteImportacionNombre); ?></strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteImportacion" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <section class="surface-card relative">
    <div wire:loading wire:target="importFile" class="loading-overlay">
      <div class="loading-spinner" role="status" aria-live="polite" aria-label="Importando archivo">
        <svg class="loading-spinner-icon" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
          <circle class="loading-spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
        </svg>
        <span class="loading-spinner-text">Importando, por favor espera...</span>
      </div>
    </div>

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
          <p class="metric-label">Personal creado</p>
          <strong class="metric-value"><?php echo e($summary['empleados_creados'] ?? 0); ?></strong>
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
        El sistema importa las marcaciones y ahora tambien puede crear automaticamente al personal nuevo usando el codigo, nombre completo y sucursal detectados desde la planilla.
      </p>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($summary['empleados_no_registrados'])): ?>
        <div class="device-alert-box mt-6">
          <p class="device-alert-title">Filas que no pudieron convertirse en personal automaticamente</p>
          <div class="device-alert-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $summary['empleados_no_registrados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleadoNoRegistrado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span class="device-alert-pill"><?php echo e($empleadoNoRegistrado); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>

  <section class="surface-card" wire:poll.30s>
    <div class="history-header">
      <div>
        <p class="section-kicker">Monitoreo por IP</p>
        <h3 class="section-title">Estado de conexion de biometricos</h3>
        <p class="section-copy-sm">Cuando un biometrico este conectado, sus asistencias podran registrarse directo en el sistema.</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="button" wire:click="openBiometricoModal" class="table-action-button">
          Agregar biometrico
        </button>
        <div class="history-pill">
          <span class="hero-status-icon"></span>
          <span><?php echo e(collect($connections)->where('connected', true)->count()); ?> equipos conectados</span>
        </div>
      </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div>
        <label for="export-year" class="form-label">Filtrar por anio</label>
        <select id="export-year" wire:model.live="exportYear" class="form-input">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exportYearOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
      <div>
        <label for="export-month" class="form-label">Filtrar por mes</label>
        <select id="export-month" wire:model.live="exportMonth" class="form-input">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exportMonthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($monthOption['value']); ?>"><?php echo e($monthOption['label']); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
      <div class="md:col-span-2 xl:col-span-2 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Periodo de extraccion:
        <strong class="text-slate-900">
          <?php echo e(collect($exportMonthOptions)->firstWhere('value', $exportMonth)['label'] ?? $exportMonth); ?>/<?php echo e($exportYear); ?>

        </strong>
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
            <span>Puerto: <strong><?php echo e($device['port'] ?? 4370); ?></strong></span>
            <span>Modo: <strong><?php echo e($device['connection_mode'] ?? 'TCP/IP'); ?></strong></span>
            <span><?php echo e($device['last_sync']); ?></span>
          </div>
          <div class="mt-5 flex flex-wrap gap-3">
            <button
              type="button"
              wire:click="probarConexion(<?php echo e($loop->index); ?>)"
              wire:loading.attr="disabled"
              wire:target="probarConexion(<?php echo e($loop->index); ?>)"
              class="table-action-button"
            >
              <span wire:loading.remove wire:target="probarConexion(<?php echo e($loop->index); ?>)">Probar conexion</span>
              <span wire:loading wire:target="probarConexion(<?php echo e($loop->index); ?>)">Probando...</span>
            </button>
            <button
              type="button"
              wire:click="extraerExcel(<?php echo e($loop->index); ?>)"
              wire:loading.attr="disabled"
              wire:target="extraerExcel(<?php echo e($loop->index); ?>)"
              class="table-action-button"
            >
              <span wire:loading.remove wire:target="extraerExcel(<?php echo e($loop->index); ?>)">Extraer Excel</span>
              <span wire:loading wire:target="extraerExcel(<?php echo e($loop->index); ?>)">Extrayendo...</span>
            </button>
            <button
              type="button"
              wire:click="<?php echo e(! empty($device['id']) ? 'openEditBiometricoModal('.$device['id'].')' : 'openEditBiometricoModalByIndex('.$loop->index.')'); ?>"
              class="table-action-button"
            >
              Editar
            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($device['id'])): ?>
              <button
                type="button"
                wire:click="deleteBiometrico(<?php echo e($device['id']); ?>)"
                class="table-action-button table-action-button-danger"
              >
                Eliminar
              </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(collect($connections)->where('connected', false)->isNotEmpty()): ?>
      <div class="device-alert-box">
        <p class="device-alert-title">Sucursales sin conexion directa</p>
        <div class="device-alert-list">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($connections)->where('connected', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="device-alert-pill"><?php echo e($device['branch']); ?> - <?php echo e($device['department']); ?></span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($row['file']); ?></td>
              <td><?php echo e($row['records']); ?></td>
              <td><?php echo e($row['date']); ?></td>
              <td><span class="status-badge <?php echo e($row['status'] === 'Completado' ? 'status-available' : ($row['status'] === 'Error' ? 'status-danger' : 'status-info')); ?>"><?php echo e($row['status']); ?></span></td>
              <td>
                <button
                  type="button"
                  wire:click="openDeleteModal(<?php echo e($row['id']); ?>, '<?php echo e(addslashes($row['file'])); ?>')"
                  class="table-action-button table-action-button-danger"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="5" class="text-center text-slate-400">Todavia no existen importaciones reales procesadas.</td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/importar-excel.blade.php ENDPATH**/ ?>