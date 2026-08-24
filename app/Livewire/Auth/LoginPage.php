<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboard');
        }
    }

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Ingresa tu usuario o correo.',
            'password.required' => 'Ingresa tu contrasena.',
        ]);

        $login = trim($credentials['email']);

        $user = User::query()
            ->where('email', $login)
            ->orWhere('name', $login)
            ->get()
            ->first(fn (User $candidate) => Hash::check($credentials['password'], $candidate->password));

        if (! $user) {
            $this->addError('email', 'Las credenciales no coinciden con nuestra base de datos.');
            return;
        }

        Auth::login($user, $this->remember);
        request()->session()->regenerate();

        session()->flash('status', 'Bienvenido al sistema de recursos humanos.');

        // After regenerating the session, force a full redirect so Livewire
        // boots with the fresh CSRF token instead of the guest-page token.
        $this->redirectRoute('dashboard');
    }

    public function togglePassword(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function render()
    {
        return view('livewire.auth.login', [
            'dbConnection' => config('database.default'),
            'pgConfigured' => extension_loaded('pdo_pgsql'),
        ]);
    }
}
