<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class GestionAccesosPage extends Component
{
    private const ALLOWED_ROLES = ['administrador', 'gestor'];

    public ?int $selectedUserId = null;
    public string $selectedRole = 'gestor';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $newUserRole = 'gestor';
    public ?int $editingUserId = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editPassword = '';
    public string $editPassword_confirmation = '';
    public string $editRole = 'gestor';

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
        $createFields = ['name', 'email', 'password', 'password_confirmation', 'newUserRole'];
        $editFields = ['editName', 'editEmail', 'editPassword', 'editPassword_confirmation', 'editRole'];

        if ($this->showCreateModal && in_array($property, $createFields, true)) {
            $this->validateOnly($property, $this->createRules(), $this->validationMessages());
        }

        if ($this->showEditModal && in_array($property, $editFields, true)) {
            $this->validateOnly($property, $this->editRules(), $this->validationMessages());
        }
    }

    public function updateUserRole(): void
    {
        $data = $this->validate([
            'selectedUserId' => ['required', 'integer', 'exists:users,id'],
            'selectedRole' => ['required', 'string', 'in:administrador,gestor'],
        ], [
            'selectedUserId.required' => 'Selecciona un usuario.',
            'selectedRole.required' => 'Selecciona un rol.',
            'selectedRole.in' => 'Solo se permite asignar administrador o gestor.',
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
        $this->newUserRole = 'gestor';
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
        $this->editEmail = $this->extractCorreosLocalPart($user->email);
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->editRole = $this->normalizeRole($user->getRoleNames()->first());
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
        $this->reset(['editingUserId', 'editName', 'editEmail', 'editPassword', 'editPassword_confirmation']);
        $this->editRole = 'gestor';
    }

    public function createUser(): void
    {
        $data = $this->validate($this->createRules(), $this->validationMessages());

        $normalizedEmail = $this->normalizeCorreosEmail($data['email']);

        validator([
            'email' => $normalizedEmail,
        ], [
            'email' => ['required', 'email', 'max:255', 'ends_with:@correos.com', 'unique:users,email'],
        ], [
            'email.email' => 'Ingresa un correo valido.',
            'email.ends_with' => 'El correo debe pertenecer al dominio @correos.com.',
            'email.unique' => 'Ese correo ya existe.',
        ])->validate();

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => $normalizedEmail,
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
        $this->newUserRole = 'gestor';
        $this->resetValidation();
        $this->showCreateModal = false;

        session()->flash('status', 'Usuario creado correctamente y listo para ingresar al sistema.');
    }

    public function updateUser(): void
    {
        $data = $this->validate($this->editRules(), $this->validationMessages());

        $user = User::query()->findOrFail($data['editingUserId']);
        $antes = $this->snapshotUser($user);
        $normalizedEmail = $this->normalizeCorreosEmail($data['editEmail']);

        $validated = validator([
            'editName' => trim($data['editName']),
            'editEmail' => $normalizedEmail,
        ], [
            'editName' => ['required', 'string', 'max:120', 'unique:users,name,'.$user->id],
            'editEmail' => ['required', 'email', 'max:255', 'ends_with:@correos.com', 'unique:users,email,'.$user->id],
        ], [
            'editName.unique' => 'Ese nombre de usuario ya existe.',
            'editEmail.email' => 'Ingresa un correo valido.',
            'editEmail.ends_with' => 'El correo debe pertenecer al dominio @correos.com.',
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
        $users = User::query()->with('empleado')->orderBy('name')->get();
        $roles = Role::query()
            ->whereIn('name', self::ALLOWED_ROLES)
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, self::ALLOWED_ROLES, true))
            ->values();

        return view('livewire.gestion-accesos', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Administracion de accesos']);
    }

    private function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,name'],
            'email' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+(?:@correos\.com)?$/i'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:72'],
            'newUserRole' => ['required', 'string', 'in:administrador,gestor'],
        ];
    }

    private function editRules(): array
    {
        return [
            'editingUserId' => ['required', 'integer', 'exists:users,id'],
            'editName' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/'],
            'editEmail' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[A-Za-z0-9._-]+(?:@correos\.com)?$/i'],
            'editPassword' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed', 'required_with:editPassword_confirmation'],
            'editPassword_confirmation' => ['nullable', 'string', 'min:8', 'max:72', 'required_with:editPassword'],
            'editRole' => ['required', 'string', 'in:administrador,gestor'],
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
            'email.required' => 'Ingresa el correo del usuario.',
            'email.min' => 'El correo debe tener al menos 3 caracteres antes de @correos.com.',
            'email.max' => 'El correo no puede superar los 120 caracteres antes de @correos.com.',
            'email.regex' => 'Ingresa solo el nombre del correo corporativo antes de @correos.com.',
            'password.required' => 'Ingresa una contrasena.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.max' => 'La contrasena no puede superar los 72 caracteres.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
            'password_confirmation.required' => 'Confirma la contrasena.',
            'password_confirmation.min' => 'La confirmacion debe tener al menos 8 caracteres.',
            'password_confirmation.max' => 'La confirmacion no puede superar los 72 caracteres.',
            'newUserRole.required' => 'Selecciona un rol para el usuario.',
            'newUserRole.in' => 'Solo se permite crear usuarios administrador o gestor.',
            'editingUserId.required' => 'Selecciona un usuario valido.',
            'editName.required' => 'Ingresa el nombre del usuario.',
            'editName.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'editName.max' => 'El nombre de usuario no puede superar los 120 caracteres.',
            'editName.regex' => 'El nombre de usuario solo puede usar letras, numeros, puntos, guiones y guion bajo.',
            'editEmail.required' => 'Ingresa el correo del usuario.',
            'editEmail.min' => 'El correo debe tener al menos 3 caracteres antes de @correos.com.',
            'editEmail.max' => 'El correo no puede superar los 120 caracteres antes de @correos.com.',
            'editEmail.regex' => 'Ingresa solo el nombre del correo corporativo antes de @correos.com.',
            'editPassword.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'editPassword.max' => 'La contrasena no puede superar los 72 caracteres.',
            'editPassword.confirmed' => 'La confirmacion de contrasena no coincide.',
            'editPassword.required_with' => 'Ingresa la nueva contrasena completa para poder actualizarla.',
            'editPassword_confirmation.min' => 'La confirmacion debe tener al menos 8 caracteres.',
            'editPassword_confirmation.max' => 'La confirmacion no puede superar los 72 caracteres.',
            'editPassword_confirmation.required_with' => 'Confirma la nueva contrasena.',
            'editRole.required' => 'Selecciona un rol para el usuario.',
            'editRole.in' => 'Solo se permite asignar administrador o gestor.',
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

        return $localPart === '' ? '' : $localPart.'@correos.com';
    }

    private function normalizeRole(?string $role): string
    {
        return in_array($role, self::ALLOWED_ROLES, true) ? $role : 'gestor';
    }
}
