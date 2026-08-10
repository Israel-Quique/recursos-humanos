<div class="page-stack">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteModal): ?>
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar incidencia</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta incidencia? Esta accion quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong><?php echo e($pendingDeleteIncidenciaLabel); ?></strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteIncidencia" class="table-action-button table-action-button-danger">Si, eliminar</button>
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
            <p class="section-kicker">Registro operativo</p>
            <h3 class="section-title app-modal-title">Nueva incidencia laboral</h3>
            <p class="section-copy-sm">Registra permisos, incidencias, cumpleanos o faltas por horas, media jornada o dia completo.</p>
          </div>
        </div>

        <form wire:submit="saveIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Personal</label>
            <input
              type="search"
              wire:model.live.debounce.300ms="empleadoSearch"
              class="form-input mb-3"
              placeholder="Buscar por nombre o codigo"
            >
            <select wire:model="empleadoId" class="form-input">
              <option value="">Selecciona</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $empleadosFormulario; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($empleado->id); ?>"><?php echo e($empleado->nombre_completo); ?> | <?php echo e($empleado->sucursal ?: 'Sin sucursal'); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empleadosFormulario->isEmpty()): ?>
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['empleadoId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="tipo" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Alcance</label>
            <select wire:model.live="alcance" class="form-input" <?php if($tipo === 'cumpleanos'): echo 'disabled'; endif; ?>>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $alcances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select wire:model="estado" class="form-input">
              <option value="aprobado">Aprobado</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div>
            <label class="form-label">Fecha inicio</label>
            <input type="date" wire:model="fechaInicio" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaInicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="fechaFin" class="form-input" <?php if($tipo === 'cumpleanos'): echo 'disabled'; endif; ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaFin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alcance === 'horas'): ?>
            <div>
              <label class="form-label">Hora inicio</label>
              <input type="time" wire:model="horaInicio" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['horaInicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
              <label class="form-label">Hora fin</label>
              <input type="time" wire:model="horaFin" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['horaFin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <div class="md:col-span-2">
            <label class="form-label">Motivo / detalle</label>
            <textarea wire:model="motivo" rows="3" class="form-input" placeholder="Ej. permiso por salud, incidencia interna o falta parcial"></textarea>
          </div>
          <div class="md:col-span-2">
            <p class="section-copy-sm">
              Cumpleanos se cuenta hasta medio dia. Feriados y paros se programan en fechas especiales. Las faltas y permisos se contabilizan por horas reales o por bloque segun la seleccion.
            </p>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar incidencia</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Ajuste operativo</p>
            <h3 class="section-title app-modal-title">Editar incidencia</h3>
            <p class="section-copy-sm">Actualiza el bloque de tiempo o el tipo de incidencia segun corresponda.</p>
          </div>
        </div>

        <form wire:submit="updateIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Personal</label>
            <input
              type="search"
              wire:model.live.debounce.300ms="editEmpleadoSearch"
              class="form-input mb-3"
              placeholder="Buscar por nombre o codigo"
            >
            <select wire:model="editEmpleadoId" class="form-input">
              <option value="">Selecciona</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $empleadosEdicion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($empleado->id); ?>"><?php echo e($empleado->nombre_completo); ?> | <?php echo e($empleado->sucursal ?: 'Sin sucursal'); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empleadosEdicion->isEmpty()): ?>
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editEmpleadoId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="editTipo" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Alcance</label>
            <select wire:model.live="editAlcance" class="form-input" <?php if($editTipo === 'cumpleanos'): echo 'disabled'; endif; ?>>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $alcances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select wire:model="editEstado" class="form-input">
              <option value="aprobado">Aprobado</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div>
            <label class="form-label">Fecha inicio</label>
            <input type="date" wire:model="editFechaInicio" class="form-input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editFechaInicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="editFechaFin" class="form-input" <?php if($editTipo === 'cumpleanos'): echo 'disabled'; endif; ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editFechaFin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editAlcance === 'horas'): ?>
            <div>
              <label class="form-label">Hora inicio</label>
              <input type="time" wire:model="editHoraInicio" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editHoraInicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
              <label class="form-label">Hora fin</label>
              <input type="time" wire:model="editHoraFin" class="form-input">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editHoraFin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <div class="md:col-span-2">
            <label class="form-label">Motivo / detalle</label>
            <textarea wire:model="editMotivo" rows="3" class="form-input"></textarea>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Control de novedades</p>
        <h3 class="section-title">Incidencias, permisos y faltas</h3>
        <p class="section-copy-sm">Programa permisos por horas, incidencias por manana o tarde, cumpleanos hasta medio dia y faltas con tiempo contabilizado.</p>
      </div>
      <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar incidencia</button>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-3">
        <div class="space-y-2">
          <label class="form-label">Buscar por nombre o codigo</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Ej. Juana o 123456">
        </div>
        <div class="space-y-2">
          <label class="form-label">Filtrar por tipo</label>
          <select wire:model.live="tipoFiltro" class="form-input">
            <option value="">Todos</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Mes de referencia</label>
          <input type="month" wire:model.live="mesFiltro" class="form-input">
        </div>
      </div>

      <table class="history-table">
        <thead>
          <tr>
            <th>Personal</th>
            <th>Tipo</th>
            <th>Alcance</th>
            <th>Periodo</th>
            <th>Horas contabilizadas</th>
            <th>Estado</th>
            <th>Detalle</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $incidencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($item->empleado?->nombre_completo ?? 'Sin personal'); ?></td>
              <td><?php echo e($item->tipo_label); ?></td>
              <td><?php echo e($item->alcance_label); ?></td>
              <td>
                <?php echo e($item->fecha_inicio?->format('d/m/Y') ?? '--/--/----'); ?>

                -
                <?php echo e($item->fecha_fin?->format('d/m/Y') ?? '--/--/----'); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->hora_inicio && $item->hora_fin): ?>
                  <div class="mt-1 text-xs text-slate-400"><?php echo e(substr($item->hora_inicio, 0, 5)); ?> - <?php echo e(substr($item->hora_fin, 0, 5)); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </td>
              <td><?php echo e(sprintf('%02d:%02d', intdiv((int) ($item->minutos_contabilizados ?? 0), 60), (int) ($item->minutos_contabilizados ?? 0) % 60)); ?></td>
              <td><?php echo e(ucfirst($item->estado)); ?></td>
              <td><?php echo e($item->motivo ?: 'Sin detalle adicional'); ?></td>
              <td class="table-actions-cell">
                <div class="table-actions-group">
                  <button type="button" wire:click="openEditModal(<?php echo e($item->id); ?>)" class="table-action-button">Editar</button>
                  <button type="button" wire:click="openDeleteModal(<?php echo e($item->id); ?>)" class="table-action-button table-action-button-danger">Eliminar</button>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="8" class="text-center text-slate-400">No hay incidencias registradas para el filtro actual.</td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($incidencias->hasPages()): ?>
      <div class="table-pagination-shell">
        <div class="table-pagination-bar">
          <p class="table-pagination-copy">
            Mostrando <?php echo e($incidencias->firstItem()); ?> a <?php echo e($incidencias->lastItem()); ?> de <?php echo e($incidencias->total()); ?> registros
          </p>
          <div class="table-pagination-actions">
            <button type="button" wire:click="previousPage" <?php if($incidencias->onFirstPage()): echo 'disabled'; endif; ?> class="table-pagination-button <?php echo e($incidencias->onFirstPage() ? 'table-pagination-button-disabled' : ''); ?>">Anterior</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $incidencias->currentPage() - 2), min($incidencias->lastPage(), $incidencias->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <button type="button" wire:click="gotoPage(<?php echo e($page); ?>)" class="table-pagination-button <?php echo e($page === $incidencias->currentPage() ? 'table-pagination-button-active' : ''); ?>"><?php echo e($page); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button type="button" wire:click="nextPage" <?php if(! $incidencias->hasMorePages()): echo 'disabled'; endif; ?> class="table-pagination-button <?php echo e(! $incidencias->hasMorePages() ? 'table-pagination-button-disabled' : ''); ?>">Siguiente</button>
          </div>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/incidencias.blade.php ENDPATH**/ ?>