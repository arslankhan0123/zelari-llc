<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StockMovement extends Model
{
    protected $fillable = ['product_id', 'variation_id', 'change', 'balance', 'type', 'reason', 'user_id'];
    protected $casts = ['change' => 'integer', 'balance' => 'integer'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variation(): BelongsTo { return $this->belongsTo(ProductVariation::class, 'variation_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
