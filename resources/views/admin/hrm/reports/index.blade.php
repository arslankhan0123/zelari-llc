@extends('layouts.admin')
@section('title', 'HR Reports')
@section('breadcrumb', 'HRM · Analytics & exports')

@section('content')
@php $cur = \App\Models\HrmSetting::get('hrm_currency', 'PKR'); @endphp
<div class="w-full">

  <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <form method="GET" class="flex items-center gap-2">
      <label class="text-sm font-semibold text-gray-600">Month</label>
      <input name="month" type="month" value="{{ $month }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
    </form>
    @can('hrm.reports.view')
    <a href="{{ route('admin.hrm.reports.export') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg"><i class="fa-solid fa-download mr-1"></i> Export employees (CSV)</a>
    @endcan
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach([['Total',$headcount['total']],['Active',$headcount['active']],['On leave',$headcount['on_leave']],['Terminated',$headcount['terminated']]] as [$l,$v])
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"><div class="text-2xl font-extrabold text-gray-800">{{ $v }}</div><div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $l }}</div></div>
    @endforeach
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-3">By employment type</div>
      @forelse($byType as $type => $c)
        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0"><span class="text-gray-600">{{ ucfirst(str_replace('_',' ',$type)) }}</span><span class="font-semibold text-gray-800">{{ $c }}</span></div>
      @empty<p class="text-sm text-gray-400">No data.</p>@endforelse
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-3">Headcount by department</div>
      @forelse($byDept as $d)
        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0"><span class="text-gray-600">{{ $d->name }}</span><span class="font-semibold text-gray-800">{{ $d->employees_count }}</span></div>
      @empty<p class="text-sm text-gray-400">No departments.</p>@endforelse
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-3">Attendance — {{ $month }}</div>
      @forelse($attendance as $st => $c)
        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0"><span class="text-gray-600 capitalize">{{ str_replace('_',' ',$st) }}</span><span class="font-semibold text-gray-800">{{ $c }}</span></div>
      @empty<p class="text-sm text-gray-400">No attendance marked this month.</p>@endforelse
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="font-bold text-gray-800 mb-3">Payroll — {{ $month }}</div>
      <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50"><span class="text-gray-600">Payslips</span><span class="font-semibold text-gray-800">{{ $payroll['count'] }}</span></div>
      <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50"><span class="text-gray-600">Net payable</span><span class="font-semibold text-gray-800">{{ $cur }} {{ number_format($payroll['net']) }}</span></div>
      <div class="flex items-center justify-between py-1.5 text-sm"><span class="text-gray-600">Paid</span><span class="font-semibold text-emerald-600">{{ $cur }} {{ number_format($payroll['paid']) }}</span></div>
      <div class="mt-4 pt-3 border-t border-gray-100 font-bold text-gray-800">Leave requests</div>
      @forelse($leaveByStatus as $st => $c)
        <div class="flex items-center justify-between py-1.5 text-sm"><span class="text-gray-600 capitalize">{{ $st }}</span><span class="font-semibold text-gray-800">{{ $c }}</span></div>
      @empty<p class="text-sm text-gray-400 mt-1">No leave data.</p>@endforelse
    </div>
  </div>
</div>
@endsection
