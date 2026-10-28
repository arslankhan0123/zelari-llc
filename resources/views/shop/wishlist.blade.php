@extends('layouts.store')
@section('title', 'My Wishlist')

@push('styles')
<style>
.wl-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.wl-chip{border:1.5px solid var(--line-2);background:#fff;border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;color:var(--ink-2);cursor:pointer;font-family:inherit;transition:.15s}
.wl-chip:hover{border-color:var(--p);color:var(--p)}
.wl-chip.on{background:var(--grad);color:#fff;border-color:transparent}
.wl-clear{margin-left:auto;display:inline-flex;align-items:center;gap:8px;border:1.5px solid #fecaca;background:#fff5f5;color:var(--danger);border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.wl-clear:hover{background:var(--danger);color:#fff;border-color:var(--danger)}
.wl-empty{text-align:center;padding:70px 20px;background:var(--surface);border-radius:var(--r-lg)}
.wl-empty i{font-size:44px;color:var(--faint);margin-bottom:14px;display:block}
.wl-empty h3{font-weight:800;margin:0 0 6px;font-size:20px}
.wl-empty p{color:var(--muted);margin:0 0 20px}
.wl-none{display:none;text-align:center;color:var(--muted);padding:40px 0}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id'     => 'wishHero',
  'crumbs' => [['Home', route('home')], ['Wishlist', null]],
  'slides' => [
    ['title' => 'Your <span>wishlist</span>', 'text' => 'Everything you saved for later — in one place. Add them to your cart whenever you are ready.', 'icon' => 'fa-heart'],
  ],
])

<section class="sec wrap">
  @if($products->count())
    <div class="wl-bar reveal">
      <button type="button" class="wl-chip on" data-cat="all">All (<span class="js-wish-count">{{ $products->count() }}</span>)</button>
      @foreach($cats as $c)
        <button type="button" class="wl-chip" data-cat="{{ $c->slug }}">{{ $c->name }}</button>
      @endforeach
      <form method="POST" action="{{ route('wishlist.clear') }}" onsubmit="return confirm('Remove all {{ $products->count() }} products from your wishlist?')">
        @csrf
        <button type="submit" class="wl-clear"><i class="fa-regular fa-trash-can"></i> Clear all</button>
      </form>
    </div>

    <div class="pgrid cols-4 reveal" id="wishGrid">
      @foreach($products as $product)
        <div data-wish-item data-cat="{{ $product->category?->slug }}" style="display:contents">
          @include('shop.partials.product-card', ['product' => $product])
        </div>
      @endforeach
    </div>
    <div class="wl-none" id="wishNone">No wishlist products in this category.</div>
  @endif

  <div class="wl-empty" id="wishEmpty" style="{{ $products->count() ? 'display:none' : '' }}">
    <i class="fa-regular fa-heart"></i>
    <h3>Your wishlist is empty</h3>
    <p>Tap the ♡ on any product to save it here for later.</p>
    <a href="{{ route('shop.index') }}" class="btn-brand"><i class="fa-solid fa-bag-shopping"></i> Browse products</a>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  let cat='all';
  function items(){ return [...document.querySelectorAll('[data-wish-item]')]; }
  function apply(){
    let shown=0;
    items().forEach(w=>{
      const ok = cat==='all' || w.dataset.cat===cat;
      w.style.display = ok ? 'contents' : 'none'; if(ok) shown++;
    });
    const none=document.getElementById('wishNone');
    if(none) none.style.display = (items().length && !shown) ? 'block' : 'none';
  }
  document.querySelectorAll('.wl-chip').forEach(c=>c.onclick=()=>{
    document.querySelectorAll('.wl-chip').forEach(x=>x.classList.remove('on'));
    c.classList.add('on'); cat=c.dataset.cat; apply();
  });
  /* called by the global wishlist JS after a heart removes an item on this page */
  window.zWishEmptyCheck=function(){
    if(!items().length){
      const bar=document.querySelector('.wl-bar'), grid=document.getElementById('wishGrid');
      if(bar) bar.style.display='none'; if(grid) grid.style.display='none';
      const none=document.getElementById('wishNone'); if(none) none.style.display='none';
      document.getElementById('wishEmpty').style.display='';
    } else { apply(); }
  };
})();
</script>
@endpush
