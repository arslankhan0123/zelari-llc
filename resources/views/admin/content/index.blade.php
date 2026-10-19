@extends('layouts.admin')
@section('title', 'Website Content')
@section('breadcrumb', 'Dynamic sections of the storefront — delivery promises, home-page banners, FAQs and login pages')

@section('content')
@php $s = $settings; @endphp
<div class="p-6 space-y-6">

    {{-- ───────────── Shipping & delivery ───────────── --}}
    <div id="shipping" class="bg-white rounded-2xl p-6 scroll-mt-24" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Shipping &amp; delivery</h2>
                    <p class="text-gray-500 text-sm">Shown on product pages, cart, checkout, the confirmation page, emails and the PDF invoice. Amounts are entered in <b>USD</b> and converted to the currency each customer is browsing in.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full text-green-700 bg-green-50 whitespace-nowrap">
                <span class="w-1.5 h-1.5 rounded-full inline-block bg-green-500"></span>
                {{ (float)($s['ship_free_over_usd'] ?? 0) > 0 ? 'Free over $'.number_format((float)$s['ship_free_over_usd'], 2) : 'Always free' }}
            </span>
        </div>
        <form method="POST" action="{{ route('admin.content.update', 'shipping') }}">
            @csrf @method('PUT')
                <div class="grid grid-cols-2 md:grid-cols-3 gap-5 mb-6">
                    <div>
                        <label class="form-label">Free delivery over (USD)</label>
                        <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                        <input type="number" step="0.01" min="0" name="ship_free_over_usd" value="{{ old('ship_free_over_usd', $s['ship_free_over_usd'] ?? 30) }}" class="form-input pl-7 @error('ship_free_over_usd') border-red-400 @enderror"></div>
                        <p class="text-gray-400 text-xs mt-1.5">0 = delivery is always free.@if(($s['ship_free_over_usd'] ?? 0) > 0) Currently ≈ {{ zorder_price((object)['currency_code'=>'PKR','currency_symbol'=>'Rs','exchange_rate'=>1], zfree_over()) }} in PKR.@endif</p>
                        @error('ship_free_over_usd') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Delivery fee below that (USD)</label>
                        <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                        <input type="number" step="0.01" min="0" name="ship_fee_usd" value="{{ old('ship_fee_usd', $s['ship_fee_usd'] ?? 0) }}" class="form-input pl-7"></div>
                        <p class="text-gray-400 text-xs mt-1.5">Added at checkout when the order is under the threshold. 0 = no fee.</p>
                    </div>
                    <div>
                        <label class="form-label">Delivery time (business days)</label>
                        <input type="text" name="ship_delivery_days" value="{{ old('ship_delivery_days', $s['ship_delivery_days'] ?? '2–5') }}" placeholder="2–5" class="form-input">
                        <p class="text-gray-400 text-xs mt-1.5">Shown as "Delivered in 2–5 business days".</p>
                    </div>
                    <div>
                        <label class="form-label">Packed within (hours)</label>
                        <input type="number" min="1" name="ship_pack_hours" value="{{ old('ship_pack_hours', $s['ship_pack_hours'] ?? 24) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Returns window (days)</label>
                        <input type="number" min="0" name="ship_return_days" value="{{ old('ship_return_days', $s['ship_return_days'] ?? 7) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Coverage</label>
                        <input type="text" name="ship_coverage" value="{{ old('ship_coverage', $s['ship_coverage'] ?? 'nationwide') }}" placeholder="nationwide / across Pakistan" class="form-input">
                    </div>
                </div>
            <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm bg-brand-grad">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save shipping settings
            </button>
        </form>
    </div>

    {{-- ───────────── Deal of the week ───────────── --}}
    <div id="deal" class="bg-white rounded-2xl p-6 scroll-mt-24" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#ff4d7d,#ff8a5b);box-shadow:0 8px 24px rgba(255,77,125,.35)">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Deal of the week banner</h2>
                    <p class="text-gray-500 text-sm">The countdown banner on the home page. Change the words, the badge, where the button goes and when the timer ends.</p>
                </div>
            </div>
            @php $dealOn = ($s['deal_enabled'] ?? '1') === '1'; @endphp
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $dealOn ? 'text-green-700 bg-green-50' : 'text-gray-600 bg-gray-100' }} whitespace-nowrap">
                <span class="w-1.5 h-1.5 rounded-full inline-block {{ $dealOn ? 'bg-green-500' : 'bg-gray-400' }}"></span>{{ $dealOn ? 'Visible' : 'Hidden' }}
            </span>
        </div>
        <form method="POST" action="{{ route('admin.content.update', 'deal') }}">
            @csrf @method('PUT')
                <label class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="deal_enabled" value="1" class="w-5 h-5 rounded text-indigo-600" {{ ($s['deal_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                    <div><div class="font-semibold text-gray-800 text-sm">Show the banner</div><div class="text-gray-500 text-xs">Untick to hide it without losing the text below.</div></div>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div><label class="form-label">Small label</label><input type="text" name="deal_eyebrow" value="{{ old('deal_eyebrow', $s['deal_eyebrow'] ?? 'Deal of the week') }}" class="form-input"></div>
                    <div><label class="form-label">Badge on the photo</label><input type="text" name="deal_badge" value="{{ old('deal_badge', $s['deal_badge'] ?? 'Up to 50% OFF') }}" placeholder="Up to 50% OFF (blank to hide)" class="form-input"></div>
                    <div><label class="form-label">Headline</label><input type="text" name="deal_title" value="{{ old('deal_title', $s['deal_title'] ?? 'Special offers') }}" class="form-input"></div>
                    <div><label class="form-label">Headline — highlighted part</label><input type="text" name="deal_title_accent" value="{{ old('deal_title_accent', $s['deal_title_accent'] ?? 'ending soon') }}" class="form-input"></div>
                    <div class="md:col-span-2"><label class="form-label">Text</label><textarea name="deal_text" rows="2" class="form-input">{{ old('deal_text', $s['deal_text'] ?? '') }}</textarea></div>
                    <div><label class="form-label">Button label</label><input type="text" name="deal_button" value="{{ old('deal_button', $s['deal_button'] ?? 'Shop the deals') }}" class="form-input"></div>
                    <div><label class="form-label">Button link</label><input type="url" name="deal_link" value="{{ old('deal_link', $s['deal_link'] ?? '') }}" placeholder="{{ route('shop.index', ['on_sale' => 1]) }}" class="form-input @error('deal_link') border-red-400 @enderror"><p class="text-gray-400 text-xs mt-1.5">Blank = all products on sale.</p></div>
                    <div><label class="form-label">Countdown ends at</label><input type="datetime-local" name="deal_ends_at" value="{{ old('deal_ends_at', !empty($s['deal_ends_at']) ? str_replace(' ', 'T', $s['deal_ends_at']) : '') }}" class="form-input"><p class="text-gray-400 text-xs mt-1.5">Blank or in the past = end of the current week (resets automatically).</p></div>
                </div>
            <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#ff4d7d,#ff8a5b);box-shadow:0 8px 24px rgba(255,77,125,.3)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save banner
            </button>
        </form>
    </div>

    {{-- ───────────── Promo banner ───────────── --}}
    <div id="promo" class="bg-white rounded-2xl p-6 scroll-mt-24" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#6366f1,#22d3ee);box-shadow:0 8px 24px rgba(99,102,241,.35)">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Home promo banner</h2>
                    <p class="text-gray-500 text-sm">The wide banner on the home page ("Everything you love, delivered to your door") with its two buttons and four perk tiles. You can use placeholders: <code class="bg-slate-100 px-1 rounded">{store}</code> <code class="bg-slate-100 px-1 rounded">{returns}</code> <code class="bg-slate-100 px-1 rounded">{days}</code> <code class="bg-slate-100 px-1 rounded">{hours}</code> <code class="bg-slate-100 px-1 rounded">{free}</code> <code class="bg-slate-100 px-1 rounded">{coverage}</code>.</p>
                </div>
            </div>
            @php $promoOn = ($s['promo_enabled'] ?? '1') === '1'; @endphp
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $promoOn ? 'text-green-700 bg-green-50' : 'text-gray-600 bg-gray-100' }} whitespace-nowrap">
                <span class="w-1.5 h-1.5 rounded-full inline-block {{ $promoOn ? 'bg-green-500' : 'bg-gray-400' }}"></span>{{ $promoOn ? 'Visible' : 'Hidden' }}
            </span>
        </div>
        <form method="POST" action="{{ route('admin.content.update', 'promo') }}">
            @csrf @method('PUT')
            <label class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                <input type="checkbox" name="promo_enabled" value="1" class="w-5 h-5 rounded text-indigo-600" {{ $promoOn ? 'checked' : '' }}>
                <div><div class="font-semibold text-gray-800 text-sm">Show the banner</div><div class="text-gray-500 text-xs">The free-delivery pill above the headline fills in automatically from Shipping &amp; delivery.</div></div>
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div><label class="form-label">Headline</label><input type="text" name="promo_title" value="{{ old('promo_title', $s['promo_title'] ?? '') }}" class="form-input @error('promo_title') border-red-400 @enderror">@error('promo_title')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror</div>
                <div><label class="form-label">Headline — highlighted line</label><input type="text" name="promo_title_accent" value="{{ old('promo_title_accent', $s['promo_title_accent'] ?? '') }}" class="form-input"></div>
                <div class="md:col-span-2"><label class="form-label">Text</label><textarea name="promo_text" rows="2" class="form-input">{{ old('promo_text', $s['promo_text'] ?? '') }}</textarea></div>
                <div><label class="form-label">Button 1 label <span class="text-gray-400 font-normal">(pink)</span></label><input type="text" name="promo_btn1_label" value="{{ old('promo_btn1_label', $s['promo_btn1_label'] ?? '') }}" placeholder="Blank to hide" class="form-input"></div>
                <div><label class="form-label">Button 1 link</label><input type="url" name="promo_btn1_link" value="{{ old('promo_btn1_link', $s['promo_btn1_link'] ?? '') }}" placeholder="{{ route('shop.index') }}" class="form-input"></div>
                <div><label class="form-label">Button 2 label <span class="text-gray-400 font-normal">(outline)</span></label><input type="text" name="promo_btn2_label" value="{{ old('promo_btn2_label', $s['promo_btn2_label'] ?? '') }}" placeholder="Blank to hide" class="form-input"></div>
                <div><label class="form-label">Button 2 link</label><input type="url" name="promo_btn2_link" value="{{ old('promo_btn2_link', $s['promo_btn2_link'] ?? '') }}" placeholder="{{ route('shop.index', ['on_sale' => 1]) }}" class="form-input"></div>
            </div>
            <h3 class="font-semibold text-gray-700 mb-1 pb-2 border-b border-gray-100">Perk tiles</h3>
            <p class="text-xs text-gray-400 mb-4">Up to four. Leave a title blank to hide that tile. Icons are Font Awesome names, e.g. <code class="bg-slate-100 px-1 rounded">fa-truck-fast</code>, <code class="bg-slate-100 px-1 rounded">fa-shield-halved</code>, <code class="bg-slate-100 px-1 rounded">fa-headset</code>, <code class="bg-slate-100 px-1 rounded">fa-rotate-left</code>, <code class="bg-slate-100 px-1 rounded">fa-money-bill-wave</code>, <code class="bg-slate-100 px-1 rounded">fa-gift</code>, <code class="bg-slate-100 px-1 rounded">fa-star</code>.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @for($k = 1; $k <= 4; $k++)
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-9 h-9 rounded-lg bg-brand-grad flex items-center justify-center text-white text-sm"><i class="fa-solid {{ $s['promo_perk'.$k.'_icon'] ?? 'fa-circle-check' }}"></i></span>
                        <span class="text-sm font-semibold text-gray-700">Tile {{ $k }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div><label class="form-label text-xs">Icon</label><input type="text" name="promo_perk{{ $k }}_icon" value="{{ old('promo_perk'.$k.'_icon', $s['promo_perk'.$k.'_icon'] ?? '') }}" placeholder="fa-truck-fast" class="form-input text-sm font-mono"></div>
                        <div><label class="form-label text-xs">Title</label><input type="text" name="promo_perk{{ $k }}_title" value="{{ old('promo_perk'.$k.'_title', $s['promo_perk'.$k.'_title'] ?? '') }}" class="form-input text-sm"></div>
                        <div><label class="form-label text-xs">Sub-text</label><input type="text" name="promo_perk{{ $k }}_text" value="{{ old('promo_perk'.$k.'_text', $s['promo_perk'.$k.'_text'] ?? '') }}" class="form-input text-sm"></div>
                    </div>
                </div>
                @endfor
            </div>
            <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#6366f1,#22d3ee);box-shadow:0 8px 24px rgba(99,102,241,.3)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save promo banner
            </button>
        </form>
    </div>

    {{-- ───────────── FAQs ───────────── --}}
    <div id="faqs" class="bg-white rounded-2xl p-6 scroll-mt-24" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#0ea5e9,#22d3ee);box-shadow:0 8px 24px rgba(14,165,233,.35)">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Contact page FAQs</h2>
                <p class="text-gray-500 text-sm">One question per line as <code class="bg-slate-100 px-1 rounded">Question? | Answer</code>. Leave empty to use the built-in FAQs, which already use your delivery, returns and payment settings.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.content.update', 'faqs') }}">
            @csrf @method('PUT')
                <textarea name="store_faqs" rows="7" class="form-input font-mono text-xs mb-4" placeholder="How long does delivery take? | Orders are packed within 24 hours and delivered in 2–5 business days.&#10;Can I pay cash on delivery? | Yes — pay the rider when your order arrives.">{{ old('store_faqs', $s['store_faqs'] ?? '') }}</textarea>
            @php $preview = zfaqs(); @endphp
            <details class="mb-6 rounded-xl border border-slate-200">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-gray-700">Preview — what the contact page shows right now ({{ count($preview) }} questions)</summary>
                <div class="px-4 pb-4 divide-y divide-slate-100">
                    @foreach($preview as [$q,$a])
                    <div class="py-2.5"><p class="text-sm font-semibold text-gray-800">{{ $q }}</p><p class="text-xs text-gray-500 mt-0.5">{{ $a }}</p></div>
                    @endforeach
                </div>
            </details>
            <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#0ea5e9,#22d3ee);box-shadow:0 8px 24px rgba(14,165,233,.3)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save FAQs
            </button>
        </form>
    </div>

    {{-- ───────────── Login & register pages ───────────── --}}
    <div id="auth" class="bg-white rounded-2xl p-6 scroll-mt-24" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white bg-brand-grad" style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.35)">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Login &amp; register pages</h2>
                <p class="text-gray-500 text-sm">The headline, intro and the four perk cards on the left of the login, register and password pages. Placeholders: <code class="bg-slate-100 px-1 rounded">{store}</code> <code class="bg-slate-100 px-1 rounded">{days}</code> <code class="bg-slate-100 px-1 rounded">{returns}</code> <code class="bg-slate-100 px-1 rounded">{free}</code> <code class="bg-slate-100 px-1 rounded">{coverage}</code> <code class="bg-slate-100 px-1 rounded">{hours}</code> are filled from your settings. Leave a perk title empty to hide that card.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.content.update', 'auth') }}">
            @csrf @method('PUT')
            <div class="grid md:grid-cols-2 gap-5 mb-5">
                <div><label class="form-label">Headline</label><input type="text" name="auth_title" class="form-input" value="{{ old('auth_title', $s['auth_title'] ?? '') }}" maxlength="90" required><p class="text-xs text-gray-400 mt-1">The last part of the headline is highlighted automatically.</p></div>
                <div><label class="form-label">Intro text</label><input type="text" name="auth_text" class="form-input" value="{{ old('auth_text', $s['auth_text'] ?? '') }}" maxlength="200"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                @for($i = 1; $i <= 4; $i++)
                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-gray-500 mb-3">Perk {{ $i }}</p>
                    <div class="grid grid-cols-[110px_1fr] gap-3 mb-3">
                        <div><label class="form-label">Icon</label><input type="text" name="auth_perk{{ $i }}_icon" class="form-input font-mono text-xs" value="{{ old("auth_perk{$i}_icon", $s["auth_perk{$i}_icon"] ?? '') }}" placeholder="fa-truck-fast"></div>
                        <div><label class="form-label">Title</label><input type="text" name="auth_perk{{ $i }}_title" class="form-input" value="{{ old("auth_perk{$i}_title", $s["auth_perk{$i}_title"] ?? '') }}" maxlength="40"></div>
                    </div>
                    <div><label class="form-label">Text</label><input type="text" name="auth_perk{{ $i }}_text" class="form-input" value="{{ old("auth_perk{$i}_text", $s["auth_perk{$i}_text"] ?? '') }}" maxlength="80"></div>
                </div>
                @endfor
            </div>
            <p class="text-xs text-gray-400 mb-4">Icons are <a href="https://fontawesome.com/search?o=r&m=free&s=solid" target="_blank" rel="noopener" class="underline">Font Awesome solid</a> names, e.g. <code class="bg-slate-100 px-1 rounded">fa-truck-fast</code>, <code class="bg-slate-100 px-1 rounded">fa-shield-halved</code>, <code class="bg-slate-100 px-1 rounded">fa-headset</code>.</p>
            <button type="submit" class="btn-brand inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save login &amp; register content
            </button>
        </form>
    </div>

</div>
@endsection
