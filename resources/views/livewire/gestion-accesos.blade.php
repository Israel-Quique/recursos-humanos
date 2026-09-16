<div class="page-stack">
  {{-- Notificaciones / Alertas Flash --}}
  @if (session()->has('status'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 shadow-sm backdrop-blur-sm transition-all">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-emerald-950">Operacion completada</h4>
            <p class="text-xs text-emerald-800">{{ session('status') }}</p>
          </div>
        </div>
        <button type="button" onclick="this.closest('.rounded-2xl').remove()" class="rounded-lg p-1.5 text-emerald-700 hover:bg-emerald-100 transition" aria-label="Cerrar">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
  @endif

  @if (session()->has('error'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 shadow-sm backdrop-blur-sm transition-all">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-rose-950">Atencion</h4>
            <p class="text-xs text-rose-800">{{ session('error') }}</p>
          </div>
        </div>
        <button type="button" onclick="this.closest('.rounded-2xl').remove()" class="rounded-lg p-1.5 text-rose-700 hover:bg-rose-100 transition" aria-label="Cerrar">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
  @endif

  {{-- Tarjetas KPI de Resumen Operativo --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Usuarios</p>
          <h4 class="mt-1 text-2xl font-black text-slate-800">{{ $stats['total'] }}</h4>
          <p class="mt-0.5 text-xs text-slate-500">Cuentas habilitadas</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">Administradores</p>
          <h4 class="mt-1 text-2xl font-black text-indigo-900">{{ $stats['admins'] }}</h4>
          <p class="mt-0.5 text-xs text-slate-500">Acceso total al sistema</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Gestores RRHH</p>
          <h4 class="mt-1 text-2xl font-black text-emerald-950">{{ $stats['gestores'] }}</h4>
          <p class="mt-0.5 text-xs text-slate-500">Operacion y registros</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Vinculados a Personal</p>
          <h4 class="mt-1 text-2xl font-black text-amber-950">{{ $stats['vinculados'] }}</h4>
          <p class="mt-0.5 text-xs text-slate-500">Con legajo asociado</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition group-hover:scale-105 group-hover:bg-amber-600 group-hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal de Alta (Crear Usuario) --}}
  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="closeCreateModal" wire:keydown.escape.window="closeCreateModal" tabindex="-1">
      <div class="app-modal-card max-w-2xl" wire:click.stop>
        <button type="button" wire:click.prevent="closeCreateModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="app-modal-head">
          <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0f67c0]/10 text-[#0f67c0]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
              </svg>
            </div>
            <div>
              <p class="section-kicker">Alta en el sistema</p>
              <h3 class="section-title app-modal-title">Registrar nuevo usuario</h3>
              <p class="section-copy-sm">Habilita una nueva cuenta con rol y credenciales para el portal.</p>
            </div>
          </div>
        </div>

        <form wire:submit="createUser" class="mt-6 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Nombre de usuario
            </label>
            <input type="text" wire:model.blur="name" class="form-input" placeholder="Ej. gestor.oruro" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+" autocomplete="username" spellcheck="false">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
            <p class="mt-1 text-[11px] text-slate-400">Solo letras, números, puntos y guiones.</p>
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              Correo corporativo
            </label>
            <div class="flex overflow-hidden rounded-[1.2rem] border border-slate-200 bg-white transition focus-within:border-[#0f67c0] focus-within:ring-4 focus-within:ring-[#0f67c0]/10">
              <input type="text" wire:model.live="email" class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-slate-700 focus:outline-none focus:ring-0 text-sm" placeholder="israel" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+(@correos\.gob\.bo)?" autocomplete="off" spellcheck="false">
              <span class="inline-flex items-center border-l border-slate-200 bg-slate-50 px-3.5 text-xs font-semibold text-slate-500">@correos.gob.bo</span>
            </div>
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Rol asignado
            </label>
            <select wire:model="newUserRole" class="form-input" required>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
              @endforeach
            </select>
            @error('newUserRole') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Vincular con Empleado (Opcional)
            </label>
            <select wire:model="newEmpleadoId" class="form-input">
              <option value="">-- Sin empleado vinculado --</option>
              @foreach ($empleados as $emp)
                <option value="{{ $emp->id }}">{{ $emp->nombre }} {{ $emp->apellido }} ({{ $emp->area ?? 'General' }} - {{ $emp->sucursal ?? 'N/A' }})</option>
              @endforeach
            </select>
            @error('newEmpleadoId') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div x-data="{ showPass: false }">
            <label class="form-label flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Contraseña
              </span>
              <button type="button" @click="showPass = !showPass" class="text-[11px] font-semibold text-blue-600 hover:underline">
                <span x-show="!showPass">Mostrar</span>
                <span x-show="showPass">Ocultar</span>
              </button>
            </label>
            <input :type="showPass ? 'text' : 'password'" wire:model.blur="password" class="form-input" placeholder="Minimo 8 caracteres" required minlength="8" maxlength="72" autocomplete="new-password">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div x-data="{ showPassConf: false }">
            <label class="form-label flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Confirmar contraseña
              </span>
              <button type="button" @click="showPassConf = !showPassConf" class="text-[11px] font-semibold text-blue-600 hover:underline">
                <span x-show="!showPassConf">Mostrar</span>
                <span x-show="showPassConf">Ocultar</span>
              </button>
            </label>
            <input :type="showPassConf ? 'text' : 'password'" wire:model.blur="password_confirmation" class="form-input" placeholder="Repite la contrasena" required minlength="8" maxlength="72" autocomplete="new-password">
            @error('password_confirmation') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions pt-2">
            <button type="button" wire:click="closeCreateModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit inline-flex items-center justify-center gap-2">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <span>Dar de alta usuario</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- Modal de Modificacion (Editar Usuario) --}}
  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal" wire:keydown.escape.window="closeEditModal" tabindex="-1">
      <div class="app-modal-card max-w-2xl" wire:click.stop>
        <button type="button" wire:click.prevent="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="app-modal-head">
          <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
              </svg>
            </div>
            <div>
              <p class="section-kicker">Modificacion de accesos</p>
              <h3 class="section-title app-modal-title">Editar usuario</h3>
              <p class="section-copy-sm">Actualiza datos personales, rol o vinculación del usuario seleccionado.</p>
            </div>
          </div>
        </div>

        <form wire:submit="updateUser" class="mt-6 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Nombre de usuario
            </label>
            <input type="text" wire:model.blur="editName" class="form-input" placeholder="Ej. gestor.oruro" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+" autocomplete="username" spellcheck="false">
            @error('editName') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              Correo corporativo
            </label>
            <div class="flex overflow-hidden rounded-[1.2rem] border border-slate-200 bg-white transition focus-within:border-[#0f67c0] focus-within:ring-4 focus-within:ring-[#0f67c0]/10">
              <input type="text" wire:model.live="editEmail" class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-slate-700 focus:outline-none focus:ring-0 text-sm" placeholder="israel" required minlength="3" maxlength="120" pattern="[A-Za-z0-9._-]+(@correos\.gob\.bo)?" autocomplete="off" spellcheck="false">
              <span class="inline-flex items-center border-l border-slate-200 bg-slate-50 px-3.5 text-xs font-semibold text-slate-500">@correos.gob.bo</span>
            </div>
            @error('editEmail') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Rol en el sistema
            </label>
            <select wire:model="editRole" class="form-input" required>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
              @endforeach
            </select>
            @error('editRole') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label flex items-center gap-1.5">
              <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Vincular con Empleado (Opcional)
            </label>
            <select wire:model="editEmpleadoId" class="form-input">
              <option value="">-- Sin empleado vinculado --</option>
              @foreach ($empleados as $emp)
                <option value="{{ $emp->id }}">{{ $emp->nombre }} {{ $emp->apellido }} ({{ $emp->area ?? 'General' }} - {{ $emp->sucursal ?? 'N/A' }})</option>
              @endforeach
            </select>
            @error('editEmpleadoId') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Cambio de contraseña (opcional)</p>
            <p class="text-xs text-slate-400 mt-0.5">Deja los campos en blanco si deseas mantener la contraseña actual.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
              <div x-data="{ showEditPass: false }">
                <label class="form-label flex items-center justify-between text-xs">
                  <span>Nueva contraseña</span>
                  <button type="button" @click="showEditPass = !showEditPass" class="text-[10px] font-semibold text-blue-600 hover:underline">
                    <span x-show="!showEditPass">Mostrar</span>
                    <span x-show="showEditPass">Ocultar</span>
                  </button>
                </label>
                <input :type="showEditPass ? 'text' : 'password'" wire:model.blur="editPassword" class="form-input bg-white" placeholder="Nueva contrasena (min. 8 car.)" minlength="8" maxlength="72" autocomplete="new-password">
                @error('editPassword') <p class="form-error">{{ $message }}</p> @enderror
              </div>

              <div x-data="{ showEditPassConf: false }">
                <label class="form-label flex items-center justify-between text-xs">
                  <span>Confirmar nueva contraseña</span>
                  <button type="button" @click="showEditPassConf = !showEditPassConf" class="text-[10px] font-semibold text-blue-600 hover:underline">
                    <span x-show="!showEditPassConf">Mostrar</span>
                    <span x-show="showEditPassConf">Ocultar</span>
                  </button>
                </label>
                <input :type="showEditPassConf ? 'text' : 'password'" wire:model.blur="editPassword_confirmation" class="form-input bg-white" placeholder="Repite la nueva contrasena" minlength="8" maxlength="72" autocomplete="new-password">
                @error('editPassword_confirmation') <p class="form-error">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          <div class="md:col-span-2 app-modal-actions pt-2">
            <button type="button" wire:click="closeEditModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit inline-flex items-center justify-center gap-2">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              <span>Guardar modificaciones</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- Modal de Baja / Eliminacion (Confirmacion de Seguridad) --}}
  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal" wire:keydown.escape.window="closeDeleteModal" tabindex="-1">
      <div class="app-modal-card max-w-lg" wire:click.stop>
        <button type="button" wire:click.prevent="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="app-modal-head">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
              </svg>
            </div>
            <div>
              <p class="section-kicker text-rose-600">Confirmación de Baja</p>
              <h3 class="section-title app-modal-title text-rose-950">Dar de baja usuario</h3>
              <p class="section-copy-sm">Revocar credenciales de acceso y eliminar la cuenta.</p>
            </div>
          </div>
        </div>

        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50/70 p-4">
          <p class="text-xs text-rose-800 font-medium leading-relaxed">
            ¿Estás seguro de dar de baja al siguiente usuario? Esta acción eliminará su acceso de inmediato y quedará registrada en la bitácora de auditoría.
          </p>

          <div class="mt-4 space-y-2 rounded-xl bg-white/90 p-3.5 border border-rose-100 text-xs shadow-sm">
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Nombre de usuario:</span>
              <span class="font-bold text-slate-800">{{ $pendingDeleteUserName }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Correo:</span>
              <span class="font-semibold text-slate-700">{{ $pendingDeleteUserEmail }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Rol actual:</span>
              <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $pendingDeleteUserRole === 'administrador' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ \Illuminate\Support\Str::headline($pendingDeleteUserRole) }}
              </span>
            </div>
            @if ($pendingDeleteUserEmpleado)
              <div class="flex items-center justify-between border-t border-slate-100 pt-2">
                <span class="text-slate-500 font-medium">Empleado vinculado:</span>
                <span class="font-semibold text-slate-700">{{ $pendingDeleteUserEmpleado }}</span>
              </div>
            @endif
          </div>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteUser" class="table-action-button table-action-button-danger inline-flex items-center justify-center gap-2 px-5 py-2.5 font-bold shadow-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            <span>Confirmar baja</span>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- Seccion Principal: Barra de Herramientas y Tabla --}}
  <section class="surface-card">
    <div class="section-head-row flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="section-kicker">Panel de Control</p>
        <h3 class="section-title">Usuarios con acceso al sistema</h3>
        <p class="section-copy-sm">Administra altas, bajas y modificaciones de credenciales, roles y vinculaciones con el personal.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="button" wire:click="openCreateModal" class="section-action-button inline-flex items-center gap-2 shadow-sm">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Dar de alta usuario</span>
        </button>
      </div>
    </div>

    {{-- Filtros y Busqueda Dinamica --}}
    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between rounded-2xl border border-slate-100 bg-slate-50/50 p-3">
      <div class="relative flex-1">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm transition focus:border-[#0f67c0] focus:outline-none focus:ring-2 focus:ring-[#0f67c0]/10" placeholder="Buscar por usuario, correo o empleado vinculado...">
      </div>

      <div class="flex items-center gap-2">
        <div class="relative min-w-[170px]">
          <select wire:model.live="roleFilter" class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-8 text-sm font-medium text-slate-700 shadow-sm focus:border-[#0f67c0] focus:outline-none focus:ring-2 focus:ring-[#0f67c0]/10">
            <option value="">Todos los roles</option>
            <option value="administrador">Solo Administrador</option>
            <option value="gestor">Solo Gestor</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          </div>
        </div>

        @if ($search !== '' || $roleFilter !== '')
          <button type="button" wire:click="clearFilters" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition" title="Limpiar filtros">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <span>Limpiar</span>
          </button>
        @endif
      </div>
    </div>

    {{-- Tabla Mejorada con Estilos, Badges e Iconos --}}
    <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200/80 shadow-sm">
      <div class="overflow-x-auto">
        <table class="history-table w-full">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/80">
              <th class="py-4 pl-6 pr-4">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  Usuario
                </span>
              </th>
              <th class="px-4 py-4">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  Correo Corporativo
                </span>
              </th>
              <th class="px-4 py-4">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  Rol de Acceso
                </span>
              </th>
              <th class="px-4 py-4">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  Registro
                </span>
              </th>
              <th class="py-4 pl-4 pr-6 text-right">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                  Acciones
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($users as $user)
              @php
                $roleName = $user->getRoleNames()->first() ?? 'sin rol';
                $isSelf = $user->id === auth()->id();
                $initials = strtoupper(substr($user->name, 0, 2));
              @endphp
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-4 pl-6 pr-4">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold text-xs shadow-sm {{ $roleName === 'administrador' ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-emerald-600 text-white shadow-emerald-200' }}">
                      {{ $initials }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-sm">{{ $user->name }}</span>
                        @if ($isSelf)
                          <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">Tu cuenta</span>
                        @endif
                      </div>
                      <span class="text-[11px] text-slate-400">ID #{{ $user->id }}</span>
                    </div>
                  </div>
                </td>

                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <span class="text-sm font-medium text-slate-700">{{ $user->email }}</span>
                  </div>
                </td>

                <td class="px-4 py-4">
                  @if ($roleName === 'administrador')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 shadow-sm">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                      Administrador
                    </span>
                  @elseif ($roleName === 'gestor')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 shadow-sm">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                      Gestor
                    </span>
                  @else
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                      {{ \Illuminate\Support\Str::headline($roleName) }}
                    </span>
                  @endif
                </td>

                <td class="px-4 py-4 text-xs text-slate-500 whitespace-nowrap">
                  {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/D' }}
                  <span class="block text-[10px] text-slate-400">{{ $user->created_at ? $user->created_at->format('H:i') : '' }}</span>
                </td>

                <td class="py-4 pl-4 pr-6 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    {{-- Modificaciones (Editar: solo icono lapiz) --}}
                    <button type="button" wire:click="openEditModal({{ $user->id }})" class="table-action-button p-2.5 rounded-xl hover:bg-amber-50 hover:border-amber-300 transition" title="Editar usuario" aria-label="Editar usuario">
                      <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                        <path d="m15 5 4 4"/>
                      </svg>
                    </button>

                    {{-- Bajas (Eliminar: solo icono basurero) --}}
                    @if ($isSelf)
                      <button type="button" disabled class="table-action-button p-2.5 rounded-xl opacity-35 cursor-not-allowed text-slate-300 border-slate-200" title="No puedes darte de baja a ti mismo" aria-label="No puedes darte de baja a ti mismo">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 6h18"/>
                          <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                          <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                      </button>
                    @else
                      <button type="button" wire:click="confirmDelete({{ $user->id }})" class="table-action-button table-action-button-danger p-2.5 rounded-xl hover:bg-rose-100 hover:border-rose-400 transition" title="Dar de baja usuario" aria-label="Dar de baja usuario">
                        <svg class="h-4 w-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 6h18"/>
                          <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                          <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                          <line x1="10" y1="11" x2="10" y2="17"/>
                          <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="mx-auto max-w-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <h4 class="mt-3 text-sm font-bold text-slate-700">No se encontraron usuarios</h4>
                    <p class="mt-1 text-xs text-slate-400">
                      @if ($search !== '' || $roleFilter !== '')
                        Ningún usuario coincide con los criterios de búsqueda actuales.
                      @else
                        No hay usuarios registrados en el sistema.
                      @endif
                    </p>
                    @if ($search !== '' || $roleFilter !== '')
                      <button type="button" wire:click="clearFilters" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                        Limpiar filtros
                      </button>
                    @else
                      <button type="button" wire:click="openCreateModal" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-[#0f67c0] px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-[#0d59a7] transition">
                        Dar de alta primer usuario
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
