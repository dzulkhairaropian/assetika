<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && !($user->is_active ?? true)) {
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact administrator.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $hour = (int) now()->format('H');
            $greetingTime = match (true) {
                $hour >= 4 && $hour < 12 => 'Good morning',
                $hour >= 12 && $hour < 17 => 'Good afternoon',
                $hour >= 17 && $hour < 21 => 'Good evening',
                default => 'Good night',
            };

            $roleName = auth()->user()->roles->pluck('name')->first() ?? 'User';

            $messages = [
                "{$greetingTime}, " . auth()->user()->name . "! Ready to manage assets today?",
                "{$greetingTime}, " . auth()->user()->name . "! Logged in as " . strtoupper($roleName) . ".",
                "{$greetingTime}, " . auth()->user()->name . "! Have a productive session in Assetika.",
                "{$greetingTime}, " . auth()->user()->name . "! Everything is set and ready to go.",
            ];

            $randomGreeting = $messages[array_rand($messages)];
            $request->session()->flash('welcome_greeting', $randomGreeting);

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/signin');
    }
}
