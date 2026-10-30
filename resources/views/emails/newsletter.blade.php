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
    <div style="background:#fff;padding:24px;border-radius:0 0 16px 16px">
      @if(count($deals))
        <div style="font-size:16px;font-weight:800;color:#e5322d;margin:0 0 12px">🔥 Hot Deals</div>
        <table width="100%" cellpadding="0" cellspacing="0"><tr>
          @foreach($deals as $d)
          <td width="50%" style="padding:6px;vertical-align:top">
            <a href="{{ $d['url'] }}" style="text-decoration:none;color:#1f2430;display:block;border:1px solid #eee;border-radius:12px;overflow:hidden">
              @if($d['image'])<img src="{{ $d['image'] }}" alt="{{ $d['name'] }}" style="width:100%;height:120px;object-fit:cover;display:block">@endif
              <div style="padding:10px">
                <div style="font-size:13px;font-weight:700;line-height:1.3">{{ \Illuminate\Support\Str::limit($d['name'],40) }}</div>
                <div style="margin-top:4px"><span style="color:#e5322d;font-weight:800">{{ $d['price'] }}</span> @if($d['was'])<span style="color:#999;text-decoration:line-through;font-size:12px">{{ $d['was'] }}</span>@endif</div>
              </div>
            </a>
          </td>
          @if($loop->iteration % 2 == 0 && !$loop->last)</tr><tr>@endif
          @endforeach
        </tr></table>
      @endif
      @if(count($fresh))
        <div style="font-size:16px;font-weight:800;color:#1f7244;margin:20px 0 12px">🆕 New Arrivals</div>
        <table width="100%" cellpadding="0" cellspacing="0"><tr>
          @foreach($fresh as $d)
          <td width="50%" style="padding:6px;vertical-align:top">
            <a href="{{ $d['url'] }}" style="text-decoration:none;color:#1f2430;display:block;border:1px solid #eee;border-radius:12px;overflow:hidden">
              @if($d['image'])<img src="{{ $d['image'] }}" alt="{{ $d['name'] }}" style="width:100%;height:120px;object-fit:cover;display:block">@endif
              <div style="padding:10px">
                <div style="font-size:13px;font-weight:700;line-height:1.3">{{ \Illuminate\Support\Str::limit($d['name'],40) }}</div>
                <div style="margin-top:4px;color:#1f7244;font-weight:800">{{ $d['price'] }}</div>
              </div>
            </a>
          </td>
          @if($loop->iteration % 2 == 0 && !$loop->last)</tr><tr>@endif
          @endforeach
        </tr></table>
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
