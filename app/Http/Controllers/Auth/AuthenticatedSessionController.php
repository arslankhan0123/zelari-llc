<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $u = $request->user();

        // Opt-in two-factor: email a one-time code and hold login until verified.
        if ($u->two_factor_enabled) {
            $intended = $this->redirectTargetFor($u);
            \App\Http\Controllers\Auth\TwoFactorController::startChallenge($u);
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            $request->session()->put('2fa:user', $u->id);
            $request->session()->put('2fa:intended', $intended);
            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended($this->redirectTargetFor($u));
    }

    private function redirectTargetFor($u): string
    {
        if ($u->isVendor()) {
            return route('admin.dashboard', absolute: false);
        }
        if ($u->isMasterAdmin() || $u->isAdmin() || ($u->role && count($u->role->permissionStrings()) > 0)) {
            return route('dashboard', absolute: false);
        }
        return route('home', absolute: false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
