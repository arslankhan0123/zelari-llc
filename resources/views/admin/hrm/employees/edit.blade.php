@extends('layouts.admin')
@section('title', 'Edit Employee')
@section('breadcrumb', 'HRM · Edit employee')

@section('content')
@php $cur = \App\Models\HrmSetting::get('hrm_currency', 'PKR'); @endphp
<div class="w-full">
  <a href="{{ route('admin.hrm.employees.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 inline-block mb-4"><i class="fa-solid fa-arrow-left mr-1"></i> All employees</a>

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-4 mb-5">
      <img src="{{ $employee->photo ? asset('storage/'.ltrim($employee->photo,'/')) : 'https://ui-avatars.com/api/?name='.urlencode($employee->name).'&background=6366f1&color=fff' }}" class="w-16 h-16 rounded-full object-cover border border-gray-200">
      <div><div class="font-bold text-gray-800 text-lg">{{ $employee->name }}</div><div class="text-xs text-gray-400">{{ $employee->code }}</div></div>
    </div>
    <form method="POST" action="{{ route('admin.hrm.employees.update', $employee) }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
      @csrf @method('PUT')
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Name *</label><input name="name" value="{{ old('name',$employee->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Code</label><input name="code" value="{{ old('code',$employee->code) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Designation</label><input name="designation" value="{{ old('designation',$employee->designation) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Department</label>
        <select name="department_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id',$employee->department_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Email</label><input name="email" type="email" value="{{ old('email',$employee->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Phone</label><input name="phone" value="{{ old('phone',$employee->phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Employment type</label>
        <select name="employment_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)<option value="{{ $v }}" {{ old('employment_type',$employee->employment_type)==$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Join date</label><input name="join_date" type="date" value="{{ old('join_date', optional($employee->join_date)->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Base salary ({{ $cur }})</label><input name="base_salary" type="number" step="0.01" min="0" value="{{ old('base_salary',$employee->base_salary) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['active'=>'Active','on_leave'=>'On leave','terminated'=>'Terminated'] as $v=>$l)<option value="{{ $v }}" {{ old('status',$employee->status)==$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
      <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 mb-1">Address</label><textarea name="address" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">{{ old('address',$employee->address) }}</textarea></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Photo</label><input name="photo" type="file" accept="image/*" class="w-full text-sm"></div>
      <div class="md:col-span-2"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg">Save changes</button></div>
    </form>
  </div>
</div>
@endsection
