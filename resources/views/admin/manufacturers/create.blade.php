@extends('layouts.admin')

@section('title', 'Create Manufacturer')
@section('breadcrumb', 'Add a new brand')
@section('manu_open', 'true')

@section('content')

<div class="page-header">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.manufacturers.index') }}" class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 transition" style="box-shadow:0 2px 10px rgba(0,0,0,.06)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Add Manufacturer</h2>
            <p class="text-sm text-gray-500 mt-0.5">Brands appear on the storefront Brands page</p>
        </div>
    </div>
    <a href="{{ route('admin.manufacturers.index') }}" class="btn-ghost flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        All Manufacturers
    </a>
</div>

<form action="{{ route('admin.manufacturers.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.manufacturers._form', ['m' => null])

    <div class="flex items-center gap-3 mt-6">
        <button type="submit" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Create Manufacturer
        </button>
        <a href="{{ route('admin.manufacturers.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>

@endsection
