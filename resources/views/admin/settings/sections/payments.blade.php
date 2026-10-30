<div class="max-w-4xl">
  @php
    $stripeOn    = ($s['pay_stripe_enabled'] ?? '0') === '1';
    $stripeReady = $stripeOn && !empty($s['stripe_publishable_key']) && !empty($s['stripe_secret_key']);
    $stripeMode  = str_starts_with((string)($s['stripe_publishable_key'] ?? ''), 'pk_live_') ? 'live' : 'test';
  @endphp
  <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)" x-data="{stripe: {{ $stripeOn ? 'true' : 'false' }}, showSk:false, showWh:false, paypal: {{ ($s['pay_paypal_enabled'] ?? '0') === '1' ? 'true' : 'false' }}, showPp:false}">
    <div class="flex items-start justify-between gap-4 mb-6">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);box-shadow:0 8px 24px rgba(249,115,22,.35);">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Payment Methods</h2>
          <p class="text-gray-500 text-sm">Choose what customers can pay with. Only enabled methods appear at checkout.</p>
        </div>
      </div>
      @php
        $ppReady = ($s['pay_paypal_enabled'] ?? '0') === '1' && !empty($s['paypal_client_id']) && !empty($s['paypal_secret']);
        $ppMode  = ($s['paypal_mode'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';
      @endphp
      <div class="flex flex-col items-end gap-1.5">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $stripeReady ? ($stripeMode === 'live' ? 'text-green-700 bg-green-50' : 'text-sky-700 bg-sky-50') : 'text-gray-600 bg-gray-100' }}">
          <span class="w-1.5 h-1.5 rounded-full inline-block {{ $stripeReady ? ($stripeMode === 'live' ? 'bg-green-500' : 'bg-sky-500') : 'bg-gray-400' }}"></span>
          {{ $stripeReady ? 'Stripe ' . $stripeMode : 'Stripe off' }}
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $ppReady ? ($ppMode === 'live' ? 'text-green-700 bg-green-50' : 'text-sky-700 bg-sky-50') : 'text-gray-600 bg-gray-100' }}">
          <span class="w-1.5 h-1.5 rounded-full inline-block {{ $ppReady ? ($ppMode === 'live' ? 'bg-green-500' : 'bg-sky-500') : 'bg-gray-400' }}"></span>
          {{ $ppReady ? 'PayPal ' . $ppMode : 'PayPal off' }}
        </span>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.payments') }}" autocomplete="off">
      @csrf @method('PUT')

      <label class="flex items-start gap-3 mb-3 p-4 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
        <input type="checkbox" name="pay_cod_enabled" value="1" class="w-5 h-5 mt-0.5 rounded text-indigo-600" {{ ($s['pay_cod_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
        <div class="flex-1">
          <div class="font-semibold text-gray-800 text-sm">Cash on Delivery</div>
          <div class="text-gray-500 text-xs">Customer pays the rider in cash. Nothing to configure.</div>
        </div>
        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">No fees</span>
      </label>

      <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <label class="flex items-start gap-3 p-4 bg-slate-50 cursor-pointer">
          <input type="checkbox" name="pay_stripe_enabled" value="1" x-model="stripe" class="w-5 h-5 mt-0.5 rounded text-indigo-600">
          <div class="flex-1">
            <div class="font-semibold text-gray-800 text-sm">Credit / Debit Card <span class="font-normal text-gray-400">via Stripe</span></div>
            <div class="text-gray-500 text-xs">Visa, Mastercard, Amex, Apple Pay &amp; Google Pay. Card details go straight to Stripe and never touch your server.</div>
          </div>
          <svg class="w-10 h-5 text-[#635bff]" viewBox="0 0 60 25" fill="currentColor" aria-label="Stripe"><path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.9 0 1.85 6.29.97 6.29 5.88z"/></svg>
        </label>
        <div x-show="stripe" class="p-4 border-t border-slate-200 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="form-label">Publishable key</label>
              <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $s['stripe_publishable_key'] ?? '') }}" placeholder="pk_test_…" spellcheck="false" class="form-input font-mono text-sm @error('stripe_publishable_key') border-red-400 @enderror">
              @error('stripe_publishable_key') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
              <p class="text-gray-400 text-xs mt-1.5">Safe to show in the browser — it only lets the card form talk to Stripe.</p>
            </div>
            <div>
              <label class="form-label">Secret key</label>
              <div class="relative">
                <input :type="showSk ? 'text' : 'password'" name="stripe_secret_key" value="" placeholder="{{ !empty($s['stripe_secret_key']) ? '•••••••• (saved — leave blank to keep)' : 'sk_test_…' }}" autocomplete="new-password" spellcheck="false" class="form-input font-mono text-sm pr-11 @error('stripe_secret_key') border-red-400 @enderror">
                <button type="button" @click="showSk=!showSk" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
              </div>
              @error('stripe_secret_key') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
              <p class="text-gray-400 text-xs mt-1.5">Stored encrypted, used only on the server. Checked against Stripe when you save.</p>
            </div>
            <div>
              <label class="form-label">Webhook signing secret <span class="text-gray-400 font-normal">(recommended)</span></label>
              <div class="relative">
                <input :type="showWh ? 'text' : 'password'" name="stripe_webhook_secret" value="" placeholder="{{ !empty($s['stripe_webhook_secret']) ? '•••••••• (saved — leave blank to keep)' : 'whsec_…' }}" autocomplete="new-password" spellcheck="false" class="form-input font-mono text-sm pr-11 @error('stripe_webhook_secret') border-red-400 @enderror">
                <button type="button" @click="showWh=!showWh" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
              </div>
              @error('stripe_webhook_secret') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
              <p class="text-gray-400 text-xs mt-1.5">Confirms payments that finish after the customer closes the tab.</p>
            </div>
          </div>
          <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 text-xs text-sky-800 leading-relaxed">
            <b>Setup:</b> Stripe Dashboard → Developers → <b>API keys</b> → copy both keys here.
            Then Developers → <b>Webhooks</b> → Add endpoint
            <code class="bg-white px-1.5 py-0.5 rounded border border-sky-100 select-all">{{ url('/webhooks/stripe') }}</code>
            with events <code class="bg-white px-1 rounded">payment_intent.succeeded</code>, <code class="bg-white px-1 rounded">payment_intent.payment_failed</code>, <code class="bg-white px-1 rounded">payment_intent.canceled</code>, and paste its signing secret above.
            Test with card <code class="bg-white px-1.5 py-0.5 rounded border border-sky-100">4242 4242 4242 4242</code>, any future date, any CVC.
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <label class="flex items-start gap-3 p-4 bg-slate-50 cursor-pointer">
          <input type="checkbox" name="pay_paypal_enabled" value="1" x-model="paypal" class="w-5 h-5 mt-0.5 rounded text-indigo-600">
          <div class="flex-1">
            <div class="font-semibold text-gray-800 text-sm">PayPal</div>
            <div class="text-gray-500 text-xs">PayPal buttons at checkout — PayPal balance, linked bank, or guest card. Needs a PayPal Business account. Charged in the customer's currency when PayPal supports it, otherwise in USD.</div>
          </div>
          <svg class="w-10 h-6" viewBox="0 0 24 24" fill="#003087" aria-label="PayPal"><path d="M7.1 21.3H3.7a.6.6 0 0 1-.6-.7L5.9 2.9c.1-.5.5-.9 1-.9h6.6c2.2 0 3.9.5 4.9 1.5.9.9 1.2 2.1 1 3.7-.6 4.1-3 6.3-7.1 6.3h-2.1c-.5 0-.9.4-1 .9l-1.1 6.9zm10.3-13.6c.1-.5.1-1 0-1.3-.4-.5-1.3-.8-2.7-.8H9.3l-1.4 8.8h1.5c3 0 5.1-1.5 5.6-4.6.1-.8.3-1.5.4-2.1z"/></svg>
        </label>
        <div x-show="paypal" class="p-4 border-t border-slate-200 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="form-label">Mode</label>
              <select name="paypal_mode" class="form-input">
                <option value="sandbox" {{ $ppMode === 'sandbox' ? 'selected' : '' }}>Sandbox (testing)</option>
                <option value="live" {{ $ppMode === 'live' ? 'selected' : '' }}>Live</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="form-label">Client ID</label>
              <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $s['paypal_client_id'] ?? '') }}" placeholder="AbCd…" spellcheck="false" class="form-input font-mono text-sm @error('paypal_client_id') border-red-400 @enderror">
              @error('paypal_client_id') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
              <p class="text-gray-400 text-xs mt-1.5">Public by design — it only identifies your app to the PayPal buttons.</p>
            </div>
            <div class="md:col-span-3">
              <label class="form-label">Secret</label>
              <div class="relative">
                <input :type="showPp ? 'text' : 'password'" name="paypal_secret" value="" placeholder="{{ !empty($s['paypal_secret']) ? '•••••••• (saved — leave blank to keep)' : 'EFgH…' }}" autocomplete="new-password" spellcheck="false" class="form-input font-mono text-sm pr-11">
                <button type="button" @click="showPp=!showPp" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
              </div>
              <p class="text-gray-400 text-xs mt-1.5">Stored encrypted, used only on the server. Verified against PayPal when you save.</p>
            </div>
          </div>
          <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 text-xs text-sky-800 leading-relaxed">
            <b>Setup:</b> developer.paypal.com → Apps &amp; Credentials → choose <b>Sandbox</b> or <b>Live</b> → Create App → copy the Client ID and Secret here and pick the matching mode.
            To test, pay in the PayPal window with a sandbox <i>personal</i> account (Testing Tools → Sandbox Accounts).
          </div>
        </div>
      </div>

      <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#f59e0b,#b45309);box-shadow:0 8px 24px rgba(249,115,22,.3)">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Save payment settings
      </button>
    </form>
  </div>
</div>
