<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSucursalEmployeesModal): ?>
    <div class="app-modal-backdrop" wire:click="closeSucursalEmployeesModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeSucursalEmployeesModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Personal por sucursal</p>
            <h3 class="section-title app-modal-title"><?php echo e($selectedSucursal); ?></h3>
            <p class="section-copy-sm">Listado completo del personal asignado a esta sucursal.</p>
          </div>
        </div>

        <div class="history-table-shell mt-8">
          <table class="history-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Codigo</th>
                <th>Area</th>
                <th>Sucursal</th>
              </tr>
            </thead>
            <tbody>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sucursalEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($empleado->nombre_completo); ?></td>
                  <td><?php echo e($empleado->codigo_biometrico ?: 'Sin asignar'); ?></td>
                  <td><?php echo e($empleado->area ?: 'Sin area'); ?></td>
                  <td><?php echo e($empleado->sucursal); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="text-center text-slate-400">No hay personal registrado en esta sucursal.</td>
                </tr>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Horario regional</p>
            <h3 class="section-title app-modal-title">Actualizar entrada y salida</h3>
            <p class="section-copy-sm">Configura el horario general que se aplicara a todo el personal de la sucursal seleccionada.</p>
          </div>
        </div>

        <form wire:submit="saveHorario" class="mt-8 grid gap-5 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label">Sucursal</label>
            <input type="text" value="<?php echo e($editingSucursal); ?>" class="form-input" disabled>
          </div>
          <div>
            <label class="form-label">Hora de entrada</label>
            <input type="time" wire:model="editHoraEntrada" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editHoraEntrada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Hora de salida</label>
            <input type="time" wire:model="editHoraSalida" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editHoraSalida'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar horario</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Gestion operativa</p>
          <h3 class="section-title">Horarios por sucursal</h3>
          <p class="section-copy-sm">Administra una sola hora de entrada y salida para todo el personal de cada regional o sucursal.</p>
        </div>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-1">
          <div class="space-y-2">
            <label for="horario-search" class="form-label">Buscar por sucursal</label>
            <input
              id="horario-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Escribe una sucursal o regional"
              autocomplete="off"
            >
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Sucursal</th>
              <th>Personal asignado</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $horarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $horario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($horario->sucursal); ?></td>
                <td>
                  <button
                    type="button"
                    wire:click="openSucursalEmployeesModal('<?php echo e(str_replace("'", "\\'", $horario->sucursal)); ?>')"
                    class="table-action-button"
                    title="Ver personal de la sucursal"
                  >
                    <?php echo e($horario->empleados); ?>

                  </button>
                </td>
                <td><?php echo e($horario->hora_entrada); ?></td>
                <td><?php echo e($horario->hora_salida); ?></td>
                <td>
                  <button type="button" wire:click="openEditModal('<?php echo e(str_replace("'", "\\'", $horario->sucursal)); ?>')" class="table-action-button">Modificar horario</button>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="5" class="text-center text-slate-400">No hay sucursales disponibles para configurar horarios.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($horarios->hasPages()): ?>
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando <?php echo e($horarios->firstItem()); ?> a <?php echo e($horarios->lastItem()); ?> de <?php echo e($horarios->total()); ?> registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                <?php if($horarios->onFirstPage()): echo 'disabled'; endif; ?>
                class="table-pagination-button <?php echo e($horarios->onFirstPage() ? 'table-pagination-button-disabled' : ''); ?>"
              >
                Anterior
              </button>

              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $horarios->currentPage() - 2), min($horarios->lastPage(), $horarios->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                  type="button"
                  wire:click="gotoPage(<?php echo e($page); ?>)"
                  class="table-pagination-button <?php echo e($page === $horarios->currentPage() ? 'table-pagination-button-active' : ''); ?>"
                >
                  <?php echo e($page); ?>

                </button>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

              <button
                type="button"
                wire:click="nextPage"
                <?php if(! $horarios->hasMorePages()): echo 'disabled'; endif; ?>
                class="table-pagination-button <?php echo e(! $horarios->hasMorePages() ? 'table-pagination-button-disabled' : ''); ?>"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </article>
  </section>
</div>
<?php /**PATH C:\Users\WILLIAMS\Desktop\recursos-humanos-master\resources\views/livewire/horarios.blade.php ENDPATH**/ ?>