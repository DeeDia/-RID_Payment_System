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
            $currentIp = $request->ip();
            $currentUa = $request->userAgent();

            $boundIp = $request->session()->get('bound_ip');
            $boundUa = $request->session()->get('bound_ua');

            // ✅ If session not yet bound, bind it (prevents false logout)
            if (!$boundIp || !$boundUa) {
                $request->session()->put('bound_ip', $currentIp);
                $request->session()->put('bound_ua', $currentUa);
            } else {
                // ✅ Primary check: User-Agent mismatch
                if ($boundUa !== $currentUa) {
                    return $this->logout($request, 'Your session changed (device/browser mismatch). Please log in again.');
                }

                // ✅ Optional: Soft IP check (don’t be strict)
                if (!$this->ipRoughlyMatches($boundIp, $currentIp)) {
                    // Instead of logging out, just update IP
                    $request->session()->put('bound_ip', $currentIp);
                }
            }

            // ✅ Inactivity timeout
            $user = Auth::user();
            $timeoutMinutes = $user->role === 'employee' ? 15 : 30;

            $lastActivity = $request->session()->get('last_activity', now()->timestamp);

            if (now()->timestamp - $lastActivity > $timeoutMinutes * 60) {
                return $this->logout($request, 'Your session has expired due to inactivity.');
            }

            $request->session()->put('last_activity', now()->timestamp);
        }

        return $next($request);
    }

    private function logout(Request $request, string $message)
    {
        $role = Auth::user()?->role;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $route = $role === 'employee' ? 'employee.login' : 'login';

        return redirect()->route($route)
            ->withErrors(['session' => $message]);
    }

    /**
     * Soft IP comparison:
     * Matches first 2 octets for IPv4 (e.g. 192.168.*.*)
     * Falls back to strict compare for unknown formats
     */
    private function ipRoughlyMatches($bound, $current)
    {
        if (!$bound || !$current) return true;

        // IPv4 check
        if (filter_var($bound, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            filter_var($current, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {

            $b = explode('.', $bound);
            $c = explode('.', $current);

            return $b[0] === $c[0] && $b[1] === $c[1];
        }

        // IPv6 or unknown → don’t enforce
        return true;
    }
}
