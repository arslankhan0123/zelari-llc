@extends('layouts.admin')
@section('title','Shipping')
@php $base = \App\Support\Currency::base(); @endphp

@section('content')
<div class="p-6 space-y-5 max-w-5xl">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Shipping Zones &amp; Rates</h1>
        <p class="text-sm text-gray-400 mt-0.5">Group destinations into zones and set delivery rates. Rates are in {{ $base }}. A vendor's own rate (on their profile) overrides these for their items.</p>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    {{-- Add zone --}}
    <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <h2 class="font-semibold text-gray-700 mb-3">Add a zone</h2>
        <form method="POST" action="{{ route('admin.shipping.zones.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Zone name</label>
                <input type="text" name="name" class="form-input w-full text-sm" placeholder="e.g. Pakistan, GCC, Europe" required>
            </div>
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Country codes (ISO-2, comma-separated)</label>
                <input type="text" name="countries" class="form-input w-full text-sm" placeholder="PK, AE, SA">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600 pb-2"><input type="checkbox" name="is_rest" value="1"> Rest of world (catch-all)</label>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Add Zone</button>
        </form>
    </div>

    @forelse($zones as $zone)
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="px-5 py-4 border-b border-gray-100">
            <form method="POST" action="{{ route('admin.shipping.zones.update', $zone) }}" class="flex flex-wrap items-end gap-3">
                @csrf @method('PUT')
                <div class="min-w-40">
                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Zone</label>
                    <input type="text" name="name" value="{{ $zone->name }}" class="form-input text-sm font-semibold">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Countries</label>
                    <input type="text" name="countries" value="{{ implode(', ', (array) $zone->countries) }}" class="form-input w-full text-sm" {{ $zone->is_rest ? 'disabled' : '' }}>
                </div>
                <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2"><input type="checkbox" name="is_rest" value="1" {{ $zone->is_rest?'checked':'' }}> Rest of world</label>
                <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2"><input type="checkbox" name="enabled" value="1" {{ $zone->enabled?'checked':'' }}> Enabled</label>
                <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100">Save</button>
                <button form="delzone{{ $zone->id }}" class="text-sm px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">Delete</button>
            </form>
            <form id="delzone{{ $zone->id }}" method="POST" action="{{ route('admin.shipping.zones.destroy', $zone) }}" onsubmit="return confirm('Delete this zone and its methods?')">@csrf @method('DELETE')</form>
        </div>

        <div class="p-5 space-y-3">
            @foreach($zone->methods as $m)
            <form method="POST" action="{{ route('admin.shipping.methods.update', $m) }}" class="flex flex-wrap items-end gap-3 pb-3 border-b border-gray-50">
                @csrf @method('PUT')
                <div class="min-w-36"><label class="block text-[11px] text-gray-400 mb-1">Method</label><input type="text" name="name" value="{{ $m->name }}" class="form-input text-sm"></div>
                <div class="w-28"><label class="block text-[11px] text-gray-400 mb-1">Rate ({{ $base }})</label><input type="number" step="0.01" min="0" name="rate" value="{{ $m->rate }}" class="form-input text-sm w-full"></div>
                <div class="w-32"><label class="block text-[11px] text-gray-400 mb-1">Free over ({{ $base }})</label><input type="number" step="0.01" min="0" name="free_over" value="{{ $m->free_over }}" placeholder="—" class="form-input text-sm w-full"></div>
                <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2"><input type="checkbox" name="enabled" value="1" {{ $m->enabled?'checked':'' }}> Enabled</label>
                <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100">Save</button>
                <button form="delm{{ $m->id }}" class="text-sm px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">Remove</button>
            </form>
            <form id="delm{{ $m->id }}" method="POST" action="{{ route('admin.shipping.methods.destroy', $m) }}">@csrf @method('DELETE')</form>
            @endforeach

            {{-- add method --}}
            <form method="POST" action="{{ route('admin.shipping.methods.store', $zone) }}" class="flex flex-wrap items-end gap-3 pt-1">
                @csrf
                <div class="min-w-36"><label class="block text-[11px] text-gray-400 mb-1">New method</label><input type="text" name="name" placeholder="Standard, Express…" class="form-input text-sm" required></div>
                <div class="w-28"><label class="block text-[11px] text-gray-400 mb-1">Rate ({{ $base }})</label><input type="number" step="0.01" min="0" name="rate" value="0" class="form-input text-sm w-full" required></div>
                <div class="w-32"><label class="block text-[11px] text-gray-400 mb-1">Free over ({{ $base }})</label><input type="number" step="0.01" min="0" name="free_over" placeholder="—" class="form-input text-sm w-full"></div>
                <button type="submit" class="btn-primary text-sm px-4 py-2">Add Method</button>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl py-12 text-center text-sm text-gray-400" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        No zones yet. Add one above — until then, the global flat rate from Settings → Shipping applies.
    </div>
    @endforelse
</div>
@endsection
