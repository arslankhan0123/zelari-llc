@extends('layouts.store')
@section('title', $tool['name'] . ' — Free Online Image Tool')
@section('meta_description', $tool['desc'])

@php
  $multi = in_array($slug, ['compress-image', 'convert-to-jpg', 'convert-to-png', 'convert-to-webp']);
  $more  = collect($tools)->filter(fn ($t, $s) => $s !== $slug && ($t['live'] ?? false))->take(4);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-tools.css') }}?v={{ @filemtime(public_path('css/zl-tools.css')) ?: 1 }}">
<link rel="stylesheet" href="{{ asset('css/zl-imgtools.css') }}?v={{ @filemtime(public_path('css/zl-imgtools.css')) ?: 1 }}">
@endpush

@section('content')
<section class="sec wrap th-sec">
  <a href="{{ route('imgtools.index') }}" class="im-back"><i class="fa-solid fa-arrow-left"></i> All image tools</a>

  <div class="im-head">
    <span class="ic" style="background:{{ $tool['color'] }}"><i class="fa-solid {{ $tool['icon'] }}"></i></span>
    <div>
      <h1>{{ $tool['name'] }}</h1>
      <p>{{ $tool['desc'] }}</p>
    </div>
  </div>

  <div class="im-card">
    <div class="im-drop" id="imDrop">
      <i class="fa-solid fa-cloud-arrow-up"></i>
      <b>Choose {{ $multi ? 'images' : 'an image' }} or drag &amp; drop {{ $multi ? 'them' : 'it' }} here</b>
      <span>JPG, PNG, WebP, GIF or BMP{{ $multi ? ' — several files at once' : '' }}</span>
      <button type="button" class="im-pick" id="imPick">Select image{{ $multi ? 's' : '' }}</button>
      <input type="file" id="imInput" accept="image/*" {{ $multi ? 'multiple' : '' }} hidden>
    </div>

    <div class="im-files" id="imFiles" hidden></div>
    <div class="im-stagewrap" id="imStageWrap" hidden><canvas id="imStage"></canvas></div>
    <div class="im-opts" id="imOpts" hidden></div>
    <div class="im-result" id="imResult" hidden></div>

    <div class="im-foot" id="imFoot" hidden>
      <span class="im-note"><i class="fa-solid fa-shield-halved"></i> Processed in your browser &mdash; your images never leave your device.</span>
      <button type="button" class="im-go" id="imGo"><i class="fa-solid fa-bolt"></i> <span>Go</span></button>
    </div>
  </div>

  @if($more->count())
  <div class="im-more">
    <h2>More image tools</h2>
    <div class="tl-grid">
      @foreach($more as $s2 => $t2)
        <a href="{{ route('imgtools.show', $s2) }}" class="tl-card">
          <span class="ic" style="background:{{ $t2['color'] }}"><i class="fa-solid {{ $t2['icon'] }}"></i></span>
          <b>{{ $t2['name'] }}</b>
          <p>{{ $t2['desc'] }}</p>
        </a>
      @endforeach
    </div>
  </div>
  @endif
</section>
@endsection

@push('scripts')
@if($multi)
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
@endif
<script>window.ZIMG = { slug: @json($slug), name: @json($tool['name']) };</script>
<script src="{{ asset('js/zl-imgtools.js') }}?v={{ @filemtime(public_path('js/zl-imgtools.js')) ?: 1 }}"></script>
@endpush
