<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Account') · {{ zstore('name', 'Zelari') }}</title>
<link rel="icon" href="{{ zbrand('icon') }}">
@include('partials.theme-vars')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink);background:var(--surface);-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}
.auth{min-height:100vh;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1fr)}
@media(max-width:960px){.auth{grid-template-columns:1fr}.auth .auth-art{display:none}}
/* ── Brand side ── */
.auth-art{position:relative;overflow:hidden;background:var(--brand-dark);color:#fff;padding:48px 56px;display:flex;flex-direction:column;justify-content:space-between}
.auth-art .bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.28;filter:saturate(1.1)}
.auth-art .shade{position:absolute;inset:0;background:linear-gradient(160deg,rgba(15,18,48,.55) 0%,rgba(30,27,75,.9) 60%,rgba(15,18,48,.98) 100%)}
.auth-art .orb{position:absolute;border-radius:50%;filter:blur(70px);opacity:.45;pointer-events:none}
.auth-art .o1{width:380px;height:380px;background:var(--brand-light);top:-120px;left:-80px;animation:drift 14s ease-in-out infinite}
.auth-art .o2{width:300px;height:300px;background:var(--accent);bottom:-120px;right:-60px;animation:drift 11s ease-in-out infinite reverse}
@keyframes drift{0%,100%{transform:translate(0,0)}50%{transform:translate(40px,30px)}}
.auth-art>*{position:relative}
.auth-logo img,.auth-logo svg{height:44px;width:auto;display:block}
.auth-logo img{background:#fff;border-radius:12px;padding:6px 10px}
.auth-copy h1{font-size:clamp(30px,3.4vw,44px);line-height:1.08;letter-spacing:-1.2px;margin:0 0 14px;font-weight:800}
.auth-copy h1 span{background:linear-gradient(90deg,#c4b5fd,var(--accent));-webkit-background-clip:text;background-clip:text;color:transparent}
.auth-copy p{font-size:16px;line-height:1.6;color:rgba(255,255,255,.78);max-width:460px;margin:0 0 28px}
.perks{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:520px}
.perk{display:flex;gap:12px;align-items:flex-start;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:14px;padding:13px 14px;backdrop-filter:blur(8px)}
.perk i{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.12);display:grid;place-items:center;color:var(--accent);flex-shrink:0;font-size:14px}
.perk b{display:block;font-size:13.5px}
.perk small{display:block;color:rgba(255,255,255,.65);font-size:12px;margin-top:2px}
.auth-foot{font-size:12.5px;color:rgba(255,255,255,.5)}
/* ── Form side ── */
.auth-main{display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{width:100%;max-width:460px}
.auth-mobile-logo{display:none;margin-bottom:26px}
.auth-mobile-logo img,.auth-mobile-logo svg{height:40px;width:auto}
@media(max-width:960px){.auth-mobile-logo{display:block}}
.auth-box{background:#fff;border:1px solid var(--line);border-radius:24px;padding:36px;box-shadow:0 30px 60px -30px rgba(var(--shadow-rgb),.25)}
@media(max-width:480px){.auth-box{padding:26px 20px;border-radius:18px}}
.auth-box h2{font-size:26px;letter-spacing:-.6px;margin:0 0 6px;font-weight:800}
.auth-box .lead{color:var(--muted);font-size:14px;margin:0 0 24px;line-height:1.55}
.fld{margin-bottom:16px}
.fld label{display:block;font-size:13px;font-weight:700;color:var(--ink-2);margin-bottom:7px}
.fld label .opt{color:var(--faint);font-weight:600}
.fld .row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px}
.fld .row-between label{margin:0}
.ctl{position:relative}
.ctl>i.lead-ic{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--faint);font-size:14px;pointer-events:none}
.inp{width:100%;height:48px;border:1.5px solid var(--line-2);border-radius:13px;padding:0 44px 0 42px;font:inherit;font-size:14px;color:var(--ink);background:#fff;outline:0;transition:.2s}
.inp::placeholder{color:#b3b8d0}
.inp:focus{border-color:var(--brand);box-shadow:0 0 0 4px rgba(var(--shadow-rgb),.12)}
.inp.err{border-color:var(--sale);background:#fff7f9}
.ctl .eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:32px;height:32px;border:0;background:none;color:var(--faint);cursor:pointer;border-radius:8px}
.ctl .eye:hover{color:var(--ink-2);background:var(--surface)}
.ferr{color:var(--sale);font-size:12.5px;margin-top:6px;font-weight:600}
.chk{display:flex;align-items:center;gap:9px;font-size:13.5px;color:var(--ink-2);cursor:pointer;user-select:none}
.chk input{width:17px;height:17px;accent-color:var(--brand)}
.link{color:var(--brand);font-weight:700}
.link:hover{text-decoration:underline}
.btn-brand{width:100%;height:50px;border-radius:13px;font-size:15px;margin-top:6px}
.alt{display:flex;align-items:center;gap:12px;margin:22px 0 18px;color:var(--faint);font-size:12px;font-weight:700;letter-spacing:.06em}
.alt::before,.alt::after{content:"";flex:1;height:1px;background:var(--line)}
.auth-switch{text-align:center;font-size:14px;color:var(--muted)}
.status-ok{display:flex;gap:10px;align-items:center;background:#e6f9f2;border:1px solid #bdeedb;color:#0d6a4f;border-radius:12px;padding:11px 14px;font-size:13.5px;margin-bottom:18px}
.status-err{display:flex;gap:10px;align-items:flex-start;background:#fdecef;border:1px solid #ffc7d4;color:#b4124b;border-radius:12px;padding:11px 14px;font-size:13.5px;margin-bottom:18px}
.strength{display:flex;gap:4px;margin-top:8px}
.strength i{flex:1;height:4px;border-radius:4px;background:var(--line)}
.strength[data-s="1"] i:nth-child(-n+1){background:var(--sale)}
.strength[data-s="2"] i:nth-child(-n+2){background:var(--warn)}
.strength[data-s="3"] i:nth-child(-n+3){background:#22c55e}
.strength[data-s="4"] i{background:var(--ok)}
.back-home{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--muted);margin-top:22px}
.back-home:hover{color:var(--brand)}
</style>
</head>
<body>
<div class="auth">
  <aside class="auth-art">
    <img class="bg" src="{{ zbg('hero1', 1400) }}" alt=""><span class="shade"></span><span class="orb o1"></span><span class="orb o2"></span>
    <a href="{{ route('home') }}" class="auth-logo" aria-label="{{ zstore('name','Zelari') }}">
      @if(zbrand_custom('logo'))<img src="{{ zbrand('logo') }}" alt="{{ zstore('name','Zelari') }}">@else{!! zlogo('logo-white.svg', 'g') !!}@endif
    </a>
    <div class="auth-copy">
      @php
        // Headline: the last ~45% of the words are highlighted (e.g. "Shopping that feels" + "simple, honest and fast.")
        $zw = preg_split('/\s+/u', trim(zcontent('auth_title', 'Shopping that feels simple, honest and fast.')));
        $zcut = count($zw) > 3 ? max(1, (int) floor(count($zw) * 0.5)) : count($zw);
        $zt = implode(' ', array_slice($zw, 0, $zcut)); $zh = implode(' ', array_slice($zw, $zcut));
      @endphp
      <h1>@hasSection('art_title')@yield('art_title')@else{{ $zt }} @if($zh)<span>{{ $zh }}</span>@endif @endif</h1>
      <p>@hasSection('art_text')@yield('art_text')@else{{ zcontent('auth_text', 'One account for faster checkout, order tracking and invoices — across {store}.') }}@endif</p>
      @php $zperks = collect([1,2,3,4])->map(fn($i) => ['icon'=>trim((string) \App\Models\Setting::get("auth_perk{$i}_icon", '')), 'title'=>zcontent("auth_perk{$i}_title", ''), 'text'=>zcontent("auth_perk{$i}_text", '')])->filter(fn($p) => $p['title'] !== ''); @endphp
      @if($zperks->isNotEmpty())
      <div class="perks">
        @foreach($zperks as $pk)
        <div class="perk"><i class="fa-solid {{ $pk['icon'] ?: 'fa-circle-check' }}"></i><div><b>{{ $pk['title'] }}</b>@if($pk['text'])<small>{{ ucfirst($pk['text']) }}</small>@endif</div></div>
        @endforeach
      </div>
      @endif
    </div>
    <div class="auth-foot">© {{ date('Y') }} {{ zstore('name','Zelari') }} · {{ zstore('tagline','') }}</div>
  </aside>

  <main class="auth-main">
    <div class="auth-card">
      <a href="{{ route('home') }}" class="auth-mobile-logo">@if(zbrand_custom('logo'))<img src="{{ zbrand('logo') }}" alt="">@else{!! zlogo('logo.svg', 'm') !!}@endif</a>
      <div class="auth-box">
        {{ $slot }}
      </div>
      <a href="{{ route('home') }}" class="back-home"><i class="fa-solid fa-arrow-left"></i> Back to the store</a>
    </div>
  </main>
</div>
<script>
function togglePwd(id, btn){ const i=document.getElementById(id); const on=i.type==='password'; i.type=on?'text':'password'; btn.innerHTML='<i class="fa-regular '+(on?'fa-eye-slash':'fa-eye')+'"></i>'; }
</script>
</body>
</html>
