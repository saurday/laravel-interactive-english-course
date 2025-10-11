<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API: jangan redirect tamu ke login, cukup 401 JSON
        $middleware->redirectGuestsTo(
            fn (Request $request) =>
                ($request->is('api/*') || $request->expectsJson()) ? null : '/login'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ⚠️ Penting: paksa semua request ke /api/* dirender sebagai JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // Tangani AuthenticationException jadi 401 JSON (bukan redirect)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            // untuk web biarkan default (redirect ke /login)
        });
    })
    ->create();
