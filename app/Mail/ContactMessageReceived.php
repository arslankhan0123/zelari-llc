<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contact)
    {
    }

    public function envelope(): Envelope
    {
        $subject = $this->contact->subject
            ? "New contact message: {$this->contact->subject}"
            : "New contact message from {$this->contact->name}";

        return new Envelope(
            subject: $subject,
            // Hitting Reply in the mail client answers the customer directly.
            replyTo: [new Address($this->contact->email, $this->contact->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            // A plain-text part alongside the HTML: spam filters score HTML-only mail higher.
            text: 'emails.contact-message-text',
            with: [
                'contact' => $this->contact,
                'store'   => [
                    'name'  => Setting::get('store_name', 'Zelari'),
                    'email' => Setting::get('store_email', ''),
                ],
                'adminUrl' => route('admin.messages.index'),
            ],
        );
    }
}
