@extends('layouts.admin')
@section('title','Earnings')


@section('content')
<div class="p-6 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Earnings</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $isVendor ? 'Your sales, commission and payout balance' : 'Marketplace earnings across all vendors' }}</p>
        </div>
        <a href="{{ route('admin.earnings.export') }}" class="btn-primary flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 7H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    @if($isVendor)
    @php $s = $summary; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Available</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ \App\Support\Currency::baseMoney($s['available']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">Ready to withdraw</p>
        </div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Earned</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ \App\Support\Currency::baseMoney($s['earned']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">On delivered orders</p>
        </div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ \App\Support\Currency::baseMoney($s['pending']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">Not yet delivered</p>
        </div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Paid out</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ \App\Support\Currency::baseMoney($s['paid']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">Total withdrawn</p>
        </div>
    </div>

    @can('payouts.create')
    <div class="flex">
        <a href="{{ route('admin.payouts.index') }}" class="text-sm text-indigo-600 hover:underline flex items-center gap-1">
            Request a payout →
        </a>
    </div>
    @endcan

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Order earnings</h3></div>
        @if($rows->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Order</th><th>Date</th><th class="text-right" style="text-align:right">Subtotal</th><th class="text-right" style="text-align:right">Commission</th><th class="text-right" style="text-align:right">Payout</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($rows as $vo)
                <tr>
                    <td class="font-mono text-xs text-indigo-600">{{ $vo->sub_number }}</td>
                    <td class="text-sm text-gray-500">{{ optional($vo->order)->created_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="text-right text-sm text-gray-700">{{ \App\Support\Currency::baseMoney($vo->subtotal) }}</td>
                    <td class="text-right text-sm text-gray-500">{{ \App\Support\Currency::baseMoney($vo->commission_amount) }}</td>
                    <td class="text-right text-sm font-semibold text-emerald-600">{{ \App\Support\Currency::baseMoney($vo->payout_amount) }}</td>
                    <td><span class="badge-{{ $vo->status_color }}">{{ ucfirst($vo->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($rows->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $rows->links() }}</div>@endif
        @else
        <p class="px-6 py-10 text-sm text-gray-400 text-center">No earnings yet. They'll appear here as your orders are placed.</p>
        @endif
    </div>

    @else
    {{-- Admin overview --}}
    @php $t = $totals; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Gross sales</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ \App\Support\Currency::baseMoney($t['gross']) }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Platform commission</p><p class="text-2xl font-bold text-indigo-600 mt-1">{{ \App\Support\Currency::baseMoney($t['commission']) }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Vendor earnings</p><p class="text-2xl font-bold text-gray-800 mt-1">{{ \App\Support\Currency::baseMoney($t['earned']) }}</p></div>
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Paid out</p><p class="text-2xl font-bold text-emerald-600 mt-1">{{ \App\Support\Currency::baseMoney($t['paid']) }}</p></div>
    </div>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">By vendor</h3></div>
        @if($overview->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Vendor</th><th class="text-right" style="text-align:right">Gross</th><th class="text-right" style="text-align:right">Commission</th><th class="text-right" style="text-align:right">Earned</th><th class="text-right" style="text-align:right">Pending</th><th class="text-right" style="text-align:right">Paid</th><th class="text-right" style="text-align:right">Available</th></tr></thead>
            <tbody>
                @foreach($overview as $row)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $row['vendor']->name }}</td>
                    <td class="text-right text-sm text-gray-700">{{ \App\Support\Currency::baseMoney($row['gross']) }}</td>
                    <td class="text-right text-sm text-indigo-600">{{ \App\Support\Currency::baseMoney($row['commission']) }}</td>
                    <td class="text-right text-sm text-gray-700">{{ \App\Support\Currency::baseMoney($row['earned']) }}</td>
                    <td class="text-right text-sm text-amber-600">{{ \App\Support\Currency::baseMoney($row['pending']) }}</td>
                    <td class="text-right text-sm text-gray-500">{{ \App\Support\Currency::baseMoney($row['paid']) }}</td>
                    <td class="text-right text-sm font-semibold text-emerald-600">{{ \App\Support\Currency::baseMoney($row['available']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <p class="px-6 py-10 text-sm text-gray-400 text-center">No vendors yet.</p>
        @endif
    </div>
    @endif
</div>
@endsection
