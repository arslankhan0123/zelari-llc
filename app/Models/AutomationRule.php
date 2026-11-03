<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AutomationRule extends Model
{
    protected $fillable = ['name','event','conditions','actions','enabled','run_count','last_run_at'];
    protected $casts = ['conditions'=>'array','actions'=>'array','enabled'=>'boolean','last_run_at'=>'datetime'];
    public function runs(): HasMany { return $this->hasMany(AutomationRun::class, 'rule_id'); }
}
