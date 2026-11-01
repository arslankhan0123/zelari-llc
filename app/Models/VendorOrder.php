<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorOrder extends Model
{
    public const PENDING    = 'pending';
    public const PROCESSING = 'processing';
    public const SHIPPED    = 'shipped';
    public const DELIVERED  = 'delivered';
    public const CANCELLED  = 'cancelled';

    protected $fillable = [
        'order_id', 'vendor_id', 'sub_number',
        'subtotal', 'commission_rate', 'commission_amount', 'payout_amount',
        'status', 'note',
    ];

    protected $casts = [
        'subtotal'          => 'decimal:2',
        'commission_rate'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_amount'     => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** Line items that belong to this vendor on the parent order. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id')
                    ->where('order_items.vendor_id', $this->vendor_id);
    }

    public function money($amount): string
    {
        return $this->order ? $this->order->money($amount) : number_format((float) $amount, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::PENDING    => 'yellow',
            self::PROCESSING => 'blue',
            self::SHIPPED    => 'indigo',
            self::DELIVERED  => 'green',
            self::CANCELLED  => 'red',
            default          => 'gray',
        };
    }

    /**
     * Split a freshly-created order into per-vendor sub-orders.
     * Stamps each order item with its product's vendor_id, groups the items
     * by vendor, and creates one vendor_order per vendor with the commission
     * taken from that vendor's commission_rate. Safe to call inside a
     * transaction; does nothing for items with no resolvable vendor.
     */
    public static function splitFromOrder(Order $order): void
    {
        $order->loadMissing('items.product');

        $byVendor = [];

        foreach ($order->items as $item) {
            $vendorId = $item->vendor_id ?: optional($item->product)->vendor_id;
            if (!$vendorId) {
                continue;
            }

            if ((int) $item->vendor_id !== (int) $vendorId) {
                $item->forceFill(['vendor_id' => $vendorId])->save();
            }

            if (!isset($byVendor[$vendorId])) {
                $byVendor[$vendorId] = ['subtotal' => 0.0];
            }
            $byVendor[$vendorId]['subtotal'] += (float) $item->subtotal;
        }

        if (empty($byVendor)) {
            return;
        }

        $seq = 0;
        foreach ($byVendor as $vendorId => $data) {
            $seq++;
            $vendor = Vendor::find($vendorId);
            $rate   = (float) ($vendor->commission_rate ?? 0);
            $sub    = round((float) $data['subtotal'], 2);
            $comm   = round($sub * $rate / 100, 2);

            static::create([
                'order_id'          => $order->id,
                'vendor_id'         => $vendorId,
                'sub_number'        => $order->order_number . '-V' . $seq,
                'subtotal'          => $sub,
                'commission_rate'   => $rate,
                'commission_amount' => $comm,
                'payout_amount'     => round($sub - $comm, 2),
                'status'            => self::PENDING,
            ]);
        }
    }

    /** Keep every sub-order status aligned with the parent when an admin changes the whole order. */
    public static function syncStatusFromOrder(Order $order): void
    {
        static::where('order_id', $order->id)->update(['status' => $order->status]);
    }

    /**
     * Recompute the parent order status from its vendor sub-orders.
     * An order is only as finished as its least-progressed active vendor:
     * pending < processing < shipped < delivered. Cancelled sub-orders are
     * set aside; when every sub-order is cancelled, the whole order is cancelled.
     * Called after a vendor changes their own sub-order status.
     */
    public static function rollUpToOrder(Order $order): void
    {
        $statuses = static::where('order_id', $order->id)->pluck('status');
        if ($statuses->isEmpty()) {
            return;
        }

        $rank   = [self::PENDING => 0, self::PROCESSING => 1, self::SHIPPED => 2, self::DELIVERED => 3];
        $active = $statuses->reject(fn ($st) => $st === self::CANCELLED);

        if ($active->isEmpty()) {
            $new = self::CANCELLED;                       // every vendor cancelled
        } else {
            $new = $active->sortBy(fn ($st) => $rank[$st] ?? 0)->first(); // least-progressed active vendor
        }

        if ($order->status !== $new) {
            $order->forceFill(['status' => $new])->save();
        }
    }
}
