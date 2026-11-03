<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Reusable one-time email code (OTP) for registration, enabling 2FA, and
 * password reset. Codes are 6 digits, hashed at rest, valid 10 minutes, and
 * emailed through the Admin -> Settings SMTP config. The code is always also
 * written to the log as a dev fallback when SMTP is not configured.
 */
class EmailOtp
{
    public const TTL_MINUTES = 10;

    /** Generate, store and email a code for ($email, $purpose). Returns the plain code. */
    public static function send(string $email, string $purpose, array $payload = [], ?string $subject = null): string
    {
        $email = mb_strtolower(trim($email));
        $code  = (string) random_int(100000, 999999);

        DB::table('email_otps')->where('email', $email)->where('purpose', $purpose)->delete();
        DB::table('email_otps')->insert([
            'email'      => $email,
            'purpose'    => $purpose,
            'code'       => Hash::make($code),
            'payload'    => $payload ? json_encode($payload) : null,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Email OTP [{$purpose}] for {$email}: {$code}");

        [$subjectDefault, $heading, $intro] = self::copyFor($purpose);
        $subject = $subject ?: $subjectDefault;

        try {
            if (Setting::applyMailConfig()) {
                Mail::send('emails.otp', [
                    'code'    => $code,
                    'heading' => $heading,
                    'intro'   => $intro,
                    'minutes' => self::TTL_MINUTES,
                ], fn ($m) => $m->to($email)->subject($subject));
                Setting::recordMailResult(null);
            }
        } catch (\Throwable $e) {
            report($e);
            Setting::recordMailResult('OTP email to ' . $email . ': ' . $e->getMessage());
        }

        return $code;
    }

    /**
     * Check a submitted code. On success the row is consumed (deleted) and the
     * stored payload is returned as ['payload' => array]; on failure, false.
     */
    public static function verify(string $email, string $purpose, string $code): array|false
    {
        $email = mb_strtolower(trim($email));
        $row = DB::table('email_otps')->where('email', $email)->where('purpose', $purpose)->orderByDesc('id')->first();
        if (!$row) {
            return false;
        }
        if ($row->expires_at && now()->greaterThan($row->expires_at)) {
            return false;
        }
        if (!Hash::check(trim($code), $row->code)) {
            return false;
        }
        DB::table('email_otps')->where('id', $row->id)->delete();
        return ['payload' => $row->payload ? (json_decode($row->payload, true) ?: []) : []];
    }

    /** Subject + heading + intro for each purpose. */
    private static function copyFor(string $purpose): array
    {
        return match ($purpose) {
            'register'       => ['Verify your email', 'Verify your email address', 'Enter the code below to finish creating your account.'],
            '2fa_enable'     => ['Your two-factor setup code', 'Confirm two-factor setup', 'Enter this code to turn on two-factor authentication for your account.'],
            'password_reset' => ['Your password reset code', 'Reset your password', 'Enter the code below to choose a new password. If you didn\'t ask to reset it, ignore this email.'],
            'login'          => ['Your login code', 'Sign-in verification', 'Enter this code to finish signing in to your account.'],
            default          => ['Your verification code', 'Your verification code', 'Use the code below to continue.'],
        };
    }

    public static function clear(string $email, string $purpose): void
    {
        DB::table('email_otps')->where('email', mb_strtolower(trim($email)))->where('purpose', $purpose)->delete();
    }
}
