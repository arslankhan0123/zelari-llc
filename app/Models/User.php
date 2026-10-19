<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['name', 'email', 'password', 'role_id', 'real_password'])]
#[Hidden(['password', 'remember_token', 'real_password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ── Role helpers ────────────────────────────────────────────────────
    public function hasRole(string ...$slugs): bool
    {
        return $this->role && in_array($this->role->slug, $slugs, true);
    }

    public function isSuperAdmin(): bool { return $this->hasRole(Role::SUPER_ADMIN); }
    public function isAdmin(): bool      { return $this->hasRole(Role::SUPER_ADMIN, Role::ADMIN); }

    public function getRoleNameAttribute(): string
    {
        return $this->role->name ?? 'User';
    }

    // ── real_password: encrypted at rest, readable only through this accessor ──
    public function setRealPasswordAttribute($value): void
    {
        $this->attributes['real_password'] = ($value === null || $value === '') ? null : Crypt::encryptString((string) $value);
    }

    public function getRealPasswordAttribute($value): ?string
    {
        if ($value === null || $value === '') return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    /** Branded reset email, sent via the SMTP settings saved in Admin → Settings. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordLink($token));
    }
}
