@extends('layouts.store')
@section('title', 'Checkout')

@push('styles')
<style>
.co{padding:34px 0 60px;background:var(--surface)}
.co-grid{display:grid;grid-template-columns:1fr 420px;gap:28px;align-items:start}
@media(max-width:992px){.co-grid{grid-template-columns:1fr}}
.steps{display:flex;align-items:center;gap:10px;margin-bottom:26px;flex-wrap:wrap}
.step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--faint)}
.step i{width:30px;height:30px;border-radius:50%;background:#fff;display:grid;place-items:center;font-size:12px;font-style:normal}
.step.on{color:var(--p)}.step.on i{background:var(--grad);color:#fff;box-shadow:0 8px 18px -8px rgba(106,61,245,.9)}
.step.done{color:var(--ok)}.step.done i{background:var(--ok);color:#fff}
.step-line{flex:1;max-width:70px;height:2px;background:var(--line-2)}
.card-z{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:26px;margin-bottom:18px}
.card-z h3{font-size:17px;font-weight:800;margin:0 0 20px;display:flex;align-items:center;gap:10px}
.card-z h3 i{width:34px;height:34px;border-radius:10px;background:var(--grad);color:#fff;display:grid;place-items:center;font-size:14px}
.alert-z{background:#fdecef;border:1px solid #ffc7d4;color:#b4124b;border-radius:14px;padding:14px 18px;font-size:14px;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start}
.payopt{display:flex;align-items:flex-start;gap:14px;border:1.5px solid var(--line-2);border-radius:14px;padding:16px;cursor:pointer;transition:.2s;margin-bottom:10px;position:relative;background:#fff}
.payopt:has(input:checked){border-color:var(--p);background:var(--p-soft);box-shadow:var(--glow)}
.payopt input{accent-color:var(--p);margin-top:3px;width:18px;height:18px}
.payopt .ic{width:44px;height:44px;border-radius:12px;background:var(--surface);display:grid;place-items:center;color:var(--p);font-size:18px;flex-shrink:0}
.payopt:has(input:checked) .ic{background:var(--grad);color:#fff}
.payopt b{font-size:14.5px;display:block}
.payopt small{color:var(--muted);font-size:13px}
.payopt .tag{position:absolute;right:14px;top:14px;font-size:11px;font-weight:800;background:#e6f9f2;color:#0d8f6a;padding:3px 9px;border-radius:999px}
/* summary */
.sum{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;position:sticky;top:calc(var(--head-h) + 70px)}
@media(max-width:992px){.sum{position:static}}
.sum h3{font-size:17px;font-weight:800;margin:0 0 16px}
.sitems{max-height:300px;overflow:auto;display:flex;flex-direction:column;gap:12px;padding-right:4px}
.sitem{display:flex;gap:12px;align-items:center}
.sitem .im{width:56px;height:56px;border-radius:12px;background:var(--surface);display:grid;place-items:center;overflow:hidden;flex-shrink:0;position:relative}
.sitem .im img{width:100%;height:100%;object-fit:cover;display:block}
.sitem .im i{color:#c8cce3}
.sitem .im .q{position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;border-radius:10px;background:var(--ink);color:#fff;font-size:11px;font-weight:800;display:grid;place-items:center;padding:0 5px}
.sitem .nm{font-size:13.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sitem .pr{font-size:14px;font-weight:800;flex-shrink:0}
.sum hr{border:0;border-top:1px dashed var(--line-2);margin:14px 0}
.sum .row2{display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:var(--muted)}
.sum .row2 b{color:var(--ink)}
.sum .tot{display:flex;justify-content:space-between;align-items:center;padding-top:6px}
.sum .tot span{font-size:16px;font-weight:800}
.sum .tot b{font-size:28px;font-weight:800;letter-spacing:-1px;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.place{width:100%;height:54px;font-size:16px;border-radius:14px;margin-top:16px}
.secure{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;font-size:12px;color:var(--faint)}
/* Stripe card form */
.paybox{display:none;margin:-2px 0 14px;padding:18px;border:1.5px solid var(--line-2);border-top:0;border-radius:0 0 14px 14px;background:#fff}
.payopt.has-form:has(input:checked){border-radius:14px 14px 0 0;margin-bottom:0}
.payopt.has-form:has(input:checked)+.paybox{display:block;border-color:var(--p);animation:popIn .25s ease}
.paybox .pe-wrap{min-height:120px;position:relative}
.paybox .pe-load{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:10px;color:var(--muted);font-size:13.5px}
.paybox .pe-err{display:none;margin-top:12px;background:#fdecef;border:1px solid #ffc7d4;color:#b4124b;border-radius:12px;padding:10px 14px;font-size:13px}
.paybox .pe-note{display:flex;align-items:center;gap:8px;margin-top:12px;font-size:12px;color:var(--faint)}
.paybox .pe-note b{color:var(--ink-2)}
.testmode{display:inline-flex;align-items:center;gap:6px;background:#fff5e0;color:#b36b00;font-size:11px;font-weight:800;padding:3px 9px;border-radius:999px;margin-left:8px;vertical-align:middle}
.payopt.is-paypal:has(input:checked) .ic{background:#003087}
.payopt .brands{display:flex;gap:4px;margin-top:6px}
#paypalButtons{min-height:48px}
.payopt .brands span{font-size:10px;font-weight:800;border:1px solid var(--line);border-radius:5px;padding:1px 6px;color:var(--ink-2);background:#fff}
</style>
@endpush

@section('content')
<section class="co">
  <div class="wrap">
    <div class="steps">
      <div class="step done"><i><i class="fa-solid fa-check"></i></i> Cart</div><span class="step-line"></span>
      <div class="step on"><i>2</i> Checkout</div><span class="step-line"></span>
      <div class="step"><i>3</i> Done</div>
    </div>

    @if(session('checkout_error'))<div class="alert-z"><i class="fa-solid fa-circle-exclamation"></i><div>{{ session('checkout_error') }}</div></div>@endif
    @if($errors->any())<div class="alert-z"><i class="fa-solid fa-circle-exclamation"></i><div><b>Please fix the following:</b><ul style="margin:6px 0 0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>@endif

    <form action="{{ route('checkout.store') }}" method="POST" id="coForm">
      @csrf
      <div class="co-grid">
        <div>
          <div class="card-z reveal">
            <h3><i class="fa-solid fa-user"></i> Contact & Delivery</h3>
            <div class="row g-3">
              <div class="col-sm-6 fld"><label>Full name <b>*</b></label><input class="inp {{ $errors->has('name')?'err':'' }}" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required placeholder="Your full name">@error('name')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>Email <b>*</b></label><input class="inp {{ $errors->has('email')?'err':'' }}" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required placeholder="you@example.com">@error('email')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>Phone <b>*</b></label><input class="inp {{ $errors->has('phone')?'err':'' }}" type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+92 3xx xxxxxxx">@error('phone')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-sm-6 fld"><label>City <b>*</b></label><input class="inp {{ $errors->has('city')?'err':'' }}" name="city" value="{{ old('city') }}" required placeholder="Lahore">@error('city')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-12 fld"><label>Delivery address <b>*</b></label><textarea class="inp {{ $errors->has('address')?'err':'' }}" name="address" rows="3" required placeholder="House, street, area…">{{ old('address') }}</textarea>@error('address')<div class="ferr">{{ $message }}</div>@enderror</div>
              <div class="col-12 fld"><label>Order notes <span style="color:var(--faint);font-weight:600">(optional)</span></label><textarea class="inp" name="notes" rows="2" placeholder="Delivery instructions, landmark…">{{ old('notes') }}</textarea></div>
            </div>
          </div>

          <div class="card-z reveal" data-delay="1">
            <h3><i class="fa-solid fa-wallet"></i> Payment Method</h3>
            @php $selected = old('payment_method', array_key_first($methods)); @endphp
            @forelse($methods as $code => $m)
              <label class="payopt {{ in_array($code, ['card','paypal']) ? 'has-form' : '' }} {{ $code === 'paypal' ? 'is-paypal' : '' }}">
                <input type="radio" name="payment_method" value="{{ $code }}" {{ $selected === $code ? 'checked' : '' }} required>
                <span class="ic"><i class="{{ str_starts_with($m['icon'], 'fa-brands') ? $m['icon'] : 'fa-solid '.$m['icon'] }}"></i></span>
                <span><b>{{ $m['label'] }}@if($code === 'card' && str_starts_with($stripeKey ?? '', 'pk_test_'))<span class="testmode"><i class="fa-solid fa-flask"></i> Test mode</span>@endif</b><small>{{ $m['desc'] }}</small>
                  @if($code === 'card')<span class="brands"><span>VISA</span><span>Mastercard</span><span>AMEX</span><span>Apple Pay</span><span>G Pay</span></span>@endif
                </span>
                @if(!empty($m['tag']))<span class="tag">{{ $m['tag'] }}</span>@endif
              </label>
              @if($code === 'paypal')
                <div class="paybox" id="paypalBox">
                  <div class="pe-wrap" style="min-height:60px">
                    <div class="pe-load" id="ppLoad"><i class="fa-solid fa-spinner fa-spin"></i> Loading PayPal…</div>
                    <div id="paypalButtons"></div>
                  </div>
                  <div class="pe-err" id="ppErr"></div>
                  <div class="pe-note"><i class="fa-solid fa-lock" style="color:var(--ok)"></i> You'll approve <b id="ppAmount">{{ zprice(array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$cart))) }}</b> in the PayPal window{{ (($paypalCurrency ?? null) && strtoupper(zcur()['code']) !== $paypalCurrency) ? ' — charged in '.$paypalCurrency.' because PayPal does not support '.zcur()['code'] : '' }}. Fill in your details above first.</div>
                </div>
              @endif
              @if($code === 'card')
                <div class="paybox" id="stripeBox">
                  <div class="pe-wrap">
                    <div class="pe-load" id="peLoad"><i class="fa-solid fa-spinner fa-spin"></i> Loading secure card form…</div>
                    <div id="paymentElement"></div>
                  </div>
                  <div class="pe-err" id="peErr"></div>
                  <div class="pe-note"><i class="fa-solid fa-lock" style="color:var(--ok)"></i> Encrypted by <b>Stripe</b> — your card number never reaches our servers. You'll be charged <b id="peAmount">{{ zprice(array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$cart))) }}</b>.</div>
                </div>
              @endif
            @empty
              <div class="alert-z"><i class="fa-solid fa-circle-exclamation"></i><div>No payment methods are available right now. Please contact us to complete your order.</div></div>
            @endforelse
          </div>
        </div>

        @php $subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$cart)); $shipping = zshipping($subtotal); @endphp
        <aside class="sum reveal" data-delay="2">
          <h3>Order Summary</h3>
          <div class="sitems">
            @foreach($cart as $it)
            @php $img = zimg($it['image'] ?? null) ?? zcat_image(null,(int)($it['product_id'] ?? 0),200); @endphp
            <div class="sitem">
              <div class="im"><img src="{{ $img }}" alt=""><span class="q">{{ $it['quantity'] }}</span></div>
              <div style="flex:1;min-width:0"><div class="nm">{{ $it['name'] }}</div><small style="color:var(--muted)">{{ zprice($it['price']) }} × {{ $it['quantity'] }}</small></div>
              <div class="pr">{{ zprice($it['price']*$it['quantity']) }}</div>
            </div>
            @endforeach
          </div>
          <hr>
          <div class="row2"><span>Subtotal</span><b>{{ zprice($subtotal) }}</b></div>
          <div class="row2"><span>Shipping</span><b style="color:{{ $shipping>0 ? 'var(--ink)' : 'var(--ok)' }}">{{ $shipping>0 ? zprice($shipping) : 'Free' }}</b></div>
          <hr>
          <div class="tot"><span>Total</span><b>{{ zprice($subtotal+$shipping) }}</b></div>
          <button type="submit" class="btn-g place" id="placeBtn" {{ empty($methods) ? 'disabled' : '' }}><i class="fa-solid fa-lock"></i> <span id="placeTxt">Place Order</span></button>
          <div id="ppPlaceNote" style="display:none;margin-top:16px;padding:12px 14px;border-radius:12px;background:#fff7e6;border:1px solid #ffe3a3;color:#7a5200;font-size:13px;font-weight:600;text-align:center"><i class="fa-brands fa-paypal"></i> Complete your order with the PayPal button in the Payment Method section.</div>
          <div class="secure"><i class="fa-solid fa-shield-halved"></i> Prices & stock are re-verified on our server</div>
          <div style="display:flex;justify-content:center;margin-top:12px">@include('shop.partials.payment-icons', ['size' => 'sm'])</div>
          <a href="{{ route('cart.index') }}" style="display:block;text-align:center;margin-top:14px;font-size:13px;font-weight:700;color:var(--muted)"><i class="fa-solid fa-arrow-left"></i> Back to cart</a>
        </aside>
      </div>
    </form>
  </div>
</section>
@endsection

@push('scripts')
@if($stripeKey)<script src="https://js.stripe.com/v3/"></script>@endif
@if($paypalClient ?? null)<script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypalClient) }}&currency={{ $paypalCurrency }}&intent=capture&components=buttons&disable-funding=card,credit,paylater" data-page-type="checkout"></script>@endif
<script>
(function(){
  const form=document.getElementById('coForm'), btn=document.getElementById('placeBtn'), txt=document.getElementById('placeTxt');
  const token=document.querySelector('meta[name=csrf-token]').content;
  const STRIPE_KEY=@json($stripeKey);
  const INTENT_URL=@json(route('checkout.stripe.intent'));
  let stripe=null, elements=null, mounted=false, mounting=false, busy=false;

  const method=()=> (form.querySelector('input[name=payment_method]:checked')||{}).value;
  const PAYPAL_ON=@json((bool) ($paypalClient ?? false));
  const CAPTURE_URL=@json(route('checkout.paypal.capture'));
  const setBusy=(on,label)=>{ busy=on; btn.disabled=on; btn.innerHTML=on?'<i class="fa-solid fa-spinner fa-spin"></i> '+label:'<i class="fa-solid fa-lock"></i> <span id="placeTxt">'+(method()==='card'?'Pay now':'Place Order')+'</span>'; };
  const showErr=m=>{ const e=document.getElementById(method()==='paypal'?'ppErr':'peErr'); if(!e) return; e.textContent=m||''; e.style.display=m?'block':'none'; };
  const relabel=()=>{ const t=document.getElementById('placeTxt'); if(t) t.textContent=method()==='card'?'Pay now':'Place Order'; btn.style.display=method()==='paypal'?'none':''; const n=document.getElementById('ppPlaceNote'); if(n) n.style.display=method()==='paypal'?'block':'none'; };

  async function mountStripe(){
    if(mounted||mounting||!STRIPE_KEY) return; mounting=true; showErr('');
    try{
      const r=await fetch(INTENT_URL,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
      const j=await r.json().catch(()=>({}));
      if(!r.ok){ throw new Error(j.message||'Could not start card payment.'); }
      stripe=Stripe(STRIPE_KEY);
      const cs=getComputedStyle(document.documentElement);
      elements=stripe.elements({clientSecret:j.clientSecret,appearance:{theme:'stripe',variables:{colorPrimary:cs.getPropertyValue('--p').trim()||@json(ztheme('brand')),borderRadius:'12px',fontFamily:'inherit',fontSizeBase:'14px'}}});
      const pe=elements.create('payment',{layout:'tabs'});
      pe.on('ready',()=>{ document.getElementById('peLoad').style.display='none'; mounted=true; });
      pe.on('loaderror',ev=>{ showErr(ev.error?.message||'Card form failed to load.'); document.getElementById('peLoad').style.display='none'; });
      pe.mount('#paymentElement');
    }catch(e){ document.getElementById('peLoad').innerHTML='<i class="fa-solid fa-triangle-exclamation" style="color:#b4124b"></i> '+e.message; }
    finally{ mounting=false; }
  }

  /* ── PayPal buttons: our order is created only when the buyer clicks the button ── */
  let ppMounted=false, ppOrderNumber=null;
  function mountPayPal(){
    if(ppMounted||!PAYPAL_ON||!window.paypal) { if(!window.paypal&&PAYPAL_ON){ const l=document.getElementById('ppLoad'); if(l) l.innerHTML='<i class="fa-solid fa-triangle-exclamation" style="color:#b4124b"></i> PayPal failed to load (ad blocker?). Please choose another method.'; } return; }
    ppMounted=true;
    paypal.Buttons({
      style:{layout:'vertical',color:'gold',shape:'rect',label:'paypal',height:48},
      onInit:(data,actions)=>{ document.getElementById('ppLoad').style.display='none'; },
      onClick:(data,actions)=>{
        // Validate the delivery form first (same rules as the normal submit)
        showErr('');
        if(!form.reportValidity()) return actions.reject();
        return actions.resolve();
      },
      createOrder:async()=>{
        const fd=new FormData(form); fd.set('payment_method','paypal');
        const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
        const j=await r.json().catch(()=>({}));
        if(!r.ok||!j.ok||!j.paypal_order_id){ const m=j.message||Object.values(j.errors||{}).flat()[0]||'Could not start PayPal checkout.'; showErr(m); throw new Error(m); }
        ppOrderNumber=j.order; return j.paypal_order_id;
      },
      onApprove:async(data)=>{
        showErr(''); const box=document.getElementById('paypalBox'); box.style.opacity='.6';
        const fd=new FormData(); fd.append('order',ppOrderNumber); fd.append('paypal_order_id',data.orderID);
        const r=await fetch(CAPTURE_URL,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
        const j=await r.json().catch(()=>({}));
        box.style.opacity='';
        if(!r.ok||!j.ok){ showErr(j.message||'PayPal payment could not be completed.'); return; }
        location.href=j.redirect;
      },
      onCancel:()=>{ if(ppOrderNumber) fetch(@json(url('/checkout/paypal/cancel'))+'/'+ppOrderNumber,{headers:{'Accept':'application/json'}}).catch(()=>{}); ppOrderNumber=null; showErr('PayPal payment was cancelled — nothing was charged.'); },
      onError:(err)=>{ console.error(err); if(!document.getElementById('ppErr').textContent) showErr('PayPal ran into a problem. Please try again or choose another method.'); }
    }).render('#paypalButtons');
  }

  form.querySelectorAll('input[name=payment_method]').forEach(r=>r.addEventListener('change',()=>{ relabel(); if(method()==='card') mountStripe(); if(method()==='paypal') mountPayPal(); }));
  if(method()==='card') mountStripe(); if(method()==='paypal') mountPayPal(); relabel();

  form.addEventListener('submit',async function(ev){
    if(method()==='paypal'){ ev.preventDefault(); showErr('Use the PayPal button above to pay.'); return; }
    if(method()!=='card'){ setBusy(true,'Placing order…'); return; } // normal COD post
    ev.preventDefault(); if(busy) return;
    if(!mounted||!stripe||!elements){ showErr('The card form is still loading — one moment.'); return; }
    showErr(''); setBusy(true,'Checking details…');
    try{
      // 1) validate the card form client-side first (cheap, no order yet)
      const v=await elements.submit(); if(v.error){ throw new Error(v.error.message); }
      // 2) create the order on our server (re-verifies stock/prices, syncs the amount)
      const fd=new FormData(form);
      const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
      const j=await r.json().catch(()=>({}));
      if(!r.ok||!j.ok){ throw new Error(j.message||Object.values(j.errors||{}).flat()[0]||'Could not place the order.'); }
      if(j.redirect){ location.href=j.redirect; return; }
      // 3) hand off to Stripe — redirects to return_url when the bank needs 3-D Secure
      setBusy(true,'Confirming payment…');
      const {error}=await stripe.confirmPayment({elements,confirmParams:{return_url:j.return_url,payment_method_data:{billing_details:{name:fd.get('name'),email:fd.get('email'),phone:fd.get('phone'),address:{city:fd.get('city'),line1:(fd.get('address')||'').slice(0,200)}}}}});
      if(error){ // declined / invalid — the same order & payment attempt are reused when they try again
        showErr(error.message||'Payment failed.'); setBusy(false); return;
      }
    }catch(e){ showErr(e.message); setBusy(false); }
  });
})();
</script>
@endpush
