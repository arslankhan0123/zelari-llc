@extends('layouts.admin')
@section('title',$ticket->ref)
@section('content')
<div class="p-6 space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tickets.index') }}" class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h1>
            <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ $ticket->ref }} · {{ $ticket->customer_name ?: $ticket->customer_email }} · <span class="badge-{{ $ticket->status_color }}">{{ ucfirst($ticket->status) }}</span> <span class="badge-{{ $ticket->priority_color }}">{{ ucfirst($ticket->priority) }}</span></p>
        </div>
    </div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl p-5 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                @forelse($ticket->replies as $r)
                <div class="flex gap-3 {{ $r->is_staff ? '' : 'flex-row-reverse' }}">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0 {{ $r->is_staff ? 'bg-brand' : 'bg-gray-400' }}">{{ strtoupper(substr($r->author_name ?: 'C',0,1)) }}</div>
                    <div class="max-w-[80%] {{ $r->is_staff ? '' : 'text-right' }}">
                        <p class="text-xs text-gray-400 mb-1">{{ $r->author_name ?: ($r->is_staff?'Staff':'Customer') }} · {{ $r->created_at->diffForHumans() }}</p>
                        <div class="rounded-xl px-4 py-3 text-sm {{ $r->is_staff ? 'bg-indigo-50 text-gray-800' : 'bg-gray-100 text-gray-800' }}" style="white-space:pre-wrap">{{ $r->body }}</div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">No messages yet. Start the conversation below.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.tickets.reply',$ticket) }}" class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                @csrf
                @if($canned->count())
                <select onchange="if(this.value){document.getElementById('replyBody').value=this.value;this.selectedIndex=0;}" class="form-input text-sm w-full">
                    <option value="">Insert a canned reply…</option>
                    @foreach($canned as $c)<option value="{{ $c->body }}">{{ $c->title }}</option>@endforeach
                </select>
                @endif
                <textarea id="replyBody" name="body" rows="4" class="form-input w-full text-sm resize-none" placeholder="Write a reply…" required></textarea>
                <div class="flex items-center gap-3">
                    <button class="btn-primary text-sm">Send Reply</button>
                    <label class="text-sm text-gray-500 flex items-center gap-2">then set
                        <select name="status" class="form-input text-sm">
                            <option value="">— keep status —</option>
                            <option value="pending">Pending</option>
                            <option value="closed">Closed</option>
                        </select>
                    </label>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <form method="POST" action="{{ route('admin.tickets.update',$ticket) }}" class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                @csrf @method('PUT')
                <h3 class="font-semibold text-gray-700">Ticket</h3>
                <div><label class="block text-xs text-gray-500 mb-1">Status</label><select name="status" class="form-input w-full text-sm">@foreach(['open','pending','closed'] as $st)<option value="{{ $st }}" {{ $ticket->status===$st?'selected':'' }}>{{ ucfirst($st) }}</option>@endforeach</select></div>
                <div><label class="block text-xs text-gray-500 mb-1">Priority</label><select name="priority" class="form-input w-full text-sm">@foreach(['low','normal','high','urgent'] as $pr)<option value="{{ $pr }}" {{ $ticket->priority===$pr?'selected':'' }}>{{ ucfirst($pr) }}</option>@endforeach</select></div>
                <div><label class="block text-xs text-gray-500 mb-1">Assigned to</label><select name="assigned_to" class="form-input w-full text-sm"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}" {{ $ticket->assigned_to==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select></div>
                <div><label class="block text-xs text-gray-500 mb-1">SLA due</label><input type="date" name="due_at" value="{{ optional($ticket->due_at)->format('Y-m-d') }}" class="form-input w-full text-sm"></div>
                <button class="btn-primary w-full text-sm">Save</button>
            </form>
            <div class="bg-white rounded-2xl p-5 text-sm text-gray-500 space-y-1" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
                <p><span class="text-gray-400">Email:</span> {{ $ticket->customer_email }}</p>
                @if($ticket->order)<p><span class="text-gray-400">Order:</span> <a href="{{ route('admin.orders.show',$ticket->order) }}" class="text-indigo-600 font-mono">{{ $ticket->order->order_number }}</a></p>@endif
                <p><span class="text-gray-400">Opened:</span> {{ $ticket->created_at->format('M d, Y') }}</p>
                @if($ticket->first_responded_at)<p><span class="text-gray-400">First reply:</span> {{ $ticket->first_responded_at->diffForHumans($ticket->created_at, true) }} after open</p>@endif
            </div>
        </div>
    </div>
</div>
@endsection
