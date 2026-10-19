<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller {

    public function index(Request $request) {
        $query = Order::with('customer', 'user')->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('order_number','like',"%$s%")
                  ->orWhereHas('customer', fn($c) => $c->where('name','like',"%$s%"))
            );
        }
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        $orders = $query->paginate(10)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function create() {
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('status','active')->orderBy('name')->get();
        $productsJson = $products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'sku'=>$p->sku,'price'=>(float)$p->sale_price]);
        return view('admin.orders.create', compact('customers','products','productsJson'));
    }

    public function store(Request $request) {
        // Parse items from JSON sent by Alpine
        $items = [];
        if ($request->filled('items_json')) {
            $items = json_decode($request->items_json, true) ?? [];
        }

        $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'payment_method'   => 'required|in:cash_on_delivery,bank_transfer,card,paypal,other',
            'payment_status'   => 'required|in:unpaid,paid,refunded',
            'status'           => 'required|in:pending,processing,shipped,delivered,cancelled',
            'discount'         => 'nullable|numeric|min:0',
            'shipping_fee'     => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'shipping_name'    => 'nullable|string|max:255',
            'shipping_phone'   => 'nullable|string|max:30',
            'shipping_address' => 'nullable|string',
            'shipping_city'    => 'nullable|string|max:100',
        ]);

        if (empty($items)) {
            return back()->withErrors(['items' => 'Please add at least one item.'])->withInput();
        }

        $subtotal  = 0;
        $lineItems = [];

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $name      = $item['product_name'] ?? '';
            $sku       = $item['product_sku'] ?? '';
            $price     = (float)($item['unit_price'] ?? 0);
            $qty       = max(1, (int)($item['quantity'] ?? 1));
            $lineSub   = $price * $qty;
            $subtotal += $lineSub;

            // Resolve product if ID given
            $product = $productId ? Product::find($productId) : null;

            $lineItems[] = [
                'product_id'   => $product?->id,
                'product_name' => $product?->name ?? $name,
                'product_sku'  => $product?->sku  ?? $sku,
                'unit_price'   => $price,
                'quantity'     => $qty,
                'subtotal'     => $lineSub,
            ];
        }

        $discount    = (float)($request->discount     ?? 0);
        $shippingFee = (float)($request->shipping_fee ?? 0);
        $total       = $subtotal - $discount + $shippingFee;

        $defCode = \App\Support\Currency::default();
        $defCur  = \App\Support\Currency::all()[$defCode] ?? ['symbol' => '$', 'rate' => 1];

        $order = Order::create([
            'order_number'     => 'ORD-' . strtoupper(Str::random(8)),
            'currency_code'    => $defCode,
            'currency_symbol'  => $defCur['symbol'],
            'exchange_rate'    => $defCur['rate'],
            'customer_id'      => $request->customer_id ?: null,
            'status'           => $request->status,
            'payment_status'   => $request->payment_status,
            'payment_method'   => $request->payment_method,
            'subtotal'         => $subtotal,
            'discount'         => $discount,
            'shipping_fee'     => $shippingFee,
            'total'            => $total,
            'shipping_name'    => $request->shipping_name,
            'shipping_phone'   => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_city'    => $request->shipping_city,
            'notes'            => $request->notes,
        ]);

        $order->items()->createMany($lineItems);
        $pay = \App\Support\Payments::openAttempt($order);
        if ($order->payment_status === 'paid') {
            $order->forceFill(['paid_at' => now()])->save();
            $pay->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
        }
        return redirect()->route('admin.orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function show(Order $order) {
        $order->load('customer','user','items','payments');
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order) {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order) {
        $request->validate([
            'status'         => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'payment_method' => 'required|in:cash_on_delivery,bank_transfer,card,paypal,other',
            'notes'          => 'nullable|string',
        ]);
        $wasPaid = $order->payment_status === 'paid';
        $order->update($request->only('status','payment_status','payment_method','notes'));

        // Keep the payments ledger in step with manual changes (e.g. COD collected, refund issued)
        $pay = \App\Support\Payments::attemptFor($order);
        if ($order->payment_status === 'paid' && !$wasPaid) {
            $order->forceFill(['paid_at' => now()])->save();
            $pay->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
        } elseif ($order->payment_status === 'refunded') {
            $pay->forceFill(['status' => 'refunded'])->save();
        } elseif ($order->payment_status === 'unpaid' && $wasPaid) {
            $order->forceFill(['paid_at' => null])->save();
            $pay->forceFill(['status' => 'pending', 'paid_at' => null])->save();
        }
        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }

    public function destroy(Order $order) {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}
