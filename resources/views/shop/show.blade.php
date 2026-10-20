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
.rev{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--line)}
.rev:last-of-type{border-bottom:0}
.rev .av{width:34px;height:34px;border-radius:50%;background:var(--grad);color:#fff;display:grid;place-items:center;font-weight:800;font-size:13px;flex-shrink:0}
.rev-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap;line-height:1}
.rev b{font-size:13.5px}
.rev .stars{font-size:11.5px}
.rev p{margin:5px 0 0;font-size:13.5px;color:var(--muted);line-height:1.55}
.rev .rev-title{color:var(--ink);font-weight:700;margin-top:4px;font-size:13.5px}
.rv-vp{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;color:#0d7a52;background:#e6f7f0;padding:2px 7px;border-radius:999px}
.rv-date{font-size:11.5px;color:var(--faint)}
.rv-sum{display:flex;gap:22px;align-items:center;flex-wrap:wrap;padding:14px 18px;background:var(--surface);border-radius:14px;margin-bottom:6px}
.rv-score{display:flex;align-items:center;gap:10px}
.rv-score b{font-size:28px;line-height:1;letter-spacing:-.5px}
.rv-score .stars{font-size:12px;display:block}
.rv-score small{color:var(--muted);font-size:12px;display:block;margin-top:3px}
.rv-bars{display:grid;gap:4px;min-width:200px;max-width:320px;flex:1}
.rv-bar{display:flex;align-items:center;gap:8px;font-size:11.5px;color:var(--muted)}
.rv-bar>span{width:26px;display:inline-flex;align-items:center;gap:3px;color:var(--ink-2);font-weight:700}
.rv-bar>span i{font-size:9px;color:var(--star)}
.rv-bar .track{flex:1;height:5px;border-radius:99px;background:var(--line-2);overflow:hidden;display:block}
.rv-bar .track b{display:block;height:100%;background:var(--star);border-radius:99px}
.rv-bar small{width:16px;text-align:right}
.rv-write{margin-top:22px;padding-top:20px;border-top:1px solid var(--line)}
.rv-write h3{font-size:17px;font-weight:800;margin:0 0 4px}
.rv-sub{font-size:13.5px;color:var(--muted);margin:0 0 14px;max-width:620px}
.rv-form{max-width:640px;display:grid;gap:16px}
.rv-field{display:block}
.rv-label{display:block;font-size:13px;font-weight:700;color:var(--ink-2);margin-bottom:6px}
.rv-label b{color:var(--sale);font-weight:700}
.rv-label span{color:var(--faint);font-weight:500}
.rv-input{display:block;width:100%;border:1.5px solid var(--line-2);border-radius:12px;padding:11px 14px;font:inherit;font-size:14px;outline:0;background:#fff;transition:.2s;color:var(--ink)}
.rv-input::placeholder{color:#b3b8d0}
.rv-input:focus{border-color:var(--brand);box-shadow:0 0 0 4px rgba(var(--shadow-rgb),.12)}
textarea.rv-input{resize:vertical;min-height:110px;line-height:1.6}
.rv-stars{display:inline-flex;flex-direction:row-reverse;gap:5px}
.rv-stars input{position:absolute;opacity:0;width:0;height:0}
.rv-stars label{font-size:26px;line-height:1;color:var(--line-2);cursor:pointer;transition:color .15s,transform .15s}
.rv-stars label:hover,.rv-stars label:hover~label,.rv-stars input:checked~label{color:var(--star)}
.rv-stars label:hover{transform:scale(1.12)}
.rv-stars input:focus-visible+label{outline:2px solid var(--brand);outline-offset:3px;border-radius:4px}
.rv-verify{border:1px solid var(--line);background:var(--surface);border-radius:16px;padding:16px 18px;display:grid;gap:14px}
.rv-vtitle{margin:0;font-size:13px;font-weight:800;color:var(--ink-2);display:flex;align-items:center;gap:8px}
.rv-vtitle i{color:var(--brand)}
.rv-vhint{margin:0;font-size:12px;color:var(--muted);line-height:1.55}
.rv-actions{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.rv-err{color:var(--sale);font-size:12.5px;font-weight:600;margin-top:6px}
.rv-note{display:flex;gap:10px;align-items:flex-start;border-radius:12px;padding:12px 15px;font-size:13.5px;margin-bottom:16px;line-height:1.5;max-width:640px}
.rv-note.ok{background:#e6f9f2;border:1px solid #bdeedb;color:#0d6a4f}
.rv-note.bad{background:#fdecef;border:1px solid #ffc7d4;color:#b4124b}
.rv-note.info{background:var(--surface);border:1px solid var(--line);color:var(--ink-2)}
.rv-link{color:var(--brand);font-weight:700;text-decoration:underline}
.rv-hint{font-size:12.5px;color:var(--muted);display:flex;gap:7px;align-items:center}
.rv-hint i{color:var(--faint)}
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
  $starsN  = $product->stars;                       // real average, 0 when unrated
  $revCount = (int) $product->reviews_count;
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
        <span>@if($revCount){{ number_format((float) $product->reviews_avg, 1) }} ({{ $revCount }} {{ Str::plural('review', $revCount) }})@else<a href="#reviews" style="color:inherit">No reviews yet</a>@endif</span><span class="sep"></span>
        <span><i class="fa-solid fa-fire" style="color:var(--sale)"></i> {{ ($product->id*3)%40+8 }} sold this week</span>
      </div>

      <div class="pd-price">
        <b>{{ zprice($product->price) }}</b>
        @if($product->compare_price)<s>{{ zprice($product->compare_price) }}</s><span class="save">Save {{ zprice($product->compare_price-$product->price) }}</span>@endif
      </div>
      <div class="pd-tax">Inclusive of all taxes · {{ zfree_over() > 0 ? 'Free delivery over '.zprice(zfree_over()) : 'Free delivery' }}</div>

      @if($product->short_description)<div class="pd-short">{{ trim(html_entity_decode(strip_tags($product->short_description), ENT_QUOTES, 'UTF-8')) }}</div>@endif

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
  <div class="tabs reveal" id="reviews">
    <div class="tabs-nav">
      <button class="on" onclick="tab(this,'t1')">Description</button>
      <button onclick="tab(this,'t2')">Details</button>
      <button id="tabReviews" onclick="tab(this,'t3')">Reviews ({{ $revCount }})</button>
    </div>
    <div class="tab-pane on" id="t1">
      @php
        // Descriptions can arrive with HTML (from the import or a rich editor).
        // Strip the markup and show clean text, keeping paragraph breaks.
        $zdesc = trim(preg_replace("/\n{3,}/", "\n\n", html_entity_decode(strip_tags(preg_replace('#<(br|/p|/div|/li)[^>]*>#i', "\n", (string) $product->description)), ENT_QUOTES, 'UTF-8')));
      @endphp
      {!! $zdesc !== '' ? nl2br(e($zdesc)) : '<span style="color:var(--faint)">No description provided for this product yet.</span>' !!}
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
      @if(session('review_success'))
        <div class="rv-note ok"><i class="fa-solid fa-circle-check"></i> {{ session('review_success') }}</div>
      @endif
      @if(session('review_error'))
        <div class="rv-note bad"><i class="fa-solid fa-circle-exclamation"></i> {{ session('review_error') }}</div>
      @endif

      @if($revCount)
      {{-- Summary --}}
      <div class="rv-sum">
        <div class="rv-score">
          <b>{{ number_format((float) $product->reviews_avg, 1) }}</b>
          <span class="stars">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$starsN?'off':'' }}"></i>@endfor</span>
          <small>{{ $revCount }} {{ Str::plural('review', $revCount) }}</small>
        </div>
        <div class="rv-bars">
          @foreach($breakdown as $star => $n)
          <div class="rv-bar">
            <span>{{ $star }}<i class="fa-solid fa-star"></i></span>
            <i class="track"><b style="width:{{ $revCount ? round($n / $revCount * 100) : 0 }}%"></b></i>
            <small>{{ $n }}</small>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- The reviews themselves --}}
      @forelse($reviews as $r)
        <div class="rev">
          <div class="av">{{ $r->initial }}</div>
          <div style="flex:1;min-width:0">
            <div class="rev-head">
              <b>{{ $r->author_name }}</b>
              <span class="stars">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$r->rating?'off':'' }}"></i>@endfor</span>
              @if($r->verified_purchase)<span class="rv-vp"><i class="fa-solid fa-circle-check"></i> Verified</span>@endif
              <span class="rv-date">{{ $r->created_at->format('d M Y') }}</span>
            </div>
            @if($r->title)<p class="rev-title">{{ $r->title }}</p>@endif
            <p>{{ $r->body }}</p>
          </div>
        </div>
      @empty
        <p style="margin:6px 0 0;font-size:14px;color:var(--muted)">No reviews yet — be the first to review this product after your order arrives.</p>
      @endempty

      {{-- Write a review --}}
      <div class="rv-write">
        @if($myReview)
          <div class="rv-note {{ $myReview->isApproved() ? 'ok' : 'info' }}">
            <i class="fa-solid fa-{{ $myReview->isApproved() ? 'circle-check' : 'clock' }}"></i>
            <span>{{ $myReview->isApproved()
                ? 'Your review is published — thank you!'
                : ($myReview->status === 'rejected'
                    ? 'Your review was not published. Please contact us if you think this is a mistake.'
                    : 'Your review has been submitted and is waiting for approval. It appears here once our team checks it.') }}</span>
          </div>
        @else
          <h3>Write a review</h3>
          <p class="rv-sub">
            @if($canReview)
              You bought this product, so your review is marked as a verified purchase. It appears here once our team approves it.
            @else
              Reviews are open to customers who bought this product. Enter your order number and the email you ordered with — both are in your confirmation email.
            @endif
          </p>

          <form method="POST" action="{{ route('reviews.store', $product->slug) }}" class="rv-form">
            @csrf

            {{-- 1 · rating --}}
            <div class="rv-field">
              <label class="rv-label">Your rating <b>*</b></label>
              <div class="rv-stars" id="rvStars">
                @for($i = 5; $i >= 1; $i--)
                  <input type="radio" name="rating" id="rvs{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                  <label for="rvs{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}"><i class="fa-solid fa-star"></i></label>
                @endfor
              </div>
              @error('rating')<div class="rv-err">{{ $message }}</div>@enderror
            </div>

            {{-- 2 · the review itself --}}
            <div class="rv-field">
              <label class="rv-label" for="rvTitle">Headline <span>(optional)</span></label>
              <input id="rvTitle" type="text" name="title" maxlength="120" value="{{ old('title') }}"
                     class="rv-input" placeholder="Sum it up in a few words">
              @error('title')<div class="rv-err">{{ $message }}</div>@enderror
            </div>

            <div class="rv-field">
              <label class="rv-label" for="rvBody">Your review <b>*</b></label>
              <textarea id="rvBody" name="body" rows="5" maxlength="2000" required class="rv-input"
                        placeholder="What did you like or dislike? How was the quality and the delivery?">{{ old('body') }}</textarea>
              @error('body')<div class="rv-err">{{ $message }}</div>@enderror
            </div>

            {{-- 3 · proof of purchase --}}
            @unless($canReview)
            <div class="rv-verify">
              <p class="rv-vtitle"><i class="fa-solid fa-shield-halved"></i> Verify your purchase</p>

              @guest
              <div class="rv-field">
                <label class="rv-label" for="rvName">Your name <b>*</b></label>
                <input id="rvName" type="text" name="name" maxlength="80" required class="rv-input"
                       value="{{ old('name', $sessionOrder->shipping_name ?? '') }}" placeholder="Shown next to your review">
                @error('name')<div class="rv-err">{{ $message }}</div>@enderror
              </div>
              @endguest

              <div class="rv-field">
                <label class="rv-label" for="rvOrder">Order number <b>*</b></label>
                <input id="rvOrder" type="text" name="order_number" maxlength="40" required class="rv-input"
                       value="{{ old('order_number', $sessionOrder->order_number ?? '') }}" placeholder="ORD-XXXXXXXX">
                @error('order_number')<div class="rv-err">{{ $message }}</div>@enderror
              </div>

              <div class="rv-field">
                <label class="rv-label" for="rvEmail">Email used on the order <b>*</b></label>
                <input id="rvEmail" type="email" name="email" maxlength="150" required class="rv-input"
                       value="{{ old('email', $sessionOrder->customer_email ?? (auth()->user()->email ?? '')) }}" placeholder="you@example.com">
                @error('email')<div class="rv-err">{{ $message }}</div>@enderror
              </div>

              <p class="rv-vhint">Both are in your order confirmation email. We only use them to confirm you bought this product.</p>
            </div>
            @endunless

            {{-- 4 · submit --}}
            <div class="rv-actions">
              <button type="submit" class="btn-brand"><i class="fa-regular fa-paper-plane"></i> Submit review</button>
              @guest
                <span class="rv-hint"><i class="fa-regular fa-circle-question"></i> Have an account? <a href="{{ route('login') }}" class="rv-link">Log in</a> and we'll find your orders automatically.</span>
              @endguest
            </div>
          </form>
        @endif
      </div>
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

/* Arriving at #reviews (from the order confirmation, an email or the rating line):
   open the Reviews tab first — the pane is hidden, so the browser cannot scroll to it on its own. */
function zOpenReviews(smooth){
  const btn = document.getElementById('tabReviews');
  if (!btn) return;
  tab(btn, 't3');
  const box = document.getElementById('reviews');
  if (!box) return;
  // clear the sticky header
  const head = document.querySelector('.header');
  const off  = (head ? head.getBoundingClientRect().height : 0) + 18;
  requestAnimationFrame(() => {
    const y = box.getBoundingClientRect().top + window.pageYOffset - off;
    window.scrollTo({ top: y, behavior: smooth ? 'smooth' : 'auto' });
  });
}
if (location.hash === '#reviews') { window.addEventListener('load', () => zOpenReviews(false)); }
window.addEventListener('hashchange', () => { if (location.hash === '#reviews') zOpenReviews(true); });
document.querySelectorAll('a[href="#reviews"]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); zOpenReviews(true); }));
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
