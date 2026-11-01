@extends('layouts.admin')
@section('title', 'Departments')
@section('breadcrumb', 'HRM · Teams & hierarchy')

@section('content')
<div class="w-full" x-data="{ edit: null }">

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @can('hrm.departments.create')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-sitemap text-indigo-500 mr-1"></i> New department</div>
    <form method="POST" action="{{ route('admin.hrm.departments.store') }}" class="grid md:grid-cols-4 gap-4">
      @csrf
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Name *</label><input name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Code</label><input name="code" value="{{ old('code') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Parent</label>
        <select name="parent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
        </select></div>
      <div><label class="block text-xs font-bold text-gray-500 mb-1">Head</label>
        <select name="head_employee_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">—</option>
          @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
        </select></div>
      <div class="md:col-span-4"><button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg">Add department</button></div>
    </form>
  </div>
  @endcan

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-800">Departments</div>
    @if($departments->count())
    <div class="divide-y divide-gray-100">
      @foreach($departments as $d)
      <div class="px-5 py-3">
        <div class="flex items-center justify-between">
          <div>
            <span class="font-bold text-gray-800">{{ $d->name }}</span>
            @if($d->code)<span class="text-[11px] text-gray-400 ml-1">{{ $d->code }}</span>@endif
            <div class="text-[11px] text-gray-400">
              {{ $d->employees_count }} {{ \Illuminate\Support\Str::plural('member',$d->employees_count) }}
              @if($d->parent) · under {{ $d->parent->name }}@endif
              @if($d->head) · head: {{ $d->head->name }}@endif
            </div>
          </div>
          <div class="flex items-center gap-1.5">
            @can('hrm.departments.edit')
            <button @click="edit = (edit==={{ $d->id }} ? null : {{ $d->id }})" class="w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center text-indigo-600"><i class="fa-solid fa-pen text-xs"></i></button>
            @endcan
            @can('hrm.departments.delete')
            <form method="POST" action="{{ route('admin.hrm.departments.destroy', $d) }}" onsubmit="return confirm('Delete {{ $d->name }}?')">@csrf @method('DELETE')
              <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500"><i class="fa-solid fa-trash text-xs"></i></button>
            </form>
            @endcan
          </div>
        </div>
        @can('hrm.departments.edit')
        <div x-show="edit==={{ $d->id }}" x-cloak class="mt-3 pt-3 border-t border-gray-100">
          <form method="POST" action="{{ route('admin.hrm.departments.update', $d) }}" class="grid md:grid-cols-4 gap-3">
            @csrf @method('PUT')
            <input name="name" value="{{ $d->name }}" required placeholder="Name" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <input name="code" value="{{ $d->code }}" placeholder="Code" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <select name="parent_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">No parent</option>
              @foreach($departments as $p)@if($p->id!==$d->id)<option value="{{ $p->id }}" {{ $d->parent_id==$p->id?'selected':'' }}>{{ $p->name }}</option>@endif @endforeach
            </select>
            <select name="head_employee_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm"><option value="">No head</option>
              @foreach($employees as $e)<option value="{{ $e->id }}" {{ $d->head_employee_id==$e->id?'selected':'' }}>{{ $e->name }}</option>@endforeach
            </select>
            <div class="md:col-span-4"><button class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded-lg">Update</button></div>
          </form>
        </div>
        @endcan
      </div>
      @endforeach
    </div>
    @else
    <div class="px-5 py-12 text-center text-gray-400"><i class="fa-solid fa-sitemap text-3xl mb-2"></i><p class="text-sm">No departments yet.</p></div>
    @endif
  </div>
</div>
@endsection
