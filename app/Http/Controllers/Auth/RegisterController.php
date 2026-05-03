<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function index()
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
        $request->session()->regenerate();

        $request->session()->put('bound_ip', $request->ip());
        $request->session()->put('bound_ua', $request->userAgent());

        return redirect()->route('customer.dashboard');
    }
}
