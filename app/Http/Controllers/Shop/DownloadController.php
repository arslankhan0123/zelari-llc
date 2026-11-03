<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /** Secure delivery of a digital/downloadable product after a paid order. */
    public function download(OrderItem $item)
    {
        $order   = $item->order;
        $product = $item->product;

        abort_unless($product && $product->download_file, 404);

        $owns = (auth()->check() && $order->user_id === auth()->id())
            || (session('last_order_id') == $order->id);
        abort_unless($owns, 403);
        abort_unless($order->payment_status === 'paid', 403, 'This download will be available once your payment is confirmed.');
        abort_unless(Storage::disk('public')->exists($product->download_file), 404);

        return Storage::disk('public')->download($product->download_file);
    }
}
