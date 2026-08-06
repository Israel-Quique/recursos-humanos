<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Control administrativo</p>
        <h3 class="section-title">Auditoria del sistema</h3>
        <p class="section-copy-sm">Revisa quien creo, edito, elimino o cambio accesos dentro del sistema.</p>
      </div>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-4">
        <div class="space-y-2">
          <label class="form-label">Buscar</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Modulo, actor o descripcion">
        </div>
        <div class="space-y-2">
          <label class="form-label">Modulo</label>
          <select wire:model.live="moduloFiltro" class="form-input">
            <option value="">Todos</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $modulos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($modulo); ?>"><?php echo e($modulo); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Accion</label>
          <select wire:model.live="accionFiltro" class="form-input">
            <option value="">Todas</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $acciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($accion); ?>"><?php echo e(\Illuminate\Support\Str::headline($accion)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Usuario</label>
          <select wire:model.live="actorFiltro" class="form-input">
            <option value="">Todos</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $actores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($actor->id); ?>"><?php echo e($actor->name); ?> - <?php echo e($actor->email); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
        </div>
      </div>

      <table class="history-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Modulo</th>
            <th>Accion</th>
            <th>Descripcion</th>
            <th>Detalle</th>
            <th>Control</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($auditoria->created_at?->format('d/m/Y H:i') ?? 'Sin fecha'); ?></td>
              <td><?php echo e($auditoria->actor?->name ?? 'Sistema'); ?></td>
              <td><?php echo e($auditoria->modulo); ?></td>
              <td>
                <span class="status-badge <?php echo e($auditoria->accion === 'eliminar' ? 'status-danger' : ($auditoria->accion === 'editar' || $auditoria->accion === 'cambiar_rol' ? 'status-warning' : 'status-info')); ?>">
                  <?php echo e(\Illuminate\Support\Str::headline($auditoria->accion)); ?>

                </span>
              </td>
              <td><?php echo e($auditoria->descripcion); ?></td>
              <td class="audit-detail-cell">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditoria->antes): ?>
                  <div class="audit-detail-block">
                    <strong>Antes:</strong>
                    <pre><?php echo e(json_encode($auditoria->antes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></pre>
                  </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditoria->despues): ?>
                  <div class="audit-detail-block">
                    <strong>Despues:</strong>
                    <pre><?php echo e(json_encode($auditoria->despues, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></pre>
                  </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </td>
              <td class="table-actions-cell audit-control-cell">
                <div class="table-actions-group">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUndo($auditoria)): ?>
                    <button type="button" wire:click="deshacerAccion(<?php echo e($auditoria->id); ?>)" class="table-action-button table-action-button-danger">
                      Deshacer
                    </button>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canRedo($auditoria)): ?>
                    <button type="button" wire:click="rehacerAccion(<?php echo e($auditoria->id); ?>)" class="table-action-button">
                      Rehacer
                    </button>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->canUndo($auditoria) && ! $this->canRedo($auditoria)): ?>
                    <span class="text-xs text-slate-400">Sin accion</span>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="text-center text-slate-400">Todavia no hay registros de auditoria.</td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditorias->hasPages()): ?>
      <div class="table-pagination-shell">
        <div class="table-pagination-bar">
          <p class="table-pagination-copy">
            Mostrando <?php echo e($auditorias->firstItem()); ?> a <?php echo e($auditorias->lastItem()); ?> de <?php echo e($auditorias->total()); ?> registros
          </p>

          <div class="table-pagination-actions">
            <button
              type="button"
              wire:click="previousPage"
              <?php if($auditorias->onFirstPage()): echo 'disabled'; endif; ?>
              class="table-pagination-button <?php echo e($auditorias->onFirstPage() ? 'table-pagination-button-disabled' : ''); ?>"
            >
              Anterior
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $auditorias->currentPage() - 2), min($auditorias->lastPage(), $auditorias->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <button
                type="button"
                wire:click="gotoPage(<?php echo e($page); ?>)"
                class="table-pagination-button <?php echo e($page === $auditorias->currentPage() ? 'table-pagination-button-active' : ''); ?>"
              >
                <?php echo e($page); ?>

              </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button
              type="button"
              wire:click="nextPage"
              <?php if(! $auditorias->hasMorePages()): echo 'disabled'; endif; ?>
              class="table-pagination-button <?php echo e(! $auditorias->hasMorePages() ? 'table-pagination-button-disabled' : ''); ?>"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/auditoria.blade.php ENDPATH**/ ?>