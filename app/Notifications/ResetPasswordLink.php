<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Password-reset email, sent through the SMTP details saved in Admin → Settings
 * and rendered with the storefront's branded template.
 */
class ResetPasswordLink extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Use the SMTP settings saved in the admin panel (host, port, credentials, from-address).
        Setting::applyMailConfig();

        $expire = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);
        $url    = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));
        $store  = Setting::get('store_name', 'Zelari');

        return (new MailMessage)
            ->subject("Reset your {$store} password")
            ->view('emails.reset-password', [
                'user'   => $notifiable,
                'url'    => $url,
                'expire' => $expire,
                'store'  => [
                    'name'    => $store,
                    'email'   => Setting::get('store_email', ''),
                    'phone'   => Setting::get('store_phone', ''),
                    'address' => Setting::get('store_address', ''),
                ],
                'logo'   => zbrand('print'),
            ]);
    }
}
