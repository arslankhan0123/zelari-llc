@php
    /** @var \App\Models\Order $order */
    $pdf   = $pdf ?? false;
    $store = $store ?? ['name' => 'Zelari', 'phone' => '', 'address' => '', 'email' => ''];
    $logoSrc = $pdf ? zbrand_path('print') : zbrand('print');
    $paid    = $order->payment_status === 'paid';
    $method  = ['cash_on_delivery' => 'Cash on Delivery', 'bank_transfer' => 'Bank Transfer', 'card' => 'Credit Card', 'paypal' => 'PayPal'][$order->payment_method] ?? ucwords(str_replace('_', ' ', (string) $order->payment_method));
    $amountPaid = $paid ? $order->total : 0;
    $balanceDue = $paid ? 0 : $order->total;
    $invDate = $order->created_at->format('F d, Y');
    $payDate = ($paid && $order->paid_at) ? $order->paid_at->format('F d, Y') : ($paid ? $order->created_at->format('F d, Y') : '—');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $order->order_number }} — {{ $store['name'] }}</title>
<style>
  @page { margin: 0; }
  body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#1f2433; font-size:11px; margin:0; padding:0; line-height:1.45; }
  table { border-collapse:collapse; }
  td, th { vertical-align:top; }
  .right { text-align:right; } .center { text-align:center; }
  .muted { color:#6b7183; } .faint { color:#9aa0b0; }
  .wrap { padding: 30px 40px 26px; }

  /* strip */
  .strip td { height:6px; line-height:6px; font-size:1px; }

  /* header */
  .head { width:100%; margin-bottom:26px; }
  .logo { height:46px; }
  .title { font-size:28px; font-weight:bold; letter-spacing:4px; color:#151537; }
  .num { font-size:11px; color:#6b7183; font-weight:bold; margin-top:4px; }
  .badge { display:inline-block; padding:4px 11px; border-radius:9px; font-size:10px; font-weight:bold; letter-spacing:.5px; margin-top:8px; }
  .b-paid { background:#eafaef; color:#15803d; }
  .b-unpaid { background:#fff5e0; color:#b36b00; }

  /* parties */
  .meta { width:100%; margin-bottom:24px; }
  .meta td { width:50%; padding-right:18px; }
  .lbl { font-size:9px; letter-spacing:1.2px; text-transform:uppercase; color:#9aa0b0; font-weight:bold; margin-bottom:6px; }
  .pname { font-weight:bold; font-size:13px; color:#151537; margin-bottom:2px; }
  .psub { color:#6b7183; font-size:11px; line-height:1.6; }

  /* dates */
  .dates { width:100%; margin-bottom:22px; }
  .dates td { width:33%; }
  .dval { font-size:12px; font-weight:bold; color:#1f2433; margin-top:3px; }

  /* items */
  .items { width:100%; }
  .items th { background:#151537; color:#fff; text-align:left; font-size:9px; letter-spacing:.6px; text-transform:uppercase; padding:10px 12px; font-weight:bold; }
  .items td { padding:10px 12px; border-bottom:1px solid #e7e9f0; font-size:11.5px; }
  .idesc { font-weight:bold; color:#1f2433; }
  .sku { color:#9aa0b0; font-size:10px; font-weight:normal; }

  /* totals */
  .ft { width:100%; margin-top:14px; }
  .ft .notes { width:52%; padding-right:24px; padding-top:6px; }
  .notes .nt-lbl { font-size:9px; letter-spacing:1.2px; text-transform:uppercase; color:#9aa0b0; font-weight:bold; margin-bottom:6px; }
  .notes p { margin:0 0 8px; color:#6b7183; font-size:11px; line-height:1.55; }
  .pay-line { font-size:11px; color:#1f2433; }

  .sum { width:100%; }
  .sum td { padding:7px 0; font-size:12px; color:#6b7183; }
  .sum td.v { text-align:right; color:#1f2433; font-weight:bold; }
  .sum .divr td { border-top:1px solid #e7e9f0; padding:0; height:4px; line-height:4px; font-size:1px; }
  .grand td { background:#151537; color:#fff; padding:13px 16px; }
  .grand .g-lbl { font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#cfd2e6; }
  .grand .g-val { text-align:right; font-size:19px; font-weight:bold; color:#fff; }
  .paidrow td { padding:10px 2px 0; font-size:12px; color:#15803d; font-weight:bold; }
  .paidrow td.v { text-align:right; color:#15803d; }
  .balrow td { padding:5px 2px 0; font-size:12px; color:#6b7183; font-weight:bold; }
  .balrow td.v { text-align:right; color:#1f2433; }

  .thanks { margin-top:28px; padding-top:18px; border-top:1px solid #e7e9f0; text-align:center; }
  .thanks .big { font-size:14px; font-weight:bold; color:#151537; }
  .thanks .sm { font-size:10px; color:#9aa0b0; margin-top:6px; line-height:1.6; }

  .no-print { position:fixed; bottom:18px; right:18px; background:#151537; color:#fff; border:0; border-radius:10px; padding:10px 16px; font-weight:bold; cursor:pointer; font-family:inherit; font-size:13px; }
  @media print { .no-print { display:none !important; } }
</style>
</head>
<body>
@unless($pdf)<button class="no-print" onclick="window.print()">Save as PDF / Print</button>@endunless

{{-- brand accent strip --}}
<table class="strip" width="100%" cellpadding="0" cellspacing="0">
  <tr><td style="background:#151537; width:62%">&nbsp;</td><td style="background:#c99a3a">&nbsp;</td></tr>
</table>

<div class="wrap">

  {{-- header --}}
  <table class="head">
    <tr>
      <td style="width:58%"><img src="{{ $logoSrc }}" class="logo" alt="{{ $store['name'] }}"></td>
      <td class="right">
        <div class="title">INVOICE</div>
        <div class="num">No. {{ $order->order_number }}</div>
        <span class="badge {{ $paid ? 'b-paid' : 'b-unpaid' }}">{{ $paid ? 'PAID' : 'UNPAID' }}</span>
      </td>
    </tr>
  </table>

  {{-- from / bill-to --}}
  <table class="meta">
    <tr>
      <td>
        <div class="lbl">From</div>
        <div class="pname">{{ $store['name'] }}</div>
        <div class="psub">
          {!! $store['address'] ? e($store['address']).'<br>' : '' !!}
          {{ trim(($store['email'] ?? '').((($store['email'] ?? '') && ($store['phone'] ?? '')) ? ' · ' : '').($store['phone'] ?? '')) }}
        </div>
      </td>
      <td>
        <div class="lbl">Bill To</div>
        <div class="pname">{{ $order->shipping_name }}</div>
        <div class="psub">
          {!! $order->shipping_address ? e($order->shipping_address).'<br>' : '' !!}
          {!! $order->shipping_city ? e($order->shipping_city).'<br>' : '' !!}
          {{ $order->shipping_phone }}
        </div>
      </td>
    </tr>
  </table>

  {{-- dates --}}
  <table class="dates">
    <tr>
      <td><div class="lbl">Invoice Date</div><div class="dval">{{ $invDate }}</div></td>
      <td><div class="lbl">{{ $paid ? 'Payment Date' : 'Order Date' }}</div><div class="dval">{{ $payDate }}</div></td>
      <td><div class="lbl">Payment Method</div><div class="dval">{{ $method }}</div></td>
    </tr>
  </table>

  {{-- items --}}
  <table class="items">
    <thead>
      <tr>
        <th>Description</th>
        <th class="center" style="width:50px">Qty</th>
        <th class="right" style="width:95px">Rate</th>
        <th class="right" style="width:105px">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $it)
      <tr>
        <td><span class="idesc">{{ $it->product_name }}</span>@if($it->product_sku)<br><span class="sku">SKU: {{ $it->product_sku }}</span>@endif</td>
        <td class="center">{{ $it->quantity }}</td>
        <td class="right">{{ zorder_price($order, $it->unit_price) }}</td>
        <td class="right"><b>{{ zorder_price($order, $it->subtotal) }}</b></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- notes + totals --}}
  <table class="ft">
    <tr>
      <td class="notes">
        <div class="nt-lbl">Notes</div>
        @if($paid)
          <p>Thank you for your business. This invoice has been paid in full &mdash; no further action is required.</p>
          <div class="pay-line">Paid via <b>{{ $method }}</b> on <b>{{ $payDate }}</b>.</div>
        @else
          @if($order->payment_method === 'cash_on_delivery')
            <p>Thank you for your order. Please keep <b>{{ zorder_price($order, $order->total) }}</b> ready for payment on delivery.</p>
          @else
            <p>Thank you for your order. Payment is due for this order.</p>
          @endif
        @endif
        @if($order->notes)<p style="margin-top:10px"><b style="color:#151537">Order notes:</b> {{ $order->notes }}</p>@endif
      </td>
      <td style="width:48%">
        <table class="sum">
          <tr><td>Subtotal</td><td class="v">{{ zorder_price($order, $order->subtotal) }}</td></tr>
          @if($order->discount > 0)<tr><td>Discount</td><td class="v" style="color:#15803d">&minus; {{ zorder_price($order, $order->discount) }}</td></tr>@endif
          <tr><td>Shipping</td><td class="v">{{ $order->shipping_fee > 0 ? zorder_price($order, $order->shipping_fee) : 'Free' }}</td></tr>
          @if($order->tax > 0)<tr><td>Sales Tax</td><td class="v">{{ zorder_price($order, $order->tax) }}</td></tr>@endif
          <tr class="divr"><td colspan="2">&nbsp;</td></tr>
          <tr class="grand"><td class="g-lbl">Total</td><td class="g-val">{{ zorder_price($order, $order->total) }}</td></tr>
          <tr class="paidrow"><td>Amount Paid</td><td class="v">{{ zorder_price($order, $amountPaid) }}</td></tr>
          <tr class="balrow"><td>Balance Due</td><td class="v">{{ zorder_price($order, $balanceDue) }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- footer --}}
  <div class="thanks">
    <div class="big">Thank you for choosing {{ $store['name'] }}</div>
    <div class="sm">
      Questions about this invoice? Contact us{{ ($store['email'] ?? '') ? ' at '.$store['email'] : '' }}{{ ($store['phone'] ?? '') ? ' or '.$store['phone'] : '' }}.<br>
      {{ $store['name'] }}{{ ($store['address'] ?? '') ? ' · '.$store['address'] : '' }}
    </div>
  </div>

</div>

@unless($pdf)<script>if (new URLSearchParams(location.search).get('print')==='1') setTimeout(()=>window.print(), 400);</script>@endunless
</body>
</html>
