@extends('layouts.admin')
@section('title', $post->exists ? 'Edit Post' : 'New Post')
@section('content')
<div class="p-6 max-w-3xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.blog.index') }}" class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $post->exists ? 'Edit Post' : 'New Post' }}</h1>
    </div>
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ $post->exists ? route('admin.blog.update',$post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf @if($post->exists)@method('PUT')@endif
        <div><label class="block text-sm text-gray-600 mb-1.5">Title</label><input name="title" value="{{ old('title',$post->title) }}" class="form-input w-full text-sm" required></div>
        <div><label class="block text-sm text-gray-600 mb-1.5">Excerpt</label><textarea name="excerpt" rows="2" class="form-input w-full text-sm resize-none">{{ old('excerpt',$post->excerpt) }}</textarea></div>
        <div><label class="block text-sm text-gray-600 mb-1.5">Body</label><textarea name="body" rows="10" class="form-input w-full text-sm">{{ old('body',$post->body) }}</textarea></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-600 mb-1.5">Cover image</label>@if($post->cover)<p class="text-[11px] text-emerald-600 mb-1">Current set</p>@endif<input type="file" name="cover" class="form-input w-full text-sm"></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Status</label><select name="status" class="form-input w-full text-sm"><option value="draft" {{ old('status',$post->status)==='draft'?'selected':'' }}>Draft</option><option value="published" {{ old('status',$post->status)==='published'?'selected':'' }}>Published</option></select></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Meta title (SEO)</label><input name="meta_title" value="{{ old('meta_title',$post->meta_title) }}" class="form-input w-full text-sm"></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Meta description (SEO)</label><input name="meta_description" value="{{ old('meta_description',$post->meta_description) }}" class="form-input w-full text-sm"></div>
        </div>
        <button class="btn-primary">Save Post</button>
    </form>
</div>
@endsection
