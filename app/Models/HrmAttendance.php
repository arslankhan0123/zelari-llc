<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmAttendance extends Model
{
    protected $table = 'hrm_attendances';
    protected $fillable = ['employee_id', 'date', 'check_in', 'check_out', 'status', 'note'];
    protected $casts = ['date' => 'date'];

    public function employee(): BelongsTo { return $this->belongsTo(HrmEmployee::class, 'employee_id'); }

    public function breaks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HrmAttendanceBreak::class, 'attendance_id');
    }

    /** The currently-open break (started, not ended), if any. */
    public function openBreak(): ?HrmAttendanceBreak
    {
        return $this->breaks()->whereNotNull('break_start')->whereNull('break_end')->latest('break_start')->first();
    }

    public function totalBreakMinutes(): int
    {
        return (int) $this->breaks()->get()->sum(fn ($b) => $b->minutes);
    }
}
