@extends('layouts.admin')
@section('title', $customer->name)
@section('cust_open','true')

@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">Customer Details</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.edit', $customer) }}"
               class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left: Info --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl font-bold shrink-0"
                         style="background: {{ ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'][crc32($customer->name) % 6] }}">
                        {{ $customer->initial }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">{{ $customer->name }}</h2>
                        <p class="text-sm text-gray-400">{{ $customer->email }}</p>
                    </div>
                    @if($customer->status === 'active')
                        <span class="badge-active ml-auto">Active</span>
                    @else
                        <span class="badge-inactive ml-auto">Inactive</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-gray-400 text-xs mb-0.5">Phone</p><p class="font-medium text-gray-700">{{ $customer->phone ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs mb-0.5">City</p><p class="font-medium text-gray-700">{{ $customer->city ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs mb-0.5">Country</p><p class="font-medium text-gray-700">{{ $customer->country ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs mb-0.5">Member Since</p><p class="font-medium text-gray-700">{{ $customer->created_at->format('M d, Y') }}</p></div>
                    @if($customer->address)
                    <div class="col-span-2"><p class="text-gray-400 text-xs mb-0.5">Address</p><p class="font-medium text-gray-700">{{ $customer->address }}</p></div>
                    @endif
                </div>
            </div>

            {{-- Orders --}}
            <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">Order History</h3>
                    <span class="text-xs text-gray-400">{{ $customer->orders->count() }} orders</span>
                </div>
                @if($customer->orders->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th class="text-center">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->orders as $order)
                        <tr>
                            <td class="font-mono text-sm font-semibold text-indigo-600">{{ $order->order_number }}</td>
                            <td class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td><span class="badge-{{ $order->status_color }}">{{ ucfirst($order->status) }}</span></td>
                            <td class="font-semibold text-gray-800">{{ $order->money($order->total) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.orders.show', $order) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="py-10 text-center text-gray-400 text-sm">No orders yet</div>
                @endif
            </div>
        </div>

        {{-- Right: Stats --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3">Summary</p>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Orders</span>
                        <span class="font-bold text-gray-800">{{ $customer->orders->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Spent</span>
                        <span class="font-bold text-indigo-600">{{ zprice($customer->orders->sum('total')) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Delivered</span>
                        <span class="font-bold text-emerald-600">{{ $customer->orders->where('status','delivered')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Pending</span>
                        <span class="font-bold text-amber-600">{{ $customer->orders->where('status','pending')->count() }}</span>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer and all their data?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 text-sm rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Customer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
