<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['name', 'email', 'password', 'role_id', 'vendor_id', 'real_password'])]
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

    public function isMasterAdmin(): bool { return $this->hasRole(Role::MASTER_ADMIN); }
    public function isSuperAdmin(): bool  { return $this->isMasterAdmin(); } // BC alias
    public function isAdmin(): bool       { return $this->hasRole(Role::MASTER_ADMIN, Role::ADMIN); }
    public function isVendor(): bool     { return $this->hasRole(Role::VENDOR, Role::VENDOR_STAFF); }
    public function isVendorOwner(): bool { return $this->hasRole(Role::VENDOR); }
    public function isVendorStaff(): bool { return $this->hasRole(Role::VENDOR_STAFF); }

    /** The vendor this user belongs to (owner or staff), or null. Drives all vendor data scoping. */
    public function vendorId(): ?int
    {
        return $this->isVendor() ? ($this->vendor_id ?: optional($this->getRelationValue('vendor'))->id) : null;
    }

    /** Permission check: master admin has everything; others go by their role. */
    public function hasPermission(string $perm): bool
    {
        if ($this->isMasterAdmin()) return true;
        return $this->role && $this->role->hasPermission($perm);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

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
