<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /** Display the "enter your email" view. */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a one-time reset code (not a link). We never reveal whether the
     * address exists; the same confirmation shows either way.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower($request->email);

        // Require the account to exist before proceeding.
        if (!User::where('email', $email)->exists()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'No account was found with that email address.']);
        }

        EmailOtp::send($email, 'password_reset', [], 'Your password reset code');
        $request->session()->put('pwreset:email', $email);

        return redirect()->route('password.reset')
            ->with('status', 'A 6-digit reset code has been emailed to ' . $email . '. It expires in 10 minutes.');
    }
}
