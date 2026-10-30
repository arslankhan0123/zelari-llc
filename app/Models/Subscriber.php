<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = ['email', 'token', 'active'];
    protected $casts = ['active' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($s) {
            if (empty($s->token)) $s->token = Str::random(48);
        });
    }
}
