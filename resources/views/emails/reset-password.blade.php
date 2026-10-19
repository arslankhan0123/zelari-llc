<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light only">
<title>Reset your password</title>
<style>
  :root{color-scheme:light only;supported-color-schemes:light only}
  body{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
  table{border-collapse:collapse}
  img{border:0;line-height:100%;outline:none;text-decoration:none}
  @media only screen and (max-width:600px){
    .wrap{width:100%!important}
    .pad{padding-left:18px!important;padding-right:18px!important}
    .h1{font-size:22px!important}
    .btn a{display:block!important}
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
      <td style="padding:0;">@include('emails.partials.logo')</td>
      <td align="right" style="color:#ffffff;font-size:12px;letter-spacing:.08em;text-transform:uppercase;font-weight:700;white-space:nowrap;">Password reset</td>
    </tr></table>
  </td></tr>

  {{-- Body --}}
  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:36px 32px 28px;">
    <h1 class="h1" style="margin:0 0 10px;font-size:26px;letter-spacing:-.5px;color:{{ ztheme('brand_dark') }};">Reset your password</h1>
    <p style="margin:0 0 22px;color:#6b7191;font-size:15px;line-height:1.65;">Hi {{ $user->name }}, we received a request to reset the password for your {{ $store['name'] }} account. Click the button below to choose a new one.</p>

    <table role="presentation" cellspacing="0" cellpadding="0" class="btn" style="margin:0 0 22px;">
      <tr><td bgcolor="{{ ztheme('brand') }}" style="background:{{ ztheme('brand') }};border-radius:12px;">
        <a href="{{ $url }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-weight:700;font-size:15px;text-decoration:none;border-radius:12px;">Reset password</a>
      </td></tr>
    </table>

    <p style="margin:0 0 6px;color:#6b7191;font-size:13.5px;line-height:1.6;">This link expires in <b style="color:{{ ztheme('brand_dark') }};">{{ $expire }} minutes</b>. If you didn't ask to reset your password, you can safely ignore this email — your password won't change.</p>
  </td></tr>

  {{-- Fallback link --}}
  <tr><td class="pad" bgcolor="#f8f9fd" style="background:#f8f9fd;padding:18px 32px;border-top:1px solid #eceef5;">
    <p style="margin:0 0 6px;color:#9094ad;font-size:12px;">If the button doesn't work, copy and paste this link into your browser:</p>
    <p style="margin:0;font-size:12px;word-break:break-all;"><a href="{{ $url }}" style="color:{{ ztheme('brand') }};">{{ $url }}</a></p>
  </td></tr>

  {{-- Footer --}}
  <tr><td class="pad" bgcolor="{{ ztheme('brand_dark') }}" style="background:{{ ztheme('brand_dark') }};border-radius:0 0 18px 18px;padding:20px 32px;color:rgba(255,255,255,.7);font-size:12px;line-height:1.7;">
    <b style="color:#ffffff;">{{ $store['name'] }}</b>
    @if($store['address']) · {{ $store['address'] }}@endif
    @if($store['phone'])<br>{{ $store['phone'] }}@endif
    @if($store['email']) · <a href="mailto:{{ $store['email'] }}" style="color:#ffffff;text-decoration:none;">{{ $store['email'] }}</a>@endif
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
