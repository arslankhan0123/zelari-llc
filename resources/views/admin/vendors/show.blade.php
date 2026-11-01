@extends('layouts.admin')
@section('title','Vendor · '.$vendor->name)

@section('content')
<div class="p-6 space-y-5 max-w-5xl">

    <a href="{{ route('admin.vendors.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Back to vendors
    </a>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>@endif

    {{-- Header card --}}
    <div class="bg-white rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center gap-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-2xl flex-shrink-0">{{ strtoupper(substr($vendor->name,0,1)) }}</div>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800">{{ $vendor->name }}</h1>
                <span class="badge-{{ $vendor->status_color }}">{{ ucfirst($vendor->status) }}</span>
            </div>
            <p class="text-sm text-gray-400 mt-0.5 font-mono">/{{ $vendor->slug }} · {{ $vendor->products_count }} products · joined {{ $vendor->created_at->format('d M Y') }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $vendor->email ?: '—' }} {{ $vendor->phone ? '· '.$vendor->phone : '' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($vendor->status !== 'approved')
            <form method="POST" action="{{ route('admin.vendors.approve',$vendor) }}">@csrf<button class="btn-primary text-sm">Approve</button></form>
            @endif
            @if($vendor->status === 'approved' && $vendor->slug !== 'zelari')
            <form method="POST" action="{{ route('admin.vendors.suspend',$vendor) }}">@csrf<button class="text-sm px-4 py-2 rounded-lg bg-amber-100 text-amber-700 font-semibold hover:bg-amber-200">Suspend</button></form>
            @endif
            @if($vendor->status === 'suspended')
            <form method="POST" action="{{ route('admin.vendors.reactivate',$vendor) }}">@csrf<button class="text-sm px-4 py-2 rounded-lg bg-emerald-100 text-emerald-700 font-semibold hover:bg-emerald-200">Reactivate</button></form>
            @endif
            @if($vendor->status === 'pending')
            <form method="POST" action="{{ route('admin.vendors.reject',$vendor) }}">@csrf<button class="text-sm px-4 py-2 rounded-lg bg-red-100 text-red-700 font-semibold hover:bg-red-200">Reject</button></form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Settings form --}}
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-bold text-gray-800">Commission &amp; notes</h3>
            <form method="POST" action="{{ route('admin.vendors.update',$vendor) }}" class="space-y-4">@csrf @method('PUT')
                <div>
                    <label class="text-sm font-medium text-gray-600">Commission rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate',$vendor->commission_rate) }}" class="form-input w-full mt-1 text-sm">
                    <p class="text-[11px] text-gray-400 mt-1">Platform's cut on each sale by this vendor.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Shipping flat ({{ \App\Support\Currency::base() }})</label>
                        <input type="number" step="0.01" min="0" name="shipping_flat" value="{{ old('shipping_flat',$vendor->shipping_flat) }}" placeholder="Default" class="form-input w-full mt-1 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Free over ({{ \App\Support\Currency::base() }})</label>
                        <input type="number" step="0.01" min="0" name="shipping_free_over" value="{{ old('shipping_free_over',$vendor->shipping_free_over) }}" placeholder="—" class="form-input w-full mt-1 text-sm">
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 -mt-2">Per-vendor delivery rate for this vendor's items. Leave flat blank to use shipping zones / global rate.</p>
                <div>
                    <label class="text-sm font-medium text-gray-600">Internal notes</label>
                    <textarea name="admin_notes" rows="4" class="form-input w-full mt-1 text-sm" placeholder="Private notes…">{{ old('admin_notes',$vendor->admin_notes) }}</textarea>
                </div>
                <button class="btn-primary text-sm w-full">Save</button>
            </form>
            @if($vendor->description)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-600 mb-1">About</p>
                <p class="text-sm text-gray-500">{{ $vendor->description }}</p>
            </div>
            @endif
        </div>

        {{-- Products --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Products</h3>
                <span class="text-sm text-gray-400">{{ $vendor->products_count }} total</span>
            </div>
            @if($products->count())
            <div class="divide-y divide-gray-100">
                @foreach($products as $p)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
                        @if($p->feature_image)<img src="{{ asset('storage/'.$p->feature_image) }}" class="w-full h-full object-cover" alt="">@endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ $p->name }}</div>
                        <div class="text-[11px] text-gray-400">{{ $p->sku ?: '—' }}</div>
                    </div>
                    <span class="badge-{{ $p->status === 'active' ? 'green' : 'draft' }}">{{ ucfirst($p->status) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-gray-400 py-10 text-sm">No products from this vendor yet.</p>
            @endif
        </div>
    </div>

</div>
@endsection
