@extends('layouts.admin')
@section('title', 'Edit HR Role')
@section('breadcrumb', 'HRM · Edit role')
@section('content')
<div class="w-full">
  <a href="{{ route('admin.hrm.roles.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 inline-block mb-4"><i class="fa-solid fa-arrow-left mr-1"></i> Roles</a>
  @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-5"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ route('admin.hrm.roles.update',$role) }}">
    @csrf @method('PUT')
    @include('admin.hrm.roles._form', ['modules'=>$modules, 'role'=>$role, 'granted'=>$granted])
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg">Save role</button>
  </form>
</div>
@endsection
