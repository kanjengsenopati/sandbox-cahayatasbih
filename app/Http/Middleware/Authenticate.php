<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request and maintain real-time online presence for Backoffice Admins.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        try {
            if (Auth::guard('web')->check()) {
                $admin = Auth::guard('web')->user();
                if ($admin instanceof Admin) {
                    Cache::forget('admin_logged_out_' . $admin->id);
                    $needsRefresh = !$admin->last_login_at || $admin->last_login_at->diffInSeconds(now()) >= 120;
                    if ($needsRefresh) {
                        $now = now();
                        Admin::where('id', $admin->id)->update(['last_login_at' => $now]);
                        $admin->last_login_at = $now;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore presence heartbeat errors so request flow is never interrupted
        }

        return $next($request);
    }

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
