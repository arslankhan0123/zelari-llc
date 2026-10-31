<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildTask extends Model
{
    protected $fillable = ['phase', 'phase_title', 'title', 'status', 'sort'];
}
