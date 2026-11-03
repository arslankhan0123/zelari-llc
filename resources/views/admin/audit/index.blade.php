@extends('layouts.admin')
@section('title','Audit Log')
@section('content')
<div class="p-6 space-y-5">
    <div><h1 class="text-2xl font-bold text-gray-800">Audit Log</h1><p class="text-sm text-gray-400 mt-0.5">Who changed what, and when.</p></div>
    <form method="GET" class="bg-white rounded-2xl px-5 py-4 flex items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <select name="action" class="form-input text-sm w-36"><option value="">All actions</option>@foreach(['created','updated','deleted'] as $a)<option value="{{ $a }}" {{ request('action')===$a?'selected':'' }}>{{ ucfirst($a) }}</option>@endforeach</select>
        <select name="model" class="form-input text-sm w-44"><option value="">All types</option>@foreach($models as $m)<option value="{{ $m }}" {{ request('model')===$m?'selected':'' }}>{{ $m }}</option>@endforeach</select>
        <button class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request()->hasAny(['action','model']))<a href="{{ route('admin.audit.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>
    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($logs->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>What</th><th>IP</th></tr></thead>
            <tbody>
                @foreach($logs as $l)
                <tr>
                    <td class="text-sm text-gray-500">{{ $l->created_at->format('M d, H:i') }}</td>
                    <td class="text-sm text-gray-700">{{ optional($l->user)->name ?? 'system' }}</td>
                    <td><span class="badge-{{ ['created'=>'green','updated'=>'blue','deleted'=>'red'][$l->action] ?? 'gray' }}">{{ ucfirst($l->action) }}</span></td>
                    <td class="text-sm text-gray-700">{{ $l->description }}</td>
                    <td class="text-xs text-gray-400 font-mono">{{ $l->ip }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($logs->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>@endif
        @else<p class="py-10 text-center text-sm text-gray-400">No activity logged yet.</p>@endif
    </div>
</div>
@endsection
