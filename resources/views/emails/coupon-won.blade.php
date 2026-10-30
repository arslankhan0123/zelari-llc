<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f4f5fb;font-family:Arial,Helvetica,sans-serif;color:#1f2430">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5fb;padding:24px 12px">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px -18px rgba(16,24,40,.4)">
        <tr><td style="background:linear-gradient(135deg,#1e1b4b,#312e81);padding:30px 32px;text-align:center">
          <div style="color:#fbbf24;font-size:13px;font-weight:bold;letter-spacing:.08em;text-transform:uppercase">{{ $store['name'] }}</div>
          <div style="color:#fff;font-size:26px;font-weight:bold;margin-top:8px">🎉 You won {{ $prize }}!</div>
        </td></tr>
        <tr><td style="padding:30px 32px">
          <p style="font-size:15px;line-height:1.6;margin:0 0 18px">Hi{{ $name ? ' '.$name : '' }}, congratulations! You spun the wheel and won <b>{{ $prize }}</b>. Here is your personal coupon code:</p>
          <div style="text-align:center;margin:22px 0">
            <div style="display:inline-block;border:2px dashed #f59e0b;border-radius:12px;padding:16px 34px;font-size:26px;font-weight:bold;letter-spacing:.1em;color:#1e1b4b;background:#fffbeb">{{ $coupon->code }}</div>
          </div>
          <table role="presentation" width="100%" style="font-size:14px;color:#475467;margin:0 0 18px">
            <tr><td style="padding:4px 0">Discount</td><td align="right" style="padding:4px 0;color:#1f2430;font-weight:bold">{{ \App\Support\Marketing::couponLabel($coupon) }}</td></tr>
            @if($coupon->min_spend > 0)<tr><td style="padding:4px 0">Minimum spend</td><td align="right" style="padding:4px 0;color:#1f2430;font-weight:bold">${{ number_format($coupon->min_spend,2) }}</td></tr>@endif
            <tr><td style="padding:4px 0">Valid until</td><td align="right" style="padding:4px 0;color:#1f2430;font-weight:bold">{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'No expiry' }}</td></tr>
            <tr><td style="padding:4px 0">Uses</td><td align="right" style="padding:4px 0;color:#1f2430;font-weight:bold">One-time, your account only</td></tr>
          </table>
          <div style="text-align:center;margin:24px 0 8px">
            <a href="{{ $shopUrl }}" style="display:inline-block;background:linear-gradient(90deg,#fbbf24,#f59e0b);color:#1e1b4b;font-weight:bold;font-size:15px;text-decoration:none;padding:14px 34px;border-radius:12px">Shop now &amp; redeem</a>
          </div>
          <p style="font-size:12.5px;color:#98a2b3;line-height:1.6;margin:18px 0 0">Enter this code at checkout while logged in to this account. It can only be used once.</p>
        </td></tr>
        <tr><td style="padding:18px 32px;background:#f9fafb;text-align:center;font-size:12px;color:#98a2b3">{{ $store['name'] }}@if($store['email']) &middot; {{ $store['email'] }}@endif</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
