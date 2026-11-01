@extends('layouts.admin')
@section('title', 'Payroll')
@section('breadcrumb', 'HRM · Salaries & payslips')

@section('content')
@php $cur = \App\Models\HrmSetting::get('hrm_currency', 'PKR'); $m = fn($v)=>$cur.' '.number_format($v); @endphp
<div class="w-full" x-data="{ edit: null }">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <form method="GET" class="flex items-center gap-2">
      <label class="text-sm font-semibold text-gray-600">Period</label>
      <input name="period" type="month" value="{{ $period }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
    </form>
    @can('hrm.payroll.edit')
    <form method="POST" action="{{ route('admin.hrm.payroll.generate') }}">@csrf
      <input type="hidden" name="period" value="{{ $period }}">
      <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Generate payslips for {{ $period }}</button>
    </form>
    @endcan
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach([['Payslips',$totals['count'],false],['Gross',$totals['gross'],true],['Deductions',$totals['deductions'],true],['Net payable',$totals['net'],true]] as [$l,$v,$mon])
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"><div class="text-lg font-extrabold text-gray-800">{{ $mon ? $m($v) : number_format($v) }}</div><div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $l }}</div></div>
    @endforeach
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Payslips — {{ $period }}</div>
    @if($payslips->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Employee</th>
            <th class="text-right font-bold px-5 py-3">Basic</th>
            <th class="text-right font-bold px-5 py-3">Allowances</th>
            <th class="text-right font-bold px-5 py-3">Deductions</th>
            <th class="text-right font-bold px-5 py-3">Net</th>
            <th class="text-center font-bold px-5 py-3">Status</th>
            <th class="text-right font-bold px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($payslips as $p)
          <tr>
            <td class="px-5 py-3 font-semibold text-gray-800">{{ optional($p->employee)->name ?? '—' }}</td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $m($p->basic) }}</td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $m($p->allowances) }}</td>
            <td class="px-5 py-3 text-right text-gray-600">{{ $m($p->deductions) }}</td>
            <td class="px-5 py-3 text-right font-bold text-gray-800">{{ $m($p->net) }}</td>
            <td class="px-5 py-3 text-center">
              @if($p->status==='paid')<span class="text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-full px-2.5 py-1">Paid</span>
              @else<span class="text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1">Draft</span>@endif
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              @can('hrm.payroll.edit')
              <button @click="edit = (edit==={{ $p->id }} ? null : {{ $p->id }})" class="text-indigo-600 hover:text-indigo-800 mr-2" title="Edit"><i class="fa-solid fa-pen"></i></button>
              @if($p->status!=='paid')
              <form method="POST" action="{{ route('admin.hrm.payroll.paid',$p) }}" class="inline">@csrf<button class="text-green-600 hover:text-green-800 mr-2" title="Mark paid"><i class="fa-solid fa-money-check-dollar"></i></button></form>
              @endif
              @endcan
              @can('hrm.payroll.delete')
              <form method="POST" action="{{ route('admin.hrm.payroll.destroy',$p) }}" onsubmit="return confirm('Delete this payslip?')" class="inline">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              @endcan
            </td>
          </tr>
          @can('hrm.payroll.edit')
          <tr x-show="edit==={{ $p->id }}" x-cloak><td colspan="7" class="px-5 py-3 bg-gray-50">
            <form method="POST" action="{{ route('admin.hrm.payroll.update',$p) }}" class="grid md:grid-cols-4 gap-3">
              @csrf @method('PUT')
              <div><label class="block text-[11px] font-bold text-gray-500 mb-1">Basic</label><input name="basic" type="number" step="0.01" min="0" value="{{ $p->basic }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
              <div><label class="block text-[11px] font-bold text-gray-500 mb-1">Allowances</label><input name="allowances" type="number" step="0.01" min="0" value="{{ $p->allowances }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
              <div><label class="block text-[11px] font-bold text-gray-500 mb-1">Deductions</label><input name="deductions" type="number" step="0.01" min="0" value="{{ $p->deductions }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
              <div class="flex items-end"><button class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg">Save</button></div>
            </form>
          </td></tr>
          @endcan
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-money-check-dollar text-3xl mb-2"></i><p class="text-sm">No payslips for {{ $period }}. Use “Generate payslips”.</p></div>
    @endif
  </div>
</div>
@endsection
