@extends('layouts.admin')
@section('title','Two-Factor Authentication')
@section('content')
<div class="p-6 max-w-2xl space-y-5">
    <div><h1 class="text-2xl font-bold text-gray-800">Two-Factor Authentication</h1><p class="text-sm text-gray-400 mt-0.5">Add a one-time email code to your login for extra security.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($user->two_factor_enabled)
        <p class="text-sm text-gray-700 mb-4"><span class="badge-green">Enabled</span> At each login, a code is emailed to <b>{{ $user->email }}</b>.</p>
        <form method="POST" action="{{ route('two-factor.disable') }}">@csrf<button class="py-2.5 px-4 rounded-xl border border-red-200 text-red-500 text-sm hover:bg-red-50">Disable two-factor</button></form>
        @else
        <p class="text-sm text-gray-700 mb-4"><span class="badge-gray">Disabled</span> Turn this on to require an emailed code at login.</p>
        <p class="text-[12px] text-gray-400 mb-4">Make sure email is configured under Settings → Email, otherwise you won't receive the code.</p>
        <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<button class="btn-primary">Enable two-factor</button></form>
        @endif
    </div>
</div>
@endsection
