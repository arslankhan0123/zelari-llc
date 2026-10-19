@extends('layouts.store')
@section('title', 'About Us')

@push('styles')
<style>
.ab{padding:60px 0 10px}
.ab-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center}
@media(max-width:992px){.ab-grid{grid-template-columns:1fr;gap:30px}}
.ab-eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--p-soft);color:var(--p);padding:6px 14px;border-radius:999px;font-size:12.5px;font-weight:800;margin-bottom:14px}
.ab h2{font-size:clamp(26px,3.4vw,40px);font-weight:800;letter-spacing:-1px;margin:0 0 14px;line-height:1.15}
.ab h2 span{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.ab p{color:var(--muted);font-size:15.5px;line-height:1.75}
/* 3D scene */
.scene{position:relative;height:420px;perspective:1100px}
.scene-inner{position:absolute;inset:0;transform-style:preserve-3d;transform:rotateX(var(--px,0deg)) rotateY(var(--py,0deg));transition:transform .3s ease-out}
.cube{position:absolute;left:50%;top:50%;width:200px;height:200px;margin:-100px 0 0 -100px;transform-style:preserve-3d;animation:cubeSpin 18s linear infinite}
@keyframes cubeSpin{from{transform:rotateX(-20deg) rotateY(0)}to{transform:rotateX(-20deg) rotateY(360deg)}}
.face{position:absolute;inset:0;border-radius:24px;background:linear-gradient(135deg,rgba(106,61,245,.9),rgba(34,211,238,.75));border:1px solid rgba(255,255,255,.5);display:grid;place-items:center;font-size:56px;color:#fff;backface-visibility:hidden;box-shadow:inset 0 0 40px rgba(255,255,255,.25)}
.face.f{transform:translateZ(100px)}.face.b{transform:rotateY(180deg) translateZ(100px)}.face.r{transform:rotateY(90deg) translateZ(100px)}.face.l{transform:rotateY(-90deg) translateZ(100px)}.face.t{transform:rotateX(90deg) translateZ(100px)}.face.d{transform:rotateX(-90deg) translateZ(100px)}
.scene .orbit{position:absolute;left:50%;top:50%;width:360px;height:360px;margin:-180px 0 0 -180px;border-radius:50%;border:2px dashed rgba(106,61,245,.3);transform:rotateX(70deg);animation:spinSlow 30s linear infinite}
.scene .sat{position:absolute;background:#fff;border-radius:14px;padding:10px 14px;box-shadow:var(--shadow-lg);font-size:13px;font-weight:700;display:flex;align-items:center;gap:8px;animation:floatY 5s ease-in-out infinite;transform:translateZ(120px)}
.scene .sat i{width:28px;height:28px;border-radius:8px;background:var(--grad);color:#fff;display:grid;place-items:center;font-size:12px}
.scene .s1{left:0;top:40px}.scene .s2{right:0;top:120px;animation-delay:-2s}.scene .s3{left:30px;bottom:40px;animation-delay:-3.5s}
.scene .glow{position:absolute;left:50%;top:50%;width:300px;height:300px;margin:-150px 0 0 -150px;border-radius:50%;background:var(--grad);filter:blur(80px);opacity:.35}
.scene .photo{position:absolute;inset:6% 4%;border-radius:30px;object-fit:cover;transform:translateZ(-80px) rotateY(-6deg);box-shadow:var(--shadow-lg);border:6px solid #fff}
.scene .photo-shade{position:absolute;inset:6% 4%;border-radius:30px;background:linear-gradient(160deg,rgba(106,61,245,.35),rgba(34,211,238,.25));transform:translateZ(-79px) rotateY(-6deg)}
/* stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:60px 0}
@media(max-width:768px){.stats{grid-template-columns:repeat(2,1fr)}}
.stat{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:26px 20px;text-align:center;position:relative;overflow:hidden}
.stat::before{content:"";position:absolute;inset:0;background:var(--grad);opacity:0;transition:.3s}
.stat:hover::before{opacity:.06}
.stat b{display:block;font-size:38px;font-weight:800;letter-spacing:-1.5px;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.stat span{font-size:13.5px;color:var(--muted);font-weight:600}
/* values */
.values{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:992px){.values{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.values{grid-template-columns:1fr}}
.val{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:26px;position:relative;overflow:hidden}
.val .ic{width:56px;height:56px;border-radius:16px;background:var(--surface);display:grid;place-items:center;font-size:24px;color:var(--p);margin-bottom:16px;transform-style:preserve-3d;transition:.5s}
.val:hover .ic{background:var(--grad);color:#fff;transform:translateZ(30px) rotateY(360deg)}
.val h4{font-size:17px;font-weight:800;margin:0 0 8px}
.val p{font-size:14px;color:var(--muted);line-height:1.65;margin:0}
/* timeline */
.tl{position:relative;margin:60px 0;padding-left:30px}
.tl::before{content:"";position:absolute;left:9px;top:6px;bottom:6px;width:2px;background:linear-gradient(var(--p),var(--p2))}
.tl-item{position:relative;padding:0 0 26px 20px}
.tl-item::before{content:"";position:absolute;left:-26px;top:4px;width:14px;height:14px;border-radius:50%;background:var(--grad);box-shadow:0 0 0 5px var(--p-soft)}
.tl-item b{display:block;font-size:15px}
.tl-item small{color:var(--p);font-weight:800;font-size:12px;letter-spacing:.04em}
.tl-item p{color:var(--muted);font-size:14px;margin:4px 0 0}
/* cta */
.cta{border-radius:var(--r-lg);background:var(--ink);color:#fff;padding:50px 40px;text-align:center;position:relative;overflow:hidden;margin-top:40px}
.cta::before{content:"";position:absolute;inset:0;background:radial-gradient(500px 250px at 20% 50%,rgba(106,61,245,.6),transparent),radial-gradient(500px 250px at 80% 50%,rgba(34,211,238,.4),transparent)}
.cta h3{font-size:30px;font-weight:800;margin:0 0 10px;position:relative;letter-spacing:-.6px}
.cta p{color:rgba(255,255,255,.75);position:relative;margin:0 0 22px}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', ['id'=>'aboutHero','crumbs'=>[['Home',route('home')],['About',null]],'slides'=>[
  ['title'=>'We are <span>'.e(zstore('name','Zelari')).'</span>','text'=>'A modern online store built around one idea: shopping should be simple, honest and fast.','icon'=>'fa-rocket'],
  ['title'=>'Quality You <span>Can Trust</span>','text'=>'Every product is checked by our team before it reaches our shelves.','icon'=>'fa-gem'],
  ['title'=>'Honest Pricing, <span>Always</span>','text'=>'No hidden fees. No surprise charges. What you see is what you pay.','icon'=>'fa-handshake'],
]])

<section class="ab wrap">
  <div class="ab-grid">
    <div class="reveal">
      <span class="ab-eyebrow"><i class="fa-solid fa-sparkles"></i> Our story</span>
      <h2>A smarter way to shop, <span>built in Pakistan</span></h2>
      <p>{{ zstore('name','Zelari') }} started with a simple frustration: buying good things online shouldn't mean guessing about quality, prices or delivery. So we built a store that gets the basics right — curated products, transparent pricing and delivery you can count on.</p>
      <p>Today we ship across the country with cash on delivery, {{ zship('return_days','7') }}-day returns and a support team that actually replies. We're just getting started.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px">
        <a href="{{ route('shop.index') }}" class="btn-g"><i class="fa-solid fa-bag-shopping"></i> Shop now</a>
        <a href="{{ route('contact') }}" class="btn-o"><i class="fa-solid fa-envelope"></i> Contact us</a>
      </div>
    </div>
    <div class="scene reveal js-scene" data-delay="1">
      <div class="scene-inner">
        <div class="glow"></div>
        <img class="photo" src="{{ zbg('about',1200) }}" alt="">
        <div class="photo-shade"></div>
        <div class="orbit"></div>
        <div class="cube">
          <div class="face f">🛍️</div><div class="face b">🚚</div><div class="face r">💜</div><div class="face l">⚡</div><div class="face t">✨</div><div class="face d">🎁</div>
        </div>
        <div class="sat s1"><i class="fa-solid fa-truck-fast"></i> {{ zship('delivery_days','2–5') }} day delivery</div>
        <div class="sat s2"><i class="fa-solid fa-shield-halved"></i> Secure checkout</div>
        <div class="sat s3"><i class="fa-solid fa-star"></i> 4.9 avg rating</div>
      </div>
    </div>
  </div>

  <div class="stats">
    @foreach([['500','+','Products'],['2,300','+','Happy customers'],['99','%','Satisfaction'],['24','h','Avg. dispatch']] as $i => [$n,$suf,$l])
      <div class="stat reveal" data-delay="{{ $i+1 }}" data-tilt="8"><b><span class="count" data-to="{{ str_replace(',','',$n) }}">0</span>{{ $suf }}</b><span>{{ $l }}</span></div>
    @endforeach
  </div>

  <div class="sec-head reveal"><div><h2 class="sec-title tt-none">What drives us</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">The four things we refuse to compromise on</p></div></div>
  <div class="values">
    @foreach([['fa-handshake','Trust','Clear communication and promises we keep — from product pages to delivery day.'],['fa-gem','Quality','We only list what we would buy ourselves. Every item is checked before it ships.'],['fa-bolt','Speed','Orders packed within 24 hours and delivered in 2–5 business days nationwide.'],['fa-headset','Support','Real people, fast replies — before, during and after your purchase.']] as $i => [$ic,$t,$d])
      <div class="val reveal" data-delay="{{ $i+1 }}" data-tilt="7"><div class="ic"><i class="fa-solid {{ $ic }}"></i></div><h4>{{ $t }}</h4><p>{{ $d }}</p></div>
    @endforeach
  </div>

  <div class="ab-grid" style="margin-top:60px">
    <div class="reveal">
      <span class="ab-eyebrow"><i class="fa-solid fa-timeline"></i> Milestones</span>
      <h2>How we got <span>here</span></h2>
      <p>A short history of a store that keeps growing with its customers.</p>
    </div>
    <div class="tl reveal" data-delay="1">
      <div class="tl-item"><small>DAY ONE</small><b>{{ zstore('name','Zelari') }} launches</b><p>A small catalog, cash on delivery and a promise to keep things honest.</p></div>
      <div class="tl-item"><small>GROWTH</small><b>Nationwide delivery</b><p>Partnerships with couriers bring {{ zship('delivery_days','2–5') }} day delivery to every major city.</p></div>
      <div class="tl-item"><small>TRUST</small><b>{{ zship('return_days','7') }}-day returns & 24/7 support</b><p>Because buying online should feel as safe as buying in person.</p></div>
      <div class="tl-item"><small>TODAY</small><b>Thousands of happy customers</b><p>And a catalog that grows every single week.</p></div>
    </div>
  </div>

  <div class="cta reveal">
    <h3>Ready to explore?</h3>
    <p>Browse the full catalog and find something you'll love.</p>
    <a href="{{ route('shop.index') }}" class="btn-g" style="padding:14px 28px;font-size:15px;position:relative"><i class="fa-solid fa-bag-shopping"></i> Shop all products</a>
  </div>
</section>
@endsection

@push('scripts')
<script>
/* parallax scene */
(function(){ const s=document.querySelector('.js-scene'); if(!s||!matchMedia('(hover:hover)').matches) return; const i=s.querySelector('.scene-inner');
  s.addEventListener('mousemove',e=>{const r=s.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;i.style.setProperty('--px',(-y*18)+'deg');i.style.setProperty('--py',(x*24)+'deg');});
  s.addEventListener('mouseleave',()=>{i.style.setProperty('--px','0deg');i.style.setProperty('--py','0deg');}); })();
/* count-up */
const cio=new IntersectionObserver(es=>es.forEach(e=>{ if(!e.isIntersecting) return; cio.unobserve(e.target); const el=e.target,to=+el.dataset.to,t0=performance.now();
  (function step(t){ const p=Math.min(1,(t-t0)/1400),v=Math.round(to*(1-Math.pow(1-p,3))); el.textContent=v.toLocaleString(); if(p<1) requestAnimationFrame(step); })(t0); }),{threshold:.5});
document.querySelectorAll('.count').forEach(el=>cio.observe(el));
</script>
@endpush
