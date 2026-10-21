@extends('layouts.admin')

@section('title', 'Reviews')
@section('breadcrumb', 'Approve what customers see on your product pages')

@section('content')

<!-- Page header -->
<div class="page-header">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Product Reviews</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $reviews->total() }} reviews found · only approved reviews are visible on the website</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach([
        ['Pending',  $stats['pending'],  'amber',   'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Approved', $stats['approved'], 'green',   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Rejected', $stats['rejected'], 'red',     'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Avg. rating', $stats['average'] ?: '—',  'indigo', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ] as [$label, $value, $tone, $icon])
    <div class="bg-white rounded-2xl p-4 flex items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-{{ $tone }}-50 text-{{ $tone }}-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800 leading-none">{{ $value }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.reviews.index') }}"
      class="bg-white rounded-2xl px-5 py-4 mb-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product, customer or text…" class="form-input pl-9 w-full text-sm">
    </div>
    <select name="status" class="form-input text-sm w-40">
        <option value="">All Statuses</option>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $v)
            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
    </select>
    <select name="rating" class="form-input text-sm w-36">
        <option value="">All Ratings</option>
        @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" {{ (string) request('rating') === (string) $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
        @endfor
    </select>
    <button type="submit" class="btn-primary text-sm px-4 py-2 flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
        Filter
    </button>
    @if(request()->hasAny(['search','status','rating']))
        <a href="{{ route('admin.reviews.index') }}" class="text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </a>
    @endif
</form>

<!-- List -->
<div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    @if($reviews->count())
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:210px">Customer</th>
                    <th style="width:90px">Rating</th>
                    <th>Review</th>
                    <th style="width:150px">Product</th>
                    <th style="width:120px">Order</th>
                    <th style="width:100px">Status</th>
                    <th style="width:150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $r)
                <tr>
                    {{-- Customer --}}
                    <td>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-brand-grad text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($r->author_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-800 text-sm truncate flex items-center gap-1.5">
                                    {{ $r->author_name }}
                                    @if($r->verified_purchase)
                                        <i class="fa-solid fa-circle-check text-green-500 text-[11px]" title="Verified purchase"></i>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 truncate" title="{{ $r->author_email }}">{{ $r->author_email }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Rating --}}
                    <td>
                        <div class="text-amber-400 text-xs whitespace-nowrap">
                            @for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i > $r->rating ? 'text-gray-200' : '' }}"></i>@endfor
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1">{{ $r->created_at->format('d M Y') }}</div>
                    </td>

                    {{-- Review text --}}
                    <td>
                        @if($r->title)<p class="font-semibold text-gray-800 text-sm">{{ $r->title }}</p>@endif
                        <p class="text-sm text-gray-600 leading-snug">{{ Str::limit($r->body, 150) }}</p>
                        @if(mb_strlen($r->body) > 150)
                            <details class="mt-1">
                                <summary class="text-xs text-indigo-600 cursor-pointer hover:underline">Read full review</summary>
                                <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $r->body }}</p>
                            </details>
                        @endif
                        @if($r->admin_note)<p class="text-xs text-gray-400 mt-1"><b>Note:</b> {{ $r->admin_note }}</p>@endif
                    </td>

                    {{-- Product --}}
                    <td>
                        @if($r->product)
                            <a href="{{ route('admin.products.edit', $r->product) }}" class="text-sm text-gray-700 hover:text-indigo-600">{{ Str::limit($r->product->name, 22) }}</a>
                        @else
                            <span class="text-xs text-gray-400">deleted</span>
                        @endif
                    </td>

                    {{-- Order --}}
                    <td>
                        @if($r->order)
                            <a href="{{ route('admin.orders.show', $r->order) }}" class="font-mono text-xs text-indigo-600 hover:underline">{{ $r->order->order_number }}</a>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap {{ ['pending' => 'text-amber-600 bg-amber-50', 'approved' => 'text-green-600 bg-green-50', 'rejected' => 'text-red-500 bg-red-50'][$r->status] }}">
                            {{ ucfirst($r->status) }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex items-center gap-2">
                            @if($r->status !== 'approved')
                            <form method="POST" action="{{ route('admin.reviews.approve', $r) }}">
                                @csrf
                                <button class="w-8 h-8 rounded-lg bg-green-50 hover:bg-green-100 flex items-center justify-center text-green-600 transition" title="Approve &amp; publish">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.reviews.unpublish', $r) }}">
                                @csrf
                                <button class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition" title="Unpublish (back to pending)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029M9.88 9.88a3 3 0 104.24 4.24M3 3l18 18"/></svg>
                                </button>
                            </form>
                            @endif

                            @if($r->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.reviews.reject', $r) }}" onsubmit="return confirm('Reject this review? It stays hidden from the website.')">
                                @csrf
                                <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition" title="Reject">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            @endif

                            @if($r->product)
                            <a href="{{ route('shop.show', $r->product->slug) }}#reviews" target="_blank" rel="noopener"
                               class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition" title="View on site">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif

                            <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" onsubmit="return confirm('Delete this review permanently?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-red-50 flex items-center justify-center text-gray-400 hover:text-red-500 transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($reviews->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $reviews->withQueryString()->links() }}</div>
    @endif

    @else
    <div class="text-center py-16 px-6">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <h3 class="font-semibold text-gray-700 mb-1">No reviews{{ request()->hasAny(['search','status','rating']) ? ' match these filters' : ' yet' }}</h3>
        <p class="text-sm text-gray-400">Customers can review a product once they have ordered it. New reviews land here for approval.</p>
    </div>
    @endif
</div>

@endsection
