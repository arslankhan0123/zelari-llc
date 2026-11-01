@extends('layouts.admin')
@section('title', 'HR Settings')
@section('breadcrumb', 'HRM · Configuration')

@section('content')
<div class="w-full">
  @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-gear text-indigo-500 mr-1"></i> General</div>
    <form method="POST" action="{{ route('admin.hrm.settings.update') }}" class="grid md:grid-cols-2 gap-4">
      @csrf @method('PUT')
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Company name</label><input name="hrm_company_name" value="{{ $settings['hrm_company_name'] ?? '' }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Currency</label><input name="hrm_currency" value="{{ $settings['hrm_currency'] ?? 'PKR' }}" maxlength="10" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Working days</label><input name="hrm_work_days" value="{{ $settings['hrm_work_days'] ?? '' }}" placeholder="Mon,Tue,Wed,Thu,Fri" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Payroll cycle</label>
        <select name="hrm_payroll_cycle" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach(['monthly'=>'Monthly','biweekly'=>'Bi-weekly','weekly'=>'Weekly'] as $v=>$l)<option value="{{ $v }}" {{ ($settings['hrm_payroll_cycle']??'monthly')==$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Work start</label><input name="hrm_work_start" type="time" value="{{ $settings['hrm_work_start'] ?? '09:00' }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Work end</label><input name="hrm_work_end" type="time" value="{{ $settings['hrm_work_end'] ?? '18:00' }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div class="md:col-span-2"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg">Save settings</button></div>
    </form>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-plane text-indigo-500 mr-1"></i> Leave types</div>
    <form method="POST" action="{{ route('admin.hrm.settings.leavetype.store') }}" class="grid md:grid-cols-4 gap-3 mb-5">
      @csrf
      <input name="name" required placeholder="Leave type name" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <input name="annual_quota" type="number" min="0" max="365" value="0" placeholder="Annual quota (days)" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="paid" value="1" checked> Paid</label>
      <button class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold px-4 py-2 rounded-lg">Add type</button>
    </form>
    <div class="divide-y divide-gray-100">
      @foreach($leaveTypes as $t)
      <div class="flex items-center justify-between py-2.5 text-sm">
        <div><span class="font-semibold text-gray-800">{{ $t->name }}</span>
          <span class="text-[11px] text-gray-400 ml-2">{{ $t->annual_quota }} days/yr · {{ $t->paid ? 'Paid' : 'Unpaid' }}</span></div>
        <form method="POST" action="{{ route('admin.hrm.settings.leavetype.destroy',$t) }}" onsubmit="return confirm('Remove {{ $t->name }}?')">@csrf @method('DELETE')<button class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash text-xs"></i></button></form>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
