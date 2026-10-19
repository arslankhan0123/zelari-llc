@extends('layouts.admin')
@section('title','Payments')

@section('content')
<div class="p-6 space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payments</h1>
            <p class="text-sm text-gray-400 mt-0.5">Every payment attempt — card and cash — with the card details Stripe reports, for verifying customer claims.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['Paid today',$stats['paid_today'],'text-emerald-600','bg-emerald-50'],['Paid (all time)',$stats['paid_total'],'text-indigo-600','bg-indigo-50'],['Card payments',$stats['card_paid'],'text-sky-600','bg-sky-50'],['Failed / cancelled',$stats['failed'],'text-red-500','bg-red-50']] as [$l,$v,$c,$bg])
        <div class="bg-white rounded-2xl p-5 flex items-center justify-between" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div><p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">{{ $l }}</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ $v }}</p></div>
            <div class="w-10 h-10 rounded-xl {{ $bg }} {{ $c }} flex items-center justify-center font-bold">{{ substr($l,0,1) }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.payments.index') }}" class="bg-white rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="relative flex-1 min-w-56">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Last 4 digits, order #, name, email, phone, Stripe ID…" class="form-input pl-9 w-full text-sm">
        </div>
        <select name="provider" class="form-input text-sm w-40">
            <option value="">All methods</option>
            <option value="stripe" {{ request('provider')==='stripe'?'selected':'' }}>Card (Stripe)</option>
            <option value="paypal" {{ request('provider')==='paypal'?'selected':'' }}>PayPal</option>
            <option value="cod"    {{ request('provider')==='cod'?'selected':'' }}>Cash on Delivery</option>
        </select>
        <select name="status" class="form-input text-sm w-36">
            <option value="">All statuses</option>
            @foreach(['paid','pending','failed','cancelled','refunded'] as $st)
            <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="form-input text-sm w-40" title="From">
        <input type="date" name="to" value="{{ request('to') }}" class="form-input text-sm w-40" title="To">
        <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request('card'))<input type="hidden" name="card" value="{{ request('card') }}"><span class="text-xs text-indigo-700 bg-indigo-50 px-2 py-1 rounded-lg">Filtered: one card</span>@endif
        @if(request()->hasAny(['search','status','provider','from','to','card']))
        <a href="{{ route('admin.payments.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($payments->count())
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Card</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                    <tr>
                        <td class="text-sm text-gray-500 whitespace-nowrap">{{ $p->created_at->format('M d, Y') }}<br><span class="text-xs text-gray-400">{{ $p->created_at->format('h:i A') }}</span></td>
                        <td>
                            @if($p->order)
                            <a href="{{ route('admin.orders.show', $p->order) }}" class="font-mono text-sm font-semibold text-indigo-600 hover:underline">{{ $p->order->order_number }}</a>
                            @else<span class="text-gray-400 text-sm">—</span>@endif
                        </td>
                        <td>
                            <p class="text-sm font-medium text-gray-800">{{ $p->order->shipping_name ?? $p->cardholder_name ?? 'Guest' }}</p>
                            <p class="text-xs text-gray-400">{{ $p->order->customer_email ?? $p->billing_email }}</p>
                        </td>
                        <td class="text-sm text-gray-700">
                            {{ $p->provider_label }}
                            @if($p->card_wallet)<span class="block text-xs text-gray-400">{{ ucwords(str_replace('_',' ',$p->card_wallet)) }}</span>@endif
                        </td>
                        <td>
                            @if($p->card_last4)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center h-6 px-2 rounded-md text-[10px] font-extrabold uppercase tracking-wide {{ ['visa'=>'bg-blue-50 text-blue-700','mastercard'=>'bg-orange-50 text-orange-700','amex'=>'bg-sky-50 text-sky-700'][$p->card_brand] ?? 'bg-gray-100 text-gray-600' }}">{{ $p->card_brand }}</span>
                                <span class="font-mono text-sm text-gray-800">•••• {{ $p->card_last4 }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Exp {{ $p->card_expiry }}@if($p->card_funding) · {{ ucfirst($p->card_funding) }}@endif @if($p->card_country) · {{ $p->card_country }}@endif</p>
                            @if($p->card_id)<a href="{{ route('admin.payments.index', ['card' => $p->card_fingerprint]) }}" class="inline-block mt-1 font-mono text-[11px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded hover:bg-indigo-100" title="Card ID — click to see every payment with this exact card">{{ $p->card_id }}</a>@endif
                            @elseif($p->provider === 'paypal')
                            <div class="flex items-center gap-2"><span class="inline-flex items-center justify-center h-6 px-2 rounded-md text-[10px] font-extrabold uppercase tracking-wide bg-blue-50 text-blue-800">PayPal</span><span class="text-sm text-gray-700 truncate max-w-[160px]">{{ $p->billing_email ?: '—' }}</span></div>
                            @if($p->cardholder_name)<p class="text-xs text-gray-400 mt-0.5">{{ $p->cardholder_name }}@if($p->card_country) · {{ $p->card_country }}@endif</p>@endif
                            @else<span class="text-gray-400 text-sm">—</span>@endif
                        </td>
                        <td class="font-semibold text-gray-800 whitespace-nowrap">{{ $p->amount_label }}<span class="block text-xs font-normal text-gray-400">{{ $p->currency }}</span></td>
                        <td>
                            <span class="badge-{{ $p->status_color }}">{{ ucfirst($p->status) }}</span>
                            @if($p->failure_message)<p class="text-[11px] text-red-500 mt-1 max-w-[180px] truncate" title="{{ $p->failure_message }}">{{ $p->failure_message }}</p>@endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.payments.show', $p) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition" title="Details">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($p->receipt_url)
                                <a href="{{ $p->receipt_url }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition" title="Stripe receipt">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h7l5 5v11a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
        @endif
        @else
        <div class="py-16 text-center">
            <p class="text-gray-400 font-medium">No payments match</p>
            <p class="text-xs text-gray-400 mt-1">Payments appear here as soon as an order is placed.</p>
        </div>
        @endif
    </div>
</div>
@endsection
