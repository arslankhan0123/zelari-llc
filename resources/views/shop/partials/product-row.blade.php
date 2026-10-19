@once
@push('styles')
<style>
.prow{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px dashed var(--line)}
.prow:last-child{border-bottom:0}
.prow-img{width:64px;height:64px;border-radius:12px;background:var(--surface);flex-shrink:0;display:grid;place-items:center;overflow:hidden;transition:transform .3s}
.prow:hover .prow-img{transform:scale(1.06) rotate(-3deg)}
.prow-img img{width:100%;height:100%;object-fit:cover;display:block}
.prow-img i{color:#c8cce3;font-size:22px}
.prow-name{font-size:13.5px;font-weight:700;color:var(--ink);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}
.prow-name:hover{color:var(--p)}
.prow .stars{font-size:10px}
.prow-price{font-size:14px;font-weight:800;color:var(--p);margin-top:2px}
.prow-price s{font-size:11px;color:var(--faint);margin-left:5px;font-weight:600}
</style>
@endpush
@endonce
@php $img = $product->display_image; $real=$product->has_real_image; $n = 4 + (($product->id % 3) ? 1 : 0); @endphp
<div class="prow">
  <a href="{{ route('shop.show',$product->slug) }}" class="prow-img">
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">
  </a>
  <div style="min-width:0">
    <a href="{{ route('shop.show',$product->slug) }}" class="prow-name">{{ $product->name }}</a>
    <span class="stars">@for($i=1;$i<=5;$i++)<i class="fa-solid fa-star {{ $i>$n?'off':'' }}"></i>@endfor</span> <small style="font-size:11px;color:var(--faint)">({{ ($product->id*7)%60+12 }})</small>
    <div class="prow-price">{{ zprice($product->price) }}@if($product->compare_price)<s>{{ zprice($product->compare_price) }}</s>@endif</div>
  </div>
</div>
