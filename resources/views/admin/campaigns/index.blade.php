@extends('layouts.admin')
@section('title','Campaigns')
@section('content')
<div class="p-6 space-y-5 max-w-4xl">
    <div><h1 class="text-2xl font-bold text-gray-800">Marketing Campaigns</h1><p class="text-sm text-gray-400 mt-0.5">Reach a customer segment. Email works out of the box; SMS/push need a provider.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.campaigns.store') }}" class="bg-white rounded-2xl p-5 space-y-3" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @csrf
        <h2 class="font-semibold text-gray-700">New campaign</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input name="name" placeholder="Campaign name" class="form-input text-sm" required>
            <select name="segment" class="form-input text-sm"><option value="all">All customers</option><option value="new">New</option><option value="repeat">Repeat</option><option value="vip">VIP</option><option value="lapsed">Lapsed</option></select>
            <select name="channel" class="form-input text-sm"><option value="email">Email</option><option value="sms">SMS</option><option value="push">Push</option></select>
        </div>
        <input name="subject" placeholder="Subject" class="form-input w-full text-sm">
        <textarea name="body" rows="3" placeholder="Message…" class="form-input w-full text-sm resize-none"></textarea>
        <button class="btn-primary text-sm">Save Draft</button>
    </form>

    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($campaigns->count())
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Name</th><th>Segment</th><th>Channel</th><th>Status</th><th>Recipients</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($campaigns as $c)
                <tr>
                    <td class="text-sm font-medium text-gray-800">{{ $c->name }}</td>
                    <td class="text-sm text-gray-500">{{ ucfirst($c->segment) }}</td>
                    <td class="text-sm text-gray-500">{{ ucfirst($c->channel) }}</td>
                    <td><span class="badge-{{ $c->status_color }}">{{ ucfirst($c->status) }}</span></td>
                    <td class="text-sm text-gray-600">{{ $c->recipients }}</td>
                    <td><div class="flex justify-center">@if($c->status!=='sent')<form method="POST" action="{{ route('admin.campaigns.send',$c) }}" onsubmit="return confirm('Send this campaign now?')">@csrf<button class="btn-primary text-xs px-3 py-1.5">Send</button></form>@else<span class="text-xs text-gray-400">{{ optional($c->sent_at)->format('M d') }}</span>@endif</div></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else<p class="py-10 text-center text-sm text-gray-400">No campaigns yet.</p>@endif
    </div>
</div>
@endsection
