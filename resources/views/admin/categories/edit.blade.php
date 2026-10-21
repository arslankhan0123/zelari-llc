@extends('layouts.admin')
@section('title', 'Edit: ' . $category->name)
@section('breadcrumb', 'Update category details')
@section('cat_open', 'true')

@section('content')
<div class="page-header">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.categories.index') }}" class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition text-gray-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Category</h2>
            <p class="text-sm text-gray-500 mt-0.5">Editing: <span class="text-indigo-600 font-medium">{{ $category->name }}</span></p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category permanently? Sub-categories will lose their parent.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
        </form>
        <a href="{{ route('admin.categories.index') }}" class="btn-ghost">All Categories</a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" id="editForm" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-5">
                    <label class="form-label">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="catName" value="{{ old('name', $category->name) }}"
                           class="form-input @error('name') border-red-400 @enderror"
                           placeholder="Category name"  required>
                    @error('name') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label">Slug <span class="text-gray-400 font-normal text-xs ml-1">(auto-generated from name)</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">/</span>
                        <input type="text" name="slug" id="catSlug" value="{{ old('slug', $category->slug) }}"
                               readonly disabled
                               class="form-input pl-6 bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 cursor-not-allowed select-none @error('slug') border-red-400 @enderror">
                    </div>
                    @error('slug') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label">Status</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition {{ old('status', $category->status) === 'active' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="status" value="active" {{ old('status', $category->status) === 'active' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Active</div>
                                <div class="text-xs text-gray-400">Visible in store</div>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition {{ old('status', $category->status) === 'inactive' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="status" value="inactive" {{ old('status', $category->status) === 'inactive' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Inactive</div>
                                <div class="text-xs text-gray-400">Hidden from store</div>
                            </div>
                        </label>
                    </div>
                    @error('status') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label">Parent Category <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                    @include('admin.partials.category-select', [
                        'items' => $parents,
                        'name' => 'parent_id',
                        'selected' => old('parent_id', $category->parent_id),
                        'placeholder' => '— None (Top-level) —',
                        'triggerClass' => 'form-input ' . ($errors->has('parent_id') ? 'border-red-400' : ''),
                    ])
                    @error('parent_id') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>


                <div class="mb-5" x-data="{ preview: null, current: '{{ $category->hasImage() ? $category->image_url : '' }}' }">
                    <label class="form-label">Category Image <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal text-xs ml-1">{{ $category->hasImage() ? 'choose a new file to replace the current image' : 'no image yet — please upload one' }}</span></label>
                    <label for="catImage" class="relative block w-full h-44 rounded-xl border-2 border-dashed cursor-pointer overflow-hidden transition-colors @error('image') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 hover:border-indigo-400 hover:bg-indigo-50/40 @enderror">
                        <template x-if="!preview && !current">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-sm text-gray-500">Click to upload category image</p>
                                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2MB</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="preview || current"><img :src="preview || current" class="absolute inset-0 w-full h-full object-cover"></template>
                        <span x-show="!preview && current" class="absolute bottom-2 right-2 text-[11px] font-semibold bg-black/55 text-white px-2 py-1 rounded-md">Click to replace</span>
                        <span x-show="preview" class="absolute bottom-2 right-2 text-[11px] font-semibold bg-indigo-600 text-white px-2 py-1 rounded-md">New image — saved on submit</span>
                        <input id="catImage" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden" {{ $category->hasImage() ? '' : 'required' }}
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                    <template x-if="preview">
                        <button type="button" @click="preview=null; document.getElementById('catImage').value=''" class="mt-2 text-xs text-red-500 hover:text-red-700">Keep current image</button>
                    </template>
                    @error('image')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label class="form-label">Description <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                    <textarea name="description" rows="4" class="form-input resize-none @error('description') border-red-400 @enderror"
                              placeholder="Describe this category...">{{ old('description', $category->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Changes
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>

        @if($category->children && $category->children->count())
        <div class="bg-white rounded-2xl p-6 mt-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-4">Sub-categories ({{ $category->children->count() }})</h3>
            <div class="space-y-2">
                @foreach($category->children as $child)
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">{{ strtoupper(substr($child->name,0,1)) }}</div>
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ $child->name }}</div>
                            <code class="text-xs text-gray-400">{{ $child->slug }}</code>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="{{ $child->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($child->status) }}</span>
                        <a href="{{ route('admin.categories.edit', $child) }}" class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-3">Category Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-500">ID</span><code class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">#{{ $category->id }}</code></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-500">Created</span><span class="text-gray-700 font-medium">{{ $category->created_at->format('M d, Y') }}</span></div>
                <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-500">Updated</span><span class="text-gray-700 font-medium">{{ $category->updated_at->format('M d, Y') }}</span></div>
                <div class="flex justify-between py-2"><span class="text-gray-500">Sub-categories</span><span class="text-gray-700 font-bold">{{ $category->children ? $category->children->count() : 0 }}</span></div>
            </div>
        </div>

        <div class="rounded-2xl p-5" style="background:linear-gradient(135deg,rgba(99,102,241,.06),rgba(139,92,246,.06));border:1px solid rgba(99,102,241,.15)">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="font-semibold text-indigo-700">Note</h3>
            </div>
            <ul class="space-y-2 text-xs text-indigo-600">
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Deleting this category will remove it from all sub-categories (parent set to none).</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Changing slug may break existing links to this category page.</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Setting Inactive hides this category but keeps all data intact.</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Slug auto-updates when name changes; field is read-only
function slugify(val) {
    return val.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
}
document.querySelectorAll('input[name="name"]').forEach(function(el) {
    el.addEventListener('input', function() {
        document.getElementById('catSlug').value = slugify(this.value);
    });
});
</script>
@endpush
@endsection
