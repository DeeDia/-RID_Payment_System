<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ValidateSession — binds session to the originating IP + User-Agent.
 *
 * Mitigates session jacking / session hijacking:
 * If either the IP or User-Agent changes mid-session, the session is
 * immediately invalidated and the user is forced to re-authenticate.
 */
class ValidateSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $boundIp = $request->session()->get('bound_ip');
            $boundUa = $request->session()->get('bound_ua');

            $currentIp = $request->ip();
            $currentUa = $request->userAgent();

            if ($boundIp !== $currentIp || $boundUa !== $currentUa) {
                // Session binding violation — force logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['session' => 'Your session has been invalidated due to a security check. Please log in again.']);
            }

            // Enforce inactivity timeout
            $timeoutMinutes = Auth::user()->role === 'employee' ? 15 : 30;
            $lastActivity   = $request->session()->get('last_activity', now()->timestamp);

            if (now()->timestamp - $lastActivity > $timeoutMinutes * 60) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $route = Auth::user()?->role === 'employee' ? 'employee.login' : 'login';
                return redirect()->route($route)
                    ->withErrors(['session' => 'Your session has expired due to inactivity. Please log in again.']);
            }

            $request->session()->put('last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
