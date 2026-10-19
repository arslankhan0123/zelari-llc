<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public bool $forAdmin = false)
    {
        $this->order->loadMissing('items.product', 'customer');
    }

    public function envelope(): Envelope
    {
        $store = Setting::get('store_name', 'Zelari');
        $no    = $this->order->order_number;
        return new Envelope(
            subject: $this->forAdmin
                ? "🛒 New order #{$no} — PKR " . number_format($this->order->total, 0)
                : "Your {$store} order #{$no} is confirmed",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
            with: [
                'order'    => $this->order,
                'forAdmin' => $this->forAdmin,
                'store'    => [
                    'name'    => Setting::get('store_name', 'Zelari'),
                    'phone'   => Setting::get('store_phone', ''),
                    'address' => Setting::get('store_address', ''),
                    'email'   => Setting::get('store_email', Setting::get('mail_from_address', '')),
                ],
                'logo'     => zbrand('print'),
            ],
        );
    }

    public function attachments(): array
    {
        // Attach the PDF invoice when dompdf is installed
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return [];
        }
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shop.invoice', [
                'order' => $this->order, 'pdf' => true,
                'store' => ['name' => Setting::get('store_name', 'Zelari'), 'phone' => Setting::get('store_phone', ''), 'address' => Setting::get('store_address', ''), 'email' => Setting::get('store_email', Setting::get('mail_from_address', ''))],
            ])->setPaper('a4');
            return [
                Attachment::fromData(fn () => $pdf->output(), 'Zelari-Invoice-' . $this->order->order_number . '.pdf')->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }
}
