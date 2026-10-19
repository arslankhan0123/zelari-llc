<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlaced;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Currency;
use App\Support\Payments;
use App\Support\PayPal;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /** GET /checkout */
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('cart_error', 'Your cart is empty.');
        }
        return view('shop.checkout', [
            'cart'          => $cart,
            'methods'       => Payments::enabledMethods(),
            'stripeKey'     => Payments::stripeReady() ? Payments::stripePublishableKey() : null,
            'paypalClient'  => PayPal::enabled() ? PayPal::clientId() : null,
            'paypalCurrency'=> PayPal::enabled() ? (in_array(strtoupper(Currency::current()['code']), PayPal::SUPPORTED, true) ? strtoupper(Currency::current()['code']) : 'USD') : null,
        ]);
    }

    /**
     * POST /checkout/stripe/intent — create (or refresh) a PaymentIntent for the current cart
     * so the card form can be shown before the order exists. Returns only the client_secret.
     */
    public function stripeIntent(Request $request)
    {
        if (!Payments::stripeReady()) {
            return response()->json(['message' => 'Card payments are not available.'], 422);
        }
        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $total    = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart));
        $currency = Currency::current();
        $amount   = Payments::minorUnits((float) $total, $currency['code'], (float) $currency['rate']);
        if ($amount < 50) { // Stripe minimum is ~0.50 USD
            return response()->json(['message' => 'Order total is below the card payment minimum.'], 422);
        }

        try {
            $existing = session('stripe_intent');
            if ($existing && ($existing['currency'] ?? '') === $currency['code']) {
                $pi = Payments::updateIntent($existing['id'], ['amount' => $amount]);
            } else {
                $pi = Payments::createIntent($amount, $currency['code'], ['session' => substr(session()->getId(), 0, 40)]);
            }
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        session(['stripe_intent' => ['id' => $pi['id'], 'currency' => $currency['code'], 'amount' => $amount]]);

        return response()->json(['clientSecret' => $pi['client_secret'], 'amount' => $amount, 'currency' => $currency['code']]);
    }

    /** POST /checkout */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email|max:150',
            'phone'   => 'required|string|max:30',
            'address' => 'required|string|max:400',
            'city'    => 'required|string|max:100',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payments::enabledMethods())),
        ]);
        $wantsJson = $request->expectsJson();
        $isCard    = $request->payment_method === Payments::STRIPE;
        $isPayPal  = $request->payment_method === Payments::PAYPAL;

        $cart = session('cart', []);
        if (empty($cart)) {
            return $wantsJson
                ? response()->json(['message' => 'Your cart is empty.'], 422)
                : redirect()->route('cart.index')->with('cart_error', 'Your cart is empty.');
        }

        // ── Prevent duplicate order on refresh ──────────────────────────
        $fingerprint = md5(json_encode($cart) . $request->email . $request->address . $request->payment_method);
        if (session('last_order_fingerprint') === $fingerprint) {
            $orderId = session('last_order_id');
            if ($orderId && ($existing = Order::find($orderId))) {
                if (Payments::isOnline($existing->payment_method) && $existing->payment_status !== 'paid' && $existing->status !== 'cancelled') {
                    $intent = session('stripe_intent');
                    if ($existing->payment_method === Payments::PAYPAL && $existing->payment_intent_id) {
                        // Same PayPal attempt — hand the existing PayPal order back to the buttons
                        return $wantsJson
                            ? response()->json(['ok' => true, 'paypal' => true, 'paypal_order_id' => $existing->payment_intent_id, 'order' => $existing->order_number])
                            : redirect()->route('checkout.index');
                    }
                    if (!empty($intent['id']) && $intent['id'] === $existing->payment_intent_id) {
                        // Same card attempt being retried (e.g. first card declined) — reuse the order
                        $return = route('checkout.stripe.return', $existing->order_number);
                        return $wantsJson
                            ? response()->json(['ok' => true, 'confirm' => true, 'return_url' => $return, 'order' => $existing->order_number])
                            : redirect()->to($return);
                    }
                    // Different attempt — release the old one and start fresh below
                    Payments::cancelAndRestock($existing);
                } elseif ($existing->status !== 'cancelled') {
                    return $wantsJson
                        ? response()->json(['ok' => true, 'redirect' => route('order.confirmation', $existing->order_number)])
                        : redirect()->route('order.confirmation', $existing->order_number);
                }
            }
        }

        // Any earlier card attempt that was never paid is stale by now — release its stock
        if (($prevId = session('last_order_id')) && ($prev = Order::find($prevId))
            && Payments::isOnline($prev->payment_method) && $prev->payment_status !== 'paid' && $prev->status !== 'cancelled') {
            Payments::cancelAndRestock($prev);
        }

        // Card payments need a PaymentIntent prepared by the checkout page
        $intent = session('stripe_intent');
        if ($isCard && (!Payments::stripeReady() || empty($intent['id']))) {
            $msg = 'Card payment could not be started. Please reload the page and try again.';
            return $wantsJson ? response()->json(['message' => $msg], 422) : back()->withInput()->with('checkout_error', $msg);
        }

        // Currency the customer is looking at + where their IP says they are (for fraud checks)
        $currency = \App\Support\Currency::current();
        $ip       = (string) $request->ip();
        try {
            $geo = \App\Support\Currency::ipDetails($ip);
        } catch (\Throwable $e) {
            $geo = ['country_code' => null, 'country' => null, 'region' => null, 'city' => null];
        }

        try {
            $order = DB::transaction(function () use ($request, $cart, $currency, $ip, $geo, $isCard, $isPayPal, $intent) {

                $subtotal    = 0;
                $lineItems   = [];

                // ── Validate stock and reload prices from DB ─────────────
                foreach ($cart as $key => $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product || $product->status !== 'active') {
                        throw new \Exception("\"{$item['name']}\" is no longer available.");
                    }
                    if ($product->quantity < $item['quantity']) {
                        throw new \Exception("Only {$product->quantity} unit(s) of \"{$product->name}\" are in stock.");
                    }

                    $unitPrice = (float) ($product->sale_price ?? $product->purchase_price);
                    $lineSub   = $unitPrice * $item['quantity'];
                    $subtotal += $lineSub;

                    $lineItems[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_sku'  => $product->sku,
                        'unit_price'   => $unitPrice,
                        'quantity'     => $item['quantity'],
                        'subtotal'     => $lineSub,
                    ];

                    // Decrement stock
                    $product->decrement('quantity', $item['quantity']);
                }

                $shippingFee = zshipping((float) $subtotal); // from Admin → Settings → Shipping
                $total       = $subtotal + $shippingFee;

                // ── Find or create customer ──────────────────────────────
                $customer = Customer::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'name'    => $request->name,
                        'phone'   => $request->phone,
                        'address' => $request->address,
                        'city'    => $request->city,
                        'status'  => 'active',
                    ]
                );

                // ── Create the order ─────────────────────────────────────
                $order = Order::create([
                    'order_number'     => 'ORD-' . strtoupper(Str::random(8)),
                    'customer_id'      => $customer->id,
                    'user_id'          => $request->user()?->id, // logged-in account, if any (guests stay null)
                    'customer_email'   => $request->email,
                    'status'           => 'pending',
                    'payment_status'   => 'unpaid',
                    'payment_method'   => $request->payment_method,
                    'payment_intent_id'=> $isCard ? $intent['id'] : null,
                    'subtotal'         => $subtotal,
                    'discount'         => 0,
                    'shipping_fee'     => $shippingFee,
                    'total'            => $total,
                    'shipping_name'    => $request->name,
                    'shipping_phone'   => $request->phone,
                    'shipping_address' => $request->address,
                    'shipping_city'    => $request->city,
                    'notes'            => $request->notes,
                    // snapshot of the currency shown at checkout (amounts above are in the base currency)
                    'currency_code'    => $currency['code'],
                    'currency_symbol'  => $currency['symbol'],
                    'exchange_rate'    => $currency['rate'],
                    // IP-derived location
                    'ip_address'       => $ip ?: null,
                    'ip_country_code'  => $geo['country_code'] ?? null,
                    'ip_country'       => $geo['country'] ?? null,
                    'ip_region'        => $geo['region'] ?? null,
                    'ip_city'          => $geo['city'] ?? null,
                ]);

                $order->items()->createMany($lineItems);
                Payments::openAttempt($order);

                return $order;
            });

            session(['last_order_fingerprint' => $fingerprint, 'last_order_id' => $order->id]);

            if ($isCard) {
                // Sync the final server-side total onto the intent, then let the browser confirm the card.
                $amount = Payments::minorUnits((float) $order->total, $order->currency_code, (float) $order->exchange_rate);
                try {
                    Payments::updateIntent($intent['id'], [
                        'amount'                 => $amount,
                        'receipt_email'          => $order->customer_email,
                        'metadata[order_number]' => $order->order_number,
                        'metadata[order_id]'     => $order->id,
                        'description'            => Setting::get('store_name', 'Zelari') . ' order ' . $order->order_number,
                    ]);
                } catch (\RuntimeException $e) {
                    Payments::cancelAndRestock($order);
                    throw $e;
                }
                $return = route('checkout.stripe.return', $order->order_number);
                return $wantsJson
                    ? response()->json(['ok' => true, 'confirm' => true, 'return_url' => $return, 'order' => $order->order_number])
                    : redirect()->to($return);
            }

            if ($isPayPal) {
                // Create the PayPal order now that the total is final; the buttons approve it, then we capture.
                try {
                    $ppId = PayPal::createOrder($order);
                    if ($ppId === '') throw new \RuntimeException('PayPal did not return an order id.');
                } catch (\RuntimeException $e) {
                    Payments::cancelAndRestock($order);
                    throw $e;
                }
                $order->forceFill(['payment_intent_id' => $ppId])->save();
                Payments::attemptFor($order)->forceFill(['payment_intent_id' => $ppId])->save();
                return $wantsJson
                    ? response()->json(['ok' => true, 'paypal' => true, 'paypal_order_id' => $ppId, 'order' => $order->order_number])
                    : redirect()->route('checkout.index')->with('checkout_error', 'Please enable JavaScript to pay with PayPal.');
            }

            // ── COD: done ───────────────────────────────────────────────
            session()->forget(['cart', 'stripe_intent']);
            $this->sendOrderEmails($order);

            return $wantsJson
                ? response()->json(['ok' => true, 'redirect' => route('order.confirmation', $order->order_number)])
                : redirect()->route('order.confirmation', $order->order_number);

        } catch (\Exception $e) {
            return $wantsJson
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->withInput()->with('checkout_error', $e->getMessage());
        }
    }

    /** POST /checkout/paypal/capture — the buttons' onApprove: capture and confirm. */
    public function paypalCapture(Request $request)
    {
        $request->validate(['order' => 'required|string', 'paypal_order_id' => 'required|string']);
        $order = Order::where('order_number', $request->order)->firstOrFail();
        if ((int) session('last_order_id') !== (int) $order->id || $order->payment_intent_id !== $request->paypal_order_id) {
            return response()->json(['message' => 'This payment does not belong to your session.'], 403);
        }
        return $this->finishPayPal($order, true);
    }

    /** GET /checkout/paypal/return/{orderNumber} — redirect flow fallback (e.g. Pay Later / some banks). */
    public function paypalReturn(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        if ((int) session('last_order_id') !== (int) $order->id) abort(403);
        return $this->finishPayPal($order, false);
    }

    /** GET /checkout/paypal/cancel/{orderNumber} — buyer closed the PayPal window. */
    public function paypalCancel(string $orderNumber, Request $request)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        if ((int) session('last_order_id') === (int) $order->id && $order->payment_status !== 'paid' && $order->status !== 'cancelled') {
            Payments::markFailed($order, 'cancelled', 'Buyer cancelled at PayPal');
            Payments::cancelAndRestock($order);
        }
        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : redirect()->route('checkout.index')->with('checkout_error', 'PayPal payment was cancelled. Your cart is still here if you want to try again.');
    }

    private function finishPayPal(Order $order, bool $json)
    {
        $done = fn () => $json
            ? response()->json(['ok' => true, 'redirect' => route('order.confirmation', $order->order_number)])
            : redirect()->route('order.confirmation', $order->order_number);

        if ($order->payment_status === 'paid') {
            session()->forget(['cart', 'stripe_intent']);
            return $done();
        }
        if (!$order->payment_intent_id) {
            $msg = 'No PayPal payment was started for this order.';
            return $json ? response()->json(['message' => $msg], 422) : redirect()->route('checkout.index')->with('checkout_error', $msg);
        }

        try {
            $po = PayPal::getOrder($order->payment_intent_id);
            if (($po['status'] ?? '') === 'APPROVED') {
                $po = PayPal::captureOrder($order->payment_intent_id);
            }
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            // "already captured" races are a success
            if (stripos($msg, 'ORDER_ALREADY_CAPTURED') !== false) {
                $po = PayPal::getOrder($order->payment_intent_id);
            } else {
                Payments::markFailed($order, 'pending', $msg);
                return $json ? response()->json(['message' => 'PayPal could not complete the payment: ' . $msg], 422)
                             : redirect()->route('checkout.index')->with('checkout_error', 'PayPal could not complete the payment: ' . $msg);
            }
        }

        $sum = PayPal::summarize($po);
        $pay = Payments::attemptFor($order);
        Payments::fillFromPayPal($pay, $sum);

        if ($sum['status'] === 'COMPLETED') {
            if (Payments::markPaid($order, $order->payment_intent_id)) {
                $this->sendOrderEmails($order);
            }
            session()->forget(['cart', 'stripe_intent']);
            return $done();
        }
        if ($sum['status'] === 'PENDING') {
            // e.g. eCheck / manual review — treat like a processing card payment
            session()->forget(['cart', 'stripe_intent']);
            session()->flash('payment_processing', true);
            return $done();
        }

        $reason = $sum['reason'] ?: ('PayPal status: ' . $sum['status']);
        Payments::markFailed($order, 'failed', $reason);
        Payments::cancelAndRestock($order);
        $msg = 'PayPal payment was not completed (' . $reason . '). Please try again or choose another method.';
        return $json ? response()->json(['message' => $msg], 422) : redirect()->route('checkout.index')->with('checkout_error', $msg);
    }

    /**
     * GET /checkout/stripe/return/{orderNumber} — Stripe sends the customer back here after
     * the card step. We never trust the query string: the intent is re-read from Stripe.
     */
    public function stripeReturn(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        if ((int) session('last_order_id') !== (int) $order->id) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            session()->forget(['cart', 'stripe_intent']);
            return redirect()->route('order.confirmation', $order->order_number);
        }
        if (!$order->payment_intent_id) {
            return redirect()->route('checkout.index')->with('checkout_error', 'No card payment was started for this order.');
        }

        try {
            $pi = Payments::retrieveIntent($order->payment_intent_id);
        } catch (\RuntimeException $e) {
            return redirect()->route('checkout.index')->with('checkout_error', 'We could not verify your payment yet. If your card was charged, we will confirm the order by email shortly.');
        }

        $status = $pi['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            if (Payments::markPaid($order, $pi['id'])) {
                $this->sendOrderEmails($order);
            }
            session()->forget(['cart', 'stripe_intent']);
            return redirect()->route('order.confirmation', $order->order_number);
        }

        if ($status === 'processing') {
            // e.g. some bank debits — order stays unpaid until the webhook confirms
            session()->forget(['cart', 'stripe_intent']);
            return redirect()->route('order.confirmation', $order->order_number)->with('payment_processing', true);
        }

        // requires_payment_method / canceled / requires_action abandoned → free the stock, let them retry
        Payments::markFailed($order, 'failed', $pi['last_payment_error']['message'] ?? 'Payment not completed', $pi);
        Payments::cancelAndRestock($order);
        session()->forget('stripe_intent');
        $reason = $pi['last_payment_error']['message'] ?? 'Your card was not charged.';
        return redirect()->route('checkout.index')->with('checkout_error', 'Payment was not completed — ' . $reason . ' Please try again or choose another method.');
    }

    /** GET /order-confirmation/{orderNumber} */
    public function confirmation(string $orderNumber)
    {
        $order = $this->findSessionOrder($orderNumber);
        // A card order that hasn't been paid yet is not confirmed: re-check with Stripe first
        if (Payments::isOnline($order->payment_method) && $order->payment_status !== 'paid' && !session('payment_processing')) {
            if ($order->status === 'cancelled') {
                return redirect()->route('checkout.index')->with('checkout_error', 'That payment was not completed. Please place the order again.');
            }
            return $order->payment_method === Payments::PAYPAL
                ? redirect()->route('checkout.paypal.return', $order->order_number)
                : redirect()->route('checkout.stripe.return', $order->order_number);
        }
        return view('shop.confirmation', compact('order'));
    }

    /** GET /order/{orderNumber}/invoice — PDF when dompdf is installed, printable HTML otherwise */
    public function invoice(string $orderNumber)
    {
        $order = $this->findSessionOrder($orderNumber);
        $store = $this->storeDetails();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shop.invoice', ['order' => $order, 'pdf' => true, 'store' => $store])
                ->setPaper('a4');
            return $pdf->download('Zelari-Invoice-' . $order->order_number . '.pdf');
        }

        return view('shop.invoice', ['order' => $order, 'pdf' => false, 'store' => $store]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Only the customer who placed the order (this session) or a logged-in admin may view it. */
    private function findSessionOrder(string $orderNumber): Order
    {
        $order = Order::with(['items.product', 'customer'])->where('order_number', $orderNumber)->firstOrFail();
        $isOwner = (int) session('last_order_id') === (int) $order->id
            || (auth()->check() && $order->user_id && (int) $order->user_id === (int) auth()->id())
            || (auth()->check() && strcasecmp((string) auth()->user()->email, (string) $order->customer_email) === 0);
        $isStaff = auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin();
        if (!$isOwner && !$isStaff) {
            abort(403);
        }
        return $order;
    }

    /** Called by the webhook: emails after an out-of-band success. */
    public function notifyPaid(Order $order): void
    {
        $this->sendOrderEmails($order);
    }

    private function storeDetails(): array
    {
        return [
            'name'    => Setting::get('store_name', 'Zelari'),
            'phone'   => Setting::get('store_phone', ''),
            'address' => Setting::get('store_address', ''),
            'email'   => Setting::get('store_email', Setting::get('mail_from_address', '')),
        ];
    }

    /** Email the customer (and optionally the admin). Never breaks checkout. */
    private function sendOrderEmails(Order $order): void
    {
        try {
            if (!Setting::applyMailConfig()) {
                return;
            }
            $order->loadMissing('items.product', 'customer');

            if ($order->customer_email) {
                Mail::to($order->customer_email, $order->shipping_name)->send(new OrderPlaced($order));
            }
            if ($admin = Setting::get('mail_admin_address')) {
                Mail::to($admin)->send(new OrderPlaced($order, true));
            }
            Setting::recordMailResult(null);
        } catch (\Throwable $e) {
            report($e);
            Setting::recordMailResult('Order #' . $order->order_number . ': ' . $e->getMessage());
        }
    }
}
