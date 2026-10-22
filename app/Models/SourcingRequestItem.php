<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourcingRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sourcing_request_id', 'name', 'quantity', 'unit',
        'target_price', 'reference_url', 'specs', 'position',
    ];

    protected $casts = ['quantity' => 'integer'];

    public function request()
    {
        return $this->belongsTo(SourcingRequest::class, 'sourcing_request_id');
    }
}
