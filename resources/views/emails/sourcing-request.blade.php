<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<title>Sourcing request {{ $sourcing->reference }}</title>
<style>
  table{border-collapse:collapse}
  img{border:0;line-height:100%;outline:none;text-decoration:none}
  @media only screen and (max-width:600px){.wrap{width:100%!important}.pad{padding-left:18px!important;padding-right:18px!important}}
</style>
</head>
<body style="margin:0;padding:0;background:#f4f5fb;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:{{ ztheme('brand_dark') }};">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f4f5fb" style="background:#f4f5fb;padding:20px 10px;">
<tr><td align="center">
<table role="presentation" class="wrap" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;">

  <tr><td class="pad" bgcolor="{{ ztheme('brand') }}" style="background:{{ ztheme('brand') }};border-radius:18px 18px 0 0;padding:24px 32px;">
    <table role="presentation" width="100%"><tr>
      <td style="padding:0;">@include('emails.partials.logo')</td>
      <td align="right" style="color:#ffffff;font-size:12px;letter-spacing:.08em;text-transform:uppercase;font-weight:700;white-space:nowrap;">{{ $sourcing->reference }}</td>
    </tr></table>
  </td></tr>

  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;padding:28px 32px 8px;">
    <h1 style="margin:0 0 6px;font-size:21px;line-height:1.3;">New sourcing request</h1>
    <p style="margin:0 0 22px;font-size:14px;color:#6b7280;">
      {{ $sourcing->name }} wants {{ $sourcing->items->count() }} {{ Str::plural('product', $sourcing->items->count()) }} sourced{{ $sourcing->source_country ? ' from ' . $sourcing->source_country : '' }}.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#EFF6FF;border:1px solid #DBEAFE;border-radius:12px;">
      <tr><td style="padding:14px 16px;font-size:14px;line-height:1.8;">
        <strong style="color:#1f2937;">{{ $sourcing->name }}</strong>@if($sourcing->company) · {{ $sourcing->company }}@endif<br>
        <a href="mailto:{{ $sourcing->email }}" style="color:{{ ztheme('brand') }};text-decoration:none;">{{ $sourcing->email }}</a>@if($sourcing->phone) · {{ $sourcing->phone }}@endif
        @if($sourcing->deliver_to)<br><span style="color:#6b7280;">Deliver to:</span> {{ $sourcing->deliver_to }}@endif
        @if($sourcing->budget)<br><span style="color:#6b7280;">Budget:</span> {{ $sourcing->budget }}@endif
        @if($sourcing->needed_by)<br><span style="color:#6b7280;">Needed by:</span> {{ $sourcing->needed_by->format('d M Y') }}@endif
      </td></tr>
    </table>

    <p style="margin:22px 0 8px;font-size:12px;letter-spacing:.07em;text-transform:uppercase;font-weight:700;color:#9ca3af;">Products</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;font-size:14px;">
      <tr style="background:#f9fafb;">
        <th align="left"  style="padding:10px 14px;font-size:12px;color:#6b7280;">Product</th>
        <th align="right" style="padding:10px 14px;font-size:12px;color:#6b7280;white-space:nowrap;">Qty</th>
      </tr>
      @foreach($sourcing->items as $item)
      <tr style="border-top:1px solid #f1f2f7;">
        <td style="padding:12px 14px;color:#374151;">
          <strong>{{ $item->name }}</strong>
          @if($item->specs)<br><span style="color:#6b7280;font-size:13px;">{{ $item->specs }}</span>@endif
          @if($item->target_price)<br><span style="color:#6b7280;font-size:13px;">Target price: {{ $item->target_price }}</span>@endif
          @if($item->reference_url)<br><a href="{{ $item->reference_url }}" style="color:{{ ztheme('brand') }};font-size:13px;">Reference link</a>@endif
        </td>
        <td align="right" style="padding:12px 14px;color:#374151;white-space:nowrap;">{{ number_format($item->quantity) }}{{ $item->unit ? ' ' . $item->unit : '' }}</td>
      </tr>
      @endforeach
    </table>

    @if($sourcing->notes)
    <p style="margin:22px 0 8px;font-size:12px;letter-spacing:.07em;text-transform:uppercase;font-weight:700;color:#9ca3af;">Notes</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:12px;">
      <tr><td style="padding:14px 16px;font-size:14px;line-height:1.7;color:#374151;white-space:pre-wrap;">{{ $sourcing->notes }}</td></tr>
    </table>
    @endif

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:26px 0 8px;">
      <tr>
        <td style="border-radius:10px;background:{{ ztheme('brand') }};">
          <a href="mailto:{{ $sourcing->email }}?subject={{ rawurlencode('Re: sourcing request ' . $sourcing->reference) }}"
             style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Reply to {{ $sourcing->name }}</a>
        </td>
        <td style="width:10px;"></td>
        <td style="border-radius:10px;border:1px solid #d1d5db;">
          <a href="{{ $adminUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#374151;text-decoration:none;">Open in admin</a>
        </td>
      </tr>
    </table>
  </td></tr>

  <tr><td class="pad" bgcolor="#ffffff" style="background:#ffffff;border-radius:0 0 18px 18px;padding:18px 32px 26px;border-top:1px solid #f1f2f7;">
    <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
      Sent automatically by {{ $store['name'] }} when someone submits the sourcing form. Replying to this email goes to {{ $sourcing->email }}.
    </p>
  </td></tr>

</table>
</td></tr></table>
</body>
</html>
