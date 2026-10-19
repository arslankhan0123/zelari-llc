{{--
  Inner-page hero slider (breadcrumb banner style).
  @param array  $slides  [['title'=>'Plain <span>Highlight</span>','text'=>'...','icon'=>'fa-bag-shopping'], ...]
  @param array  $crumbs  [['Home', route('home')], ['Shop', null]]
  @param string $id      unique swiper id
--}}
@php
  $id = $id ?? 'pageHero';
  $defaultBg = ['shopHero'=>'shop','cartHero'=>'cart','aboutHero'=>'about','contactHero'=>'contact'][$id] ?? 'hero1';
  $altBgs = ['hero1','hero2','hero3','deal','shop'];
@endphp
<section class="page-hero">
  <div class="swiper" id="{{ $id }}">
    <div class="swiper-wrapper">
      @foreach($slides as $s)
      <div class="swiper-slide ph-slide">
        <img class="ph-bg" data-swiper-parallax="30%" src="{{ $s['bg'] ?? zbg($loop->first ? $defaultBg : $altBgs[$loop->index % count($altBgs)]) }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
        <span class="ph-shade"></span>
        <div class="ph-grid"></div>
        <div class="wrap ph-content">
          <div class="ph-crumb">
            @foreach($crumbs as $i => $c)
              @if($c[1] ?? null)<a href="{{ $c[1] }}">{{ $c[0] }}</a>@else<span style="color:#fff;font-weight:600">{{ $c[0] }}</span>@endif
              @if(!$loop->last)<i class="fa-solid fa-chevron-right"></i>@endif
            @endforeach
          </div>
          <h1 data-swiper-parallax="-200">{!! $s['title'] !!}</h1>
          @if(!empty($s['text']))<p>{{ $s['text'] }}</p>@endif
        </div>
        <div class="ph-art"><div class="ring"></div><div class="cube"></div><div class="ic"><i class="fa-solid {{ $s['icon'] ?? 'fa-sparkles' }}"></i></div></div>
      </div>
      @endforeach
    </div>
    @if(count($slides) > 1)<div class="swiper-pagination"></div>@endif
  </div>
</section>
@push('scripts')
<script>
new Swiper('#{{ $id }}',{loop:{{ count($slides)>1?'true':'false' }},speed:900,parallax:true,autoplay:{delay:5500,disableOnInteraction:false},pagination:{el:'#{{ $id }} .swiper-pagination',clickable:true}});
</script>
@endpush
