<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class CustomerLoginController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Login');
    }

    public function login(Request $request)
    {
        // Rate-limit: max 7 attempts per IP per minute (brute-force mitigation)
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 7)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['throttle' => "Too many login attempts. Try again in {$seconds} seconds."]);
        }

        // Server-side input whitelisting
        $request->validate([
            'account_number' => ['required', 'regex:/^\d{6,16}$/'],
            'password'       => ['required', 'string', 'min:8', 'max:72'],
        ]);

        $credentials = $request->only('account_number', 'password');

        if (Auth::attempt(['account_number' => $credentials['account_number'], 'password' => $credentials['password'], 'role' => 'customer'])) {
            RateLimiter::clear($key);

            // Regenerate session ID on login to prevent session fixation
            $request->session()->regenerate();

            // Store IP and User-Agent for session binding validation
            $request->session()->put('bound_ip', $request->ip());
            $request->session()->put('bound_ua', $request->userAgent());

            return redirect()->route('customer.dashboard');
        }

        RateLimiter::hit($key, 60);

        
        return back()->withErrors(['credentials' => 'Invalid account number or password.']);
    }
}
