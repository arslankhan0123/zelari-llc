<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model {
    protected $fillable = ['name','email','phone','address','city','country','status'];

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    public function getInitialAttribute(): string {
        return strtoupper(substr($this->name, 0, 1));
    }
}
