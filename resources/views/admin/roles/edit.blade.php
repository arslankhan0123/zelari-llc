@extends('layouts.admin')
@section('title','Edit role')
@section('content')
<div class="p-6 max-w-6xl">
  <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 mb-4"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Back to roles</a>
  <h1 class="text-2xl font-bold text-gray-800 mb-5">Edit role — {{ $role->name }}</h1>
  <form method="POST" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT')
    @include('admin.roles._form')
    <div class="mt-5"><button class="btn-primary">Save changes</button></div>
  </form>
</div>
@endsection
