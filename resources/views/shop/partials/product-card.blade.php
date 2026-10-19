@once
@push('styles')
<style>
/* ── Bordered product grid (Fastkart style) ── */
.pgrid{display:grid;grid-template-columns:repeat(5,1fr);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;background:#fff;gap:0}
.pgrid.cols-4{grid-template-columns:repeat(4,1fr)}
.pgrid.cols-6{grid-template-columns:repeat(6,1fr)}
@media(min-width:1600px){.pgrid{grid-template-columns:repeat(6,1fr)}}
@media(max-width:1200px){.pgrid,.pgrid.cols-6{grid-template-columns:repeat(4,1fr)}}
@media(max-width:992px){.pgrid,.pgrid.cols-4,.pgrid.cols-6{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.pgrid,.pgrid.cols-4,.pgrid.cols-6{grid-template-columns:repeat(2,1fr)}}
.pgrid .pcard{border-radius:0;border:0;box-shadow:inset -1px -1px 0 var(--line)}
/* ── Card ── */
.pcard{position:relative;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:12px;display:flex;flex-direction:column;height:100%;transition:box-shadow .3s,z-index 0s;z-index:1}
.pcard:hover{box-shadow:0 24px 50px -22px rgba(39,30,92,.35);z-index:3}
.pc-img{position:relative;display:block;aspect-ratio:4/3;border-radius:12px;overflow:hidden;background:radial-gradient(120% 90% at 50% 100%,#eef0fa,#fff 70%);margin-bottom:10px;transform-style:preserve-3d}
.pc-img img{width:100%;height:100%;object-fit:cover;padding:0;border-radius:12px;display:block}
.pc-img img{transition:transform .6s cubic-bezier(.2,.7,.2,1);transform:translateZ(30px)}
.pcard:hover .pc-img img{transform:translateZ(40px) scale(1.08)}
.pc-ph{width:100%;height:100%;display:grid;place-items:center;font-size:42px;color:#c8cce3}
.pc-badge{position:absolute;top:10px;left:10px;z-index:2;font-size:11px;font-weight:800;padding:4px 9px;border-radius:8px;color:#fff;background:var(--sale);letter-spacing:.02em;box-shadow:0 6px 14px -6px rgba(255,77,125,.8)}
.pc-badge.new{background:var(--grad);box-shadow:0 6px 14px -6px rgba(106,61,245,.8)}
.pc-badge.hot{background:linear-gradient(135deg,#ff8a00,#ff4d7d)}
.pc-badge.oos{background:#9aa0bd;box-shadow:none}
.pc-quick{position:absolute;right:10px;top:10px;display:flex;flex-direction:column;gap:6px;z-index:2}
.pc-quick a,.pc-quick button{width:34px;height:34px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;color:var(--ink-2);font-size:13px;opacity:0;transform:translateX(14px) rotateY(60deg);transition:.3s;cursor:pointer;box-shadow:var(--shadow)}
.pc-quick a:nth-child(2),.pc-quick button:nth-child(2){transition-delay:.06s}
.pcard:hover .pc-quick a,.pcard:hover .pc-quick button{opacity:1;transform:none}
.pc-quick a:hover,.pc-quick button:hover{background:var(--p);color:#fff;border-color:var(--p)}
.pc-oos{position:absolute;inset:0;background:rgba(255,255,255,.55);backdrop-filter:grayscale(1);z-index:1}
.pc-body{display:flex;flex-direction:column;flex:1}
.pc-cat{font-size:10.5px;font-weight:700;color:var(--p);letter-spacing:.04em;text-transform:uppercase;margin-bottom:2px}
.pc-name{font-size:13.5px;font-weight:700;color:var(--ink);line-height:1.3;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;min-height:18px;margin-bottom:4px}
.pc-name:hover{color:var(--p)}
.pc-rate{display:flex;align-items:center;gap:6px;margin-bottom:2px}
.pc-rate small{font-size:11px;color:var(--faint)}
.pc-meta{font-size:11.5px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.pc-meta .dot{width:7px;height:7px;border-radius:50%;background:var(--ok);box-shadow:0 0 0 3px rgba(20,184,138,.18)}
.pc-meta .dot.low{background:var(--warn);box-shadow:0 0 0 3px rgba(246,166,9,.18)}
.pc-meta .dot.out{background:var(--faint);box-shadow:none}
.pc-foot{margin-top:auto;display:flex;align-items:center;justify-content:space-between;gap:8px}
.pc-price{display:flex;flex-direction:column;line-height:1.15}
.pc-price b{font-size:15px;font-weight:800;color:var(--ink)}
.pc-price s{font-size:12px;color:var(--faint)}
.pc-add{display:inline-flex;align-items:center;gap:6px;background:var(--p-soft);color:var(--p);border:0;border-radius:9px;padding:7px 11px;font-size:12.5px;font-weight:800;cursor:pointer;transition:.2s;white-space:nowrap}
.pc-add:hover{background:var(--grad);color:#fff;transform:translateY(-2px);box-shadow:0 10px 20px -10px rgba(106,61,245,.8)}
.pc-add:disabled{background:var(--surface);color:var(--faint);cursor:not-allowed;transform:none;box-shadow:none}
.pc-add i{font-size:11px}
.pc-bar{height:5px;border-radius:5px;background:var(--surface);overflow:hidden;margin:2px 0 10px}
.pc-bar i{display:block;height:100%;background:var(--grad);border-radius:5px}
</style>
@endpush
@endonce

@php
    /** @var \App\Models\Product $product */
    $img      = $product->display_image;
    $real     = $product->has_real_image;
    $price    = $product->price;
    $compare  = $product->compare_price;
    $disc     = $product->discount_percent;
    $isNew    = $product->created_at && $product->created_at->gt(now()->subDays(14));
    $qty      = (int) $product->quantity;
    $badge    = $badge ?? ($qty <= 0 ? 'oos' : ($disc ? 'sale' : ($isNew ? 'new' : null)));
    $reviews  = ($product->id * 7) % 60 + 12;      // deterministic decorative count
    $starsN   = 4 + (($product->id % 3) ? 1 : 0);  // 4 or 5 stars
    $showBar  = $showBar ?? false;
@endphp

<div class="pcard" data-tilt="6">
  <a href="{{ route('shop.show', $product->slug) }}" class="pc-img">
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="{{ $real ? '' : 'cover' }}" onerror="this.onerror=null;this.src='{{ zcat_image(null,(int)$product->id) }}'">
    @if($qty <= 0)<span class="pc-oos"></span>@endif
    @if($badge === 'oos')<span class="pc-badge oos">SOLD OUT</span>
    @elseif($badge === 'sale')<span class="pc-badge">-{{ $disc }}%</span>
    @elseif($badge === 'new')<span class="pc-badge new">NEW</span>
    @elseif($badge === 'hot')<span class="pc-badge hot"><i class="fa-solid fa-fire"></i> HOT</span>
    @endif
  </a>
  <div class="pc-quick">
    <a href="{{ route('shop.show', $product->slug) }}" title="Quick view"><i class="fa-regular fa-eye"></i></a>
    <button type="button" title="Wishlist" onclick="this.firstElementChild.className='fa-solid fa-heart';this.style.color='var(--sale)';zToast('Saved to wishlist','info')"><i class="fa-regular fa-heart"></i></button>
  </div>

  <div class="pc-body">
    @if($product->category)<a href="{{ route('shop.index',['category'=>$product->category->slug]) }}" class="pc-cat">{{ $product->category->name }}</a>@endif
    <a href="{{ route('shop.show', $product->slug) }}" class="pc-name">{{ $product->name }}</a>
    <div class="pc-rate">
      <span class="stars">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$starsN?'off':'' }}"></i>@endfor</span>
      <small>({{ $reviews }})</small>
    </div>
    <div class="pc-meta">
      @if($qty <= 0)<span class="dot out"></span> Out of stock
      @elseif($qty <= 5)<span class="dot low"></span> Only {{ $qty }} left
      @else<span class="dot"></span> In stock
      @endif
      @if($product->sku)<span style="margin-left:auto;color:var(--faint)">{{ $product->sku }}</span>@endif
    </div>
    @if($showBar && $qty > 0)
      @php $sold = ($product->id * 13) % 70 + 15; @endphp
      <div class="pc-bar"><i style="width:{{ $sold }}%"></i></div>
    @endif
    <div class="pc-foot">
      <div class="pc-price">
        <b>{{ zprice($price) }}</b>
        @if($compare)<s>{{ zprice($compare) }}</s>@endif
      </div>
      <form action="{{ route('cart.add') }}" method="POST" class="js-atc">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="pc-add" {{ $qty <= 0 ? 'disabled' : '' }}>Add <i class="fa-solid fa-plus"></i></button>
      </form>
    </div>
  </div>
</div>
