@extends('layouts.admin')
@section('title', 'Dashboard')
@section('breadcrumb', 'Your workspace overview')

@php
  $h = now()->hour;
  $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
  $name = explode(' ', trim(Auth::user()->name ?? 'Admin'))[0];
@endphp

@section('content')
<div class="max-w-6xl mx-auto">

  {{-- Hero --}}
  <div class="relative overflow-hidden rounded-3xl p-8 sm:p-10 mb-7 text-white"
       style="background:linear-gradient(135deg,#0f0c29 0%,#302b63 55%,#5b21b6 100%)">
    <div class="absolute inset-0 opacity-60" style="background:radial-gradient(circle at 85% 15%,rgba(236,72,153,.35),transparent 45%),radial-gradient(circle at 10% 90%,rgba(99,102,241,.4),transparent 45%)"></div>
    <div class="relative">
      <p class="text-white/70 text-sm font-medium">{{ now()->format('l, d M Y') }}</p>
      <h1 class="text-3xl sm:text-4xl font-extrabold mt-2">{{ $greet }}, {{ $name }} 👋</h1>
      <p class="text-white/70 mt-2 max-w-lg">Here's a quick look at your store. Pick a workspace below to dive in.</p>
    </div>
  </div>

  {{-- Stat tiles --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    @php
      $tiles = [
        ['Total Revenue', zprice($s['revenue']), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2', '#6366f1', $s['pending'].' pending orders'],
        ['Orders', number_format($s['orders']), 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', '#0ea5e9', $s['pending'].' awaiting action'],
        ['Customers', number_format($s['customers']), 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z', '#16a34a', 'Total registered'],
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

  {{-- Workspaces --}}
  <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">Workspaces</h2>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Ecommerce --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-40 h-40 opacity-10" style="background:radial-gradient(circle at 70% 30%,#6366f1,transparent 60%)"></div>
      <div class="relative">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-800">Ecommerce</h3>
            <p class="text-gray-500 text-sm mt-1">Products, orders, customers, marketing, SEO &amp; store tools.</p>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-5">
          <a href="{{ route('admin.orders.index') }}" class="text-center py-3 rounded-xl bg-gray-50 hover:bg-indigo-50 transition">
            <div class="text-lg font-bold text-gray-800">{{ number_format($s['orders']) }}</div>
            <div class="text-[11px] text-gray-400">Orders</div>
          </a>
          <a href="{{ route('admin.products.index') }}" class="text-center py-3 rounded-xl bg-gray-50 hover:bg-indigo-50 transition">
            <div class="text-lg font-bold text-gray-800">{{ number_format($s['products']) }}</div>
            <div class="text-[11px] text-gray-400">Products</div>
          </a>
          <a href="{{ route('admin.customers.index') }}" class="text-center py-3 rounded-xl bg-gray-50 hover:bg-indigo-50 transition">
            <div class="text-lg font-bold text-gray-800">{{ number_format($s['customers']) }}</div>
            <div class="text-[11px] text-gray-400">Customers</div>
          </a>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 mt-5 px-4 py-2.5 rounded-xl text-white font-semibold text-sm hover:gap-2.5 transition-all" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          Open store panel
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
      </div>
    </div>

    {{-- HRM --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-40 h-40 opacity-10" style="background:radial-gradient(circle at 70% 30%,#ec4899,transparent 60%)"></div>
      <div class="relative">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white flex-shrink-0" style="background:linear-gradient(135deg,#ec4899,#f43f5e)">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">HRM <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">SOON</span></h3>
            <p class="text-gray-500 text-sm mt-1">Employees, attendance, payroll &amp; leave management.</p>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-5">
          @foreach(['Employees','Attendance','Payroll'] as $m)
            <div class="text-center py-3 rounded-xl bg-gray-50 text-gray-300">
              <div class="text-lg font-bold">—</div>
              <div class="text-[11px]">{{ $m }}</div>
            </div>
          @endforeach
        </div>
        <a href="{{ route('admin.hrm.index') }}" class="inline-flex items-center gap-1.5 mt-5 px-4 py-2.5 rounded-xl text-white font-semibold text-sm hover:gap-2.5 transition-all" style="background:linear-gradient(135deg,#ec4899,#f43f5e)">
          Open HR panel
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
      </div>
    </div>

  </div>

  {{-- Quick actions --}}
  <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3 mt-8">Quick actions</h2>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    @php
      $qa = [
        ['Add Product', route('admin.products.index'), 'M12 4v16m8-8H4', '#6366f1'],
        ['View Orders', route('admin.orders.index'), 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', '#0ea5e9'],
        ['Marketing', route('admin.marketing.index'), 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', '#ec4899'],
        ['SEO Report', route('admin.seo.index'), 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', '#16a34a'],
      ];
    @endphp
    @foreach($qa as [$label,$url,$icon,$color])
      <a href="{{ $url }}" class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-0.5 transition">
        <span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:{{ $color }}18;color:{{ $color }}">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </span>
        <span class="font-semibold text-gray-700 text-sm">{{ $label }}</span>
      </a>
    @endforeach
  </div>

</div>
@endsection
