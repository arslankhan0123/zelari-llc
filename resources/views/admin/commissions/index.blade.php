@extends('layouts.admin')
@section('title','Commission Rules')

@section('content')
<div class="p-6 space-y-5 max-w-5xl">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Commission Rules</h1>
        <p class="text-sm text-gray-400 mt-0.5">How much the platform keeps on each sale. Precedence: category override → vendor rate → global default.</p>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.commissions.update') }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- Global default --}}
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h2 class="font-semibold text-gray-700 mb-1">Global default rate</h2>
            <p class="text-sm text-gray-400 mb-4">Applied when a product's category and its vendor have no specific rate.</p>
            <div class="flex items-center gap-2 max-w-xs">
                <input type="number" step="0.01" min="0" max="100" name="default_rate" value="{{ old('default_rate', $default) }}" class="form-input w-full text-sm" required>
                <span class="text-gray-500 font-medium">%</span>
            </div>
        </div>

        {{-- Per-category overrides --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Per-category overrides</h2>
                <p class="text-sm text-gray-400 mt-0.5">Leave blank to use the global default. A value here overrides the vendor's own rate.</p>
            </div>
            @if($categories->count())
            <div class="table-scroll"><table class="data-table">
                <thead><tr><th>Category</th><th class="w-48">Rate (%)</th></tr></thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="text-sm font-medium text-gray-800">{{ $cat->name }}</td>
                        <td>
                            <div class="flex items-center gap-2 max-w-[10rem]">
                                <input type="number" step="0.01" min="0" max="100" name="category_rates[{{ $cat->id }}]"
                                       value="{{ old('category_rates.'.$cat->id, $cat->commission_rate) }}"
                                       placeholder="Default" class="form-input w-full text-sm">
                                <span class="text-gray-400 text-sm">%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @else
            <p class="px-6 py-8 text-sm text-gray-400 text-center">No categories yet.</p>
            @endif
        </div>

        <div><button type="submit" class="btn-primary">Save Commission Rules</button></div>
    </form>

    {{-- Per-vendor rates (managed on each vendor's profile) --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">Per-vendor rates</h2>
            <p class="text-sm text-gray-400 mt-0.5">Set on each vendor's profile. Used when a product's category has no override.</p>
        </div>
        @if($vendors->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Vendor</th><th>Rate (%)</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($vendors as $v)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $v->name }}</td>
                    <td class="text-sm text-gray-600">{{ $v->commission_rate !== null ? rtrim(rtrim(number_format($v->commission_rate,2),'0'),'.').'%' : 'Default' }}</td>
                    <td>
                        <div class="flex items-center justify-center">
                            @can('vendors.view')
                            <a href="{{ route('admin.vendors.show',$v) }}" class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center text-indigo-600 transition" title="Open vendor">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <p class="px-6 py-8 text-sm text-gray-400 text-center">No vendors yet.</p>
        @endif
    </div>
</div>
@endsection
