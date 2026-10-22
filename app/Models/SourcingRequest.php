<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourcingRequest extends Model
{
    use HasFactory;

    public const NEW       = 'new';
    public const REVIEWING = 'reviewing';
    public const QUOTED    = 'quoted';
    public const CLOSED    = 'closed';

    protected $fillable = [
        'reference', 'user_id', 'name', 'email', 'phone', 'company',
        'source_country', 'deliver_to', 'budget', 'needed_by', 'notes',
        'status', 'admin_note', 'ip', 'emailed', 'email_error',
    ];

    protected $casts = [
        'needed_by' => 'date',
        'emailed'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $req) {
            $req->reference = $req->reference ?: static::nextReference();
        });
    }

    /** SR-2026-0001, restarting each year. */
    public static function nextReference(): string
    {
        $year = now()->format('Y');
        $last = static::where('reference', 'like', "SR-$year-%")->max('reference');
        $seq  = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('SR-%s-%04d', $year, $seq);
    }

    public function items()
    {
        return $this->hasMany(SourcingRequestItem::class)->orderBy('position');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpen($q)
    {
        return $q->where('status', self::NEW);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $take  = array_slice(array_filter($parts), 0, 2);
        return mb_strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), $take))) ?: '?';
    }
}
