<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// this middleware provides an application-layer safety net.

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        // Only redirect in production; allow HTTP in local development
        if (app()->environment('production') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
