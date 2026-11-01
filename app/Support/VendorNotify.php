<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Lightweight vendor notifications. Emails use the Admin → Settings SMTP
 * config (Setting::applyMailConfig). Never throws — notifications must not
 * break product approval or checkout.
 */
class VendorNotify
{
    /** Resolve the best contact email for a vendor (store email, else owner). */
    public static function emailFor(Vendor $vendor): ?string
    {
        if ($vendor->email) {
            return $vendor->email;
        }
        return optional($vendor->user)->email;
    }

    /** Send a plain-text notification email to a vendor. Safe to call anywhere. */
    public static function send(Vendor $vendor, string $subject, string $body): void
    {
        try {
            $to = static::emailFor($vendor);
            if (!$to) {
                return;
            }

            // Always leave a trace even when SMTP is not configured yet.
            Log::info('[VendorNotify] ' . $vendor->name . ' <' . $to . '>: ' . $subject);

            if (!Setting::applyMailConfig()) {
                return;
            }

            $store = Setting::get('site_name', config('app.name', 'Zelari'));
            Mail::raw($body, function ($m) use ($to, $vendor, $subject, $store) {
                $m->to($to, $vendor->name)->subject($subject . ' — ' . $store);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
