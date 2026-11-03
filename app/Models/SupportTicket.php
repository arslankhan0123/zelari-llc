<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SupportTicket extends Model
{
    protected $fillable = ['ref', 'subject', 'customer_name', 'customer_email', 'order_id',
        'status', 'priority', 'assigned_to', 'due_at', 'first_responded_at'];
    protected $casts = ['due_at' => 'datetime', 'first_responded_at' => 'datetime'];

    public function replies(): HasMany { return $this->hasMany(SupportTicketReply::class, 'ticket_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function getStatusColorAttribute(): string
    {
        return ['open' => 'blue', 'pending' => 'yellow', 'closed' => 'green'][$this->status] ?? 'gray';
    }
    public function getPriorityColorAttribute(): string
    {
        return ['low' => 'gray', 'normal' => 'blue', 'high' => 'yellow', 'urgent' => 'red'][$this->priority] ?? 'gray';
    }
    public function isOverdue(): bool
    {
        return $this->due_at && $this->status !== 'closed' && $this->due_at->isPast();
    }
}
