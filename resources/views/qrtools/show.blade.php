@extends('layouts.store')
@section('title', $tool['name'])
@section('meta_description', $tool['desc'])

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-calcs.css') }}?v={{ @filemtime(public_path('css/zl-calcs.css')) ?: 1 }}">
<link rel="stylesheet" href="{{ asset('css/zl-newtools.css') }}?v={{ @filemtime(public_path('css/zl-newtools.css')) ?: 1 }}">
@endpush

@section('content')
<section class="sec wrap">
  <div class="cl-shell">
    <a href="{{ route('qrtools.index') }}" class="cl-back"><i class="fa-solid fa-arrow-left"></i> All QR tools</a>
    <div class="cl-head">
      <span class="cl-ic" style="background:{{ $tool['color'] }}"><i class="fa-solid {{ $tool['icon'] }}"></i></span>
      <div>
        <h1>{{ $tool['name'] }}</h1>
        <p>{{ $tool['desc'] }}</p>
      </div>
    </div>
    <div class="cl-card" id="toolUI"></div>
    <div class="cl-note"><i class="fa-solid fa-shield-halved"></i> Everything runs instantly in your browser — nothing is uploaded or stored.</div>

    <div class="cl-more">
      <h3>More QR tools</h3>
      <div class="cl-more-grid">
        @foreach(collect($tools)->except($slug)->take(5) as $s2 => $t2)
          @if($t2['live'] ?? false)
            <a href="{{ route('qrtools.show', $s2) }}"><span class="ic" style="background:{{ $t2['color'] }}"><i class="fa-solid {{ $t2['icon'] }}"></i></span> {{ $t2['name'] }}</a>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>window.ZTOOL={slug:@json($slug), name:@json($tool['name'])};</script>
<script src="{{ asset('js/zl-qrtools.js') }}?v={{ @filemtime(public_path('js/zl-qrtools.js')) ?: 1 }}"></script>
@endpush
