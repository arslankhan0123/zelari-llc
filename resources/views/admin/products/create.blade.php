@extends('layouts.admin')

@section('title', 'Add Product')
@section('prod_open', 'true')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.products.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Product</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Create a new product listing</p>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">
        <p class="font-semibold mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="productForm">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- ── LEFT COLUMN (main fields) ── -->
            <div class="xl:col-span-2 space-y-6">

                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Basic Information</h2>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               placeholder="e.g. Wireless Bluetooth Headphones"
                               class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Slug
                            <span class="font-normal text-gray-400 text-xs ml-1">(auto-generated from name)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none">/</span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                                   readonly disabled
                                   placeholder="will be generated from name…"
                                   class="w-full pl-8 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 placeholder-gray-400 font-mono text-sm cursor-not-allowed select-none @error('slug') border-red-500 @enderror">
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Auto-generated from product name — unique and URL-safe.</p>
                        @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. WBH-2024-BLK"
                               class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sku') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-400">Stock Keeping Unit — unique identifier for inventory</p>
                        @error('sku')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
                        <select name="category_id" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Pricing & Stock -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Pricing & Stock</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Purchase Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-xs">{{ config('currencies.base') }}</span>
                                <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" step="0.01" min="0" required placeholder="0.00"
                                       class="w-full pl-12 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('purchase_price') border-red-500 @enderror">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Cost / buying price</p>
                            @error('purchase_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sale Price</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-xs">{{ config('currencies.base') }}</span>
                                <input type="number" name="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0" placeholder="0.00"
                                       class="w-full pl-12 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sale_price') border-red-500 @enderror">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Discounted selling price</p>
                            @error('sale_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" required placeholder="0"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-400">Stock available</p>
                            @error('quantity')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Descriptions</h2>

                    <!-- Short Description -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Short Description</label>
                            <span id="shortDescCount" class="text-xs text-gray-400">0 / 500</span>
                        </div>
                        <textarea name="short_description" id="shortDesc" rows="3" maxlength="500"
                                  placeholder="Brief summary shown in product listings…"
                                  class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('short_description') border-red-500 @enderror">{{ old('short_description') }}</textarea>
                        @error('short_description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Long Description (Quill) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Long Description</label>
                        <!-- Quill toolbar + editor -->
                        <div class="border border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden">
                            <div id="quillToolbar" class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-300 dark:border-gray-600">
                                <span class="ql-formats">
                                    <select class="ql-header"><option selected></option><option value="1">H1</option><option value="2">H2</option><option value="3">H3</option></select>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-bold"></button>
                                    <button class="ql-italic"></button>
                                    <button class="ql-underline"></button>
                                    <button class="ql-strike"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered"></button>
                                    <button class="ql-list" value="bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-link"></button>
                                    <button class="ql-blockquote"></button>
                                    <button class="ql-code-block"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-clean"></button>
                                </span>
                            </div>
                            <div id="quillEditor" class="bg-white dark:bg-gray-700" style="min-height:220px;max-height:500px;overflow-y:auto;"></div>
                        </div>
                        <input type="hidden" name="description" id="descriptionInput">
                        @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Feature Image -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Feature Image</h2>
                    <div x-data="{ preview: null }">
                        <label for="featureImage"
                               class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors relative overflow-hidden">
                            <template x-if="!preview">
                                <div class="text-center">
                                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click to upload feature image</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">JPG, PNG, WebP — max 2MB</p>
                                </div>
                            </template>
                            <template x-if="preview">
                                <img :src="preview" class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            <input id="featureImage" type="file" name="feature_image" accept="image/*" class="hidden"
                                   @change="preview = URL.createObjectURL($event.target.files[0])">
                        </label>
                        <template x-if="preview">
                            <button type="button" @click="preview=null; document.getElementById('featureImage').value=''"
                                    class="mt-2 text-xs text-red-500 hover:text-red-700 transition-colors">Remove image</button>
                        </template>
                        @error('feature_image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Gallery Images -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4" x-data="galleryUploader()">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white text-base">Gallery Images</h2>
                        <span class="text-xs text-gray-400" x-text="images.length + ' image(s) selected'"></span>
                    </div>

                    <!-- Drop Zone -->
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Click to add gallery images</p>
                        <p class="text-xs text-gray-400 mt-0.5">Multiple files allowed — JPG, PNG, WebP — max 2MB each</p>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple class="hidden" @change="addImages($event)">
                    </label>

                    <!-- Preview Grid -->
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3" x-show="images.length > 0">
                        <template x-for="(img, i) in images" :key="i">
                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                <img :src="img.url" class="w-full h-full object-cover">
                                <button type="button" @click="removeImage(i)"
                                        class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                    @error('gallery_images.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- ── RIGHT COLUMN (sidebar) ── -->
            <div class="space-y-6">

                <!-- Status -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Status</h2>
                    @foreach(['draft' => ['Draft', 'yellow', 'Saved but not visible'], 'active' => ['Active', 'green', 'Visible on storefront'], 'inactive' => ['Inactive', 'red', 'Hidden from storefront']] as $val => $info)
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors
                        {{ old('status', 'draft') === $val ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="radio" name="status" value="{{ $val }}" {{ old('status', 'draft') === $val ? 'checked' : '' }}
                               class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $info[0] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $info[2] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                    <button type="submit" class="btn-primary w-full">
                        Create Product
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="block w-full py-2.5 px-4 text-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </a>
                </div>

                <!-- Tips -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-5 space-y-2">
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tips
                    </h3>
                    <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1.5">
                        <li>• Product name auto-generates the slug</li>
                        <li>• Leave Sale Price blank to show Purchase Price</li>
                        <li>• Gallery supports multiple images at once</li>
                        <li>• Use Draft status to save without publishing</li>
                        <li>• SKU helps track inventory uniquely</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- Quill CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<!-- Quill JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
// ── Auto slug (read-only, driven by name) ──
function slugify(str) {
    return str.toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
document.getElementById('name').addEventListener('input', function() {
    document.getElementById('slug').value = slugify(this.value);
});

// ── Short description counter ──
const shortDesc = document.getElementById('shortDesc');
const counter   = document.getElementById('shortDescCount');
function updateCount() { counter.textContent = shortDesc.value.length + ' / 500'; }
shortDesc.addEventListener('input', updateCount);
updateCount();

// ── Quill rich text editor ──
const quill = new Quill('#quillEditor', {
    theme: 'snow',
    modules: { toolbar: '#quillToolbar' },
    placeholder: 'Write a detailed product description…',
});

// Restore old value on validation failure
const oldDesc = @json(old('description', ''));
if (oldDesc) { quill.clipboard.dangerouslyPasteHTML(oldDesc); }

// Sync to hidden input before submit
document.getElementById('productForm').addEventListener('submit', function() {
    document.getElementById('descriptionInput').value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
});

// ── Dark mode Quill styles ──
if (document.documentElement.classList.contains('dark')) {
    document.querySelector('.ql-toolbar')?.classList.add('ql-dark');
}

// ── Gallery uploader (Alpine component) ──
function galleryUploader() {
    return {
        images: [],
        files: [],
        addImages(event) {
            const newFiles = Array.from(event.target.files);
            newFiles.forEach(file => {
                this.images.push({ url: URL.createObjectURL(file), file });
                this.files.push(file);
            });
            this.syncInput();
            event.target.value = '';
        },
        removeImage(index) {
            URL.revokeObjectURL(this.images[index].url);
            this.images.splice(index, 1);
            this.files.splice(index, 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            document.querySelector('input[name="gallery_images[]"]').files = dt.files;
        }
    };
}
</script>

<style>
/* Quill dark mode overrides */
.dark .ql-toolbar { background: rgb(55 65 81); border-color: rgb(75 85 99) !important; }
.dark .ql-container { background: rgb(55 65 81); border-color: rgb(75 85 99) !important; color: #f3f4f6; }
.dark .ql-editor.ql-blank::before { color: rgb(156 163 175); }
.dark .ql-stroke { stroke: rgb(209 213 219) !important; }
.dark .ql-fill { fill: rgb(209 213 219) !important; }
.dark .ql-picker { color: rgb(209 213 219) !important; }
.dark .ql-picker-options { background: rgb(31 41 55) !important; border-color: rgb(75 85 99) !important; }
</style>
@endpush
