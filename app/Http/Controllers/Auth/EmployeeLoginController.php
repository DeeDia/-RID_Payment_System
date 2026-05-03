<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class EmployeeLoginController extends Controller
{
    public function index()
    {
        return Inertia::render('Employee/Login');
    }

    public function login(Request $request)
    {
        $key = 'employee-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['throttle' => "Too many login attempts. Try again in {$seconds} seconds."]);
        }

        $request->validate([
            'employee_id' => ['required', 'regex:/^EMP\d{4,8}$/'],
            'password'    => ['required', 'string', 'min:8', 'max:72'],
        ]);

        if (Auth::attempt(['employee_id' => $request->employee_id, 'password' => $request->password, 'role' => 'employee'])) {
            RateLimiter::clear($key);

            $request->session()->regenerate();
            $request->session()->put('bound_ip', $request->ip());
            $request->session()->put('bound_ua', $request->userAgent());

            return redirect()->route('employee.dashboard');
        }

        RateLimiter::hit($key, 60);
        return back()->withErrors(['credentials' => 'Invalid employee ID or password.']);
    }
}
