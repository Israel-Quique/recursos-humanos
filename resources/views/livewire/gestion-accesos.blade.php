<div class="page-stack">
  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="$set('showCreateModal', false)" wire:keydown.escape.window="$set('showCreateModal', false)" tabindex="-1">
      <div class="app-modal-card" wire:click.stop>
        <button type="button" wire:click.prevent="$set('showCreateModal', false)" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>

        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Registro operativo</p>
            <h3 class="section-title app-modal-title">Crear nuevo usuario</h3>
            <p class="section-copy-sm">Registra un usuario nuevo para que pueda ingresar al sistema.</p>
          </div>
        </div>

        <form wire:submit="createUser" class="mt-6 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Nombre de usuario</label>
            <input type="text" wire:model="name" class="form-input" placeholder="Ej. gestor.oruro">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Correo</label>
            <input type="email" wire:model="email" class="form-input" placeholder="usuario@recursoshumanos.local">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Contrasena</label>
            <input type="password" wire:model="password" class="form-input" placeholder="Minimo 8 caracteres">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Confirmar contrasena</label>
            <input type="password" wire:model="password_confirmation" class="form-input" placeholder="Repite la contrasena">
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Rol inicial</label>
            <select wire:model="newUserRole" class="form-input">
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
              @endforeach
            </select>
            @error('newUserRole') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Crear usuario</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="$set('showEditModal', false)" wire:keydown.escape.window="$set('showEditModal', false)" tabindex="-1">
      <div class="app-modal-card" wire:click.stop>
        <button type="button" wire:click.prevent="$set('showEditModal', false)" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>

        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Ajuste de accesos</p>
            <h3 class="section-title app-modal-title">Editar usuario</h3>
            <p class="section-copy-sm">Actualiza los datos del usuario y su rol de acceso al sistema.</p>
          </div>
        </div>

        <form wire:submit="updateUser" class="mt-6 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Nombre de usuario</label>
            <input type="text" wire:model="editName" class="form-input" placeholder="Ej. gestor.oruro">
            @error('editName') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Correo</label>
            <input type="email" wire:model="editEmail" class="form-input" placeholder="usuario@recursoshumanos.local">
            @error('editEmail') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Nueva contrasena</label>
            <input type="password" wire:model="editPassword" class="form-input" placeholder="Solo si quieres cambiarla">
            @error('editPassword') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Confirmar contrasena</label>
            <input type="password" wire:model="editPassword_confirmation" class="form-input" placeholder="Repite la contrasena nueva">
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Rol</label>
            <select wire:model="editRole" class="form-input">
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
              @endforeach
            </select>
            @error('editRole') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <section class="grid gap-6 xl:grid-cols-[420px,1fr]">
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Control de accesos</p>
          <h3 class="section-title">Roles de RRHH</h3>
          <p class="section-copy-sm">Administra los perfiles `administrador`, `gestor` y `visor` para el personal autorizado de recursos humanos.</p>
        </div>

        <button type="button" wire:click="openCreateModal" class="section-action-button">Usuarios</button>
      </div>

      <form wire:submit="updateUserRole" class="mt-8 space-y-5">
        <div>
          <label class="form-label">Usuario del sistema</label>
          <select wire:model="selectedUserId" class="form-input">
            @foreach ($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
            @endforeach
          </select>
          @error('selectedUserId') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="form-label">Rol asignado</label>
          <select wire:model="selectedRole" class="form-input">
            @foreach ($roles as $role)
              <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
            @endforeach
          </select>
          @error('selectedRole') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="login-submit">Guardar rol</button>
      </form>

      <div class="mt-8 grid gap-3">
        @foreach ($roleProfiles as $roleName => $description)
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="font-semibold text-slate-900">{{ \Illuminate\Support\Str::headline($roleName) }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
          </div>
        @endforeach
      </div>

      <div class="mt-8 rounded-[1.4rem] border border-amber-200 bg-amber-50 px-5 py-5 text-sm text-amber-900">
        <p class="font-semibold">Compatibilidad detectada</p>
        <p class="mt-2">Asignaciones heredadas en `role_user`: <strong>{{ $legacyAssignments }}</strong>. Desde ahora Spatie controlara los accesos nuevos.</p>
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
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $user)
              <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                  <span class="status-badge status-info">
                    {{ \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? 'sin rol') }}
                  </span>
                </td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button type="button" wire:click="openEditModal({{ $user->id }})" class="table-action-button">Editar</button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-8 grid gap-5 lg:grid-cols-2">
        @foreach ($permissions as $roleGroup => $items)
          <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-5 py-5">
            <p class="metric-label">{{ $roleGroup === 'sin-asignacion' ? 'Sin asignacion' : 'Rol' }}</p>
            <h4 class="mt-2 text-lg font-semibold text-slate-900">
              {{ $roleGroup === 'sin-asignacion' ? 'Permisos sin rol' : \Illuminate\Support\Str::headline($roleGroup) }}
            </h4>
            <div class="mt-4 flex flex-wrap gap-3">
              @foreach ($items as $permission)
                <span class="status-badge status-available">{{ $permission->name }}</span>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </article>
  </section>
</div>
