@extends('layouts.admin')
@section('title','Blog')
@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-800">Blog</h1><p class="text-sm text-gray-400 mt-0.5">Articles for SEO and content marketing.</p></div>
        <a href="{{ route('admin.blog.create') }}" class="btn-primary text-sm flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New Post</a>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($posts->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Title</th><th>Status</th><th>Published</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($posts as $p)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $p->title }}</td>
                    <td><span class="badge-{{ $p->status==='published'?'green':'gray' }}">{{ ucfirst($p->status) }}</span></td>
                    <td class="text-sm text-gray-400">{{ optional($p->published_at)->format('M d, Y') ?? '—' }}</td>
                    <td><x-row-actions module="blog" :view="$p->status==='published' ? route('shop.blog.show',$p->slug) : null" :edit="route('admin.blog.edit',$p)" :delete="route('admin.blog.destroy',$p)" confirm="Delete this post?" /></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($posts->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $posts->links() }}</div>@endif
        @else<p class="py-10 text-center text-sm text-gray-400">No posts yet.</p>@endif
    </div>
</div>
@endsection
