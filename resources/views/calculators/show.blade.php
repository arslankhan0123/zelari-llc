@extends('layouts.store')
@section('title', $tool['name'])
@section('meta_description', $tool['desc'])

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-calcs.css') }}?v={{ @filemtime(public_path('css/zl-calcs.css')) ?: 1 }}">
@endpush

@section('content')
<section class="sec wrap">
  <div class="cl-shell">
    <a href="{{ route('calculators.index') }}" class="cl-back"><i class="fa-solid fa-arrow-left"></i> All calculators</a>
    <div class="cl-head">
      <span class="cl-ic" style="background:{{ $tool['color'] }}"><i class="fa-solid {{ $tool['icon'] }}"></i></span>
      <div>
        <h1>{{ $tool['name'] }}</h1>
        <p>{{ $tool['desc'] }}</p>
      </div>
    </div>
    <div class="cl-card" id="calcUI"></div>
    <div class="cl-note"><i class="fa-solid fa-shield-halved"></i> Calculations run instantly in your browser — nothing is uploaded or stored.</div>

    <div class="cl-more">
      <h3>More calculators</h3>
      <div class="cl-more-grid">
        @foreach(collect($tools)->where('cat', $tool['cat'])->except($slug)->take(4) as $s2 => $t2)
          @if($t2['live'] ?? false)
            <a href="{{ route('calculators.show', $s2) }}"><span class="ic" style="background:{{ $t2['color'] }}"><i class="fa-solid {{ $t2['icon'] }}"></i></span> {{ $t2['name'] }}</a>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>window.ZCALC={slug:@json($slug), name:@json($tool['name'])};</script>
<script src="{{ asset('js/zl-calcs.js') }}?v={{ @filemtime(public_path('js/zl-calcs.js')) ?: 1 }}"></script>
@endpush
