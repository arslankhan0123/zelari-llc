<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrmPayslip extends Model
{
    protected $fillable = ['employee_id', 'period', 'basic', 'allowances', 'deductions', 'net', 'status', 'paid_at', 'note'];
    protected $casts = [
        'basic' => 'decimal:2', 'allowances' => 'decimal:2', 'deductions' => 'decimal:2',
        'net' => 'decimal:2', 'paid_at' => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(HrmEmployee::class, 'employee_id'); }
}
