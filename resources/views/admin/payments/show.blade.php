@extends('layouts.admin')
@section('title', 'Payment #'.$payment->id)

@section('content')
@php $o = $payment->order; $testMode = str_starts_with((string) \App\Support\Payments::stripePublishableKey(), 'pk_test_'); @endphp
<div class="p-6 space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payment <span class="text-gray-400 font-mono text-lg">#{{ $payment->id }}</span></h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $payment->created_at->format('M d, Y · h:i A') }} · {{ $payment->provider_label }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(in_array($payment->provider, ['stripe','paypal']) && $payment->payment_intent_id)
            <form method="POST" action="{{ route('admin.payments.sync', $payment) }}">@csrf
                <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh from {{ $payment->provider === 'paypal' ? 'PayPal' : 'Stripe' }}
                </button>
            </form>
            @endif
            <a href="{{ route('admin.payments.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Back</a>
        </div>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">

            {{-- Card --}}
            <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold text-gray-700">Card used</h3>
                    <span class="badge-{{ $payment->status_color }}">{{ ucfirst($payment->status) }}</span>
                </div>
                @if($payment->card_last4)
                <div class="grid md:grid-cols-[260px_1fr] gap-6 items-start">
                    {{-- visual card --}}
                    <div class="rounded-2xl p-5 text-white relative overflow-hidden" style="aspect-ratio:1.586;background:linear-gradient(135deg,#1b1740,#2b1e6b 55%,#0c3b5c);box-shadow:0 18px 40px -16px rgba(39,30,92,.6)">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-7 rounded-md" style="background:linear-gradient(135deg,#f6d365,#fda085)"></span>
                            <span class="text-xs font-extrabold uppercase tracking-widest opacity-90">{{ $payment->card_brand }}</span>
                        </div>
                        <div class="font-mono text-lg tracking-[.18em] mt-6">•••• &nbsp;•••• &nbsp;•••• &nbsp;{{ $payment->card_last4 }}</div>
                        <div class="flex items-end justify-between mt-4 text-[11px]">
                            <div><div class="opacity-60 uppercase tracking-wider">Card holder</div><div class="font-semibold text-sm truncate max-w-[150px]">{{ $payment->cardholder_name ?: ($o->shipping_name ?? '—') }}</div></div>
                            <div class="text-right"><div class="opacity-60 uppercase tracking-wider">Expires</div><div class="font-semibold text-sm">{{ $payment->card_expiry }}</div></div>
                        </div>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-xs text-gray-400">Brand</dt><dd class="font-medium text-gray-800">{{ ucfirst($payment->card_brand) }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Last 4 digits</dt><dd class="font-mono font-semibold text-gray-800 text-base">{{ $payment->card_last4 }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs text-gray-400">Card ID <span class="text-gray-300">(unique per physical card)</span></dt>
                            <dd class="flex items-center gap-2 flex-wrap">
                                @if($payment->card_id)
                                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg tracking-wider">{{ $payment->card_id }}</span>
                                <a href="{{ route('admin.payments.index', ['card' => $payment->card_fingerprint]) }}" class="text-xs text-indigo-600 hover:underline">{{ $sameCard->count() }} other payment{{ $sameCard->count() === 1 ? '' : 's' }} with this card →</a>
                                @else<span class="text-gray-400">— press Refresh from Stripe</span>@endif
                            </dd></div>
                        <div><dt class="text-xs text-gray-400">Expiry</dt><dd class="font-medium text-gray-800">{{ $payment->card_expiry }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Type</dt><dd class="font-medium text-gray-800">{{ $payment->card_funding ? ucfirst($payment->card_funding) : '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Issuing country</dt><dd class="font-medium text-gray-800">{{ $payment->card_country ?: '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Wallet</dt><dd class="font-medium text-gray-800">{{ $payment->card_wallet ? ucwords(str_replace('_',' ',$payment->card_wallet)) : 'None (typed card)' }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Cardholder name</dt><dd class="font-medium text-gray-800">{{ $payment->cardholder_name ?: '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-400">Billing email</dt><dd class="font-medium text-gray-800 break-all">{{ $payment->billing_email ?: '—' }}</dd></div>
                        @if($payment->risk_level)
                        <div class="col-span-2"><dt class="text-xs text-gray-400">Stripe Radar risk</dt>
                            <dd class="font-medium"><span class="badge-{{ ['normal'=>'green','elevated'=>'yellow','highest'=>'red'][$payment->risk_level] ?? 'gray' }}">{{ ucfirst($payment->risk_level) }}</span> <span class="text-gray-400 text-xs">score {{ $payment->risk_score }}/100</span></dd></div>
                        @endif
                    </dl>
                </div>
                <div class="mt-5 p-3 rounded-xl bg-slate-50 text-xs text-gray-500 leading-relaxed">
                    <b class="text-gray-700">Verifying a customer:</b> ask for the <b>last 4 digits</b>, <b>expiry</b> and <b>card brand</b> and compare them here. If two customers share the same last 4, the <b>Card ID</b> tells them apart — it is unique to the physical card. Stripe never shares the full card number or CVC with any merchant (PCI rules), so those cannot be shown.
                </div>

                @if($sameCard->isNotEmpty())
                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Same card, other payments</p>
                    <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                        @foreach($sameCard as $sp)
                        <a href="{{ route('admin.payments.show', $sp) }}" class="flex items-center justify-between px-4 py-2.5 text-sm hover:bg-slate-50">
                            <span class="text-gray-500">{{ $sp->created_at->format('M d, Y') }}</span>
                            <span class="font-mono text-indigo-600">{{ $sp->order->order_number ?? '—' }}</span>
                            <span class="text-gray-700">{{ $sp->order->shipping_name ?? $sp->cardholder_name ?? '—' }}</span>
                            <span class="font-semibold text-gray-800">{{ $sp->amount_label }}</span>
                            <span class="badge-{{ $sp->status_color }}">{{ ucfirst($sp->status) }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
                @else
                @if($payment->provider === 'paypal')
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-xs text-gray-400">Paid via</dt><dd class="font-medium text-gray-800">PayPal account</dd></div>
                    <div><dt class="text-xs text-gray-400">Payer email</dt><dd class="font-medium text-gray-800 break-all">{{ $payment->billing_email ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Payer name</dt><dd class="font-medium text-gray-800">{{ $payment->cardholder_name ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Payer country</dt><dd class="font-medium text-gray-800">{{ $payment->card_country ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">PayPal order</dt><dd class="font-mono text-xs text-gray-700 break-all">{{ $payment->payment_intent_id ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Capture ID</dt><dd class="font-mono text-xs text-gray-700 break-all">{{ $payment->charge_id ?: '—' }}</dd></div>
                </dl>
                <div class="mt-5 p-3 rounded-xl bg-slate-50 text-xs text-gray-500 leading-relaxed"><b class="text-gray-700">Verifying a customer:</b> match the <b>payer email</b> and <b>name</b>, or the PayPal order / capture ID from their PayPal receipt. PayPal does not share the funding card with merchants.</div>
                @else
                <p class="text-sm text-gray-400">
                    @if($payment->provider === 'cod') Cash on delivery — no card involved.
                    @elseif($payment->status === 'pending') No card has been submitted yet for this attempt.
                    @else Card details were not captured. @if($payment->payment_intent_id)Try <b>Refresh from Stripe</b>.@endif
                    @endif
                </p>
                @endif
                @endif
                @if($payment->failure_message)
                <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700"><b>Last error:</b> {{ $payment->failure_message }}</div>
                @endif
            </div>

            {{-- Order summary --}}
            @if($o)
            <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">Order <a href="{{ route('admin.orders.show', $o) }}" class="font-mono text-indigo-600 hover:underline">{{ $o->order_number }}</a></h3>
                    <span class="badge-{{ $o->status_color }}">{{ ucfirst($o->status) }}</span>
                </div>
                <table class="data-table">
                    <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($o->items as $it)
                        <tr><td class="text-sm text-gray-800">{{ $it->product_name }}</td><td class="text-center text-sm">{{ $it->quantity }}</td><td class="text-right text-sm font-semibold">{{ $o->money($it->subtotal) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-between font-bold text-gray-800"><span>Order total</span><span>{{ $o->money($o->total) }}</span></div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Payment</p>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Amount</span><span class="font-bold text-gray-800">{{ $payment->amount_label }} <span class="text-gray-400 font-normal">{{ $payment->currency }}</span></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Method</span><span class="text-gray-700 font-medium">{{ $payment->provider_label }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Status</span><span class="badge-{{ $payment->status_color }}">{{ ucfirst($payment->status) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Attempted</span><span class="text-gray-700">{{ $payment->created_at->format('M d, Y h:i A') }}</span></div>
                @if($payment->paid_at)<div class="flex justify-between text-sm"><span class="text-gray-500">Paid at</span><span class="text-gray-700">{{ $payment->paid_at->format('M d, Y h:i A') }}</span></div>@endif
                @if($payment->ip_address)<div class="flex justify-between text-sm"><span class="text-gray-500">Customer IP</span><span class="font-mono text-xs text-gray-700">{{ $payment->ip_address }}</span></div>@endif
            </div>

            @if($payment->provider === 'paypal' && $payment->payment_intent_id)
            <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">PayPal references</p>
                <div><p class="text-xs text-gray-400">Order ID</p><p class="font-mono text-xs text-gray-700 break-all">{{ $payment->payment_intent_id }}</p></div>
                @if($payment->charge_id)<div><p class="text-xs text-gray-400">Capture / transaction ID</p><a href="https://www.{{ \App\Support\PayPal::mode() === 'sandbox' ? 'sandbox.' : '' }}paypal.com/activity/payment/{{ $payment->charge_id }}" target="_blank" rel="noopener" class="font-mono text-xs text-indigo-600 hover:underline break-all">{{ $payment->charge_id }}</a></div>@endif
            </div>
            @endif
            @if($payment->provider === 'stripe')
            <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Stripe references</p>
                @if($payment->payment_intent_id)
                <div><p class="text-xs text-gray-400">Payment intent</p><a href="https://dashboard.stripe.com/{{ $testMode ? 'test/' : '' }}payments/{{ $payment->payment_intent_id }}" target="_blank" rel="noopener" class="font-mono text-xs text-indigo-600 hover:underline break-all">{{ $payment->payment_intent_id }}</a></div>
                @endif
                @if($payment->charge_id)<div><p class="text-xs text-gray-400">Charge</p><p class="font-mono text-xs text-gray-700 break-all">{{ $payment->charge_id }}</p></div>@endif
                @if($payment->receipt_url)<a href="{{ $payment->receipt_url }}" target="_blank" rel="noopener" class="block text-center text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl py-2.5">Open Stripe receipt →</a>@endif
            </div>
            @endif

            @if($o && $o->customer)
            <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Customer</p>
                <p class="font-semibold text-sm text-gray-800">{{ $o->customer->name }}</p>
                <p class="text-xs text-gray-400">{{ $o->customer->email }}</p>
                @if($o->shipping_phone)<p class="text-xs text-gray-500 mt-1">{{ $o->shipping_phone }}</p>@endif
                <a href="{{ route('admin.customers.show', $o->customer) }}" class="mt-3 block text-center text-xs text-indigo-600 hover:underline">View Customer →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
