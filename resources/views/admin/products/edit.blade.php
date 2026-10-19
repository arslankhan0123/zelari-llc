@extends('layouts.admin')

@section('title', 'Edit Product')
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Product</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $product->name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">
        <p class="font-semibold mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" id="productForm">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- ── LEFT COLUMN ── -->
            <div class="xl:col-span-2 space-y-6">

                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Basic Information</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                               class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Slug
                            <span class="font-normal text-gray-400 text-xs ml-1">(auto-generated from name)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none">/</span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}"
                                   readonly disabled
                                   class="w-full pl-8 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-mono text-sm cursor-not-allowed select-none @error('slug') border-red-500 @enderror">
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Auto-generated from product name — unique and URL-safe.</p>
                        @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                               class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sku') border-red-500 @enderror">
                        @error('sku')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
                        <select name="category_id" class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                                <input type="number" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" step="0.01" min="0" required
                                       class="w-full pl-12 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('purchase_price') border-red-500 @enderror">
                            </div>
                            @error('purchase_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sale Price</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-xs">{{ config('currencies.base') }}</span>
                                <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0"
                                       class="w-full pl-12 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('sale_price') border-red-500 @enderror">
                            </div>
                            @error('sale_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" min="0" required
                                   class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror">
                            @error('quantity')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Descriptions</h2>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Short Description</label>
                            <span id="shortDescCount" class="text-xs text-gray-400">0 / 500</span>
                        </div>
                        <textarea name="short_description" id="shortDesc" rows="3" maxlength="500"
                                  class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('short_description') border-red-500 @enderror">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Long Description</label>
                        <div class="border border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden">
                            <div id="quillToolbar" class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-300 dark:border-gray-600">
                                <span class="ql-formats">
                                    <select class="ql-header"><option selected></option><option value="1">H1</option><option value="2">H2</option><option value="3">H3</option></select>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-bold"></button><button class="ql-italic"></button>
                                    <button class="ql-underline"></button><button class="ql-strike"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-link"></button><button class="ql-blockquote"></button><button class="ql-code-block"></button>
                                </span>
                                <span class="ql-formats"><button class="ql-clean"></button></span>
                            </div>
                            <div id="quillEditor" class="bg-white dark:bg-gray-700" style="min-height:220px;max-height:500px;overflow-y:auto;"></div>
                        </div>
                        <input type="hidden" name="description" id="descriptionInput">
                        @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Feature Image -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4" x-data="featureUploader()">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Feature Image</h2>

                    <!-- Current feature image -->
                    @if($product->feature_image)
                    <div class="flex items-start gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl" x-show="!newPreview">
                        <img src="{{ Storage::url($product->feature_image) }}" alt="Current" class="w-24 h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Current image</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ basename($product->feature_image) }}</p>
                            <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer">
                                <input type="checkbox" name="remove_feature_image" value="1" class="rounded text-red-500">
                                <span class="text-xs text-red-500">Remove current image</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    <label for="featureImage"
                           class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors relative overflow-hidden">
                        <template x-if="!newPreview">
                            <div class="text-center">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->feature_image ? 'Upload replacement image' : 'Click to upload' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WebP — max 2MB</p>
                            </div>
                        </template>
                        <template x-if="newPreview">
                            <img :src="newPreview" class="absolute inset-0 w-full h-full object-cover">
                        </template>
                        <input id="featureImage" type="file" name="feature_image" accept="image/*" class="hidden"
                               @change="newPreview = URL.createObjectURL($event.target.files[0])">
                    </label>
                    <template x-if="newPreview">
                        <button type="button" @click="newPreview=null; document.getElementById('featureImage').value=''"
                                class="text-xs text-red-500 hover:text-red-700 transition-colors">Remove new selection</button>
                    </template>
                    @error('feature_image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Gallery Images -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5" x-data="galleryUploader()">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white text-base">Gallery Images</h2>
                        <span class="text-xs text-gray-400">{{ $product->images->count() }} existing + <span x-text="newImages.length"></span> new</span>
                    </div>

                    <!-- Existing gallery images -->
                    @if($product->images->count())
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Existing Images</p>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                            @foreach($product->images as $img)
                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                <img src="{{ Storage::url($img->image_path) }}" alt="Gallery" class="w-full h-full object-cover">
                                <label class="absolute inset-0 bg-red-500/0 group-hover:bg-red-500/70 transition-colors flex items-center justify-center cursor-pointer">
                                    <input type="checkbox" name="remove_images[]" value="{{ $img->id }}" class="sr-only peer">
                                    <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <div class="absolute inset-0 border-4 border-red-500 rounded-lg opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Hover and click an image to mark it for deletion</p>
                    </div>
                    @endif

                    <!-- New gallery uploads -->
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Add New Images</p>
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <svg class="w-7 h-7 text-gray-400 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Click to add more images</p>
                            <p class="text-xs text-gray-400 mt-0.5">Multiple allowed — JPG, PNG, WebP — max 2MB each</p>
                            <input type="file" name="gallery_images[]" accept="image/*" multiple class="hidden" @change="addImages($event)">
                        </label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mt-3" x-show="newImages.length > 0">
                            <template x-for="(img, i) in newImages" :key="i">
                                <div class="relative group aspect-square rounded-lg overflow-hidden border-2 border-indigo-300 dark:border-indigo-600">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    <button type="button" @click="removeNew(i)"
                                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold">✕</button>
                                    <span class="absolute bottom-1 left-1 text-xs bg-indigo-600 text-white px-1.5 py-0.5 rounded-full">New</span>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('gallery_images.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- ── RIGHT COLUMN ── -->
            <div class="space-y-6">

                <!-- Status -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Status</h2>
                    @foreach(['draft' => ['Draft', 'Saved but not visible'], 'active' => ['Active', 'Visible on storefront'], 'inactive' => ['Inactive', 'Hidden from storefront']] as $val => $info)
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors
                        {{ old('status', $product->status) === $val ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="radio" name="status" value="{{ $val }}" {{ old('status', $product->status) === $val ? 'checked' : '' }}
                               class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $info[0] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $info[1] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                    <button type="submit" class="btn-primary w-full">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="block w-full py-2.5 px-4 text-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </a>
                    <hr class="border-gray-200 dark:border-gray-700">
                    <button type="submit" form="deleteProductForm"
                            onclick="return confirm('Permanently delete this product and all its images?')"
                            class="w-full py-2.5 px-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-sm font-medium rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors border border-red-200 dark:border-red-800">
                        Delete Product
                    </button>
                </div>

                <!-- Product Info -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 space-y-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Product Info</h3>
                    <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between"><span>Created</span><span>{{ $product->created_at->format('M d, Y') }}</span></div>
                        <div class="flex justify-between"><span>Updated</span><span>{{ $product->updated_at->format('M d, Y') }}</span></div>
                        <div class="flex justify-between"><span>ID</span><span class="font-mono">#{{ $product->id }}</span></div>
                        <div class="flex justify-between"><span>Gallery</span><span>{{ $product->images->count() }} image(s)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

{{-- Delete form outside main form to avoid nested form bug --}}
<form id="deleteProductForm" method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:none">
    @csrf @method('DELETE')
</form>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
// ── Auto slug (read-only, driven by name) ──
function slugify(str) {
    return str.toLowerCase().trim().replace(/[^\w\s-]/g,'').replace(/[\s_-]+/g,'-').replace(/^-+|-+$/g,'');
}
document.getElementById('name').addEventListener('input', function() {
    document.getElementById('slug').value = slugify(this.value);
});

// ── Short description counter ──
const shortDesc = document.getElementById('shortDesc');
const counter   = document.getElementById('shortDescCount');
function updateCount(){ counter.textContent = shortDesc.value.length + ' / 500'; }
shortDesc.addEventListener('input', updateCount);
updateCount();

// ── Quill ──
const quill = new Quill('#quillEditor', {
    theme: 'snow',
    modules: { toolbar: '#quillToolbar' },
    placeholder: 'Write a detailed product description…',
});
const existingDesc = @json(old('description', $product->description ?? ''));
if (existingDesc) { quill.clipboard.dangerouslyPasteHTML(existingDesc); }
document.getElementById('productForm').addEventListener('submit', function() {
    document.getElementById('descriptionInput').value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
});

// ── Feature uploader (Alpine) ──
function featureUploader() { return { newPreview: null }; }

// ── Gallery uploader (Alpine) ──
function galleryUploader() {
    return {
        newImages: [],
        files: [],
        addImages(event) {
            Array.from(event.target.files).forEach(file => {
                this.newImages.push({ url: URL.createObjectURL(file), file });
                this.files.push(file);
            });
            this.syncInput();
            event.target.value = '';
        },
        removeNew(index) {
            URL.revokeObjectURL(this.newImages[index].url);
            this.newImages.splice(index, 1);
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
.dark .ql-toolbar { background: rgb(55 65 81); border-color: rgb(75 85 99) !important; }
.dark .ql-container { background: rgb(55 65 81); border-color: rgb(75 85 99) !important; color: #f3f4f6; }
.dark .ql-editor.ql-blank::before { color: rgb(156 163 175); }
.dark .ql-stroke { stroke: rgb(209 213 219) !important; }
.dark .ql-fill { fill: rgb(209 213 219) !important; }
.dark .ql-picker { color: rgb(209 213 219) !important; }
.dark .ql-picker-options { background: rgb(31 41 55) !important; border-color: rgb(75 85 99) !important; }
</style>
@endpush
