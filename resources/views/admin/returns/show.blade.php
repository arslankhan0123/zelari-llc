@extends('layouts.admin')
@section('title','Return '.$return->ref)
@section('content')
<div class="p-6 space-y-5 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.returns.index') }}" class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <div><h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $return->ref }}</h1><p class="text-sm text-gray-400 mt-0.5">{{ ucfirst($return->type) }} · <span class="badge-{{ $return->status_color }}">{{ ucfirst($return->status) }}</span></p></div>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <div class="bg-white rounded-2xl p-6 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><p class="text-gray-400 text-xs">Order</p><p class="font-mono text-gray-700">{{ optional($return->order)->order_number ?? '—' }}</p></div>
            <div><p class="text-gray-400 text-xs">Email</p><p class="text-gray-700">{{ $return->customer_email }}</p></div>
            <div><p class="text-gray-400 text-xs">Order total</p><p class="text-gray-700">{{ $return->order ? $return->order->money($return->order->total) : '—' }}</p></div>
            <div><p class="text-gray-400 text-xs">Requested</p><p class="text-gray-700">{{ $return->created_at->format('M d, Y · H:i') }}</p></div>
        </div>
        <div><p class="text-gray-400 text-xs mb-1">Reason</p><p class="text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $return->reason }}</p></div>
    </div>

    <form method="POST" action="{{ route('admin.returns.update',$return) }}" class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf @method('PUT')
        <h3 class="font-semibold text-gray-700">Process request</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-600 mb-1.5">Status</label>
                <select name="status" class="form-input w-full text-sm">
                    @foreach(['requested','approved','rejected','completed'] as $st)<option value="{{ $st }}" {{ $return->status===$st?'selected':'' }}>{{ ucfirst($st) }}</option>@endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Marking a refund "completed" sets the order to refunded.</p>
            </div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Refund amount (optional)</label><input type="number" step="0.01" min="0" name="amount" value="{{ $return->amount }}" class="form-input w-full text-sm"></div>
        </div>
        <div><label class="block text-sm text-gray-600 mb-1.5">Admin note</label><textarea name="admin_note" rows="3" class="form-input w-full text-sm resize-none">{{ $return->admin_note }}</textarea></div>
        <button class="btn-primary">Save</button>
    </form>
</div>
@endsection
