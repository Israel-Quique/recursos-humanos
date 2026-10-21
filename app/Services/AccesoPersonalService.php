<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\User;
use App\Support\SucursalNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class AccesoPersonalService
{
    public function ingresar(string $codigo, string $password): User
    {
        $codigo = trim($codigo);
        $key = 'login:'.hash('sha256', strtolower($codigo).'|'.request()->ip());
        $ipKey = 'login-ip:'.hash('sha256', (string) request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 60)) {
            throw ValidationException::withMessages(['carnet' => 'Demasiados intentos. Espera un minuto antes de intentar de nuevo.']);
        }
        RateLimiter::hit($key, 60);
        RateLimiter::hit($ipKey, 60);
        $empleados = Empleado::query()->where('codigo_biometrico', $codigo)->get();
        $empleado = $empleados->count() === 1 ? $empleados->first() : null;
        $user = $empleado?->user;
        if ($empleado && $this->habilitado($empleado) && ! $user && hash_equals($codigo, $password)) {
            $user = $this->crearCuenta($empleado);
        }
        if (! $empleado || ! $user?->acceso_personal || ! $this->habilitado($empleado) || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['carnet' => 'El carnet o la contraseña no son correctos, o tu acceso no está habilitado. Contacta a RRHH.']);
        }
        if (hash_equals($codigo, $password) && ! $user->must_change_password) {
            $user->update(['must_change_password' => true]);
        }
        Auth::login($user);
        session()->regenerate();
        session()->put('password_hash_web', $user->password);
        RateLimiter::clear($key);
        return $user;
    }

    public function destino(User $user): string
    {
        return $user->must_change_password ? route('password.change')
            : URL::signedRoute('perfil-horas', ['empleado' => $user->empleado_id], absolute: false);
    }

    public function habilitado(Empleado $empleado): bool
    {
        foreach ([['persona', (string) $empleado->id], ['sucursal', SucursalNormalizer::canonicalLabel($empleado->sucursal)], ['todos', '*']] as [$tipo, $referencia]) {
            $regla = DB::table('acceso_personal_reglas')->where(compact('tipo', 'referencia'))->first();
            if ($regla) {
                return (bool) $regla->habilitado;
            }
        }
        return false;
    }

    public function guardar(string $tipo, string $referencia, bool $habilitado): void
    {
        DB::transaction(function () use ($tipo, $referencia, $habilitado) {
            DB::table('acceso_personal_reglas')->updateOrInsert(compact('tipo', 'referencia'), [
                'habilitado' => $habilitado, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    // La cuenta se crea solo al ingresar por primera vez y si RRHH habilitó su acceso.
    public function crearCuenta(Empleado $empleado): User
    {
        return DB::transaction(function () use ($empleado) {
            $empleado = Empleado::query()->lockForUpdate()->findOrFail($empleado->id);
            abort_unless($this->habilitado($empleado), 403);
            if ($existente = $empleado->user()->first()) {
                abort_unless($existente->acceso_personal, 403);
                return $existente;
            }
            $codigo = (string) $empleado->codigo_biometrico;
            if ($codigo === '' || User::query()->where('username', $codigo)->orWhere('name', $codigo)->orWhere('email', $codigo)->exists()) {
                throw ValidationException::withMessages(['email' => 'RRHH debe revisar el código de acceso de esta persona.']);
            }
            return User::query()->create([
                'name' => $empleado->nombre_completo,
                'username' => $codigo,
                'email' => null,
                'password' => $codigo,
                'empleado_id' => $empleado->id,
                'acceso_personal' => true,
                'must_change_password' => true,
            ]);
        });
    }

    public function puedeIngresar(User $user): bool
    {
        return ! $user->acceso_personal || ($user->empleado && $this->habilitado($user->empleado));
    }

    public function autorizarEmpleado(Empleado $empleado): void
    {
        $user = auth()->user();
        abort_unless($user && $user->acceso_personal && $this->puedeIngresar($user) && ! $user->must_change_password, 403);
        abort_unless((int) $user->empleado_id === (int) $empleado->id, 403);
    }
}
