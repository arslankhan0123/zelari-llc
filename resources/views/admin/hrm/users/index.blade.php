@extends('layouts.admin')
@section('title', 'HR Users')
@section('breadcrumb', 'HRM · Who can access HRM')

@section('content')
<div class="w-full">

  @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

  <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm rounded-xl p-4 mb-6">
    <i class="fa-solid fa-circle-info mr-1"></i>
    HRM access is separate from the ecommerce panel. Grant an existing login account an HR role here; that decides what they can do inside HRM. The master admin always has full access.
  </div>

  @can('hrm.users.create')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-user-shield text-indigo-500 mr-1"></i> Grant HRM access</div>
    <form method="POST" action="{{ route('admin.hrm.users.store') }}" class="grid md:grid-cols-3 gap-4">
      @csrf
      <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 mb-1">Account email *</label><input name="email" type="email" value="{{ old('email') }}" required placeholder="person@email.com" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">HR role *</label>
        <select name="hrm_role_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          @foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
        </select></div>
      <div class="md:col-span-3"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Grant access</button></div>
    </form>
  </div>
  @endcan

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">People with HRM access</div>
    @if($users->count())
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
        <th class="text-left font-bold px-5 py-3">User</th><th class="text-left font-bold px-5 py-3">HR Role</th><th class="text-right font-bold px-5 py-3">Action</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($users as $u)
        <tr>
          <td class="px-5 py-3"><span class="font-semibold text-gray-800">{{ $u->name }}</span><div class="text-[11px] text-gray-400">{{ $u->email }}</div></td>
          <td class="px-5 py-3">
            @can('hrm.users.edit')
            <form method="POST" action="{{ route('admin.hrm.users.update',$u) }}" class="flex items-center gap-2">@csrf @method('PUT')
              <select name="hrm_role_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm">
                @foreach($roles as $r)<option value="{{ $r->id }}" {{ $u->hrm_role_id==$r->id?'selected':'' }}>{{ $r->name }}</option>@endforeach
              </select>
            </form>
            @else {{ optional($u->hrmRole)->name }} @endcan
          </td>
          <td class="px-5 py-3 text-right">
            @can('hrm.users.delete')
            <form method="POST" action="{{ route('admin.hrm.users.destroy',$u) }}" onsubmit="return confirm('Revoke {{ $u->name }}\'s HRM access?')" class="inline">@csrf @method('DELETE')<button class="text-red-500 hover:text-red-700 text-xs font-semibold"><i class="fa-solid fa-ban mr-1"></i>Revoke</button></form>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-user-shield text-3xl mb-2"></i><p class="text-sm">Only the master admin has HRM access so far.</p></div>
    @endif
  </div>
</div>
@endsection
