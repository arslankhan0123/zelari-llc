<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmAttendanceBreak extends Model
{
    protected $table = 'hrm_attendance_breaks';
    protected $fillable = ['attendance_id', 'break_start', 'break_end'];
    protected $casts = ['break_start' => 'datetime', 'break_end' => 'datetime'];

    public function attendance(): BelongsTo { return $this->belongsTo(HrmAttendance::class, 'attendance_id'); }

    public function getMinutesAttribute(): int
    {
        if (!$this->break_start) return 0;
        $end = $this->break_end ?: now();
        return $this->break_start->diffInMinutes($end);
    }
}
