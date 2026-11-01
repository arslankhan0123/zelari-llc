<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class BlogPost extends Model
{
    protected $fillable = ['title','slug','cover','excerpt','body','status','meta_title','meta_description','published_at'];
    protected $casts = ['published_at'=>'datetime'];
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($p) {
            if (empty($p->slug)) $p->slug = Str::slug($p->title) . '-' . Str::random(4);
        });
    }
    public function scopePublished($q) { return $q->where('status','published'); }
}
