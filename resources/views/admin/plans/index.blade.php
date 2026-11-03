@extends('layouts.admin')
@section('title','Subscription Plans')
@php $base = \App\Support\Currency::base(); @endphp
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Subscription Plans</h1><p class="text-sm text-gray-400 mt-0.5">Vendor plans with product listing limits and optional commission override.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.plans.store') }}" class="bg-white rounded-2xl p-5 flex flex-wrap items-end gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf
        <div><label class="block text-xs text-gray-500 mb-1">Name</label><input name="name" class="form-input text-sm" required></div>
        <div><label class="block text-xs text-gray-500 mb-1">Price ({{ $base }})</label><input type="number" step="0.01" min="0" name="price" value="0" class="form-input text-sm w-28" required></div>
        <div><label class="block text-xs text-gray-500 mb-1">Product limit</label><input type="number" min="0" name="product_limit" placeholder="∞" class="form-input text-sm w-28"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Commission % (opt)</label><input type="number" step="0.01" min="0" max="100" name="commission_rate" placeholder="—" class="form-input text-sm w-28"></div>
        <button class="btn-primary text-sm">Add Plan</button>
    </form>

    @forelse($plans as $plan)
    <form method="POST" action="{{ route('admin.plans.update',$plan) }}" class="bg-white rounded-2xl p-5 flex flex-wrap items-end gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf @method('PUT')
        <div><label class="block text-xs text-gray-500 mb-1">Name</label><input name="name" value="{{ $plan->name }}" class="form-input text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Price ({{ $base }})</label><input type="number" step="0.01" min="0" name="price" value="{{ $plan->price }}" class="form-input text-sm w-28"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Product limit</label><input type="number" min="0" name="product_limit" value="{{ $plan->product_limit }}" placeholder="∞" class="form-input text-sm w-28"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Commission %</label><input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ $plan->commission_rate }}" placeholder="—" class="form-input text-sm w-28"></div>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2"><input type="checkbox" name="enabled" value="1" {{ $plan->enabled?'checked':'' }}> Enabled</label>
        <span class="text-xs text-gray-400 pb-2">{{ $plan->vendors_count }} vendor(s)</span>
        <button class="text-sm px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100">Save</button>
        <button form="delplan{{ $plan->id }}" class="text-sm px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">Delete</button>
    </form>
    <form id="delplan{{ $plan->id }}" method="POST" action="{{ route('admin.plans.destroy',$plan) }}" onsubmit="return confirm('Delete plan?')">@csrf @method('DELETE')</form>
    @empty
    <p class="text-sm text-gray-400">No plans yet. Add one above (leave product limit blank for unlimited).</p>
    @endforelse
</div>
@endsection
