@extends('layouts.store')
@section('title', 'PDF Tools')
@section('meta_description', 'Every tool you need to work with PDFs in one place — merge, split, compress, convert, rotate, unlock and watermark PDFs with just a few clicks.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-tools.css') }}?v={{ @filemtime(public_path('css/zl-tools.css')) ?: 1 }}">
<style>
.tl-head{text-align:center;max-width:760px;margin:0 auto 26px}
.tl-head h1{font-size:clamp(26px,3.4vw,40px);font-weight:800;letter-spacing:-1px;margin:0 0 10px}
.tl-head h1 span{color:var(--p)}
.tl-head p{color:var(--muted);font-size:15.5px;line-height:1.65;margin:0}
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
<section class="sec wrap">
  <div class="tl-head reveal">
    <h1>Every tool you need to work with <span>PDFs</span> in one place</h1>
    <p>All tools are 100% FREE and easy to use! Merge, split, compress, convert, rotate, unlock and watermark PDFs with just a few clicks.</p>
  </div>

  <div class="tl-chips reveal">
    <button type="button" class="tl-chip on" data-cat="all">All</button>
    <button type="button" class="tl-chip" data-cat="organize">Organize PDF</button>
    <button type="button" class="tl-chip" data-cat="optimize">Optimize PDF</button>
    <button type="button" class="tl-chip" data-cat="convert">Convert PDF</button>
    <button type="button" class="tl-chip" data-cat="edit">Edit PDF</button>
    <button type="button" class="tl-chip" data-cat="security">PDF Security</button>
  </div>

  <div class="tl-grid reveal" id="tlGrid">
    @foreach($tools as $slug => $t)
      <a href="{{ ($t['live'] ?? false) ? route('tools.show', $slug) : '#' }}" class="tl-card {{ ($t['live'] ?? false) ? '' : 'js-soon' }}" data-cat="{{ $t['cat'] }}" data-name="{{ $t['name'] }}">
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
  const cards=[...document.querySelectorAll('.tl-card')];
  const rm=matchMedia('(prefers-reduced-motion: reduce)').matches;
  cards.forEach((k,i)=>{ k.style.setProperty('--i',i); k.addEventListener('animationend',()=>{ k.style.animation=rm?'none':('tlDrift '+(4.6+(i%5)*0.5)+'s ease-in-out '+(-(i%8)*0.7)+'s infinite'); },{once:true}); });
  document.querySelectorAll('.tl-card.js-soon').forEach(k=>k.addEventListener('click',e=>{
    e.preventDefault();
    zToast('<b>'+k.dataset.name+'</b> is coming soon — we are working on it! Meanwhile, try one of the LIVE tools.','info');
  }));
  @if(session('toolsoon'))
  zToast('<b>{{ session('toolsoon') }}</b> is not available yet — it is coming soon! Here are all the tools you can use right now.','info');
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
