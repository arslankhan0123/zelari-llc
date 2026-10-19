<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Outgoing mail uses the SMTP details saved in Admin → Settings.
        if (!Setting::applyMailConfig()) {
            report(new \RuntimeException('Password reset requested but SMTP is not configured in Admin → Settings.'));
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email sending is not set up yet. Please contact us and we will reset your password for you.']);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);
            Setting::recordMailResult($e->getMessage());
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not send the email right now. Please try again in a few minutes or contact us.']);
        }

        // Don't reveal whether the address exists; always show the same confirmation.
        if ($status === Password::RESET_LINK_SENT || $status === Password::INVALID_USER) {
            return back()->with('status', 'If an account exists for that email, a reset link is on its way. It expires in '
                . (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) . ' minutes.');
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
