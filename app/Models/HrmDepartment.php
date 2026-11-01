<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrmDepartment extends Model
{
    protected $fillable = ['name', 'code', 'parent_id', 'head_employee_id', 'description'];

    public function parent(): BelongsTo { return $this->belongsTo(HrmDepartment::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(HrmDepartment::class, 'parent_id'); }
    public function employees(): HasMany { return $this->hasMany(HrmEmployee::class, 'department_id'); }
    public function head(): BelongsTo { return $this->belongsTo(HrmEmployee::class, 'head_employee_id'); }
}
