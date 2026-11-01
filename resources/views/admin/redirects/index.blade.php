@extends('layouts.admin')
@section('title','Redirects')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Redirects</h1><p class="text-sm text-gray-400 mt-0.5">Forward old URLs to new ones (SEO-friendly 301/302).</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.redirects.store') }}" class="bg-white rounded-2xl p-5 flex flex-wrap items-end gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf
        <div class="flex-1 min-w-48"><label class="block text-xs text-gray-500 mb-1">From path</label><input name="from_path" placeholder="/old-page" class="form-input w-full text-sm" required></div>
        <div class="flex-1 min-w-48"><label class="block text-xs text-gray-500 mb-1">To path / URL</label><input name="to_path" placeholder="/new-page" class="form-input w-full text-sm" required></div>
        <div><label class="block text-xs text-gray-500 mb-1">Code</label><select name="code" class="form-input text-sm"><option value="301">301</option><option value="302">302</option></select></div>
        <button class="btn-primary text-sm">Add</button>
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($redirects->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>From</th><th>To</th><th>Code</th><th>Hits</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($redirects as $r)
                <tr>
                    <td class="font-mono text-xs text-gray-700">{{ $r->from_path }}</td>
                    <td class="font-mono text-xs text-gray-700">{{ $r->to_path }}</td>
                    <td class="text-sm">{{ $r->code }}</td>
                    <td class="text-sm text-gray-500">{{ $r->hits }}</td>
                    <td><div class="flex justify-center"><form method="POST" action="{{ route('admin.redirects.destroy',$r) }}" onsubmit="return confirm('Remove redirect?')">@csrf @method('DELETE')<button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></form></div></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No redirects yet.</p>@endif
    </div>
</div>
@endsection
