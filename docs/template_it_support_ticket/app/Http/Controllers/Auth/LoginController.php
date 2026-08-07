<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $base = config('assetika.base_url');
        $timeout = config('assetika.timeout');

        try {
            $response = Http::timeout($timeout)
                ->post("{$base}/login", [
                    'email' => $credentials['email'],
                    'password' => $credentials['password'],
                ]);

            if ($response->successful()) {
                $user = User::where('email', $credentials['email'])->first();

                if ($user) {
                    Auth::login($user, $request->boolean('remember'));
                    $request->session()->regenerate();

                    return redirect()->intended(route('dashboard'));
                }

                return back()->withErrors([
                    'email' => 'User not found locally. Run sync command first.',
                ])->onlyInput('email');
            }
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Cannot connect to Assetika authentication server.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
