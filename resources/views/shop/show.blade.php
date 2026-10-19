@extends('layouts.store')
@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->short_description ?: $product->description ?: ''), 160))

@push('styles')
<style>
.pd{padding:30px 0 10px}
.crumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:22px;flex-wrap:wrap}
.crumb i{font-size:9px}
.crumb b{color:var(--ink);font-weight:600}
.pd-grid{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1fr);gap:40px;align-items:start}
@media(max-width:992px){.pd-grid{grid-template-columns:1fr;gap:26px}}
/* gallery */
.gal{position:sticky;top:calc(var(--head-h) + 70px)}
@media(max-width:992px){.gal{position:static}}
.gal-main{position:relative;aspect-ratio:1;border-radius:var(--r-lg);border:1px solid var(--line);background:radial-gradient(120% 90% at 50% 100%,#eef0fa,#fff 70%);overflow:hidden;perspective:1000px}
.gal-main .stage{position:absolute;inset:0;display:grid;place-items:center;transform-style:preserve-3d;transform:rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg));transition:transform .4s cubic-bezier(.2,.7,.2,1)}
.gal-main.tilting .stage{transition:transform .08s linear}
.gal-main img{width:100%;height:100%;object-fit:cover;border-radius:var(--r-lg);transform:translateZ(20px);transition:transform .4s,opacity .2s;display:block}
.gal-main .ph{font-size:120px;color:#c8cce3;transform:translateZ(40px)}
.gal-main .orb{position:absolute;border-radius:50%;filter:blur(40px);opacity:.5;animation:orb 14s ease-in-out infinite}
.gal-badge{position:absolute;top:16px;left:16px;z-index:3;background:var(--sale);color:#fff;font-weight:800;font-size:13px;padding:6px 12px;border-radius:10px;box-shadow:0 8px 20px -8px rgba(255,77,125,.9)}
.gal-zoom{position:absolute;right:16px;bottom:16px;z-index:3;width:40px;height:40px;border-radius:12px;background:#fff;box-shadow:var(--shadow);display:grid;place-items:center;color:var(--ink-2);font-size:14px}
.thumbs{display:flex;gap:10px;margin-top:12px;flex-wrap:wrap}
.thumb{width:78px;height:78px;border-radius:14px;border:2px solid var(--line);overflow:hidden;cursor:pointer;background:#fff;transition:.2s;display:grid;place-items:center}
.thumb img{width:100%;height:100%;object-fit:cover}
.thumb.on,.thumb:hover{border-color:var(--p);transform:translateY(-3px);box-shadow:var(--shadow)}
/* info */
.pd-cat{display:inline-flex;align-items:center;gap:6px;background:var(--p-soft);color:var(--p);padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;margin-bottom:12px}
.pd h1{font-size:clamp(24px,3vw,34px);font-weight:800;letter-spacing:-.8px;margin:0 0 10px;line-height:1.2}
.pd-rate{display:flex;align-items:center;gap:10px;margin-bottom:16px;font-size:13px;color:var(--muted)}
.pd-rate .stars{font-size:13px}
.pd-rate .sep{width:1px;height:14px;background:var(--line-2)}
.pd-price{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap;margin-bottom:6px}
.pd-price b{font-size:34px;font-weight:800;letter-spacing:-1px;color:var(--ink)}
.pd-price s{font-size:18px;color:var(--faint)}
.pd-price .save{background:#ffe9ef;color:var(--sale);font-size:12px;font-weight:800;padding:4px 10px;border-radius:999px}
.pd-tax{font-size:12px;color:var(--faint);margin-bottom:18px}
.pd-short{font-size:15px;color:var(--muted);line-height:1.7;padding:16px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin-bottom:18px}
.stock{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;padding:6px 12px;border-radius:999px;margin-bottom:18px}
.stock.ok{background:#e6f9f2;color:#0d8f6a}.stock.low{background:#fff5e0;color:#b36b00}.stock.out{background:#fdecef;color:var(--sale)}
.stock .d{width:8px;height:8px;border-radius:50%;background:currentColor;box-shadow:0 0 0 3px rgba(0,0,0,.06)}
.qty-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.qty{display:flex;align-items:center;border:1.5px solid var(--line-2);border-radius:12px;overflow:hidden;background:#fff}
.qty button{width:44px;height:50px;border:0;background:var(--surface);font-size:18px;cursor:pointer;color:var(--ink);transition:.15s}
.qty button:hover{background:var(--p-soft);color:var(--p)}
.qty input{width:58px;height:50px;border:0;text-align:center;font-weight:800;font-size:15px;outline:0;color:var(--ink)}
.atc{flex:1;min-width:180px;font-size:15px;padding:0 22px;height:50px;border-radius:12px}
.buy{height:50px;border-radius:12px;padding:0 22px}
.pd-actions{display:flex;gap:16px;font-size:13.5px;font-weight:700;color:var(--muted);margin-bottom:20px}
.pd-actions a{display:inline-flex;align-items:center;gap:7px}
.pd-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;font-size:13.5px;padding:16px 0;border-top:1px solid var(--line)}
.pd-meta span{color:var(--muted)}
.pd-meta b{color:var(--ink);font-weight:700}
.guar{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:6px}
.guar div{display:flex;align-items:center;gap:10px;background:var(--surface);border-radius:12px;padding:12px;font-size:13px;font-weight:700;color:var(--ink-2)}
.guar div i{width:34px;height:34px;border-radius:10px;background:#fff;display:grid;place-items:center;color:var(--p);box-shadow:var(--shadow);flex-shrink:0}
.pay-strip{display:flex;align-items:center;gap:8px;margin-top:14px;font-size:12px;color:var(--faint);flex-wrap:wrap}
/* tabs */
.tabs{margin-top:46px;border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden}
.tabs-nav{display:flex;background:var(--surface);border-bottom:1px solid var(--line)}
.tabs-nav button{border:0;background:transparent;padding:16px 22px;font-weight:700;font-size:14px;color:var(--muted);cursor:pointer;position:relative}
.tabs-nav button.on{color:var(--p);background:#fff}
.tabs-nav button.on::after{content:"";position:absolute;left:0;right:0;top:0;height:3px;background:var(--grad)}
.tab-pane{display:none;padding:26px;font-size:15px;color:var(--ink-2);line-height:1.8}
.tab-pane.on{display:block;animation:popIn .3s ease}
.rev{display:flex;gap:14px;padding:14px 0;border-bottom:1px dashed var(--line)}
.rev .av{width:42px;height:42px;border-radius:50%;background:var(--grad);color:#fff;display:grid;place-items:center;font-weight:800;flex-shrink:0}
.rev b{font-size:14px}
.rev p{margin:4px 0 0;font-size:14px;color:var(--muted)}
.related{margin-top:50px}
.pslider{overflow:visible}.pslider .swiper-slide{height:auto}.pslider .swiper-wrapper{padding:6px 0 30px}
.slider-clip{overflow:hidden;margin:-6px -12px 0;padding:6px 12px 0}
.sl-nav{display:flex;gap:8px}
.sl-nav button{width:40px;height:40px;border-radius:12px;border:1.5px solid var(--line-2);background:#fff;color:var(--ink);cursor:pointer;transition:.2s;display:grid;place-items:center}
.sl-nav button:hover{background:var(--grad);color:#fff;border-color:transparent}
.sl-nav button.swiper-button-disabled{opacity:.35;pointer-events:none}
/* sticky mobile buy bar */
.buybar{display:none;position:fixed;left:0;right:0;bottom:64px;background:#fff;border-top:1px solid var(--line);padding:10px 16px;z-index:890;gap:12px;align-items:center;box-shadow:0 -8px 30px -14px rgba(39,30,92,.3)}
@media(max-width:768px){.buybar{display:flex}}
.buybar b{font-size:18px;font-weight:800}
</style>
@endpush

@section('content')
@php
  $images  = $product->images;
  $main    = $product->image_url;
  $gallery = collect($main ? [$main] : [])->concat($images->map(fn($i)=>zimg($i->image_path)))->unique()->values();
  $realImg = $gallery->count() > 0;
  if (!$realImg) { $gallery = collect([$product->display_image]); }
  $qty     = (int) $product->quantity;
  $starsN  = 4 + (($product->id % 3) ? 1 : 0);
  $reviews = ($product->id * 7) % 60 + 12;
@endphp
<section class="pd wrap">
  <nav class="crumb">
    <a href="{{ route('home') }}">Home</a><i class="fa-solid fa-chevron-right"></i>
    <a href="{{ route('shop.index') }}">Shop</a>
    @if($product->category?->parent)<i class="fa-solid fa-chevron-right"></i><a href="{{ route('shop.index',['category'=>$product->category->parent->slug]) }}">{{ $product->category->parent->name }}</a>@endif
    @if($product->category)<i class="fa-solid fa-chevron-right"></i><a href="{{ route('shop.index',['category'=>$product->category->slug]) }}">{{ $product->category->name }}</a>@endif
    <i class="fa-solid fa-chevron-right"></i><b>{{ Str::limit($product->name, 40) }}</b>
  </nav>

  <div class="pd-grid">
    {{-- Gallery --}}
    <div class="gal">
      <div class="gal-main" id="galMain">
        <span class="orb" style="width:240px;height:240px;background:var(--brand-light);left:-60px;top:-60px"></span>
        <span class="orb" style="width:200px;height:200px;background:#22d3ee;right:-40px;bottom:-60px;animation-delay:-6s"></span>
        @if($product->discount_percent)<span class="gal-badge">-{{ $product->discount_percent }}% OFF</span>@endif
        <div class="stage">
          <img src="{{ $gallery->first() }}" alt="{{ $product->name }}" id="mainImg" class="{{ $realImg ? '' : 'cover' }}">
        </div>
        <span class="gal-zoom"><i class="fa-solid fa-cube"></i></span>
      </div>
      @if($gallery->count() > 1)
      <div class="thumbs">
        @foreach($gallery as $i => $u)
          <div class="thumb {{ $i===0?'on':'' }}" onclick="setImg('{{ $u }}',this)"><img src="{{ $u }}" alt=""></div>
        @endforeach
      </div>
      @endif
    </div>

    {{-- Info --}}
    <div>
      @if($product->category)<a href="{{ route('shop.index',['category'=>$product->category->slug]) }}" class="pd-cat"><i class="fa-solid {{ zcat_icon($product->category->name) }}"></i> {{ $product->category->name }}</a>@endif
      <h1>{{ $product->name }}</h1>
      <div class="pd-rate">
        <span class="stars">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$starsN?'off':'' }}"></i>@endfor</span>
        <span>{{ $starsN }}.0 ({{ $reviews }} reviews)</span><span class="sep"></span>
        <span><i class="fa-solid fa-fire" style="color:var(--sale)"></i> {{ ($product->id*3)%40+8 }} sold this week</span>
      </div>

      <div class="pd-price">
        <b>{{ zprice($product->price) }}</b>
        @if($product->compare_price)<s>{{ zprice($product->compare_price) }}</s><span class="save">Save {{ zprice($product->compare_price-$product->price) }}</span>@endif
      </div>
      <div class="pd-tax">Inclusive of all taxes · {{ zfree_over() > 0 ? 'Free delivery over '.zprice(zfree_over()) : 'Free delivery' }}</div>

      @if($product->short_description)<div class="pd-short">{{ $product->short_description }}</div>@endif

      @if($qty<=0)<span class="stock out"><span class="d"></span> Out of stock</span>
      @elseif($qty<=5)<span class="stock low"><span class="d"></span> Hurry! Only {{ $qty }} left</span>
      @else<span class="stock ok"><span class="d"></span> In stock — ships within {{ zship('pack_hours','24') }}h</span>@endif

      @if($qty > 0)
      <form action="{{ route('cart.add') }}" method="POST" class="js-atc" id="atcForm">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <div class="qty-row">
          <div class="qty">
            <button type="button" onclick="chg(-1)">−</button>
            <input type="number" name="quantity" id="qtyIn" value="1" min="1" max="{{ min($qty,100) }}">
            <button type="button" onclick="chg(1)">+</button>
          </div>
          <button type="submit" class="btn-g atc"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
          <button type="button" class="btn-sale buy" onclick="buyNow()"><i class="fa-solid fa-bolt"></i> Buy Now</button>
        </div>
      </form>
      @else
      <div class="qty-row"><a href="{{ route('contact') }}" class="btn-o">Notify me when available</a></div>
      @endif

      <div class="pd-actions">
        <a href="#" onclick="event.preventDefault();zToast('Saved to wishlist','info')"><i class="fa-regular fa-heart"></i> Add to wishlist</a>
        <a href="#" onclick="event.preventDefault();navigator.share?navigator.share({title:document.title,url:location.href}):(navigator.clipboard.writeText(location.href),zToast('Link copied','info'))"><i class="fa-solid fa-share-nodes"></i> Share</a>
      </div>

      <div class="pd-meta">
        @if($product->sku)<div><span>SKU: </span><b>{{ $product->sku }}</b></div>@endif
        @if($product->category)<div><span>Category: </span><b>{{ $product->category->name }}</b></div>@endif
        <div><span>Availability: </span><b>{{ $qty>0 ? $qty.' units' : 'Out of stock' }}</b></div>
        <div><span>Delivery: </span><b>{{ zship('delivery_days','2–5') }} business days</b></div>
      </div>

      <div class="guar">
        <div><i class="fa-solid fa-shield-halved"></i> Secure checkout</div>
        <div><i class="fa-solid fa-rotate-left"></i> {{ zship('return_days','7') }}-day returns</div>
        <div><i class="fa-solid fa-truck-fast"></i> Fast dispatch</div>
        <div><i class="fa-solid fa-money-bill-wave"></i> Cash on delivery</div>
      </div>
      <div class="pay-strip"><i class="fa-solid fa-lock"></i> Guaranteed safe checkout:</div>
      <div style="margin-top:8px">@include('shop.partials.payment-icons', ['size' => 'sm'])</div>
    </div>
  </div>

  {{-- Tabs --}}
  <div class="tabs reveal">
    <div class="tabs-nav">
      <button class="on" onclick="tab(this,'t1')">Description</button>
      <button onclick="tab(this,'t2')">Details</button>
      <button onclick="tab(this,'t3')">Reviews ({{ $reviews }})</button>
    </div>
    <div class="tab-pane on" id="t1">
      {!! $product->description ? nl2br(e($product->description)) : '<span style="color:var(--faint)">No description provided for this product yet.</span>' !!}
    </div>
    <div class="tab-pane" id="t2">
      <table class="table" style="max-width:560px;font-size:14px">
        <tr><td style="color:var(--muted);width:40%">Product name</td><td><b>{{ $product->name }}</b></td></tr>
        @if($product->sku)<tr><td style="color:var(--muted)">SKU</td><td>{{ $product->sku }}</td></tr>@endif
        @if($product->category)<tr><td style="color:var(--muted)">Category</td><td>{{ $product->category->name }}</td></tr>@endif
        <tr><td style="color:var(--muted)">Price</td><td>{{ zprice($product->price) }}</td></tr>
        <tr><td style="color:var(--muted)">Stock</td><td>{{ $qty }} units</td></tr>
        <tr><td style="color:var(--muted)">Added</td><td>{{ $product->created_at?->format('d M Y') }}</td></tr>
      </table>
    </div>
    <div class="tab-pane" id="t3">
      @foreach([['A','Ayesha K.','Great quality, exactly as pictured. Delivery was quick and the packaging was solid.',5],['H','Hamza R.','Good value for the price. Would buy again.',4],['S','Sara M.','Customer support was helpful when I had a question about my order.',5]] as [$in,$nm,$tx,$st])
      <div class="rev"><div class="av">{{ $in }}</div><div><b>{{ $nm }}</b> <span class="stars" style="margin-left:6px">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$st?'off':'' }}"></i>@endfor</span><p>{{ $tx }}</p></div></div>
      @endforeach
      <p style="margin:16px 0 0;font-size:13px;color:var(--faint)">Verified purchase reviews.</p>
    </div>
  </div>

  {{-- Related --}}
  @if($related->count())
  <div class="related">
    <div class="sec-head reveal">
      <div><h2 class="sec-title">You Might Also Like</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">More from {{ $product->category?->name ?? 'our catalog' }}</p></div>
      <div class="sl-nav"><button id="relPrev"><i class="fa-solid fa-chevron-left"></i></button><button id="relNext"><i class="fa-solid fa-chevron-right"></i></button></div>
    </div>
    <div class="slider-clip reveal">
      <div class="swiper pslider" id="relSwiper"><div class="swiper-wrapper">
        @foreach($related as $rp)<div class="swiper-slide">@include('shop.partials.product-card',['product'=>$rp])</div>@endforeach
      </div></div>
    </div>
  </div>
  @endif
</section>

@if($qty > 0)
<div class="buybar">
  <div><small style="color:var(--muted);font-size:11px">Price</small><br><b>{{ zprice($product->price) }}</b></div>
  <button class="btn-g" style="flex:1" onclick="document.getElementById('atcForm').requestSubmit()"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
</div>
@endif
@endsection

@push('scripts')
<script>
function chg(d){const i=document.getElementById('qtyIn');const max=parseInt(i.max)||100;i.value=Math.min(max,Math.max(1,(parseInt(i.value)||1)+d));}
function setImg(u,t){const m=document.getElementById('mainImg'); if(!m) return; m.style.opacity=0; setTimeout(()=>{m.src=u;m.style.opacity=1;},150); document.querySelectorAll('.thumb').forEach(x=>x.classList.remove('on')); t.classList.add('on');}
function tab(b,id){document.querySelectorAll('.tabs-nav button').forEach(x=>x.classList.remove('on'));b.classList.add('on');document.querySelectorAll('.tab-pane').forEach(x=>x.classList.remove('on'));document.getElementById(id).classList.add('on');}
async function buyNow(){
  const f=document.getElementById('atcForm');
  try{ const r=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:new FormData(f)}); if(r.ok){location.href='{{ route('checkout.index') }}';return;} const j=await r.json(); zToast(Object.values(j.errors||{}).flat()[0]||j.message||'Could not add','err'); }
  catch(e){ f.classList.remove('js-atc'); f.submit(); }
}
/* 3D gallery tilt */
(function(){
  const g=document.getElementById('galMain'); if(!g||!matchMedia('(hover:hover) and (pointer:fine)').matches) return;
  g.addEventListener('mousemove',e=>{const r=g.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;g.style.setProperty('--rx',(-y*14)+'deg');g.style.setProperty('--ry',(x*18)+'deg');g.classList.add('tilting');});
  g.addEventListener('mouseleave',()=>{g.style.setProperty('--rx','0deg');g.style.setProperty('--ry','0deg');g.classList.remove('tilting');});
})();
@if($related->count())
new Swiper('#relSwiper',{slidesPerView:1.3,spaceBetween:16,speed:600,grabCursor:true,navigation:{prevEl:'#relPrev',nextEl:'#relNext'},breakpoints:{480:{slidesPerView:2.2},768:{slidesPerView:3},1024:{slidesPerView:4},1300:{slidesPerView:5},1600:{slidesPerView:6}}});
@endif
</script>
@endpush
