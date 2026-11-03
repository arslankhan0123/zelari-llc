<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ReturnRequest extends Model
{
    public const REQUESTED = 'requested';
    public const APPROVED  = 'approved';
    public const REJECTED  = 'rejected';
    public const COMPLETED = 'completed';

    protected $fillable = ['ref', 'order_id', 'customer_email', 'type', 'reason',
        'status', 'amount', 'admin_note', 'processed_by', 'processed_at'];
    protected $casts = ['amount' => 'decimal:2', 'processed_at' => 'datetime'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }

    public function getStatusColorAttribute(): string
    {
        return [self::REQUESTED => 'yellow', self::APPROVED => 'blue', self::REJECTED => 'red', self::COMPLETED => 'green'][$this->status] ?? 'gray';
    }
}
