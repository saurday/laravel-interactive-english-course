<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{


protected function redirectTo(Request $request): ?string
{
    if ($request->expectsJson() || $request->is('api/*')) {
        return null; // -> 401 JSON, tidak redirect
    }
    return '/login'; // jangan pakai route('login') kalau tidak punya named route
}
}
