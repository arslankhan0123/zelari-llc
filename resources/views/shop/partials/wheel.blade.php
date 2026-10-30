@php $wcfg = \App\Support\Marketing::wheelConfig(); @endphp
@if($wcfg['enabled'])
<link rel="stylesheet" href="{{ asset('css/zl-wheel.css') }}?v={{ @filemtime(public_path('css/zl-wheel.css')) ?: 1 }}">
<div id="zwheel" class="zw-overlay" hidden aria-hidden="true">
  <div class="zw-modal" role="dialog" aria-label="Spin to win a discount">
    <button class="zw-x" id="zwClose" aria-label="Close">&times;</button>
    @include('shop.partials.wheel-core', ['wcfg' => $wcfg])
  </div>
</div>

<style>
.zw-overlay{position:fixed;inset:0;z-index:4000;background:rgba(15,16,34,.72);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:18px;opacity:0;transition:opacity .35s}
.zw-overlay.show{opacity:1}
.zw-modal{position:relative;width:min(760px,100%);background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:24px;box-shadow:0 40px 90px -30px rgba(0,0,0,.7);display:grid;grid-template-columns:1fr 1fr;gap:0;overflow:hidden;transform:scale(.9);transition:transform .35s cubic-bezier(.2,.8,.3,1.2)}
.zw-overlay.show .zw-modal{transform:scale(1)}
.zw-modal .zw-root{display:contents}
.zw-modal .zw-left{display:grid;place-items:center;padding:28px 10px;background:radial-gradient(circle at 50% 40%,rgba(251,191,36,.18),transparent 60%)}
.zw-modal .zw-panel{padding:34px 32px}
.zw-x{position:absolute;top:12px;right:14px;z-index:3;background:rgba(255,255,255,.12);border:0;color:#fff;width:34px;height:34px;border-radius:50%;font-size:22px;line-height:1;cursor:pointer;transition:.2s}
.zw-x:hover{background:rgba(255,255,255,.25);transform:rotate(90deg)}
@media(max-width:640px){.zw-modal{grid-template-columns:1fr;max-width:380px}.zw-modal .zw-left{padding:24px 10px 6px}.zw-modal .zw-wheelwrap{width:260px;height:260px}.zw-modal .zw-panel{padding:22px 24px 28px;text-align:center}.zw-modal .zw-badge{align-self:center}.zw-modal .zw-code{flex-direction:column}}
</style>

<script src="{{ asset('js/zl-wheel.js') }}?v={{ @filemtime(public_path('js/zl-wheel.js')) ?: 1 }}"></script>
<script>
(function(){
  var SEEN='zwheel_v2', overlay=document.getElementById('zwheel');
  if(!overlay) return;
  function open(){ overlay.hidden=false; overlay.setAttribute('aria-hidden','false'); requestAnimationFrame(function(){ overlay.classList.add('show'); }); }
  function close(){ overlay.classList.remove('show'); overlay.setAttribute('aria-hidden','true'); setTimeout(function(){ overlay.hidden=true; },350); try{ localStorage.setItem(SEEN,'1'); }catch(e){} }
  document.getElementById('zwClose').addEventListener('click', close);
  overlay.addEventListener('click', function(e){ if(e.target===overlay) close(); });
  var seen=false; try{ seen = localStorage.getItem(SEEN)==='1'; }catch(e){}
  if(!seen) setTimeout(open, 2600);
  window.zOpenWheel = open;
})();
</script>
@endif
