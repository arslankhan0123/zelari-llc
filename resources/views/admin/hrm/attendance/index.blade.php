@extends('layouts.admin')
@section('title', 'Attendance')
@section('breadcrumb', 'HRM · Daily attendance')

@section('content')
<div class="w-full">

  <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <form method="GET" class="flex items-center gap-2">
      <label class="text-sm font-semibold text-gray-600">Date</label>
      <input name="date" type="date" value="{{ $date }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
    </form>
    <div class="flex gap-2 text-xs">
      <span class="px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-100 font-semibold">Present {{ $summary['present'] }}</span>
      <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-600 border border-red-100 font-semibold">Absent {{ $summary['absent'] }}</span>
      <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100 font-semibold">Leave {{ $summary['leave'] }}</span>
      <span class="px-2.5 py-1 rounded-full bg-gray-50 text-gray-600 border border-gray-200 font-semibold">Total {{ $summary['total'] }}</span>
    </div>
  </div>

  @can('hrm.attendance.edit')
  <form method="POST" action="{{ route('admin.hrm.attendance.save') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
  @endcan
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800 flex items-center justify-between">
        <span>Attendance — {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</span>
        @can('hrm.attendance.edit')<button type="button" onclick="document.querySelectorAll('select[data-att]').forEach(s=>s.value='present')" class="text-xs font-semibold text-indigo-600">Mark all present</button>@endcan
      </div>
      @if($employees->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="text-left font-bold px-5 py-3">Employee</th>
              <th class="text-left font-bold px-5 py-3">Status</th>
              <th class="text-left font-bold px-5 py-3">Check-in</th>
              <th class="text-left font-bold px-5 py-3">Check-out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($employees as $e)
            @php $rec = $records[$e->id] ?? null; @endphp
            <tr>
              <td class="px-5 py-2.5"><span class="font-semibold text-gray-800">{{ $e->name }}</span><span class="text-[11px] text-gray-400 ml-1">{{ $e->code }}</span></td>
              <td class="px-5 py-2.5">
                <select name="rows[{{ $e->id }}][status]" data-att class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm" {{ auth()->user()->can('hrm.attendance.edit') ? '' : 'disabled' }}>
                  <option value="">—</option>
                  @foreach(['present'=>'Present','absent'=>'Absent','leave'=>'Leave','half_day'=>'Half day','holiday'=>'Holiday'] as $v=>$l)
                    <option value="{{ $v }}" {{ optional($rec)->status==$v?'selected':'' }}>{{ $l }}</option>
                  @endforeach
                </select>
              </td>
              <td class="px-5 py-2.5"><input name="rows[{{ $e->id }}][check_in]" type="time" value="{{ optional($rec)->check_in }}" class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm" {{ auth()->user()->can('hrm.attendance.edit') ? '' : 'disabled' }}></td>
              <td class="px-5 py-2.5"><input name="rows[{{ $e->id }}][check_out]" type="time" value="{{ optional($rec)->check_out }}" class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm" {{ auth()->user()->can('hrm.attendance.edit') ? '' : 'disabled' }}></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @can('hrm.attendance.edit')
      <div class="px-5 py-4 border-t border-gray-100"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Save attendance</button></div>
      @endcan
      @else
      <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-user-clock text-3xl mb-2"></i><p class="text-sm">No employees to mark. Add employees first.</p></div>
      @endif
    </div>
  @can('hrm.attendance.edit')
  </form>
  @endcan
</div>
@endsection
