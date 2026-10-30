@extends('layouts.admin')
@section('title', 'Settings')
@section('breadcrumb', 'Store configuration — branding, email, payments and contact details')

@section('content')
@php
  $s = $settings;
  $tabs = [
    'branding' => ['Branding', 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343'],
    'email'    => ['Email (SMTP)', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    'store'    => ['Store Details', 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'],
    'payments' => ['Payment Methods', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
  ];
@endphp

{{-- Mobile: custom dropdown --}}
@php [$curLabel, $curIcon] = $tabs[$section]; @endphp
<div class="md:hidden mb-6 relative z-20" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
  <button type="button" @click="open = !open"
          class="w-full flex items-center justify-between gap-2 bg-brand-grad text-white font-semibold text-sm rounded-xl px-4 py-3.5 shadow"
          style="box-shadow:0 8px 24px rgba(var(--shadow-rgb),.3)">
    <span class="flex items-center gap-2.5">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $curIcon }}"/></svg>
      {{ $curLabel }}
    </span>
    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
  </button>
  <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
       class="absolute left-0 right-0 mt-2 bg-white rounded-xl overflow-hidden border border-gray-100" style="box-shadow:0 16px 40px -12px rgba(16,24,40,.25)">
    @foreach($tabs as $key => [$label, $icon])
      <a href="{{ route('admin.settings.show', $key) }}"
         class="flex items-center gap-2.5 px-4 py-3 text-sm font-semibold {{ $section === $key ? 'bg-brand-grad text-white' : 'text-gray-700 hover:bg-slate-50' }}">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        {{ $label }}
        @if($section === $key)<svg class="w-4 h-4 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>@endif
      </a>
    @endforeach
  </div>
</div>

{{-- Desktop: tab chips --}}
<div class="hidden md:flex flex-wrap gap-2 mb-6">
  @foreach($tabs as $key => [$label, $icon])
    <a href="{{ route('admin.settings.show', $key) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $section === $key ? 'bg-brand-grad text-white shadow' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-100' }}">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
      {{ $label }}
    </a>
  @endforeach
</div>

@include('admin.settings.sections.' . $section)
@endsection
