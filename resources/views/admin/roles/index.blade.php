@extends('layouts.admin')
@section('title','Roles & Permissions')

@section('content')
<div class="p-6 space-y-5">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Roles & Permissions</h1>
      <p class="text-sm text-gray-400 mt-0.5">Create roles and control exactly what each one can access</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn-primary flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New role
    </a>
  </div>

  @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">{{ session('error') }}</div>@endif

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($roles as $r)
    <div class="bg-white rounded-2xl p-5 border border-gray-100" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">{{ strtoupper(substr($r->name,0,1)) }}</div>
          <div>
            <div class="font-bold text-gray-800">{{ $r->name }}</div>
            <div class="text-[11px] text-gray-400">{{ $r->users_count }} {{ Str::plural('user',$r->users_count) }}@if($r->is_system) · built-in @endif</div>
          </div>
        </div>
        <div class="flex items-center gap-1">
          @if($r->slug !== 'master_admin')
          <a href="{{ route('admin.roles.edit',$r) }}" class="text-gray-400 hover:text-indigo-600 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
          @endif
          @unless($r->is_system)
          <form method="POST" action="{{ route('admin.roles.destroy',$r) }}" onsubmit="return confirm('Delete this role?')">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
          @endunless
        </div>
      </div>
      <p class="text-sm text-gray-500 mt-3">{{ $r->description ?: '—' }}</p>
      <div class="mt-3 text-[11px] font-semibold px-2 py-1 rounded-full inline-block {{ $r->slug==='master_admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
        {{ $r->slug==='master_admin' ? 'Full access' : count($r->permissionStrings()).' permissions' }}
      </div>
    </div>
    @endforeach
  </div>
</div>
@endsection
