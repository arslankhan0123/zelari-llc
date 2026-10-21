@extends('layouts.store')
@section('title', 'Brands')

@push('styles')
<style>
/* ── stats strip ── */
.bstats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin:-34px 0 44px;position:relative;z-index:3}
@media(max-width:860px){.bstats{grid-template-columns:repeat(2,1fr)}}
.bstat{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 18px 40px -32px rgba(var(--shadow-rgb),.55)}
.bstat i{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:var(--brand-soft);color:var(--p);font-size:16px;flex:none}
.bstat b{display:block;font-size:22px;line-height:1.1;letter-spacing:-.5px}
.bstat small{color:var(--muted);font-size:12.5px}

/* ── brand grid ── */
.bgrid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
@media(max-width:1180px){.bgrid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:860px){.bgrid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}}
@media(max-width:480px){.bgrid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}}
.bcard{position:relative;display:flex;flex-direction:column;background:#EFF6FF;border:1px solid #DBEAFE;border-radius:var(--r-lg);overflow:hidden;transition:transform .3s cubic-bezier(.2,.7,.3,1),box-shadow .3s,border-color .3s}
.bcard:hover{transform:translateY(-6px);border-color:#93C5FD;box-shadow:0 28px 50px -26px rgba(var(--shadow-rgb),.5)}
.bcard .logo{position:relative;height:132px;display:grid;place-items:center;padding:24px;background:linear-gradient(180deg,#fff 28%,#EFF6FF);overflow:hidden}
.bcard .logo img{max-width:100%;max-height:100%;object-fit:contain;transition:transform .35s cubic-bezier(.2,.7,.3,1)}
/* ── motion: each card drifts and catches the light on its own beat ── */
.bcard .lw{display:grid;place-items:center;max-width:100%;max-height:100%;animation:bdrift 6.5s ease-in-out infinite;animation-delay:calc(var(--i,0)*-1.3s)}
@keyframes bdrift{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.bcard .logo::after{content:'';position:absolute;top:-70%;left:-130%;width:55%;height:240%;pointer-events:none;background:linear-gradient(90deg,transparent,rgba(255,255,255,.85),transparent);transform:rotate(18deg);animation:bsheen 7s ease-in-out infinite;animation-delay:calc(var(--i,0)*1.15s)}
@keyframes bsheen{0%{left:-130%}26%{left:165%}100%{left:165%}}
.bcard:hover .logo img,.bcard:hover .mono{transform:scale(1.07)}
.bcard .mono{transition:transform .35s cubic-bezier(.2,.7,.3,1);width:62px;height:62px;border-radius:18px;display:grid;place-items:center;background:var(--brand-grad);color:#fff;font-weight:800;font-size:21px;letter-spacing:-.5px}
.bcard .star{position:absolute;top:11px;right:11px;display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:800;letter-spacing:.05em;color:#8a6100;background:#fff5d6;border:1px solid #ffe9a8;padding:4px 8px;border-radius:999px}
.bcard .body{padding:15px 17px 17px;display:flex;flex-direction:column;flex:1;border-top:1px solid #DBEAFE}
.bcard h3{font-size:15.5px;font-weight:800;letter-spacing:-.2px;margin:0 0 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bmeta{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:9px}
.bmeta span{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--ink-2);background:#fff;border:1px solid #DBEAFE;padding:3px 8px;border-radius:999px;max-width:100%}
.bmeta span i{color:var(--faint);font-size:10px}
.bmeta span b{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bcard p{font-size:13px;color:var(--muted);line-height:1.55;margin:0 0 14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.bcard .foot{margin-top:auto;padding-top:12px;border-top:1px dashed #C7DCFB;display:flex;align-items:center;justify-content:space-between;gap:10px}
.bcard .cnt{font-size:11.5px;font-weight:700;color:var(--ink-2)}
.bcard .cnt.zero{color:var(--faint)}
.bcard .go{font-size:12.5px;font-weight:800;color:var(--p);display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.bcard:hover .go i{transform:translateX(3px)}
.bcard .go i{transition:transform .2s}
@media(prefers-reduced-motion:reduce){.bcard .lw,.bcard .logo::after{animation:none}}
@media(max-width:620px){.bcard .logo{height:104px;padding:16px}.bcard .body{padding:12px 13px 14px}.bcard p{display:none}.bcard h3{font-size:14px}}

/* ── partner band ── */
.bpartner{margin-top:52px;position:relative;overflow:hidden;border-radius:var(--r-lg);background:var(--brand-dark);color:#fff;padding:40px 44px;display:flex;align-items:center;justify-content:space-between;gap:30px;flex-wrap:wrap}
.bpartner::after{content:"";position:absolute;width:320px;height:320px;border-radius:50%;background:var(--p);filter:blur(90px);opacity:.35;right:-70px;top:-110px}
.bpartner>*{position:relative;z-index:1}
.bpartner h2{font-size:clamp(22px,2.4vw,30px);letter-spacing:-.7px;margin:0 0 8px;font-weight:800}
.bpartner p{margin:0;color:rgba(255,255,255,.72);font-size:14.5px;line-height:1.6;max-width:520px}
.bpartner .pacts{display:flex;gap:12px;flex-wrap:wrap}
.bp-btn{display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--brand-dark);font-weight:800;font-size:14px;padding:14px 22px;border-radius:13px;white-space:nowrap}
.bp-btn:hover{filter:brightness(.95)}
.bp-btn.ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.22)}
.bp-btn.ghost:hover{background:rgba(255,255,255,.17)}
@media(max-width:620px){.bpartner{padding:28px 22px}.bp-btn{width:100%;justify-content:center}.bpartner .pacts{width:100%}}

.bempty{text-align:center;padding:66px 20px;background:var(--surface);border-radius:var(--r-lg)}
.bempty h3{font-weight:800;margin:12px 0 6px}
.bempty p{color:var(--muted);margin-bottom:18px}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id'     => 'brandHero',
  'crumbs' => [['Home', route('home')], ['Brands', null]],
  'slides' => [
    ['title' => 'The brands we <span>stand behind</span>', 'text' => 'Every manufacturer stocked at ' . zstore('name', 'Zelari') . ' — checked, authorised and backed by our returns policy.', 'icon' => 'fa-award'],
  ],
])

<section class="sec wrap">

  {{-- Trust strip --}}
  <div class="bstats reveal">
    <div class="bstat"><i class="fa-solid fa-award"></i><div><b>{{ $stats['brands'] }}</b><small>{{ Str::plural('Brand', $stats['brands']) }} on board</small></div></div>
    <div class="bstat"><i class="fa-solid fa-box-open"></i><div><b>{{ $stats['products'] }}</b><small>Products available</small></div></div>
    <div class="bstat"><i class="fa-solid fa-earth-asia"></i><div><b>{{ max($stats['countries'], 1) }}</b><small>{{ Str::plural('Country', max($stats['countries'], 1)) }} sourced from</small></div></div>
    <div class="bstat"><i class="fa-solid fa-shield-halved"></i><div><b>100%</b><small>Genuine articles</small></div></div>
  </div>

  {{-- Brands --}}
  <div class="sec-head reveal">
    <div>
      <h2 class="sec-title tt-none">Shop by brand</h2>
      <div class="sec-deco"><i></i><b></b></div>
      <p class="sec-sub">{{ $stats['brands'] }} {{ Str::plural('brand', $stats['brands']) }} · tap any logo to see everything they make</p>
    </div>
    <a href="{{ route('shop.index') }}" class="sec-link">All products <i class="fa-solid fa-arrow-right"></i></a>
  </div>

  @if($brands->count())
    <div class="bgrid reveal">
      @foreach($brands as $b)
      <a class="bcard" style="--i:{{ $loop->index % 4 }}" href="{{ route('shop.index', ['brand' => $b->slug]) }}">
        <div class="logo">
          @if($b->featured)<span class="star"><i class="fa-solid fa-star"></i> FEATURED</span>@endif
          <span class="lw">
            @if($b->hasLogo())
              <img src="{{ $b->logo_url }}" alt="{{ $b->name }}" loading="lazy">
            @else
              <span class="mono">{{ $b->initials }}</span>
            @endif
          </span>
        </div>
        <div class="body">
          <h3>{{ $b->name }}</h3>

          @if($b->country || $b->established_year)
          <div class="bmeta">
            @if($b->country)<span><i class="fa-solid fa-location-dot"></i><b>{{ $b->country }}</b></span>@endif
            @if($b->established_year)<span><i class="fa-regular fa-calendar"></i><b>Since {{ $b->established_year }}</b></span>@endif
          </div>
          @endif

          @if($b->description)<p>{{ $b->description }}</p>@endif

          <div class="foot">
            <span class="cnt {{ $b->products_count ? '' : 'zero' }}">
              {{ $b->products_count ?: 'No' }} {{ Str::plural('product', $b->products_count) }}
            </span>
            <span class="go">Shop brand <i class="fa-solid fa-arrow-right"></i></span>
          </div>
        </div>
      </a>
      @endforeach
    </div>
  @else
    <div class="bempty reveal">
      <i class="fa-solid fa-award" style="font-size:50px;color:var(--faint)"></i>
      <h3>Brands are on the way</h3>
      <p>We're finalising partnerships with the manufacturers we stock. Browse the catalogue in the meantime.</p>
      <a href="{{ route('shop.index') }}" class="btn-brand"><i class="fa-solid fa-bag-shopping"></i> Browse products</a>
    </div>
  @endif

  {{-- Partner with us --}}
  <div class="bpartner reveal">
    <div>
      <h2>Make it. We'll sell it.</h2>
      <p>Are you a manufacturer or distributor? List your products with {{ zstore('name', 'Zelari') }} and reach customers {{ zship('coverage', 'nationwide') }} — we handle the storefront, payments and delivery.</p>
    </div>
    <div class="pacts">
      <a href="{{ route('contact') }}" class="bp-btn"><i class="fa-solid fa-handshake"></i> Become a partner</a>
      @if(zstore('phone'))
        <a href="tel:{{ ztel(zstore('phone')) }}" class="bp-btn ghost"><i class="fa-solid fa-phone"></i> {{ zstore('phone') }}</a>
      @endif
    </div>
  </div>

</section>
@endsection
