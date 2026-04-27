<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// CheckRole — Role-Based Access Control (RBAC) gate.
//Ensures customers cannot reach employee routes and vice versa.
//Employees are never self-registered; their accounts are seeded at onboarding.
 
class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route($role === 'employee' ? 'employee.login' : 'login');
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'Unauthorized: insufficient role.');
        }

        return $next($request);
    }
}
