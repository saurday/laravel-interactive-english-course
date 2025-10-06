<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Override default redirect behavior for API requests.
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }
}
