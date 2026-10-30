@extends('layouts.admin')
@section('title', 'SEO')
@section('breadcrumb', 'Automated daily on-site SEO audit & report')

@php
  $scoreColor = function($s){ return $s>=80 ? ['#16a34a','#dcfce7'] : ($s>=50 ? ['#d97706','#fef3c7'] : ['#dc2626','#fee2e2']); };
@endphp

@section('content')
<div class="max-w-6xl">

  {{-- Header row --}}
  <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
      <div class="font-bold text-gray-800 text-lg">SEO Health</div>
      <p class="text-sm text-gray-500 mt-0.5">
        @if($report)
          Last audit: {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->diffForHumans() }}
          <span class="text-gray-400">({{ \Illuminate\Support\Carbon::parse($report['generated_at'])->format('d M Y, H:i') }})</span>
        @else
          No audit has run yet.
        @endif
      </p>
    </div>
    <form method="POST" action="{{ route('admin.seo.run') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Running audit…'">
      @csrf
      <button class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition">
        <i class="fa-solid fa-rotate"></i> Run audit now
      </button>
    </form>
  </div>

  @if(request('done'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm font-semibold flex items-center gap-2">
      <i class="fa-solid fa-circle-check"></i> SEO audit finished — report updated below.
    </div>
  @endif

  @if(!$report)
    {{-- Empty state --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center">
      <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 grid place-items-center text-2xl mx-auto mb-4"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
      <div class="font-bold text-gray-800 text-lg mb-1">Run your first SEO audit</div>
      <p class="text-sm text-gray-500 max-w-md mx-auto mb-5">It crawls your key pages and checks your whole product catalog for SEO issues — titles, meta descriptions, headings, images and more — then scores your site. After the first run it keeps running automatically every day.</p>
      <form method="POST" action="{{ route('admin.seo.run') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Running…'">
        @csrf
        <button class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl"><i class="fa-solid fa-play"></i> Run audit now</button>
      </form>
    </div>
  @else
    @php [$sc,$scBg] = $scoreColor($report['score']); @endphp

    {{-- Score cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="relative w-16 h-16 shrink-0">
          <svg viewBox="0 0 36 36" class="w-16 h-16 -rotate-90">
            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eee" stroke-width="3.4"></circle>
            <circle cx="18" cy="18" r="15.9" fill="none" stroke="{{ $sc }}" stroke-width="3.4" stroke-linecap="round" stroke-dasharray="{{ $report['score'] }} 100"></circle>
          </svg>
          <span class="absolute inset-0 grid place-items-center font-extrabold text-gray-800">{{ $report['score'] }}</span>
        </div>
        <div><div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Overall SEO</div><div class="text-sm font-semibold" style="color:{{ $sc }}">{{ $report['score']>=80?'Good':($report['score']>=50?'Needs work':'Poor') }}</div></div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Page SEO</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1">{{ $report['page_score'] }}<span class="text-base text-gray-400">/100</span></div>
        <div class="text-xs text-gray-500 mt-1">{{ $report['pages_ok'] }}/{{ $report['pages_audited'] }} pages OK</div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Catalog health</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1">{{ $report['catalog_health'] }}<span class="text-base text-gray-400">%</span></div>
        <div class="text-xs text-gray-500 mt-1">{{ $report['products_checked'] }} products checked</div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Issues found</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1">{{ array_sum($report['issue_counts']) }}</div>
        <div class="text-xs text-gray-500 mt-1">across all pages</div>
      </div>
    </div>

    {{-- Trend --}}
    @if(count($history) > 1)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-4">Score trend ({{ count($history) }} days)</div>
      <div class="flex items-end gap-2 h-32">
        @foreach($history as $h)
          @php [$hc] = $scoreColor($h['score']); @endphp
          <div class="flex-1 flex flex-col items-center gap-1" title="{{ $h['date'] }}: {{ $h['score'] }}">
            <div class="w-full rounded-t" style="height:{{ max(4,$h['score']) }}%;background:{{ $hc }}"></div>
            <span class="text-[9px] text-gray-400">{{ \Illuminate\Support\Carbon::parse($h['date'])->format('d/m') }}</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
      {{-- Issue breakdown --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="font-bold text-gray-800 mb-4">Top issues</div>
        @if(count($report['issue_counts']))
          <div class="space-y-2.5">
            @foreach($report['issue_counts'] as $type => $n)
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-700">{{ $type }}</span>
                <span class="font-bold text-gray-800 bg-gray-100 rounded-full px-2.5 py-0.5 text-xs">{{ $n }}</span>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-sm text-green-600 font-semibold"><i class="fa-solid fa-circle-check"></i> No on-page issues found.</p>
        @endif
      </div>

      {{-- Recommendations --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="font-bold text-gray-800 mb-4">What to fix first</div>
        @if(count($report['recommendations']))
          <ul class="space-y-2">
            @foreach($report['recommendations'] as $rec)
              <li class="flex items-start gap-2 text-sm text-gray-700"><i class="fa-solid fa-arrow-right text-indigo-500 mt-1 text-xs"></i> {{ ucfirst($rec) }}</li>
            @endforeach
          </ul>
        @else
          <p class="text-sm text-green-600 font-semibold"><i class="fa-solid fa-circle-check"></i> Everything looks healthy!</p>
        @endif
      </div>
    </div>

    {{-- Pages table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 overflow-x-auto">
      <div class="font-bold text-gray-800 mb-4">Page audit</div>
      <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-400 border-b border-gray-100">
          <th class="py-2 pr-3 font-semibold">Page</th><th class="py-2 px-2 font-semibold">Score</th>
          <th class="py-2 px-2 font-semibold">Title</th><th class="py-2 px-2 font-semibold">Meta</th>
          <th class="py-2 px-2 font-semibold">H1</th><th class="py-2 pl-2 font-semibold">Issues</th>
        </tr></thead>
        <tbody>
          @foreach($report['pages'] as $p)
            @php [$pc,$pcb] = $scoreColor($p['score']); @endphp
            <tr class="border-b border-gray-50">
              <td class="py-2.5 pr-3"><div class="font-semibold text-gray-800">{{ $p['label'] }}</div><div class="text-[11px] text-gray-400 truncate max-w-[220px]">{{ $p['url'] }}</div></td>
              <td class="py-2.5 px-2"><span class="font-bold px-2 py-0.5 rounded-full text-xs" style="color:{{ $pc }};background:{{ $pcb }}">{{ $p['status']==200 ? $p['score'] : '—' }}</span></td>
              <td class="py-2.5 px-2 text-gray-600">{{ $p['title_len'] ?: '—' }}</td>
              <td class="py-2.5 px-2 text-gray-600">{{ $p['desc_len'] ?: '—' }}</td>
              <td class="py-2.5 px-2 text-gray-600">{{ $p['h1'] }}</td>
              <td class="py-2.5 pl-2">
                @if(count($p['issues']))
                  <span class="text-xs text-gray-500">{{ collect($p['issues'])->pluck('type')->take(2)->implode(', ') }}@if(count($p['issues'])>2) +{{ count($p['issues'])-2 }}@endif</span>
                @else <span class="text-xs text-green-600 font-semibold">Clean</span> @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Catalog --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="font-bold text-gray-800 mb-4">Product catalog SEO</div>
      <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-2xl font-extrabold text-gray-800">{{ $report['catalog']['missing_desc'] }}</div><div class="text-xs text-gray-500 mt-0.5">no / short description</div></div>
        <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-2xl font-extrabold text-gray-800">{{ $report['catalog']['missing_image'] }}</div><div class="text-xs text-gray-500 mt-0.5">no image</div></div>
        <div class="bg-gray-50 rounded-lg p-3 text-center"><div class="text-2xl font-extrabold text-gray-800">{{ $report['catalog']['missing_slug'] }}</div><div class="text-xs text-gray-500 mt-0.5">no slug</div></div>
      </div>
      @if(count($report['catalog']['examples']))
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Products to fix</div>
        <div class="space-y-1.5">
          @foreach($report['catalog']['examples'] as $ex)
            <div class="flex items-center justify-between text-sm border border-gray-100 rounded-lg px-3 py-2">
              <span class="text-gray-800 font-medium truncate">{{ $ex['name'] }}</span>
              <span class="flex items-center gap-2">
                <span class="text-[11px] text-amber-600">{{ implode(', ', $ex['issues']) }}</span>
                @if($ex['id'])<a href="{{ route('admin.products.edit', $ex['id']) }}" class="text-indigo-600 text-xs font-semibold hover:underline">Edit</a>@endif
              </span>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-sm text-green-600 font-semibold"><i class="fa-solid fa-circle-check"></i> Every product has a description, image and slug.</p>
      @endif
    </div>
  @endif

  {{-- Automation note --}}
  <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-sm text-indigo-900">
    <div class="font-bold mb-1"><i class="fa-solid fa-robot"></i> Daily automation</div>
    <p class="mb-2">This audit runs automatically every day at 3:00 AM once the Laravel scheduler is active on your server. Add this single cron job in Hostinger (hPanel → Advanced → Cron Jobs), set to run <b>every minute</b>:</p>
    <code class="block bg-white border border-indigo-100 rounded-lg px-3 py-2 text-indigo-700 text-xs overflow-x-auto">* * * * * cd /home/USER/domains/zelarillc.com/public_html && php artisan schedule:run >> /dev/null 2>&1</code>
    <p class="mt-2 text-indigo-700/80 text-xs">Until then, you can always press “Run audit now”. The report keeps 30 days of history for the trend.</p>
  </div>
</div>
@endsection
