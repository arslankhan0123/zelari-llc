<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Payments;
use Illuminate\Http\Request;

/**
 * POST /webhooks/stripe
 * Safety net for payments that finish after the customer has left the page
 * (bank redirects, "processing" debits, closed tabs). Marks the order paid and
 * sends the emails if the return URL didn't already do it.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();

        if (!Payments::verifyWebhook($payload, $request->header('Stripe-Signature'))) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true) ?: [];
        $type  = $event['type'] ?? '';
        $pi    = $event['data']['object'] ?? [];
        $piId  = $pi['id'] ?? null;

        if (!$piId || !str_starts_with($type, 'payment_intent.')) {
            return response()->json(['received' => true]);
        }

        $order = Order::where('payment_intent_id', $piId)->first()
            ?: (isset($pi['metadata']['order_number']) ? Order::where('order_number', $pi['metadata']['order_number'])->first() : null);

        if (!$order) {
            return response()->json(['received' => true, 'order' => 'unknown']);
        }

        switch ($type) {
            case 'payment_intent.succeeded':
                if (Payments::markPaid($order, $piId)) {
                    app(CheckoutController::class)->notifyPaid($order);
                }
                break;

            case 'payment_intent.payment_failed':
                if ($order->payment_status !== 'paid') {
                    // Customer may still retry on the page — record the reason, keep the order open
                    Payments::markFailed($order, 'pending', $pi['last_payment_error']['message'] ?? 'Payment failed', $pi);
                }
                break;

            case 'payment_intent.canceled':
                if ($order->payment_status !== 'paid') {
                    Payments::markFailed($order, 'cancelled', 'Payment cancelled', $pi);
                    Payments::cancelAndRestock($order);
                }
                break;
        }

        return response()->json(['received' => true]);
    }
}
