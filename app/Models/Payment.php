<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'provider', 'status', 'payment_intent_id', 'charge_id', 'amount', 'currency',
        'card_brand', 'card_last4', 'card_fingerprint', 'card_exp_month', 'card_exp_year', 'card_funding', 'card_country', 'card_wallet',
        'cardholder_name', 'billing_email', 'risk_level', 'risk_score', 'receipt_url', 'failure_message', 'ip_address', 'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Short, human-friendly card ID shown in the admin (derived from Stripe's fingerprint). */
    public function getCardIdAttribute(): ?string
    {
        if (!$this->card_fingerprint) return null;
        $h = strtoupper(substr(hash('sha256', $this->card_fingerprint), 0, 8));
        return substr($h, 0, 4) . '-' . substr($h, 4, 4);
    }

    /** Other payments made with this exact card. */
    public function sameCardPayments()
    {
        return static::where('card_fingerprint', $this->card_fingerprint)->where('id', '!=', $this->id)->with('order')->latest();
    }

    /** "Visa •••• 4242" */
    public function getCardLabelAttribute(): ?string
    {
        if (!$this->card_last4) return null;
        return ucfirst((string) $this->card_brand) . ' •••• ' . $this->card_last4;
    }

    /** "12/27" */
    public function getCardExpiryAttribute(): ?string
    {
        return $this->card_exp_month ? sprintf('%02d/%02d', $this->card_exp_month, $this->card_exp_year % 100) : null;
    }

    public function getProviderLabelAttribute(): string
    {
        return ['stripe' => 'Card (Stripe)', 'paypal' => 'PayPal', 'cod' => 'Cash on Delivery'][$this->provider] ?? ucfirst($this->provider);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid'      => 'green',
            'pending'   => 'yellow',
            'failed'    => 'red',
            'cancelled' => 'gray',
            'refunded'  => 'blue',
            default     => 'gray',
        };
    }

    /** Formatted amount in the payment's own currency. */
    public function getAmountLabelAttribute(): string
    {
        $cur = \App\Support\Currency::all()[$this->currency] ?? ['symbol' => $this->currency, 'decimals' => 2];
        return \App\Support\Currency::format((float) $this->amount, ['symbol' => $cur['symbol'], 'rate' => 1, 'decimals' => $cur['decimals'] ?? 2]);
    }
}
