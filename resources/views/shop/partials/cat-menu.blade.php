{{--
  Recursive cascading category menu.
  @param Collection $items  categories (with childrenRecursive loaded)
  @param int        $depth  current depth (0 = root)
--}}
@php $depth = $depth ?? 0; @endphp
<ul class="cmenu {{ $depth ? 'cmenu-sub' : 'cmenu-root' }}" data-depth="{{ $depth }}">
  @foreach($items as $c)
    @php $kids = $c->relationLoaded('childrenRecursive') ? $c->childrenRecursive : collect(); @endphp
    <li class="{{ $kids->isNotEmpty() ? 'has-sub' : '' }}">
      <a href="{{ route('shop.index', ['category' => $c->slug]) }}">
        @if($depth === 0)<span class="ci"><i class="fa-solid {{ zcat_icon($c->name) }}"></i></span>@endif
        <span class="nm">{{ $c->name }}</span>
        @if(isset($c->total_count) && $kids->isEmpty())<span class="cnt-b">{{ $c->total_count }}</span>@endif
        @if($kids->isNotEmpty())<i class="fa-solid fa-chevron-right arr"></i>@endif
      </a>
      @if($kids->isNotEmpty())
        @include('shop.partials.cat-menu', ['items' => $kids, 'depth' => $depth + 1])
      @endif
    </li>
  @endforeach
</ul>
