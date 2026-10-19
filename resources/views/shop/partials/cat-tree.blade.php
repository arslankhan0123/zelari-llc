{{--
  Sidebar category tree (recursive). Auto-expands the branch containing the active category.
  @param Collection $items
  @param array      $activePath  slugs from root → selected category
  @param int        $depth
--}}
@php $depth = $depth ?? 0; $activePath = $activePath ?? []; @endphp
@foreach($items as $c)
  @php
    $kids   = $c->relationLoaded('childrenRecursive') ? $c->childrenRecursive : collect();
    $isOn   = request('category') === $c->slug;
    $inPath = in_array($c->slug, $activePath, true);
  @endphp
  <div class="ctree-item" style="--d:{{ $depth }}">
    <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $c->slug])) }}" class="{{ $isOn ? 'on' : '' }} {{ $inPath && !$isOn ? 'path' : '' }}">
      @if($depth === 0)<i class="fa-solid {{ zcat_icon($c->name) }}"></i>@else<i class="fa-solid fa-angle-right"></i>@endif
      {{ $c->name }}
      @if(isset($c->total_count))<span class="n">{{ $c->total_count }}</span>@endif
      @if($kids->isNotEmpty())<span class="tog {{ $inPath ? 'open' : '' }}" onclick="event.preventDefault();event.stopPropagation();this.closest('a').nextElementSibling.classList.toggle('show');this.classList.toggle('open')"><i class="fa-solid fa-chevron-down"></i></span>@endif
    </a>
    @if($kids->isNotEmpty())
      <div class="ctree-kids {{ $inPath ? 'show' : '' }}">
        @include('shop.partials.cat-tree', ['items' => $kids, 'activePath' => $activePath, 'depth' => $depth + 1])
      </div>
    @endif
  </div>
@endforeach
