@extends('layouts.admin')
@section('title', 'Employees')
@section('breadcrumb', 'HRM · Employee directory')

@section('content')
@php $cur = \App\Models\HrmSetting::get('hrm_currency', 'PKR'); @endphp
<div class="w-full" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <form method="GET" class="flex items-center gap-2">
      <input name="q" value="{{ request('q') }}" placeholder="Search name / code / email" class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-56">
      <select name="department" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <option value="">All departments</option>
        @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
      </select>
      <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <option value="">Any status</option>
        @foreach(['active','on_leave','terminated'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
      </select>
      <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg">Filter</button>
    </form>
    @can('hrm.employees.create')
    <button @click="showForm=!showForm" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg"><i class="fa-solid fa-user-plus mr-1"></i> Add employee</button>
    @endcan
  </div>

  @can('hrm.employees.create')
  <div x-show="showForm" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4">New employee</div>
    <form method="POST" action="{{ route('admin.hrm.employees.store') }}" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-4">
      @csrf
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Name *</label><input name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Code</label><input name="code" value="{{ old('code') }}" placeholder="Auto if blank" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Designation</label><input name="designation" value="{{ old('designation') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Email</label><input name="email" type="email" value="{{ old('email') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Phone</label><input name="phone" value="{{ old('phone') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Department</label>
        <select name="department_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Employment type</label>
        <select name="employment_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)<option value="{{ $v }}" {{ old('employment_type')==$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Join date</label><input name="join_date" type="date" value="{{ old('join_date') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Base salary ({{ $cur }})</label><input name="base_salary" type="number" step="0.01" min="0" value="{{ old('base_salary',0) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['active'=>'Active','on_leave'=>'On leave','terminated'=>'Terminated'] as $v=>$l)<option value="{{ $v }}" {{ old('status','active')==$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Photo</label><input name="photo" type="file" accept="image/*" class="w-full text-sm"></div>
      <div class="md:col-span-3"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Save employee</button></div>
    </form>
  </div>
  @endcan

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">All employees ({{ $employees->total() }})</div>
    @if($employees->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Employee</th>
            <th class="text-left font-bold px-5 py-3">Department</th>
            <th class="text-left font-bold px-5 py-3">Designation</th>
            <th class="text-left font-bold px-5 py-3">Type</th>
            <th class="text-right font-bold px-5 py-3">Salary</th>
            <th class="text-center font-bold px-5 py-3">Status</th>
            <th class="text-center font-bold px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($employees as $e)
          <tr>
            <td class="px-5 py-3">
              <div class="font-bold text-gray-800">{{ $e->name }}</div>
              <div class="text-[11px] text-gray-400">{{ $e->code }}@if($e->email) · {{ $e->email }}@endif</div>
            </td>
            <td class="px-5 py-3 text-gray-600">{{ optional($e->department)->name ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ $e->designation ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ ucfirst(str_replace('_',' ',$e->employment_type)) }}</td>
            <td class="px-5 py-3 text-right text-gray-700">{{ $cur }} {{ number_format($e->base_salary) }}</td>
            <td class="px-5 py-3 text-center">
              <span class="text-xs font-semibold capitalize px-2.5 py-1 rounded-full
                {{ $e->status==='active' ? 'bg-green-50 text-green-700 border border-green-100' : ($e->status==='terminated' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">{{ str_replace('_',' ',$e->status) }}</span>
            </td>
            <td class="px-5 py-3">
              <x-row-actions module="hrm.employees"
                :edit="route('admin.hrm.employees.edit', $e)"
                :delete="route('admin.hrm.employees.destroy', $e)"
                confirm="Delete employee {{ $e->name }}?" />
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-4">{{ $employees->links() }}</div>
    @else
    <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-users text-3xl mb-2"></i><p class="text-sm">No employees found.</p></div>
    @endif
  </div>
</div>
@endsection
