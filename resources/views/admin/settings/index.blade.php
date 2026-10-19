@extends('layouts.admin')
@section('title', 'Settings')
@section('breadcrumb', 'Store configuration — branding, email, payments and contact details')

@section('content')
@php $s = $settings; @endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-2 space-y-6">

        {{-- ───────────── Branding card ───────────── --}}
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white"
                     style="background:linear-gradient(135deg,#ec4899,#8b5cf6);box-shadow:0 8px 24px rgba(236,72,153,.35);">
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
                <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#ec4899,#8b5cf6);box-shadow:0 8px 24px rgba(236,72,153,.3)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save branding
                </button>
            </form>
        </div>

        {{-- ───────────── SMTP / Email card ───────────── --}}
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

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf @method('PUT')

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

                <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Sender & notifications</h3>
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
                        <p class="text-gray-400 text-xs mt-1.5">Receives a copy of every new order. Leave blank to disable.</p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-700 mb-1 pb-2 border-b border-gray-100">Store details</h3>
                <p class="text-gray-400 text-xs mb-4">Shown in the website header, footer, contact page, emails and invoices.</p>
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

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save settings
                    </button>
                </div>
            </form>
        </div>

        {{-- ───────────── Payments card ───────────── --}}
        @php
            $stripeOn    = ($s['pay_stripe_enabled'] ?? '0') === '1';
            $stripeReady = $stripeOn && !empty($s['stripe_publishable_key']) && !empty($s['stripe_secret_key']);
            $stripeMode  = str_starts_with((string)($s['stripe_publishable_key'] ?? ''), 'pk_live_') ? 'live' : 'test';
        @endphp
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)" x-data="{stripe: {{ $stripeOn ? 'true' : 'false' }}, showSk:false, showWh:false, paypal: {{ ($s['pay_paypal_enabled'] ?? '0') === '1' ? 'true' : 'false' }}, showPp:false}">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white"
                         style="background:linear-gradient(135deg,#0ea5e9,#6366f1);box-shadow:0 8px 24px rgba(14,165,233,.35);">
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

                {{-- COD --}}
                <label class="flex items-start gap-3 mb-3 p-4 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="pay_cod_enabled" value="1" class="w-5 h-5 mt-0.5 rounded text-indigo-600" {{ ($s['pay_cod_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 text-sm">Cash on Delivery</div>
                        <div class="text-gray-500 text-xs">Customer pays the rider in cash. Nothing to configure.</div>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">No fees</span>
                </label>

                {{-- Stripe --}}
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

                {{-- PayPal --}}
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

                <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#0ea5e9,#6366f1);box-shadow:0 8px 24px rgba(14,165,233,.3)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save payment settings
                </button>
            </form>
        </div>

    </div>

    {{-- ───────────── Side help ───────────── --}}
    <div class="space-y-6">
    {{-- ───────────── Test email ───────────── --}}
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
@endsection
