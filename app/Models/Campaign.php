<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Campaign extends Model
{
    protected $fillable = ['name','segment','channel','subject','body','status','recipients','sent_at'];
    protected $casts = ['sent_at'=>'datetime'];
    public function getStatusColorAttribute(): string { return $this->status==='sent'?'green':'gray'; }
}
