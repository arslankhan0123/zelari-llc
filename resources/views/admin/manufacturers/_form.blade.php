{{-- Shared fields for the manufacturer create / edit forms.
     @param $m  Manufacturer|null --}}
@php $m = $m ?? null; @endphp

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: the details --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">

        <div class="mb-5">
            <label class="form-label">Brand Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $m->name ?? '') }}" required maxlength="150"
                   class="form-input @error('name') border-red-400 @enderror" placeholder="e.g. Engine, Gul Ahmed, Sunsilk">
            @error('name')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-400 mt-1.5">The URL slug is generated from this name automatically.</p>
        </div>

        {{-- Logo --}}
        <div class="mb-5" x-data="{ preview: null, current: '{{ $m && $m->hasLogo() ? $m->logo_url : '' }}' }">
            <label class="form-label">Logo <span class="text-red-500">*</span>
                <span class="text-gray-400 font-normal text-xs ml-1">shown on the storefront Brands page</span></label>
            <label for="brandLogo" class="relative block w-full h-44 rounded-xl border-2 border-dashed cursor-pointer overflow-hidden transition-colors @error('logo') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 hover:border-indigo-400 hover:bg-indigo-50/40 @enderror">
                <template x-if="!preview && !current">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm text-gray-500">Click to upload the brand logo</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP or SVG — max 2MB · a transparent PNG looks best</p>
                        </div>
                    </div>
                </template>
                <template x-if="preview || current">
                    <img :src="preview || current" class="absolute inset-0 w-full h-full object-contain p-4 bg-white">
                </template>
                <span x-show="!preview && current" class="absolute bottom-2 right-2 text-[11px] font-semibold bg-black/55 text-white px-2 py-1 rounded-md">Click to replace</span>
                <span x-show="preview" class="absolute bottom-2 right-2 text-[11px] font-semibold bg-indigo-600 text-white px-2 py-1 rounded-md">New logo — saved on submit</span>
                <input id="brandLogo" type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="hidden"
                       {{ $m && $m->hasLogo() ? '' : 'required' }}
                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </label>
            <template x-if="preview">
                <button type="button" @click="preview=null; document.getElementById('brandLogo').value=''" class="mt-2 text-xs text-red-500 hover:text-red-700">
                    {{ $m && $m->hasLogo() ? 'Keep current logo' : 'Remove' }}
                </button>
            </template>
            @error('logo')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
        </div>

        <div class="mb-5">
            <label class="form-label">Description <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
            <textarea name="description" rows="4" class="form-input resize-none @error('description') border-red-400 @enderror"
                      placeholder="What this brand is known for — shown under its name on the Brands page.">{{ old('description', $m->description ?? '') }}</textarea>
            @error('description')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="form-label">Website <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                <input type="url" name="website" value="{{ old('website', $m->website ?? '') }}"
                       class="form-input @error('website') border-red-400 @enderror" placeholder="https://brand.com">
                @error('website')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Country <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                <input type="text" name="country" value="{{ old('country', $m->country ?? '') }}" maxlength="80"
                       class="form-input" placeholder="e.g. Pakistan">
            </div>
            <div>
                <label class="form-label">Contact Email <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                <input type="email" name="email" value="{{ old('email', $m->email ?? '') }}"
                       class="form-input @error('email') border-red-400 @enderror" placeholder="sales@brand.com">
                @error('email')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Contact Phone <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                <input type="text" name="phone" value="{{ old('phone', $m->phone ?? '') }}" maxlength="40"
                       class="form-input" placeholder="+92 300 1234567">
            </div>
        </div>
    </div>

    {{-- Right: publishing options --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)"
             x-data="{ selectedStatus: '{{ old('status', $m->status ?? 'active') }}' }">
            <h3 class="font-bold text-gray-800 mb-4">Status</h3>

            <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition mb-3"
                   :class="selectedStatus === 'active' ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-200'">
                <input type="radio" name="status" value="active" x-model="selectedStatus" class="accent-indigo-600">
                <span class="flex-1">
                    <span class="block text-sm font-semibold text-gray-800">Active</span>
                    <span class="block text-xs text-gray-400">Visible on the Brands page</span>
                </span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600">Active</span>
            </label>

            <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition"
                   :class="selectedStatus === 'inactive' ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-200'">
                <input type="radio" name="status" value="inactive" x-model="selectedStatus" class="accent-indigo-600">
                <span class="flex-1">
                    <span class="block text-sm font-semibold text-gray-800">Inactive</span>
                    <span class="block text-xs text-gray-400">Hidden from customers</span>
                </span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inactive</span>
            </label>
        </div>

        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-bold text-gray-800 mb-4">Placement</h3>

            <label class="flex items-start gap-3 cursor-pointer mb-5">
                <input type="checkbox" name="featured" value="1" class="mt-0.5 w-4 h-4 accent-indigo-600"
                       {{ old('featured', $m->featured ?? false) ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-semibold text-gray-800">Featured brand</span>
                    <span class="block text-xs text-gray-400">Shown first on the Brands page</span>
                </span>
            </label>

            <div class="mb-5">
                <label class="form-label">Sort order</label>
                <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $m->sort_order ?? 0) }}" class="form-input">
                <p class="text-xs text-gray-400 mt-1.5">Lower numbers come first. Same number → sorted by name.</p>
            </div>

            <div>
                <label class="form-label">Established <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span></label>
                <input type="number" name="established_year" min="1800" max="{{ date('Y') + 1 }}"
                       value="{{ old('established_year', $m->established_year ?? '') }}" class="form-input @error('established_year') border-red-400 @enderror" placeholder="{{ date('Y') }}">
                @error('established_year')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        @if($m)
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-bold text-gray-800 mb-3">Usage</h3>
            <p class="text-sm text-gray-500">
                <b class="text-gray-800">{{ $m->products_count ?? $m->products()->count() }}</b> product(s) use this brand.
            </p>
            <a href="{{ route('admin.products.index', ['search' => $m->name]) }}" class="text-xs text-indigo-600 hover:underline">View products →</a>
        </div>
        @endif
    </div>
</div>
