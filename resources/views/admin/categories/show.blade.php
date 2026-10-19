@extends('layouts.admin')
@section('cat_open', 'true')
@section('title', 'View Category')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.categories.index') }}"
           class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-gray-500 hover:text-gray-700 transition"
           style="box-shadow:0 2px 8px rgba(0,0,0,.08)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ $category->name }}</h1>
            <p class="text-sm text-gray-400">Category details</p>
        </div>
    </div>
    <a href="{{ route('admin.categories.edit', $category) }}"
       class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit Category
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Basic Details -->
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Basic Information</h3>
            <dl class="space-y-4">
                <div class="flex gap-4">
                    <dt class="w-36 text-sm text-gray-400 shrink-0">Name</dt>
                    <dd class="text-sm font-semibold text-gray-800">{{ $category->name }}</dd>
                </div>
                <div class="flex gap-4">
                    <dt class="w-36 text-sm text-gray-400 shrink-0">Slug</dt>
                    <dd>
                        <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg font-mono">{{ $category->slug }}</code>
                    </dd>
                </div>
                <div class="flex gap-4">
                    <dt class="w-36 text-sm text-gray-400 shrink-0">Parent</dt>
                    <dd class="text-sm text-gray-700">
                        @if($category->parent)
                            <a href="{{ route('admin.categories.show', $category->parent) }}"
                               class="text-indigo-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                {{ $category->parent->name }}
                            </a>
                        @else
                            <span class="text-gray-400">— Top-level category</span>
                        @endif
                    </dd>
                </div>
                <div class="flex gap-4">
                    <dt class="w-36 text-sm text-gray-400 shrink-0">Image</dt>
                    <dd class="text-sm text-gray-700">
                        @if($category->hasImage())
                            <a href="{{ $category->image_url }}" target="_blank" rel="noopener"><img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-40 h-24 object-cover rounded-xl border border-gray-100"></a>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full"><i class="fa-solid fa-triangle-exclamation"></i> No image — storefront shows a stock photo</span>
                        @endif
                    </dd>
                </div>
                <div class="flex gap-4">
                    <dt class="w-36 text-sm text-gray-400 shrink-0">Description</dt>
                    <dd class="text-sm text-gray-700">
                        {{ $category->description ?: '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Subcategories -->
        @if($category->children->count())
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Subcategories
                <span class="ml-2 text-xs font-normal bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">{{ $category->children->count() }}</span>
            </h3>
            <div class="space-y-2">
                @foreach($category->children as $child)
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                             style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            {{ strtoupper(substr($child->name,0,1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ $child->name }}</div>
                            <code class="text-xs text-gray-400 font-mono">{{ $child->slug }}</code>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $child->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst($child->status) }}
                        </span>
                        <a href="{{ route('admin.categories.show', $child) }}"
                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </div>
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
            @if($category->status === 'active')
                <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 border-2 border-emerald-200">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <div class="text-sm font-semibold text-emerald-700">Active</div>
                        <div class="text-xs text-emerald-500">Visible in the storefront</div>
                    </div>
                </div>
            @elseif($category->status === 'inactive')
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border-2 border-gray-200">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <div class="text-sm font-semibold text-gray-600">Inactive</div>
                        <div class="text-xs text-gray-400">Hidden from customers</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Meta -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-400">Category ID</dt>
                    <dd class="text-sm font-mono text-gray-600">#{{ $category->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Created</dt>
                    <dd class="text-sm text-gray-600">{{ $category->created_at->format('M d, Y · h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Last Updated</dt>
                    <dd class="text-sm text-gray-600">{{ $category->updated_at->format('M d, Y · h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Subcategories</dt>
                    <dd class="text-sm text-gray-600">{{ $category->children->count() }}</dd>
                </div>
            </dl>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
            <a href="{{ route('admin.categories.edit', $category) }}"
               class="btn-primary w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Category
            </a>
            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                  onsubmit="return confirm('Delete \'{{ $category->name }}\'? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-red-200 text-red-500 hover:bg-red-50 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Category
                </button>
            </form>
            <a href="{{ route('admin.categories.index') }}"
               class="btn-ghost w-full flex items-center justify-center gap-2">
                All Categories
            </a>
        </div>

    </div>
</div>
@endsection
