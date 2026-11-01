<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const PAID     = 'paid';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'vendor_id', 'amount', 'status', 'method', 'reference',
        'note', 'admin_note', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::PENDING  => 'yellow',
            self::APPROVED => 'blue',
            self::PAID     => 'green',
            self::REJECTED => 'red',
            default        => 'gray',
        };
    }
}
