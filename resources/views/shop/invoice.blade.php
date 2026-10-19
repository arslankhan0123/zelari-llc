@php
    /** @var \App\Models\Order $order */
    $pdf   = $pdf ?? false;
    $store = $store ?? ['name' => 'Zelari', 'phone' => '', 'address' => '', 'email' => ''];
    $logoSrc  = $pdf ? zbrand_path('print') : zbrand('print');
    $paid   = $order->payment_status === 'paid';
    $method = ['cash_on_delivery' => 'Cash on Delivery', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card (Stripe)', 'paypal' => 'PayPal'][$order->payment_method] ?? ucwords(str_replace('_', ' ', $order->payment_method));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $order->order_number }} — {{ $store['name'] }}</title>
<style>
  @page { margin: 26px 30px 30px; }
  body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: {{ ztheme('brand_dark') }}; font-size: 11px; margin: 0; padding: 0; line-height: 1.4; }
  table { border-collapse: collapse; }
  td, th { vertical-align: top; }
  .muted { color: #6b7191; }
  .faint { color: #9aa0bd; }
  .right { text-align: right; }
  .b { font-weight: bold; }
  /* header */
  .hdr { width: 100%; margin-bottom: 14px; }
  .hdr td { padding-bottom: 12px; border-bottom: 3px solid {{ ztheme('brand') }}; }
  .logo { height: 40px; }
  .title { font-size: 26px; font-weight: bold; color: {{ ztheme('brand') }}; letter-spacing: -1px; line-height: 1; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; margin-left: 3px; }
  .b-paid { background: #eafaf3; color: #0d8f6a; } .b-unpaid { background: #fff5e0; color: #b36b00; } .b-status { background: #efeaff; color: {{ ztheme('brand') }}; }
  /* info cards */
  .info { width: 100%; margin: 14px 0 16px; }
  .info td.cell { width: 33.33%; padding: 0 4px; }
  .card { background: #f6f7fc; border-radius: 8px; padding: 10px 12px; }
  .lbl { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: #9aa0bd; font-weight: bold; margin-bottom: 4px; }
  /* items */
  .items { width: 100%; }
  .items th { background: {{ ztheme('brand_dark') }}; color: #fff; text-align: left; font-size: 9px; letter-spacing: .5px; text-transform: uppercase; padding: 8px 9px; }
  .items td { padding: 8px 9px; border-bottom: 1px solid #e7e9f3; }
  .items tr.alt td { background: #fafbfe; }
  .sku { color: #9aa0bd; font-size: 9.5px; }
  /* totals */
  .tot { width: 100%; margin-top: 10px; }
  .tot td.note { width: 58%; padding-right: 20px; font-size: 10px; }
  .sum { width: 100%; }
  .sum td { padding: 4px 0; }
  .sum tr.grand td { border-top: 2px solid {{ ztheme('brand_dark') }}; padding-top: 7px; font-size: 13px; font-weight: bold; }
  .sum tr.grand td.right { color: {{ ztheme('brand') }}; font-size: 16px; }
  .notes { margin-top: 16px; padding: 9px 12px; background: #f6f7fc; border-left: 3px solid {{ ztheme('brand') }}; color: #6b7191; }
  .thanks { margin-top: 20px; text-align: center; font-size: 11px; }
  .thanks b { color: {{ ztheme('brand') }}; }
  .foot { margin-top: 18px; padding-top: 9px; border-top: 1px solid #e7e9f3; color: #9aa0bd; font-size: 9px; text-align: center; line-height: 1.6; }
  .no-print { position: fixed; bottom: 18px; right: 18px; background: {{ ztheme('brand') }}; color: #fff; border: 0; border-radius: 10px; padding: 10px 16px; font-weight: bold; cursor: pointer; font-family: inherit; font-size: 13px; }
  @media print { .no-print { display: none !important; } }
</style>
</head>
<body>
@unless($pdf)<button class="no-print" onclick="window.print()">Save as PDF / Print</button>@endunless

<table class="hdr">
  <tr>
    <td style="width:55%">
      <img src="{{ $logoSrc }}" class="logo" alt="{{ $store['name'] }}"><br>
      <span class="muted" style="font-size:10px;line-height:1.6">
        {{ $store['address'] }}@if($store['phone'])<br>{{ $store['phone'] }}@endif @if($store['email'])<br>{{ $store['email'] }}@endif
      </span>
    </td>
    <td class="right">
      <div class="title">INVOICE</div>
      <div style="margin-top:6px;font-size:10px" class="muted">
        <b style="color:{{ ztheme('brand_dark') }};font-size:11px">#{{ $order->order_number }}</b><br>
        Date: <b style="color:{{ ztheme('brand_dark') }}">{{ $order->created_at->format('d M Y') }}</b><br>
        <span style="display:inline-block;margin-top:4px">
          <span class="badge {{ $paid ? 'b-paid' : 'b-unpaid' }}">{{ $paid ? 'PAID' : 'UNPAID' }}</span><span class="badge b-status">{{ strtoupper($order->status) }}</span>
        </span>
      </div>
    </td>
  </tr>
</table>

<table class="info">
  <tr>
    <td class="cell"><div class="card"><div class="lbl">Billed to</div><b>{{ $order->shipping_name }}</b><br>{{ $order->customer_email }}<br>{{ $order->shipping_phone }}</div></td>
    <td class="cell"><div class="card"><div class="lbl">Ship to</div>{{ $order->shipping_address }}<br>{{ $order->shipping_city }}<br>&nbsp;</div></td>
    <td class="cell"><div class="card"><div class="lbl">Payment</div><b>{{ $method }}</b><br>{{ $paid ? 'Payment received' : ($order->payment_method === 'cash_on_delivery' ? 'Pay on delivery' : 'Awaiting payment') }}<br>Delivery: {{ zship('delivery_days','2–5') }} business days</div></td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr><th style="width:28px">#</th><th>Item</th><th class="right" style="width:85px">Unit price</th><th class="right" style="width:40px">Qty</th><th class="right" style="width:95px">Amount</th></tr>
  </thead>
  <tbody>
    @foreach($order->items as $i => $it)
    <tr class="{{ $i % 2 ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td><b>{{ $it->product_name }}</b>@if($it->product_sku)<br><span class="sku">SKU: {{ $it->product_sku }}</span>@endif</td>
      <td class="right">{{ zorder_price($order, $it->unit_price) }}</td>
      <td class="right">{{ $it->quantity }}</td>
      <td class="right b">{{ zorder_price($order, $it->subtotal) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="tot">
  <tr>
    <td class="note muted">
      <b style="color:{{ ztheme('brand_dark') }}">Thank you for shopping with {{ $store['name'] }}.</b><br>
      Items can be returned within {{ zship('return_days','7') }} days of delivery in unused condition.<br>
      Keep this invoice for your records.
    </td>
    <td>
      <table class="sum">
        <tr><td class="muted">Subtotal</td><td class="right b">{{ zorder_price($order, $order->subtotal) }}</td></tr>
        @if($order->discount > 0)<tr><td class="muted">Discount</td><td class="right b" style="color:#0d8f6a">− {{ zorder_price($order, $order->discount) }}</td></tr>@endif
        <tr><td class="muted">Shipping</td><td class="right b">{{ $order->shipping_fee > 0 ? zorder_price($order, $order->shipping_fee) : 'Free' }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">{{ zorder_price($order, $order->total) }}</td></tr>
      </table>
    </td>
  </tr>
</table>

@if($order->notes)
  <div class="notes"><b style="color:{{ ztheme('brand_dark') }}">Order notes:</b> {{ $order->notes }}</div>
@endif

<div class="thanks">Questions about this order? Contact us and quote <b>#{{ $order->order_number }}</b>.</div>
<div class="foot">{{ $store['name'] }} · {{ $store['address'] }}@if($store['phone']) · {{ $store['phone'] }}@endif<br>This is a computer-generated invoice and does not require a signature.</div>

@unless($pdf)<script>if (new URLSearchParams(location.search).get('print')==='1') setTimeout(()=>window.print(), 400);</script>@endunless
</body>
</html>
