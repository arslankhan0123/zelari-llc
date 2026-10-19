@extends('layouts.store')
@section('title', 'Home')

@push('styles')
<style>
/* ═══════════ HERO ═══════════ */
.hero{padding-top:22px}
.hero-grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:22px}
.hero-grid>*{min-width:0}
@media(max-width:1200px){.hero-grid{grid-template-columns:1fr 280px}}
@media(max-width:992px){.hero-grid{grid-template-columns:1fr}}
.hero-swiper{height:480px;width:100%;min-width:0;border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow-lg)}
@media(max-width:768px){.hero-swiper{height:430px}}
.hs{height:100%;position:relative;overflow:hidden;display:flex;align-items:center;padding:40px 56px}
@media(max-width:768px){.hs{padding:30px 24px;align-items:flex-start}}
.hs-1{background:#1b1740}
.hs-2{background:#12102e}
.hs-3{background:#0b3d5e}
.hs-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transform:scale(1.04);transition:transform 7s ease-out}
.swiper-slide-active .hs-bg{transform:scale(1.14)}
.hs-shade{position:absolute;inset:0;z-index:1;background:linear-gradient(90deg,rgba(10,8,30,.9) 0%,rgba(10,8,30,.78) 38%,rgba(10,8,30,.35) 68%,rgba(10,8,30,.15) 100%),linear-gradient(0deg,rgba(10,8,30,.55) 0%,transparent 40%)}
.hs-orb{display:none;position:absolute;z-index:1;border-radius:50%;filter:blur(50px);opacity:.55;animation:orb 16s ease-in-out infinite;pointer-events:none}
.hs-content{position:relative;z-index:3;max-width:560px}
.hs-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);border-radius:999px;padding:6px 14px 6px 6px;font-size:12.5px;font-weight:700;color:#fff;margin-bottom:18px;backdrop-filter:blur(8px)}
.hs-eyebrow b{background:var(--sale);color:#fff;padding:4px 9px;border-radius:999px;font-size:11px}
.hs h1{font-size:clamp(32px,4.6vw,60px);font-weight:800;line-height:1.05;letter-spacing:-1.8px;margin:0 0 16px;text-transform:uppercase;color:#fff;text-shadow:0 10px 40px rgba(0,0,0,.45)}
.hs h1 span{background:linear-gradient(90deg,#c4b5fd 0%,#8b5cf6 40%,#22d3ee 100%);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 6px 24px rgba(106,61,245,.5))}
.hs p{font-size:16px;color:rgba(255,255,255,.85);max-width:460px;margin:0 0 28px;line-height:1.65;text-shadow:0 2px 12px rgba(0,0,0,.4)}
.hs-ctas{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.hs-ghost{display:inline-flex;align-items:center;gap:8px;color:#fff;font-weight:700;font-size:14.5px;padding:13px 22px;border-radius:12px;border:1.5px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08);backdrop-filter:blur(8px);transition:.2s}
.hs-ghost:hover{background:#fff;color:var(--ink)}
.hs-stats{display:flex;gap:26px;margin-top:30px}
.hs-stats div{color:#fff}
.hs-stats b{display:block;font-size:22px;font-weight:800;letter-spacing:-.5px}
.hs-stats small{font-size:12px;color:rgba(255,255,255,.7);font-weight:600}
@media(max-width:768px){.hs-stats{display:none}}
/* 3D scene */
.h3d{position:absolute;right:4%;top:50%;transform:translateY(-50%);width:380px;height:380px;perspective:1000px;z-index:2}
@media(max-width:1200px){.h3d{width:300px;height:300px;right:3%}}
@media(max-width:768px){.h3d{width:210px;height:210px;right:-10px;top:auto;bottom:-10px;transform:none;opacity:.9}}
.h3d-inner{position:absolute;inset:0;transform-style:preserve-3d;transform:rotateX(var(--px,0deg)) rotateY(var(--py,0deg));transition:transform .25s ease-out}
.h3d .ring{position:absolute;inset:0;border-radius:50%;border:2px dashed rgba(255,255,255,.35);animation:spinSlow 24s linear infinite;transform:translateZ(-40px)}
.h3d .ring2{inset:36px;border-style:solid;border-color:rgba(34,211,238,.5);animation-direction:reverse;animation-duration:18s}
.h3d .disc{position:absolute;inset:60px;border-radius:50%;background:var(--grad);filter:blur(30px);opacity:.7;box-shadow:0 40px 80px -30px rgba(106,61,245,.8);transform:translateZ(-10px);animation:floatY2 7s ease-in-out infinite}
.h3d .glass{position:absolute;inset:50px;border-radius:32px;background:#fff;border:5px solid #fff;backdrop-filter:none;display:grid;place-items:center;transform:translateZ(40px);box-shadow:0 30px 60px -20px rgba(39,30,92,.35);animation:floatY 6s ease-in-out infinite;overflow:hidden}
.h3d .glass img{width:82%;height:82%;object-fit:contain;filter:drop-shadow(0 20px 30px rgba(0,0,0,.25))}
.h3d .glass img.cover{width:100%;height:100%;object-fit:cover;border-radius:27px;filter:none;display:block}
.h3d .glass .emo{font-size:110px;filter:drop-shadow(0 20px 30px rgba(0,0,0,.25))}
.chip{position:absolute;background:#fff;border-radius:14px;padding:10px 14px;box-shadow:var(--shadow-lg);font-size:12.5px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:9px;transform:translateZ(90px);animation:floatY2 5s ease-in-out infinite;white-space:nowrap}
.chip i{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;color:#fff;font-size:12px}
.chip small{display:block;font-weight:600;color:var(--muted);font-size:11px}
.chip.c1{top:14px;left:-30px;animation-delay:-1s}.chip.c1 i{background:var(--grad)}
.chip.c2{right:-40px;top:38%;animation-delay:-2.5s}.chip.c2 i{background:var(--sale)}
.chip.c3{bottom:20px;left:0;animation-delay:-4s}.chip.c3 i{background:var(--ok)}
@media(max-width:768px){.chip{display:none}}
.hero-swiper .swiper-pagination{bottom:18px!important;left:auto!important;right:28px;width:auto!important;text-align:right}
@media(max-width:768px){.hero-swiper .swiper-pagination{right:16px}}
.hero-swiper .swiper-pagination-bullet{width:10px;height:10px;background:#fff;opacity:.45;transition:.3s}
.hero-swiper .swiper-pagination-bullet-active{opacity:1;width:30px;border-radius:6px;background:linear-gradient(90deg,#8b5cf6,#22d3ee)}
.hero-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:5;width:44px;height:44px;border-radius:50%;background:#fff;box-shadow:var(--shadow-lg);display:grid;place-items:center;color:var(--ink);cursor:pointer;opacity:0;transition:.3s;border:0}
.hero-swiper:hover .hero-nav{opacity:1}
.hero-swiper .swiper-slide{overflow:hidden}
.hero-nav.prev{left:16px}.hero-nav.next{right:16px}
.hero-nav:hover{background:var(--p);color:#fff}
/* side banner */
.side-banner{position:relative;height:480px;border-radius:var(--r-lg);overflow:hidden;background:linear-gradient(160deg,var(--brand),var(--brand-dark));color:#fff;padding:30px 28px;display:flex;flex-direction:column;box-shadow:var(--shadow-lg)}
@media(max-width:992px){.side-banner{height:auto;min-height:260px}}
.side-banner .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transition:transform .8s}
.side-banner:hover .bg{transform:scale(1.06)}
.side-banner .shade{position:absolute;inset:0;z-index:0;background:linear-gradient(180deg,rgba(46,27,143,.2) 0%,rgba(46,27,143,.6) 50%,rgba(20,12,70,.94) 100%)}
.side-banner>*:not(.bg):not(.shade){position:relative;z-index:1}
.sb-art .card img.cover{width:100%;height:100%;object-fit:cover;border-radius:22px;filter:none;display:block}
.side-banner .off{font-size:42px;font-weight:800;line-height:1;letter-spacing:-2px}
.side-banner .off small{font-size:14px;font-weight:700;letter-spacing:0;vertical-align:top;margin-left:4px;color:#ffd36b}
.side-banner h3{font-size:24px;font-weight:800;margin:6px 0 8px;line-height:1.15}
.side-banner p{font-size:13px;color:rgba(255,255,255,.8);margin:0 0 16px}
.side-banner .lnk{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:#fff;border-bottom:2px solid #22d3ee;padding-bottom:2px;align-self:flex-start}
.side-banner .lnk:hover{color:#22d3ee}
.sb-art{position:absolute;right:-20px;bottom:-10px;width:220px;height:220px;perspective:800px}
.sb-art .card{position:absolute;inset:30px;border-radius:26px;background:#fff;border:4px solid #fff;overflow:hidden;box-shadow:0 24px 50px -16px rgba(0,0,0,.55);display:grid;place-items:center;transform:rotateX(14deg) rotateY(-18deg);animation:floatY 6s ease-in-out infinite}
.sb-art .card img{width:80%;height:80%;object-fit:contain;filter:drop-shadow(0 16px 24px rgba(0,0,0,.35))}
.sb-art .card span{font-size:70px}
.flash{position:absolute;right:26px;top:26px;width:84px;height:84px;border-radius:50%;background:#ffd36b;color:var(--ink);display:grid;place-items:center;text-align:center;font-weight:800;font-size:11px;line-height:1.1;animation:spinSlow 14s linear infinite;box-shadow:0 14px 30px -10px rgba(0,0,0,.4)}
.flash b{display:block;font-size:15px}

/* ═══════════ CATEGORIES ═══════════ */
.cat-swiper{overflow:hidden;padding:4px 2px 10px}
.cat-swiper .swiper-wrapper{transition-timing-function:linear!important}
.cat-swiper .swiper-slide{height:auto}
.ctile{position:relative;display:block;height:150px;border-radius:16px;overflow:hidden;color:#fff;box-shadow:0 10px 26px -14px rgba(39,30,92,.45);isolation:isolate}
.ctile img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .7s cubic-bezier(.2,.7,.2,1);z-index:0}
.ctile:hover img{transform:scale(1.1)}
.ctile .sh{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(10,8,30,.08) 0%,rgba(10,8,30,.35) 45%,rgba(10,8,30,.88) 100%)}
.ctile .ic{position:absolute;top:12px;left:12px;z-index:2;width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.92);color:var(--p);display:grid;place-items:center;font-size:14px;box-shadow:var(--shadow);transition:.3s}
.ctile:hover .ic{background:var(--grad);color:#fff;transform:rotate(-8deg) scale(1.08)}
.ctile .cnt{position:absolute;top:12px;right:12px;z-index:2;background:var(--sale);color:#fff;font-size:11px;font-weight:800;padding:4px 9px;border-radius:999px;box-shadow:0 6px 14px -6px rgba(255,77,125,.9)}
.ctile .txt{position:absolute;left:14px;right:14px;bottom:12px;z-index:2}
.ctile b{display:block;font-size:16px;font-weight:800;letter-spacing:-.3px;line-height:1.2;text-shadow:0 2px 12px rgba(0,0,0,.6);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ctile small{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:rgba(255,255,255,.9);margin-top:3px;text-shadow:0 1px 8px rgba(0,0,0,.6)}
.ctile small i{font-size:10px;transition:transform .2s}
.ctile:hover small i{transform:translateX(4px)}
.ctile:hover{color:#fff}

/* ═══════════ PROMO BANNER ═══════════ */
.promo-wide{position:relative;overflow:hidden;border-radius:var(--r-lg);min-height:300px;display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:center;padding:40px 44px;color:#fff;box-shadow:var(--shadow-lg)}
@media(max-width:992px){.promo-wide{grid-template-columns:1fr;padding:30px 24px}}
.promo-wide .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transform:scale(1.03);transition:transform 8s ease-out}
.promo-wide:hover .bg{transform:scale(1.1)}
.promo-wide .shade{position:absolute;inset:0;z-index:0;background:linear-gradient(90deg,rgba(14,12,40,.94) 0%,rgba(76,47,217,.82) 45%,rgba(14,165,233,.55) 100%)}
.promo-wide>*:not(.bg):not(.shade){position:relative;z-index:1}
.pw-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:6px 14px;font-size:12.5px;font-weight:700;backdrop-filter:blur(6px);margin-bottom:14px}
.pw-eyebrow i{color:#67e8f9}
.promo-wide h3{font-size:clamp(24px,3.2vw,40px);font-weight:800;letter-spacing:-1px;line-height:1.1;margin:0 0 12px;text-shadow:0 6px 30px rgba(0,0,0,.35)}
.promo-wide h3 span{background:linear-gradient(90deg,#c4b5fd,#67e8f9);-webkit-background-clip:text;background-clip:text;color:transparent}
.promo-wide p{font-size:15px;color:rgba(255,255,255,.85);max-width:520px;margin:0 0 20px;line-height:1.65}
.pw-ctas{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.pw-ghost{display:inline-flex;align-items:center;gap:8px;color:#fff;font-weight:700;font-size:14.5px;padding:12px 20px;border-radius:12px;border:1.5px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08);backdrop-filter:blur(8px);transition:.2s}
.pw-ghost:hover{background:#fff;color:var(--ink)}
.pw-perks{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.pw-perks{grid-template-columns:1fr}}
.pw-perks div{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);backdrop-filter:blur(10px);border-radius:16px;padding:16px;transition:transform .3s,background .3s}
.pw-perks div:hover{transform:translateY(-4px);background:rgba(255,255,255,.18)}
.pw-perks i{width:38px;height:38px;border-radius:11px;background:var(--grad);display:grid;place-items:center;font-size:15px;margin-bottom:10px;box-shadow:0 8px 18px -8px rgba(106,61,245,.9)}
.pw-perks b{display:block;font-size:14px}
.pw-perks small{font-size:12px;color:rgba(255,255,255,.75)}

/* ═══════════ COUNTDOWN ═══════════ */
.cd{position:relative;overflow:hidden;border-radius:var(--r-lg);background:#12102e;color:#fff;display:grid;grid-template-columns:1.1fr auto 1fr;gap:28px;align-items:center;padding:34px 44px;min-height:220px;box-shadow:var(--shadow-lg)}
@media(max-width:1100px){.cd{grid-template-columns:1fr auto;}.cd .cd-art{display:none}}
@media(max-width:768px){.cd{grid-template-columns:1fr;padding:26px 22px;text-align:center}.cd-timer{justify-content:center}}
.cd .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;opacity:.9}
.cd .shade{position:absolute;inset:0;z-index:0;background:linear-gradient(90deg,rgba(14,12,40,.97) 0%,rgba(14,12,40,.92) 40%,rgba(76,47,217,.55) 75%,rgba(255,77,125,.25) 100%)}
.cd>*:not(.bg):not(.shade){position:relative;z-index:1}
.cd-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,77,125,.18);border:1px solid rgba(255,77,125,.45);color:#ffb3c7;border-radius:999px;padding:5px 12px;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;margin-bottom:12px}
.cd-eyebrow i{color:var(--sale);animation:pulseGlow 2.2s infinite;border-radius:50%}
.cd h3{font-size:clamp(24px,3vw,38px);font-weight:800;margin:0 0 8px;letter-spacing:-1px;line-height:1.1}
.cd h3 span{background:linear-gradient(90deg,#ff6b96,#ffb86b);-webkit-background-clip:text;background-clip:text;color:transparent}
.cd p{margin:0 0 18px;color:rgba(255,255,255,.75);font-size:14.5px;max-width:420px}
.cd-timer{display:flex;gap:10px;align-items:center}
.cd-box{width:74px;height:78px;border-radius:16px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(10px);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;position:relative;overflow:hidden}
.cd-box::before{content:"";position:absolute;left:0;right:0;top:50%;height:1px;background:rgba(255,255,255,.12)}
.cd-box span{font-size:30px;font-weight:800;letter-spacing:-1px;line-height:1;background:linear-gradient(180deg,#fff,#c4b5fd);-webkit-background-clip:text;background-clip:text;color:transparent;font-variant-numeric:tabular-nums}
.cd-box small{font-size:10px;font-weight:700;letter-spacing:.1em;color:rgba(255,255,255,.6)}
.cd-sep{font-size:26px;font-weight:800;color:rgba(255,255,255,.45);margin-top:-14px;animation:blink 1s steps(2) infinite}
@keyframes blink{to{opacity:.2}}
.cd-art{position:relative;height:190px;display:flex;align-items:center;justify-content:flex-end}
.cd-art .tag{position:absolute;right:0;top:0;background:var(--sale);color:#fff;font-weight:800;font-size:13px;padding:8px 14px;border-radius:12px;box-shadow:0 12px 26px -10px rgba(255,77,125,.9);transform:rotate(6deg);animation:floatY2 4s ease-in-out infinite}
.cd-art .card{width:210px;height:170px;border-radius:22px;overflow:hidden;border:4px solid rgba(255,255,255,.9);box-shadow:0 30px 60px -20px rgba(0,0,0,.6);transform:rotate(-6deg);animation:floatY 6s ease-in-out infinite;background:#fff}
.cd-art .card img{width:100%;height:100%;object-fit:cover;display:block}
.cd .btn-sale{margin-top:14px}

/* ═══════════ PRODUCT SLIDER ═══════════ */
.pslider{overflow:visible}
.pslider .swiper-slide{height:auto}
.pslider .swiper-wrapper{padding:6px 0 30px}
.sl-nav{display:flex;gap:8px}
.sl-nav button{width:40px;height:40px;border-radius:12px;border:1.5px solid var(--line-2);background:#fff;color:var(--ink);cursor:pointer;transition:.2s;display:grid;place-items:center}
.sl-nav button:hover{background:var(--grad);color:#fff;border-color:transparent;transform:translateY(-2px)}
.sl-nav button.swiper-button-disabled{opacity:.35;pointer-events:none}
.slider-clip{overflow:hidden;margin:-6px -12px 0;padding:6px 12px 0}

/* ═══════════ PROMO CARDS ═══════════ */
.promos{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:1100px){.promos{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.promos{grid-template-columns:1fr}}
.promo{position:relative;height:250px;border-radius:var(--r-lg);overflow:hidden;padding:24px;display:flex;flex-direction:column;justify-content:flex-end;color:#fff;box-shadow:var(--shadow)}
.promo.g1{background:linear-gradient(160deg,var(--brand),var(--brand-dark))}
.promo.g2{background:linear-gradient(160deg,#ff5c8a,#b4124b)}
.promo.g3{background:linear-gradient(160deg,#0ea5e9,#0b4f87)}
.promo.g4{background:linear-gradient(160deg,#f59e0b,#b4530a)}
.promo::before{content:"";position:absolute;inset:0;background:radial-gradient(300px 200px at 100% 0%,rgba(255,255,255,.35),transparent 60%)}
.promo .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transition:transform .8s cubic-bezier(.2,.7,.2,1)}
.promo:hover .bg{transform:scale(1.1)}
.promo .shade{position:absolute;inset:0;z-index:0;background:linear-gradient(180deg,rgba(0,0,0,0) 20%,rgba(10,8,30,.85) 100%)}
.promo.g1 .shade{background:linear-gradient(180deg,rgba(106,61,245,.1) 0%,rgba(59,31,163,.92) 100%)}
.promo.g2 .shade{background:linear-gradient(180deg,rgba(255,92,138,.1) 0%,rgba(140,10,60,.92) 100%)}
.promo.g3 .shade{background:linear-gradient(180deg,rgba(14,165,233,.1) 0%,rgba(11,60,110,.92) 100%)}
.promo.g4 .shade{background:linear-gradient(180deg,rgba(245,158,11,.1) 0%,rgba(140,60,5,.92) 100%)}
.promo::before{display:none}
.promo>*:not(.bg):not(.shade){position:relative;z-index:1}
.promo h4{font-size:20px;font-weight:800;margin:0 0 4px;position:relative;letter-spacing:-.3px}
.promo p{margin:0 0 12px;font-size:13px;color:rgba(255,255,255,.85);position:relative}
.promo .by{font-size:12px;color:rgba(255,255,255,.8);position:relative;display:flex;align-items:center;justify-content:space-between;gap:8px}
.promo .by b{color:#ffd36b}
.promo .by a{background:var(--sale);color:#fff;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;transition:.2s}
.promo .by a:hover{background:#fff;color:var(--ink)}

/* ═══════════ LIST COLUMNS ═══════════ */
.lists{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
@media(max-width:1100px){.lists{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.lists{grid-template-columns:1fr}}
.list-col{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:18px 18px 8px}
.list-col h4{font-size:16px;font-weight:800;margin:0 0 4px;color:var(--ink)}
.list-col .u{width:44px;height:3px;border-radius:3px;background:var(--grad);margin-bottom:8px}

/* ═══════════ TRUST STRIP ═══════════ */
.trust{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
@media(max-width:992px){.trust{grid-template-columns:repeat(3,1fr)}}
@media(max-width:560px){.trust{grid-template-columns:repeat(2,1fr)}}
.trust div{background:var(--surface);border-radius:14px;padding:16px;display:flex;align-items:center;gap:12px;font-size:13px;font-weight:700;color:var(--ink-2)}
.trust div i{width:40px;height:40px;border-radius:12px;background:#fff;display:grid;place-items:center;color:var(--p);font-size:16px;box-shadow:var(--shadow);flex-shrink:0}
</style>
@endpush

@section('content')
@php
  $hp = $heroProducts->values();
  $slides = [
    ['cls'=>'hs-1','eyebrow'=>'Exclusive offer','pill'=>'30% Off','h'=>'Smart Shopping <br><span>Delivered Daily</span>','p'=>'Curated products, honest prices and lightning-fast delivery across Pakistan. Cash on delivery everywhere.','btn'=>'Shop Now','href'=>route('shop.index'),'bg'=>'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1800&q=80','card'=>'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=700&q=80','emo'=>'🛍️','chips'=>[['fa-truck-fast','Free delivery', zfree_over() > 0 ? 'Orders over '.zprice(zfree_over()) : 'On every order'],['fa-bolt','Flash deal','Up to 50% off'],['fa-star','4.9 rating','2,300+ reviews']]],
    ['cls'=>'hs-2','eyebrow'=>'Weekend sale','pill'=>'Up to 50%','h'=>'Weekend Deals <br><span>Live Right Now</span>','p'=>'Biggest discounts of the season on trending picks. Prices drop every Friday — grab them before they are gone.','btn'=>'View Deals','href'=>route('shop.index',['on_sale'=>1]),'bg'=>'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1800&q=80','card'=>'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=700&q=80','emo'=>'🔥','chips'=>[['fa-tag','Hot price','Limited stock'],['fa-shield-halved','Secure checkout','Server-verified'],['fa-rotate-left','Easy returns','7-day policy']]],
    ['cls'=>'hs-3','eyebrow'=>'Just landed','pill'=>'New','h'=>'Fresh Picks <br><span>Just For You</span>','p'=>'New arrivals added every week, hand-picked by our team so you always find something worth loving.','btn'=>'Explore New','href'=>route('shop.index',['sort'=>'latest']),'bg'=>'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=80','card'=>'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=700&q=80','emo'=>'✨','chips'=>[['fa-wand-magic-sparkles','New arrivals','Updated weekly'],['fa-money-bill-wave','Cash on delivery','Nationwide'],['fa-headset','24/7 support','We reply fast']]],
  ];
@endphp

{{-- ═══════════ HERO ═══════════ --}}
<section class="hero wrap">
  <div class="hero-grid">
    <div class="swiper hero-swiper" id="heroSwiper">
      <div class="swiper-wrapper">
        @foreach($slides as $i => $s)
        @php $prod = $hp->get($i) ?? $hp->first(); $real = false; $img = $s['card']; @endphp
        <div class="swiper-slide hs {{ $s['cls'] }}">
          <img class="hs-bg" src="{{ $s['bg'] }}" alt="" loading="{{ $i ? 'lazy' : 'eager' }}" data-swiper-parallax="35%">
          <span class="hs-shade"></span>
          <span class="hs-orb" style="width:320px;height:320px;background:var(--brand-light);left:-80px;top:-120px"></span>
          <span class="hs-orb" style="width:260px;height:260px;background:#22d3ee;right:30%;bottom:-120px;animation-delay:-7s"></span>
          <div class="hs-content">
            <div class="hs-eyebrow" data-swiper-parallax="-120"><b>{{ $s['pill'] }}</b> {{ $s['eyebrow'] }}</div>
            <h1 data-swiper-parallax="-220">{!! $s['h'] !!}</h1>
            <p data-swiper-parallax="-320">{{ $s['p'] }}</p>
            <div class="hs-ctas" data-swiper-parallax="-420">
              <a href="{{ $s['href'] }}" class="btn-sale" style="padding:14px 28px;font-size:15px">{{ $s['btn'] }} <i class="fa-solid fa-arrow-right"></i></a>
              <a href="{{ route('shop.index') }}" class="hs-ghost"><i class="fa-solid fa-bag-shopping"></i> Browse all</a>
            </div>
            <div class="hs-stats" data-swiper-parallax="-520">
              <div><b>2,300+</b><small>Happy customers</small></div>
              <div><b>{{ zship('delivery_days','2–5') }} days</b><small>{{ ucfirst(zship('coverage','nationwide')) }} delivery</small></div>
              <div><b>COD</b><small>Cash on delivery</small></div>
            </div>
          </div>
          <div class="h3d js-parallax">
            <div class="h3d-inner">
              <div class="ring"></div><div class="ring ring2"></div>
              <div class="disc"></div>
              <a class="glass" href="{{ $real ? route('shop.show',$prod->slug) : $s['href'] }}">
                <img src="{{ $img }}" alt="" class="{{ $real ? '' : 'cover' }}">
              </a>
              @foreach($s['chips'] as $k => $c)
                <div class="chip c{{ $k+1 }}"><i class="fa-solid {{ $c[0] }}"></i><div>{{ $c[1] }}<small>{{ $c[2] }}</small></div></div>
              @endforeach
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div class="swiper-pagination"></div>
      <button class="hero-nav prev" id="heroPrev"><i class="fa-solid fa-chevron-left"></i></button>
      <button class="hero-nav next" id="heroNext"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    @php $sb = $deals->first() ?? $hp->first(); @endphp
    <a href="{{ route('shop.index',['on_sale'=>1]) }}" class="side-banner reveal" data-delay="1">
      <img class="bg" src="{{ zbg('side') }}" alt="">
      <span class="shade"></span>
      <div class="flash">FLASH<b>SALE</b></div>
      <div class="off">{{ $sb?->discount_percent ? $sb->discount_percent.'%' : '45%' }}<small>OFF</small></div>
      <h3>{{ $sb ? \Illuminate\Support\Str::limit($sb->name, 34) : 'Fresh Picks' }}</h3>
      <p>Only this week. Don't miss out on the lowest prices of the season.</p>
      <span class="lnk">Shop Now <i class="fa-solid fa-arrow-right"></i></span>
      <div class="sb-art"><div class="card"><img src="{{ zbg('sidecard',600) }}" alt="" class="cover"></div></div>
    </a>
  </div>
</section>

{{-- ═══════════ TRUST STRIP ═══════════ --}}
<section class="wrap" style="padding-top:24px">
  <div class="trust reveal">
    <div><i class="fa-solid fa-truck-fast"></i>{{ zfree_over() > 0 ? 'Free delivery over '.zprice(zfree_over()) : 'Free delivery on every order' }}</div>
    <div><i class="fa-solid fa-shield-halved"></i>Secure, verified checkout</div>
    <div><i class="fa-solid fa-rotate-left"></i>{{ zship('return_days','7') }}-day easy returns</div>
    <div><i class="fa-solid fa-money-bill-wave"></i>Cash on delivery</div>
    <div><i class="fa-solid fa-headset"></i>24/7 customer support</div>
  </div>
</section>

{{-- ═══════════ CATEGORIES ═══════════ --}}
<section class="sec wrap">
  <div class="sec-head reveal">
    <div><h2 class="sec-title tt-none">Browse By Categories</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">Top categories of the week</p></div>
    <div style="display:flex;align-items:center;gap:14px"><a href="{{ route('shop.index') }}" class="sec-link">All categories <i class="fa-solid fa-arrow-right"></i></a><div class="sl-nav"><button id="catPrev"><i class="fa-solid fa-chevron-left"></i></button><button id="catNext"><i class="fa-solid fa-chevron-right"></i></button></div></div>
  </div>
  <div class="swiper cat-swiper reveal" id="catSwiper">
    <div class="swiper-wrapper">
      @php $catList = $parentCategories->values(); $reps = $catList->count() ? max(1, (int) ceil(14 / $catList->count())) : 0; @endphp
      @forelse(collect(range(1, max($reps,1)))->flatMap(fn() => $catList) as $i => $c)
        <div class="swiper-slide">
          <a href="{{ route('shop.index',['category'=>$c->slug]) }}" class="ctile">
            <img src="{{ zcat_image($c->name, $i, 600) }}" alt="" loading="lazy">
            <span class="sh"></span>
            <span class="ic"><i class="fa-solid {{ zcat_icon($c->name) }}"></i></span>
            <span class="cnt">{{ $c->total_count ?? 0 }} items</span>
            <span class="txt"><b>{{ $c->name }}</b><small>Shop now <i class="fa-solid fa-arrow-right"></i></small></span>
          </a>
        </div>
      @empty
        <div class="swiper-slide"><a href="{{ route('shop.index') }}" class="ctile"><img src="{{ zbg('shop',600) }}" alt=""><span class="sh"></span><span class="txt"><b>All products</b></span></a></div>
      @endforelse
    </div>
  </div>
</section>

{{-- ═══════════ PROMO BANNER (Admin → Website Content) ═══════════ --}}
@if(\App\Models\Setting::get('promo_enabled', '1') === '1')
<section class="wrap">
  <div class="promo-wide reveal">
    <img class="bg" src="{{ zbg('hero3', 1800) }}" alt="" loading="lazy"><span class="shade"></span>
    <div class="pw-text">
      <span class="pw-eyebrow"><i class="fa-solid fa-truck-fast"></i> {{ zfree_over() > 0 ? 'Free delivery over '.zprice(zfree_over()) : 'Free delivery on every order' }}</span>
      <h3>{{ zcontent('promo_title', 'Everything you love,') }}@if($acc = zcontent('promo_title_accent'))<br><span>{{ $acc }}</span>@endif</h3>
      <p>{{ zcontent('promo_text') }}</p>
      <div class="pw-ctas">
        @if($l1 = zcontent('promo_btn1_label'))<a href="{{ \App\Models\Setting::get('promo_btn1_link') ?: route('shop.index') }}" class="btn-sale" style="padding:13px 24px;font-size:15px">{{ $l1 }} <i class="fa-solid fa-arrow-right"></i></a>@endif
        @if($l2 = zcontent('promo_btn2_label'))<a href="{{ \App\Models\Setting::get('promo_btn2_link') ?: route('shop.index',['on_sale'=>1]) }}" class="pw-ghost"><i class="fa-solid fa-bolt"></i> {{ $l2 }}</a>@endif
      </div>
    </div>
    <div class="pw-perks">
      @for($k = 1; $k <= 4; $k++)
        @if($pt = zcontent("promo_perk{$k}_title"))
        <div><i class="fa-solid {{ \App\Models\Setting::get("promo_perk{$k}_icon", 'fa-circle-check') }}"></i><b>{{ $pt }}</b><small>{{ zcontent("promo_perk{$k}_text") }}</small></div>
        @endif
      @endfor
    </div>
  </div>
</section>
@endif

{{-- ═══════════ CATEGORY SECTION 1 ═══════════ --}}
@if($categorySections->get(0))
@php $sec = $categorySections->get(0); @endphp
<section class="sec wrap">
  <div class="sec-head reveal">
    <div><h2 class="sec-title">{{ $sec['category']->name }}</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">{{ $sec['category']->description ?: 'Hand-picked products from this category' }}</p></div>
    <a href="{{ route('shop.index',['category'=>$sec['category']->slug]) }}" class="sec-link">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="pgrid reveal">
    @foreach($sec['products']->take(10) as $product)
      @include('shop.partials.product-card', ['product'=>$product])
    @endforeach
  </div>
</section>
@endif

{{-- ═══════════ COUNTDOWN ═══════════ --}}
@php
  $dealImg = $deals->first() ? $deals->first()->display_image : zbg('promo2', 800);
  $dealEnd = null;
  if ($raw = \App\Models\Setting::get('deal_ends_at')) { try { $dealEnd = \Carbon\Carbon::parse($raw); } catch (\Throwable) {} }
  if (!$dealEnd || $dealEnd->isPast()) { $dealEnd = now()->endOfWeek(); }
  $dealLink = \App\Models\Setting::get('deal_link') ?: route('shop.index', ['on_sale' => 1]);
@endphp
@if(\App\Models\Setting::get('deal_enabled', '1') === '1')
<section class="wrap">
  <div class="cd reveal" data-countdown data-end="{{ $dealEnd->getTimestamp() * 1000 }}">
    <img class="bg" src="{{ zbg('hero2', 1800) }}" alt="" loading="lazy"><span class="shade"></span>
    <div>
      <span class="cd-eyebrow"><i class="fa-solid fa-bolt"></i> {{ \App\Models\Setting::get('deal_eyebrow', 'Deal of the week') }}</span>
      <h3>{{ \App\Models\Setting::get('deal_title', 'Special offers') }}<br><span>{{ \App\Models\Setting::get('deal_title_accent', 'ending soon') }}</span></h3>
      <p>{{ \App\Models\Setting::get('deal_text', 'Our biggest markdowns of the week — once the timer hits zero, prices go back up.') }}</p>
      <a href="{{ $dealLink }}" class="btn-sale" style="padding:13px 24px;font-size:15px">{{ \App\Models\Setting::get('deal_button', 'Shop the deals') }} <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="cd-timer">
      <div class="cd-box"><span data-u="d">00</span><small>DAYS</small></div><span class="cd-sep">:</span>
      <div class="cd-box"><span data-u="h">00</span><small>HRS</small></div><span class="cd-sep">:</span>
      <div class="cd-box"><span data-u="m">00</span><small>MIN</small></div><span class="cd-sep">:</span>
      <div class="cd-box"><span data-u="s">00</span><small>SEC</small></div>
    </div>
    <div class="cd-art">
      <div class="card"><img src="{{ $dealImg }}" alt=""></div>
      @if($b = \App\Models\Setting::get('deal_badge'))<span class="tag">{{ $b }}</span>@endif
    </div>
  </div>
</section>
@endif

{{-- ═══════════ HOT DEALS SLIDER ═══════════ --}}
<section class="sec wrap">
  <div class="sec-head reveal">
    <div><h2 class="sec-title">Hot Deals</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">Biggest discounts right now — swipe through</p></div>
    <div class="sl-nav"><button id="dealsPrev"><i class="fa-solid fa-chevron-left"></i></button><button id="dealsNext"><i class="fa-solid fa-chevron-right"></i></button></div>
  </div>
  <div class="slider-clip reveal">
    <div class="swiper pslider" id="dealsSwiper">
      <div class="swiper-wrapper">
        @foreach($deals as $product)
          <div class="swiper-slide">@include('shop.partials.product-card', ['product'=>$product, 'showBar'=>true, 'badge'=>$product->discount_percent?'sale':'hot'])</div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ═══════════ CATEGORY SECTION 2 ═══════════ --}}
@if($categorySections->get(1))
@php $sec = $categorySections->get(1); @endphp
<section class="sec wrap" style="padding-top:0">
  <div class="sec-head reveal">
    <div><h2 class="sec-title">{{ $sec['category']->name }}</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">{{ $sec['category']->description ?: 'Hand-picked products from this category' }}</p></div>
    <a href="{{ route('shop.index',['category'=>$sec['category']->slug]) }}" class="sec-link">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="pgrid reveal">
    @foreach($sec['products']->take(10) as $product)
      @include('shop.partials.product-card', ['product'=>$product])
    @endforeach
  </div>
</section>
@endif

{{-- ═══════════ PROMO CARDS ═══════════ --}}
@php
  $pp = $deals->concat($newArrivals)->unique('id')->values();
  $promoDefs = [
    ['g1','Hot Deals on New Items','Daily essentials, better prices','🔥'],
    ['g2','Buy More & Save More','Bundle up and pay less','💸'],
    ['g3','Fresh Arrivals Weekly','New picks every Friday','✨'],
    ['g4','Top Rated by Customers','Loved by 2,300+ shoppers','⭐'],
  ];
@endphp
<section class="wrap">
  <div class="promos">
    @foreach($promoDefs as $i => $d)
      @php $pr = $pp->get($i); @endphp
      <a href="{{ $pr ? route('shop.show',$pr->slug) : route('shop.index') }}" class="promo {{ $d[0] }} reveal" data-delay="{{ $i+1 }}" data-tilt="6">
        <img class="bg" src="{{ zbg('promo'.($i+1), 900) }}" alt="" loading="lazy"><span class="shade"></span>
        <h4>{{ $d[1] }}</h4>
        <p>{{ $d[2] }}</p>
        <div class="by"><span>By <b>{{ zstore('name','Zelari') }}</b></span><span style="background:var(--sale);padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700">Shop Now <i class="fa-solid fa-arrow-right"></i></span></div>
      </a>
    @endforeach
  </div>
</section>

{{-- ═══════════ LIST COLUMNS ═══════════ --}}
<section class="sec wrap">
  <div class="lists">
    @foreach([['Top Selling',$topSelling],['Trending Products',$trending],['Recently Added',$recentlyAdded],['Top Rated',$topRated]] as $i => [$ttl,$items])
      <div class="list-col reveal" data-delay="{{ $i+1 }}">
        <h4>{{ $ttl }}</h4><div class="u"></div>
        @forelse($items->take(4) as $product)
          @include('shop.partials.product-row', ['product'=>$product])
        @empty
          <p style="color:var(--faint);font-size:13px;padding:12px 0">No products yet.</p>
        @endforelse
      </div>
    @endforeach
  </div>
</section>

{{-- ═══════════ NEW ARRIVALS SLIDER ═══════════ --}}
<section class="sec wrap" style="padding-top:0">
  <div class="sec-head reveal">
    <div><h2 class="sec-title">New Arrivals</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">Fresh on the shelf this week</p></div>
    <div class="sl-nav"><button id="newPrev"><i class="fa-solid fa-chevron-left"></i></button><button id="newNext"><i class="fa-solid fa-chevron-right"></i></button></div>
  </div>
  <div class="slider-clip reveal">
    <div class="swiper pslider" id="newSwiper">
      <div class="swiper-wrapper">
        @foreach($newArrivals as $product)
          <div class="swiper-slide">@include('shop.partials.product-card', ['product'=>$product, 'badge'=>'new'])</div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ═══════════ CATEGORY SECTION 3 ═══════════ --}}
@if($categorySections->get(2))
@php $sec = $categorySections->get(2); @endphp
<section class="sec wrap" style="padding-top:0">
  <div class="sec-head reveal">
    <div><h2 class="sec-title">{{ $sec['category']->name }}</h2><div class="sec-deco"><i></i><b></b></div><p class="sec-sub">{{ $sec['category']->description ?: 'Hand-picked products from this category' }}</p></div>
    <a href="{{ route('shop.index',['category'=>$sec['category']->slug]) }}" class="sec-link">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="pgrid reveal">
    @foreach($sec['products']->take(10) as $product)
      @include('shop.partials.product-card', ['product'=>$product])
    @endforeach
  </div>
</section>
@endif
@endsection

@push('scripts')
<script>
new Swiper('#heroSwiper',{loop:true,speed:1000,parallax:true,grabCursor:true,autoplay:{delay:6000,disableOnInteraction:false,pauseOnMouseEnter:true},pagination:{el:'#heroSwiper .swiper-pagination',clickable:true},navigation:{prevEl:'#heroPrev',nextEl:'#heroNext'}});
const pOpts = (prev,next)=>({slidesPerView:1.3,spaceBetween:16,speed:600,grabCursor:true,navigation:{prevEl:prev,nextEl:next},breakpoints:{480:{slidesPerView:2.2},768:{slidesPerView:3},1024:{slidesPerView:4},1300:{slidesPerView:5},1600:{slidesPerView:6}}});
new Swiper('#dealsSwiper',pOpts('#dealsPrev','#dealsNext'));
new Swiper('#catSwiper',{slidesPerView:1.6,spaceBetween:14,loop:true,speed:5000,autoplay:{delay:0,disableOnInteraction:false,pauseOnMouseEnter:true},freeMode:{enabled:true,momentum:false},grabCursor:true,navigation:{prevEl:'#catPrev',nextEl:'#catNext'},breakpoints:{480:{slidesPerView:2.4},768:{slidesPerView:3.4},1024:{slidesPerView:5},1300:{slidesPerView:6},1600:{slidesPerView:7}}});
new Swiper('#newSwiper',pOpts('#newPrev','#newNext'));

/* Hero 3D parallax: tilt the scene toward the cursor */
if(matchMedia('(hover:hover) and (pointer:fine)').matches && !matchMedia('(prefers-reduced-motion: reduce)').matches){
  const hero=document.getElementById('heroSwiper');
  hero.addEventListener('mousemove',e=>{
    const r=hero.getBoundingClientRect(), x=(e.clientX-r.left)/r.width-.5, y=(e.clientY-r.top)/r.height-.5;
    hero.querySelectorAll('.h3d-inner').forEach(el=>{ el.style.setProperty('--px',(-y*16)+'deg'); el.style.setProperty('--py',(x*22)+'deg'); });
  });
  hero.addEventListener('mouseleave',()=>hero.querySelectorAll('.h3d-inner').forEach(el=>{ el.style.setProperty('--px','0deg'); el.style.setProperty('--py','0deg'); }));
}
</script>
@endpush
