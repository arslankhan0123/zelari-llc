@extends('layouts.admin')
@section('title', 'Dashboard')
@section('breadcrumb', 'Your workspace overview')

@php
  $h = now()->hour;
  $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
  $name = explode(' ', trim(Auth::user()->name ?? 'Admin'))[0];
  $maxRev = max($monthly->max('val') ?? 0, 1);
@endphp

@section('content')
<div class="max-w-7xl mx-auto">

  {{-- Hero --}}
  <div class="relative overflow-hidden rounded-3xl p-7 sm:p-9 mb-6 text-white" style="background:linear-gradient(120deg,#0b0820 0%,#241b5e 45%,#6d28d9 100%)">
    {{-- decorative layers --}}
    <div class="absolute inset-0" style="background-image:radial-gradient(rgba(255,255,255,.10) 1px,transparent 1px);background-size:22px 22px;opacity:.5"></div>
    <div class="absolute -top-16 -right-10 w-72 h-72 rounded-full" style="background:radial-gradient(circle,rgba(236,72,153,.45),transparent 65%);filter:blur(10px)"></div>
    <div class="absolute -bottom-20 left-1/4 w-72 h-72 rounded-full" style="background:radial-gradient(circle,rgba(99,102,241,.5),transparent 65%);filter:blur(10px)"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div>
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur text-xs font-medium text-white/80 border border-white/10">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          {{ now()->format('l, d M Y') }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mt-4 tracking-tight">{{ $greet }}, {{ $name }} <span class="inline-block">👋</span></h1>
        <p class="text-white/70 mt-2 text-sm sm:text-base max-w-md">Welcome back to your Zelari control center. Here's how things are looking today.</p>
      </div>

      {{-- glass snapshot --}}
      <div class="rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 p-5 w-full lg:w-72 flex-shrink-0">
        <p class="text-white/60 text-xs font-medium uppercase tracking-widest">Today's sales</p>
        <p class="text-3xl font-extrabold mt-1">{{ zprice($s['salesToday']) }}</p>
        <div class="grid grid-cols-2 gap-3 mt-4">
          <div class="rounded-xl bg-white/10 p-3">
            <p class="text-xl font-bold leading-none">{{ $s['pending'] }}</p>
            <p class="text-[11px] text-white/60 mt-1">Pending</p>
          </div>
          <div class="rounded-xl bg-white/10 p-3">
            <p class="text-xl font-bold leading-none">{{ $s['messages'] }}</p>
            <p class="text-[11px] text-white/60 mt-1">Messages</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Workspace cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
    {{-- Ecommerce --}}
    <a href="{{ route('admin.dashboard') }}" class="group relative overflow-hidden rounded-2xl p-6 text-white transition hover:-translate-y-0.5" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
      <div class="absolute -top-8 -right-8 w-36 h-36 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
      <div class="relative">
        <div class="flex items-center justify-between">
          <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <svg class="w-5 h-5 opacity-70 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </div>
        <h3 class="text-xl font-bold mt-4">Ecommerce</h3>
        <p class="text-white/75 text-sm mt-1">Products, orders, customers, marketing &amp; SEO.</p>
        <div class="flex gap-4 mt-4 text-sm">
          <span><b class="font-bold">{{ number_format($s['orders']) }}</b> <span class="text-white/60">orders</span></span>
          <span><b class="font-bold">{{ number_format($s['products']) }}</b> <span class="text-white/60">products</span></span>
          <span><b class="font-bold">{{ number_format($s['customers']) }}</b> <span class="text-white/60">customers</span></span>
        </div>
      </div>
    </a>

    {{-- HRM --}}
    <a href="{{ route('admin.hrm.index') }}" class="group relative overflow-hidden rounded-2xl p-6 text-white transition hover:-translate-y-0.5" style="background:linear-gradient(135deg,#ec4899,#f43f5e)">
      <div class="absolute -top-8 -right-8 w-36 h-36 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
      <div class="relative">
        <div class="flex items-center justify-between">
          <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
          </div>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20">SOON</span>
        </div>
        <h3 class="text-xl font-bold mt-4">HRM</h3>
        <p class="text-white/75 text-sm mt-1">Employees, attendance, payroll &amp; leave.</p>
        <div class="flex gap-4 mt-4 text-sm text-white/70">
          <span>Employees</span><span>·</span><span>Attendance</span><span>·</span><span>Payroll</span>
        </div>
      </div>
    </a>
  </div>

  {{-- Stat tiles --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
      $tiles = [
        ['Revenue', zprice($s['revenue']), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2', '#6366f1', zprice($s['revenueMonth']).' this month'],
        ['Orders', number_format($s['orders']), 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', '#0ea5e9', $s['ordersMonth'].' this month'],
        ['Customers', number_format($s['customers']), 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', '#16a34a', 'Registered'],
        ['Products', number_format($s['products']), 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', '#f59e0b', $s['lowStock'].' low in stock'],
      ];
    @endphp
    @foreach($tiles as [$label,$val,$icon,$color,$sub])
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <span class="text-gray-500 text-sm font-medium">{{ $label }}</span>
          <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:{{ $color }}18;color:{{ $color }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-3">{{ $val }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $sub }}</p>
      </div>
    @endforeach
  </div>

  {{-- Main grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: chart + recent orders --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Revenue trend --}}
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h3 class="font-bold text-gray-800">Revenue trend</h3>
            <p class="text-xs text-gray-400">Last 12 months</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">{{ zprice($s['revenue']) }} total</span>
        </div>
        <div class="flex items-end gap-1.5 h-40">
          @foreach($monthly as $m)
            @php $pct = (int) round(($m['val'] / $maxRev) * 100); @endphp
            <div class="flex-1 flex flex-col items-center gap-2 group">
              <div class="w-full rounded-t-md relative" style="height:{{ max($pct,3) }}%;min-height:4px;background:linear-gradient(180deg,#8b5cf6,#6366f1)">
                <span class="absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-semibold bg-gray-900 text-white px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">{{ zprice($m['val']) }}</span>
              </div>
              <span class="text-[10px] text-gray-400">{{ $m['label'] }}</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Recent orders --}}
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-gray-800">Recent orders</h3>
          <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-gray-100">
          @forelse($recent as $o)
            @php $cn = $o->customer->name ?? $o->shipping_name ?? 'Guest'; @endphp
            <a href="{{ route('admin.orders.show', $o) }}" class="flex items-center gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
              <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">{{ strtoupper(substr($cn,0,1)) }}</div>
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-sm text-gray-800 truncate">{{ $cn }}</div>
                <div class="text-[11px] text-gray-400 font-mono">{{ $o->order_number }} · {{ $o->created_at->diffForHumans() }}</div>
              </div>
              <div class="text-right flex-shrink-0">
                <div class="font-bold text-sm text-gray-800">{{ $o->money($o->total) }}</div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ ucfirst($o->status) }}</span>
              </div>
            </a>
          @empty
            <p class="text-center text-gray-400 py-10 text-sm">No orders yet.</p>
          @endforelse
        </div>
      </div>

    </div>

    {{-- Right: alerts + workspaces --}}
    <div class="space-y-6">

      {{-- Needs attention --}}
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4">Needs attention</h3>
        <div class="space-y-2.5">
          @php
            $alerts = [
              ['Pending orders', $s['pending'], 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', '#d97706', route('admin.orders.index')],
              ['Unpaid orders', $s['unpaid'], 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', '#dc2626', route('admin.orders.index')],
              ['Low stock items', $s['lowStock'], 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', '#f59e0b', route('admin.products.index')],
              ['New messages', $s['messages'], 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', '#6366f1', route('admin.messages.index')],
            ];
          @endphp
          @foreach($alerts as [$label,$count,$icon,$color,$url])
            <a href="{{ $url }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition">
              <span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:{{ $color }}18;color:{{ $color }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
              </span>
              <span class="flex-1 text-sm font-medium text-gray-700">{{ $label }}</span>
              <span class="text-sm font-bold {{ $count > 0 ? 'text-gray-800' : 'text-gray-300' }}">{{ $count }}</span>
            </a>
          @endforeach
        </div>
      </div>

      {{-- Quick actions --}}
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4">Quick actions</h3>
        <div class="grid grid-cols-2 gap-3">
          @php
            $qa = [
              ['Add Product', route('admin.products.index'), 'M12 4v16m8-8H4', '#6366f1'],
              ['Marketing', route('admin.marketing.index'), 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', '#ec4899'],
              ['SEO Report', route('admin.seo.index'), 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', '#16a34a'],
              ['Live Chat', route('admin.chats.index'), 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', '#0ea5e9'],
            ];
          @endphp
          @foreach($qa as [$label,$url,$icon,$color])
            <a href="{{ $url }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md border border-transparent hover:border-gray-100 transition text-center">
              <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $color }}18;color:{{ $color }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
              </span>
              <span class="text-xs font-semibold text-gray-700">{{ $label }}</span>
            </a>
          @endforeach
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
