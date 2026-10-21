@extends('layouts.admin')

@section('title', 'Manufacturers')
@section('breadcrumb', 'Brands behind your products')
@section('manu_open', 'true')

@section('content')

<!-- Page header -->
<div class="page-header">
    <div>
        <h2 class="text-xl font-bold text-gray-800">All Manufacturers</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $manufacturers->total() }} brands found</p>
    </div>
    <a href="{{ route('admin.manufacturers.create') }}" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Add Manufacturer
    </a>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.manufacturers.index') }}"
      class="bg-white rounded-2xl px-5 py-4 mb-4 flex flex-wrap items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, slug or country…" class="form-input pl-9 w-full text-sm">
    </div>

    <select name="status" class="form-input text-sm w-40">
        <option value="">All Statuses</option>
        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>

    <select name="featured" class="form-input text-sm w-40">
        <option value="">All Brands</option>
        <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Featured only</option>
        <option value="no"  {{ request('featured') === 'no'  ? 'selected' : '' }}>Not featured</option>
    </select>

    <button type="submit" class="btn-primary text-sm px-4 py-2 flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
        Filter
    </button>
    @if(request()->hasAny(['search','status','featured']))
        <a href="{{ route('admin.manufacturers.index') }}" class="text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </a>
    @endif
</form>

<!-- Table card -->
<div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    @if($manufacturers->count())
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Brand</th>
                    <th>Country</th>
                    <th>Products</th>
                    <th>Website</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="width:130px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manufacturers as $i => $m)
                <tr>
                    <td class="text-gray-400 font-mono text-xs">{{ ($manufacturers->currentPage()-1)*10 + $i + 1 }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            @if($m->hasLogo())
                                <img src="{{ $m->logo_url }}" alt="{{ $m->name }}" class="w-10 h-10 rounded-xl object-contain bg-slate-50 border border-gray-100 flex-shrink-0 p-1">
                            @else
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-xs font-bold bg-brand-grad">{{ $m->initials }}</div>
                            @endif
                            <div>
                                <div class="font-semibold text-gray-800 flex items-center gap-2">
                                    {{ $m->name }}
                                    @if($m->featured)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700">FEATURED</span>
                                    @endif
                                </div>
                                <code class="text-xs text-gray-400">{{ $m->slug }}</code>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-600">{{ $m->country ?: '—' }}</td>
                    <td>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">{{ $m->products_count }}</span>
                    </td>
                    <td class="text-sm">
                        @if($m->website)
                            <a href="{{ $m->website }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ Str::limit(preg_replace('#^https?://(www\.)?#', '', $m->website), 24) }}</a>
                        @else — @endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $m->sort_order }}</td>
                    <td>
                        <span class="{{ $m->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                            {{ ucfirst($m->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('shop.index', ['brand' => $m->slug]) }}" target="_blank" rel="noopener"
                               class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition"
                               title="View on storefront">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.manufacturers.edit', $m) }}"
                               class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center text-indigo-600 transition"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('admin.manufacturers.destroy', $m) }}" method="POST"
                                  onsubmit="return confirm('Delete \'{{ $m->name }}\'? Its products stay, but lose their brand.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition"
                                        title="Delete">
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

    <div class="px-6 py-4 border-t border-gray-100">{{ $manufacturers->withQueryString()->links() }}</div>

    @else
    <div class="text-center py-16 px-6">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h3 class="font-semibold text-gray-700 mb-1">No manufacturers yet</h3>
        <p class="text-sm text-gray-400 mb-5">Add the brands you stock — they show up on the storefront's Brands page.</p>
        <a href="{{ route('admin.manufacturers.create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add your first brand
        </a>
    </div>
    @endif
</div>

@endsection
