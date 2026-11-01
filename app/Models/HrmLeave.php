<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmLeave extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'from_date', 'to_date', 'days',
        'reason', 'status', 'decided_by', 'decided_at',
    ];
    protected $casts = ['from_date' => 'date', 'to_date' => 'date', 'decided_at' => 'datetime'];

    public function employee(): BelongsTo { return $this->belongsTo(HrmEmployee::class, 'employee_id'); }
    public function type(): BelongsTo { return $this->belongsTo(HrmLeaveType::class, 'leave_type_id'); }
    public function decider(): BelongsTo { return $this->belongsTo(User::class, 'decided_by'); }

    public function getStatusColorAttribute(): string
    {
        return ['pending' => 'yellow', 'approved' => 'green', 'rejected' => 'red'][$this->status] ?? 'gray';
    }
}
