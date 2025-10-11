<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{


    protected function redirectTo(Request $request): ?string
{
    if ($request->expectsJson() || $request->is('api/*')) {
        return null; // biar 401, bukan redirect
    }
    return '/login'; // atau route('login') jika ada
}
}
