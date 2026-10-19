@extends('layouts.store')
@section('title', 'Order Confirmed')

@push('styles')
<style>
.ok-page{padding:50px 0 70px;background:radial-gradient(800px 400px at 50% 0%,#efeaff 0%,transparent 60%),var(--surface);min-height:70vh}
.ok-inner{max-width:860px;margin:0 auto}
.ok-hero{text-align:center;margin-bottom:34px;position:relative}
.ok-ring{width:110px;height:110px;margin:0 auto 20px;border-radius:50%;background:var(--grad);display:grid;place-items:center;color:#fff;font-size:46px;box-shadow:0 30px 60px -20px rgba(106,61,245,.8);animation:popIn .6s cubic-bezier(.34,1.56,.64,1);position:relative;transform-style:preserve-3d}
.ok-ring::before{content:"";position:absolute;inset:-10px;border-radius:50%;border:2px dashed rgba(106,61,245,.4);animation:spinSlow 16s linear infinite}
.ok-hero h1{font-size:clamp(26px,4vw,40px);font-weight:800;letter-spacing:-1px;margin:0 0 8px}
.ok-hero p{color:var(--muted);font-size:16px;margin:0}
.confetti{position:absolute;width:10px;height:10px;border-radius:2px;top:40px;opacity:0;animation:conf 1.6s ease-out forwards}
@keyframes conf{0%{opacity:1;transform:translate(0,0) rotate(0)}100%{opacity:0;transform:translate(var(--dx),var(--dy)) rotate(540deg)}}
.steps{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:30px;flex-wrap:wrap}
.step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--ok)}
.step i{width:30px;height:30px;border-radius:50%;background:var(--ok);color:#fff;display:grid;place-items:center;font-size:12px}
.step-line{width:60px;height:2px;background:var(--ok)}
.card-z{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:26px;margin-bottom:16px}
.card-z h3{font-size:16px;font-weight:800;margin:0 0 18px;display:flex;align-items:center;gap:10px}
.card-z h3 i{width:34px;height:34px;border-radius:10px;background:var(--p-soft);color:var(--p);display:grid;place-items:center;font-size:14px}
.kv{display:grid;grid-template-columns:1fr 1fr;gap:14px 24px}
@media(max-width:560px){.kv{grid-template-columns:1fr}}
.kv div small{display:block;font-size:12px;color:var(--faint);font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px}
.kv div b{font-size:15px;font-weight:700}
.onum{font-family:ui-monospace,Menlo,monospace;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent;font-size:18px!important}
.pill{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:800}
.pill.y{background:#fff5e0;color:#b36b00}.pill.b{background:var(--p-soft);color:var(--p)}.pill.g{background:#e6f9f2;color:#0d8f6a}
.oitem{display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px dashed var(--line)}
.oitem:last-child{border-bottom:0}
.oitem .im{width:60px;height:60px;border-radius:12px;background:var(--surface);display:grid;place-items:center;overflow:hidden;flex-shrink:0}
.oitem .im img{width:100%;height:100%;object-fit:cover;display:block}
.oitem .im i{color:#c8cce3}
.oitem b{font-size:14px;display:block}
.oitem small{color:var(--muted)}
.oitem .pr{margin-left:auto;font-weight:800;font-size:15px}
.tot-row{display:flex;justify-content:space-between;font-size:14px;color:var(--muted);padding:5px 0}
.tot-row b{color:var(--ink)}
.grand{display:flex;justify-content:space-between;align-items:center;border-top:1.5px solid var(--line);margin-top:10px;padding-top:12px}
.grand span{font-size:16px;font-weight:800}
.grand b{font-size:28px;font-weight:800;letter-spacing:-1px;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}
.next{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;position:relative}
@media(max-width:720px){.next{grid-template-columns:1fr}}
.next .nx{background:var(--surface);border-radius:16px;padding:18px 18px 18px 16px;display:flex;gap:14px;align-items:flex-start;position:relative;border:1px solid transparent;transition:.25s}
.next .nx:hover{border-color:var(--line);background:#fff;box-shadow:var(--shadow);transform:translateY(-2px)}
.next .nx i{width:44px;height:44px;border-radius:13px;background:var(--brand-grad);display:grid;place-items:center;color:#fff;font-size:17px;flex-shrink:0;box-shadow:0 10px 20px -10px rgba(var(--shadow-rgb),.7)}
.next .nx .tx{min-width:0}
.next .nx b{font-size:14.5px;display:block;line-height:1.25;margin-bottom:4px}
.next .nx small{display:block;color:var(--muted);font-size:13px;line-height:1.5}
.next .nx .n{position:absolute;top:10px;right:12px;font-size:11px;font-weight:800;color:var(--faint)}
.next .nx.done i{background:var(--ok);box-shadow:0 10px 20px -10px rgba(20,184,138,.7)}
.bank{background:var(--p-soft);border:1px solid #d9ccff;border-radius:16px;padding:18px 20px;margin-bottom:16px;display:flex;gap:12px;font-size:14px;color:#3b2a8a}
.acts{display:flex;gap:12px;flex-wrap:wrap}
.acts a{flex:1;min-width:180px;height:50px;border-radius:14px;font-size:15px}
</style>
@endpush

@section('content')
<section class="ok-page">
  <div class="wrap ok-inner">
    <div class="ok-hero">
      @for($i=0;$i<18;$i++)<span class="confetti" style="left:{{ 50 + (($i*37)%40 - 20) }}%;background:{{ [ztheme('brand'),ztheme('accent'),'#ff4d7d','#ffd36b','#14b88a'][$i%5] }};--dx:{{ (($i*53)%240)-120 }}px;--dy:{{ (($i*29)%160)-200 }}px;animation-delay:{{ ($i%6)*.08 }}s"></span>@endfor
      <div class="ok-ring"><i class="fa-solid fa-check"></i></div>
      <h1>Order confirmed 🎉</h1>
      <p>Thanks, <b>{{ $order->shipping_name ?? $order->customer?->name ?? 'friend' }}</b>. We've received your order and emailed a confirmation with your PDF invoice to <b>{{ $order->customer_email ?? $order->customer?->email }}</b>.</p>
    </div>

    <div class="steps">
      <div class="step"><i><i class="fa-solid fa-check"></i></i> Cart</div><span class="step-line"></span>
      <div class="step"><i><i class="fa-solid fa-check"></i></i> Checkout</div><span class="step-line"></span>
      <div class="step"><i><i class="fa-solid fa-check"></i></i> Done</div>
    </div>

    <div class="card-z reveal">
      <h3><i class="fa-solid fa-receipt"></i> Order Details</h3>
      <div class="kv">
        <div><small>Order number</small><b class="onum">#{{ $order->order_number }}</b></div>
        <div><small>Placed on</small><b>{{ $order->created_at->format('d M Y, g:i A') }}</b></div>
        <div><small>Payment</small><b>@if($order->payment_method==='cash_on_delivery')<span class="pill g"><i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery</span>@elseif($order->payment_method==='card')<span class="pill {{ $order->payment_status==='paid' ? 'g' : 'b' }}"><i class="fa-solid fa-credit-card"></i> Card{{ $order->payment_status==='paid' ? ' · Paid' : '' }}</span>@elseif($order->payment_method==='paypal')<span class="pill {{ $order->payment_status==='paid' ? 'g' : 'b' }}"><i class="fa-brands fa-paypal"></i> PayPal{{ $order->payment_status==='paid' ? ' · Paid' : '' }}</span>@elseif($order->payment_method==='bank_transfer')<span class="pill b"><i class="fa-solid fa-building-columns"></i> Bank Transfer</span>@else<span class="pill b">{{ ucwords(str_replace('_',' ',$order->payment_method)) }}</span>@endif</b></div>
        <div><small>Status</small><b><span class="pill y"><i class="fa-regular fa-clock"></i> {{ ucfirst($order->status) }}</span> <span class="pill {{ $order->payment_status==='paid'?'g':'y' }}">{{ ucfirst($order->payment_status) }}</span></b></div>
      </div>
    </div>

    @if(in_array($order->payment_method, ['card','paypal']) && $order->payment_status==='paid')
    <div class="bank reveal" style="background:#e6f9f2;border-color:#bdeedb;color:#0d6a4f"><i class="fa-solid fa-circle-check" style="font-size:18px;margin-top:2px"></i><div><b>Payment received — {{ zorder_price($order, $order->total) }}</b><br>{{ $order->payment_method==='paypal' ? 'Your PayPal payment went through' : 'Your card was charged successfully' }}{{ $order->paid_at ? " on ".$order->paid_at->format("d M Y, g:i A") : "" }}. A receipt from {{ $order->payment_method==='paypal' ? 'PayPal' : 'Stripe' }} is on its way to {{ $order->customer_email }}.</div></div>
    @elseif(in_array($order->payment_method, ['card','paypal']))
    <div class="bank reveal"><i class="fa-solid fa-hourglass-half" style="font-size:18px;margin-top:2px"></i><div><b>Payment is being processed</b><br>Your bank is still confirming the payment. We'll email you as soon as it clears — no need to pay again.</div></div>
    @endif
    @if($order->payment_method==='bank_transfer')
    <div class="bank reveal"><i class="fa-solid fa-circle-info" style="font-size:18px;margin-top:2px"></i><div><b>Bank transfer instructions</b><br>We'll email our account details within 1 business hour. Please transfer <b>{{ zorder_price($order, $order->total) }}</b> within 24 hours and reply with the receipt to confirm your order.</div></div>
    @endif

    <div class="card-z reveal" data-delay="1">
      <h3><i class="fa-solid fa-location-dot"></i> Delivery Information</h3>
      <div class="kv">
        <div><small>Name</small><b>{{ $order->shipping_name }}</b></div>
        <div><small>Phone</small><b>{{ $order->shipping_phone }}</b></div>
        <div style="grid-column:1/-1"><small>Address</small><b>{{ $order->shipping_address }}, {{ $order->shipping_city }}</b></div>
        @if($order->notes)<div style="grid-column:1/-1"><small>Notes</small><b style="font-weight:500;color:var(--muted)">{{ $order->notes }}</b></div>@endif
      </div>
    </div>

    <div class="card-z reveal" data-delay="2">
      <h3><i class="fa-solid fa-bag-shopping"></i> Items Ordered ({{ $order->items->sum('quantity') }})</h3>
      @foreach($order->items as $it)
      @php $img = $it->product?->display_image ?? zcat_image(null,(int)$it->product_id,200); $realI = $it->product?->has_real_image; @endphp
      <div class="oitem">
        <div class="im"><img src="{{ $img }}" alt=""></div>
        <div><b>{{ $it->product_name }}</b><small>{{ zorder_price($order, $it->unit_price) }} × {{ $it->quantity }}@if($it->product_sku) · {{ $it->product_sku }}@endif</small></div>
        <div class="pr">{{ zorder_price($order, $it->subtotal) }}</div>
      </div>
      @endforeach
      <div style="margin-top:14px">
        <div class="tot-row"><span>Subtotal</span><b>{{ zorder_price($order, $order->subtotal) }}</b></div>
        @if($order->discount>0)<div class="tot-row"><span>Discount</span><b style="color:var(--ok)">− {{ zorder_price($order, $order->discount) }}</b></div>@endif
        <div class="tot-row"><span>Shipping</span><b style="color:var(--ok)">{{ $order->shipping_fee>0 ? zorder_price($order, $order->shipping_fee) : 'Free' }}</b></div>
        <div class="grand"><span>Total</span><b>{{ zorder_price($order, $order->total) }}</b></div>
      </div>
    </div>

    <div class="card-z reveal" data-delay="3">
      <h3><i class="fa-solid fa-route"></i> What happens next?</h3>
      <div class="next">
        <div class="nx done"><i class="fa-solid fa-envelope-open-text"></i><div class="tx"><b>Confirmation email</b><small>Sent to {{ $order->customer_email }} with your invoice attached.</small></div><span class="n">1</span></div>
        <div class="nx"><i class="fa-solid fa-box-open"></i><div class="tx"><b>Packed within {{ zship('pack_hours','24') }} hours</b><small>We check every item and pack it carefully.</small></div><span class="n">2</span></div>
        <div class="nx"><i class="fa-solid fa-truck-fast"></i><div class="tx"><b>Delivered in {{ zship('delivery_days','2–5') }} days</b><small>Our rider calls you before arriving{{ $order->payment_method === 'cash_on_delivery' ? ' — keep '.zorder_price($order, $order->total).' ready' : '' }}.</small></div><span class="n">3</span></div>
      </div>
    </div>

    <div class="acts">
      <a href="{{ route('shop.index') }}" class="btn-g"><i class="fa-solid fa-bag-shopping"></i> Continue Shopping</a>
      <a href="{{ route('contact') }}" class="btn-o"><i class="fa-solid fa-headset"></i> Need help?</a>
      <a href="{{ route('order.invoice', $order->order_number) }}" class="btn-o" target="_blank" rel="noopener"><i class="fa-solid fa-file-pdf" style="color:var(--sale)"></i> Download PDF</a>
    </div>
  </div>
</section>
@endsection
