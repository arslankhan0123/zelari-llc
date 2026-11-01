@extends('layouts.admin')
@section('title','Inventory')
@section('content')
<div class="p-6 space-y-5">
    <div><h1 class="text-2xl font-bold text-gray-800">Inventory</h1><p class="text-sm text-gray-400 mt-0.5">Low-stock watch and manual stock adjustments.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <h2 class="font-semibold text-gray-700 mb-3">Adjust stock</h2>
        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            @csrf
            <div class="lg:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Product</label>
                <select name="product_id" class="form-input w-full text-sm" required>
                    @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} (stock {{ $p->quantity }})</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Change (+/-)</label><input type="number" name="change" value="0" class="form-input w-full text-sm" required></div>
            <div><label class="block text-xs text-gray-500 mb-1">Type</label><select name="type" class="form-input w-full text-sm"><option value="restock">Restock</option><option value="adjust">Adjust</option></select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Reason</label><input name="reason" placeholder="e.g. new shipment" class="form-input w-full text-sm"></div>
            <div class="lg:col-span-5"><button class="btn-primary text-sm">Apply Adjustment</button></div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Low stock (≤ {{ $threshold }})</h3></div>
            @if($lowStock->count())
            <div class="table-scroll"><table class="data-table">
                <thead><tr><th>Product</th><th>Vendor</th><th class="text-right">Qty</th></tr></thead>
                <tbody>
                    @foreach($lowStock as $p)
                    <tr><td class="text-sm font-medium text-gray-800">{{ $p->name }}</td><td class="text-sm text-gray-500">{{ optional($p->vendor)->name ?? '—' }}</td><td class="text-right"><span class="badge-{{ $p->quantity<=0?'red':'yellow' }}">{{ $p->quantity }}</span></td></tr>
                    @endforeach
                </tbody>
            </table></div>
            @if($lowStock->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $lowStock->links() }}</div>@endif
            @else<p class="py-10 text-center text-sm text-gray-400">Nothing low on stock. 🎉</p>@endif
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Recent movements</h3></div>
            @if($movements->count())
            <div class="table-scroll"><table class="data-table">
                <thead><tr><th>Product</th><th>Change</th><th>Balance</th><th>Type</th><th>By</th><th>When</th></tr></thead>
                <tbody>
                    @foreach($movements as $m)
                    <tr>
                        <td class="text-sm text-gray-800">{{ optional($m->product)->name ?? '—' }}</td>
                        <td class="text-sm font-semibold {{ $m->change>=0?'text-emerald-600':'text-red-500' }}">{{ $m->change>=0?'+':'' }}{{ $m->change }}</td>
                        <td class="text-sm text-gray-600">{{ $m->balance }}</td>
                        <td class="text-sm text-gray-500">{{ ucfirst($m->type) }}@if($m->reason)<span class="text-gray-300"> · {{ $m->reason }}</span>@endif</td>
                        <td class="text-sm text-gray-500">{{ optional($m->user)->name ?? 'system' }}</td>
                        <td class="text-sm text-gray-400">{{ $m->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @else<p class="py-10 text-center text-sm text-gray-400">No movements yet.</p>@endif
        </div>
    </div>
</div>
@endsection
