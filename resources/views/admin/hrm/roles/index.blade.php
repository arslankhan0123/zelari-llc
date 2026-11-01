@extends('layouts.admin')
@section('title', 'HR Roles')
@section('breadcrumb', 'HRM · Roles & permissions')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-5">
    <div><h2 class="text-lg font-bold text-gray-800">HR Roles</h2><p class="text-sm text-gray-500">These roles are separate from your ecommerce roles. They control HRM access only.</p></div>
    @can('hrm.roles.create')<a href="{{ route('admin.hrm.roles.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg"><i class="fa-solid fa-plus mr-1"></i> New role</a>@endcan
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
        <th class="text-left font-bold px-5 py-3">Role</th><th class="text-left font-bold px-5 py-3">Description</th>
        <th class="text-center font-bold px-5 py-3">Users</th><th class="text-right font-bold px-5 py-3">Action</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($roles as $r)
        <tr>
          <td class="px-5 py-3 font-bold text-gray-800">{{ $r->name }} @if($r->is_system)<span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 rounded-full px-2 py-0.5 ml-1">system</span>@endif</td>
          <td class="px-5 py-3 text-gray-500">{{ $r->description }}</td>
          <td class="px-5 py-3 text-center text-gray-600">{{ $r->users_count }}</td>
          <td class="px-5 py-3 text-right whitespace-nowrap">
            @can('hrm.roles.edit')<a href="{{ route('admin.hrm.roles.edit',$r) }}" class="text-indigo-600 hover:text-indigo-800 mr-3" title="Edit"><i class="fa-solid fa-pen"></i></a>@endcan
            @can('hrm.roles.delete')@unless($r->is_system)<form method="POST" action="{{ route('admin.hrm.roles.destroy',$r) }}" onsubmit="return confirm('Delete role {{ $r->name }}?')" class="inline">@csrf @method('DELETE')<button class="text-red-500 hover:text-red-700" title="Delete"><i class="fa-solid fa-trash"></i></button></form>@endunless @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
