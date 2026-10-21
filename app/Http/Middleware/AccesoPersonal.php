<?php

namespace App\Http\Middleware;

use App\Services\AccesoPersonalService;
use Closure;
use Illuminate\Http\Request;

class AccesoPersonal
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return new \Illuminate\Http\RedirectResponse(route('login'));
        }
        if ($user->acceso_personal) {
            $hash = $request->session()->get('password_hash_web');
            if ($hash && ! hash_equals($user->password, $hash)) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return new \Illuminate\Http\RedirectResponse(route('consulta-carnet'));
            }
            $request->session()->put('password_hash_web', $user->password);
        }
        abort_unless(app(AccesoPersonalService::class)->puedeIngresar($user), 403, 'Tu acceso a Mis horas fue deshabilitado. Contacta a RRHH.');
        if ($user->acceso_personal && ! in_array($request->route()?->getName(), ['mis-horas', 'perfil-horas', 'password.change', 'logout'], true)) {
            abort(403);
        }
        return $next($request);
    }
}
