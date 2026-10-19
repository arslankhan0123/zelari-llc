@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Welcome back, ' . Auth::user()->name . '! Here\'s what\'s happening.')

@section('content')
@php
    // Scale a 12-value series to bar heights (%). Flat/empty series show short, even bars.
    $bars = function ($series) {
        $max = max(1, (float) collect($series)->max());
        return collect($series)->map(fn ($v) => $v > 0 ? max(8, (int) round($v / $max * 100)) : 6)->all();
    };
    $deltaLine = function (?float $d, string $when = 'from last month') {
        if ($d === null) return ['text' => 'New this month', 'cls' => 'text-emerald-600', 'up' => true];
        if ($d == 0.0)   return ['text' => 'No change ' . $when, 'cls' => 'text-gray-400', 'up' => null];
        return ['text' => ($d > 0 ? '+' : '') . $d . '% ' . $when, 'cls' => $d > 0 ? 'text-emerald-600' : 'text-rose-500', 'up' => $d > 0];
    };
@endphp

<!-- Stats Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">

    <!-- Total Revenue -->
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                @php $d = $deltaLine($revenueDelta); @endphp
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ zprice($revenueTotal) }}</p>
                <p class="text-xs mt-2 flex items-center gap-1 {{ $d['cls'] }} font-medium">
                    @if($d['up'] === true)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    @elseif($d['up'] === false)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>@endif
                    {{ $d['text'] }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 bg-brand-grad">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <!-- Mini chart bar -->
        <div class="flex items-end gap-1 h-8">
            @foreach($bars($spark['revenue']) as $i => $h)
                <div class="flex-1 rounded-sm opacity-30 hover:opacity-60 transition-opacity" title="{{ now()->subMonths(11 - $i)->format('M Y') }}: {{ zprice($spark['revenue'][$i]) }}"
                     style="height:{{ $h }}%; background:linear-gradient(to top,var(--brand),var(--brand-light));"></div>
            @endforeach
        </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                @php $d = $deltaLine($ordersDelta); @endphp
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($ordersTotal) }}</p>
                <p class="text-xs mt-2 flex items-center gap-1 {{ $d['cls'] }} font-medium">
                    @if($d['up'] === true)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    @elseif($d['up'] === false)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>@endif
                    {{ $d['text'] }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#06b6d4,#0891b2);">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="flex items-end gap-1 h-8">
            @foreach($bars($spark['orders']) as $i => $h)
                <div class="flex-1 rounded-sm opacity-30 hover:opacity-60 transition-opacity" title="{{ now()->subMonths(11 - $i)->format('M Y') }}: {{ (int) $spark['orders'][$i] }} orders"
                     style="height:{{ $h }}%; background:linear-gradient(to top,#06b6d4,#0891b2);"></div>
            @endforeach
        </div>
    </div>

    <!-- Total Products -->
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Products</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($productsTotal) }}</p>
                <p class="text-xs mt-2 flex items-center gap-1 {{ $productsWeek > 0 ? 'text-emerald-600' : 'text-gray-400' }} font-medium">
                    @if($productsWeek > 0)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>@endif
                    {{ $productsWeek > 0 ? '+' . $productsWeek . ' added this week' : 'None added this week' }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <div class="flex items-end gap-1 h-8">
            @foreach($bars($spark['products']) as $i => $h)
                <div class="flex-1 rounded-sm opacity-30 hover:opacity-60 transition-opacity" title="{{ now()->subMonths(11 - $i)->format('M Y') }}: {{ (int) $spark['products'][$i] }} added"
                     style="height:{{ $h }}%; background:linear-gradient(to top,#f59e0b,#d97706);"></div>
            @endforeach
        </div>
    </div>

    <!-- Customers -->
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Customers</p>
                @php $d = $deltaLine($customersDelta); @endphp
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($customersTotal) }}</p>
                <p class="text-xs mt-2 flex items-center gap-1 {{ $d['cls'] }} font-medium">
                    @if($d['up'] === true)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    @elseif($d['up'] === false)<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>@endif
                    {{ $d['text'] }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background:linear-gradient(135deg,#10b981,#059669);">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div class="flex items-end gap-1 h-8">
            @foreach($bars($spark['customers']) as $i => $h)
                <div class="flex-1 rounded-sm opacity-30 hover:opacity-60 transition-opacity" title="{{ now()->subMonths(11 - $i)->format('M Y') }}: {{ (int) $spark['customers'][$i] }} new"
                     style="height:{{ $h }}%; background:linear-gradient(to top,#10b981,#059669);"></div>
            @endforeach
        </div>
    </div>

</div>

<!-- Middle row: Recent Orders + Top Categories -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-7">

    <!-- Recent Orders table -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="font-semibold text-gray-800">Recent Orders</h2>
                <p class="text-xs text-gray-400 mt-0.5">Latest 5 orders</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-indigo-600 font-medium hover:text-indigo-800 transition">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $o)
                    @php $name = $o->customer->name ?? $o->shipping_name ?? 'Guest'; @endphp
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $o) }}" class="font-semibold text-indigo-600 hover:underline font-mono text-sm">{{ $o->order_number }}</a><div class="text-[11px] text-gray-400">{{ $o->created_at->diffForHumans() }}</div></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">{{ strtoupper(substr($name,0,1)) }}</div>
                                <div><div class="text-sm">{{ $name }}</div>@if($o->shipping_city)<div class="text-[11px] text-gray-400">{{ $o->shipping_city }}</div>@endif</div>
                            </div>
                        </td>
                        <td class="text-gray-500">{{ $o->items_count }} {{ Str::plural('item', $o->items_count) }}</td>
                        <td class="font-semibold">{{ $o->money($o->total) }}<div class="text-[11px] font-normal {{ $o->payment_status === 'paid' ? 'text-emerald-600' : 'text-gray-400' }}">{{ $o->payment_label }}{{ $o->payment_status === 'paid' ? ' · paid' : '' }}</div></td>
                        <td><span class="badge-{{ $o->status_color }}">{{ ucfirst($o->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-10 text-sm">No orders yet — they'll show up here as customers check out.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick info panel -->
    <div class="space-y-5">

        <!-- Categories quick access -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Top Categories</h2>
                <a href="{{ route('admin.categories.index') }}"
                   class="text-xs text-indigo-600 font-medium hover:text-indigo-800">View all →</a>
            </div>
            <div class="space-y-2.5">
                @php $cats = $topCategories; @endphp
                @forelse($cats as $cat)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-brand-grad">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ $cat->name }}</div>
                            <div class="text-xs text-gray-400">{{ $cat->products_count }} {{ Str::plural('product', $cat->products_count) }}@if($cat->children_count) · {{ $cat->children_count }} sub</div>@else</div>@endif
                        </div>
                    </div>
                    <span class="{{ $cat->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                        {{ ucfirst($cat->status) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-6">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">No categories yet</p>
                    <a href="{{ route('admin.categories.create') }}"
                       class="text-xs text-indigo-600 font-medium mt-1 inline-block">Create first →</a>
                </div>
                @endforelse
            </div>
            @if($cats->count())
            <a href="{{ route('admin.categories.create') }}"
               class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-medium text-indigo-600 border border-indigo-200 hover:bg-indigo-50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add New Category
            </a>
            @endif
        </div>

        <!-- Quick actions -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.categories.create') }}"
                   class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-indigo-50 transition text-center group">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition bg-brand-grad">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600">Add Category</span>
                </a>
                <a href="{{ route('admin.products.create') }}"
                   class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-amber-50 transition text-center group">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition"
                         style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600">Add Product</span>
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-green-50 transition text-center group">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition"
                         style="background:linear-gradient(135deg,#10b981,#059669);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600">My Profile</span>
                </a>
                <a href="{{ route('admin.payments.index') }}"
                   class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-cyan-50 transition text-center group">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition"
                         style="background:linear-gradient(135deg,#06b6d4,#0891b2);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600">Payments</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bottom: Activity feed + Platform stats -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
    @php
        $sd = $deltaLine($salesDelta, 'vs yesterday');
        $tiles = [
            ['Sales Today', zprice($salesToday), $salesDelta === null ? ($salesToday > 0 ? 'First sales of the day' : 'No sales yet today') : ($salesDelta > 0 ? '↑ ' : ($salesDelta < 0 ? '↓ ' : '')) . abs($salesDelta) . '% vs yesterday', 'var(--brand-grad)', route('admin.orders.index')],
            ['Pending Orders', $pendingOrders, $pendingOrders ? '⚡ ' . $unpaidOrders . ' awaiting payment' : 'All caught up', 'linear-gradient(135deg,#f59e0b,#d97706)', route('admin.orders.index', ['status' => 'pending'])],
            ['Low Stock Items', $lowStock, $lowStock ? '⚠ ' . $outOfStock . ' out of stock' : 'Stock levels healthy', 'linear-gradient(135deg,#ef4444,#dc2626)', route('admin.products.index')],
            ['Paid Today', zprice($paidToday), $paidTodayCount . ' ' . Str::plural('payment', $paidTodayCount) . ' received', 'linear-gradient(135deg,#10b981,#059669)', route('admin.payments.index', ['status' => 'paid'])],
        ];
    @endphp
    @foreach($tiles as [$title,$val,$sub,$grad,$href])
    <a href="{{ $href }}" class="rounded-2xl p-5 text-white relative overflow-hidden block hover:-translate-y-0.5 transition" style="background:{{ $grad }};box-shadow:0 8px 30px rgba(0,0,0,.15)">
        <div class="absolute inset-0 opacity-10" style="background:radial-gradient(circle at 80% 20%,white,transparent)"></div>
        <p class="text-white/70 text-sm font-medium">{{ $title }}</p>
        <p class="text-3xl font-bold mt-1 mb-2">{{ $val }}</p>
        <p class="text-white/60 text-xs">{{ $sub }}</p>
    </a>
    @endforeach
</div>

@endsection
