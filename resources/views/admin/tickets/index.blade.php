@extends('layouts.admin')
@section('title','Support Tickets')
@section('content')
<div class="p-6 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-gray-800">Support Tickets</h1><p class="text-sm text-gray-400 mt-0.5">Customer support queue with priority and SLA.</p></div>
        <div class="flex gap-2">
            <a href="{{ route('admin.tickets.canned') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Canned replies</a>
            <a href="{{ route('admin.tickets.create') }}" class="btn-primary text-sm flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New Ticket</a>
        </div>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <form method="GET" class="bg-white rounded-2xl px-5 py-4 flex items-center gap-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        <select name="status" class="form-input text-sm w-36"><option value="">All Statuses</option>@foreach(['open','pending','closed'] as $st)<option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>@endforeach</select>
        <select name="priority" class="form-input text-sm w-36"><option value="">All Priority</option>@foreach(['low','normal','high','urgent'] as $pr)<option value="{{ $pr }}" {{ request('priority')===$pr?'selected':'' }}>{{ ucfirst($pr) }}</option>@endforeach</select>
        <button class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request()->hasAny(['status','priority']))<a href="{{ route('admin.tickets.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($tickets->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Ref</th><th>Subject</th><th>Customer</th><th>Priority</th><th>Status</th><th>SLA</th><th>Updated</th></tr></thead>
            <tbody>
                @foreach($tickets as $t)
                <tr class="cursor-pointer hover:bg-gray-50" onclick="location='{{ route('admin.tickets.show',$t) }}'">
                    <td class="font-mono text-xs text-indigo-600">{{ $t->ref }}</td>
                    <td class="text-sm font-medium text-gray-800">{{ \Illuminate\Support\Str::limit($t->subject,40) }}<span class="text-gray-300 text-xs"> · {{ $t->replies_count }}</span></td>
                    <td class="text-sm text-gray-500">{{ $t->customer_name ?: $t->customer_email }}</td>
                    <td><span class="badge-{{ $t->priority_color }}">{{ ucfirst($t->priority) }}</span></td>
                    <td><span class="badge-{{ $t->status_color }}">{{ ucfirst($t->status) }}</span></td>
                    <td>@if($t->isOverdue())<span class="badge-red">Overdue</span>@elseif($t->due_at)<span class="text-xs text-gray-500">{{ $t->due_at->format('M d') }}</span>@else<span class="text-gray-300">—</span>@endif</td>
                    <td class="text-sm text-gray-400">{{ $t->updated_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($tickets->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $tickets->links() }}</div>@endif
        @else<div class="py-16 text-center text-sm text-gray-400">No tickets yet.</div>@endif
    </div>
</div>
@endsection
