@extends('layouts.store')
@section('title', 'Our Vendors - Shop by Seller')

@push('styles')
<style>
.vlist{padding:30px 0 55px}
.vgrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
@media(max-width:1000px){.vgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.vgrid{grid-template-columns:1fr}}
.vcard{display:flex;flex-direction:column;gap:14px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:22px;transition:transform .3s cubic-bezier(.2,.7,.3,1),box-shadow .3s,border-color .3s}
.vcard:hover{transform:translateY(-5px);border-color:var(--brand);box-shadow:0 26px 48px -28px rgba(var(--shadow-rgb),.5)}
.vcard-top{display:flex;align-items:center;gap:14px}
.vcard-logo{width:60px;height:60px;border-radius:15px;object-fit:cover;background:var(--surface);border:1px solid var(--line);flex-shrink:0}
.vcard-name{font-size:17px;font-weight:800;letter-spacing:-.3px;margin:0}
.vcard-sub{font-size:12.5px;color:var(--muted);margin:2px 0 0}
.vcard-desc{font-size:13.5px;color:var(--muted);line-height:1.5;margin:0;min-height:40px}
.vcard-foot{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:14px;border-top:1px solid var(--line)}
.vcard-count{font-size:12.5px;font-weight:600;color:var(--ink-2);display:inline-flex;align-items:center;gap:6px}
.vcard-cta{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--brand)}
.vcard:hover .vcard-cta{gap:9px}
.vempty{text-align:center;padding:60px 20px;color:var(--muted)}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', [
  'id' => 'vendorsHero',
  'crumbs' => [['Home', route('home')], ['Vendors', null]],
  'slides' => [[
      'title' => 'Our <span>Vendors</span>',
      'text'  => 'Meet the sellers on Zelari. Browse any store to see everything they offer.',
      'icon'  => 'fa-store',
  ]],
])

<section class="vlist wrap">
  @if($vendors->count())
  <div class="vgrid">
    @foreach($vendors as $vendor)
    <a href="{{ route('shop.vendor', $vendor->slug) }}" class="vcard reveal">
      <div class="vcard-top">
        <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="vcard-logo">
        <div style="min-width:0">
          <h3 class="vcard-name">{{ $vendor->name }}</h3>
          <p class="vcard-sub"><i class="fa-solid fa-circle-check" style="color:#16a34a"></i> Verified seller @if($vendor->city)· {{ $vendor->city }}@endif</p>
        </div>
      </div>
      @if($vendor->description)
      <p class="vcard-desc">{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 110) }}</p>
      @endif
      <div class="vcard-foot">
        <span class="vcard-count"><i class="fa-solid fa-box" style="color:var(--brand)"></i> {{ $vendor->products_count }} {{ \Illuminate\Support\Str::plural('product', $vendor->products_count) }}</span>
        <span class="vcard-cta">Visit store <i class="fa-solid fa-arrow-right"></i></span>
      </div>
    </a>
    @endforeach
  </div>
  @else
  <div class="vempty">
    <h3>No vendors yet</h3>
    <p>Approved sellers will appear here. Check back soon.</p>
  </div>
  @endif
</section>
@endsection
