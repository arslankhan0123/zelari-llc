<!DOCTYPE html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f4f5f8;font-family:Arial,Helvetica,sans-serif;color:#1f2430">
  <div style="max-width:600px;margin:0 auto;padding:24px 12px">
    <div style="text-align:center;padding:12px 0 20px">
      @if($logo)<img src="{{ $logo }}" alt="{{ $store }}" style="max-height:40px">@else<div style="font-size:24px;font-weight:800;color:#1e1b4b">{{ $store }}</div>@endif
    </div>
    <div style="background:#1e1b4b;border-radius:16px 16px 0 0;padding:28px 24px;text-align:center">
      <div style="color:#fff;font-size:22px;font-weight:800">This week at {{ $store }} 🛍️</div>
      <div style="color:#c7d2fe;font-size:14px;margin-top:6px">Fresh deals and new arrivals, picked for you.</div>
    </div>
    <div style="background:#fff;padding:20px 22px;border-radius:0 0 16px 16px">

      @php
        $row = function($d, $saleColor) {
          $stars = '';
          for ($i = 1; $i <= 5; $i++) {
              $stars .= '<span style="color:'.($i <= ($d['rating'] ?? 0) ? '#f5a623' : '#d8d8d8').';font-size:13px">&#9733;</span>';
          }
          $html  = '<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #f0f1f4"><tr>';
          $html .= '<td width="76" style="padding:10px 12px 10px 0;vertical-align:top">';
          $html .= '<a href="'.$d['url'].'" style="text-decoration:none"><img src="'.$d['image'].'" alt="" width="64" height="64" style="width:64px;height:64px;object-fit:cover;border-radius:10px;display:block;border:1px solid #eee"></a>';
          $html .= '</td><td style="vertical-align:top;padding:10px 0">';
          $html .= '<a href="'.$d['url'].'" style="font-size:14px;font-weight:700;color:#1f2430;text-decoration:none;line-height:1.35">'.e(\Illuminate\Support\Str::limit($d['name'], 48)).'</a>';
          $html .= '<div style="margin:4px 0 2px">'.$stars.($d['reviews'] ? ' <span style="color:#9aa0ad;font-size:11px">('.$d['reviews'].')</span>' : '').'</div>';
          $html .= '<div style="font-size:15px;font-weight:800;color:'.$saleColor.'">'.$d['price'];
          if (!empty($d['was'])) $html .= ' <span style="color:#9aa0ad;font-weight:600;font-size:12px;text-decoration:line-through">'.$d['was'].'</span>';
          $html .= '</div></td></tr></table>';
          return $html;
        };
      @endphp

      @if(count($deals))
        <div style="font-size:16px;font-weight:800;color:#e5322d;margin:0 0 6px">🔥 Hot Deals</div>
        @foreach($deals as $d){!! $row($d, '#e5322d') !!}@endforeach
      @endif

      @if(count($fresh))
        <div style="font-size:16px;font-weight:800;color:#1f7244;margin:22px 0 6px">🆕 New Arrivals</div>
        @foreach($fresh as $d){!! $row($d, '#1f7244') !!}@endforeach
      @endif

      <div style="text-align:center;margin:26px 0 6px">
        <a href="{{ $shopUrl }}" style="background:#6d28d9;color:#fff;text-decoration:none;font-weight:700;padding:14px 32px;border-radius:12px;display:inline-block">Shop Everything →</a>
      </div>
    </div>
    <div style="text-align:center;color:#9aa0ad;font-size:11px;padding:18px 10px;line-height:1.6">
      You're receiving this because you subscribed at {{ $store }}.<br>
      <a href="{{ $unsub }}" style="color:#9aa0ad">Unsubscribe</a>
    </div>
  </div>
</body></html>
