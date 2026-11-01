<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HrmLeaveType extends Model
{
    protected $fillable = ['name', 'annual_quota', 'paid', 'active'];
    protected $casts = ['paid' => 'boolean', 'active' => 'boolean'];
}
