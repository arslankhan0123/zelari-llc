<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::allValues();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_enabled'       => 'nullable|boolean',
            'mail_mailer'        => 'required|in:smtp,log',
            'mail_host'          => 'nullable|string|max:190',
            'mail_port'          => 'nullable|integer|min:1|max:65535',
            'mail_encryption'    => 'required|in:tls,ssl,none',
            'mail_verify_peer'   => 'nullable|boolean',
            'mail_username'      => 'nullable|string|max:190',
            'mail_password'      => 'nullable|string|max:190',
            'mail_from_address'  => 'nullable|email|max:190',
            'mail_from_name'     => 'nullable|string|max:120',
            'mail_admin_address' => 'nullable|email|max:190',
            'store_name'         => 'nullable|string|max:120',
            'store_tagline'      => 'nullable|string|max:120',
            'store_phone'        => 'nullable|string|max:60',
            'store_whatsapp'     => 'nullable|string|max:60',
            'store_email'        => 'nullable|email|max:190',
            'store_address'      => 'nullable|string|max:255',
            'store_hours'        => 'nullable|string|max:120',
            'store_facebook'     => 'nullable|url|max:255',
            'store_instagram'    => 'nullable|url|max:255',
            'store_twitter'      => 'nullable|url|max:255',
            'store_pinterest'    => 'nullable|url|max:255',
        ]);

        $data['mail_enabled']     = $request->boolean('mail_enabled') ? '1' : '0';
        $data['mail_verify_peer'] = $request->boolean('mail_verify_peer') ? '1' : '0';

        // Keep the stored password when the field is left blank
        if (($data['mail_password'] ?? '') === '') {
            unset($data['mail_password']);
        }

        Setting::setMany($data);

        return back()->with('success', 'Settings saved successfully.');
    }

    /** POST /admin/settings/branding — logo, icon and print logo uploads */
    public function updateBranding(Request $request)
    {
        $request->validate([
            'brand_logo'  => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'brand_icon'  => 'nullable|file|mimes:png,ico,svg,jpg,jpeg,webp|max:1024',
            'brand_print' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ], [
            'brand_print.mimes' => 'The print logo must be a PNG or JPG (PDF generation cannot use SVG).',
        ]);

        $dir = public_path('uploads/branding');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $changed = [];
        foreach (['brand_logo', 'brand_icon', 'brand_print'] as $key) {
            // Remove
            if ($request->boolean('remove_' . $key)) {
                $old = Setting::get($key);
                if ($old && is_file(public_path($old))) @unlink(public_path($old));
                Setting::set($key, '');
                $changed[] = $key;
                continue;
            }
            // Upload
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension());
                if ($ext === 'svg' && !$this->safeSvg($file->getRealPath())) {
                    return back()->with('error', 'That SVG contains scripts or external references and was rejected. Export a plain SVG or use PNG.');
                }
                $name = substr($key, 6) . '-' . substr(md5((string) microtime(true)), 0, 8) . '.' . $ext;
                $old  = Setting::get($key);
                $file->move($dir, $name);
                if ($old && is_file(public_path($old))) @unlink(public_path($old));
                Setting::set($key, 'uploads/branding/' . $name);
                $changed[] = $key;
            }
        }

        return back()->with('success', $changed ? 'Branding updated.' : 'No branding changes were made.');
    }

    /** Reject SVGs that could run code when inlined. */
    private function safeSvg(string $path): bool
    {
        $svg = strtolower((string) @file_get_contents($path));
        foreach (['<script', 'javascript:', 'onload=', 'onerror=', 'onclick=', '<foreignobject', 'xlink:href="http', 'href="http', '<iframe', '<embed', '<object'] as $bad) {
            if (str_contains($svg, $bad)) return false;
        }
        return true;
    }

    /** PUT /admin/settings/payments */
    public function updatePayments(Request $request)
    {
        $data = $request->validate([
            'pay_cod_enabled'        => 'nullable|boolean',
            'pay_stripe_enabled'     => 'nullable|boolean',
            'stripe_publishable_key' => ['nullable', 'string', 'max:255', 'regex:/^pk_(test|live)_[A-Za-z0-9]+$/'],
            'stripe_secret_key'      => ['nullable', 'string', 'max:255', 'regex:/^(sk|rk)_(test|live)_[A-Za-z0-9]+$/'],
            'stripe_webhook_secret'  => ['nullable', 'string', 'max:255', 'regex:/^whsec_[A-Za-z0-9]+$/'],
            'pay_paypal_enabled'     => 'nullable|boolean',
            'paypal_mode'            => 'nullable|in:sandbox,live',
            'paypal_client_id'       => 'nullable|string|max:255',
            'paypal_secret'          => 'nullable|string|max:255',
        ], [
            'stripe_publishable_key.regex' => 'The publishable key should start with pk_test_ or pk_live_.',
            'stripe_secret_key.regex'      => 'The secret key should start with sk_test_ or sk_live_.',
            'stripe_webhook_secret.regex'  => 'The webhook signing secret should start with whsec_.',
        ]);

        $data['pay_cod_enabled']    = $request->boolean('pay_cod_enabled') ? '1' : '0';
        $data['pay_stripe_enabled'] = $request->boolean('pay_stripe_enabled') ? '1' : '0';
        $data['pay_paypal_enabled'] = $request->boolean('pay_paypal_enabled') ? '1' : '0';
        $data['paypal_mode']        = ($data['paypal_mode'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';
        $data['paypal_client_id']   = trim((string) ($data['paypal_client_id'] ?? ''));

        // Blank secret fields keep what is already stored (they are never echoed back to the form)
        foreach (['stripe_secret_key', 'stripe_webhook_secret', 'paypal_secret'] as $k) {
            if (($data[$k] ?? '') === '') unset($data[$k]);
        }
        $data['stripe_publishable_key'] = trim((string) ($data['stripe_publishable_key'] ?? ''));

        // Clearing the publishable key clears the mode mismatch risk: warn if test/live keys are mixed
        $pub = $data['stripe_publishable_key'];
        $sec = $data['stripe_secret_key'] ?? Setting::get('stripe_secret_key', '');
        if ($pub && $sec && (str_contains($pub, '_test_') !== str_contains($sec, '_test_'))) {
            return back()->withInput()->with('error', 'Your Stripe publishable key and secret key are from different modes (test vs live). Use a matching pair.');
        }

        $ppSecret  = $data['paypal_secret'] ?? Setting::get('paypal_secret', '');
        $ppReady   = $data['pay_paypal_enabled'] === '1' && $data['paypal_client_id'] !== '' && $ppSecret !== '';
        $stripeOk  = $data['pay_stripe_enabled'] === '1' && $pub && $sec;
        if ($data['pay_cod_enabled'] === '0' && !$stripeOk && !$ppReady) {
            return back()->withInput()->with('error', 'At least one payment method must stay enabled. Turn Cash on Delivery back on or finish the Stripe / PayPal setup.');
        }

        Setting::setMany($data);
        \Illuminate\Support\Facades\Cache::forget('settings.all');
        if (isset($data['paypal_secret']) || $data['paypal_client_id'] !== (string) Setting::get('paypal_client_id', '')) {
            \Illuminate\Support\Facades\Cache::flush(); // drop any PayPal token cached for old credentials
        }

        // Verify the keys actually work so a typo is caught here, not at checkout
        $notes = [];
        if ($data['pay_stripe_enabled'] === '1') {
            try {
                \App\Support\Payments::stripe('GET', '/v1/balance');
                $notes[] = 'Stripe connected in ' . (str_contains($pub, '_test_') ? 'test' : 'live') . ' mode.';
            } catch (\Throwable $e) {
                return back()->with('error', 'Settings saved, but Stripe rejected the secret key: ' . $e->getMessage());
            }
        }
        if ($data['pay_paypal_enabled'] === '1') {
            if (!$ppReady) {
                return back()->with('error', 'Settings saved, but PayPal is enabled without a Client ID and Secret — it will not show at checkout until both are entered.');
            }
            try {
                \App\Support\PayPal::token(true);
                $notes[] = 'PayPal connected in ' . $data['paypal_mode'] . ' mode.';
            } catch (\Throwable $e) {
                return back()->with('error', 'Settings saved, but PayPal rejected the credentials: ' . $e->getMessage() . ' (sandbox and live use different keys — check the mode).');
            }
        }

        return back()->with('success', trim('Payment settings saved. ' . implode(' ', $notes)));
    }

    public function test(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        if (!Setting::applyMailConfig()) {
            return back()->with('error', 'Email is disabled or the SMTP host is empty. Save your SMTP details first.');
        }

        // ── Stage 1: can we even open a TCP socket to the server? ──
        $host = trim((string) Setting::get('mail_host'));
        $port = (int) Setting::get('mail_port', 587);
        if (Setting::get('mail_mailer', 'smtp') === 'smtp') {
            $errno = 0; $errstr = '';
            $sock = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 10);
            if (!$sock) {
                $msg = "Cannot reach {$host}:{$port} (TCP) — {$errstr}. The port is blocked by a firewall/antivirus/ISP, or the host is wrong.";
                Setting::recordMailResult($msg);
                return back()->with('error', $msg);
            }
            // ── Stage 2: on 465, test the TLS handshake directly for a clearer message ──
            if ($port === 465) {
                stream_set_timeout($sock, 15);
                $verify = Setting::get('mail_verify_peer', '1') === '1';
                stream_context_set_option($sock, 'ssl', 'verify_peer', $verify);
                stream_context_set_option($sock, 'ssl', 'verify_peer_name', $verify);
                stream_context_set_option($sock, 'ssl', 'SNI_enabled', true);
                stream_context_set_option($sock, 'ssl', 'peer_name', $host);
                $ok = @stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($ok !== true) {
                    fclose($sock);
                    $msg = "TCP to {$host}:465 works, but the TLS handshake fails/times out. This is almost always antivirus 'mail shield' / SSL-scanning or a local firewall interfering with PHP. Try: port 587 with TLS, turn off 'Verify TLS certificate' below, or whitelist php.exe in your antivirus.";
                    Setting::recordMailResult($msg);
                    return back()->with('error', $msg);
                }
            }
            fclose($sock);
        }

        try {
            Mail::raw(
                "This is a test email from " . Setting::get('store_name', 'Zelari') . ".\n\nIf you can read this, your SMTP settings are working.",
                fn ($m) => $m->to($request->test_email)->subject('✅ Test email from ' . Setting::get('store_name', 'Zelari'))
            );
        } catch (\Throwable $e) {
            report($e);
            Setting::recordMailResult($e->getMessage());
            return back()->with('error', 'Sending failed: ' . $e->getMessage());
        }

        Setting::recordMailResult(null);
        return back()->with('success', "Test email sent to {$request->test_email}. Check the inbox (and spam folder).");
    }
}
