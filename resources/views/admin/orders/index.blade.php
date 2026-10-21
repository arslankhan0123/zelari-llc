@extends('layouts.admin')
@section('title','Orders')
@section('order_open','true')

@section('content')
<div class="p-6 space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
            <p class="text-sm text-gray-400 mt-0.5">Track and manage customer orders</p>
        </div>
        <a href="{{ route('admin.orders.create') }}"
           class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Order
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2 text-emerald-700 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.orders.index') }}"
          class="bg-white rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order # or customer…" class="form-input pl-9 w-full text-sm">
        </div>
        <select name="status" class="form-input text-sm w-40">
            <option value="">All Statuses</option>
            @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="form-input text-sm w-36">
            <option value="">All Payments</option>
            <option value="unpaid"   {{ request('payment_status')==='unpaid'   ? 'selected':'' }}>Unpaid</option>
            <option value="paid"     {{ request('payment_status')==='paid'     ? 'selected':'' }}>Paid</option>
            <option value="refunded" {{ request('payment_status')==='refunded' ? 'selected':'' }}>Refunded</option>
        </select>
        <button type="submit" class="btn-primary text-sm px-4 py-2 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['search','status','payment_status']))
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($orders->count())
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Location</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                        $statusColors = ['pending'=>'yellow','processing'=>'blue','shipped'=>'indigo','delivered'=>'green','cancelled'=>'red'];
                        $payColors    = ['paid'=>'green','unpaid'=>'red','refunded'=>'yellow'];
                        $sc = $statusColors[$order->status] ?? 'gray';
                        $pc = $payColors[$order->payment_status] ?? 'gray';
                    @endphp
                    <tr>
                        <td>
                            <span class="font-mono text-sm font-semibold text-indigo-600">{{ $order->order_number }}</span>
                        </td>
                        <td>
                            @if($order->customer)
                            @php $acctMismatch = $order->user && strcasecmp((string) $order->user->email, (string) $order->customer->email) !== 0; @endphp
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->customer->email }}</p>
                                @if($acctMismatch)
                                <p class="text-xs text-gray-500 mt-0.5" title="Logged-in account that placed this order">(<b class="text-gray-800">{{ $order->user->email }}</b>)</p>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-400 text-sm">{{ $order->shipping_name ?? 'Guest' }}</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold">{{ $order->items_count ?? '—' }}</span>
                        </td>
                        <td class="font-semibold text-gray-800">{{ $order->money($order->total) }}</td>
                        <td class="text-sm">
                            <p class="text-gray-700">{{ $order->shipping_city ?? '—' }}</p>
                            @if($order->ip_location)<p class="text-xs {{ $order->location_matches === false ? 'text-amber-600' : 'text-gray-400' }}" title="From IP {{ $order->ip_address }}"><span class="font-mono">IP</span> {{ $order->ip_city ?: $order->ip_country }}{{ $order->ip_city && $order->ip_country_code ? ', '.$order->ip_country_code : '' }}</p>@endif
                        </td>
                        <td>
                            <span class="badge-{{ $pc }}">{{ ucfirst($order->payment_status) }}</span>
                        </td>
                        <td>
                            <span class="badge-{{ $sc }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition" title="View">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete order {{ $order->order_number }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $orders->withQueryString()->links() }}</div>
        @endif
        @else
        <div class="py-16 text-center">
            <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-gray-400 font-medium">No orders found</p>
            <a href="{{ route('admin.orders.create') }}" class="mt-3 inline-flex text-sm text-indigo-600 hover:underline">Create your first order</a>
        </div>
        @endif
    </div>
</div>
@endsection
