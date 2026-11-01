@extends('layouts.admin')
@section('title','Loyalty & Referrals')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Loyalty &amp; Referrals</h1><p class="text-sm text-gray-400 mt-0.5">Reward customers with points on every purchase.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('admin.loyalty.update') }}" class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf @method('PUT')
        <label class="flex items-center gap-3"><input type="checkbox" name="enabled" value="1" {{ $settings['enabled']?'checked':'' }} class="w-4 h-4"><span class="text-sm font-medium text-gray-700">Enable loyalty points</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-600 mb-1.5">Points earned per 1 {{ \App\Support\Currency::base() }} spent</label><input type="number" step="0.01" min="0" name="earn_rate" value="{{ $settings['earn_rate'] }}" class="form-input w-full text-sm" required></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Referral reward (points)</label><input type="number" min="0" name="referral" value="{{ $settings['referral'] }}" class="form-input w-full text-sm" required></div>
        </div>
        <button class="btn-primary">Save</button>
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Top members</h3></div>
        @if($customers->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Customer</th><th>Email</th><th class="text-right">Points</th></tr></thead>
            <tbody>@foreach($customers as $c)<tr><td class="text-sm text-gray-800">{{ $c->name }}</td><td class="text-sm text-gray-500">{{ $c->email }}</td><td class="text-right font-semibold text-indigo-600">{{ number_format($c->points_balance) }}</td></tr>@endforeach</tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No points awarded yet.</p>@endif
    </div>
</div>
@endsection
