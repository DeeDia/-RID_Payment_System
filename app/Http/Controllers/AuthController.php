<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    // Registration

    public function showRegister()
    {
        return Inertia::render('Customer/Register');
    }

    public function register(Request $request)
    {
        // Rate-limit registration to prevent automated account creation
        $key = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['throttle' => "Too many attempts. Try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($key, 60);

        // Strict server-side input whitelisting via RegEx
        $request->validate([

            'full_name'      => ['required', 'regex:/^[A-Za-z\s\-\']{2,80}$/'],

            'id_number'      => ['required', 'regex:/^\d{13}$/', 'unique:users,id_number'],

            'account_number' => ['required', 'regex:/^\d{6,16}$/', 'unique:users,account_number'],

            'password'       => [
                'required',
                'confirmed',
                'min:8',
                'max:72',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'full_name.regex'      => 'Full name may only contain letters, spaces, hyphens and apostrophes.',
            'id_number.regex'      => 'ID number must be exactly 13 digits.',
            'account_number.regex' => 'Account number must be 6–16 digits.',
            'password.regex'       => 'Password must include uppercase, lowercase, number and special character.',
        ]);


        $user = User::create([
            'full_name'      => $request->full_name,
            'id_number'      => $request->id_number,
            'account_number' => $request->account_number,
            'password'       => Hash::make($request->password, ['rounds' => 12]),
            'role'           => 'customer',
        ]);

        Auth::login($user);

        return redirect()->route('customer.dashboard');
    }

    // Customer Login 

    public function showLogin()
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

    //Employee Login 

    public function showEmployeeLogin()
    {
        return Inertia::render('Employee/Login');
    }

    public function employeeLogin(Request $request)
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

    //LogoutPage

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
