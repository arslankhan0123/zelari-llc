@extends('layouts.admin')
@section('title','Returns & Refunds')
@section('content')
<div class="p-6 space-y-5">
    <div><h1 class="text-2xl font-bold text-gray-800">Returns &amp; Refunds</h1><p class="text-sm text-gray-400 mt-0.5">Customer return, exchange and refund requests.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <form method="GET" class="bg-white rounded-2xl px-5 py-4 flex items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <select name="status" class="form-input text-sm w-44">
            <option value="">All Statuses</option>
            @foreach(['requested','approved','rejected','completed'] as $st)<option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>@endforeach
        </select>
        <button class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request('status'))<a href="{{ route('admin.returns.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($requests->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Ref</th><th>Order</th><th>Type</th><th>Email</th><th>Requested</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($requests as $r)
                <tr>
                    <td class="font-mono text-xs text-indigo-600">{{ $r->ref }}</td>
                    <td class="font-mono text-xs text-gray-600">{{ optional($r->order)->order_number ?? '—' }}</td>
                    <td class="text-sm text-gray-700">{{ ucfirst($r->type) }}</td>
                    <td class="text-sm text-gray-500">{{ $r->customer_email }}</td>
                    <td class="text-sm text-gray-400">{{ $r->created_at->format('M d, Y') }}</td>
                    <td><span class="badge-{{ $r->status_color }}">{{ ucfirst($r->status) }}</span></td>
                    <td><div class="flex justify-center"><a href="{{ route('admin.returns.show',$r) }}" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center" title="View"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></div></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($requests->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>@endif
        @else<div class="py-16 text-center text-sm text-gray-400">No return requests{{ request('status') ? ' with this status' : ' yet' }}.</div>@endif
    </div>
</div>
@endsection
