@php
  $store   = zstore('name', 'Zelari');
  $heading = $heading ?? 'Your verification code';
  $intro   = $intro ?? 'Use the code below to continue.';
  $minutes = $minutes ?? 10;
  $preview = $preview ?? ('Your code is '.$code.' — expires in '.$minutes.' minutes.');
  // Branding logo (print logo is PNG/JPG, made for emails/invoices). Embed it for
  // reliable rendering in real email clients; fall back to a URL in preview, then text.
  $logoSrc = null;
  try {
      if (isset($message)) { $logoSrc = $message->embed(zbrand_path('print')); }
      else { $logoSrc = zbrand('print'); }
  } catch (\Throwable $e) { $logoSrc = null; }
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ $heading }}</title>
</head>
<body style="margin:0;padding:0;background-color:#eef0f6;-webkit-font-smoothing:antialiased;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preview }}</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef0f6;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(31,27,80,.10);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

          <!-- Brand accent strip -->
          <tr><td style="height:5px;background-color:#4f46e5;background-image:linear-gradient(90deg,#4f46e5 0%,#7c3aed 55%,#8b5cf6 100%);font-size:0;line-height:0;">&nbsp;</td></tr>

          <!-- Logo band -->
          <tr>
            <td style="padding:26px 32px 22px;text-align:center;border-bottom:1px solid #eef0f6;">
              @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $store }}" style="max-height:46px;max-width:240px;display:inline-block;border:0;outline:none;text-decoration:none;">
              @else
                <span style="font-size:24px;font-weight:800;letter-spacing:-.5px;color:#1f2937;">{{ $store }}</span>
              @endif
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:34px 36px 8px;">
              <h1 style="margin:0 0 10px;font-size:21px;line-height:1.3;font-weight:800;color:#111827;">{{ $heading }}</h1>
              <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#6b7280;">{{ $intro }}</p>

              <!-- Code box -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="background-color:#f5f3ff;border:1px solid #ddd6fe;border-radius:14px;padding:22px 16px;">
                    <div style="font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#7c3aed;margin-bottom:8px;">Verification code</div>
                    <div style="font-size:38px;font-weight:800;letter-spacing:10px;color:#3730a3;font-family:'Courier New',Courier,monospace;">{{ $code }}</div>
                  </td>
                </tr>
              </table>

              <p style="margin:22px 0 0;font-size:13.5px;line-height:1.6;color:#6b7280;">
                This code expires in <strong style="color:#374151;">{{ $minutes }} minutes</strong>. For your security, never share it with anyone.
              </p>
            </td>
          </tr>

          <!-- Divider -->
          <tr><td style="padding:24px 36px 0;"><div style="border-top:1px solid #eceef3;height:1px;line-height:1px;">&nbsp;</div></td></tr>

          <!-- Note -->
          <tr>
            <td style="padding:16px 36px 32px;">
              <p style="margin:0;font-size:12.5px;line-height:1.6;color:#9ca3af;">
                If you didn't request this, you can safely ignore this email &mdash; your account is still secure.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#fafafe;padding:20px 36px;text-align:center;border-top:1px solid #eceef3;">
              <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} {{ $store }}. All rights reserved.</p>
            </td>
          </tr>

        </table>
        <p style="margin:16px 0 0;font-size:11px;color:#b6bac6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">This is an automated message, please do not reply.</p>
      </td>
    </tr>
  </table>
</body>
</html>
