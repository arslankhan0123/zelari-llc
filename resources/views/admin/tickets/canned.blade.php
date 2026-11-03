@extends('layouts.admin')
@section('title','Canned Replies')
@section('content')
<div class="p-6 space-y-5 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tickets.index') }}" class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <div><h1 class="text-2xl font-bold text-gray-800">Canned Replies</h1><p class="text-sm text-gray-400 mt-0.5">Saved responses you can drop into any ticket.</p></div>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('admin.tickets.canned.store') }}" class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf
        <input name="title" placeholder="Title (e.g. Shipping delay)" class="form-input w-full text-sm" required>
        <textarea name="body" rows="3" placeholder="Reply text…" class="form-input w-full text-sm resize-none" required></textarea>
        <button class="btn-primary text-sm">Add Canned Reply</button>
    </form>

    @foreach($replies as $c)
    <form method="POST" action="{{ route('admin.tickets.canned.update',$c) }}" class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf @method('PUT')
        <div class="flex items-center gap-2">
            <input name="title" value="{{ $c->title }}" class="form-input w-full text-sm font-semibold">
            <button class="text-sm px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100">Save</button>
            <button form="delc{{ $c->id }}" class="text-sm px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100">Delete</button>
        </div>
        <textarea name="body" rows="3" class="form-input w-full text-sm resize-none">{{ $c->body }}</textarea>
    </form>
    <form id="delc{{ $c->id }}" method="POST" action="{{ route('admin.tickets.canned.destroy',$c) }}" onsubmit="return confirm('Delete this canned reply?')">@csrf @method('DELETE')</form>
    @endforeach
</div>
@endsection
