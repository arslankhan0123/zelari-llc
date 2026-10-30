<?php

namespace App\Mail;

use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CouponWon extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Coupon $coupon, public string $prizeLabel, public string $customerName = '')
    {
    }

    public function envelope(): Envelope
    {
        $store = Setting::get('store_name', 'Zelari');
        return new Envelope(subject: "🎉 You won {$this->prizeLabel} at {$store}!");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.coupon-won',
            with: [
                'coupon'  => $this->coupon,
                'prize'   => $this->prizeLabel,
                'name'    => $this->customerName,
                'shopUrl' => route('shop.index'),
                'store'   => [
                    'name'  => Setting::get('store_name', 'Zelari'),
                    'email' => Setting::get('store_email', Setting::get('mail_from_address', '')),
                ],
                'logo'    => function_exists('zbrand') ? zbrand('print') : null,
            ],
        );
    }
}
