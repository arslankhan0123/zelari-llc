@extends('layouts.store')
@section('title', $vendor->name . ' — Store')

@push('styles')
<style>
.vstore{padding:30px 0 50px}
.vstore-head{display:flex;align-items:center;gap:20px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:24px;margin-bottom:26px;flex-wrap:wrap}
.vstore-logo{width:84px;height:84px;border-radius:18px;object-fit:cover;background:var(--surface);border:1px solid var(--line);flex-shrink:0}
.vstore-meta{min-width:0;flex:1}
.vstore-meta h1{font-size:clamp(22px,3vw,30px);font-weight:800;letter-spacing:-.6px;margin:0 0 4px}
.vstore-meta p{color:var(--muted);font-size:14px;margin:0;max-width:60ch}
.vstore-badges{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
.vstore-badge{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:var(--ink-2);background:var(--surface);border:1px solid var(--line);border-radius:999px;padding:5px 12px}
.vgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
@media(max-width:1100px){.vgrid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:820px){.vgrid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.vgrid{grid-template-columns:1fr}}
.vempty{text-align:center;padding:60px 20px;color:var(--muted)}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id' => 'vendorHero',
  'crumbs' => [['Home', route('home')], ['Shop', route('shop.index')], [$vendor->name, null]],
  'slides' => [[
      'title' => e($vendor->name),
      'text'  => $vendor->description ? \Illuminate\Support\Str::limit(strip_tags($vendor->description), 120) : 'Browse everything sold by '.$vendor->name.' on Zelari.',
      'icon'  => 'fa-store',
  ]],
])

<section class="vstore wrap">
  <div class="vstore-head reveal">
    <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="vstore-logo">
    <div class="vstore-meta">
      <h1>{{ $vendor->name }}</h1>
      @if($vendor->description)<p>{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 200) }}</p>@endif
      <div class="vstore-badges">
        <span class="vstore-badge"><i class="fa-solid fa-box"></i> {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}</span>
        @if($vendor->city)<span class="vstore-badge"><i class="fa-solid fa-location-dot"></i> {{ $vendor->city }}</span>@endif
        <span class="vstore-badge"><i class="fa-solid fa-circle-check" style="color:#16a34a"></i> Verified seller</span>
      </div>
    </div>
  </div>

  @if($products->count())
  <div class="vgrid">
    @foreach($products as $i => $product)
    <div class="reveal" data-delay="{{ ($i%4)+1 }}">@include('shop.partials.product-card', ['product'=>$product])</div>
    @endforeach
  </div>
  <div class="pager" style="margin-top:28px">{{ $products->links('vendor.pagination.store') }}</div>
  @else
  <div class="vempty">
    <h3>No products yet</h3>
    <p>{{ $vendor->name }} hasn't listed any products yet. Check back soon.</p>
  </div>
  @endif
</section>
@endsection
