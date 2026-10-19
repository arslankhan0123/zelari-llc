{{-- Email header logo: the logo uploaded in Admin → Settings → Branding, else the default white wordmark.
     The file is embedded (CID) so it shows in Gmail/Outlook even when the site is on localhost/ngrok. --}}
@php
  $zCustom = zbrand_custom('logo');
  $zPath   = $zCustom ? zbrand_path('logo') : public_path('images/logo-white.png');
  if (!is_file($zPath)) { $zPath = public_path('images/logo-white.png'); }
  $zSrc    = isset($message) && is_file($zPath) ? $message->embed($zPath) : zbrand('logo');
  $zName   = \App\Models\Setting::get('store_name', 'Zelari');
@endphp
@if($zCustom)
  <span style="display:inline-block;background:#ffffff;border-radius:10px;padding:6px 10px;"><img src="{{ $zSrc }}" alt="{{ $zName }}" height="32" style="height:32px;max-width:180px;display:block;border:0;"></span>
@else
  <img src="{{ $zSrc }}" alt="{{ $zName }}" height="40" style="height:40px;display:block;border:0;">
@endif
