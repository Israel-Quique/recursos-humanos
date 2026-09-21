<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class GestionAccesosPage extends Component
{
    use WithFileUploads;

    private const ALLOWED_ROLES = ['administrador', 'gestor'];

    public string $search = '';
    public string $roleFilter = '';

    public ?int $selectedUserId = null;
    public string $selectedRole = 'gestor';

    // Altas (Crear)
    public bool $showCreateModal = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $newUserRole = 'gestor';
    public ?int $newEmpleadoId = null;

    // Modificaciones (Editar)
    public bool $showEditModal = false;
    public ?int $editingUserId = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editPassword = '';
    public string $editPassword_confirmation = '';
    public string $editRole = 'gestor';
    public ?int $editEmpleadoId = null;

    // Foto de perfil de usuario
    public $userFotoNueva = null;
    public ?string $editUserFotoActual = null;
    public bool $eliminarUserFoto = false;

    // Bajas (Eliminar)
    public bool $showDeleteModal = false;
    public ?int $pendingDeleteUserId = null;
    public string $pendingDeleteUserName = '';
    public string $pendingDeleteUserEmail = '';
    public string $pendingDeleteUserRole = '';
    public ?string $pendingDeleteUserEmpleado = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos'), 403);

        $firstUser = User::query()->orderBy('name')->first();
        if ($firstUser) {
            $this->selectedUserId = $firstUser->id;
            $this->selectedRole = $this->normalizeRole($firstUser->getRoleNames()->first());
        }
    }

    public function updatedEmail(string $value): void
    {
        $this->email = $this->extractCorreosLocalPart($value);
    }

    public function updatedEditEmail(string $value): void
    {
        $this->editEmail = $this->extractCorreosLocalPart($value);
    }

    public function updated(string $property): void
    {
        $createFields = ['name', 'email', 'password', 'password_confirmation', 'newUserRole', 'newEmpleadoId'];
        $editFields = ['editName', 'editEmail', 'editPassword', 'editPassword_confirmation', 'editRole', 'editEmpleadoId'];

        if ($this->showCreateModal && in_array($property, $createFields, true)) {
            $this->validateOnly($property, $this->createRules(), $this->validationMessages());
        }

        if ($this->showEditModal && in_array($property, $editFields, true)) {
            $this->validateOnly($property, $this->editRules(), $this->validationMessages());
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'roleFilter']);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'newEmpleadoId']);
        $this->newUserRole = 'gestor';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->userFotoNueva = null;
        $this->resetValidation();
    }

    public function createUser(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos'), 403);

        $data = $this->validate($this->createRules(), $this->validationMessages());

        $normalizedEmail = $this->normalizeCorreosEmail($data['email']);

        validator([
            'email' => $normalizedEmail,
        ], [
            'email' => ['required', 'email', 'max:255', 'ends_with:@correos.gob.bo', 'unique:users,email'],
        ], [
            'email.email' => 'Ingresa un correo valido.',
            'email.ends_with' => 'El correo debe pertenecer al dominio @correos.gob.bo.',
            'email.unique' => 'Ese correo corporativo ya esta registrado.',
        ])->validate();

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => $normalizedEmail,
            'password' => Hash::make($data['password']),
            'empleado_id' => ! empty($data['newEmpleadoId']) ? (int) $data['newEmpleadoId'] : null,
        ]);

        if ($this->userFotoNueva) {
            $ext = $this->userFotoNueva->getClientOriginalExtension() ?: 'jpg';
            $nombreArchivo = 'user_' . $user->id . '_' . time() . '.' . $ext;
            $user->foto = $this->userFotoNueva->storeAs('fotos/usuarios', $nombreArchivo, 'public');
            $user->save();
        }

        $user->syncRoles([$data['newUserRole']]);
        if ($data['newUserRole'] === 'gestor') {
            $user->syncPermissions([]);
        }

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'crear',
            'Se creo y dio de alta a un nuevo usuario del sistema (' . $user->name . ').',
            $user,
            null,
            $this->snapshotUser($user)
        );

        $this->selectedUserId = $user->id;
        $this->selectedRole = $data['newUserRole'];
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'newEmpleadoId', 'userFotoNueva']);
        $this->newUserRole = 'gestor';
        $this->resetValidation();
        $this->showCreateModal = false;

        session()->flash('status', 'Usuario dado de alta exitosamente y habilitado para ingresar al sistema.');
    }

    public function openEditModal(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->resetValidation();
        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $this->extractCorreosLocalPart($user->email);
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->editRole = $this->normalizeRole($user->getRoleNames()->first());
        $this->editEmpleadoId = $user->empleado_id;
        $this->editUserFotoActual = $user->foto_url;
        $this->userFotoNueva = null;
        $this->eliminarUserFoto = false;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->userFotoNueva = null;
        $this->editUserFotoActual = null;
        $this->eliminarUserFoto = false;
        $this->resetValidation();
        $this->reset(['editingUserId', 'editName', 'editEmail', 'editPassword', 'editPassword_confirmation', 'editEmpleadoId']);
        $this->editRole = 'gestor';
    }

    public function quitarUserFoto(): void
    {
        $this->userFotoNueva = null;
        $this->editUserFotoActual = null;
        $this->eliminarUserFoto = true;
    }

    public function updateUser(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos'), 403);

        $data = $this->validate($this->editRules(), $this->validationMessages());

        $user = User::query()->findOrFail($data['editingUserId']);
        $antes = $this->snapshotUser($user);
        $normalizedEmail = $this->normalizeCorreosEmail($data['editEmail']);

        $validated = validator([
            'editName' => trim($data['editName']),
            'editEmail' => $normalizedEmail,
        ], [
            'editName' => ['required', 'string', 'max:120', 'unique:users,name,'.$user->id],
            'editEmail' => ['required', 'email', 'max:255', 'ends_with:@correos.gob.bo', 'unique:users,email,'.$user->id],
        ], [
            'editName.unique' => 'Ese nombre de usuario ya esta en uso.',
            'editEmail.email' => 'Ingresa un correo valido.',
            'editEmail.ends_with' => 'El correo debe pertenecer al dominio @correos.gob.bo.',
            'editEmail.unique' => 'Ese correo corporativo ya esta en uso.',
        ])->validate();

        // Prevencion: no degradar al ultimo administrador
        $currentRole = $user->getRoleNames()->first();
        if ($currentRole === 'administrador' && $data['editRole'] !== 'administrador' && User::role('administrador')->count() <= 1) {
            session()->flash('error', 'No es posible cambiar el rol del unico administrador del sistema.');
            return;
        }

        $user->name = $validated['editName'];
        $user->email = $validated['editEmail'];
        $user->empleado_id = ! empty($data['editEmpleadoId']) ? (int) $data['editEmpleadoId'] : null;

        if ($data['editPassword'] !== '') {
            $user->password = Hash::make($data['editPassword']);
        }

        if ($this->eliminarUserFoto) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = null;
        } elseif ($this->userFotoNueva) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $ext = $this->userFotoNueva->getClientOriginalExtension() ?: 'jpg';
            $nombreArchivo = 'user_' . $user->id . '_' . time() . '.' . $ext;
            $user->foto = $this->userFotoNueva->storeAs('fotos/usuarios', $nombreArchivo, 'public');
        }

        $user->save();
        $user->syncRoles([$data['editRole']]);
        if ($data['editRole'] === 'gestor') {
            $user->syncPermissions([]);
        }

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'editar',
            'Se modificaron los datos de acceso del usuario ' . $user->name . '.',
            $user->fresh(),
            $antes,
            $this->snapshotUser($user->fresh())
        );

        $this->selectedUserId = $user->id;
        $this->selectedRole = $data['editRole'];
        $this->userFotoNueva = null;
        $this->editUserFotoActual = null;
        $this->eliminarUserFoto = false;
        $this->closeEditModal();

        session()->flash('status', 'Datos del usuario modificados correctamente.');
    }

    public function confirmDelete(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'No puedes dar de baja tu propia cuenta de acceso en uso.');
            return;
        }

        $user = User::query()->with('empleado')->findOrFail($userId);
        $roleName = $user->getRoleNames()->first() ?? 'sin rol';

        if ($roleName === 'administrador' && User::role('administrador')->count() <= 1) {
            session()->flash('error', 'No se puede dar de baja al unico administrador del sistema.');
            return;
        }

        $this->pendingDeleteUserId = $user->id;
        $this->pendingDeleteUserName = $user->name;
        $this->pendingDeleteUserEmail = $user->email;
        $this->pendingDeleteUserRole = $roleName;
        $this->pendingDeleteUserEmpleado = $user->empleado?->nombre_completo;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset([
            'pendingDeleteUserId',
            'pendingDeleteUserName',
            'pendingDeleteUserEmail',
            'pendingDeleteUserRole',
            'pendingDeleteUserEmpleado',
        ]);
    }

    public function deleteUser(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos'), 403);

        if (! $this->pendingDeleteUserId) {
            return;
        }

        if ($this->pendingDeleteUserId === auth()->id()) {
            session()->flash('error', 'No puedes dar de baja tu propia cuenta de acceso.');
            $this->closeDeleteModal();
            return;
        }

        $user = User::query()->with('empleado')->findOrFail($this->pendingDeleteUserId);
        $roleName = $user->getRoleNames()->first() ?? 'sin rol';

        if ($roleName === 'administrador' && User::role('administrador')->count() <= 1) {
            session()->flash('error', 'No se puede dar de baja al unico administrador del sistema.');
            $this->closeDeleteModal();
            return;
        }

        $snapshot = $this->snapshotUser($user);

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'eliminar',
            'Se dio de baja y elimino al usuario "' . $user->name . '" del sistema de accesos.',
            $user,
            $snapshot,
            null
        );

        $user->syncRoles([]);

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        $user->delete();

        if ($this->selectedUserId === $this->pendingDeleteUserId) {
            $firstUser = User::query()->orderBy('name')->first();
            $this->selectedUserId = $firstUser?->id;
            $this->selectedRole = $this->normalizeRole($firstUser?->getRoleNames()->first());
        }

        $this->closeDeleteModal();
        session()->flash('status', 'Usuario dado de baja y eliminado correctamente.');
    }

    public function render()
    {
        $query = User::query()->with('empleado');

        if (trim($this->search) !== '') {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('empleado', function ($eq) use ($searchTerm) {
                      $eq->where('nombre', 'like', $searchTerm)
                         ->orWhere('apellido', 'like', $searchTerm);
                  });
            });
        }

        if (in_array($this->roleFilter, self::ALLOWED_ROLES, true)) {
            $query->role($this->roleFilter);
        }

        $users = $query->orderBy('name')->get();

        $roles = Role::query()
            ->whereIn('name', self::ALLOWED_ROLES)
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, self::ALLOWED_ROLES, true))
            ->values();

        $empleados = Empleado::query()
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido', 'area', 'sucursal']);

        $stats = [
            'total' => User::count(),
            'admins' => User::role('administrador')->count(),
            'gestores' => User::role('gestor')->count(),
            'vinculados' => User::whereNotNull('empleado_id')->count(),
        ];

        return view('livewire.gestion-accesos', [
            'users' => $users,
            'roles' => $roles,
            'empleados' => $empleados,
            'stats' => $stats,
        ])->layout('layouts.app', ['title' => 'Administracion de accesos']);
    }

    private function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,name'],
            'email' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+(?:@correos\.gob\.bo)?$/i'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:72'],
            'newUserRole' => ['required', 'string', 'in:administrador,gestor'],
            'newEmpleadoId' => ['nullable', 'integer', 'exists:empleados,id'],
        ];
    }

    private function editRules(): array
    {
        return [
            'editingUserId' => ['required', 'integer', 'exists:users,id'],
            'editName' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/'],
            'editEmail' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+(?:@correos\.gob\.bo)?$/i'],
            'editPassword' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed', 'required_with:editPassword_confirmation'],
            'editPassword_confirmation' => ['nullable', 'string', 'min:8', 'max:72', 'required_with:editPassword'],
            'editRole' => ['required', 'string', 'in:administrador,gestor'],
            'editEmpleadoId' => ['nullable', 'integer', 'exists:empleados,id'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'Ingresa el nombre del usuario.',
            'name.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre de usuario no puede superar los 120 caracteres.',
            'name.regex' => 'El nombre de usuario solo puede usar letras, numeros, puntos, guiones y guion bajo.',
            'name.unique' => 'Ese nombre de usuario ya existe.',
            'email.required' => 'Ingresa el correo corporativo.',
            'email.min' => 'El correo debe tener al menos 3 caracteres antes de @correos.gob.bo.',
            'email.max' => 'El correo no puede superar los 120 caracteres antes de @correos.gob.bo.',
            'email.regex' => 'Ingresa solo el nombre del correo corporativo antes de @correos.gob.bo.',
            'password.required' => 'Ingresa una contrasena.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.max' => 'La contrasena no puede superar los 72 caracteres.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
            'password_confirmation.required' => 'Confirma la contrasena.',
            'password_confirmation.min' => 'La confirmacion debe tener al menos 8 caracteres.',
            'password_confirmation.max' => 'La confirmacion no puede superar los 72 caracteres.',
            'newUserRole.required' => 'Selecciona un rol para el usuario.',
            'newUserRole.in' => 'Solo se permite crear usuarios administrador o gestor.',
            'newEmpleadoId.exists' => 'El empleado seleccionado no es valido.',
            'editingUserId.required' => 'Selecciona un usuario valido.',
            'editName.required' => 'Ingresa el nombre del usuario.',
            'editName.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'editName.max' => 'El nombre de usuario no puede superar los 120 caracteres.',
            'editName.regex' => 'El nombre de usuario solo puede usar letras, numeros, puntos, guiones y guion bajo.',
            'editEmail.required' => 'Ingresa el correo corporativo.',
            'editEmail.min' => 'El correo debe tener al menos 3 caracteres antes de @correos.gob.bo.',
            'editEmail.max' => 'El correo no puede superar los 120 caracteres antes de @correos.gob.bo.',
            'editEmail.regex' => 'Ingresa solo el nombre del correo corporativo antes de @correos.gob.bo.',
            'editPassword.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'editPassword.max' => 'La contrasena no puede superar los 72 caracteres.',
            'editPassword.confirmed' => 'La confirmacion de contrasena no coincide.',
            'editPassword.required_with' => 'Ingresa la nueva contrasena completa para poder actualizarla.',
            'editPassword_confirmation.min' => 'La confirmacion debe tener al menos 8 caracteres.',
            'editPassword_confirmation.max' => 'La confirmacion no puede superar los 72 caracteres.',
            'editPassword_confirmation.required_with' => 'Confirma la nueva contrasena.',
            'editRole.required' => 'Selecciona un rol para el usuario.',
            'editRole.in' => 'Solo se permite asignar administrador o gestor.',
            'editEmpleadoId.exists' => 'El empleado seleccionado no es valido.',
        ];
    }

    private function snapshotUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'empleado_id' => $user->empleado_id,
            'empleado' => $user->empleado?->nombre_completo,
            'rol' => $user->getRoleNames()->first() ?? 'sin rol',
        ];
    }

    private function extractCorreosLocalPart(string $value): string
    {
        $normalized = trim(mb_strtolower($value));

        if ($normalized === '') {
            return '';
        }

        if (str_contains($normalized, '@')) {
            [$localPart] = explode('@', $normalized, 2);

            return trim($localPart);
        }

        return $normalized;
    }

    private function normalizeCorreosEmail(string $value): string
    {
        $localPart = $this->extractCorreosLocalPart($value);

        return $localPart === '' ? '' : $localPart.'@correos.gob.bo';
    }

    private function normalizeRole(?string $role): string
    {
        return in_array($role, self::ALLOWED_ROLES, true) ? $role : 'gestor';
    }
}
