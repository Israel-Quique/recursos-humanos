<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExigirCambioPassword
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->must_change_password) {
            return new \Illuminate\Http\RedirectResponse(route('password.change'));
        }
        return $next($request);
    }
}
