@extends('layouts.admin')
@section('title', 'Leave')
@section('breadcrumb', 'HRM · Leave requests & approvals')

@section('content')
<div class="w-full">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="grid grid-cols-3 gap-3 mb-6">
    @foreach([['Pending',$counts['pending'],'amber','pending'],['Approved',$counts['approved'],'green','approved'],['Rejected',$counts['rejected'],'red','rejected']] as [$l,$v,$c,$st])
      <a href="{{ route('admin.hrm.leave.index',['status'=>$st]) }}" class="bg-white rounded-xl shadow-sm border {{ request('status')==$st ? 'border-indigo-300' : 'border-gray-100' }} p-4 block">
        <div class="text-2xl font-extrabold text-{{ $c }}-600">{{ $v }}</div>
        <div class="text-xs font-semibold text-gray-500 mt-0.5">{{ $l }}</div>
      </a>
    @endforeach
  </div>

  @can('hrm.leave.create')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-calendar-plus text-indigo-500 mr-1"></i> New leave request</div>
    <form method="POST" action="{{ route('admin.hrm.leave.store') }}" class="grid md:grid-cols-5 gap-4">
      @csrf
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Employee *</label>
        <select name="employee_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Type</label>
        <select name="leave_type_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($types as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">From *</label><input name="from_date" type="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">To *</label><input name="to_date" type="date" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div class="flex items-end"><button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg">Add</button></div>
      <div class="md:col-span-5"><label class="block text-xs font-bold text-gray-500 mb-1">Reason</label><input name="reason" maxlength="500" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
    </form>
  </div>
  @endcan

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Leave requests</div>
    @if($leaves->count())
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Employee</th>
            <th class="text-left font-bold px-5 py-3">Type</th>
            <th class="text-left font-bold px-5 py-3">Dates</th>
            <th class="text-center font-bold px-5 py-3">Days</th>
            <th class="text-center font-bold px-5 py-3">Status</th>
            <th class="text-right font-bold px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($leaves as $lv)
          <tr>
            <td class="px-5 py-3 font-semibold text-gray-800">{{ optional($lv->employee)->name ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ optional($lv->type)->name ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600">{{ $lv->from_date->format('d M') }} – {{ $lv->to_date->format('d M Y') }}</td>
            <td class="px-5 py-3 text-center text-gray-600">{{ $lv->days }}</td>
            <td class="px-5 py-3 text-center">
              <span class="text-xs font-semibold capitalize px-2.5 py-1 rounded-full
                {{ $lv->status==='approved' ? 'bg-green-50 text-green-700 border border-green-100' : ($lv->status==='rejected' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">{{ $lv->status }}</span>
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              @can('hrm.leave.edit')
              @if($lv->status==='pending')
                <form method="POST" action="{{ route('admin.hrm.leave.approve',$lv) }}" class="inline">@csrf<button class="text-green-600 hover:text-green-800 mr-2" title="Approve"><i class="fa-solid fa-check"></i></button></form>
                <form method="POST" action="{{ route('admin.hrm.leave.reject',$lv) }}" class="inline">@csrf<button class="text-red-500 hover:text-red-700 mr-2" title="Reject"><i class="fa-solid fa-xmark"></i></button></form>
              @endif
              @endcan
              @can('hrm.leave.delete')
              <form method="POST" action="{{ route('admin.hrm.leave.destroy',$lv) }}" onsubmit="return confirm('Delete this request?')" class="inline">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              @endcan
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-4">{{ $leaves->links() }}</div>
    @else
    <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-plane-departure text-3xl mb-2"></i><p class="text-sm">No leave requests.</p></div>
    @endif
  </div>
</div>
@endsection
