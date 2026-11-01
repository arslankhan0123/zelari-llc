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
.vrev{margin-top:46px}
.vrev h2{font-size:22px;font-weight:800;letter-spacing:-.4px;margin:0 0 18px}
.vrev-grid{display:grid;grid-template-columns:320px 1fr;gap:26px;align-items:start}
@media(max-width:820px){.vrev-grid{grid-template-columns:1fr}}
.vrev-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:22px}
.vrev-score{font-size:46px;font-weight:800;line-height:1;letter-spacing:-1px}
.vrev-stars{color:#f59e0b;font-size:15px;margin:8px 0 4px}
.vrev-count{color:var(--muted);font-size:13px}
.vrev-item{padding:16px 0;border-bottom:1px solid var(--line)}
.vrev-item:last-child{border-bottom:0}
.vrev-item .nm{font-weight:700;font-size:14px}
.vrev-item .rs{color:#f59e0b;font-size:13px;margin-left:8px}
.vrev-item .bd{color:var(--ink-2);font-size:14px;margin:6px 0 0;line-height:1.55}
.vrev-item .dt{color:var(--muted);font-size:12px;margin-top:4px}
.vrev-form label{display:block;font-size:12.5px;font-weight:700;color:var(--ink-2);margin:0 0 5px}
.vrev-form input,.vrev-form textarea,.vrev-form select{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px 12px;font-size:14px;font-family:inherit;margin-bottom:14px;background:#fff}
.vrev-form .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.vrev-form .row{grid-template-columns:1fr}}
.vrev-btn{background:var(--ink);color:#fff;border:0;border-radius:10px;padding:11px 22px;font-weight:700;font-size:14px;cursor:pointer}
.vrev-ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;border-radius:10px;padding:12px 14px;font-size:14px;margin-bottom:16px}
.vrev-err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:12px 14px;font-size:14px;margin-bottom:16px}
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
        @if($vendor->rating_count)
        <span class="vstore-badge"><i class="fa-solid fa-star" style="color:#f59e0b"></i> {{ number_format($vendor->rating_avg,1) }} ({{ $vendor->rating_count }})</span>
        @endif
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
@php
  $reviews = $vendor->reviews()->where('status','approved')->latest()->take(20)->get();
@endphp
<section class="vrev wrap">
  <h2>Customer reviews</h2>
  <div class="vrev-grid">
    <div>
      <div class="vrev-card" style="text-align:center">
        <div class="vrev-score">{{ $vendor->rating_count ? number_format($vendor->rating_avg,1) : '—' }}</div>
        <div class="vrev-stars">
          @for($i=1;$i<=5;$i++)<i class="fa-{{ $i <= round($vendor->rating_avg) ? 'solid' : 'regular' }} fa-star"></i>@endfor
        </div>
        <div class="vrev-count">{{ $vendor->rating_count }} {{ \Illuminate\Support\Str::plural('review', $vendor->rating_count) }}</div>
      </div>

      <div class="vrev-card" style="margin-top:18px">
        <h3 style="font-size:16px;font-weight:800;margin:0 0 14px">Write a review</h3>
        @if(session('vreview_success'))<div class="vrev-ok">{{ session('vreview_success') }}</div>@endif
        @if(session('vreview_error'))<div class="vrev-err">{{ session('vreview_error') }}</div>@endif
        @if($errors->any())<div class="vrev-err">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('shop.vendor.review', $vendor->slug) }}" class="vrev-form">
          @csrf
          <div class="row">
            <div><label>Your name</label><input name="author_name" maxlength="120" required value="{{ old('author_name') }}"></div>
            <div><label>Email</label><input type="email" name="author_email" maxlength="160" required value="{{ old('author_email') }}"></div>
          </div>
          <label>Rating</label>
          <select name="rating" required>
            <option value="5">5 — Excellent</option>
            <option value="4">4 — Good</option>
            <option value="3">3 — Average</option>
            <option value="2">2 — Poor</option>
            <option value="1">1 — Terrible</option>
          </select>
          <label>Review</label>
          <textarea name="body" rows="4" maxlength="2000" required placeholder="Share your experience with this store...">{{ old('body') }}</textarea>
          <button class="vrev-btn" type="submit">Submit review</button>
        </form>
      </div>
    </div>

    <div class="vrev-card">
      @forelse($reviews as $r)
      <div class="vrev-item">
        <span class="nm">{{ $r->author_name }}</span>
        <span class="rs">@for($i=1;$i<=5;$i++)<i class="fa-{{ $i <= $r->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor</span>
        <p class="bd">{{ $r->body }}</p>
        <div class="dt">{{ $r->created_at->format('d M Y') }}</div>
      </div>
      @empty
      <div style="text-align:center;padding:40px 10px;color:var(--muted)">
        <i class="fa-regular fa-comment-dots" style="font-size:26px"></i>
        <p style="margin:10px 0 0;font-size:14px">No reviews yet. Be the first to review this store.</p>
      </div>
      @endforelse
    </div>
  </div>
</section>
@endsection
