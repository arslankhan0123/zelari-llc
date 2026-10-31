@extends('layouts.admin')
@section('title', 'HRM')
@section('breadcrumb', 'Human Resource Management')

@section('content')
<div class="max-w-6xl">

  <div class="bg-white rounded-2xl shadow-sm p-8 mb-6 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.07]" style="background:radial-gradient(circle at 20% 20%,#6366f1,transparent 40%),radial-gradient(circle at 80% 60%,#ec4899,transparent 45%)"></div>
    <div class="relative flex items-center gap-4">
      <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 00-3-3.87"/></svg>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">HRM Module</h1>
        <p class="text-gray-500 mt-1">Employee management, attendance, payroll &amp; more — coming soon.</p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @php
      $modules = [
        ['Employees','Directory, profiles &amp; roles','M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','#6366f1'],
        ['Attendance','Check-in / out &amp; shifts','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','#0ea5e9'],
        ['Payroll','Salaries, slips &amp; payouts','M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2','#16a34a'],
        ['Leave','Requests &amp; approvals','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','#d97706'],
        ['Departments','Teams &amp; hierarchy','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-14h2m-2 4h2m-2 4h2m6-8h2m-2 4h2','#8b5cf6'],
        ['Reports','HR analytics &amp; exports','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','#ec4899'],
      ];
    @endphp
    @foreach($modules as [$name,$desc,$icon,$color])
      <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 relative">
        <span class="absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">SOON</span>
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-3" style="background:{{ $color }}18;color:{{ $color }}">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </div>
        <h3 class="font-semibold text-gray-800">{{ $name }}</h3>
        <p class="text-sm text-gray-500 mt-1">{!! $desc !!}</p>
      </div>
    @endforeach
  </div>

</div>
@endsection
