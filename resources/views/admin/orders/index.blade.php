@extends('layouts.admin')
@section('title','Orders')
@section('order_open','true')

@section('content')
@php $isVendor = $isVendor ?? auth()->user()?->isVendor(); @endphp
<div class="p-6 space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                {{ $isVendor ? 'Orders that include your products' : 'Track and manage customer orders' }}
            </p>
        </div>
        @can('orders.create')
        <a href="{{ route('admin.orders.create') }}" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Order
        </a>
        @endcan
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
        @unless($isVendor)
        <select name="payment_status" class="form-input text-sm w-36">
            <option value="">All Payments</option>
            <option value="unpaid"   {{ request('payment_status')==='unpaid'   ? 'selected':'' }}>Unpaid</option>
            <option value="paid"     {{ request('payment_status')==='paid'     ? 'selected':'' }}>Paid</option>
            <option value="refunded" {{ request('payment_status')==='refunded' ? 'selected':'' }}>Refunded</option>
        </select>
        @if(isset($vendors) && $vendors->count())
        <select name="vendor" class="form-input text-sm w-44">
            <option value="">All Vendors</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ (string)request('vendor')===(string)$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        @endif
        @endunless
        <button type="submit" class="btn-primary text-sm px-4 py-2 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['search','status','payment_status','vendor']))
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
                        @if($isVendor)
                        <th>Your Subtotal</th>
                        <th>Your Payout</th>
                        <th>Your Status</th>
                        @else
                        <th>Total</th>
                        <th>Vendors</th>
                        <th>Payment</th>
                        <th>Status</th>
                        @endif
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                        $vo = $isVendor ? $order->vendorOrders->first() : null;
                    @endphp
                    <tr>
                        <td>
                            <span class="font-mono text-sm font-semibold text-indigo-600">{{ $isVendor && $vo ? $vo->sub_number : $order->order_number }}</span>
                            @if($isVendor && $vo)<p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $order->order_number }}</p>@endif
                        </td>
                        <td>
                            @if($order->customer)
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->customer->email }}</p>
                            </div>
                            @else
                            <span class="text-gray-400 text-sm">{{ $order->shipping_name ?? 'Guest' }}</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold">{{ $order->items_count ?? '—' }}</span>
                        </td>

                        @if($isVendor)
                        <td class="font-semibold text-gray-800">{{ $vo ? $order->money($vo->subtotal) : '—' }}</td>
                        <td class="text-sm text-emerald-600 font-medium">{{ $vo ? $order->money($vo->payout_amount) : '—' }}</td>
                        <td><span class="badge-{{ $vo->status_color ?? 'gray' }}">{{ ucfirst($vo->status ?? '—') }}</span></td>
                        @else
                        <td class="font-semibold text-gray-800">{{ $order->money($order->total) }}</td>
                        <td>
                            @php $vcount = $order->vendorOrders->count(); @endphp
                            @if($vcount)
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600" title="{{ $order->vendorOrders->pluck('vendor.name')->filter()->join(', ') }}">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4"/></svg>
                                {{ $vcount }} {{ \Illuminate\Support\Str::plural('vendor', $vcount) }}
                            </span>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                        <td><span class="badge-{{ $order->payment_status_color }}">{{ ucfirst($order->payment_status) }}</span></td>
                        <td><span class="badge-{{ $order->status_color }}">{{ ucfirst($order->status) }}</span></td>
                        @endif

                        <td>
                            <x-row-actions module="orders"
                                :view="route('admin.orders.show', $order)"
                                :edit="route('admin.orders.edit', $order)"
                                :delete="route('admin.orders.destroy', $order)"
                                confirm="Delete order {{ $order->order_number }}?" />
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
            @can('orders.create')
            <a href="{{ route('admin.orders.create') }}" class="mt-3 inline-flex text-sm text-indigo-600 hover:underline">Create your first order</a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
