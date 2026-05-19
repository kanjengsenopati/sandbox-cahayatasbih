<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('wali/*') || $request->is('api/wali/*') || $request->is('api/ct-mobile/*') || $request->is('ct-mobile/*')) {
            return route('wali.login');
        }

        return route('login');
    }
}
