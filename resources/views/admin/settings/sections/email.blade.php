<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
  <div class="xl:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">Email (SMTP) Settings</h2>
            <p class="text-gray-500 text-sm">Used to send order confirmations to customers and new-order alerts to you.</p>
          </div>
        </div>
        @php $configured = !empty($s['mail_host']) && ($s['mail_enabled'] ?? '1') === '1'; @endphp
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $configured ? 'text-green-700 bg-green-50' : 'text-amber-700 bg-amber-50' }}">
          <span class="w-1.5 h-1.5 rounded-full inline-block {{ $configured ? 'bg-green-500' : 'bg-amber-500' }}"></span>
          {{ $configured ? 'Configured' : 'Not configured' }}
        </span>
      </div>

      @if(!empty($s['mail_last_error']))
      <div class="mb-5 p-4 rounded-xl border border-red-200 bg-red-50">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          <div class="text-sm">
            <div class="font-semibold text-red-700">Last email attempt failed @if(!empty($s['mail_last_at']))<span class="font-normal text-red-500">({{ $s['mail_last_at'] }})</span>@endif</div>
            <div class="text-red-600 mt-1 font-mono text-xs break-all">{{ $s['mail_last_error'] }}</div>
            <div class="text-red-500 text-xs mt-2">Common fixes: port <b>465</b> for SSL or <b>587</b> for TLS · username must be the full email · for Gmail use an App password · check the host spelling.</div>
          </div>
        </div>
      </div>
      @elseif(!empty($s['mail_last_at']))
      <div class="mb-5 p-3 rounded-xl border border-green-200 bg-green-50 text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Last email sent successfully at {{ $s['mail_last_at'] }}.
      </div>
      @endif

      <form method="POST" action="{{ route('admin.settings.email') }}">
        @csrf

        <label class="flex items-center gap-3 mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
          <input type="checkbox" name="mail_enabled" value="1" class="w-5 h-5 rounded text-indigo-600" {{ ($s['mail_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
          <div>
            <div class="font-semibold text-gray-800 text-sm">Send emails</div>
            <div class="text-gray-500 text-xs">Turn off to pause all outgoing store emails without losing these settings.</div>
          </div>
        </label>

        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Server</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
          <div>
            <label class="form-label">Mailer</label>
            <select name="mail_mailer" class="form-input">
              <option value="smtp" {{ ($s['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (recommended)</option>
              <option value="log"  {{ ($s['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log only (writes to storage/logs — for testing)</option>
            </select>
          </div>
          <div>
            <label class="form-label">Encryption</label>
            <select name="mail_encryption" class="form-input" x-data @change="const p=$el.closest('form').querySelector('[name=mail_port]'); if($el.value==='ssl') p.value=465; else if($el.value==='tls') p.value=587;">
              <option value="tls"  {{ ($s['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS / STARTTLS (port 587)</option>
              <option value="ssl"  {{ ($s['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
              <option value="none" {{ ($s['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
            </select>
            <p class="text-gray-400 text-xs mt-1.5">The port decides the mode: 465 = SSL, anything else = TLS/STARTTLS.</p>
          </div>
          <div>
            <label class="form-label">SMTP Host <span class="text-red-500">*</span></label>
            <input type="text" name="mail_host" value="{{ old('mail_host', $s['mail_host'] ?? '') }}" placeholder="smtp.gmail.com" class="form-input @error('mail_host') border-red-400 @enderror">
            @error('mail_host') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Port</label>
            <input type="number" name="mail_port" value="{{ old('mail_port', $s['mail_port'] ?? 587) }}" placeholder="587" class="form-input @error('mail_port') border-red-400 @enderror">
            @error('mail_port') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Username</label>
            <input type="text" name="mail_username" value="{{ old('mail_username', $s['mail_username'] ?? '') }}" placeholder="you@gmail.com" autocomplete="off" class="form-input">
          </div>
          <div x-data="{show:false}">
            <label class="form-label">Password / App password</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" name="mail_password" value="" placeholder="{{ !empty($s['mail_password']) ? '•••••••• (saved — leave blank to keep)' : 'App password' }}" autocomplete="new-password" class="form-input pr-11">
              <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
            </div>
            <p class="text-gray-400 text-xs mt-1.5">Stored encrypted. For Gmail, create an <b>App password</b> (Google Account → Security → 2-Step Verification → App passwords).</p>
          </div>
          <div class="md:col-span-2">
            <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
              <input type="checkbox" name="mail_verify_peer" value="1" class="w-5 h-5 mt-0.5 rounded text-indigo-600" {{ ($s['mail_verify_peer'] ?? '1') === '1' ? 'checked' : '' }}>
              <div>
                <div class="font-semibold text-gray-800 text-sm">Verify TLS certificate</div>
                <div class="text-gray-500 text-xs">Keep on for production. Turn off only on a local/dev machine if you get "handshake" or "certificate verify failed" errors (antivirus SSL scanning, missing CA bundle).</div>
              </div>
            </label>
          </div>
        </div>

        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Sender &amp; notifications</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
          <div>
            <label class="form-label">From address</label>
            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $s['mail_from_address'] ?? '') }}" placeholder="orders@zelari.com" class="form-input @error('mail_from_address') border-red-400 @enderror">
            @error('mail_from_address') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            <p class="text-gray-400 text-xs mt-1.5">Most providers require this to match the username.</p>
          </div>
          <div>
            <label class="form-label">From name</label>
            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $s['mail_from_name'] ?? 'Zelari') }}" placeholder="Zelari" class="form-input">
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Admin notification email</label>
            <input type="email" name="mail_admin_address" value="{{ old('mail_admin_address', $s['mail_admin_address'] ?? '') }}" placeholder="owner@zelari.com" class="form-input @error('mail_admin_address') border-red-400 @enderror">
            <p class="text-gray-400 text-xs mt-1.5">Receives a copy of every new order and every contact-form message. Leave blank to disable.</p>
          </div>
        </div>

        <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Save email settings
        </button>
      </form>
    </div>
  </div>

  <div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-indigo-100" style="box-shadow:0 2px 16px rgba(99,102,241,.12)">
      <h3 class="font-semibold text-gray-700 mb-1 flex items-center gap-2"><svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Send a test email</h3>
      <p class="text-gray-500 text-sm mb-4">Save your settings first, then send a test to make sure everything works.</p>
      <form method="POST" action="{{ route('admin.settings.test') }}" class="flex flex-col gap-3">
        @csrf
        <input type="email" name="test_email" value="{{ old('test_email', Auth::user()->email) }}" required placeholder="you@example.com" class="form-input @error('test_email') border-red-400 @enderror">
        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
          Send test
        </button>
      </form>
      @error('test_email') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
    </div>
    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
      <h3 class="font-semibold text-gray-700 mb-3">Common providers</h3>
      <div class="space-y-3 text-sm">
        @foreach([
            ['Gmail / Google Workspace','smtp.gmail.com','587','TLS','Use an App password, not your login password.'],
            ['Outlook / Microsoft 365','smtp.office365.com','587','TLS','SMTP AUTH must be enabled for the mailbox.'],
            ['Zoho Mail','smtp.zoho.com','587','TLS','Use your full email as username.'],
            ['Mailtrap (testing)','sandbox.smtp.mailtrap.io','2525','TLS','Free inbox for safe testing.'],
            ['Brevo (Sendinblue)','smtp-relay.brevo.com','587','TLS','Free tier: 300 emails/day.'],
        ] as [$n,$h,$p,$e,$note])
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
          <div class="font-semibold text-gray-800">{{ $n }}</div>
          <div class="text-gray-600 text-xs mt-1 font-mono">{{ $h }} · {{ $p }} · {{ $e }}</div>
          <div class="text-gray-400 text-xs mt-1">{{ $note }}</div>
        </div>
        @endforeach
      </div>
    </div>
    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
      <h3 class="font-semibold text-gray-700 mb-3">What gets emailed</h3>
      <ul class="text-sm text-gray-600 space-y-2">
        <li class="flex gap-2"><span class="text-indigo-500">✓</span> Customer: order confirmation with items, totals, delivery details and a PDF invoice</li>
        <li class="flex gap-2"><span class="text-indigo-500">✓</span> Admin: a new-order alert (if an admin address is set)</li>
        <li class="flex gap-2"><span class="text-indigo-500">✓</span> Sending never blocks checkout — failures are logged in <code class="text-xs bg-slate-100 px-1 rounded">storage/logs</code></li>
      </ul>
    </div>
  </div>
</div>
