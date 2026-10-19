<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\Payments;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order.customer')->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('card_last4', $s)
                  ->orWhere('card_fingerprint', $s)
                  ->orWhere('payment_intent_id', 'like', "%$s%")
                  ->orWhere('charge_id', 'like', "%$s%")
                  ->orWhere('cardholder_name', 'like', "%$s%")
                  ->orWhere('billing_email', 'like', "%$s%")
                  ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%$s%")
                      ->orWhere('shipping_name', 'like', "%$s%")
                      ->orWhere('shipping_phone', 'like', "%$s%"));
            });
        }
        if ($request->filled('card'))     $query->where('card_fingerprint', $request->card);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('provider')) $query->where('provider', $request->provider);
        if ($request->filled('from'))     $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))       $query->whereDate('created_at', '<=', $request->to);

        $payments = $query->paginate(15)->withQueryString();

        $stats = [
            'paid_today' => Payment::where('status', 'paid')->whereDate('paid_at', today())->count(),
            'paid_total' => Payment::where('status', 'paid')->count(),
            'card_paid'  => Payment::where('status', 'paid')->where('provider', 'stripe')->count(),
            'failed'     => Payment::whereIn('status', ['failed', 'cancelled'])->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $payment->load('order.items', 'order.customer');
        $sameCard = $payment->card_fingerprint ? $payment->sameCardPayments()->limit(20)->get() : collect();
        return view('admin.payments.show', compact('payment', 'sameCard'));
    }

    /** Re-fetch card details / status from Stripe for this payment. */
    public function sync(Payment $payment)
    {
        if ($payment->provider === 'paypal' && $payment->payment_intent_id) {
            try {
                $po = \App\Support\PayPal::getOrder($payment->payment_intent_id);
            } catch (\Throwable $e) {
                return back()->with('error', 'PayPal lookup failed: ' . $e->getMessage());
            }
            $sum = \App\Support\PayPal::summarize($po);
            Payments::fillFromPayPal($payment, $sum);
            if ($sum['status'] === 'COMPLETED' && $payment->order && $payment->order->payment_status !== 'paid') {
                Payments::markPaid($payment->order, $payment->payment_intent_id);
            } elseif ($sum['status'] === 'REFUNDED' || $sum['status'] === 'PARTIALLY_REFUNDED') {
                $payment->forceFill(['status' => 'refunded'])->save();
            }
            return back()->with('success', 'Payment details refreshed from PayPal.');
        }
        if ($payment->provider !== 'stripe' || !$payment->payment_intent_id) {
            return back()->with('error', 'Only Stripe and PayPal payments can be refreshed.');
        }
        try {
            $pi = Payments::retrieveIntent($payment->payment_intent_id, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Stripe lookup failed: ' . $e->getMessage());
        }
        Payments::fillFromIntent($payment, $pi);
        if (($pi['status'] ?? '') === 'succeeded' && $payment->order && $payment->order->payment_status !== 'paid') {
            Payments::markPaid($payment->order, $pi['id']);
        } elseif (($pi['status'] ?? '') === 'succeeded' && $payment->status !== 'refunded') {
            $payment->forceFill(['status' => 'paid', 'paid_at' => $payment->paid_at ?: now()])->save();
        }
        return back()->with('success', 'Payment details refreshed from Stripe.');
    }
}
