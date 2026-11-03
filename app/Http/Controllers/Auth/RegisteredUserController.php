<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /** Display the registration view. */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Step 1 — validate details, email a verification code, and hold the
     * pending signup. The account is NOT created until the code is verified.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = mb_strtolower($request->email);

        EmailOtp::send($email, 'register', [
            'name'     => $request->name,
            'email'    => $email,
            'password' => Hash::make($request->password), // stored hashed; never in plain text
        ], 'Verify your email');

        $request->session()->put('register:email', $email);

        return redirect()->route('register.verify')
            ->with('status', 'We emailed a 6-digit code to ' . $email . '. Enter it to finish creating your account.');
    }

    /** Step 2 — show the code entry screen. */
    public function verifyForm(Request $request): RedirectResponse|View
    {
        if (!$request->session()->get('register:email')) {
            return redirect()->route('register');
        }
        return view('auth.register-verify', ['email' => $request->session()->get('register:email')]);
    }

    /** Step 2 — verify the code, then create the account and sign in. */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);
        $email = $request->session()->get('register:email');
        if (!$email) {
            return redirect()->route('register');
        }

        $res = EmailOtp::verify($email, 'register', $request->code);
        if ($res === false) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $p = $res['payload'];
        // Guard against a duplicate created meanwhile.
        if (User::where('email', $email)->exists()) {
            $request->session()->forget('register:email');
            return redirect()->route('login')->with('status', 'That email is already registered. Please log in.');
        }

        $user = User::create([
            'name'              => $p['name'] ?? 'User',
            'email'             => $p['email'] ?? $email,
            'password'          => $p['password'],      // already a bcrypt hash (the hashed cast keeps it)
            'role_id'           => Role::idFor(Role::USER),
            'email_verified_at' => now(),               // verified via the OTP
        ]);

        $request->session()->forget('register:email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /** Resend the code for the pending signup. */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('register:email');
        if (!$email) {
            return redirect()->route('register');
        }
        // Keep the original pending payload if it is still valid; otherwise ask to restart.
        $existing = \Illuminate\Support\Facades\DB::table('email_otps')->where('email', $email)->where('purpose', 'register')->orderByDesc('id')->first();
        if (!$existing || !$existing->payload) {
            return redirect()->route('register')->with('status', 'Your session expired. Please register again.');
        }
        EmailOtp::send($email, 'register', json_decode($existing->payload, true) ?: [], 'Verify your email');
        return back()->with('status', 'A new code is on its way to ' . $email . '.');
    }
}
