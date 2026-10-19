@extends('layouts.admin')
@section('prod_open', 'true')
@section('title', 'View Product')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.index') }}"
           class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-gray-500 hover:text-gray-700 transition"
           style="box-shadow:0 2px 8px rgba(0,0,0,.08)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ $product->name }}</h1>
            <p class="text-sm text-gray-400">Product details</p>
        </div>
    </div>
    <a href="{{ route('admin.products.edit', $product) }}"
       class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit Product
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Feature Image + Basic Info -->
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex gap-5">
                <!-- Feature Image -->
                <div class="shrink-0">
                    @if($product->feature_image)
                        <img src="{{ Storage::url($product->feature_image) }}"
                             alt="{{ $product->name }}"
                             class="w-28 h-28 rounded-xl object-cover border border-gray-100">
                    @else
                        <div class="w-28 h-28 rounded-xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">{{ $product->name }}</h2>
                    <code class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-mono">{{ $product->slug }}</code>
                    @if($product->short_description)
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $product->short_description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Product Details</h3>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">SKU</dt>
                    <dd class="text-sm font-mono text-gray-700">{{ $product->sku ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Category</dt>
                    <dd class="text-sm text-gray-700">{{ $product->category?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Purchase Price</dt>
                    <dd class="text-sm font-semibold text-gray-700">{{ config('currencies.base') }} {{ number_format($product->purchase_price, 0) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Sale Price</dt>
                    <dd class="text-sm font-semibold text-indigo-600">
                        {{ $product->sale_price ? config('currencies.base').' '.number_format($product->sale_price, 0) : '—' }}
                        @if($product->sale_price && $product->purchase_price > $product->sale_price)
                            <span class="ml-1 text-xs bg-red-50 text-red-500 px-1.5 py-0.5 rounded-full font-normal">
                                -{{ round((($product->purchase_price - $product->sale_price) / $product->purchase_price) * 100) }}%
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Quantity</dt>
                    <dd class="text-sm font-semibold {{ $product->quantity < 5 ? 'text-red-500' : 'text-gray-700' }}">
                        {{ $product->quantity }}
                        @if($product->quantity < 5)
                            <span class="ml-1 text-xs font-normal">⚠ Low stock</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Gallery Images</dt>
                    <dd class="text-sm text-gray-700">{{ $product->images->count() }} image(s)</dd>
                </div>
            </dl>
        </div>

        <!-- Long Description -->
        @if($product->description)
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Description</h3>
            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                {!! $product->description !!}
            </div>
        </div>
        @endif

        <!-- Gallery -->
        @if($product->images->count())
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Gallery
                <span class="ml-2 text-xs font-normal bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">{{ $product->images->count() }}</span>
            </h3>
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                @foreach($product->images as $img)
                <div class="aspect-square rounded-xl overflow-hidden border border-gray-100">
                    <img src="{{ Storage::url($img->image_path) }}"
                         alt="Gallery image"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- Sidebar -->
    <div class="space-y-5">

        <!-- Status -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Status</h3>
            @php
                $statusMap = [
                    'active'   => ['bg-emerald-50','border-emerald-200','text-emerald-700','text-emerald-500','Active','Visible on storefront'],
                    'inactive' => ['bg-gray-50','border-gray-200','text-gray-600','text-gray-400','Inactive','Hidden from customers'],
                    'draft'    => ['bg-amber-50','border-amber-200','text-amber-700','text-amber-500','Draft','Saved but not visible'],
                ];
                $s = $statusMap[$product->status] ?? $statusMap['draft'];
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl border-2 {{ $s[0] }} {{ $s[1] }}">
                <div class="w-2.5 h-2.5 rounded-full {{ str_replace('text-','bg-',$s[3]) }}"></div>
                <div>
                    <div class="text-sm font-semibold {{ $s[2] }}">{{ $s[4] }}</div>
                    <div class="text-xs {{ $s[3] }}">{{ $s[5] }}</div>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-400">Product ID</dt>
                    <dd class="text-sm font-mono text-gray-600">#{{ $product->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Created</dt>
                    <dd class="text-sm text-gray-600">{{ $product->created_at->format('M d, Y · h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Last Updated</dt>
                    <dd class="text-sm text-gray-600">{{ $product->updated_at->format('M d, Y · h:i A') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
            <a href="{{ route('admin.products.edit', $product) }}"
               class="btn-primary w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Product
            </a>
            <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                  onsubmit="return confirm('Delete &quot;{{ $product->name }}&quot;? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-red-200 text-red-500 hover:bg-red-50 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Product
                </button>
            </form>
            <a href="{{ route('admin.products.index') }}"
               class="btn-ghost w-full flex items-center justify-center gap-2">
                All Products
            </a>
        </div>

    </div>
</div>
@endsection
