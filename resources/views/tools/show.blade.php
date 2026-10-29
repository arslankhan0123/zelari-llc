@extends('layouts.store')
@section('title', $tool['name'])
@section('meta_description', $tool['desc'])

@php
  // tools processed right in the browser with pdf-lib — no upload needed
  $isLive = $tool['live'] ?? false;
  $multi  = in_array($slug, ['merge-pdf', 'jpg-to-pdf'], true);
  $accepts = [
    'jpg-to-pdf'   => ['image/jpeg,image/png', 'Select JPG / PNG images'],
    'word-to-pdf'  => ['.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Select Word file (.docx)'],
    'excel-to-pdf' => ['.xlsx,.xls,.csv', 'Select Excel file (.xlsx / .xls / .csv)'],
    'ppt-to-pdf'   => ['.pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation', 'Select PowerPoint file (.pptx)'],
  ];
  $accept = $accepts[$slug][0] ?? 'application/pdf';
  $pick   = $accepts[$slug][1] ?? 'Select PDF ' . ($multi ? 'files' : 'file');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/zl-tools.css') }}?v={{ @filemtime(public_path('css/zl-tools.css')) ?: 1 }}">
@endpush

@section('content')
<section class="sec wrap">
  <div class="ts reveal">
    <div style="text-align:left"><a href="{{ route('tools.index') }}" class="ts-back"><i class="fa-solid fa-arrow-left"></i> All PDF tools</a></div>
    <span class="ic" style="background:{{ $tool['color'] }}"><i class="fa-solid {{ $tool['icon'] }}"></i></span>
    <h1>{{ $tool['name'] }}</h1>
    <p>{{ $tool['desc'] }}</p>

    <div class="ts-drop" id="tsDrop">
      <i class="fa-solid fa-cloud-arrow-up"></i>
      <b>{{ $pick }}</b>
      <small>or drag and drop {{ $multi ? 'files' : 'a file' }} here</small>
      <input type="file" id="tsInput" accept="{{ $accept }}" {{ $multi ? 'multiple' : '' }} hidden>
    </div>
    @if($isLive)
      <div class="ts-priv"><i class="fa-solid fa-shield-halved"></i> Files are processed securely in your browser — nothing is uploaded.</div>
    @endif
    <div class="ts-files" id="tsFiles"></div>
    @if($slug === 'organize-pdf')
      <div class="org-grid" id="orgGrid"></div>
      <div class="org-hint" id="orgHint" style="display:none"><i class="fa-solid fa-hand"></i> Drag pages to reorder &nbsp;·&nbsp; ✕ to delete a page</div>
    @endif

    @if($slug === 'split-pdf')
      <div class="ts-opts"><label>Pages to extract <input type="text" id="optRange" placeholder="e.g. 1-3,5,8" autocomplete="off"></label></div>
    @elseif($slug === 'rotate-pdf')
      <div class="ts-opts"><label>Rotate by
        <select id="optAngle"><option value="90">90° clockwise</option><option value="180">180°</option><option value="270">90° counter-clockwise</option></select>
      </label></div>
    @elseif($slug === 'watermark-pdf')
      <div class="ts-opts"><label>Watermark text <input type="text" id="optText" placeholder="e.g. CONFIDENTIAL" maxlength="60" autocomplete="off"></label></div>
    @elseif($slug === 'ocr-pdf')
      <div class="ts-opts" style="font-size:12.5px;color:var(--muted)"><i class="fa-solid fa-circle-info"></i> Recognizes English text · up to 25 pages · can take a minute on big files</div>
    @elseif($slug === 'compress-pdf')
      <div class="ts-opts"><label>Compression level
        <select id="optLevel"><option value="lite">Recommended — keep quality &amp; text</option><option value="extreme">Extreme — smallest size (pages become images)</option></select>
      </label></div>
    @endif

    <button type="button" class="ts-btn" id="tsGo" disabled>{{ $tool['name'] }} <i class="fa-solid fa-arrow-right"></i></button>

    <div class="ts-more">
      <h3>More PDF tools</h3>
      <div class="ts-more-grid">
        @foreach(collect($tools)->except($slug)->take(8) as $s => $t)
          <a href="{{ route('tools.show', $s) }}"><span style="background:{{ $t['color'] }}"><i class="fa-solid {{ $t['icon'] }}"></i></span>{{ $t['name'] }}</a>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@if($isLive)<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>@endif
@if(in_array($slug, ['organize-pdf', 'compress-pdf', 'ocr-pdf', 'pdf-to-word', 'pdf-to-excel', 'pdf-to-ppt']))
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if(window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
@endif
@if($slug === 'ocr-pdf')<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.0/dist/tesseract.min.js"></script>@endif
@if(in_array($slug, ['pdf-to-excel', 'excel-to-pdf']))<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>@endif
@if($slug === 'pdf-to-word')<script src="https://cdn.jsdelivr.net/npm/docx@8.5.0/build/index.umd.js"></script>@endif
@if($slug === 'pdf-to-ppt')<script src="https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.js"></script>@endif
@if($slug === 'word-to-pdf')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.2/dist/docx-preview.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endif
@if($slug === 'ppt-to-pdf')<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>@endif
<script>window.ZTOOL={slug:@json($slug), live:@json($isLive), multi:@json($multi), name:@json($tool['name']), csrf:'{{ csrf_token() }}'};</script>
<script src="{{ asset('js/zl-tools.js') }}?v={{ @filemtime(public_path('js/zl-tools.js')) ?: 1 }}"></script>
@endpush
