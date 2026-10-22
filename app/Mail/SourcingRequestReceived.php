<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\SourcingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SourcingRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SourcingRequest $sourcing)
    {
        $this->sourcing->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        $count = $this->sourcing->items->count();

        return new Envelope(
            subject: "Sourcing request {$this->sourcing->reference} — {$count} " . ($count === 1 ? 'product' : 'products'),
            replyTo: [new Address($this->sourcing->email, $this->sourcing->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sourcing-request',
            text: 'emails.sourcing-request-text',
            with: [
                'sourcing' => $this->sourcing,
                'store'    => ['name' => Setting::get('store_name', 'Zelari')],
                'adminUrl' => route('admin.sourcing.index'),
            ],
        );
    }
}
