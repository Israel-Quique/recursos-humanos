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
            <input type="text" wire:model.blur="name" class="form-input" placeholder="Ej. gestor.oruro" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+" autocomplete="username" spellcheck="false">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Correo</label>
            <div class="flex overflow-hidden rounded-[1.2rem] border border-slate-200 bg-white transition focus-within:border-[#0f67c0] focus-within:ring-4 focus-within:ring-[#0f67c0]/10">
              <input type="text" wire:model.live="email" class="min-w-0 flex-1 border-0 bg-transparent px-5 py-4 text-slate-700 focus:outline-none focus:ring-0" placeholder="israel" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+(@correos\.com)?" autocomplete="off" spellcheck="false">
              <span class="inline-flex items-center border-l border-slate-200 px-4 text-sm font-semibold text-slate-500">@correos.com</span>
            </div>
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Contrasena</label>
            <input type="password" wire:model.blur="password" class="form-input" placeholder="Minimo 8 caracteres" required minlength="8" maxlength="72" autocomplete="new-password">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Confirmar contrasena</label>
            <input type="password" wire:model.blur="password_confirmation" class="form-input" placeholder="Repite la contrasena" required minlength="8" maxlength="72" autocomplete="new-password">
            @error('password_confirmation') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Rol inicial</label>
            <select wire:model="newUserRole" class="form-input" required>
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
            <input type="text" wire:model.blur="editName" class="form-input" placeholder="Ej. gestor.oruro" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+" autocomplete="username" spellcheck="false">
            @error('editName') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Correo</label>
            <div class="flex overflow-hidden rounded-[1.2rem] border border-slate-200 bg-white transition focus-within:border-[#0f67c0] focus-within:ring-4 focus-within:ring-[#0f67c0]/10">
              <input type="text" wire:model.live="editEmail" class="min-w-0 flex-1 border-0 bg-transparent px-5 py-4 text-slate-700 focus:outline-none focus:ring-0" placeholder="israel" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+(@correos\.com)?" autocomplete="off" spellcheck="false">
              <span class="inline-flex items-center border-l border-slate-200 px-4 text-sm font-semibold text-slate-500">@correos.com</span>
            </div>
            @error('editEmail') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Nueva contrasena</label>
            <input type="password" wire:model.blur="editPassword" class="form-input" placeholder="Solo si quieres cambiarla" minlength="8" maxlength="72" autocomplete="new-password">
            @error('editPassword') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Confirmar contrasena</label>
            <input type="password" wire:model.blur="editPassword_confirmation" class="form-input" placeholder="Repite la contrasena nueva" minlength="8" maxlength="72" autocomplete="new-password">
            @error('editPassword_confirmation') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Rol</label>
            <select wire:model="editRole" class="form-input" required>
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

  <section class="surface-card">
    <div class="section-head-row gap-5">
      <div>
        <p class="section-kicker">Resumen operativo</p>
        <h3 class="section-title">Usuarios con acceso al sistema</h3>
        <p class="section-copy-sm">Revisa rapidamente quienes pueden ingresar, su correo corporativo y el rol que tienen asignado.</p>
      </div>

      <button type="button" wire:click="openCreateModal" class="section-action-button">Crear usuario</button>
    </div>

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
          @forelse ($users as $user)
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
          @empty
            <tr>
              <td colspan="4" class="text-center text-slate-400">Todavia no hay usuarios registrados para ingresar al sistema.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

</div>
