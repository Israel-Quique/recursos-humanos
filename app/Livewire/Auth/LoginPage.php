<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
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
            $user = Auth::user();
            $this->redirectRoute($user->must_change_password ? 'password.change' : ($user->acceso_personal ? 'mis-horas' : 'inicio'), navigate: true);
        }
    }

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
        ], [
            'email.required' => 'Ingresa tu usuario o correo.',
            'password.required' => 'Ingresa tu contrasena.',
        ]);

        $login = trim($credentials['email']);

        $key = 'login:'.hash('sha256', strtolower($login).'|'.request()->ip());
        $ipKey = 'login-ip:'.hash('sha256', (string) request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 60)) {
            $this->addError('email', 'Demasiados intentos. Espera un minuto antes de intentar de nuevo.');
            return;
        }
        RateLimiter::hit($key, 60);
        RateLimiter::hit($ipKey, 60);
        $user = User::query()->where('acceso_personal', false)
            ->where(fn ($q) => $q->where('email', $login)->orWhere('name', $login))
            ->get()->first(fn (User $candidate) => Hash::check($credentials['password'], $candidate->password));
        if (! $user) {
            $this->addError('email', 'Las credenciales no coinciden con nuestra base de datos.');
            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();
        session()->put('password_hash_web', $user->password);
        RateLimiter::clear($key);
        $this->reset('password');

        session()->flash('status', 'Bienvenido al sistema de recursos humanos.');

        $this->redirectRoute($user->must_change_password ? 'password.change' : ($user->acceso_personal ? 'mis-horas' : 'inicio'), navigate: true);
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
