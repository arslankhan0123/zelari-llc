<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light only">
<title>Order {{ $order->order_number }}</title>
<style>
  :root{color-scheme:light only;supported-color-schemes:light only}
  body{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
  table{border-collapse:collapse}
  img{border:0;line-height:100%;outline:none;text-decoration:none}
  /* Stack side-by-side columns on phones */
  @media only screen and (max-width:600px){
    .wrap{width:100%!important}
    .pad{padding-left:18px!important;padding-right:18px!important}
    .col{display:block!important;width:100%!important;max-width:100%!important;padding:0!important}
    .col+.col{margin-top:10px!important}
    .tile{display:block!important;width:100%!important;box-sizing:border-box!important}
    .tile-inner{margin-bottom:8px!important}
    .h1{font-size:22px!important}
    .hdr-right{display:block!important;text-align:left!important;padding-top:10px!important}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#f4f5fb;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:{{ ztheme('brand_dark') }};">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f4f5fb" style="background:#f4f5fb;padding:20px 10px;">
<tr><td align="center">
<table role="presentation" class="wrap" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;">

  {{-- Header --}}
  <tr><td class="pad" bgcolor="{{ ztheme('brand') }}" style="background:{{ ztheme('brand') }};border-radius:18px 18px 0 0;padding:24px 32px;">
    <table role="presentation" width="100%"><tr>
      <td class="hdr-right" style="padding:0;">@include('emails.partials.logo')</td>
      <td class="hdr-right" align="right" style="color:#ffffff;font-size:12px;letter-spacing:.08em;text-transform:uppercase;font-weight:700;white-space:nowrap;">Order #{{ $order->order_number }}</td>
    </tr></table>
  </td></tr>

  {{-- Hero --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:34px 32px 10px;">
    @if($forAdmin)
      <div style="display:inline-block;background:#fff1f5;color:#c2185b;font-weight:700;font-size:12px;padding:5px 12px;border-radius:999px;margin-bottom:12px;">NEW ORDER RECEIVED</div>
      <h1 class="h1" style="margin:0 0 8px;font-size:26px;letter-spacing:-.5px;color:{{ ztheme('brand_dark') }};">{{ $order->shipping_name }} just placed an order</h1>
      <p style="margin:0;color:#6b7191;font-size:15px;line-height:1.6;">Total <b style="color:{{ ztheme('brand_dark') }};">{{ zorder_price($order, $order->total) }}</b> · {{ $order->payment_label }}{{ $order->payment_status === 'paid' ? ' (paid)' : '' }} · {{ $order->created_at->format('d M Y, g:i A') }}</p>
    @else
      <div style="display:inline-block;background:#eafaf3;color:#0d8f6a;font-weight:700;font-size:12px;padding:5px 12px;border-radius:999px;margin-bottom:12px;">✓ ORDER CONFIRMED</div>
      <h1 class="h1" style="margin:0 0 8px;font-size:26px;letter-spacing:-.5px;color:{{ ztheme('brand_dark') }};">Thanks, {{ explode(' ', trim($order->shipping_name))[0] ?? 'there' }}! We've got your order.</h1>
      <p style="margin:0;color:#6b7191;font-size:15px;line-height:1.6;">We're getting it ready now. You'll get another email when it ships. Your invoice is attached as a PDF.</p>
    @endif
  </td></tr>

  {{-- Summary tiles --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:18px 32px 8px;">
    @php $tiles = [['Order number','#'.$order->order_number],['Placed on',$order->created_at->format('d M Y, g:i A')],['Payment',$order->payment_label.($order->payment_status==='paid' ? ' · Paid' : ($order->payment_method==='cash_on_delivery' ? ' · Pay on delivery' : ' · Pending'))],['Status',ucfirst($order->status)]]; @endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
      @foreach(array_chunk($tiles, 2) as $row)
      <tr>
        @foreach($row as [$l,$v])
        <td width="50%" valign="top" style="padding:0 4px 8px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f6f7fc" style="background:#f6f7fc;border-radius:12px;"><tr><td style="padding:12px 14px;">
            <div style="font-size:10px;color:#9aa0bd;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">{{ $l }}</div>
            <div style="font-size:14px;font-weight:700;margin-top:3px;color:{{ ztheme('brand_dark') }};word-break:break-word;">{{ $v }}</div>
          </td></tr></table>
        </td>
        @endforeach
      </tr>
      @endforeach
    </table>
  </td></tr>

  {{-- Items --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:10px 32px 0;">
    <h3 style="margin:0 0 10px;font-size:15px;color:{{ ztheme('brand_dark') }};">Items</h3>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
      @foreach($order->items as $it)
      <tr>
        <td style="padding:12px 0;border-bottom:1px solid #eef0f6;">
          <div style="font-weight:700;font-size:14px;color:{{ ztheme('brand_dark') }};">{{ $it->product_name }}</div>
          <div style="color:#6b7191;font-size:12px;margin-top:2px;">{{ zorder_price($order, $it->unit_price) }} × {{ $it->quantity }}@if($it->product_sku) · {{ $it->product_sku }}@endif</div>
        </td>
        <td align="right" style="padding:12px 0;border-bottom:1px solid #eef0f6;font-weight:800;font-size:14px;white-space:nowrap;">{{ zorder_price($order, $it->subtotal) }}</td>
      </tr>
      @endforeach
      <tr><td style="padding:10px 0 4px;color:#6b7191;font-size:14px;">Subtotal</td><td align="right" style="padding:10px 0 4px;font-size:14px;">{{ zorder_price($order, $order->subtotal) }}</td></tr>
      @if($order->discount > 0)<tr><td style="padding:4px 0;color:#6b7191;font-size:14px;">Discount</td><td align="right" style="padding:4px 0;font-size:14px;color:#0d8f6a;">− {{ zorder_price($order, $order->discount) }}</td></tr>@endif
      <tr><td style="padding:4px 0;color:#6b7191;font-size:14px;">Shipping</td><td align="right" style="padding:4px 0;font-size:14px;color:#0d8f6a;">{{ $order->shipping_fee > 0 ? zorder_price($order, $order->shipping_fee) : 'Free' }}</td></tr>
      <tr><td style="padding:12px 0 0;font-size:16px;font-weight:800;border-top:2px solid {{ ztheme('brand_dark') }};color:{{ ztheme('brand_dark') }};">Total</td><td align="right" style="padding:12px 0 0;font-size:20px;font-weight:800;color:{{ ztheme('brand') }};border-top:2px solid {{ ztheme('brand_dark') }};">{{ zorder_price($order, $order->total) }}</td></tr>
    </table>
  </td></tr>

  {{-- Delivery --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:24px 32px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>
      <td class="col" width="50%" valign="top" style="padding-right:8px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f6f7fc" style="background:#f6f7fc;border-radius:14px;"><tr><td style="padding:16px;">
          <div style="font-size:10px;color:#9aa0bd;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Deliver to</div>
          <div style="font-weight:700;font-size:14px;color:{{ ztheme('brand_dark') }};">{{ $order->shipping_name }}</div>
          <div style="color:#6b7191;font-size:13px;line-height:1.6;margin-top:3px;">{{ $order->shipping_address }}<br>{{ $order->shipping_city }}<br>{{ $order->shipping_phone }}<br><a href="mailto:{{ $order->customer_email }}" style="color:#6b7191;text-decoration:none;">{{ $order->customer_email }}</a></div>
        </td></tr></table>
      </td>
      <td class="col" width="50%" valign="top" style="padding-left:8px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f6f7fc" style="background:#f6f7fc;border-radius:14px;"><tr><td style="padding:16px;">
          <div style="font-size:10px;color:#9aa0bd;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">What's next</div>
          <table role="presentation" cellspacing="0" cellpadding="0" style="color:#6b7191;font-size:13px;line-height:1.5;">
            <tr><td valign="top" style="padding:0 8px 6px 0;color:#0d8f6a;font-weight:700;">✓</td><td style="padding:0 0 6px;">Order confirmed</td></tr>
            <tr><td valign="top" style="padding:0 8px 6px 0;">○</td><td style="padding:0 0 6px;">Packed within {{ zship('pack_hours','24') }} hours</td></tr>
            <tr><td valign="top" style="padding:0 8px 6px 0;">○</td><td style="padding:0 0 6px;">Delivered in {{ zship('delivery_days','2–5') }} business days</td></tr>
            @if($order->payment_method==='cash_on_delivery')<tr><td valign="top" style="padding:0 8px 0 0;">○</td><td>Pay <b style="color:{{ ztheme('brand_dark') }};">{{ zorder_price($order, $order->total) }}</b> in cash on delivery</td></tr>@endif
            @if($order->payment_method==='bank_transfer')<tr><td valign="top" style="padding:0 8px 0 0;">○</td><td>Bank details will follow by email</td></tr>@endif
            @if($order->payment_method==='card')<tr><td valign="top" style="padding:0 8px 0 0;color:#0d8f6a;font-weight:700;">✓</td><td>Paid by card — nothing more to do</td></tr>@endif
            @if($order->payment_method==='paypal')<tr><td valign="top" style="padding:0 8px 0 0;color:#0d8f6a;font-weight:700;">✓</td><td>Paid with PayPal — nothing more to do</td></tr>@endif
          </table>
        </td></tr></table>
      </td>
    </tr></table>
    @if($order->notes)
    <div style="margin-top:14px;padding:12px 16px;border-left:3px solid {{ ztheme('brand') }};background:#f6f7fc;border-radius:0 12px 12px 0;color:#6b7191;font-size:13px;"><b style="color:{{ ztheme('brand_dark') }};">Order notes:</b> {{ $order->notes }}</div>
    @endif
  </td></tr>

  {{-- CTA --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:0 32px 32px;" align="center">
    @if($forAdmin)
      <a href="{{ url('/admin/orders/'.$order->id) }}" style="display:inline-block;background:{{ ztheme('brand') }};color:#ffffff;text-decoration:none;font-weight:700;padding:14px 28px;border-radius:12px;font-size:14px;mso-padding-alt:0;"><span style="color:#ffffff;">Open order in admin</span></a>
    @else
      <a href="{{ url('/shop') }}" style="display:inline-block;background:{{ ztheme('brand') }};color:#ffffff;text-decoration:none;font-weight:700;padding:14px 28px;border-radius:12px;font-size:14px;mso-padding-alt:0;"><span style="color:#ffffff;">Continue shopping</span></a>
    @endif
  </td></tr>

  {{-- Footer --}}
  <tr><td class="pad" bgcolor="{{ ztheme('brand_dark') }}" style="background:{{ ztheme('brand_dark') }};border-radius:0 0 18px 18px;padding:22px 32px;color:rgba(255,255,255,.7);font-size:12px;line-height:1.7;" align="center">
    <b style="color:#fff;font-size:14px;">{{ $store['name'] }}</b><br>
    {{ $store['address'] }}@if($store['phone']) · {{ $store['phone'] }}@endif @if($store['email']) · {{ $store['email'] }}@endif<br>
    <span style="color:rgba(255,255,255,.45);">Questions? Just reply to this email.</span>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
