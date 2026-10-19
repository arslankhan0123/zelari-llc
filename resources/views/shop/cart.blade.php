@extends('layouts.store')
@section('title', 'Shopping Cart')

@push('styles')
<style>
.cartp{padding:34px 0 10px}
.cart-grid{display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start}
@media(max-width:992px){.cart-grid{grid-template-columns:1fr}}
.steps{display:flex;align-items:center;gap:10px;margin-bottom:26px;flex-wrap:wrap}
.step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--faint)}
.step i{width:30px;height:30px;border-radius:50%;background:var(--surface);display:grid;place-items:center;font-size:12px;font-style:normal}
.step.on{color:var(--p)}.step.on i{background:var(--grad);color:#fff;box-shadow:0 8px 18px -8px rgba(106,61,245,.9)}
.step.done{color:var(--ok)}.step.done i{background:var(--ok);color:#fff}
.step-line{flex:1;max-width:70px;height:2px;background:var(--line)}
.ctable{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden}
.ctable .head{display:grid;grid-template-columns:1fr 140px 150px 120px 44px;gap:12px;padding:14px 22px;background:var(--surface);font-size:12px;font-weight:800;letter-spacing:.04em;color:var(--muted);text-transform:uppercase}
.citem{display:grid;grid-template-columns:1fr 140px 150px 120px 44px;gap:12px;align-items:center;padding:18px 22px;border-top:1px solid var(--line);transition:background .2s}
.citem:hover{background:#fbfbfe}
@media(max-width:768px){.ctable .head{display:none}.citem{grid-template-columns:1fr;gap:10px;padding:16px}}
.cprod{display:flex;align-items:center;gap:14px;min-width:0}
.cimg{width:84px;height:84px;border-radius:14px;background:var(--surface);border:1px solid var(--line);display:grid;place-items:center;overflow:hidden;flex-shrink:0;transform-style:preserve-3d;transition:transform .4s}
.citem:hover .cimg{transform:perspective(500px) rotateY(-12deg) translateZ(10px)}
.cimg img{width:100%;height:100%;object-fit:cover;display:block}
.cimg i{color:#c8cce3;font-size:26px}
.cname{font-weight:700;font-size:15px;color:var(--ink);display:block;line-height:1.3}
.cname:hover{color:var(--p)}
.cunit{font-size:12.5px;color:var(--muted);margin-top:3px}
.mob-lbl{display:none;font-size:11px;color:var(--faint);font-weight:700;text-transform:uppercase;letter-spacing:.04em}
@media(max-width:768px){.mob-lbl{display:block}}
.cprice{font-weight:700;font-size:15px}
.qty{display:inline-flex;align-items:center;border:1.5px solid var(--line-2);border-radius:10px;overflow:hidden;background:#fff}
.qty button{width:36px;height:40px;border:0;background:var(--surface);font-size:16px;cursor:pointer;color:var(--ink)}
.qty button:hover{background:var(--p-soft);color:var(--p)}
.qty input{width:46px;height:40px;border:0;text-align:center;font-weight:800;outline:0;font-size:14px;-moz-appearance:textfield}
.qty input::-webkit-outer-spin-button,.qty input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.qty.loading{opacity:.55;pointer-events:none}
@keyframes bumpNum{0%{transform:scale(1)}40%{transform:scale(1.12);color:var(--p)}100%{transform:scale(1)}}
.bump-num{display:inline-block;animation:bumpNum .45s ease}
.csub{font-weight:800;font-size:16px;color:var(--ink)}
.crem{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;color:var(--faint);transition:.2s}
.crem:hover{background:#fdecef;color:var(--sale);transform:rotate(12deg)}
.cfoot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 22px;border-top:1px solid var(--line);flex-wrap:wrap}
.coupon{display:flex;border:1.5px solid var(--line-2);border-radius:12px;overflow:hidden;max-width:340px}
.coupon input{flex:1;border:0;padding:0 14px;height:44px;outline:0;font-size:14px;min-width:0}
.coupon button{border:0;background:var(--ink);color:#fff;font-weight:700;padding:0 18px;cursor:pointer}
/* summary */
.sum{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;position:sticky;top:calc(var(--head-h) + 70px)}
@media(max-width:992px){.sum{position:static}}
.sum h3{font-size:18px;font-weight:800;margin:0 0 18px}
.sum .row2{display:flex;justify-content:space-between;font-size:14px;padding:8px 0;color:var(--muted)}
.sum .row2 b{color:var(--ink);font-weight:700}
.sum hr{border:0;border-top:1px dashed var(--line-2);margin:10px 0}
.sum .tot{display:flex;justify-content:space-between;align-items:center;padding-top:6px}
.sum .tot span{font-size:16px;font-weight:800}
.sum .tot b{font-size:28px;font-weight:800;letter-spacing:-1px;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.ship-bar{margin:14px 0 4px;background:var(--surface);border-radius:12px;padding:12px 14px;font-size:13px;color:var(--ink-2)}
.ship-bar .bar{height:8px;border-radius:8px;background:#e8eaf4;overflow:hidden;margin-top:8px}
.ship-bar .bar i{display:block;height:100%;background:var(--grad);border-radius:8px;transition:width 1s cubic-bezier(.2,.7,.2,1)}
.sum .btn-g{width:100%;height:52px;font-size:15px;margin-top:16px;border-radius:14px}
.secure{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;font-size:12px;color:var(--faint)}
.empty{text-align:center;padding:70px 20px;background:var(--surface);border-radius:var(--r-lg)}
.empty .e{font-size:84px;display:inline-block;animation:floatY 5s ease-in-out infinite;filter:drop-shadow(0 20px 20px rgba(39,30,92,.2))}
.empty h2{font-weight:800;margin:14px 0 6px}
.empty p{color:var(--muted);margin-bottom:22px}
</style>
@endpush

@section('content')
@include('shop.partials.page-hero', ['id'=>'cartHero','crumbs'=>[['Home',route('home')],['Cart',null]],'slides'=>[
  ['title'=>'Your <span>Shopping Cart</span>','text'=>'Review your picks, adjust quantities, and check out in under a minute.','icon'=>'fa-cart-shopping'],
  ['title'=> zfree_over() > 0 ? 'Free Delivery <span>Over '.zprice(zfree_over()).'</span>' : 'Free Delivery <span>On Every Order</span>','text'=>'Add a little more and shipping is on us.','icon'=>'fa-truck-fast'],
]])

<section class="cartp wrap">
  @php $items = $cart ?? session('cart', []); $count = array_sum(array_column($items,'quantity')); $total = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$items)); $free = zfree_over(); $shipFee = zshipping($total); @endphp

  <div class="steps">
    <div class="step on"><i>1</i> Cart</div><span class="step-line"></span>
    <div class="step"><i>2</i> Checkout</div><span class="step-line"></span>
    <div class="step"><i>3</i> Done</div>
  </div>

  @if(count($items))
  <div class="cart-grid">
    <div>
      <div class="ctable reveal">
        <div class="head"><span>Product</span><span>Price</span><span>Quantity</span><span>Subtotal</span><span></span></div>
        @foreach($items as $pid => $it)
        @php $img = zimg($it['image'] ?? null) ?? zcat_image(null,(int)$pid,300); $line = $it['price']*$it['quantity']; @endphp
        <div class="citem" data-pid="{{ $pid }}" data-price="{{ $it['price'] }}">
          <div class="cprod">
            <a href="{{ route('shop.show',$it['slug']) }}" class="cimg"><img src="{{ $img }}" alt=""></a>
            <div style="min-width:0">
              <a href="{{ route('shop.show',$it['slug']) }}" class="cname">{{ $it['name'] }}</a>
              <div class="cunit">{{ zprice($it['price']) }} each @if(($it['max_quantity'] ?? 0) <= 5)· <span style="color:var(--warn)">only {{ $it['max_quantity'] }} left</span>@endif</div>
            </div>
          </div>
          <div><span class="mob-lbl">Price</span><span class="cprice">{{ zprice($it['price']) }}</span></div>
          <div><span class="mob-lbl">Quantity</span>
            <form action="{{ route('cart.update') }}" method="POST" class="qty js-qty">
              @csrf<input type="hidden" name="product_id" value="{{ $pid }}">
              <button type="button" class="dec" aria-label="Decrease">−</button>
              <input type="number" name="quantity" value="{{ $it['quantity'] }}" min="0" max="100" inputmode="numeric">
              <button type="button" class="inc" aria-label="Increase">+</button>
            </form>
          </div>
          <div><span class="mob-lbl">Subtotal</span><span class="csub js-line">{{ zprice($line) }}</span></div>
          <a href="{{ route('cart.remove',$pid) }}" class="crem" title="Remove" onclick="return confirm('Remove this item from your cart?')"><i class="fa-regular fa-trash-can"></i></a>
        </div>
        @endforeach
        <div class="cfoot">
          <form class="coupon" onsubmit="event.preventDefault();zToast('Coupon will be applied at checkout','info')"><input placeholder="Coupon code"><button>Apply</button></form>
          <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
            <a href="{{ route('shop.index') }}" class="btn-o"><i class="fa-solid fa-arrow-left"></i> Continue shopping</a>
            <a href="{{ route('cart.clear') }}" style="color:var(--sale);font-weight:700;font-size:13.5px" onclick="return confirm('Clear the entire cart?')"><i class="fa-regular fa-trash-can"></i> Clear cart</a>
          </div>
        </div>
      </div>
    </div>

    <aside class="sum reveal" data-delay="1">
      <h3>Order Summary</h3>
      <div class="row2"><span>Items (<span id="sumCount">{{ $count }}</span>)</span><b id="sumSub">{{ zprice($total) }}</b></div>
      <div class="row2"><span>Shipping</span><b id="sumShip" style="color:{{ $shipFee <= 0 ?'var(--ok)':'var(--ink)' }}">{{ $shipFee <= 0 ? 'Free' : zprice($shipFee) }}</b></div>
      <div class="row2"><span>Discount</span><b>— {{ zprice(0) }}</b></div>
      <hr>
      <div class="tot"><span>Total</span><b id="sumTotal">{{ zprice($total) }}</b></div>
      @if($free > 0)
      <div class="ship-bar" id="shipBar" data-free="{{ $free }}" data-fee="{{ zshipping(0) }}">
        <span class="ship-msg">@if($total >= $free)<i class="fa-solid fa-circle-check" style="color:var(--ok)"></i> You've unlocked <b>free delivery</b>!
        @else<i class="fa-solid fa-truck-fast" style="color:var(--p)"></i> Add <b>{{ zprice($free-$total) }}</b> more for free delivery @endif</span>
        <div class="bar"><i style="width:{{ min(100,round($total/max(1,$free)*100)) }}%"></i></div>
      </div>
      @endif
      <a href="{{ route('checkout.index') }}" class="btn-g"><i class="fa-solid fa-lock"></i> Proceed to Checkout</a>
      <div class="secure"><i class="fa-solid fa-shield-halved"></i> Secure, server-verified checkout</div>
      <div style="display:flex;justify-content:center;margin-top:12px">@include('shop.partials.payment-icons', ['size' => 'sm'])</div>
    </aside>
  </div>
  @else
  <div class="empty reveal">
    <span class="e">🛒</span>
    <h2>Your cart is empty</h2>
    <p>Looks like you haven't added anything yet. Let's fix that.</p>
    <a href="{{ route('shop.index') }}" class="btn-g" style="padding:14px 28px;font-size:15px"><i class="fa-solid fa-bag-shopping"></i> Start Shopping</a>
  </div>
  @endif
</section>
@endsection

@push('scripts')
<script>
(function(){
  const token=document.querySelector('meta[name=csrf-token]').content;
  const fmt=n=>window.zmoney(n);
  const bump=el=>{ if(!el) return; el.classList.remove('bump-num'); void el.offsetWidth; el.classList.add('bump-num'); };
  let busy=false;
  async function update(form, qty){
    if(busy) return; busy=true;
    const row=form.closest('.citem'); const input=form.querySelector('input[name=quantity]');
    form.classList.add('loading');
    try{
      const fd=new FormData(); fd.append('product_id', form.querySelector('input[name=product_id]').value); fd.append('quantity', qty);
      const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
      if(!r.ok){ let m='Could not update quantity'; try{const j=await r.json(); m=Object.values(j.errors||{}).flat()[0]||j.message||m;}catch(_){ } zToast(m,'err'); input.value=input.dataset.prev||input.value; return; }
      const j=await r.json();
      if(j.notice) zToast(j.notice,'info');
      if(j.removed){ row.style.transition='opacity .3s,transform .3s'; row.style.opacity='0'; row.style.transform='translateX(16px)'; setTimeout(()=>row.remove(),300); }
      else { input.value=j.quantity; input.dataset.prev=j.quantity; const l=row.querySelector('.js-line'); l.textContent=fmt(j.line_total); bump(l); }
      document.getElementById('sumCount').textContent=j.count;
      document.getElementById('sumSub').textContent=fmt(j.total);
      const t=document.getElementById('sumTotal'); t.textContent=fmt(j.total); bump(t);
      document.querySelectorAll('#cartCount,.js-cart-count').forEach(el=>el.textContent=j.count);
      const ht=document.getElementById('cartTotalTxt'); if(ht) ht.textContent=fmt(j.total);
      const bar=document.getElementById('shipBar'); const ship=document.getElementById('sumShip');
      if(bar){ const free=+bar.dataset.free, fee=+bar.dataset.fee||0;
        if(j.total>=free){ bar.querySelector('.ship-msg').innerHTML='<i class="fa-solid fa-circle-check" style="color:var(--ok)"></i> You\'ve unlocked <b>free delivery</b>!'; ship.textContent='Free'; ship.style.color='var(--ok)'; }
        else { bar.querySelector('.ship-msg').innerHTML='<i class="fa-solid fa-truck-fast" style="color:var(--p)"></i> Add <b>'+fmt(free-j.total)+'</b> more for free delivery'; ship.textContent=fee>0?fmt(fee):'Free'; ship.style.color=fee>0?'var(--ink)':'var(--ok)'; }
        bar.querySelector('.bar i').style.width=Math.min(100,Math.round(j.total/free*100))+'%'; }
      if(j.empty){ setTimeout(()=>location.reload(),350); }
    }catch(e){ zToast('Network error — please try again','err'); }
    finally{ form.classList.remove('loading'); busy=false; }
  }
  document.querySelectorAll('.js-qty').forEach(f=>{
    const input=f.querySelector('input[name=quantity]'); input.dataset.prev=input.value;
    f.querySelector('.dec').addEventListener('click',()=>update(f, Math.max(0,(+input.value||0)-1)));
    f.querySelector('.inc').addEventListener('click',()=>update(f, (+input.value||0)+1));
    let tmr; input.addEventListener('input',()=>{ clearTimeout(tmr); tmr=setTimeout(()=>{ const v=Math.max(0,parseInt(input.value)||0); update(f,v); },600); });
    input.addEventListener('keydown',e=>{ if(e.key==='Enter'){ e.preventDefault(); clearTimeout(tmr); update(f, Math.max(0,parseInt(input.value)||0)); } });
    f.addEventListener('submit',e=>{ e.preventDefault(); update(f, Math.max(0,parseInt(input.value)||0)); });
  });
})();
</script>
@endpush
