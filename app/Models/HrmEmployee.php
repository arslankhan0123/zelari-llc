<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HrmEmployee extends Model
{
    protected $fillable = [
        'user_id', 'department_id', 'code', 'name', 'email', 'phone',
        'designation', 'employment_type', 'join_date', 'base_salary', 'status', 'photo', 'address',
    ];
    protected $casts = ['join_date' => 'date', 'base_salary' => 'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($e) {
            if (empty($e->code)) {
                do { $code = 'EMP-' . strtoupper(Str::random(5)); } while (static::where('code', $code)->exists());
                $e->code = $code;
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(HrmDepartment::class, 'department_id'); }
    public function leaves(): HasMany { return $this->hasMany(HrmLeave::class, 'employee_id'); }
    public function attendances(): HasMany { return $this->hasMany(HrmAttendance::class, 'employee_id'); }
    public function payslips(): HasMany { return $this->hasMany(HrmPayslip::class, 'employee_id'); }

    public function scopeActive($q) { return $q->where('status', 'active'); }
}
