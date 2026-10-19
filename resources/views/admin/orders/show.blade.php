@extends('layouts.admin')
@section('title', $order->order_number)
@section('order_open','true')

@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">Placed {{ $order->created_at->format('M d, Y · h:i A') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.edit', $order) }}"
               class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Update Status
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2 text-emerald-700 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Status bar --}}
    @php
        $steps = ['pending','processing','shipped','delivered'];
        $stepIdx = array_search($order->status, $steps);
    @endphp
    @if($order->status !== 'cancelled')
    <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-center gap-0">
            @foreach($steps as $i => $step)
            <div class="flex-1 flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $i <= $stepIdx ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                    @if($i < $stepIdx)
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>
                <p class="text-xs mt-1 {{ $i <= $stepIdx ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">{{ ucfirst($step) }}</p>
            </div>
            @if(!$loop->last)
            <div class="flex-1 h-0.5 mb-5 {{ $i < $stepIdx ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            @endif
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 font-medium">⚠ This order was cancelled.</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Order items --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700">Order Items</h3>
                </div>
                <table class="data-table">
                    <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-right">Unit Price</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <p class="font-medium text-gray-800 text-sm">{{ $item->product_name }}</p>
                                @if($item->product_sku)<p class="text-xs text-gray-400 font-mono">{{ $item->product_sku }}</p>@endif
                            </td>
                            <td class="text-center font-medium text-gray-700">{{ $item->quantity }}</td>
                            <td class="text-right text-gray-600 text-sm">{{ $order->money($item->unit_price) }}</td>
                            <td class="text-right font-semibold text-gray-800">{{ $order->money($item->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 space-y-2 border-t border-gray-100">
                    <div class="flex justify-between text-sm text-gray-500"><span>Subtotal</span><span>{{ $order->money($order->subtotal) }}</span></div>
                    @if($order->discount > 0)<div class="flex justify-between text-sm text-emerald-600"><span>Discount</span><span>− {{ $order->money($order->discount) }}</span></div>@endif
                    @if($order->shipping_fee > 0)<div class="flex justify-between text-sm text-gray-500"><span>Shipping</span><span>{{ $order->money($order->shipping_fee) }}</span></div>@endif
                    <div class="flex justify-between text-base font-bold text-gray-800 pt-2 border-t border-gray-100"><span>Total</span><span>{{ $order->money($order->total) }}</span></div>
                </div>
            </div>

            {{-- Shipping info + IP location, side by side for verification --}}
            @php $match = $order->location_matches; @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <h3 class="font-semibold text-gray-700 mb-1">Shipping Details</h3>
                    <p class="text-xs text-gray-400 mb-4">Entered by the customer</p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-gray-400 text-xs mb-0.5">Name</p><p class="font-medium text-gray-700">{{ $order->shipping_name ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs mb-0.5">Phone</p><p class="font-medium text-gray-700">{{ $order->shipping_phone ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs mb-0.5">City</p><p class="font-medium text-gray-700">{{ $order->shipping_city ?? '—' }}</p></div>
                        <div class="col-span-2"><p class="text-gray-400 text-xs mb-0.5">Address</p><p class="font-medium text-gray-700">{{ $order->shipping_address ?? '—' }}</p></div>
                    </div>
                    @if($order->notes)<div class="mt-3 p-3 bg-gray-50 rounded-xl text-sm text-gray-600"><span class="font-semibold">Notes:</span> {{ $order->notes }}</div>@endif
                </div>

                <div class="bg-white rounded-2xl p-6 border {{ $match === false ? 'border-amber-300' : 'border-transparent' }}" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-gray-700">Detected Location</h3>
                        @if($match === true)<span class="badge-green">Matches</span>
                        @elseif($match === false)<span class="badge-yellow">Check</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Approximate, from the customer's IP at checkout</p>
                    @if($order->ip_address)
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-gray-400 text-xs mb-0.5">IP address</p><p class="font-mono font-medium text-gray-700">{{ $order->ip_address }}</p></div>
                        <div><p class="text-gray-400 text-xs mb-0.5">Country</p><p class="font-medium text-gray-700">{{ $order->ip_country ?? '—' }}@if($order->ip_country_code) <span class="text-gray-400 font-mono text-xs">{{ $order->ip_country_code }}</span>@endif</p></div>
                        <div><p class="text-gray-400 text-xs mb-0.5">Region</p><p class="font-medium text-gray-700">{{ $order->ip_region ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs mb-0.5">City</p><p class="font-medium text-gray-700">{{ $order->ip_city ?? '—' }}</p></div>
                    </div>
                    @if($match === false)
                    <div class="mt-3 p-3 bg-amber-50 rounded-xl text-xs text-amber-700">The IP location doesn't match the shipping details or the order's currency region. Worth a quick call before dispatch.</div>
                    @elseif(!$order->ip_location)
                    <div class="mt-3 p-3 bg-gray-50 rounded-xl text-xs text-gray-500">Location lookup didn't return a result for this IP (private network or lookup unavailable).</div>
                    @endif
                    @else
                    <p class="text-sm text-gray-400">Not captured for this order.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Order Info</p>
                @php $sc = $order->status_color; $pc = $order->payment_status_color; @endphp
                <div class="flex justify-between items-center text-sm"><span class="text-gray-500">Status</span><span class="badge-{{ $sc }}">{{ ucfirst($order->status) }}</span></div>
                <div class="flex justify-between items-center text-sm"><span class="text-gray-500">Payment</span><span class="badge-{{ $pc }}">{{ ucfirst($order->payment_status) }}</span></div>
                <div class="flex justify-between items-center text-sm"><span class="text-gray-500">Method</span><span class="text-gray-700 font-medium">{{ $order->payment_label }}</span></div>
                @if($order->paid_at)<div class="flex justify-between items-center text-sm"><span class="text-gray-500">Paid at</span><span class="text-gray-700 font-medium">{{ $order->paid_at->format('M d, Y · h:i A') }}</span></div>@endif
                @if($order->payment_intent_id)<div class="flex justify-between items-center text-sm gap-2"><span class="text-gray-500 shrink-0">Stripe ref</span><a href="https://dashboard.stripe.com/{{ str_starts_with((string) \App\Support\Payments::stripePublishableKey(), 'pk_test_') ? 'test/' : '' }}payments/{{ $order->payment_intent_id }}" target="_blank" rel="noopener" class="font-mono text-xs text-indigo-600 hover:underline truncate">{{ $order->payment_intent_id }}</a></div>@endif
                <div class="flex justify-between items-center text-sm"><span class="text-gray-500">Currency</span><span class="text-gray-700 font-medium">{{ $order->currency_code ?? '—' }} <span class="text-gray-400">({{ $order->currency_symbol }})</span></span></div>
                @if($order->currency_code && $order->currency_code !== config('currencies.base'))
                <p class="text-[11px] text-gray-400 leading-snug">Charged as shown to the customer at checkout. Rate used: 1 {{ config('currencies.base') }} = {{ rtrim(rtrim(number_format((float) $order->exchange_rate, 6), '0'), '.') }} {{ $order->currency_code }}.</p>
                @endif
            </div>

            @php $pay = $order->payment; @endphp
            @if($pay && ($pay->card_last4 || $pay->provider === 'stripe'))
            <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="flex items-center justify-between mb-3"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Card used</p><a href="{{ route('admin.payments.show', $pay) }}" class="text-xs text-indigo-600 hover:underline">Details →</a></div>
                @if($pay->card_last4)
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-7 px-2 rounded-md text-[10px] font-extrabold uppercase tracking-wide {{ ['visa'=>'bg-blue-50 text-blue-700','mastercard'=>'bg-orange-50 text-orange-700','amex'=>'bg-sky-50 text-sky-700'][$pay->card_brand] ?? 'bg-gray-100 text-gray-600' }}">{{ $pay->card_brand }}</span>
                    <div><p class="font-mono font-semibold text-gray-800">•••• •••• •••• {{ $pay->card_last4 }}</p><p class="text-xs text-gray-400">Exp {{ $pay->card_expiry }}@if($pay->card_funding) · {{ ucfirst($pay->card_funding) }}@endif @if($pay->card_country) · {{ $pay->card_country }}@endif @if($pay->card_wallet) · {{ ucwords(str_replace('_',' ',$pay->card_wallet)) }}@endif</p></div>
                </div>
                @if($pay->card_id)<p class="text-xs text-gray-500 mt-2">Card ID: <span class="font-mono font-bold text-indigo-700">{{ $pay->card_id }}</span></p>@endif
                @if($pay->cardholder_name)<p class="text-xs text-gray-500 mt-1">Name on card: <b class="text-gray-700">{{ $pay->cardholder_name }}</b></p>@endif
                @if($pay->risk_level)<p class="text-xs text-gray-500 mt-1">Radar risk: <span class="badge-{{ ['normal'=>'green','elevated'=>'yellow','highest'=>'red'][$pay->risk_level] ?? 'gray' }}">{{ ucfirst($pay->risk_level) }}</span></p>@endif
                @else
                <p class="text-xs text-gray-400">No card captured yet.</p>
                @endif
                @if($pay->failure_message)<p class="text-xs text-red-500 mt-2">{{ $pay->failure_message }}</p>@endif
            </div>
            @endif

            @if($order->customer)
            <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Customer</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0 bg-brand">{{ strtoupper(substr($order->customer->name,0,1)) }}</div>
                    <div>
                        <p class="font-semibold text-sm text-gray-800">{{ $order->customer->name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->customer->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.customers.show', $order->customer) }}" class="mt-3 block text-center text-xs text-indigo-600 hover:underline">View Customer →</a>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                    <span class="text-gray-400">Account</span>
                    @if($order->user)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-user-check"></i> {{ $order->user->name }} <span class="text-green-600/70 font-normal">#{{ $order->user->id }}</span></span>
                    @else
                        <span class="inline-flex items-center gap-1.5 font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full"><i class="fa-regular fa-user"></i> Guest checkout</span>
                    @endif
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 text-sm rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Order
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
