@extends('layouts.admin')
@section('title','Automations')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Automations</h1><p class="text-sm text-gray-400 mt-0.5">When an event happens and conditions match, run an action.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.automations.store') }}" class="bg-white rounded-2xl p-5 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)" x-data="{ at:'email' }">
        @csrf
        <h2 class="font-semibold text-gray-700">New rule</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div><label class="block text-xs text-gray-500 mb-1">Name</label><input name="name" class="form-input w-full text-sm" required></div>
            <div><label class="block text-xs text-gray-500 mb-1">When (event)</label><select name="event" class="form-input w-full text-sm">@foreach($events as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div><label class="block text-xs text-gray-500 mb-1">Condition field (optional)</label><input name="cond_field" placeholder="e.g. total" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Operator</label><select name="cond_op" class="form-input w-full text-sm"><option value="=">=</option><option value="!=">!=</option><option value=">">&gt;</option><option value="<">&lt;</option><option value="contains">contains</option></select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Value</label><input name="cond_value" class="form-input w-full text-sm"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div><label class="block text-xs text-gray-500 mb-1">Action</label><select name="action_type" x-model="at" class="form-input w-full text-sm"><option value="email">Send email</option><option value="note">Log a note</option><option value="webhook">Call webhook</option></select></div>
            <div x-show="at==='email'"><label class="block text-xs text-gray-500 mb-1">Email to (blank = customer)</label><input name="action_to" class="form-input w-full text-sm"></div>
            <div x-show="at==='webhook'"><label class="block text-xs text-gray-500 mb-1">Webhook URL</label><input name="action_url" class="form-input w-full text-sm"></div>
            <div x-show="at==='email'"><label class="block text-xs text-gray-500 mb-1">Subject</label><input name="action_subject" class="form-input w-full text-sm"></div>
            <div class="sm:col-span-2" x-show="at!=='webhook'"><label class="block text-xs text-gray-500 mb-1" x-text="at==='email'?'Body':'Note text'"></label><textarea name="action_body" rows="2" class="form-input w-full text-sm resize-none" placeholder="Use {order_number}, {total}, {email}…"></textarea></div>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="enabled" value="1" checked> Enabled</label>
        <button class="btn-primary text-sm">Create Rule</button>
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($rules->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Name</th><th>Event</th><th>Action</th><th>Runs</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($rules as $r)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $r->name }}</td>
                    <td class="text-sm text-gray-500">{{ $events[$r->event] ?? $r->event }}</td>
                    <td class="text-sm text-gray-500">{{ ucfirst($r->actions[0]['type'] ?? '—') }}</td>
                    <td class="text-sm text-gray-600">{{ $r->runs_count }}</td>
                    <td><span class="badge-{{ $r->enabled?'green':'gray' }}">{{ $r->enabled?'On':'Off' }}</span></td>
                    <td><div class="flex justify-center"><form method="POST" action="{{ route('admin.automations.destroy',$r) }}" onsubmit="return confirm('Delete rule?')">@csrf @method('DELETE')<button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></form></div></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No automation rules yet.</p>@endif
    </div>
</div>
@endsection
