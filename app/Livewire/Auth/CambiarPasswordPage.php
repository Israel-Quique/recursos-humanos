<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class CambiarPasswordPage extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function guardar(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
        ]);
        $user = auth()->user();
        if ($user->empleado && hash_equals((string) $user->empleado->codigo_biometrico, $this->password)) {
            $this->addError('password', 'La nueva contraseña debe ser diferente de tu código biométrico.');
            return;
        }
        $user->forceFill(['password' => $this->password, 'must_change_password' => false, 'remember_token' => Str::random(60)])->save();
        session()->regenerate();
        session()->put('password_hash_web', $user->password);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->redirect($user->acceso_personal ? app(\App\Services\AccesoPersonalService::class)->destino($user) : route('inicio'));
    }

    public function render()
    {
        return view('livewire.auth.cambiar-password');
    }
}
