<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => route(
            $request->is('mis-horas', 'perfil-horas/*', 'cambiar-contrasena') ? 'consulta-carnet' : 'login'
        ));
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => route(
            $request->user()?->must_change_password ? 'password.change' : ($request->user()?->acceso_personal ? 'mis-horas' : 'inicio')
        ));
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
