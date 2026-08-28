<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Session-based login for the Blade admin panel — separate from the Sanctum
 * token auth in Api\V1\Auth\AuthController used by the React frontend. Same
 * `users` table underneath, different guard/mechanism, per the brief's
 * instruction to keep the admin UI and the public API cleanly separated.
 */
class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account does not have admin access.'])->onlyInput('email');
        }

        if ($user->status === UserStatus::Disabled) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account has been disabled.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
