<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaign extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data, public string $unsubscribeUrl, public bool $isTest = false)
    {
    }

    public function envelope(): Envelope
    {
        $store = Setting::get('store_name', 'Zelari');
        return new Envelope(subject: ($this->isTest ? '[TEST] ' : '').'New deals & arrivals at '.$store.' 🛍️');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter', with: [
            'deals'   => $this->data['deals'] ?? [],
            'fresh'   => $this->data['fresh'] ?? [],
            'shopUrl' => url('/shop'),
            'unsub'   => $this->unsubscribeUrl,
            'store'   => Setting::get('store_name', 'Zelari'),
            'logo'    => function_exists('zbrand') ? zbrand('print') : null,
        ]);
    }
}
