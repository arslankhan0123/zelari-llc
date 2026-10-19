<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@php $zc = zcur(); @endphp
<script>window.ZCUR={code:@json($zc['code']),symbol:@json($zc['symbol']),rate:{{ (float) $zc['rate'] }},decimals:{{ (int) $zc['decimals'] }}};
window.zmoney=function(n){var c=window.ZCUR,v=(Number(n)||0)*c.rate,s=v.toLocaleString('en-US',{minimumFractionDigits:c.decimals,maximumFractionDigits:c.decimals});return (c.symbol.length<=2&&!/^[A-Za-z]+$/.test(c.symbol))?c.symbol+s:c.symbol+' '+s};</script>
<title>@yield('title', 'Shop') · {{ zstore('name','Zelari') }}</title>
<meta name="description" content="@yield('meta_description', zstore('name','Zelari').' — '.zstore('tagline','a smarter way to shop').'. Curated products, honest prices, fast delivery across Pakistan.')">

@if(zbrand_custom('icon'))
<link rel="icon" href="{{ zbrand('icon') }}">
<link rel="apple-touch-icon" href="{{ zbrand('icon') }}">
@else
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
@endif
<meta name="theme-color" content="{{ ztheme('brand_dark') }}">
@include('partials.theme-vars')
<meta property="og:image" content="{{ asset('images/logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════ TOKENS ═══════════════════════════════ */
:root{
  /* Colours live in config/theme.php → partials/theme-vars (--brand, --accent, …). These are aliases used by this layout. */
  --p:var(--brand); --p-dark:var(--brand-dark); --p-soft:var(--brand-soft); --p2:var(--accent); --p2-soft:var(--accent-soft);
  --grad:var(--brand-grad);
  --grad-h:var(--brand-grad-hover);
  --white:#fff;
  --r:14px; --r-lg:22px;
  --shadow:0 10px 30px -12px rgba(var(--shadow-rgb),.18);
  --shadow-lg:0 30px 60px -20px rgba(var(--shadow-rgb),.3);
  --glow:0 0 0 4px rgba(var(--shadow-rgb),.14);
  --head-h:74px;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink);background:#fff;margin:0;-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
a:hover{color:var(--p)}
img{max-width:100%}
button,input,select,textarea{font-family:inherit}
::selection{background:var(--p);color:#fff}
.wrap{max-width:1420px;margin:0 auto;padding:0 24px}
@media(max-width:576px){.wrap{padding:0 16px}}

/* ═══════════════════════════════ UTILITIES ═══════════════════════════════ */
.grad-text{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.btn-g{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--grad);color:#fff;border:0;border-radius:12px;padding:12px 22px;font-weight:700;font-size:14px;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;box-shadow:0 10px 24px -10px rgba(106,61,245,.6)}
.btn-g::after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,transparent 30%,rgba(255,255,255,.35) 50%,transparent 70%);transform:translateX(-120%);transition:transform .6s}
.btn-g:hover{color:#fff;transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(106,61,245,.7)}
.btn-g:hover::after{transform:translateX(120%)}
.btn-o{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--ink);border:1.5px solid var(--line-2);border-radius:12px;padding:11px 20px;font-weight:700;font-size:14px;cursor:pointer;transition:.2s}
.btn-o:hover{border-color:var(--p);color:var(--p);background:var(--p-soft)}
.btn-sale{display:inline-flex;align-items:center;gap:8px;background:var(--sale);color:#fff;border:0;border-radius:10px;padding:11px 20px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 10px 24px -10px rgba(255,77,125,.7);transition:.2s}
.btn-sale:hover{color:#fff;transform:translateY(-2px);background:#ff2f68}

.sec{padding-top:52px;padding-bottom:52px}
@media(max-width:768px){.sec{padding-top:36px;padding-bottom:36px}}
.sec-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.sec-title{font-size:24px;font-weight:800;letter-spacing:-.4px;margin:0 0 6px;text-transform:uppercase;color:var(--ink)}
.sec-title.tt-none{text-transform:none}
.sec-deco{display:flex;align-items:center;gap:6px;margin-bottom:6px}
.sec-deco i{width:52px;height:3px;border-radius:3px;background:var(--grad)}
.sec-deco b{width:6px;height:6px;border-radius:50%;background:var(--p2)}
.sec-sub{color:var(--muted);font-size:14px;margin:0}
.sec-link{font-weight:700;font-size:14px;color:var(--p);display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.sec-link i{transition:transform .2s}
.sec-link:hover i{transform:translateX(4px)}

/* stars */
.stars{display:inline-flex;gap:2px;color:var(--star);font-size:11px}
.stars .off{color:#d9dcec}

/* 3D tilt */
[data-tilt]{transform-style:preserve-3d;transform:perspective(900px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg));transition:transform .55s cubic-bezier(.2,.7,.2,1),box-shadow .3s;will-change:transform}
[data-tilt].tilting{transition:transform .08s linear,box-shadow .3s}
.tilt-glare{position:absolute;inset:0;pointer-events:none;border-radius:inherit;background:radial-gradient(400px circle at var(--gx,50%) var(--gy,50%),rgba(255,255,255,.35),transparent 45%);opacity:0;transition:opacity .3s;z-index:3}
[data-tilt].tilting .tilt-glare{opacity:1}
.lift{transition:transform .35s cubic-bezier(.2,.7,.2,1),box-shadow .35s}
.lift:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg)}

/* Reveal on scroll */
.reveal{opacity:0;transform:translateY(28px) perspective(900px) rotateX(6deg);transform-origin:top center}
.reveal.in{opacity:1;transform:none;transition:opacity .7s ease,transform .8s cubic-bezier(.2,.7,.2,1)}
.reveal.in[data-tilt]{transform:perspective(900px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg))}
.reveal.in[data-tilt].tilting{transition:transform .08s linear,box-shadow .3s}
.reveal[data-delay="1"].in{transition-delay:.08s}.reveal[data-delay="2"].in{transition-delay:.16s}.reveal[data-delay="3"].in{transition-delay:.24s}.reveal[data-delay="4"].in{transition-delay:.32s}
@media(prefers-reduced-motion:reduce){
  .reveal{opacity:1;transform:none}
  [data-tilt]{transform:none!important}
  *,*::before,*::after{animation-duration:.001ms!important;transition-duration:.001ms!important}
}

/* floating / glow keyframes */
@keyframes floatY{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-14px) rotate(1.5deg)}}
@keyframes floatY2{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
@keyframes spinSlow{to{transform:rotate(360deg)}}
@keyframes pulseGlow{0%,100%{box-shadow:0 0 0 0 rgba(34,211,238,.55)}70%{box-shadow:0 0 0 14px rgba(34,211,238,0)}}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@keyframes orb{0%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-20px) scale(1.1)}66%{transform:translate(-20px,15px) scale(.95)}100%{transform:translate(0,0) scale(1)}}
@keyframes shimmer{to{background-position:200% center}}
@keyframes toastIn{from{opacity:0;transform:translateX(30px) scale(.96)}to{opacity:1;transform:none}}
@keyframes popIn{from{transform:scale(.4);opacity:0}to{transform:scale(1);opacity:1}}
@keyframes cartBump{0%{transform:scale(1)}40%{transform:scale(1.35) rotate(-8deg)}100%{transform:scale(1)}}

/* ═══════════════════════════════ TOP BAR ═══════════════════════════════ */
.topbar{background:linear-gradient(90deg,#1b1740,#2b1e6b 50%,#0c3b5c);color:rgba(255,255,255,.85);font-size:12.5px;height:36px;display:flex;align-items:center;position:relative;overflow:hidden}
.topbar::before{content:"";position:absolute;inset:0;background:radial-gradient(600px 80px at 20% 50%,rgba(34,211,238,.18),transparent),radial-gradient(500px 80px at 80% 50%,rgba(139,92,246,.25),transparent)}
.topbar .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;position:relative}
.topbar-loc{display:flex;align-items:center;gap:7px;white-space:nowrap}
.topbar-loc i{color:var(--p2)}
.topbar-marquee{flex:1;overflow:hidden;position:relative;mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)}
.topbar-marquee span{display:inline-block;white-space:nowrap;animation:marquee 34s linear infinite}
.topbar-marquee b{color:#fff}
.topbar-marquee em{font-style:normal;color:var(--p2);font-weight:700}
.topbar-marquee .sep{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--p2);margin:0 34px;vertical-align:middle;opacity:.8}
.topbar-sel{display:flex;gap:14px;white-space:nowrap}
.topbar-sel .sel{display:flex;align-items:center;gap:5px;cursor:pointer;color:#fff}
.topbar-sel .sel i{font-size:10px;opacity:.7}
@media(max-width:768px){.topbar-sel,.topbar-loc span{display:none}}

/* ═══════════════════════════════ HEADER ═══════════════════════════════ */
.header{position:sticky;top:0;z-index:1000;background:#fff;box-shadow:0 1px 0 var(--line)}
.header.scrolled{box-shadow:0 8px 30px -14px rgba(39,30,92,.25)}
.hdr-main{height:var(--head-h);display:flex;align-items:center;gap:20px}
.logo{display:flex;align-items:center;flex-shrink:0;position:relative}
.logo svg,.logo img{height:46px;width:auto;display:block;transition:transform .35s cubic-bezier(.2,.7,.2,1),filter .35s}
.logo:hover svg,.logo:hover img{transform:translateY(-1px) scale(1.03);filter:drop-shadow(0 10px 18px rgba(106,61,245,.35))}
.logo.sm svg,.logo.sm img{height:36px}.logo.lg svg,.logo.lg img{height:66px}
.logo img{max-width:220px;object-fit:contain}
@media(max-width:576px){.logo svg,.logo img{height:38px}}
.loc{flex-shrink:0;display:flex}
.loc-pill{display:flex;align-items:center;gap:9px;border:0;border-right:1.5px solid var(--line);border-radius:12px 0 0 12px;padding:0 12px 0 14px;height:100%;font-size:13px;font-weight:600;color:var(--ink-2);white-space:nowrap;background:var(--surface);cursor:pointer;transition:.2s;font-family:inherit}
.loc-pill:hover,.loc.open .loc-pill{background:var(--p-soft)}
.loc-pill .flag{font-size:17px;line-height:1}
.loc .flag img{flex:none;width:24px;min-width:24px;height:18px;object-fit:cover;border-radius:4px;display:block;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.loc-pill small{display:block;font-size:10.5px;color:var(--faint);font-weight:600;line-height:1.1}
.loc-pill strong{display:block;font-size:13px;line-height:1.15}
.loc-pill strong em{font-style:normal;color:var(--p);font-weight:800;margin-left:4px}
.loc-pill i.fa-chevron-down{font-size:10px;color:var(--faint);transition:transform .25s}
.loc.open .loc-pill i.fa-chevron-down{transform:rotate(180deg)}
.loc-dd{position:absolute;top:calc(100% + 10px);left:-1.5px;width:300px;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 24px 60px -18px rgba(39,30,92,.35);padding:8px;opacity:0;visibility:hidden;transform:translateY(8px) scale(.98);transition:.22s cubic-bezier(.2,.7,.2,1);z-index:1200;display:flex;flex-direction:column}
.loc-list{max-height:320px;overflow-y:auto;padding-right:2px}
.loc-search{display:flex;align-items:center;gap:8px;margin:0 4px 8px;border:1.5px solid var(--line-2);border-radius:10px;padding:0 10px;background:var(--surface)}
.loc-search i{font-size:12px;color:var(--faint)}
.loc-search input{flex:1;border:0;background:none;height:36px;font:inherit;font-size:13px;outline:0;min-width:0}
.loc-none{display:none;padding:14px 10px;font-size:12.5px;color:var(--muted);text-align:center}
.loc-list.empty .loc-none{display:block}
.loc.open .loc-dd{opacity:1;visibility:visible;transform:none}
.loc-dd h6{font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--faint);margin:8px 10px 6px}
.loc-dd button{display:flex;align-items:center;gap:11px;width:100%;border:0;background:none;padding:9px 10px;border-radius:10px;cursor:pointer;font-family:inherit;text-align:left;color:var(--ink);transition:.15s}
.loc-dd button:hover{background:var(--p-soft)}
.loc-dd button.on{background:var(--p-soft);color:var(--p)}
.loc-dd button .flag{font-size:19px;width:24px;text-align:center;display:flex;justify-content:center}
.loc-dd button b{font-size:13.5px;font-weight:700;flex:1}
.loc-dd button span{font-size:12px;color:var(--muted);font-weight:700;background:var(--surface);padding:3px 8px;border-radius:999px}
.loc-dd button.on span{background:#fff;color:var(--p)}
.loc-dd button .fa-check{font-size:12px;color:var(--p)}
@media(max-width:1300px){.loc .loc-pill small{display:none}}
.acct{position:relative}
.acct-av{width:30px;height:30px;border-radius:50%;background:var(--brand-grad);color:#fff;font-weight:800;font-size:13px;display:grid;place-items:center}
.acct-av.lg{width:40px;height:40px;font-size:16px;flex-shrink:0}
.acct-btn{padding:0}
.acct.open .acct-btn{border-color:var(--p);box-shadow:var(--glow)}
.acct-dd{position:absolute;top:calc(100% + 10px);right:0;width:260px;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 24px 60px -18px rgba(var(--shadow-rgb),.35);padding:8px;opacity:0;visibility:hidden;transform:translateY(8px) scale(.98);transition:.22s cubic-bezier(.2,.7,.2,1);z-index:1200}
.acct.open .acct-dd{opacity:1;visibility:visible;transform:none}
.acct-head{display:flex;align-items:center;gap:12px;padding:10px 10px 12px;border-bottom:1px solid var(--line);margin-bottom:6px}
.acct-head b{display:block;font-size:14px;line-height:1.2}
.acct-head small{display:block;color:var(--muted);font-size:12px;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px}
.acct-head.guest{display:block}
.acct-head.guest small{white-space:normal;max-width:none;line-height:1.45}
.acct-dd a,.acct-dd button{display:flex;align-items:center;gap:11px;width:100%;padding:10px 10px;border-radius:10px;font-size:13.5px;font-weight:700;color:var(--ink);border:0;background:none;cursor:pointer;font-family:inherit;text-align:left;transition:.15s}
.acct-dd a i,.acct-dd button i{width:18px;text-align:center;color:var(--p)}
.acct-dd a:hover,.acct-dd button:hover{background:var(--p-soft)}
.acct-dd a em{margin-left:auto;font-style:normal;font-size:11px;font-weight:800;background:var(--p-soft);color:var(--p);padding:2px 8px;border-radius:999px}
.acct-dd a.acct-primary{background:var(--brand-grad);color:#fff;justify-content:center;margin-bottom:4px}
.acct-dd a.acct-primary i{color:#fff}
.acct-dd a.acct-primary:hover{background:var(--brand-grad-hover)}
.acct-dd a.is-admin i{color:var(--accent)}
.acct-out{margin:6px 0 0;padding-top:6px;border-top:1px solid var(--line)}
.acct-out button{color:var(--sale)}
.acct-out button i{color:var(--sale)}
.drawer .dloc{padding:12px 18px 4px;border-bottom:1px solid var(--surface)}
.drawer .dloc label{display:block;font-size:12px;letter-spacing:.06em;color:var(--faint);text-transform:uppercase;margin-bottom:6px}
.drawer .dloc select{width:100%;border:1.5px solid var(--line-2);border-radius:12px;padding:10px 12px;font:inherit;font-weight:600;background:#fff}
.search{flex:1;display:flex;align-items:stretch;max-width:640px;margin:0 auto;position:relative;border:1.5px solid var(--line-2);border-radius:14px;background:#fff;transition:.2s}
.search:focus-within,.search:has(.loc.open){border-color:var(--p);box-shadow:var(--glow)}
.search form{display:flex;flex:1;min-width:0}
.search select{border:0;border-right:1.5px solid var(--line);background:var(--surface);padding:0 12px;font-size:13px;font-weight:600;color:var(--ink-2);outline:0;max-width:160px}
.search .s-ic{align-self:center;color:var(--faint);padding-left:16px;font-size:14px}
.search input{flex:1;border:0;padding:0 14px;height:46px;font-size:14px;outline:0;min-width:0}
.search form button{border:0;background:var(--grad);color:#fff;width:54px;font-size:16px;cursor:pointer;transition:filter .2s;border-radius:0 12px 12px 0}
.search form button:hover{filter:brightness(1.08)}
@media(max-width:992px){.search{display:none}}
.hdr-actions{display:flex;align-items:center;gap:6px;margin-left:auto;flex-shrink:0}
.hdr-call{display:flex;align-items:center;gap:10px;padding:6px 10px;border-radius:12px;color:var(--ink)}
.hdr-call i{width:40px;height:40px;border-radius:12px;background:var(--p-soft);display:grid;place-items:center;color:var(--p);font-size:15px}
.hdr-call small{display:block;font-size:11px;color:var(--muted)}
.hdr-call strong{font-size:13.5px;display:block;line-height:1.1}
@media(max-width:1400px){.hdr-call div{display:none}}
.icon-btn{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:var(--ink);font-size:18px;position:relative;transition:.2s;border:0;background:transparent;cursor:pointer}
.icon-btn:hover{background:var(--p-soft);color:var(--p)}
.icon-btn .cnt{position:absolute;top:4px;right:4px;min-width:18px;height:18px;border-radius:9px;background:var(--sale);color:#fff;font-size:10.5px;font-weight:800;display:grid;place-items:center;padding:0 5px;border:2px solid #fff}
.icon-btn.bump i{animation:cartBump .5s}
.hdr-cart{display:flex;align-items:center;gap:8px;padding-right:6px;border-radius:12px;color:var(--ink)}
.hdr-cart .icon-btn{pointer-events:none}
.hdr-cart span{font-size:13px;font-weight:700;white-space:nowrap}
.hdr-cart span small{display:block;font-size:11px;color:var(--muted);font-weight:500}
@media(max-width:1400px){.hdr-cart span{display:none}}
.burger{display:none}
@media(max-width:992px){.burger{display:grid}}

/* ─── Nav row ─── */
.navrow{height:58px;display:flex;align-items:center;gap:16px;border-top:1px solid var(--line)}
@media(max-width:992px){.navrow{display:none}}
.allcat{position:relative}
.allcat-btn{display:flex;align-items:center;gap:10px;background:var(--grad);color:#fff;border:0;border-radius:12px;padding:0 18px;height:44px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 10px 24px -10px rgba(106,61,245,.6)}
.allcat-btn i.fa-chevron-down{font-size:10px;margin-left:4px;transition:transform .25s}
.allcat:hover .allcat-btn i.fa-chevron-down,.allcat.open .allcat-btn i.fa-chevron-down{transform:rotate(180deg)}
.dd{position:absolute;top:calc(100% + 10px);left:0;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow-lg);opacity:0;visibility:hidden;transform:translateY(10px) perspective(800px) rotateX(-8deg);transform-origin:top center;transition:.25s;z-index:50}
.allcat:hover .dd,.allcat.open .dd,.nav-item:hover .dd,.nav-item.open .dd{opacity:1;visibility:visible;transform:none}
.dd::before{content:"";position:absolute;top:-10px;left:0;right:0;height:10px}
.allcat .dd{width:290px;padding:8px}
.cat-list a{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;font-size:14px;font-weight:600;color:var(--ink-2);transition:.15s}
.cat-list a:hover{background:var(--p-soft);color:var(--p);transform:translateX(3px)}
.cat-list a .ci{width:34px;height:34px;border-radius:10px;background:var(--surface);display:grid;place-items:center;font-size:14px;color:var(--p);flex-shrink:0}
.cat-list a .n{margin-left:auto;font-size:11px;color:var(--faint);font-weight:700;background:var(--surface);padding:2px 8px;border-radius:999px}
.cat-list .all{border-top:1px solid var(--line);margin-top:6px;padding-top:12px;color:var(--p);justify-content:center}
/* ─── Cascading category menu ─── */
.cmenu{list-style:none;margin:0;padding:8px;min-width:250px;max-height:300px;overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin;scrollbar-color:#cfd3e6 transparent}
.cmenu::-webkit-scrollbar{width:6px}.cmenu::-webkit-scrollbar-thumb{background:#cfd3e6;border-radius:6px}.cmenu::-webkit-scrollbar-track{background:transparent}
.cmenu li{position:relative}
.cmenu li>a{display:flex;align-items:center;gap:10px;padding:6px 10px;border-radius:10px;font-size:13.5px;font-weight:600;color:var(--ink-2);transition:.15s;white-space:nowrap}
.cmenu li>a .ci{width:30px;height:30px;border-radius:9px;background:var(--surface);display:grid;place-items:center;font-size:14px;color:var(--p);flex-shrink:0}
.cmenu li>a .nm{flex:1}
.cmenu li>a .arr{font-size:10px;color:var(--faint);transition:transform .2s}
.cmenu li>a .cnt-b{font-size:11px;font-weight:700;color:var(--faint);background:var(--surface);padding:2px 8px;border-radius:999px}
.cmenu li:hover>a{background:var(--p-soft);color:var(--p)}
.cmenu li:hover>a .ci{background:var(--grad);color:#fff}
.cmenu li:hover>a .arr{transform:translateX(3px);color:var(--p)}
.cmenu-sub{position:fixed;top:0;left:0;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow-lg);opacity:0;visibility:hidden;transform:translateX(8px);transition:opacity .2s,transform .2s,visibility .2s;z-index:1200}
.cmenu li.has-sub:hover>.cmenu-sub{opacity:1;visibility:visible;transform:none}
.cmenu-sub li>a{font-weight:600;font-size:13.5px}
.shop-dd{display:flex;align-items:stretch;width:auto;padding:0;left:-60px;overflow:visible}
.shop-dd .cmenu-root{padding:8px;border-right:1px solid var(--line);max-height:320px}
.shop-dd.sub-open .shop-promo,.shop-dd:has(.cmenu-root > li.has-sub:hover) .shop-promo{display:none}
.shop-dd.sub-open .cmenu-root,.shop-dd:has(.cmenu-root > li.has-sub:hover) .cmenu-root{border-right:0}
.shop-dd .shop-promo{width:250px;padding:18px;display:flex;flex-direction:column;justify-content:flex-end;gap:8px;border-radius:0 16px 16px 0;background:var(--grad);color:#fff;position:relative;overflow:hidden}
.shop-dd .shop-promo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35;mix-blend-mode:luminosity}
.shop-dd .shop-promo>*{position:relative}
.shop-dd .shop-promo b{font-size:17px}
.shop-dd .shop-promo small{opacity:.9;font-size:12.5px}
.shop-dd .shop-promo a{display:inline-flex;align-self:flex-start;background:#fff;color:var(--p);padding:8px 14px;border-radius:9px;font-size:13px;font-weight:700;margin-top:4px}
.allcat .dd{padding:0;width:auto}
.nav-main{display:flex;align-items:center;gap:6px;margin:0;padding:0;list-style:none}
.nav-item{position:relative}
.nav-item>a{display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:10px;font-weight:700;font-size:14.5px;color:var(--ink);transition:.15s}
.nav-item>a i{font-size:10px;color:var(--faint);transition:transform .25s}
.nav-item:hover>a{color:var(--p);background:var(--p-soft)}
.nav-item:hover>a i{transform:rotate(180deg)}
.nav-item>a.active{color:var(--p)}
.nav-item>a.active::after{content:"";position:absolute;left:16px;right:16px;bottom:4px;height:3px;border-radius:3px;background:var(--grad)}
.mega{width:720px;padding:22px 24px;display:grid;grid-template-columns:repeat(3,1fr);gap:18px 24px;left:-60px}
.mega .col h6{font-size:13.5px;font-weight:800;margin:0 0 10px;display:flex;align-items:center;gap:8px;color:var(--ink)}
.mega .col h6 i{color:var(--p);font-size:13px}
.mega .col a{display:block;font-size:13px;color:var(--muted);padding:5px 0;transition:.15s}
.mega .col a:hover{color:var(--p);transform:translateX(3px)}
.mega .col a.more{color:var(--p);font-weight:700;font-size:12.5px}
.mega-promo{grid-column:1/-1;margin-top:6px;border-radius:12px;background:var(--grad);color:#fff;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.mega-promo b{font-size:15px}
.mega-promo small{opacity:.85}
.mega-promo a{background:#fff;color:var(--p);padding:8px 14px;border-radius:9px;font-size:13px;font-weight:700}
.deal-btn{margin-left:auto;display:inline-flex;align-items:center;gap:9px;background:var(--p-soft);color:var(--p);padding:0 18px;height:44px;border-radius:12px;font-weight:700;font-size:14px;transition:.2s;position:relative}
.deal-btn i{animation:pulseGlow 2.2s infinite;border-radius:50%}
.deal-btn:hover{background:var(--p);color:#fff}

/* ─── Mobile drawer ─── */
.drawer-bg{position:fixed;inset:0;background:rgba(15,18,48,.55);backdrop-filter:blur(3px);z-index:1100;opacity:0;visibility:hidden;transition:.3s}
.drawer{position:fixed;top:0;left:0;bottom:0;width:min(340px,88vw);background:#fff;z-index:1101;transform:translateX(-100%) ;transition:transform .35s cubic-bezier(.2,.7,.2,1);display:flex;flex-direction:column;overflow-y:auto}
body.drawer-open .drawer-bg{opacity:1;visibility:visible}
body.drawer-open .drawer{transform:none}
.drawer-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--line)}
.drawer form{display:flex;margin:14px 18px;border:1.5px solid var(--line-2);border-radius:12px;overflow:hidden}
.drawer form input{flex:1;border:0;padding:10px 14px;outline:0;min-width:0}
.drawer form button{border:0;background:var(--grad);color:#fff;width:46px}
.drawer nav a{display:flex;align-items:center;gap:12px;padding:13px 18px;font-weight:700;font-size:15px;border-bottom:1px solid var(--surface)}
.drawer nav a i{width:22px;color:var(--p)}
.ctree-item a{display:flex;align-items:center;gap:10px;padding:8px 10px;padding-left:calc(10px + var(--d,0) * 16px);border-radius:10px;font-size:14px;color:var(--ink-2);font-weight:600;transition:.15s}
.ctree-item a i:first-child{width:16px;color:var(--p);font-size:13px;text-align:center}
.ctree-item a:hover{background:var(--p-soft);color:var(--p)}
.ctree-item a.on{background:var(--grad);color:#fff}
.ctree-item a.on i:first-child{color:#fff}
.ctree-item a.path{color:var(--p)}
.ctree-item a .n{margin-left:auto;font-size:11px;background:var(--surface);color:var(--muted);padding:2px 8px;border-radius:999px;font-weight:700}
.ctree-item a.on .n{background:rgba(255,255,255,.25);color:#fff}
.ctree-item a .tog{margin-left:auto;width:26px;height:26px;border-radius:8px;display:grid;place-items:center;font-size:11px;color:var(--faint);transition:.2s}
.ctree-item a .n + .tog{margin-left:6px}
.ctree-item a .tog:hover{background:rgba(106,61,245,.12);color:var(--p)}
.ctree-item a .tog.open{transform:rotate(180deg)}
.ctree-kids{display:none}
.ctree-kids.show{display:block}
.drawer .dcats{padding:8px 18px 20px}
.drawer .dcats h6{font-size:12px;letter-spacing:.06em;color:var(--faint);margin:12px 0 6px;text-transform:uppercase}

/* ─── Mobile bottom nav ─── */
.mnav{display:none;position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--line);z-index:900;box-shadow:0 -8px 30px -14px rgba(39,30,92,.3)}
@media(max-width:768px){.mnav{display:flex}body{padding-bottom:64px}}
.mnav a{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:9px 0 8px;font-size:11px;font-weight:700;color:var(--muted);position:relative}
.mnav a i{font-size:18px}
.mnav a.active{color:var(--p)}
.mnav a .cnt{position:absolute;top:5px;left:calc(50% + 6px);min-width:17px;height:17px;border-radius:9px;background:var(--sale);color:#fff;font-size:10px;font-weight:800;display:grid;place-items:center;padding:0 4px}

/* ═══════════════════════════════ TOASTS ═══════════════════════════════ */
.toasts{position:fixed;top:90px;right:20px;z-index:2000;display:flex;flex-direction:column;gap:10px;max-width:360px}
.toast-z{display:flex;align-items:flex-start;gap:12px;background:#fff;border:1px solid var(--line);border-left:4px solid var(--ok);line-height:1.45;border-radius:14px;padding:14px 16px;box-shadow:var(--shadow-lg);animation:toastIn .35s cubic-bezier(.2,.7,.2,1);font-size:14px}
.toast-z.err{border-left-color:var(--sale)}
.toast-z.info{border-left-color:var(--p)}
.toast-z i.ic{font-size:18px;color:var(--ok);margin-top:1px}
.toast-z.err i.ic{color:var(--sale)}.toast-z.info i.ic{color:var(--p)}
.toast-z .x{margin-left:auto;background:0;border:0;color:var(--faint);cursor:pointer;font-size:14px}
.toast-z a{color:var(--p);font-weight:700}

/* ═══════════════════════════════ NEWSLETTER ═══════════════════════════════ */
.newsletter{margin:20px auto 0}
.nl-box{position:relative;overflow:hidden;border-radius:var(--r-lg);background:linear-gradient(120deg,#17143a 0%,#2a1f6e 50%,#0b3d5e 100%);color:#fff;padding:48px 56px;display:flex;align-items:center;justify-content:space-between;gap:30px}
.nl-box .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;opacity:.35;mix-blend-mode:luminosity}
.nl-box>*:not(.bg){position:relative;z-index:1}
.nl-box::before,.nl-box::after{z-index:1;content:"";position:absolute;border-radius:50%;filter:blur(40px);opacity:.6;animation:orb 14s ease-in-out infinite}
.nl-box::before{width:260px;height:260px;background:var(--brand-light);top:-100px;right:28%}
.nl-box::after{width:220px;height:220px;background:#22d3ee;bottom:-120px;right:4%;animation-delay:-6s}
.nl-box h3{font-size:28px;font-weight:800;margin:0 0 6px;letter-spacing:-.5px;position:relative}
.nl-box p{margin:0 0 18px;color:#ffd36b;font-weight:600;position:relative}
.nl-form{display:flex;max-width:520px;background:#fff;border-radius:12px;overflow:hidden;position:relative}
.nl-form input{flex:1;border:0;padding:0 18px;height:50px;outline:0;font-size:14px;color:var(--ink);min-width:0}
.nl-form button{border:0;background:var(--sale);color:#fff;font-weight:700;padding:0 22px;cursor:pointer}
.nl-art{position:relative;width:260px;height:160px;flex-shrink:0;transform-style:preserve-3d}
.nl-art .bub{position:absolute;border-radius:16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(6px);display:grid;place-items:center;font-size:26px;animation:floatY 5s ease-in-out infinite}
.nl-art .b1{width:76px;height:76px;left:0;top:30px}
.nl-art .b2{width:96px;height:96px;left:90px;top:0;animation-delay:-1.5s;background:rgba(34,211,238,.18)}
.nl-art .b3{width:64px;height:64px;left:196px;top:70px;animation-delay:-3s;background:rgba(255,77,125,.2)}
@media(max-width:992px){.nl-box{padding:36px 28px;flex-direction:column;align-items:flex-start}.nl-art{display:none}}

/* ═══════════════════════════════ FOOTER ═══════════════════════════════ */
.footer{background:var(--surface);margin-top:56px;border-top:1px solid var(--line)}
.f-feats{display:grid;grid-template-columns:repeat(4,1fr);gap:0;padding:30px 0;border-bottom:1px solid var(--line)}
.f-feat{display:flex;align-items:center;gap:14px;padding:0 20px;border-right:1px solid var(--line)}
.f-feat:last-child{border-right:0}
.f-feat i{width:48px;height:48px;border-radius:14px;background:#fff;display:grid;place-items:center;color:var(--p);font-size:20px;box-shadow:var(--shadow);flex-shrink:0}
.f-feat span{font-weight:700;font-size:14px;color:var(--ink-2)}
@media(max-width:992px){.f-feats{grid-template-columns:repeat(2,1fr);gap:18px 0}.f-feat:nth-child(2){border-right:0}}
@media(max-width:576px){.f-feats{grid-template-columns:1fr}.f-feat{border-right:0;padding:0}}
.f-main{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr 1.3fr;gap:30px;padding:48px 0 40px}
@media(max-width:1100px){.f-main{grid-template-columns:1fr 1fr 1fr}}
@media(max-width:640px){.f-main{grid-template-columns:1fr 1fr;gap:26px 18px}}
.f-brand p{color:var(--muted);font-size:14px;line-height:1.7;margin:14px 0 18px;max-width:300px}
.f-brand .fl{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--ink-2);margin-bottom:8px}
.f-brand .fl i{color:var(--p);width:18px}
.f-col h5{font-size:16px;font-weight:800;margin:0 0 18px;color:var(--ink)}
.f-col a{display:block;font-size:14px;color:var(--muted);padding:5px 0;transition:.15s}
.f-col a:hover{color:var(--p);transform:translateX(3px)}
.f-contact .fc{display:flex;gap:12px;align-items:flex-start;margin-bottom:16px}
.f-contact .fc i{width:36px;height:36px;border-radius:10px;background:#fff;display:grid;place-items:center;color:var(--p);box-shadow:var(--shadow);flex-shrink:0}
.f-contact .fc small{display:block;color:var(--muted);font-size:12px}
.f-contact .fc b{font-size:14px;color:var(--ink)}
.app-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.app-badge{display:flex;align-items:center;gap:8px;background:var(--ink);color:#fff;border-radius:9px;padding:7px 11px;font-size:11px;line-height:1.1;transition:.2s}
.app-badge:hover{color:#fff;transform:translateY(-2px)}
.app-badge i{font-size:18px}
.app-badge b{display:block;font-size:12px}
.f-bottom{border-top:1px solid var(--line);padding:18px 0;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
.payrow{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.payrow .pm{height:34px;width:52px;border-radius:7px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;padding:0;overflow:hidden;box-shadow:0 1px 2px rgba(15,18,48,.05);flex:0 0 auto}
.payrow .pm svg{display:block;height:100%;width:100%}
.payrow.sm .pm{height:26px;width:40px;border-radius:5px}
.pay-icons{display:flex;gap:8px;flex-wrap:wrap}
.pay{height:28px;padding:0 10px;border-radius:7px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;font-size:11px;font-weight:800;letter-spacing:.02em}
.pay.visa{color:#1a1f71}.pay.mc{color:#eb001b}.pay.pp{color:#003087}.pay.amex{color:#2e77bb}.pay.cod{color:var(--ok)}.pay.bank{color:var(--p)}
.socials{display:flex;gap:8px;align-items:center}
.socials a{width:34px;height:34px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;color:var(--ink-2);font-size:14px;transition:.2s}
.socials a:hover{background:var(--grad);color:#fff;border-color:transparent;transform:translateY(-3px) rotate(-6deg)}

/* Back to top */
.totop{position:fixed;right:20px;bottom:84px;width:46px;height:46px;border-radius:14px;background:var(--grad);color:#fff;display:grid;place-items:center;box-shadow:var(--shadow-lg);z-index:800;opacity:0;visibility:hidden;transform:translateY(16px);transition:.3s;border:0;cursor:pointer}
.totop.show{opacity:1;visibility:visible;transform:none}
@media(min-width:769px){.totop{bottom:28px}}

/* Breadcrumb hero shared by inner pages */
.page-hero{position:relative;overflow:hidden;background:var(--ink)}
.page-hero .swiper{height:300px}
@media(max-width:768px){.page-hero .swiper{height:230px}}
.ph-slide{height:100%;display:flex;align-items:center;position:relative;overflow:hidden}
.ph-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transform:scale(1.04);transition:transform 7s ease-out}
.swiper-slide-active .ph-bg{transform:scale(1.12)}
.ph-shade{position:absolute;inset:0;z-index:1;background:linear-gradient(90deg,rgba(15,18,48,.94) 0%,rgba(15,18,48,.8) 45%,rgba(15,18,48,.45) 75%,rgba(15,18,48,.3) 100%)}
.ph-slide::before{content:"";position:absolute;inset:0;z-index:1;background:radial-gradient(600px 300px at 15% 50%,rgba(106,61,245,.5),transparent),radial-gradient(500px 300px at 85% 40%,rgba(34,211,238,.3),transparent)}
.ph-grid{position:absolute;inset:0;z-index:1;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:44px 44px;transform:perspective(500px) rotateX(55deg) translateY(120px) scale(2.2);transform-origin:center bottom;opacity:.5}
.ph-content{position:relative;z-index:2;color:#fff;width:100%}
.ph-crumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.7);margin-bottom:12px}
.ph-crumb a:hover{color:#fff}
.ph-crumb i{font-size:9px}
.ph-content h1{font-size:clamp(28px,4vw,46px);font-weight:800;letter-spacing:-1px;margin:0 0 8px}
.ph-content h1 span{background:linear-gradient(90deg,#c4b5fd,#22d3ee);-webkit-background-clip:text;background-clip:text;color:transparent}
.ph-content p{color:rgba(255,255,255,.75);max-width:560px;margin:0;font-size:15px}
.ph-art{position:absolute;z-index:2;right:6%;top:50%;transform:translateY(-50%);width:200px;height:200px;transform-style:preserve-3d;animation:floatY 6s ease-in-out infinite}
.ph-art .cube{position:absolute;inset:30px;border-radius:28px;background:var(--grad);box-shadow:0 30px 60px -20px rgba(106,61,245,.8);transform:rotateX(18deg) rotateY(-24deg)}
.ph-art .cube::after{content:"";position:absolute;inset:14px;border-radius:20px;border:2px solid rgba(255,255,255,.3)}
.ph-art .ring{position:absolute;inset:0;border-radius:50%;border:2px dashed rgba(34,211,238,.6);animation:spinSlow 20s linear infinite}
.ph-art .ic{position:absolute;inset:0;display:grid;place-items:center;font-size:56px;color:#fff;text-shadow:0 10px 30px rgba(0,0,0,.3)}
@media(max-width:768px){.ph-art{display:none}}
.page-hero .swiper-slide{overflow:hidden}
.page-hero .swiper-pagination-bullet{background:rgba(255,255,255,.5);opacity:1;width:9px;height:9px}
.page-hero .swiper-pagination-bullet-active{background:#fff;width:26px;border-radius:5px}

/* Shared form inputs */
.fld label{display:block;font-size:13px;font-weight:700;color:var(--ink-2);margin-bottom:6px}
.fld label b{color:var(--sale)}
.inp{width:100%;border:1.5px solid var(--line-2);border-radius:12px;padding:12px 14px;font-size:14px;outline:0;color:var(--ink);transition:.2s;background:#fff}
.inp:focus{border-color:var(--p);box-shadow:var(--glow)}
.inp.err{border-color:var(--sale)}
.ferr{font-size:12px;color:var(--sale);margin-top:5px}
</style>
@stack('styles')
</head>
<body>

{{-- ═════════════ TOP BAR ═════════════ --}}
<div class="topbar">
  <div class="wrap">
    <div class="topbar-loc"><i class="fa-solid fa-location-dot"></i><span>{{ zstore('address') }}</span></div>
    <div class="topbar-marquee">
      <span>
        <b>Welcome to {{ zstore('name','Zelari') }}!</b> {{ zfree_over() > 0 ? 'Free delivery on orders over '.zprice(zfree_over()) : 'Free delivery on every order' }}<i class="sep"></i><em>New arrivals</em> added every week<i class="sep"></i>Weekend deals refresh every Friday<i class="sep"></i>Cash on delivery available nationwide<i class="sep"></i>7-day easy returns on every order<i class="sep"></i>
        <b>Welcome to {{ zstore('name','Zelari') }}!</b> {{ zfree_over() > 0 ? 'Free delivery on orders over '.zprice(zfree_over()) : 'Free delivery on every order' }}<i class="sep"></i><em>New arrivals</em> added every week<i class="sep"></i>Weekend deals refresh every Friday<i class="sep"></i>Cash on delivery available nationwide<i class="sep"></i>7-day easy returns on every order<i class="sep"></i>
      </span>
    </div>
  </div>
</div>

{{-- ═════════════ HEADER ═════════════ --}}
<header class="header" id="header">
  <div class="wrap">
    <div class="hdr-main">
      <button class="icon-btn burger" id="burger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>

      <a href="{{ route('home') }}" class="logo" aria-label="{{ zstore('name','Zelari') }} home">@if(zbrand_custom('logo'))<img src="{{ zbrand('logo') }}" alt="{{ zstore('name','Zelari') }}">@else{!! zlogo('logo.svg') !!}@endif</a>


      <div class="search">
        @php $zcc = zcountry(); $zcountries = zcountries(); $zhere = $zcountries[$zcc] ?? reset($zcountries); @endphp
        <div class="loc" id="locPick">
        <button type="button" class="loc-pill" aria-haspopup="listbox" aria-expanded="false" title="Change location & currency">
          <span class="flag"><img src="https://flagcdn.com/w40/{{ strtolower($zcc) }}.png" srcset="https://flagcdn.com/w80/{{ strtolower($zcc) }}.png 2x" alt="{{ $zhere['name'] }}" width="24" height="18"></span>
          <span><small>Deliver to</small><strong>{{ $zhere['name'] }}<em>{{ $zc['code'] }}</em></strong></span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="loc-dd" role="listbox">
          <h6>Choose your location</h6>
          <div class="loc-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Search country or currency…" autocomplete="off" aria-label="Search country"></div>
          <div class="loc-list">
          @foreach($zcountries as $cc => $c)
            <button type="button" class="js-loc {{ $cc === $zcc ? 'on' : '' }}" data-cc="{{ $cc }}" role="option" aria-selected="{{ $cc === $zcc ? 'true' : 'false' }}">
              <span class="flag"><img src="https://flagcdn.com/w40/{{ strtolower($cc) }}.png" srcset="https://flagcdn.com/w80/{{ strtolower($cc) }}.png 2x" alt="" width="24" height="18" loading="lazy"></span><b>{{ $c['name'] }}</b><span>{{ $c['currency'] }}</span>@if($cc === $zcc)<i class="fa-solid fa-check"></i>@endif
            </button>
          @endforeach
          <div class="loc-none">No match. Other countries are shown prices in USD ($).</div>
          </div>
        </div>
      </div>
        <form action="{{ route('shop.index') }}" method="GET">
          <i class="fa-solid fa-magnifying-glass s-ic"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search for products, brands and more…" autocomplete="off">
          <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
      </div>

      <div class="hdr-actions">
        @if(zstore('phone'))<a href="tel:{{ ztel(zstore('phone')) }}" class="hdr-call"><i class="fa-solid fa-phone"></i><div><small>Hotline 24/7</small><strong>{{ zstore('phone') }}</strong></div></a>@endif
        <a href="{{ route('shop.index') }}" class="icon-btn" title="Wishlist"><i class="fa-regular fa-heart"></i></a>
        <a href="{{ route('cart.index') }}" class="hdr-cart" id="hdrCart">
          <span class="icon-btn" id="cartIcon"><i class="fa-solid fa-cart-shopping"></i><b class="cnt" id="cartCount">{{ $cartCount ?? 0 }}</b></span>
          <span><small>My cart</small><em id="cartTotalTxt" style="font-style:normal">{{ zprice($cartTotal ?? 0) }}</em></span>
        </a>
        <div class="acct" id="acctMenu">
          <button type="button" class="icon-btn acct-btn" aria-haspopup="menu" aria-expanded="false" title="Account">
            @auth<span class="acct-av">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>@else<i class="fa-regular fa-user"></i>@endauth
          </button>
          <div class="acct-dd" role="menu">
            @auth
              <div class="acct-head">
                <span class="acct-av lg">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->email }}</small></div>
              </div>
              <a href="{{ route('account.index') }}" role="menuitem"><i class="fa-regular fa-id-badge"></i> My account</a>
              <a href="{{ route('account.index', ['tab' => 'orders']) }}#orders" role="menuitem"><i class="fa-solid fa-box"></i> My orders</a>
              <a href="{{ route('cart.index') }}" role="menuitem"><i class="fa-solid fa-cart-shopping"></i> My cart <em>{{ $cartCount ?? 0 }}</em></a>
              @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                <a href="{{ route('dashboard') }}" role="menuitem" class="is-admin"><i class="fa-solid fa-user-gear"></i> Admin panel</a>
              @endif
              <form method="POST" action="{{ route('logout') }}" class="acct-out">@csrf<button type="submit" role="menuitem"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</button></form>
            @else
              <div class="acct-head guest"><b>Welcome to {{ zstore('name','Zelari') }}</b><small>Log in to track orders and check out faster.</small></div>
              <a href="{{ route('login') }}" class="acct-primary" role="menuitem"><i class="fa-solid fa-arrow-right-to-bracket"></i> Log in</a>
              @if(\Illuminate\Support\Facades\Route::has('register'))<a href="{{ route('register') }}" role="menuitem"><i class="fa-solid fa-user-plus"></i> Create account</a>@endif
            @endauth
          </div>
        </div>
      </div>
    </div>

    <div class="navrow">
      <div class="allcat" id="allcat">
        <button class="allcat-btn" type="button"><i class="fa-solid fa-table-cells-large"></i> All Categories <i class="fa-solid fa-chevron-down"></i></button>
        <div class="dd">
          @if(($navCategories ?? collect())->isNotEmpty())
            @include('shop.partials.cat-menu', ['items' => $navCategories, 'depth' => 0])
          @endif
          <div class="cat-list" style="padding:0 8px 8px"><a href="{{ route('shop.index') }}" class="all">Browse everything <i class="fa-solid fa-arrow-right"></i></a></div>
        </div>
      </div>

      <ul class="nav-main">
        <li class="nav-item"><a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}">Home</a></li>
        <li class="nav-item"><a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*')?'active':'' }}">Shop</a></li>
        {{-- Shop mega-dropdown (categories + Weekend Deals promo) — disabled for now.
             To bring it back: delete the plain <li> above and remove this comment wrapper.
        <li class="nav-item">
          <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*')?'active':'' }}">Shop <i class="fa-solid fa-chevron-down"></i></a>
          <div class="dd shop-dd">
            @if(($navCategories ?? collect())->isNotEmpty())
              @include('shop.partials.cat-menu', ['items' => $navCategories, 'depth' => 0])
            @else
              <ul class="cmenu cmenu-root"><li><a href="{{ route('shop.index') }}"><span class="ci"><i class="fa-solid fa-box"></i></span><span class="nm">All products</span></a></li></ul>
            @endif
            <div class="shop-promo">
              <img src="{{ zbg('hero2', 600) }}" alt="">
              <b>Weekend Deals</b>
              <small>Up to 50% off selected items — limited time</small>
              <a href="{{ route('shop.index',['on_sale'=>1]) }}">Shop deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>
          </div>
        </li>
        --}}
        <li class="nav-item"><a href="{{ route('about') }}" class="{{ request()->routeIs('about')?'active':'' }}">About</a></li>
        <li class="nav-item"><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact')?'active':'' }}">Contact</a></li>
      </ul>

      <a href="{{ route('shop.index',['on_sale'=>1]) }}" class="deal-btn"><i class="fa-solid fa-bolt"></i> Deal Today</a>
    </div>
  </div>
</header>

{{-- ═════════════ MOBILE DRAWER ═════════════ --}}
<div class="drawer-bg" id="drawerBg"></div>
<aside class="drawer" id="drawer">
  <div class="drawer-head">
    <a href="{{ route('home') }}" class="logo sm">@if(zbrand_custom('logo'))<img src="{{ zbrand('logo') }}" alt="{{ zstore('name','Zelari') }}">@else{!! zlogo('logo.svg', 'd') !!}@endif</a>
    <button class="icon-btn" id="drawerClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <form action="{{ route('shop.index') }}" method="GET"><input type="text" name="q" placeholder="Search products…"><button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button></form>
  <div class="dloc"><label>Location &amp; currency</label>
    <select class="js-loc-select">
      @foreach(zcountries() as $cc => $c)<option value="{{ $cc }}" {{ $cc === zcountry() ? 'selected' : '' }}>{{ $c['name'] }} — {{ $c['currency'] }}</option>@endforeach
    </select>
  </div>
  <nav>
    <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a>
    <a href="{{ route('shop.index') }}"><i class="fa-solid fa-bag-shopping"></i> Shop</a>
    <a href="{{ route('shop.index',['on_sale'=>1]) }}"><i class="fa-solid fa-bolt"></i> Deal Today</a>
    <a href="{{ route('about') }}"><i class="fa-solid fa-circle-info"></i> About</a>
    <a href="{{ route('contact') }}"><i class="fa-solid fa-envelope"></i> Contact</a>
    <a href="{{ route('cart.index') }}"><i class="fa-solid fa-cart-shopping"></i> Cart ({{ $cartCount ?? 0 }})</a>
    @auth
      <a href="{{ route('account.index') }}"><i class="fa-regular fa-id-badge"></i> My account</a>
      <a href="{{ route('account.index', ['tab' => 'orders']) }}#orders"><i class="fa-solid fa-box"></i> My orders</a>
      @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())<a href="{{ route('dashboard') }}"><i class="fa-solid fa-user-gear"></i> Admin panel</a>@endif
      <form method="POST" action="{{ route('logout') }}" style="margin:0;border:0;padding:0;display:block">@csrf<button type="submit" style="display:flex;align-items:center;gap:12px;padding:13px 18px;font-weight:700;font-size:15px;border:0;border-bottom:1px solid var(--surface);background:none;width:100%;text-align:left;color:var(--sale);font-family:inherit;cursor:pointer"><i class="fa-solid fa-arrow-right-from-bracket" style="width:22px"></i> Log out</button></form>
    @else
      <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-right-to-bracket"></i> Log in</a>
      @if(\Illuminate\Support\Facades\Route::has('register'))<a href="{{ route('register') }}"><i class="fa-solid fa-user-plus"></i> Create account</a>@endif
    @endauth
  </nav>
  <div class="dcats">
    <h6>Categories</h6>
    @include('shop.partials.cat-tree', ['items' => $navCategories ?? collect(), 'activePath' => [], 'depth' => 0])
  </div>
</aside>

{{-- ═════════════ FLASH TOASTS ═════════════ --}}
<div class="toasts" id="toasts">
  @if(session('cart_success'))<div class="toast-z"><i class="ic fa-solid fa-circle-check"></i><div>{{ session('cart_success') }} <a href="{{ route('cart.index') }}">View cart</a></div><button class="x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button></div>@endif
  @if(session('success'))<div class="toast-z"><i class="ic fa-solid fa-circle-check"></i><div>{{ session('success') }}</div><button class="x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button></div>@endif
  @if(session('cart_error'))<div class="toast-z err"><i class="ic fa-solid fa-circle-exclamation"></i><div>{{ session('cart_error') }}</div><button class="x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button></div>@endif
  @if(session('info'))<div class="toast-z info"><i class="ic fa-solid fa-circle-info"></i><div>{{ session('info') }}</div><button class="x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button></div>@endif
</div>

{{-- ═════════════ PAGE ═════════════ --}}
<main>
  @yield('content')
</main>

{{-- ═════════════ NEWSLETTER ═════════════ --}}
@unless(request()->routeIs('checkout.*') || request()->routeIs('order.*'))
<section class="newsletter wrap reveal">
  <div class="nl-box">
    <img class="bg" src="{{ zbg('news') }}" alt="" loading="lazy">
    <div>
      <h3>Join Our Newsletter And Get…</h3>
      <p>{{ zprice(500) }} discount for your first order</p>
      <form class="nl-form" onsubmit="event.preventDefault();zToast('Thanks! You are on the list.');this.reset()">
        <input type="email" placeholder="Enter Your Email" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
    <div class="nl-art"><div class="bub b1">🎁</div><div class="bub b2">✨</div><div class="bub b3">💬</div></div>
  </div>
</section>
@endunless

{{-- ═════════════ FOOTER ═════════════ --}}
<footer class="footer">
  <div class="wrap">
    <div class="f-feats">
      <div class="f-feat"><i class="fa-solid fa-gem"></i><span>Curated Quality Products</span></div>
      <div class="f-feat"><i class="fa-solid fa-truck-fast"></i><span>{{ zfree_over() > 0 ? 'Free Delivery Over '.zprice(zfree_over()) : 'Free Delivery On Every Order' }}</span></div>
      <div class="f-feat"><i class="fa-solid fa-percent"></i><span>Daily Mega Discounts</span></div>
      <div class="f-feat"><i class="fa-solid fa-tags"></i><span>Best Price On The Market</span></div>
    </div>

    <div class="f-main">
      <div class="f-brand">
        <a href="{{ route('home') }}" class="logo lg">@if(zbrand_custom('logo'))<img src="{{ zbrand('logo') }}" alt="{{ zstore('name','Zelari') }}">@else{!! zlogo('logo-tagline.svg') !!}@endif</a>
        <p>{{ zstore('tagline') ? zstore('tagline').'. ' : '' }}We curate quality products, keep prices honest, and deliver fast across Pakistan — with cash on delivery everywhere.</p>
        @if(zstore('address'))<div class="fl"><i class="fa-solid fa-location-dot"></i> {{ zstore('address') }}</div>@endif
        @if(zstore('email'))<div class="fl"><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ zstore('email') }}">{{ zstore('email') }}</a></div>@endif
      </div>
      <div class="f-col">
        <h5>Categories</h5>
        @foreach(($navCategories ?? collect())->take(6) as $c)<a href="{{ route('shop.index',['category'=>$c->slug]) }}">{{ $c->name }}</a>@endforeach
        @if(($navCategories ?? collect())->isEmpty())<a href="{{ route('shop.index') }}">All products</a>@endif
      </div>
      <div class="f-col">
        <h5>Useful Links</h5>
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('shop.index') }}">Shop</a>
        <a href="{{ route('about') }}">About Us</a>
        <a href="{{ route('contact') }}">Contact Us</a>
        <a href="{{ route('cart.index') }}">Cart</a>
      </div>
      <div class="f-col">
        <h5>Help Center</h5>
        <a href="{{ route('cart.index') }}">Your Order</a>
        <a href="{{ route('contact') }}">Track Order</a>
        <a href="{{ route('shop.index') }}">Your Wishlist</a>
        <a href="{{ route('shop.index') }}">Search</a>
        <a href="{{ route('contact') }}">FAQ</a>
      </div>
      <div class="f-col f-contact">
        <h5>Contact Us</h5>
        @if(zstore('phone'))<a href="tel:{{ ztel(zstore('phone')) }}" class="fc" style="display:flex;color:inherit"><i class="fa-solid fa-phone"></i><div><small>Hotline 24/7</small><b>{{ zstore('phone') }}</b></div></a>@endif
        @if(zstore('email'))<a href="mailto:{{ zstore('email') }}" class="fc" style="display:flex;color:inherit"><i class="fa-solid fa-envelope"></i><div><small>Email Address</small><b>{{ zstore('email') }}</b></div></a>@endif
        @if(zstore('hours'))<div class="fc"><i class="fa-regular fa-clock"></i><div><small>Support hours</small><b>{{ zstore('hours') }}</b></div></div>@endif
        <small style="color:var(--muted);font-size:12px;display:block;margin-bottom:8px">We accept :</small>
        @include('shop.partials.payment-icons', ['size' => 'sm'])
      </div>
    </div>

    <div class="f-bottom">
      <div>©{{ date('Y') }} {{ zstore('name','Zelari') }}. All rights reserved.</div>
      @include('shop.partials.payment-icons')
      <div class="socials"><span style="font-size:12px;margin-right:4px">Stay connected :</span>
        <a href="{{ zstore('facebook','#') }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a><a href="{{ zstore('twitter','#') }}" target="_blank" rel="noopener"><i class="fa-brands fa-x-twitter"></i></a><a href="{{ zstore('instagram','#') }}" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a><a href="{{ zstore('pinterest','#') }}" target="_blank" rel="noopener"><i class="fa-brands fa-pinterest-p"></i></a>
      </div>
    </div>
  </div>
</footer>

{{-- ═════════════ MOBILE BOTTOM NAV ═════════════ --}}
<nav class="mnav">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}"><i class="fa-solid fa-house"></i>Home</a>
  <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*')?'active':'' }}"><i class="fa-solid fa-bag-shopping"></i>Shop</a>
  <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*')?'active':'' }}"><i class="fa-solid fa-cart-shopping"></i>Cart<b class="cnt js-cart-count">{{ $cartCount ?? 0 }}</b></a>
  <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact')?'active':'' }}"><i class="fa-regular fa-comment-dots"></i>Help</a>
</nav>

<button class="totop" id="toTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
/* ───── Z engine: tilt, reveal, toasts, cart, countdown ───── */
(function(){
  const fine = matchMedia('(hover:hover) and (pointer:fine)').matches;
  const rm   = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* 3D tilt */
  window.zTilt = function(root){
    if(!fine || rm) return;
    (root||document).querySelectorAll('[data-tilt]:not(.tilt-ready)').forEach(el=>{
      el.classList.add('tilt-ready');
      if(!el.querySelector('.tilt-glare')){ const g=document.createElement('span'); g.className='tilt-glare'; el.appendChild(g); }
      const max = parseFloat(el.dataset.tilt)||8;
      el.addEventListener('mousemove', e=>{
        const r=el.getBoundingClientRect(), x=(e.clientX-r.left)/r.width-.5, y=(e.clientY-r.top)/r.height-.5;
        el.style.setProperty('--rx',(-y*max)+'deg'); el.style.setProperty('--ry',(x*max)+'deg');
        el.style.setProperty('--gx',(x*100+50)+'%'); el.style.setProperty('--gy',(y*100+50)+'%');
        el.classList.add('tilting');
      });
      el.addEventListener('mouseleave', ()=>{ el.style.setProperty('--rx','0deg'); el.style.setProperty('--ry','0deg'); el.classList.remove('tilting'); });
    });
  };

  /* Reveal on scroll */
  const io = new IntersectionObserver(es=>es.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} }),{rootMargin:'0px 0px -8% 0px',threshold:.08});
  window.zReveal = function(root){ (root||document).querySelectorAll('.reveal:not(.in)').forEach(el=>io.observe(el)); };

  /* Toast */
  window.zToast = function(msg,type){
    const t=document.createElement('div'); t.className='toast-z '+(type||'');
    const ic = type==='err'?'fa-circle-exclamation':type==='info'?'fa-circle-info':'fa-circle-check';
    t.innerHTML='<i class="ic fa-solid '+ic+'"></i><div>'+msg+'</div><button class="x"><i class="fa-solid fa-xmark"></i></button>';
    t.querySelector('.x').onclick=()=>t.remove();
    document.getElementById('toasts').appendChild(t);
    setTimeout(()=>{ t.style.transition='opacity .4s'; t.style.opacity='0'; setTimeout(()=>t.remove(),400); },4200);
  };
  document.querySelectorAll('#toasts .toast-z').forEach(t=>setTimeout(()=>{ t.style.transition='opacity .4s'; t.style.opacity='0'; setTimeout(()=>t.remove(),400); },5000));

  /* AJAX add-to-cart with fly animation */
  const token = document.querySelector('meta[name=csrf-token]').content;
  function fly(from){
    const to=document.getElementById('cartIcon'); if(!from||!to||rm) return;
    const a=from.getBoundingClientRect(), b=to.getBoundingClientRect();
    const d=document.createElement('div');
    d.style.cssText='position:fixed;z-index:3000;left:'+(a.left+a.width/2-14)+'px;top:'+(a.top+a.height/2-14)+'px;width:28px;height:28px;border-radius:50%;background:var(--grad);box-shadow:0 10px 24px -8px rgba(106,61,245,.8);transition:all .8s cubic-bezier(.2,.8,.2,1);pointer-events:none';
    document.body.appendChild(d);
    requestAnimationFrame(()=>{ d.style.left=(b.left+b.width/2-6)+'px'; d.style.top=(b.top+b.height/2-6)+'px'; d.style.width='12px'; d.style.height='12px'; d.style.opacity='.2'; });
    setTimeout(()=>{ d.remove(); to.classList.add('bump'); setTimeout(()=>to.classList.remove('bump'),500); },800);
  }
  const CART_URL='{{ route('cart.index') }}';
  function showGoToCart(btn){
    if(!btn) return;
    const a=document.createElement('a'); a.href=CART_URL; a.className=btn.className+' go-cart'; a.innerHTML='<i class="fa-solid fa-cart-shopping"></i> Go to Cart';
    a.style.cssText='background:var(--grad);color:#fff;box-shadow:0 10px 20px -10px rgba(106,61,245,.8);text-decoration:none';
    btn.style.display='none'; btn.insertAdjacentElement('afterend', a);
    setTimeout(()=>{ a.remove(); btn.style.display=''; btn.disabled=false; }, 2000);
  }
  document.addEventListener('submit', async e=>{
    const f=e.target; if(!f.classList.contains('js-atc')) return;
    e.preventDefault();
    const btn=f.querySelector('button[type=submit]'); const old=btn?btn.innerHTML:''; if(btn){btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';}
    const restore=()=>{ if(btn){btn.innerHTML=old;btn.disabled=false;} };
    try{
      const r=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(f)});
      if(r.ok){
        const j=await r.json();
        document.querySelectorAll('#cartCount,.js-cart-count').forEach(el=>el.textContent=j.count);
        if(j.total!==undefined){ const t=document.getElementById('cartTotalTxt'); if(t) t.textContent=window.zmoney(j.total); }
        fly(btn); zToast('<b>Added to cart!</b> '+(j.name?('“'+j.name+'” is in your cart. '):'')+'<a href="'+CART_URL+'">View cart</a>');
        if(j.notice) setTimeout(()=>zToast(j.notice,'info'),250);
        if(btn){ btn.innerHTML=old; showGoToCart(btn); }
      } else {
        let msg='Could not add to cart';
        try{ const j=await r.json(); msg=Object.values(j.errors||{}).flat()[0]||j.message||msg; }catch(_){}
        zToast(msg,'err'); restore();
      }
    }catch(_){ zToast('Network error — please try again','err'); restore(); }
  });

  /* Countdown: data-end = unix ms */
  window.zCountdown = function(el){
    const end = parseInt(el.dataset.end,10); const b = el.querySelectorAll('[data-u]');
    function tick(){ let d=Math.max(0,end-Date.now()); const u={d:Math.floor(d/864e5),h:Math.floor(d/36e5)%24,m:Math.floor(d/6e4)%60,s:Math.floor(d/1e3)%60}; b.forEach(x=>x.textContent=String(u[x.dataset.u]).padStart(2,'0')); }
    tick(); setInterval(tick,1000);
  };

  /* Header + drawer + dropdowns */
  const header=document.getElementById('header');
  const onScroll=()=>{ header.classList.toggle('scrolled',scrollY>10); document.getElementById('toTop').classList.toggle('show',scrollY>500); };
  addEventListener('scroll',onScroll,{passive:true}); onScroll();
  document.getElementById('toTop').onclick=()=>scrollTo({top:0,behavior:'smooth'});
  const open=()=>document.body.classList.add('drawer-open'), close=()=>document.body.classList.remove('drawer-open');
  document.getElementById('burger').onclick=open; document.getElementById('drawerClose').onclick=close; document.getElementById('drawerBg').onclick=close;
  document.querySelectorAll('.allcat-btn').forEach(b=>b.addEventListener('click',()=>b.parentElement.classList.toggle('open')));
  document.querySelectorAll('.shop-dd .cmenu-root > li').forEach(li=>{
    const dd=li.closest('.shop-dd');
    li.addEventListener('mouseenter',()=>dd.classList.toggle('sub-open',li.classList.contains('has-sub')));
  });
  document.querySelectorAll('.shop-dd').forEach(dd=>dd.addEventListener('mouseleave',()=>dd.classList.remove('sub-open')));
  document.querySelectorAll('.cmenu li.has-sub').forEach(li=>li.addEventListener('mouseenter',()=>{
    const sub=li.querySelector(':scope > .cmenu-sub'); if(!sub) return;
    const r=li.getBoundingClientRect(); sub.style.top='0px'; sub.style.left='0px';
    const w=sub.offsetWidth, h=sub.offsetHeight;
    let left=r.right+6; if(left+w+8>innerWidth) left=r.left-w-6;
    let top=r.top-8; if(top+h+8>innerHeight) top=Math.max(8, innerHeight-h-8);
    sub.style.left=left+'px'; sub.style.top=top+'px';
  }));
  /* keep flyouts attached while the list scrolls: hide them until re-hovered */
  document.querySelectorAll('.cmenu').forEach(ul=>ul.addEventListener('scroll',()=>{ ul.querySelectorAll(':scope > li.has-sub > .cmenu-sub').forEach(sub=>{ sub.style.visibility='hidden'; sub.style.opacity='0'; }); },{passive:true}));
  document.querySelectorAll('.cmenu li.has-sub').forEach(li=>li.addEventListener('mouseenter',()=>{ const sub=li.querySelector(':scope > .cmenu-sub'); if(sub){ sub.style.visibility=''; sub.style.opacity=''; } }));
  document.addEventListener('click',e=>{ if(!e.target.closest('.allcat')) document.querySelectorAll('.allcat.open').forEach(x=>x.classList.remove('open')); });

  /* Account menu */
  const acct=document.getElementById('acctMenu');
  if(acct){
    const b=acct.querySelector('.acct-btn');
    b.addEventListener('click',e=>{ e.stopPropagation(); const o=acct.classList.toggle('open'); b.setAttribute('aria-expanded',o); document.getElementById('locPick')?.classList.remove('open'); });
    document.addEventListener('click',e=>{ if(!e.target.closest('#acctMenu')) acct.classList.remove('open'); });
    document.addEventListener('keydown',e=>{ if(e.key==='Escape') acct.classList.remove('open'); });
  }

  /* Location / currency picker */
  const LOC_URL=@json(route('location.set'));
  const loc=document.getElementById('locPick');
  if(loc){
    const pill=loc.querySelector('.loc-pill');
    pill.addEventListener('click',e=>{ e.stopPropagation(); const o=loc.classList.toggle('open'); pill.setAttribute('aria-expanded',o); });
    document.addEventListener('click',e=>{ if(!e.target.closest('#locPick')) loc.classList.remove('open'); });
    document.addEventListener('keydown',e=>{ if(e.key==='Escape') loc.classList.remove('open'); });
    const q=loc.querySelector('.loc-search input'), list=loc.querySelector('.loc-list');
    if(q){
      q.addEventListener('click',e=>e.stopPropagation());
      q.addEventListener('input',()=>{ const v=q.value.trim().toLowerCase(); let n=0; list.querySelectorAll('.js-loc').forEach(b=>{ const hit=!v||b.textContent.toLowerCase().includes(v)||b.dataset.cc.toLowerCase()===v; b.style.display=hit?'':'none'; if(hit) n++; }); list.classList.toggle('empty',n===0); });
      pill.addEventListener('click',()=>{ if(loc.classList.contains('open')){ q.value=''; q.dispatchEvent(new Event('input')); setTimeout(()=>q.focus(),50); } });
    }
  }
  async function setLocation(cc){
    try{
      const fd=new FormData(); fd.append('country',cc);
      const r=await fetch(LOC_URL,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
      const j=await r.json().catch(()=>({}));
      if(!r.ok||!j.ok){ zToast(j.message||'Could not change location','err'); return; }
      zToast('Prices now shown in '+j.currency+' ('+j.symbol+')','ok');
      setTimeout(()=>location.reload(),350);
    }catch(_){ zToast('Network error — please try again','err'); }
  }
  document.querySelectorAll('.js-loc').forEach(b=>b.addEventListener('click',()=>{ if(!b.classList.contains('on')) setLocation(b.dataset.cc); else loc.classList.remove('open'); }));
  document.querySelectorAll('.js-loc-select').forEach(s=>s.addEventListener('change',()=>setLocation(s.value)));

  document.addEventListener('DOMContentLoaded',()=>{ zTilt(); zReveal(); document.querySelectorAll('[data-countdown]').forEach(zCountdown); });
})();
</script>
@stack('scripts')
</body>
</html>
