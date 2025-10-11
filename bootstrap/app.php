<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    // HAPUS / GANTI baris redirect default-mu dengan ini:
    $middleware->redirectGuestsTo(function (Request $request) {
        // Untuk API: jangan redirect, cukup 401 JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }
        // Kalau ingin redirect di web route, pastikan URL/route ini ada:
        return '/login'; // atau route('login') kalau memang ada rutenya
    });
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
