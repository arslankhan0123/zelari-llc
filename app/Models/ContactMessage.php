<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    public const NEW      = 'new';
    public const READ     = 'read';
    public const REPLIED  = 'replied';
    public const ARCHIVED = 'archived';

    protected $fillable = [
        'name', 'email', 'subject', 'message', 'status',
        'ip', 'user_agent', 'emailed', 'email_error',
        'admin_note', 'read_at', 'read_by',
    ];

    protected $casts = [
        'emailed' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function reader()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function scopeUnread($q)
    {
        return $q->where('status', self::NEW);
    }

    /** First time a staff member opens it, stamp who read it. */
    public function markRead(?int $userId = null): void
    {
        if ($this->status !== self::NEW) {
            return;
        }
        $this->forceFill([
            'status'  => self::READ,
            'read_at' => now(),
            'read_by' => $userId,
        ])->save();
    }

    public function getSubjectLineAttribute(): string
    {
        return $this->subject ?: 'No subject';
    }

    /** Initials for the avatar bubble in the admin list. */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $take  = array_slice(array_filter($parts), 0, 2);
        return mb_strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), $take))) ?: '?';
    }
}
