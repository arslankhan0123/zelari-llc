@extends('layouts.store')
@section('title', 'Free QR Code and Barcode Generator')
@section('meta_description', 'Free QR code generator (URL, WiFi, vCard, text) and barcode generator — create and download instantly in your browser.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-tools.css') }}?v={{ @filemtime(public_path('css/zl-tools.css')) ?: 1 }}">
<style>
.tl-chips{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin:22px 0 28px}
.tl-chip{border:1.5px solid var(--line-2);background:#fff;border-radius:999px;padding:8px 18px;font-size:13px;font-weight:700;color:var(--ink-2);cursor:pointer;font-family:inherit;transition:.15s}
.tl-chip:hover{border-color:var(--p);color:var(--p)}
.tl-chip.on{background:var(--grad);color:#fff;border-color:transparent}
.tl-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:1200px){.tl-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:860px){.tl-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.tl-grid{grid-template-columns:1fr}}
.tl-card{position:relative;background:#fff;border:1px solid var(--line);border-radius:16px;padding:20px;display:flex;flex-direction:column;gap:10px;transition:transform .25s,box-shadow .25s,border-color .25s}
.tl-badge{position:absolute;top:13px;right:13px;font-size:9.5px;font-weight:800;letter-spacing:.07em;padding:4px 10px;border-radius:999px}
.tl-badge.yes{background:#dcfce7;color:#15803d}
.tl-badge.soon{background:#fef3c7;color:#92400e}
.tl-card:hover{transform:translateY(-4px);border-color:var(--p);box-shadow:0 22px 44px -20px rgba(var(--shadow-rgb),.4)}
.tl-card .ic{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;color:#fff;font-size:19px}
.tl-card b{font-size:15.5px;font-weight:800;color:var(--ink)}
.tl-card p{font-size:12.5px;color:var(--muted);line-height:1.55;margin:0}
.tl-none{display:none;text-align:center;color:var(--muted);padding:40px 0}
</style>
@endpush

@section('content')
@php $cats = collect($tools)->pluck('cat')->filter()->unique()->values(); @endphp
<section class="sec wrap th-sec">
  <div class="th-hero">
    <i class="fa-solid fa-wand-magic-sparkles th-fl f1"></i>
    <i class="fa-solid fa-bolt th-fl f2"></i>
    <i class="fa-solid fa-gear th-fl f3"></i>
    <i class="fa-solid fa-star th-fl f4"></i>
    <div class="th-left">
      <span class="th-pill"><i class="fa-solid fa-bolt"></i> {{ count($tools) }} free tools &mdash; instant results</span>
      <h1>Free <span>QR code</span> and barcode generator</h1>
      <p>Create QR codes and barcodes for links, WiFi, contacts and products — download as image, no sign-up.</p>
    </div>
    <div class="th-right">
      <div class="th-stats">
        <span><i class="fa-solid fa-circle-check"></i> 100% free</span>
        <span><i class="fa-solid fa-circle-check"></i> Instant results</span>
        <span><i class="fa-solid fa-circle-check"></i> Private &amp; secure</span>
      </div>
      <div class="tl-chips th-chips">
        <button type="button" class="tl-chip on" data-cat="all">All</button>
        @foreach($cats as $c)
        <button type="button" class="tl-chip" data-cat="{{ $c }}">{{ ucfirst($c) }}</button>
        @endforeach
      </div>
    </div>
  </div>

  <div class="tl-grid" id="tlGrid">
    @foreach($tools as $slug => $t)
      <a href="{{ ($t['live'] ?? false) ? route('qrtools.show', $slug) : '#' }}" class="tl-card {{ ($t['live'] ?? false) ? '' : 'js-soon' }}" data-cat="{{ $t['cat'] }}" data-name="{{ $t['name'] }}">
        <span class="tl-badge {{ ($t['live'] ?? false) ? 'yes' : 'soon' }}">{{ ($t['live'] ?? false) ? 'LIVE' : 'COMING SOON' }}</span>
        <span class="ic" style="background:{{ $t['color'] }}"><i class="fa-solid {{ $t['icon'] }}"></i></span>
        <b>{{ $t['name'] }}</b>
        <p>{{ $t['desc'] }}</p>
      </a>
    @endforeach
  </div>
  <div class="tl-none" id="tlNone">No tools in this category.</div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  const cards=[...document.querySelectorAll('.tl-card')]; cards.forEach((k,i)=>k.style.setProperty('--i',i));
  document.querySelectorAll('.tl-card.js-soon').forEach(k=>k.addEventListener('click',e=>{
    e.preventDefault();
    zToast('<b>'+k.dataset.name+'</b> is coming soon — we are working on it!','info');
  }));
  @if(session('toolsoon'))
  zToast('<b>{{ session('toolsoon') }}</b> is not available yet — it is coming soon! Here are the tools you can use right now.','info');
  @endif
  document.querySelectorAll('.tl-chip').forEach(c=>c.onclick=()=>{
    document.querySelectorAll('.tl-chip').forEach(x=>x.classList.remove('on'));
    c.classList.add('on');
    let shown=0;
    cards.forEach(k=>{ const ok=c.dataset.cat==='all'||k.dataset.cat===c.dataset.cat; k.style.display=ok?'':'none'; if(ok) shown++; });
    document.getElementById('tlNone').style.display=shown?'none':'block';
  });
})();
</script>
@endpush
