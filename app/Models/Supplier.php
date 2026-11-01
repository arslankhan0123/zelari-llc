<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Supplier extends Model
{
    protected $fillable = ['vendor_id', 'name', 'email', 'phone', 'address', 'notes'];
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Vendor::class); }
}
