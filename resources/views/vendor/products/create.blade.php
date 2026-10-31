@extends('layouts.vendor')
@section('title','Add product')
@section('content')
<div class="max-w-5xl">
  <a href="{{ route('vendor.products.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 mb-4"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Back</a>
  <h2 class="text-xl font-bold text-gray-800 mb-5">Add product</h2>
  <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">@csrf
    @include('vendor.products._form')
    <div class="mt-5 flex gap-3"><button class="px-5 py-2.5 rounded-xl text-white font-semibold text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Submit for approval</button></div>
  </form>
</div>
@endsection
