<div class="max-w-4xl">
  <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    <div class="flex items-center gap-4 mb-6">
      <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);box-shadow:0 8px 24px rgba(249,115,22,.35);">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
      </div>
      <div>
        <h2 class="text-xl font-bold text-gray-800">Branding</h2>
        <p class="text-gray-500 text-sm">Logo, icon and print logo used across the website, this admin panel, emails and PDF invoices.</p>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.branding') }}" enctype="multipart/form-data">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        @foreach([
            ['brand_logo',  'logo',  'Website logo',  'Header, mobile menu and footer. SVG or PNG with transparent background, roughly 4:1 (e.g. 480×120).', 'h-12 w-auto max-w-full', 'bg-white'],
            ['brand_icon',  'icon',  'Icon / favicon', 'Browser tab icon and the admin panel badge. Square PNG or SVG, 192×192 or larger.', 'h-14 w-14 rounded-xl object-cover', 'bg-slate-900'],
            ['brand_print', 'print', 'Print logo',    'Used on the PDF invoice and in emails. PNG or JPG only, dark on transparent/white, ~600×150.', 'h-12 w-auto max-w-full', 'bg-white'],
        ] as [$field, $key, $label, $hint, $imgCls, $bg])
        <div class="rounded-2xl border border-slate-200 overflow-hidden" x-data="{name:''}">
          <div class="h-28 {{ $bg }} flex items-center justify-center p-4 relative" style="background-image:linear-gradient(45deg,rgba(0,0,0,.04) 25%,transparent 25%,transparent 75%,rgba(0,0,0,.04) 75%),linear-gradient(45deg,rgba(0,0,0,.04) 25%,transparent 25%,transparent 75%,rgba(0,0,0,.04) 75%);background-size:16px 16px;background-position:0 0,8px 8px">
            <img src="{{ zbrand($key) }}" alt="{{ $label }}" class="{{ $imgCls }}">
            @if(zbrand_custom($key))<span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500 text-white">Custom</span>
            @else<span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">Default</span>@endif
          </div>
          <div class="p-4">
            <div class="font-semibold text-gray-800 text-sm">{{ $label }}</div>
            <p class="text-gray-400 text-xs mt-1 leading-relaxed">{{ $hint }}</p>
            <label class="mt-3 flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-indigo-300 text-indigo-700 bg-indigo-50/60 hover:bg-indigo-50 text-xs font-semibold cursor-pointer">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              <span x-text="name || 'Choose file…'" class="truncate"></span>
              <input type="file" name="{{ $field }}" class="hidden" accept="{{ $key === 'print' ? '.png,.jpg,.jpeg' : '.png,.jpg,.jpeg,.webp,.svg,.ico' }}" @change="name = $event.target.files[0]?.name || ''">
            </label>
            @error($field) <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            @if(zbrand_custom($key))
            <label class="mt-2 flex items-center gap-2 text-xs text-gray-500 cursor-pointer"><input type="checkbox" name="remove_{{ $field }}" value="1" class="rounded text-red-500"> Remove and use default</label>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#f59e0b,#b45309);box-shadow:0 8px 24px rgba(249,115,22,.3)">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Save branding
      </button>
    </form>
  </div>
</div>
