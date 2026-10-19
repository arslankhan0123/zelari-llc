@extends('layouts.store')
@section('title', 'Contact Us')

@push('styles')
<style>
.ct{padding:56px 0 10px}
.ct-grid{display:grid;grid-template-columns:1.25fr 1fr;gap:28px;align-items:start}
@media(max-width:992px){.ct-grid{grid-template-columns:1fr}}
.ct-card{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:30px}
.ct-card h2{font-size:22px;font-weight:800;margin:0 0 6px;letter-spacing:-.4px}
.ct-card>p{color:var(--muted);font-size:14px;margin:0 0 22px}
.ok-box{background:#e6f9f2;border:1px solid #bdeedb;color:#0d6a4f;border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:10px;align-items:flex-start;font-size:14px;animation:popIn .4s ease}
.info{display:grid;gap:14px}
.ibox{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:20px;display:flex;gap:14px;align-items:flex-start;transition:.3s}
.ibox:hover{border-color:var(--p);box-shadow:var(--shadow)}
.ibox .ic{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:18px;flex-shrink:0;transform-style:preserve-3d;transition:.5s}
.ibox:hover .ic{transform:translateZ(20px) rotateY(360deg)}
.ibox b{display:block;font-size:15px;margin-bottom:3px}
.ibox span{display:block;font-size:14px;color:var(--ink-2)}
.ibox small{color:var(--faint);font-size:12px}
.wa{border-radius:var(--r-lg);background:linear-gradient(135deg,#25d366,#128c7e);color:#fff;padding:24px;position:relative;overflow:hidden}
.wa::before{content:"";position:absolute;inset:0;background:radial-gradient(300px 150px at 100% 0%,rgba(255,255,255,.35),transparent 60%)}
.wa b{font-size:18px;display:block;margin-bottom:4px;position:relative}
.wa p{font-size:13.5px;color:rgba(255,255,255,.9);margin:0 0 14px;position:relative}
.wa a{display:inline-flex;align-items:center;gap:8px;background:#fff;color:#128c7e;padding:10px 16px;border-radius:10px;font-weight:800;font-size:13.5px;position:relative}
.wa .e{position:absolute;right:14px;bottom:-14px;font-size:84px;opacity:.9;animation:floatY 5s ease-in-out infinite}
.hours{display:grid;grid-template-columns:1fr auto;gap:6px 20px;font-size:13.5px}
.hours span{color:var(--muted)}
.hours b{font-weight:700}
.map{margin-top:28px;border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--line);height:300px;position:relative;background:radial-gradient(600px 300px at 30% 50%,#efeaff,transparent),radial-gradient(500px 300px at 80% 50%,#e4fbff,transparent),var(--surface)}
.map .grid-bg{position:absolute;inset:0;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:40px 40px;opacity:.7}
.map .pin{position:absolute;left:50%;top:50%;transform:translate(-50%,-100%);text-align:center}
.map .pin i{font-size:46px;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 14px 16px rgba(106,61,245,.4));animation:floatY2 3s ease-in-out infinite;display:block}
.map .pin span{display:inline-block;margin-top:8px;background:#fff;border-radius:10px;padding:8px 14px;font-size:13px;font-weight:700;box-shadow:var(--shadow)}
.map .ring{position:absolute;left:50%;top:50%;width:14px;height:14px;margin:-7px;border-radius:50%;background:var(--p);animation:pulseGlow 2s infinite}
.faq{margin-top:40px}
.faq details{background:#fff;border:1px solid var(--line);border-radius:14px;padding:0 20px;margin-bottom:10px;transition:.2s}
.faq details[open]{border-color:var(--p);box-shadow:var(--shadow)}
.faq summary{cursor:pointer;font-weight:700;font-size:15px;padding:16px 0;list-style:none;display:flex;justify-content:space-between;align-items:center}
.faq summary::-webkit-details-marker{display:none}
.faq summary i{color:var(--p);transition:transform .3s}
.faq details[open] summary i{transform:rotate(45deg)}
.faq details p{color:var(--muted);font-size:14px;margin:0 0 16px;line-height:1.7}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', ['id'=>'contactHero','crumbs'=>[['Home',route('home')],['Contact',null]],'slides'=>[
  ['title'=>'Get in <span>Touch</span>','text'=>'Questions, feedback or just saying hello — we\'d love to hear from you.','icon'=>'fa-comments'],
  ['title'=>'We Reply <span>Within 24 Hours</span>','text'=>'Monday to Saturday, 9 AM – 6 PM PKT. Usually much faster.','icon'=>'fa-clock'],
]])

<section class="ct wrap">
  <div class="ct-grid">
    <div class="ct-card reveal">
      @if(session('success'))<div class="ok-box"><i class="fa-solid fa-circle-check" style="font-size:18px"></i><div><b>Message sent!</b><br>{{ session('success') }}</div></div>@endif
      <h2>Send us a message</h2>
      <p>Fill in the form and our team will get back to you shortly.</p>
      <form action="{{ route('contact') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-sm-6 fld"><label>Your name <b>*</b></label><input class="inp {{ $errors->has('name')?'err':'' }}" name="name" value="{{ old('name') }}" required placeholder="Full name">@error('name')<div class="ferr">{{ $message }}</div>@enderror</div>
          <div class="col-sm-6 fld"><label>Email <b>*</b></label><input class="inp {{ $errors->has('email')?'err':'' }}" type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">@error('email')<div class="ferr">{{ $message }}</div>@enderror</div>
          <div class="col-12 fld"><label>Subject</label><input class="inp {{ $errors->has('subject')?'err':'' }}" name="subject" value="{{ old('subject') }}" placeholder="How can we help?">@error('subject')<div class="ferr">{{ $message }}</div>@enderror</div>
          <div class="col-12 fld"><label>Message <b>*</b></label><textarea class="inp {{ $errors->has('message')?'err':'' }}" name="message" rows="6" required placeholder="Write your message here…">{{ old('message') }}</textarea>@error('message')<div class="ferr">{{ $message }}</div>@enderror</div>
          <div class="col-12"><button type="submit" class="btn-g" style="padding:13px 26px;font-size:15px"><i class="fa-solid fa-paper-plane"></i> Send message</button></div>
        </div>
      </form>
    </div>

    <div class="info">
      @foreach([['fa-envelope','var(--p-soft)','var(--p)','Email us',zstore('email'),'We reply within 24 hours','mailto:'.zstore('email')],['fa-phone','#e6f9f2','#0d8f6a','Call us',zstore('phone'),zstore('hours'),'tel:'.ztel(zstore('phone'))],['fa-location-dot','#fff5e0','#b36b00','Visit us',zstore('address'),'By appointment',null]] as $i => [$ic,$bg,$c,$t,$v,$s,$href])
        @if($v)
        @if($href)<a href="{{ $href }}" class="ibox reveal" data-delay="{{ $i+1 }}">@else<div class="ibox reveal" data-delay="{{ $i+1 }}">@endif<div class="ic" style="background:{{ $bg }};color:{{ $c }}"><i class="fa-solid {{ $ic }}"></i></div><div><b>{{ $t }}</b><span>{{ $v }}</span><small>{{ $s }}</small></div>@if($href)</a>@else</div>@endif
        @endif
      @endforeach
      <div class="ibox reveal" data-delay="4" style="display:block">
        <b style="display:block;margin-bottom:10px"><i class="fa-regular fa-clock" style="color:var(--p);margin-right:8px"></i>Support hours</b>
        <div style="font-size:14px;font-weight:700;color:var(--ink)">{{ zstore('hours','Mon – Sat, 9 AM – 6 PM PKT') }}</div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px">Messages sent outside these hours are answered the next working day.</div>
      </div>
      <div class="wa reveal" data-delay="4">
        <b>Need quick help?</b>
        <p>Chat with us on WhatsApp for the fastest response.</p>
        <a href="https://wa.me/{{ ztel(zstore('whatsapp') ?: zstore('phone')) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" style="font-size:18px"></i> Chat on WhatsApp</a>
        <span class="e">💬</span>
      </div>
    </div>
  </div>

  <div class="map reveal"><div class="grid-bg"></div><span class="ring"></span><div class="pin"><i class="fa-solid fa-location-dot"></i><span>{{ zstore('name','Zelari') }} · {{ zstore('address') }}</span></div></div>

  <div class="faq">
    <div class="sec-head reveal"><div><h2 class="sec-title tt-none">Frequently asked</h2><div class="sec-deco"><i></i><b></b></div></div></div>
    @foreach(zfaqs() as $i => [$q,$a])
      <details class="reveal" data-delay="{{ ($i%4)+1 }}"><summary>{{ $q }} <i class="fa-solid fa-plus"></i></summary><p>{{ $a }}</p></details>
    @endforeach
  </div>
</section>
@endsection
