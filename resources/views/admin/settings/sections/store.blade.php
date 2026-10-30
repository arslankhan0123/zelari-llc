<div class="max-w-4xl">
  <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
    <div class="flex items-center gap-4 mb-6">
      <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2zM3 7l2-4h14l2 4M16 11a4 4 0 01-8 0"/></svg>
      </div>
      <div>
        <h2 class="text-xl font-bold text-gray-800">Store Details</h2>
        <p class="text-gray-500 text-sm">Shown in the website header, footer, contact page, emails and invoices.</p>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.store') }}">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div>
          <label class="form-label">Store name</label>
          <input type="text" name="store_name" value="{{ old('store_name', $s['store_name'] ?? 'Zelari') }}" class="form-input">
        </div>
        <div>
          <label class="form-label">Tagline</label>
          <input type="text" name="store_tagline" value="{{ old('store_tagline', $s['store_tagline'] ?? '') }}" placeholder="Smart Shopping" class="form-input">
        </div>
        <div>
          <label class="form-label">Hotline phone</label>
          <input type="text" name="store_phone" value="{{ old('store_phone', $s['store_phone'] ?? '') }}" placeholder="+92 300 0000000" class="form-input">
        </div>
        <div>
          <label class="form-label">WhatsApp number</label>
          <input type="text" name="store_whatsapp" value="{{ old('store_whatsapp', $s['store_whatsapp'] ?? '') }}" placeholder="+92 300 0000000 (blank = use hotline)" class="form-input">
        </div>
        <div>
          <label class="form-label">Support email</label>
          <input type="email" name="store_email" value="{{ old('store_email', $s['store_email'] ?? '') }}" placeholder="support@zelari.com" class="form-input @error('store_email') border-red-400 @enderror">
          @error('store_email') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="form-label">Support hours</label>
          <input type="text" name="store_hours" value="{{ old('store_hours', $s['store_hours'] ?? '') }}" placeholder="Mon – Sat, 9 AM – 6 PM PKT" class="form-input">
        </div>
        <div class="md:col-span-2">
          <label class="form-label">Address</label>
          <input type="text" name="store_address" value="{{ old('store_address', $s['store_address'] ?? '') }}" class="form-input">
        </div>
        @foreach([['store_facebook','Facebook URL','https://facebook.com/yourpage'],['store_instagram','Instagram URL','https://instagram.com/yourpage'],['store_twitter','X / Twitter URL','https://x.com/yourpage'],['store_pinterest','Pinterest URL','https://pinterest.com/yourpage']] as [$k,$l,$ph])
        <div>
          <label class="form-label">{{ $l }}</label>
          <input type="url" name="{{ $k }}" value="{{ old($k, $s[$k] ?? '') }}" placeholder="{{ $ph }}" class="form-input @error($k) border-red-400 @enderror">
          @error($k) <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>
        @endforeach
      </div>

      <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Save store details
      </button>
    </form>
  </div>
</div>
