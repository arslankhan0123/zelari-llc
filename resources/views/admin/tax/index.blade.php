@extends('layouts.admin')
@section('title','Tax')

@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tax Settings</h1>
        <p class="text-sm text-gray-400 mt-0.5">VAT / GST / sales tax applied at checkout. Leave disabled to charge no tax.</p>
    </div>

    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.tax.update') }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl p-6 space-y-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="enabled" value="1" {{ $tax['enabled']?'checked':'' }} class="w-4 h-4">
                <span class="text-sm font-medium text-gray-700">Charge tax at checkout</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Label</label>
                    <input type="text" name="label" value="{{ old('label', $tax['label']) }}" class="form-input w-full text-sm" placeholder="VAT / GST / Tax" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="rate" value="{{ old('rate', $tax['rate']) }}" class="form-input w-full text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Price mode</label>
                    <select name="inclusive" class="form-input w-full text-sm">
                        <option value="0" {{ !$tax['inclusive']?'selected':'' }}>Exclusive (added on top)</option>
                        <option value="1" {{ $tax['inclusive']?'selected':'' }}>Inclusive (already in prices)</option>
                    </select>
                </div>
            </div>
            <p class="text-[12px] text-gray-400">Exclusive adds the tax to the order total. Inclusive treats product prices as tax-inclusive and only shows the tax portion.</p>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Tax-exempt categories</h2>
                <p class="text-sm text-gray-400 mt-0.5">Items in a checked category are left out of the taxable amount.</p>
            </div>
            @if($categories->count())
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($categories as $cat)
                <label class="flex items-center gap-2 text-sm text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="exempt[{{ $cat->id }}]" value="1" {{ $cat->tax_exempt?'checked':'' }}>
                    {{ $cat->name }}
                </label>
                @endforeach
            </div>
            @else
            <p class="px-6 py-8 text-sm text-gray-400 text-center">No categories yet.</p>
            @endif
        </div>

        <div><button type="submit" class="btn-primary">Save Tax Settings</button></div>
    </form>
</div>
@endsection
