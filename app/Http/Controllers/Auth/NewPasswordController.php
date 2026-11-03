<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /** Show the "enter code + new password" view. */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->session()->get('pwreset:email', old('email', '')),
        ]);
    }

    /**
     * Verify the emailed code and set the new password.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'code'     => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = mb_strtolower($request->email);

        if (EmailOtp::verify($email, 'password_reset', $request->code) === false) {
            return back()->withInput($request->only('email'))
                ->withErrors(['code' => 'Invalid or expired code. Please request a new one.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'No account found for that email.']);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'real_password'  => $request->password,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
        $request->session()->forget('pwreset:email');

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
    }
}
