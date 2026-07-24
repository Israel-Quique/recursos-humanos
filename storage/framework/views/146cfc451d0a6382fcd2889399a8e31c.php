<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreateModal): ?>
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" wire:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Alta de personal</p>
            <h3 class="section-title app-modal-title">Registrar nuevo integrante</h3>
            <p class="section-copy-sm">Guarda nombre, apellido, codigo biometrico, area, sucursal y vigencia laboral del personal.</p>
          </div>
          <button type="button" wire:click="closeCreateModal" class="app-modal-close" aria-label="Cerrar modal">Cerrar</button>
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
            <label class="form-label">Fecha de contratacion</label>
            <input type="date" wire:model="fechaContratacion" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaContratacion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Fecha de despido</label>
            <input type="date" wire:model="fechaDespido" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaDespido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeCreateModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Registrar personal</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Personal registrado</p>
          <h3 class="section-title">Plantilla activa de RRHH</h3>
          <p class="section-copy-sm">Visualiza la plantilla actual y administra el alta del personal desde un solo punto.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar personal</button>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <table class="history-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Codigo</th>
              <th>Area</th>
              <th>Sucursal</th>
              <th>Horario</th>
              <th>Entrada hoy</th>
              <th>Salida hoy</th>
              <th>Verificacion</th>
              <th>Estado actual</th>
              <th>Horas mes</th>
              <th>Retraso mes</th>
              <th>Saldo</th>
              <th>Vigencia</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($empleado->nombre_completo); ?></td>
                <td><?php echo e($empleado->codigo_biometrico ?: 'Sin asignar'); ?></td>
                <td><?php echo e($empleado->area); ?></td>
                <td><?php echo e($empleado->sucursal); ?></td>
                <td><?php echo e(substr($empleado->hora_entrada_programada, 0, 5)); ?> - <?php echo e($empleado->hora_salida_programada ? substr($empleado->hora_salida_programada, 0, 5) : '--:--'); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['entrada_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['salida_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['verificacion_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['estado_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['horas_mes']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['retraso_mes']); ?> min</td>
                <td><?php echo e($empleado->resumen_asistencia['saldo_retraso']); ?> min</td>
                <td><?php echo e($empleado->fecha_contratacion?->format('d/m/Y')); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empleado->fecha_despido): ?> / <?php echo e($empleado->fecha_despido->format('d/m/Y')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="13" class="text-center text-slate-400">Todavia no hay personal registrado.</td>
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

  <section>
    <article class="surface-card">
      <p class="section-kicker">Control mensual</p>
      <h3 class="section-title">Horas y consumo de tolerancia</h3>
      <p class="section-copy-sm">
        Regla aplicada: ingreso programado a las 08:30, salida desde las 16:30 y una tolerancia maxima de 35 minutos por mes.
      </p>

      <div class="history-table-shell history-table-shell-personal">
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Entrada hoy</th>
              <th>Salida hoy</th>
              <th>Verificacion</th>
              <th>Estado</th>
              <th>Horas acumuladas</th>
              <th>Minutos usados</th>
              <th>Saldo mensual</th>
              <th>Tolerancia</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $empleados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($empleado->nombre_completo); ?></td>
                <td><?php echo e($empleado->sucursal); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['entrada_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['salida_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['verificacion_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['estado_hoy']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['horas_mes']); ?></td>
                <td><?php echo e($empleado->resumen_asistencia['retraso_mes']); ?> min</td>
                <td><?php echo e($empleado->resumen_asistencia['saldo_retraso']); ?> min</td>
                <td>
                  <span class="status-badge <?php echo e($empleado->resumen_asistencia['estado_retraso'] === 'Excedido' ? 'status-danger' : 'status-available'); ?>">
                    <?php echo e($empleado->resumen_asistencia['estado_retraso']); ?>

                  </span>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="10" class="text-center text-slate-400">Aun no existen horas acumuladas para mostrar.</td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\personal.blade.php ENDPATH**/ ?>