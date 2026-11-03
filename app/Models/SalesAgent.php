<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SalesAgent extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'code', 'commission_rate', 'active',
        'payout_method', 'bank_name', 'account_name', 'account_number', 'note',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'active'          => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($a) {
            if (empty($a->code)) {
                $a->code = static::uniqueCode($a->name);
            }
            $a->code = strtoupper($a->code);
        });
    }

    /** Make a unique referral code from a name (e.g. "Ahmed Raza" -> AHMED-7F3A). */
    public static function uniqueCode(?string $name = null): string
    {
        $base = strtoupper(Str::slug($name ?: 'agent'));
        $base = $base ? explode('-', $base)[0] : 'AGENT';
        do {
            $code = $base . '-' . strtoupper(Str::random(4));
        } while (static::where('code', $code)->exists());
        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'sales_agent_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AgentPayout::class);
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    /**
     * Commission snapshot (all amounts in base currency).
     * sales      = subtotal of attributed, non-cancelled orders
     * earned     = commission on delivered orders (realised)
     * pending    = commission on active, not-yet-delivered orders
     * paid       = total already paid out to this agent
     * available  = earned - paid (what is still owed)
     */
    public function earnings(): array
    {
        $orders = $this->orders()
            ->where('status', '!=', 'cancelled')
            ->get(['status', 'subtotal', 'agent_commission']);

        $sales   = (float) $orders->sum('subtotal');
        $earned  = (float) $orders->where('status', 'delivered')->sum('agent_commission');
        $pending = (float) $orders->whereIn('status', ['pending', 'processing', 'shipped'])->sum('agent_commission');
        $paid    = (float) $this->payouts()->sum('amount');

        $available = max(0, round($earned - $paid, 2));

        return [
            'orders'    => $orders->count(),
            'sales'     => round($sales, 2),
            'earned'    => round($earned, 2),
            'pending'   => round($pending, 2),
            'paid'      => round($paid, 2),
            'available' => $available,
        ];
    }
}
