@php $wcfg = $wcfg ?? \App\Support\Marketing::wheelConfig(); @endphp
<div class="zw-root" data-spin="{{ route('wheel.spin') }}" data-csrf="{{ csrf_token() }}" data-authed="{{ auth()->check() ? '1' : '0' }}" data-segments='@json($wcfg['segments'])'>
  <div class="zw-left">
    <div class="zw-wheelwrap">
      <canvas class="zw-canvas" width="340" height="340"></canvas>
      <div class="zw-pointer"></div>
      <div class="zw-hub"><i class="fa-solid fa-gift"></i></div>
    </div>
  </div>
  <div class="zw-panel">
    <div class="zw-stage zw-s1">
      <span class="zw-badge"><i class="fa-solid fa-bolt"></i> Exclusive offer</span>
      <h3 class="zw-title">{{ $wcfg['title'] }}</h3>
      <p class="zw-sub">{{ $wcfg['subtitle'] }}</p>
      @auth
        <button type="button" class="zw-spin"><i class="fa-solid fa-dharmachakra"></i> SPIN THE WHEEL</button>
        <div class="zw-err"></div>
        <small class="zw-fine">One spin per account. Your coupon is saved to your account and emailed to you.</small>
      @else
        <a href="{{ route('login') }}" class="zw-spin zw-login"><i class="fa-solid fa-right-to-bracket"></i> LOG IN TO SPIN</a>
        <small class="zw-fine">Log in or create a free account to spin the wheel and claim your coupon.</small>
      @endauth
    </div>
    <div class="zw-stage zw-s2" hidden>
      <div class="zw-result-ic"><i class="fa-solid fa-gift"></i></div>
      <h3 class="zw-restitle">You won!</h3>
      <p class="zw-restext"></p>
      <div class="zw-code" hidden><code class="zw-codeval"></code><button type="button" class="zw-copy"><i class="fa-regular fa-copy"></i> Copy</button></div>
      <small class="zw-fine zw-resfine"></small>
      <a href="{{ route('shop.index') }}" class="zw-shop">Start shopping</a>
    </div>
  </div>
</div>
