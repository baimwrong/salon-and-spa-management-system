<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin(string $role)
    {
        if (!app()->environment('local')) {
            abort(403, 'Quick Demo Switch is only enabled in local development mode.');
        }

        $validRoles = ['admin', 'manager', 'receptionist', 'therapist'];
        if (!in_array($role, $validRoles)) {
            return redirect()->route('login')->with('error', 'Invalid role requested.');
        }

        $user = User::where('role', $role)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', "No demo account found for role [{$role}]. Please run database seeders.");
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Logged in as {$user->name} ({$user->role}) via Quick Demo switch.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out successfully.');
    }
}
