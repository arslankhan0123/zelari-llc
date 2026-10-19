@extends('layouts.store')
@section('title', $selectedCategory ? $selectedCategory->name : (request('q') ? 'Search: '.request('q') : 'Shop'))

@push('styles')
<style>
.shop{padding:34px 0 20px}
.shop-grid{display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start}
@media(max-width:992px){.shop-grid{grid-template-columns:1fr}}
/* sidebar */
.side{position:sticky;top:calc(var(--head-h) + 70px)}
@media(max-width:992px){.side{position:static}}
.fcard{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:20px;margin-bottom:16px}
.fcard h5{font-size:15px;font-weight:800;margin:0 0 14px;display:flex;align-items:center;justify-content:space-between}
.fcard h5 i{color:var(--p)}
.fcat a{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:10px;font-size:14px;color:var(--ink-2);font-weight:600;transition:.15s}
.fcat a:hover{background:var(--p-soft);color:var(--p)}
.fcat a.on{background:var(--grad);color:#fff}
.fcat a .n{margin-left:auto;font-size:11px;background:var(--surface);color:var(--muted);padding:2px 8px;border-radius:999px;font-weight:700}
.fcat a.on .n{background:rgba(255,255,255,.25);color:#fff}
.fcat .sub{padding-left:22px}
.fcat .sub a{font-size:13px;padding:6px 10px}
.price-in{display:flex;gap:10px;align-items:center;margin-bottom:12px}
.price-in input{width:100%;border:1.5px solid var(--line-2);border-radius:10px;padding:9px 10px;font-size:13px;outline:0}
.price-in input:focus{border-color:var(--p)}
.chk{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:14px;color:var(--ink-2);cursor:pointer;font-weight:600}
.chk input{width:18px;height:18px;accent-color:var(--p)}
.side-promo{border-radius:var(--r);background:linear-gradient(160deg,var(--brand-dark),var(--brand) 60%,var(--accent));color:#fff;padding:24px;position:relative;overflow:hidden}
.side-promo b{font-size:22px;display:block;line-height:1.1}
.side-promo span{font-size:13px;opacity:.9}
.side-promo .e{position:absolute;right:-10px;bottom:-14px;font-size:84px;transform:rotate(-12deg);animation:floatY 6s ease-in-out infinite}
/* toolbar */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:var(--surface);border-radius:var(--r);padding:12px 16px;margin-bottom:18px}
.toolbar .cnt{font-size:14px;color:var(--muted)}
.toolbar .cnt b{color:var(--ink)}
.toolbar select{border:1.5px solid var(--line-2);border-radius:10px;padding:8px 12px;font-size:13px;font-weight:600;background:#fff;outline:0;color:var(--ink)}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.chips a{display:inline-flex;align-items:center;gap:8px;background:var(--p-soft);color:var(--p);padding:6px 12px;border-radius:999px;font-size:12.5px;font-weight:700}
.chips a i{font-size:11px}
.chips a.clear{background:#fff;border:1px solid var(--line-2);color:var(--muted)}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(min-width:1600px){.grid{grid-template-columns:repeat(5,1fr)}}
@media(max-width:1400px){.grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.grid{grid-template-columns:repeat(2,1fr);gap:10px}}
.empty{text-align:center;padding:70px 20px;background:var(--surface);border-radius:var(--r-lg)}
.empty .e{font-size:64px;margin-bottom:12px;display:inline-block;animation:floatY 5s ease-in-out infinite}
.empty h3{font-weight:800;margin-bottom:6px}
.empty p{color:var(--muted);margin-bottom:18px}
.pager{margin-top:28px;display:flex;justify-content:center}
.filter-toggle{display:none}
@media(max-width:992px){.filter-toggle{display:inline-flex}.side{display:none}.side.open{display:block}}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id' => 'shopHero',
  'crumbs' => [['Home', route('home')], ['Shop', $selectedCategory ? route('shop.index') : null], ...($selectedCategory ? array_merge(array_map(fn($a)=>[$a->name, route('shop.index',['category'=>$a->slug])], $selectedCategory->ancestors()), [[$selectedCategory->name, null]]) : [])],
  'slides' => [
    ['title' => ($selectedCategory ? e($selectedCategory->name) : 'All <span>Products</span>'), 'text' => $selectedCategory ? ($selectedCategory->description ?: 'Everything in '.$selectedCategory->name.', hand-picked and ready to ship.') : 'Browse the full Zelari catalog — '.$products->total().' products, filtered your way.', 'icon' => $selectedCategory ? zcat_icon($selectedCategory->name) : 'fa-bag-shopping'],
    ['title' => 'Weekend <span>Deals Live</span>', 'text' => 'Up to 50% off selected items. Prices refresh every Friday.', 'icon' => 'fa-bolt'],
    ['title' => zfree_over() > 0 ? 'Free Delivery <span>Over '.zprice(zfree_over()).'</span>' : 'Free Delivery <span>On Every Order</span>', 'text' => zship('return_days','7').'-day easy returns, delivery in '.zship('delivery_days','2–5').' business days.', 'icon' => 'fa-truck-fast'],
  ],
])

<section class="shop wrap">
  <div class="shop-grid">

    {{-- ─── Sidebar ─── --}}
    <aside class="side" id="side">
      <form method="GET" action="{{ route('shop.index') }}" id="filterForm">
        @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        <input type="hidden" name="category" id="catInput" value="{{ request('category') }}">

        <div class="fcard">
          <h5>Categories <i class="fa-solid fa-layer-group"></i></h5>
          @php $activePath = $selectedCategory ? array_merge(array_map(fn($a)=>$a->slug, $selectedCategory->ancestors()), [$selectedCategory->slug]) : []; @endphp
          <div class="ctree-item" style="--d:0"><a href="{{ route('shop.index', request()->except(['category','page'])) }}" class="{{ !request('category')?'on':'' }}"><i class="fa-solid fa-table-cells-large"></i> All products</a></div>
          @include('shop.partials.cat-tree', ['items' => $categories, 'activePath' => $activePath, 'depth' => 0])
        </div>

        <div class="fcard">
          <h5>Price <i class="fa-solid fa-sliders"></i></h5>
          <div class="price-in">
            <input type="number" name="min_price" placeholder="Min {{ (int) floor(($priceBounds->min_p ?? 0) * zcur()['rate']) }}" value="{{ request('min_price') }}" min="0">
            <span style="color:var(--faint)">—</span>
            <input type="number" name="max_price" placeholder="Max {{ (int) ceil(($priceBounds->max_p ?? 0) * zcur()['rate']) }}" value="{{ request('max_price') }}" min="0">
          </div>
          <label class="chk"><input type="checkbox" name="in_stock" value="1" {{ request('in_stock')?'checked':'' }} onchange="this.form.submit()"> In stock only</label>
          <label class="chk"><input type="checkbox" name="on_sale" value="1" {{ request('on_sale')?'checked':'' }} onchange="this.form.submit()"> On sale</label>
          <button type="submit" class="btn-g" style="width:100%;margin-top:10px;padding:11px">Apply filters</button>
        </div>
      </form>

      <div class="side-promo"><span>Weekend special</span><b>Up to 50% off</b><span>on selected items</span><br><a href="{{ route('shop.index',['on_sale'=>1]) }}" style="display:inline-block;margin-top:12px;background:#fff;color:var(--p);padding:8px 14px;border-radius:9px;font-size:13px;font-weight:700">Shop deals</a><span class="e">⚡</span></div>
    </aside>

    {{-- ─── Products ─── --}}
    <div>
      @php $hasFilters = request()->hasAny(['category','min_price','max_price','q','in_stock','on_sale']); @endphp
      @if($hasFilters)
      <div class="chips">
        @if(request('q'))<a href="{{ route('shop.index', request()->except(['q','page'])) }}">Search: "{{ request('q') }}" <i class="fa-solid fa-xmark"></i></a>@endif
        @if($selectedCategory)<a href="{{ route('shop.index', request()->except(['category','page'])) }}">{{ $selectedCategory->name }} <i class="fa-solid fa-xmark"></i></a>@endif
        @if(request('min_price')||request('max_price'))<a href="{{ route('shop.index', request()->except(['min_price','max_price','page'])) }}">{{ zcur()['symbol'] }}{{ request('min_price',0) }} – {{ request('max_price','∞') }} <i class="fa-solid fa-xmark"></i></a>@endif
        @if(request('in_stock'))<a href="{{ route('shop.index', request()->except(['in_stock','page'])) }}">In stock <i class="fa-solid fa-xmark"></i></a>@endif
        @if(request('on_sale'))<a href="{{ route('shop.index', request()->except(['on_sale','page'])) }}">On sale <i class="fa-solid fa-xmark"></i></a>@endif
        <a href="{{ route('shop.index') }}" class="clear">Clear all</a>
      </div>
      @endif

      <div class="toolbar">
        <div class="cnt">Showing <b>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</b> of <b>{{ $products->total() }}</b> products</div>
        <div style="display:flex;gap:10px;align-items:center">
          <button type="button" class="btn-o filter-toggle" style="padding:8px 12px" onclick="document.getElementById('side').classList.toggle('open')"><i class="fa-solid fa-filter"></i> Filters</button>
          <form method="GET" action="{{ route('shop.index') }}">
            @foreach(request()->except(['sort','page']) as $k=>$v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <select name="sort" onchange="this.form.submit()">
              <option value="">Sort: Newest</option>
              <option value="price_asc" {{ request('sort')==='price_asc'?'selected':'' }}>Price: Low to High</option>
              <option value="price_desc" {{ request('sort')==='price_desc'?'selected':'' }}>Price: High to Low</option>
              <option value="name_asc" {{ request('sort')==='name_asc'?'selected':'' }}>Name A–Z</option>
              <option value="discount" {{ request('sort')==='discount'?'selected':'' }}>Biggest Discount</option>
            </select>
          </form>
        </div>
      </div>

      @if($products->count())
        <div class="grid">
          @foreach($products as $i => $product)
            <div class="reveal" data-delay="{{ ($i%4)+1 }}">@include('shop.partials.product-card', ['product'=>$product])</div>
          @endforeach
        </div>
        <div class="pager">{{ $products->links('vendor.pagination.store') }}</div>
      @else
        <div class="empty">
          <span class="e">🔍</span>
          <h3>No products match these filters</h3>
          <p>Try a different category, widen the price range, or clear the search.</p>
          <a href="{{ route('shop.index') }}" class="btn-g">Show all products</a>
        </div>
      @endif

      @if(isset($moreProducts) && $moreProducts->count())
      <div style="margin-top:44px">
        <div class="sec-head reveal">
          <div><h2 class="sec-title">More from {{ $parentCategory->name }}</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">Other products across the {{ $parentCategory->name }} category</p></div>
          <a href="{{ route('shop.index',['category'=>$parentCategory->slug]) }}" class="sec-link">View all {{ $parentCategory->name }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="grid">
          @foreach($moreProducts as $i => $product)
            <div class="reveal" data-delay="{{ ($i%4)+1 }}">@include('shop.partials.product-card', ['product'=>$product])</div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>
</section>
@endsection
