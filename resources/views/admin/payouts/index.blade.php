@extends('layouts.admin')
@section('title','Payouts')


@section('content')
<div class="p-6 space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Payouts</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ $isVendor ? 'Request withdrawals from your available balance' : 'Review and process vendor payout requests' }}</p>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-600 text-sm">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    @if($isVendor)
    @php $s = $summary; @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Request form --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Available balance</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ \App\Support\Currency::baseMoney($s['available']) }}</p>
                <p class="text-[11px] text-gray-400 mt-1">Earned {{ \App\Support\Currency::baseMoney($s['earned']) }} · Reserved {{ \App\Support\Currency::baseMoney($s['reserved']) }}</p>
            </div>

            @can('payouts.create')
            <div class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <h3 class="font-semibold text-gray-700">Request a payout</h3>
                @if($s['available'] < 1)
                <p class="text-sm text-gray-400">You have no available balance to withdraw yet.</p>
                @else
                <form method="POST" action="{{ route('admin.payouts.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                        <input type="number" step="0.01" min="1" max="{{ $s['available'] }}" name="amount" value="{{ old('amount') }}" class="form-input w-full text-sm" required>
                        <p class="text-[11px] text-gray-400 mt-1">Max {{ \App\Support\Currency::baseMoney($s['available']) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Method (optional)</label>
                        <input type="text" name="method" value="{{ old('method') }}" placeholder="Bank transfer, JazzCash…" class="form-input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Note (optional)</label>
                        <textarea name="note" rows="2" class="form-input w-full text-sm resize-none" placeholder="Account details or a message">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full text-center text-sm">Submit Request</button>
                </form>
                @endif
            </div>
            @endcan
        </div>

        {{-- Vendor's requests --}}
        <div class="lg:col-span-2 bg-white rounded-2xl overflow-hidden self-start" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Your requests</h3></div>
            @if($requests->count())
            <div class="table-scroll"><table class="data-table">
                <thead><tr><th>Date</th><th class="text-right" style="text-align:right">Amount</th><th>Method</th><th>Status</th><th>Admin note</th></tr></thead>
                <tbody>
                    @foreach($requests as $r)
                    <tr>
                        <td class="text-sm text-gray-500">{{ $r->created_at->format('M d, Y') }}</td>
                        <td class="text-right font-semibold text-gray-800">{{ \App\Support\Currency::baseMoney($r->amount) }}</td>
                        <td class="text-sm text-gray-600">{{ $r->method ?: '—' }}</td>
                        <td><span class="badge-{{ $r->status_color }}">{{ ucfirst($r->status) }}</span>@if($r->status==='paid' && $r->reference)<p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $r->reference }}</p>@endif</td>
                        <td class="text-sm text-gray-500">{{ $r->admin_note ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @if($requests->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>@endif
            @else
            <p class="px-6 py-10 text-sm text-gray-400 text-center">No payout requests yet.</p>
            @endif
        </div>
    </div>

    @else
    {{-- Admin queue --}}
    <form method="GET" class="bg-white rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <select name="status" class="form-input text-sm w-40">
            <option value="">All Statuses</option>
            @foreach(['pending','approved','paid','rejected'] as $st)
            <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @if($vendors->count())
        <select name="vendor" class="form-input text-sm w-44">
            <option value="">All Vendors</option>
            @foreach($vendors as $v)<option value="{{ $v->id }}" {{ (string)request('vendor')===(string)$v->id?'selected':'' }}>{{ $v->name }}</option>@endforeach
        </select>
        @endif
        <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request()->hasAny(['status','vendor']))<a href="{{ route('admin.payouts.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($requests->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Vendor</th><th class="text-right" style="text-align:right">Amount</th><th>Method</th><th>Requested</th><th>Status</th><th class="text-center" style="text-align:center">Actions</th></tr></thead>
            <tbody>
                @foreach($requests as $r)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $r->vendor->name ?? 'Vendor #'.$r->vendor_id }}@if($r->note)<p class="text-[11px] text-gray-400 mt-0.5">{{ $r->note }}</p>@endif</td>
                    <td class="text-right font-semibold text-gray-800">{{ \App\Support\Currency::baseMoney($r->amount) }}</td>
                    <td class="text-sm text-gray-600">{{ $r->method ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $r->created_at->format('M d, Y') }}</td>
                    <td><span class="badge-{{ $r->status_color }}">{{ ucfirst($r->status) }}</span>@if($r->reference)<p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $r->reference }}</p>@endif</td>
                    <td>
                        <div class="flex items-center justify-center gap-1.5">
                            @can('payouts.edit')
                            @if($r->status === 'pending')
                            <form method="POST" action="{{ route('admin.payouts.approve',$r) }}">@csrf
                                <button type="submit" title="Approve" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                            </form>
                            <form method="POST" action="{{ route('admin.payouts.reject',$r) }}" onsubmit="return confirm('Reject this payout request?')">@csrf
                                <button type="submit" title="Reject" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </form>
                            @elseif($r->status === 'approved')
                            <form method="POST" action="{{ route('admin.payouts.pay',$r) }}" onsubmit="return confirm('Mark this payout as paid?')">@csrf
                                <button type="submit" title="Mark paid" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg></button>
                            </form>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($requests->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>@endif
        @else
        <div class="py-16 text-center text-sm text-gray-400">No payout requests{{ request()->hasAny(['status','vendor']) ? ' match your filters' : ' yet' }}.</div>
        @endif
    </div>
    @endif
</div>
@endsection
