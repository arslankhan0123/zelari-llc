<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VendorReview extends Model
{
    protected $fillable = ['vendor_id','order_id','author_name','author_email','rating','body','status'];
    protected $casts = ['rating'=>'integer'];
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function scopeApproved($q) { return $q->where('status','approved'); }
}
