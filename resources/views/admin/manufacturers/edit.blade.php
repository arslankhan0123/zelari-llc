@extends('layouts.admin')

@section('title', 'Edit Manufacturer')
@section('breadcrumb', 'Update brand details')
@section('manu_open', 'true')

@section('content')

<div class="page-header">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.manufacturers.index') }}" class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 transition" style="box-shadow:0 2px 10px rgba(0,0,0,.06)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $manufacturer->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">/{{ $manufacturer->slug }}</p>
        </div>
    </div>
    <form action="{{ route('admin.manufacturers.destroy', $manufacturer) }}" method="POST"
          onsubmit="return confirm('Delete this brand permanently? Its products stay, but lose their brand.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-ghost text-red-500 hover:bg-red-50 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete
        </button>
    </form>
</div>

<form action="{{ route('admin.manufacturers.update', $manufacturer) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.manufacturers._form', ['m' => $manufacturer])

    <div class="flex items-center gap-3 mt-6">
        <button type="submit" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save Changes
        </button>
        <a href="{{ route('admin.manufacturers.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>

@endsection
