@extends('layouts.admin')
@section('title', 'My Attendance')
@section('breadcrumb', 'HRM · Clock in / out')

@section('content')
@php
  $clockedIn  = $attendance && $attendance->check_in;
  $clockedOut = $attendance && $attendance->check_out;
  $onBreak    = (bool) $openBreak;
  $breakMins  = $attendance ? $attendance->totalBreakMinutes() : 0;
@endphp
<div class="w-full max-w-3xl mx-auto">

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.06]" style="background:radial-gradient(circle at 25% 20%,#6366f1,transparent 42%),radial-gradient(circle at 80% 70%,#8b5cf6,transparent 45%)"></div>
    <div class="relative">
      <div class="text-sm font-semibold text-gray-400">{{ now()->format('l, d M Y') }}</div>
      <div class="text-5xl font-extrabold text-gray-800 mt-1 mb-1" x-data="{ t: '' }" x-init="t = new Date().toLocaleTimeString(); setInterval(()=>t=new Date().toLocaleTimeString(),1000)" x-text="t">{{ now()->format('h:i:s A') }}</div>
      <div class="text-gray-500 text-sm mb-6">{{ $employee->name }} · {{ $employee->code }}</div>

      {{-- Status pill --}}
      <div class="mb-7">
        @if($clockedOut)
          <span class="inline-flex items-center gap-2 text-sm font-bold text-gray-600 bg-gray-100 border border-gray-200 rounded-full px-4 py-2"><i class="fa-solid fa-circle-check"></i> Day complete</span>
        @elseif($onBreak)
          <span class="inline-flex items-center gap-2 text-sm font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-4 py-2"><i class="fa-solid fa-mug-hot"></i> On break</span>
        @elseif($clockedIn)
          <span class="inline-flex items-center gap-2 text-sm font-bold text-green-700 bg-green-50 border border-green-200 rounded-full px-4 py-2"><i class="fa-solid fa-circle-play"></i> Working</span>
        @else
          <span class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 bg-gray-50 border border-gray-200 rounded-full px-4 py-2"><i class="fa-solid fa-circle-dot"></i> Not clocked in</span>
        @endif
      </div>

      {{-- Buttons by state --}}
      <div class="flex items-center justify-center gap-3 flex-wrap">
        @if(!$clockedIn)
          {{-- State 1: not clocked in --}}
          <form method="POST" action="{{ route('admin.hrm.clock.in') }}">@csrf
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3.5 rounded-xl text-base"><i class="fa-solid fa-right-to-bracket mr-2"></i> Clock In</button>
          </form>
        @elseif(!$clockedOut)
          {{-- State 2/3: clocked in (working or on break) --}}
          @if($onBreak)
            <form method="POST" action="{{ route('admin.hrm.clock.breakend') }}">@csrf
              <button class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-7 py-3.5 rounded-xl text-base"><i class="fa-solid fa-circle-stop mr-2"></i> End Break</button>
            </form>
          @else
            <form method="POST" action="{{ route('admin.hrm.clock.breakstart') }}">@csrf
              <button class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-7 py-3.5 rounded-xl text-base"><i class="fa-solid fa-mug-hot mr-2"></i> Start Break</button>
            </form>
          @endif
          <form method="POST" action="{{ route('admin.hrm.clock.out') }}" onsubmit="return confirm('Clock out for today?')">@csrf
            <button class="bg-gray-800 hover:bg-gray-900 text-white font-bold px-7 py-3.5 rounded-xl text-base"><i class="fa-solid fa-right-from-bracket mr-2"></i> Clock Out</button>
          </form>
        @else
          {{-- State 4: clocked out --}}
          <span class="text-sm text-gray-500">You have clocked out for today. See you tomorrow!</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Today summary --}}
  @if($clockedIn)
  <div class="grid grid-cols-3 gap-3 mt-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
      <div class="text-lg font-bold text-gray-800">{{ \Illuminate\Support\Carbon::parse($attendance->check_in)->format('h:i A') }}</div>
      <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Clock in</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
      <div class="text-lg font-bold text-gray-800">{{ $attendance->check_out ? \Illuminate\Support\Carbon::parse($attendance->check_out)->format('h:i A') : '—' }}</div>
      <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Clock out</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
      <div class="text-lg font-bold text-gray-800">{{ $breakMins }} min</div>
      <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Break time</div>
    </div>
  </div>
  @endif

  {{-- Recent history --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-5">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">My recent attendance</div>
    @if($recent->count())
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
        <th class="text-left font-bold px-5 py-3">Date</th>
        <th class="text-left font-bold px-5 py-3">In</th>
        <th class="text-left font-bold px-5 py-3">Out</th>
        <th class="text-left font-bold px-5 py-3">Break</th>
        <th class="text-center font-bold px-5 py-3">Status</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($recent as $r)
        <tr>
          <td class="px-5 py-3 font-semibold text-gray-700">{{ $r->date->format('d M Y') }}</td>
          <td class="px-5 py-3 text-gray-600">{{ $r->check_in ? \Illuminate\Support\Carbon::parse($r->check_in)->format('h:i A') : '—' }}</td>
          <td class="px-5 py-3 text-gray-600">{{ $r->check_out ? \Illuminate\Support\Carbon::parse($r->check_out)->format('h:i A') : '—' }}</td>
          <td class="px-5 py-3 text-gray-600">{{ $r->totalBreakMinutes() }} min</td>
          <td class="px-5 py-3 text-center"><span class="text-xs font-semibold capitalize px-2.5 py-1 rounded-full bg-gray-50 text-gray-600 border border-gray-200">{{ str_replace('_',' ',$r->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="px-5 py-10 text-center text-gray-400 text-sm">No attendance records yet.</div>
    @endif
  </div>
</div>
@endsection
