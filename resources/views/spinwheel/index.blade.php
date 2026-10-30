@extends('layouts.store')
@section('title', 'Spin & Win — Zelari')
@section('meta_description', 'Spin the Zelari prize wheel and win an exclusive discount coupon for your next order.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-tools.css') }}?v={{ @filemtime(public_path('css/zl-tools.css')) ?: 1 }}">
<link rel="stylesheet" href="{{ asset('css/zl-wheel.css') }}?v={{ @filemtime(public_path('css/zl-wheel.css')) ?: 1 }}">
<style>
.sw-card{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 55%,#1e1b4b 100%);border-radius:26px;box-shadow:0 36px 70px -34px rgba(30,27,75,.6);padding:38px;position:relative;overflow:hidden;margin-bottom:28px}
.sw-card::before{content:"";position:absolute;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,rgba(251,191,36,.16),transparent 65%);top:-180px;left:-120px;pointer-events:none}
.sw-card::after{content:"";position:absolute;width:460px;height:460px;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.3),transparent 65%);bottom:-220px;right:-150px;pointer-events:none}
.sw-card .zw-root{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:20px 50px;align-items:center}
.sw-card .zw-left{display:grid;place-items:center}
.sw-card .zw-wheelwrap{width:380px;height:380px}
@media(max-width:820px){.sw-card .zw-root{grid-template-columns:1fr;text-align:center}.sw-card .zw-badge{align-self:center}.sw-card{padding:26px 18px}.sw-card .zw-wheelwrap{width:300px;height:300px}}
/* coupons as rows (not a table) */
.sw-mine{margin-bottom:26px}
.sw-mine h2{font-size:19px;font-weight:800;margin:0 0 14px}
.sw-rows{display:flex;flex-direction:column;gap:10px}
.sw-row{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--line);border-radius:13px;padding:13px 18px;flex-wrap:wrap}
.sw-row.dead{opacity:.55}
.sw-row .c-code{flex:0 0 170px;font-size:15px;font-weight:800;letter-spacing:.05em;color:var(--ink);display:flex;align-items:center;gap:8px}
.sw-row .c-code i{color:var(--p)}
.sw-row .c-benefit{flex:0 0 110px;font-size:13.5px;font-weight:700;color:var(--p)}
.sw-row .c-exp{flex:1;min-width:150px;font-size:12.5px;color:var(--muted)}
.sw-row .c-status{flex:0 0 auto;font-size:10px;font-weight:800;letter-spacing:.06em;padding:4px 10px;border-radius:999px}
.sw-row .c-status.ok{background:#dcfce7;color:#15803d}
.sw-row .c-status.no{background:#fee2e2;color:#b91c1c}
.sw-row .c-copy{flex:0 0 auto;border:1.5px solid var(--line-2);background:#fff;border-radius:9px;padding:7px 13px;font-family:inherit;font-size:12.5px;font-weight:700;color:var(--ink-2);cursor:pointer}
.sw-row .c-copy:hover{border-color:var(--p);color:var(--p)}
.sw-row-new{animation:swNew 1.1s ease}
@keyframes swNew{0%{background:#fffbeb;border-color:#fbbf24}100%{background:#fff}}
@media(max-width:560px){.sw-row{gap:8px 12px}.sw-row .c-code{flex-basis:100%}.sw-row .c-exp{flex-basis:100%;min-width:0}}
</style>
@endpush

@section('content')
<section class="sec wrap th-sec">
  <div class="th-hero">
    <i class="fa-solid fa-dharmachakra th-fl f1"></i>
    <i class="fa-solid fa-gift th-fl f2"></i>
    <i class="fa-solid fa-ticket th-fl f3"></i>
    <i class="fa-solid fa-bolt th-fl f4"></i>
    <div class="th-left">
      <span class="th-pill"><i class="fa-solid fa-bolt"></i> Exclusive offer &mdash; one spin per account</span>
      <h1>Spin the wheel &amp; <span>win a discount</span></h1>
      <p>Log in, give the wheel a spin and get an exclusive coupon saved straight to your account &mdash; ready to use at checkout.</p>
    </div>
    <div class="th-right">
      <div class="th-stats">
        <span><i class="fa-solid fa-circle-check"></i> 100% free</span>
        <span><i class="fa-solid fa-circle-check"></i> Instant coupon</span>
        <span><i class="fa-solid fa-circle-check"></i> Saved to your account</span>
      </div>
    </div>
  </div>

  @auth
  <div class="sw-mine" {{ $myCoupons->count() ? '' : 'hidden' }}>
    <h2>Your coupons</h2>
    <div class="sw-rows">
      @foreach($myCoupons as $c)
        @php $dead = (!$c->active) || ($c->expires_at && $c->expires_at->isPast()) || ($c->usage_limit !== null && $c->used_count >= $c->usage_limit); @endphp
        <div class="sw-row {{ $dead ? 'dead' : '' }}">
          <span class="c-code"><i class="fa-solid fa-ticket"></i> {{ $c->code }}</span>
          <span class="c-benefit">{{ \App\Support\Marketing::couponLabel($c) }}</span>
          <span class="c-exp">{{ $c->expires_at ? ('Valid until '.$c->expires_at->format('d M Y')) : 'No expiry' }}@if($c->min_spend > 0) &middot; min ${{ number_format($c->min_spend,2) }}@endif</span>
          <span class="c-status {{ $dead ? 'no' : 'ok' }}">{{ $dead ? 'Used / Expired' : 'Active' }}</span>
          @if(!$dead)<button type="button" class="c-copy" data-code="{{ $c->code }}"><i class="fa-regular fa-copy"></i> Copy</button>@endif
        </div>
      @endforeach
    </div>
  </div>
  @endauth

  <div class="sw-card">
    @include('shop.partials.wheel-core', ['wcfg' => $wcfg])
  </div>

</section>
@endsection

@push('scripts')
<script src="{{ asset('js/zl-wheel.js') }}?v={{ @filemtime(public_path('js/zl-wheel.js')) ?: 1 }}"></script>
<script>
document.querySelectorAll('.sw-row .c-copy').forEach(function(b){ b.addEventListener('click', function(){ navigator.clipboard.writeText(b.dataset.code); var o=b.innerHTML; b.innerHTML='<i class="fa-solid fa-check"></i> Copied'; setTimeout(function(){ b.innerHTML=o; },1500); }); });
</script>
@endpush
