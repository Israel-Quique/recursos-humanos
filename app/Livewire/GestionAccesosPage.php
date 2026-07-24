<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GestionAccesosPage extends Component
{
    public ?int $selectedUserId = null;
    public string $selectedRole = 'gestor';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $newUserRole = 'visor';
    public ?int $editingUserId = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editPassword = '';
    public string $editPassword_confirmation = '';
    public string $editRole = 'visor';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('gestionar accesos'), 403);

        $firstUser = User::query()->orderBy('name')->first();
        if ($firstUser) {
            $this->selectedUserId = $firstUser->id;
            $this->selectedRole = $firstUser->getRoleNames()->first() ?? 'gestor';
        }
    }

    public function updateUserRole(): void
    {
        $data = $this->validate([
            'selectedUserId' => ['required', 'integer', 'exists:users,id'],
            'selectedRole' => ['required', 'string', 'in:administrador,gestor,visor'],
        ], [
            'selectedUserId.required' => 'Selecciona un usuario.',
            'selectedRole.required' => 'Selecciona un rol.',
        ]);

        $user = User::query()->findOrFail($data['selectedUserId']);
        $antes = ['rol' => $user->getRoleNames()->first() ?? 'sin rol'];
        $user->syncRoles([$data['selectedRole']]);

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'cambiar_rol',
            'Se actualizo el rol de un usuario del sistema.',
            $user,
            $antes,
            ['rol' => $data['selectedRole']]
        );

        session()->flash('status', 'Rol actualizado correctamente.');
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->newUserRole = 'visor';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function openEditModal(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->resetValidation();
        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->editRole = $user->getRoleNames()->first() ?? 'visor';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
        $this->reset(['editingUserId', 'editName', 'editEmail', 'editPassword', 'editPassword_confirmation']);
        $this->editRole = 'visor';
    }

    public function createUser(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120', 'unique:users,name'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'newUserRole' => ['required', 'string', 'in:administrador,gestor,visor'],
        ], [
            'name.required' => 'Ingresa el nombre del usuario.',
            'name.unique' => 'Ese nombre de usuario ya existe.',
            'email.required' => 'Ingresa el correo del usuario.',
            'email.email' => 'Ingresa un correo valido.',
            'email.unique' => 'Ese correo ya existe.',
            'password.required' => 'Ingresa una contrasena.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
            'newUserRole.required' => 'Selecciona un rol para el usuario.',
        ]);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => trim(mb_strtolower($data['email'])),
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['newUserRole']]);

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'crear',
            'Se creo un nuevo usuario del sistema.',
            $user,
            null,
            $this->snapshotUser($user)
        );

        $this->selectedUserId = $user->id;
        $this->selectedRole = $data['newUserRole'];
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->newUserRole = 'visor';
        $this->resetValidation();
        $this->showCreateModal = false;

        session()->flash('status', 'Usuario creado correctamente y listo para ingresar al sistema.');
    }

    public function updateUser(): void
    {
        $data = $this->validate([
            'editingUserId' => ['required', 'integer', 'exists:users,id'],
            'editName' => ['required', 'string', 'max:120'],
            'editEmail' => ['required', 'email', 'max:255'],
            'editPassword' => ['nullable', 'string', 'min:8', 'confirmed'],
            'editRole' => ['required', 'string', 'in:administrador,gestor,visor'],
        ], [
            'editingUserId.required' => 'Selecciona un usuario valido.',
            'editName.required' => 'Ingresa el nombre del usuario.',
            'editEmail.required' => 'Ingresa el correo del usuario.',
            'editEmail.email' => 'Ingresa un correo valido.',
            'editPassword.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'editPassword.confirmed' => 'La confirmacion de contrasena no coincide.',
            'editRole.required' => 'Selecciona un rol para el usuario.',
        ]);

        $user = User::query()->findOrFail($data['editingUserId']);
        $antes = $this->snapshotUser($user);

        $validated = validator([
            'editName' => trim($data['editName']),
            'editEmail' => trim(mb_strtolower($data['editEmail'])),
        ], [
            'editName' => ['required', 'string', 'max:120', 'unique:users,name,'.$user->id],
            'editEmail' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ], [
            'editName.unique' => 'Ese nombre de usuario ya existe.',
            'editEmail.unique' => 'Ese correo ya existe.',
        ])->validate();

        $user->name = $validated['editName'];
        $user->email = $validated['editEmail'];

        if ($data['editPassword'] !== '') {
            $user->password = Hash::make($data['editPassword']);
        }

        $user->save();
        $user->syncRoles([$data['editRole']]);

        app(AuditoriaService::class)->registrar(
            'Accesos',
            'editar',
            'Se editaron los datos de un usuario del sistema.',
            $user->fresh(),
            $antes,
            $this->snapshotUser($user->fresh())
        );

        $this->selectedUserId = $user->id;
        $this->selectedRole = $data['editRole'];
        $this->closeEditModal();

        session()->flash('status', 'Usuario actualizado correctamente.');
    }

    public function render()
    {
        $users = User::query()->orderBy('name')->get();
        $roles = Role::query()
            ->whereIn('name', ['administrador', 'gestor', 'visor'])
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, ['administrador', 'gestor', 'visor'], true))
            ->values();
        $permissions = Permission::query()->orderBy('name')->get()->groupBy(fn (Permission $permission) => $permission->roles->pluck('name')->sort()->join(', ') ?: 'sin-asignacion');
        $legacyAssignments = DB::table('role_user')->count();
        $roleProfiles = [
            'administrador' => 'Ve todo el sistema y administra accesos, configuraciones y operacion.',
            'gestor' => 'Sube asistencias, controla personal, incidencias, horarios y reportes.',
            'visor' => 'Consulta horas, asistencia, calendario y reportes sin editar informacion.',
        ];

        return view('livewire.gestion-accesos', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'legacyAssignments' => $legacyAssignments,
            'roleProfiles' => $roleProfiles,
        ])->layout('layouts.app', ['title' => 'Administracion de accesos']);
    }

    private function snapshotUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->getRoleNames()->first() ?? 'sin rol',
        ];
    }
}
