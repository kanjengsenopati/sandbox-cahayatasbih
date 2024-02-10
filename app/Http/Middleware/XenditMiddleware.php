<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XenditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-CALLBACK-TOKEN') != env('XENDIT_TOKEN_CALLBACK')) {
            return response()->json(['message' => 'token invalid'], 401);
        }
        return $next($request);
    }
}
