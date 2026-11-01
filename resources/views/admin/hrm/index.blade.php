@extends('layouts.admin')
@section('title', 'HR Overview')
@section('breadcrumb', 'Human Resource Management')

@section('content')
@php $cur = \App\Models\HrmSetting::get('hrm_currency', 'PKR'); @endphp
<div class="w-full">

  <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.07]" style="background:radial-gradient(circle at 20% 20%,#6366f1,transparent 40%),radial-gradient(circle at 80% 60%,#8b5cf6,transparent 45%)"></div>
    <div class="relative flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-800">{{ \App\Models\HrmSetting::get('hrm_company_name', 'Company') }} — HR Overview</h1>
        <p class="text-gray-500 text-sm mt-0.5">Employees, attendance, leave, payroll and reports.</p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @foreach([
      ['Employees', $stats['employees'], 'admin.hrm.employees.index', false],
      ['Active', $stats['active'], null, false],
      ['Departments', $stats['departments'], 'admin.hrm.departments.index', false],
      ['Present today', $stats['present_today'], 'admin.hrm.attendance.index', false],
      ['Pending leave', $stats['pending_leave'], 'admin.hrm.leave.index', false],
      ['Payroll (month)', $stats['payroll_month'], 'admin.hrm.payroll.index', true],
    ] as [$label, $val, $route, $money])
      <a @if($route) href="{{ route($route) }}" @endif class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 block {{ $route ? 'hover:border-indigo-300 transition' : '' }}">
        <div class="text-lg font-extrabold text-gray-800">{{ $money ? $cur.' '.number_format($val) : number_format($val) }}</div>
        <div class="text-[11px] font-semibold text-gray-500 mt-0.5">{{ $label }}</div>
      </a>
    @endforeach
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800 flex items-center justify-between">
        Recent leave requests
        <a href="{{ route('admin.hrm.leave.index') }}" class="text-xs font-semibold text-indigo-600">View all</a>
      </div>
      @forelse($recentLeaves as $lv)
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between text-sm">
          <div>
            <span class="font-semibold text-gray-800">{{ optional($lv->employee)->name ?? 'Employee' }}</span>
            <span class="text-gray-400 text-xs ml-1">{{ optional($lv->type)->name }}</span>
            <div class="text-[11px] text-gray-400">{{ $lv->from_date->format('d M') }} – {{ $lv->to_date->format('d M') }} · {{ $lv->days }}d</div>
          </div>
          <span class="text-xs font-semibold capitalize px-2.5 py-1 rounded-full
            {{ $lv->status==='approved' ? 'bg-green-50 text-green-700 border border-green-100' : ($lv->status==='rejected' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">{{ $lv->status }}</span>
        </div>
      @empty
        <div class="px-5 py-10 text-center text-sm text-gray-400">No leave requests yet.</div>
      @endforelse
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Headcount by department</div>
      @forelse($byDept as $d)
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between text-sm">
          <span class="font-semibold text-gray-700">{{ $d->name }}</span>
          <span class="text-gray-500">{{ $d->employees_count }} {{ \Illuminate\Support\Str::plural('person', $d->employees_count) }}</span>
        </div>
      @empty
        <div class="px-5 py-10 text-center text-sm text-gray-400">No departments yet.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
