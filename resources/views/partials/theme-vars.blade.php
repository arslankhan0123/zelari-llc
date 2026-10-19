{{-- CSS variables generated from config/theme.php — included by both layouts. Edit the config, not this file. --}}
@php $t = config('theme'); @endphp
<style id="theme-vars">
:root{
  --brand:{{ $t['brand'] }};--brand-dark:{{ $t['brand_dark'] }};--brand-light:{{ $t['brand_light'] }};--brand-soft:{{ $t['brand_soft'] }};
  --accent:{{ $t['accent'] }};--accent-soft:{{ $t['accent_soft'] }};
  --brand-grad:{{ $t['grad'] }};--brand-grad-hover:{{ $t['grad_hover'] }};
  --sale:{{ $t['sale'] }};--ok:{{ $t['success'] }};--warn:{{ $t['warning'] }};--danger:{{ $t['danger'] }};--star:{{ $t['star'] }};
  --ink:{{ $t['ink'] }};--ink-2:{{ $t['ink_2'] }};--muted:{{ $t['muted'] }};--faint:{{ $t['faint'] }};
  --surface:{{ $t['surface'] }};--line:{{ $t['line'] }};--line-2:{{ $t['line_2'] }};
  --shadow-rgb:{{ $t['shadow_rgb'] }};
}
</style>
<link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ @filemtime(public_path('css/theme.css')) ?: 1 }}">
