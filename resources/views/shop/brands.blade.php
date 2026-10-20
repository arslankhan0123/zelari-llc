@extends('layouts.store')
@section('title', 'Brands')

@push('styles')
<style>
.bgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:1100px){.bgrid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:800px){.bgrid{grid-template-columns:repeat(2,1fr);gap:12px}}
.bcard{display:flex;flex-direction:column;background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden;transition:transform .25s,box-shadow .25s,border-color .25s;height:100%}
.bcard:hover{transform:translateY(-4px);border-color:transparent;box-shadow:0 22px 40px -24px rgba(var(--shadow-rgb),.5)}
.bcard .logo{position:relative;height:140px;display:grid;place-items:center;padding:22px;background:linear-gradient(180deg,#fff, var(--surface))}
.bcard .logo img{max-width:100%;max-height:100%;object-fit:contain}
.bcard .mono{width:64px;height:64px;border-radius:18px;display:grid;place-items:center;background:var(--brand-grad);color:#fff;font-weight:800;font-size:22px;letter-spacing:-.5px}
.bcard .star{position:absolute;top:12px;right:12px;display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:800;letter-spacing:.04em;color:#8a6100;background:#fff5d6;border:1px solid #ffe9a8;padding:4px 9px;border-radius:999px}
.bcard .body{padding:16px 18px 18px;display:flex;flex-direction:column;flex:1;border-top:1px solid var(--line)}
.bcard h3{font-size:16px;font-weight:800;letter-spacing:-.3px;margin:0 0 4px;display:flex;align-items:center;gap:8px}
.bcard .meta{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:9px}
.bcard .meta i{color:var(--faint)}
.bcard p{font-size:13.5px;color:var(--muted);line-height:1.6;margin:0 0 14px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.bcard .foot{margin-top:auto;display:flex;align-items:center;justify-content:space-between;gap:10px}
.bcard .cnt{font-size:12px;font-weight:700;color:var(--ink-2);background:var(--surface);border:1px solid var(--line);padding:5px 10px;border-radius:999px}
.bcard .go{font-size:13px;font-weight:800;color:var(--brand);display:inline-flex;align-items:center;gap:6px}
.bcard .go:hover{text-decoration:underline}
.bempty{text-align:center;padding:70px 20px;background:var(--surface);border-radius:var(--r-lg)}
.bempty h3{font-weight:800;margin:10px 0 6px}
.bempty p{color:var(--muted);margin-bottom:18px}
.bcount{color:var(--muted);font-size:14px;margin-bottom:18px}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id'     => 'brandHero',
  'crumbs' => [['Home', route('home')], ['Brands', null]],
  'slides' => [
    ['title' => 'The brands we <span>stock</span>', 'text' => 'Every manufacturer available at ' . zstore('name', 'Zelari') . ' — browse by the names you already trust.', 'icon' => 'fa-award'],
  ],
])

<section class="sec wrap">
  @if($brands->count())
    <p class="bcount">{{ $brands->count() }} {{ Str::plural('brand', $brands->count()) }} available</p>

    <div class="bgrid">
      @foreach($brands as $b)
      <a class="bcard" href="{{ route('shop.index', ['brand' => $b->slug]) }}">
        <div class="logo">
          @if($b->featured)<span class="star"><i class="fa-solid fa-star"></i> FEATURED</span>@endif
          @if($b->hasLogo())
            <img src="{{ $b->logo_url }}" alt="{{ $b->name }}" loading="lazy">
          @else
            <span class="mono">{{ $b->initials }}</span>
          @endif
        </div>
        <div class="body">
          <h3>{{ $b->name }}</h3>
          @if($b->country || $b->established_year)
          <div class="meta">
            @if($b->country)<span><i class="fa-solid fa-location-dot"></i> {{ $b->country }}</span>@endif
            @if($b->country && $b->established_year)·@endif
            @if($b->established_year)<span><i class="fa-regular fa-calendar"></i> Since {{ $b->established_year }}</span>@endif
          </div>
          @endif
          @if($b->description)<p>{{ $b->description }}</p>@endif
          <div class="foot">
            <span class="cnt">{{ $b->products_count }} {{ Str::plural('product', $b->products_count) }}</span>
            <span class="go">Shop brand <i class="fa-solid fa-arrow-right"></i></span>
          </div>
        </div>
      </a>
      @endforeach
    </div>
  @else
    <div class="bempty">
      <i class="fa-solid fa-award" style="font-size:52px;color:var(--faint)"></i>
      <h3>No brands yet</h3>
      <p>We're busy adding the manufacturers we work with. In the meantime, browse everything in the shop.</p>
      <a href="{{ route('shop.index') }}" class="btn-brand"><i class="fa-solid fa-bag-shopping"></i> Browse products</a>
    </div>
  @endif
</section>
@endsection
