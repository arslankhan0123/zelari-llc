<?php
namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReturnRequestController extends Controller
{
    /** Customer submits a return/refund request for one of their orders. */
    public function store(Request $request, Order $order)
    {
        $data = $request->validate([
            'email'  => 'required|email',
            'type'   => 'required|in:return,exchange,refund',
            'reason' => 'required|string|max:2000',
        ]);

        // Verify the requester owns this order (by email or session).
        $owns = strcasecmp((string) $order->customer_email, $data['email']) === 0
            || (session('last_order_id') == $order->id)
            || (auth()->check() && $order->user_id === auth()->id());
        if (!$owns) {
            return back()->with('return_error', 'That email does not match this order.');
        }

        if (ReturnRequest::where('order_id', $order->id)->whereIn('status', ['requested', 'approved'])->exists()) {
            return back()->with('return_error', 'A return request for this order is already in progress.');
        }

        ReturnRequest::create([
            'ref'            => 'RET-' . strtoupper(Str::random(6)),
            'order_id'       => $order->id,
            'customer_email' => $order->customer_email,
            'type'           => $data['type'],
            'reason'         => $data['reason'],
            'status'         => ReturnRequest::REQUESTED,
        ]);

        return back()->with('return_success', 'Your request has been submitted. Our team will review it shortly.');
    }
}
