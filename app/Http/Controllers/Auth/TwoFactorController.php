<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    /** Account security settings page. */
    public function settings()
    {
        return view('auth.two-factor-settings', ['user' => auth()->user()]);
    }

    public function enable(Request $request)
    {
        $request->user()->forceFill(['two_factor_enabled' => true])->save();
        return back()->with('success', 'Two-factor authentication enabled. You will be emailed a code at each login.');
    }

    public function disable(Request $request)
    {
        $request->user()->forceFill(['two_factor_enabled' => false, 'two_factor_code' => null, 'two_factor_expires_at' => null])->save();
        return back()->with('success', 'Two-factor authentication disabled.');
    }

    /** Generate + send the OTP, then show the challenge screen. Called from login. */
    public static function startChallenge(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'two_factor_code'       => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        // Always log the code (dev fallback); email it when mail is configured.
        Log::info("2FA code for {$user->email}: {$code}");
        try {
            if (Setting::applyMailConfig()) {
                Mail::raw("Your login code is: {$code}\n\nIt expires in 10 minutes.", fn ($m) => $m->to($user->email)->subject('Your login code'));
            }
        } catch (\Throwable $e) { /* code is still in the log */ }
    }

    public function challenge()
    {
        if (!session('2fa:user')) return redirect()->route('login');
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $userId = session('2fa:user');
        $user   = $userId ? User::find($userId) : null;
        if (!$user) return redirect()->route('login');

        if (!$user->two_factor_code || !$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)
            || !Hash::check($request->code, $user->two_factor_code)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
        session()->forget('2fa:user');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(session('2fa:intended', route('dashboard', absolute: false)));
    }
}
