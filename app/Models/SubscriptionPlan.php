<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SubscriptionPlan extends Model
{
    protected $fillable = ['name','price','product_limit','commission_rate','features','enabled','sort'];
    protected $casts = ['price'=>'decimal:2','product_limit'=>'integer','commission_rate'=>'decimal:2','features'=>'array','enabled'=>'boolean'];
    public function vendors(): HasMany { return $this->hasMany(Vendor::class, 'plan_id'); }
}
