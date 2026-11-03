@extends('layouts.admin')
@section('title','Reports')
@php use App\Support\Currency; $maxDay = max(1, max(array_values($series) ?: [0])); @endphp
@section('content')
<div class="p-6 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-gray-800">Reports</h1><p class="text-sm text-gray-400 mt-0.5">Sales, products and customer segments.</p></div>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.export.sales') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Export sales CSV</a>
            @unless(auth()->user()->vendorId())<a href="{{ route('admin.reports.export.segments') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Export segments CSV</a>@endunless
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Revenue (paid)</p><p class="text-2xl font-bold text-emerald-600 mt-1">{{ Currency::baseMoney($revenue) }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Paid orders</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ $paidCount }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Avg order value</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ Currency::baseMoney($aov) }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">All orders</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ $orders }}</p></div>
    </div>

    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <h3 class="font-semibold text-gray-700 mb-4">Revenue — last 30 days</h3>
        <div style="display:flex;align-items:flex-end;gap:3px;height:140px">
            @foreach($series as $day => $val)
            <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;height:100%" title="{{ $day }}: {{ Currency::baseMoney($val) }}">
                <div style="background:linear-gradient(180deg,#6366f1,#818cf8);border-radius:4px 4px 0 0;height:{{ max(2, round($val/$maxDay*100)) }}%"></div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Top products</h3></div>
            @if($topProducts->count())
            <div class="table-scroll"><table class="data-table">
                <thead><tr><th>Product</th><th class="text-right">Units</th><th class="text-right">Revenue</th></tr></thead>
                <tbody>@foreach($topProducts as $p)<tr><td class="text-sm text-gray-800">{{ $p->product_name }}</td><td class="text-right text-sm text-gray-600">{{ (int)$p->qty }}</td><td class="text-right text-sm font-semibold text-gray-800">{{ Currency::baseMoney($p->revenue) }}</td></tr>@endforeach</tbody>
            </table></div>
            @else<p class="py-10 text-center text-sm text-gray-400">No sales yet.</p>@endif
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <h3 class="font-semibold text-gray-700 mb-4">Orders by status</h3>
                <div class="space-y-2">
                    @foreach(['pending','processing','shipped','delivered','cancelled'] as $st)
                    <div class="flex items-center justify-between text-sm"><span class="badge-{{ ['pending'=>'yellow','processing'=>'blue','shipped'=>'indigo','delivered'=>'green','cancelled'=>'red'][$st] }}">{{ ucfirst($st) }}</span><b class="text-gray-700">{{ $byStatus[$st] ?? 0 }}</b></div>
                    @endforeach
                </div>
            </div>
            @unless(auth()->user()->vendorId())
            <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <h3 class="font-semibold text-gray-700 mb-4">Customer segments</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">New</span><b>{{ $segments['new'] }}</b></div>
                    <div class="flex justify-between"><span class="text-gray-500">Repeat</span><b>{{ $segments['repeat'] }}</b></div>
                    <div class="flex justify-between"><span class="text-gray-500">VIP</span><b>{{ $segments['vip'] }}</b></div>
                    <div class="flex justify-between"><span class="text-gray-500">Lapsed</span><b>{{ $segments['lapsed'] }}</b></div>
                </div>
            </div>
            @endunless
        </div>
    </div>
</div>
@endsection
