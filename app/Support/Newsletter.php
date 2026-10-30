<?php

namespace App\Support;

use App\Mail\NewsletterCampaign;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Newsletter
{
    public static function enabled(): bool
    {
        try { return (string) Setting::get('newsletter_enabled', '0') === '1'; }
        catch (\Throwable $e) { return false; }
    }

    public static function frequency(): string
    {
        $f = (string) Setting::get('newsletter_frequency', 'weekly');
        return in_array($f, ['weekly', 'biweekly', 'monthly']) ? $f : 'weekly';
    }

    public static function subscribe(string $email): string
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'invalid';
        try {
            $s = Subscriber::firstOrNew(['email' => $email]);
            if ($s->exists && $s->active) return 'exists';
            $s->active = true;
            $s->save();
            return 'ok';
        } catch (\Throwable $e) { return 'error'; }
    }

    public static function unsubscribe(string $token): bool
    {
        try {
            $s = Subscriber::where('token', $token)->first();
            if ($s) { $s->active = false; $s->save(); return true; }
        } catch (\Throwable $e) {}
        return false;
    }

    public static function count(): int
    {
        try { return Subscriber::where('active', true)->count(); } catch (\Throwable $e) { return 0; }
    }

    /** Build the campaign content: top deals + new arrivals. */
    public static function content(): array
    {
        $map = fn ($p) => [
            'name'  => $p->name,
            'url'   => url('/product/'.$p->slug),
            'image' => $p->display_image,
            'price' => zprice((float) ($p->sale_price ?: $p->purchase_price)),
            'was'   => ($p->sale_price && $p->sale_price < $p->purchase_price) ? zprice((float) $p->purchase_price) : null,
        ];
        $base = fn () => Product::where('status', 'active')->whereNotNull('slug');
        $deals = $base()->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'purchase_price')->latest()->take(4)->get()->map($map)->all();
        $fresh = $base()->latest()->take(4)->get()->map($map)->all();
        return ['deals' => $deals, 'fresh' => $fresh];
    }

    /** Send one test email to a single address. */
    public static function sendTest(string $email): bool
    {
        if (!Setting::applyMailConfig()) return false;
        try {
            Mail::to($email)->send(new NewsletterCampaign(self::content(), url('/'), true));
            return true;
        } catch (\Throwable $e) { return false; }
    }

    /** Send the campaign to all active subscribers. Returns number queued. */
    public static function sendCampaign(): int
    {
        if (!Setting::applyMailConfig()) return 0;
        $content = self::content();
        $n = 0;
        try {
            Subscriber::where('active', true)->whereNotNull('email')->chunk(100, function ($chunk) use ($content, &$n) {
                foreach ($chunk as $sub) {
                    try {
                        Mail::to($sub->email)->send(new NewsletterCampaign($content, url('/newsletter/unsubscribe/'.$sub->token)));
                        $n++;
                    } catch (\Throwable $e) {}
                }
            });
            Setting::set('newsletter_last_sent', now()->toDateTimeString());
        } catch (\Throwable $e) {}
        return $n;
    }

    public static function due(): bool
    {
        if (!self::enabled()) return false;
        $last = Setting::get('newsletter_last_sent', null);
        if (!$last) return true;
        try { $days = now()->diffInDays(\Illuminate\Support\Carbon::parse($last)); }
        catch (\Throwable $e) { return true; }
        return match (self::frequency()) {
            'monthly'  => $days >= 28,
            'biweekly' => $days >= 14,
            default    => $days >= 7,
        };
    }
}
