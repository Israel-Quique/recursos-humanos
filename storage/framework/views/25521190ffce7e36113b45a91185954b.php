<div class="page-stack">
  <section class="grid gap-6 xl:grid-cols-[420px,1fr]">
    <article class="surface-card">
      <p class="section-kicker">Control de accesos</p>
      <h3 class="section-title">Roles de RRHH</h3>
      <p class="section-copy-sm">Administra los roles `admin` y `usuario` para el personal autorizado de recursos humanos.</p>

      <form wire:submit="updateUserRole" class="mt-8 space-y-5">
        <div>
          <label class="form-label">Usuario del sistema</label>
          <select wire:model="selectedUserId" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> - <?php echo e($user->email); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedUserId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
          <label class="form-label">Rol asignado</label>
          <select wire:model="selectedRole" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($role->name); ?>"><?php echo e(\Illuminate\Support\Str::headline($role->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedRole'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="submit" class="login-submit">Guardar rol</button>
      </form>

      <div class="mt-8 rounded-[1.4rem] border border-amber-200 bg-amber-50 px-5 py-5 text-sm text-amber-900">
        <p class="font-semibold">Compatibilidad detectada</p>
        <p class="mt-2">Asignaciones heredadas en `role_user`: <strong><?php echo e($legacyAssignments); ?></strong>. Desde ahora Spatie controlara los accesos nuevos.</p>
      </div>
    </article>

    <article class="surface-card">
      <p class="section-kicker">Resumen operativo</p>
      <h3 class="section-title">Usuarios y permisos activos</h3>

      <div class="mt-8 overflow-hidden rounded-[1.5rem] border border-slate-100">
        <table class="history-table">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol actual</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td><?php echo e($user->name); ?></td>
                <td><?php echo e($user->email); ?></td>
                <td>
                  <span class="status-badge status-info">
                    <?php echo e(\Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? 'sin rol')); ?>

                  </span>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-8 grid gap-5 lg:grid-cols-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleGroup => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-5 py-5">
            <p class="metric-label"><?php echo e($roleGroup === 'sin-asignacion' ? 'Sin asignacion' : 'Rol'); ?></p>
            <h4 class="mt-2 text-lg font-semibold text-slate-900">
              <?php echo e($roleGroup === 'sin-asignacion' ? 'Permisos sin rol' : \Illuminate\Support\Str::headline($roleGroup)); ?>

            </h4>
            <div class="mt-4 flex flex-wrap gap-3">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="status-badge status-available"><?php echo e($permission->name); ?></span>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </article>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views\livewire\gestion-accesos.blade.php ENDPATH**/ ?>