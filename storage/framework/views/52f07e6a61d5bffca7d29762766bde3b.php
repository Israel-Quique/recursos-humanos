<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteModal): ?>
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar personal</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar este registro del personal? Esta accion tambien quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong><?php echo e($pendingDeleteEmpleadoNombre); ?></strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteEmpleado" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteRegistroModal): ?>
    <div class="app-modal-backdrop" wire:click="closeDeleteRegistroModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar marcacion</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta marcacion? Esta accion tambien quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong><?php echo e($pendingDeleteRegistroLabel); ?></strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteRegistroAsistencia" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreateModal): ?>
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Alta de personal</p>
            <h3 class="section-title app-modal-title">Registrar nuevo integrante</h3>
            <p class="section-copy-sm">Guarda nombre, apellido, codigo biometrico, fecha de nacimiento, area y sucursal del personal.</p>
          </div>
        </div>

        <form wire:submit="saveEmpleado" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Nombre del personal</label>
            <input type="text" wire:model="nombre" class="form-input" placeholder="Ej. Maria">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Apellido</label>
            <input type="text" wire:model="apellido" class="form-input" placeholder="Ej. Perez">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['apellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Codigo biometrico</label>
            <input type="text" wire:model="codigoBiometrico" class="form-input" placeholder="Ej. 1045">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['codigoBiometrico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Area</label>
            <input type="text" wire:model="area" class="form-input" placeholder="Opcional">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Sucursal</label>
            <input type="text" wire:model="sucursal" class="form-input" placeholder="Ej. La Paz">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sucursal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" wire:model="fechaNacimiento" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaNacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Registrar personal</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Editar personal</p>
            <h3 class="section-title app-modal-title">Actualizar datos del integrante</h3>
            <p class="section-copy-sm">Modifica nombre, codigo biometrico, fecha de nacimiento, area y sucursal del personal.</p>
          </div>
          <button type="button" wire:click="closeEditModal" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        <form wire:submit="updateEmpleado" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Nombre del personal</label>
            <input type="text" wire:model="editNombre" class="form-input" placeholder="Ej. Maria">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Apellido</label>
            <input type="text" wire:model="editApellido" class="form-input" placeholder="Ej. Perez">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editApellido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Codigo biometrico</label>
            <input type="text" wire:model="editCodigoBiometrico" class="form-input" placeholder="Ej. 1045">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editCodigoBiometrico'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Area</label>
            <input type="text" wire:model="editArea" class="form-input" placeholder="Opcional">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editArea'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Sucursal</label>
            <input type="text" wire:model="editSucursal" class="form-input" placeholder="Ej. La Paz">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editSucursal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" wire:model="editFechaNacimiento" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editFechaNacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeEditModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDetailModal): ?>
    <div class="app-modal-backdrop" wire:click="closeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Ficha del personal</p>
            <h3 class="section-title app-modal-title"><?php echo e($detailEmpleado['nombre_completo'] ?? 'Detalle del personal'); ?></h3>
            <p class="section-copy-sm">Resumen del perfil, horario regional asignado y detalle mensual de marcaciones.</p>
          </div>
          <div class="flex flex-col gap-3 md:items-end">
            <div class="w-full min-w-[16rem] md:w-auto">
              <label for="detail-reference-month" class="form-label">Filtrar por mes</label>
              <select id="detail-reference-month" wire:model.live="detailReferenceMonth" class="form-input min-w-[16rem]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailMonthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($option['value']); ?>"><?php echo e($option['label']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-5 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          Mostrando resumen mensual de:
          <strong class="text-slate-900"><?php echo e($detailEmpleado['mes_referencia'] ?? '-'); ?></strong>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          <div class="metric-card metric-card-detail">
            <p class="metric-label">Codigo</p>
            <strong class="metric-value metric-value-detail"><?php echo e($detailEmpleado['codigo_biometrico'] ?? 'Sin asignar'); ?></strong>
          </div>
          <div class="metric-card metric-card-detail">
            <p class="metric-label">Sucursal</p>
            <strong class="metric-value metric-value-detail"><?php echo e($detailEmpleado['sucursal'] ?? 'Sin sucursal'); ?></strong>
          </div>
          <div class="metric-card metric-card-detail">
            <p class="metric-label">Horario</p>
            <strong class="metric-value metric-value-detail"><?php echo e($detailEmpleado['hora_entrada_programada'] ?? '--:--'); ?> - <?php echo e($detailEmpleado['hora_salida_programada'] ?? '--:--'); ?></strong>
          </div>
          <div class="metric-card metric-card-detail">
            <p class="metric-label">Dias tarde</p>
            <strong class="metric-value metric-value-detail"><?php echo e($detailEmpleado['dias_tarde'] ?? 0); ?></strong>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
          <div class="detail-info-card">
            <p class="metric-label">Area</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['area'] ?? 'Sin area'); ?></p>
          </div>
          <div class="detail-info-card">
            <p class="metric-label">Nacimiento</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['fecha_nacimiento'] ?? 'Sin fecha'); ?></p>
          </div>
          <div class="detail-info-card">
            <p class="metric-label">Horas del mes</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['horas_mes'] ?? '00:00'); ?></p>
          </div>
          <div class="detail-info-card">
            <p class="metric-label">Retraso del mes</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['retraso_mes'] ?? '0 min'); ?></p>
          </div>
          <div class="detail-info-card">
            <p class="metric-label">Olvidos del mes</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['olvidos_marcacion'] ?? 0); ?></p>
          </div>
          <div class="detail-info-card">
            <p class="metric-label">Saldo de tolerancia</p>
            <p class="detail-info-value"><?php echo e($detailEmpleado['saldo_mes'] ?? '0 min'); ?></p>
          </div>
        </div>

        <div class="detail-marking-filter-row">
          <button
            type="button"
            wire:click="setDetailMarkingFilter('salida')"
            class="detail-marking-filter-button <?php echo e($detailMarkingFilter === 'salida' ? 'detail-marking-filter-button-active' : ''); ?>"
          >
            Salida
          </button>
          <button
            type="button"
            wire:click="setDetailMarkingFilter('entrada')"
            class="detail-marking-filter-button <?php echo e($detailMarkingFilter === 'entrada' ? 'detail-marking-filter-button-active' : ''); ?>"
          >
            Entrada
          </button>
        </div>

        <div class="mt-8">
          <div class="section-head-row">
            <div>
              <p class="section-kicker">Marcaciones del mes</p>
              <h4 class="section-title text-2xl">Detalle de Marcados</h4>
            </div>
          </div>

          <div class="history-table-shell mt-4">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Dia</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Retraso</th>
                  <th>Estado</th>
                  <th>Biometrico</th>
                </tr>
              </thead>
              <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detailEmpleado['marcaciones_mes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tardanza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($tardanza['fecha']); ?></td>
                    <td><?php echo e($tardanza['dia']); ?></td>
                    <td><?php echo e($tardanza['entrada']); ?></td>
                    <td><?php echo e($tardanza['salida']); ?></td>
                    <td><?php echo e($tardanza['retraso']); ?></td>
                    <td><?php echo e($tardanza['estado']); ?></td>
                    <td><?php echo e($tardanza['estado_biometrico']); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="7" class="text-center text-slate-400">No se registraron marcaciones en el mes de referencia.</td>
                  </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPdfModal): ?>
    <div class="app-modal-backdrop" wire:click="closePdfModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Exportacion del personal</p>
            <h3 class="section-title app-modal-title">Ficha lista para PDF</h3>
            <p class="section-copy-sm">Revisa la informacion consolidada del personal y usa el boton de PDF para guardarla o imprimirla.</p>
          </div>
          <div class="flex flex-col gap-3 md:items-end">
            <div class="w-full min-w-[16rem] md:w-auto">
              <label for="pdf-reference-month" class="form-label">Seleccionar mes</label>
              <select id="pdf-reference-month" wire:model.live="pdfReferenceMonth" class="form-input min-w-[16rem]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pdfMonthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($option['value']); ?>"><?php echo e($option['label']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </select>
            </div>
            <div class="app-modal-actions">
              <button type="button" wire:click="descargarPdfEmpleado" class="table-action-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
                </svg>
                <span>PDF</span>
              </button>
              <button type="button" wire:click="closePdfModal" class="app-modal-close" aria-label="Cerrar modal">X</button>
            </div>
          </div>
        </div>

        <div id="employee-pdf-content" class="pdf-export-sheet mt-8 space-y-8">
          <header class="pdf-export-header">
            <div>
              <p class="pdf-export-kicker">Correos de Bolivia</p>
              <h2 class="pdf-export-title">Ficha individual del personal</h2>
              <p class="pdf-export-copy">Resumen operativo del perfil, horario, consumo de tolerancia y registro de tardanzas.</p>
            </div>
            <div class="pdf-export-badge">
              <span class="pdf-export-badge-label">Mes</span>
              <strong class="pdf-export-badge-value"><?php echo e($pdfEmpleado['mes_referencia'] ?? '-'); ?></strong>
            </div>
          </header>

          <div class="pdf-export-grid pdf-export-grid-primary">
            <div class="pdf-export-card pdf-export-card-highlight">
              <p class="pdf-export-label">Nombre</p>
              <strong class="pdf-export-value"><?php echo e($pdfEmpleado['nombre_completo'] ?? 'Sin nombre'); ?></strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Codigo</p>
              <strong class="pdf-export-value"><?php echo e($pdfEmpleado['codigo_biometrico'] ?? 'Sin asignar'); ?></strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Sucursal</p>
              <strong class="pdf-export-value"><?php echo e($pdfEmpleado['sucursal'] ?? 'Sin sucursal'); ?></strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Estado mensual</p>
              <strong class="pdf-export-value"><?php echo e($pdfEmpleado['estado_retraso'] ?? 'Sin estado'); ?></strong>
            </div>
          </div>

          <div class="pdf-export-grid pdf-export-grid-secondary">
            <div class="pdf-export-card">
              <p class="pdf-export-label">Area</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['area'] ?? 'Sin area'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Contratacion</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['fecha_contratacion'] ?? 'Sin fecha'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Nacimiento</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['fecha_nacimiento'] ?? 'Sin fecha'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Despido</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['fecha_despido'] ?? 'Activo'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Horario regional</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['hora_entrada_programada'] ?? '--:--'); ?> - <?php echo e($pdfEmpleado['hora_salida_programada'] ?? '--:--'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Horas del mes</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['horas_mes'] ?? '00:00'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Retraso del mes</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['retraso_mes'] ?? '0 min'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Olvidos del mes</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['olvidos_marcacion'] ?? 0); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Saldo de tolerancia</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['saldo_mes'] ?? '0 min'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Tolerancia mensual</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['tolerancia_mensual'] ?? '0 min'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Dias tarde</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['dias_tarde'] ?? 0); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Entrada hoy</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['entrada_hoy'] ?? '--:--'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Salida hoy</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['salida_hoy'] ?? '--:--'); ?></p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Verificacion hoy</p>
              <p class="pdf-export-value-sm"><?php echo e($pdfEmpleado['verificacion_hoy'] ?? 'Sin registro'); ?></p>
            </div>
          </div>

          <div class="history-table-shell pdf-export-table-shell">
            <div class="section-head-row pdf-export-section-head">
              <div>
                <p class="section-kicker">Marcaciones del mes</p>
                <h4 class="section-title text-2xl">Detalle para exportacion</h4>
              </div>
            </div>

            <table class="history-table mt-4">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Dia</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Retraso</th>
                  <th>Estado</th>
                  <th>Biometrico</th>
                </tr>
              </thead>
              <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($pdfEmpleado['marcaciones_mes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tardanza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($tardanza['fecha']); ?></td>
                    <td><?php echo e($tardanza['dia']); ?></td>
                    <td><?php echo e($tardanza['entrada']); ?></td>
                    <td><?php echo e($tardanza['salida']); ?></td>
                    <td><?php echo e($tardanza['retraso']); ?></td>
                    <td><?php echo e($tardanza['estado']); ?></td>
                    <td><?php echo e($tardanza['estado_biometrico']); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="7" class="text-center text-slate-400">No existen marcaciones registradas para exportar.</td>
                  </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vista === 'personal'): ?>
  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Personal registrado</p>
          <h3 class="section-title">Plantilla activa de RRHH</h3>
          <p class="section-copy-sm">Resumen calculado con las marcaciones de <?php echo e($mes_resumen); ?>.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar personal</button>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-2">
          <div class="space-y-2">
            <label for="personal-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="personal-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Escribe un codigo, nombre o apellido"
              autocomplete="off"
            >
          </div>
          <div class="space-y-2">
            <label for="personal-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="personal-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursalOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sucursalOption); ?>"><?php echo e($sucursalOption); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Sucursal</th>
              <th>Codigo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($empleado->nombre_completo); ?></td>
                <td><?php echo e($empleado->sucursal); ?></td>
                <td><?php echo e($empleado->codigo_biometrico ?: 'Sin asignar'); ?></td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button
                      type="button"
                      wire:click="openDetailModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button"
                      aria-label="Ver detalle del personal"
                      title="Ver detalle"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Ver</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openEditModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button"
                      aria-label="Editar personal"
                      title="Editar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                      </svg>
                      <span>Editar</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openPdfModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button"
                      aria-label="Exportar ficha del personal en PDF"
                      title="PDF"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h8M8 17h5"/>
                      </svg>
                      <span>PDF</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openDeleteModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button table-action-button-danger"
                      aria-label="Eliminar personal"
                      title="Eliminar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                      </svg>
                      <span>Eliminar</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="4" class="text-center text-slate-400">Todavia no hay personal registrado.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empleados->hasPages()): ?>
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando <?php echo e($empleados->firstItem()); ?> a <?php echo e($empleados->lastItem()); ?> de <?php echo e($empleados->total()); ?> registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                <?php if($empleados->onFirstPage()): echo 'disabled'; endif; ?>
                class="table-pagination-button <?php echo e($empleados->onFirstPage() ? 'table-pagination-button-disabled' : ''); ?>"
              >
                Anterior
              </button>

              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $empleados->currentPage() - 2), min($empleados->lastPage(), $empleados->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                  type="button"
                  wire:click="gotoPage(<?php echo e($page); ?>)"
                  class="table-pagination-button <?php echo e($page === $empleados->currentPage() ? 'table-pagination-button-active' : ''); ?>"
                >
                  <?php echo e($page); ?>

                </button>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

              <button
                type="button"
                wire:click="nextPage"
                <?php if(! $empleados->hasMorePages()): echo 'disabled'; endif; ?>
                class="table-pagination-button <?php echo e(! $empleados->hasMorePages() ? 'table-pagination-button-disabled' : ''); ?>"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </article>
  </section>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vista === 'marcaciones'): ?>
  <section>
    <article class="surface-card">
      <div class="section-head-row section-head-row-spacious">
        <div class="history-panel-intro">
          <p class="section-kicker">Dias marcados</p>
          <h3 class="section-title">Historial reciente de marcaciones</h3>
          <p class="section-copy-sm">Lista de los ultimos dias en los que se registro una marcacion, con fecha y dia.</p>
        </div>
      </div>

      <div class="history-table-shell">
        <div class="history-filters-grid md:grid-cols-3">
          <div>
            <label for="marcaciones-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="marcaciones-search"
              type="search"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Ej. 10909669 o ABEL ROJAS"
            >
          </div>
          <div>
            <label for="marcaciones-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="marcaciones-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sucursal); ?>"><?php echo e($sucursal); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
          <div>
            <label for="marcaciones-orden" class="form-label">Ordenar por</label>
            <select id="marcaciones-orden" wire:model.live="ordenMarcaciones" class="form-input">
              <option value="fecha_reciente">Fecha mas reciente</option>
              <option value="fecha_antigua">Fecha mas antigua</option>
              <option value="hora_asc">Hora mas temprana</option>
              <option value="hora_desc">Hora mas tarde</option>
              <option value="nombre_asc">Empleado A-Z</option>
              <option value="nombre_desc">Empleado Z-A</option>
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Empleado</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th>Dia</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($registro->empleado?->nombre_completo ?? 'Sin empleado'); ?></td>
                <td><?php echo e($registro->empleado?->sucursal ?? 'Sin sucursal'); ?></td>
                <td><?php echo e($registro->fecha_formateada ?? 'Sin fecha'); ?></td>
                <td><?php echo e(ucfirst($registro->dia ?? 'Sin dia')); ?></td>
                <td><?php echo e($registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--'); ?></td>
                <td><?php echo e($registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--'); ?></td>
                <td><?php echo e($registro->estado_marcacion ?? 'Sin registro'); ?></td>
                <td class="table-actions-cell">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registro->empleado): ?>
                    <div class="table-actions-group">
                      <button
                        type="button"
                        wire:click="openDetailModal(<?php echo e($registro->empleado->id); ?>)"
                        class="table-action-button"
                        title="Ver perfil del personal"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                          <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span>Ver</span>
                      </button>
                      <button
                        type="button"
                        wire:click="openEditModal(<?php echo e($registro->empleado->id); ?>)"
                        class="table-action-button"
                        title="Editar personal"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                        </svg>
                        <span>Editar</span>
                      </button>
                      <button
                        type="button"
                        wire:click="openDeleteRegistroModal(<?php echo e($registro->id); ?>)"
                        class="table-action-button table-action-button-danger"
                        title="Eliminar marcacion"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                        </svg>
                        <span>Eliminar</span>
                      </button>
                    </div>
                  <?php else: ?>
                    <span class="text-slate-400">Sin acciones</span>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="8" class="text-center text-slate-400">No hay marcaciones recientes para mostrar.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registros->hasPages()): ?>
    <div class="table-pagination-shell mt-4">
      <div class="table-pagination-bar">
        <p class="table-pagination-copy">
          Mostrando <?php echo e($registros->firstItem()); ?> a <?php echo e($registros->lastItem()); ?> de <?php echo e($registros->total()); ?> marcaciones
        </p>

        <div class="table-pagination-actions">
          <button
            type="button"
            wire:click="previousPage('registrosPage')"
            <?php if($registros->onFirstPage()): echo 'disabled'; endif; ?>
            class="table-pagination-button <?php echo e($registros->onFirstPage() ? 'table-pagination-button-disabled' : ''); ?>"
          >
            Anterior
          </button>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $registros->currentPage() - 2), min($registros->lastPage(), $registros->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
              type="button"
              wire:click="gotoPage(<?php echo e($page); ?>, 'registrosPage')"
              class="table-pagination-button <?php echo e($page === $registros->currentPage() ? 'table-pagination-button-active' : ''); ?>"
            >
              <?php echo e($page); ?>

            </button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <button
            type="button"
            wire:click="nextPage('registrosPage')"
            <?php if(! $registros->hasMorePages()): echo 'disabled'; endif; ?>
            class="table-pagination-button <?php echo e(! $registros->hasMorePages() ? 'table-pagination-button-disabled' : ''); ?>"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vista === 'control'): ?>
  <section>
    <article class="surface-card">
      <div class="history-panel-intro">
        <p class="section-kicker">Control mensual</p>
        <h3 class="section-title">Horas y consumo de tolerancia</h3>
        <p class="section-copy-sm">
          Regla aplicada: se usa el horario regional de cada sucursal, una tolerancia maxima de 35 minutos por mes y tambien se respetan los feriados o jornadas especiales programadas.
        </p>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="history-filters-grid md:grid-cols-2">
          <div>
            <label for="control-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="control-search"
              type="search"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Ej. 10909669 o ABEL ROJAS"
            >
          </div>
          <div>
            <label for="control-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="control-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sucursal); ?>"><?php echo e($sucursal); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Horas acumuladas</th>
              <th>Minutos usados</th>
              <th>Saldo mensual</th>
              <th>Tolerancia</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($empleado->nombre_completo); ?></td>
                <td><?php echo e($empleado->sucursal); ?></td>

                <td><?php echo e($empleado->resumen_asistencia['horas_mes']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['retraso_mes_formateado']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['saldo_retraso_formateado']); ?></td>
                <td>
                  <span class="status-badge <?php echo e($empleado->resumen_asistencia['estado_retraso'] === 'Excedido' ? 'status-danger' : 'status-available'); ?>">
                    <?php echo e($empleado->resumen_asistencia['estado_retraso']); ?>

                  </span>
                </td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button
                      type="button"
                      wire:click="openDetailModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button"
                      title="Ver detalle del personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Ver</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openEditModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button"
                      title="Editar personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                      </svg>
                      <span>Editar</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openDeleteModal(<?php echo e($empleado->id); ?>)"
                      class="table-action-button table-action-button-danger"
                      title="Eliminar personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                      </svg>
                      <span>Eliminar</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="text-center text-slate-400">Aun no existen horas acumuladas para mostrar.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/personal.blade.php ENDPATH**/ ?>