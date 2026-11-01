@extends('layouts.admin')
@section('title','New Ticket')
@section('content')
<div class="p-6 max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tickets.index') }}" class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-800">New Ticket</h1>
    </div>
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('admin.tickets.store') }}" class="bg-white rounded-2xl p-6 space-y-4" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf
        <div><label class="block text-sm text-gray-600 mb-1.5">Subject</label><input name="subject" value="{{ old('subject') }}" class="form-input w-full text-sm" required></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-600 mb-1.5">Customer name</label><input name="customer_name" value="{{ old('customer_name') }}" class="form-input w-full text-sm"></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Customer email</label><input type="email" name="customer_email" value="{{ old('customer_email') }}" class="form-input w-full text-sm" required></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">Priority</label><select name="priority" class="form-input w-full text-sm">@foreach(['low','normal','high','urgent'] as $pr)<option value="{{ $pr }}" {{ old('priority','normal')===$pr?'selected':'' }}>{{ ucfirst($pr) }}</option>@endforeach</select></div>
            <div><label class="block text-sm text-gray-600 mb-1.5">SLA due (optional)</label><input type="date" name="due_at" value="{{ old('due_at') }}" class="form-input w-full text-sm"></div>
        </div>
        <div><label class="block text-sm text-gray-600 mb-1.5">First message (optional)</label><textarea name="body" rows="4" class="form-input w-full text-sm resize-none">{{ old('body') }}</textarea></div>
        <button class="btn-primary">Create Ticket</button>
    </form>
</div>
@endsection
