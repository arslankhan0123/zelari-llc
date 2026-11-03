@extends('layouts.admin')
@section('title','Two-Factor Authentication')
@section('content')
<div class="p-6 max-w-2xl space-y-5">
    <div><h1 class="text-2xl font-bold text-gray-800">Two-Factor Authentication</h1><p class="text-sm text-gray-400 mt-0.5">Add a one-time email code to your login for extra security.</p></div>
    @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-600 text-sm">{{ $errors->first() }}</div>@endif

    <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
        @if($user->two_factor_enabled)
            <p class="text-sm text-gray-700 mb-4"><span class="badge-green">Enabled</span> At each login, a code is emailed to <b>{{ $user->email }}</b>.</p>
            <form method="POST" action="{{ route('two-factor.disable') }}">@csrf<button class="py-2.5 px-4 rounded-xl border border-red-200 text-red-500 text-sm hover:bg-red-50">Disable two-factor</button></form>

        @elseif(session('2fa_enable_pending'))
            {{-- Step 2: verify the emailed code before enabling --}}
            <p class="text-sm text-gray-700 mb-1"><span class="badge-gray">Verify</span> We emailed a 6-digit code to <b>{{ $user->email }}</b>.</p>
            <p class="text-[12px] text-gray-400 mb-4">Enter it below to turn on two-factor. The code expires in 10 minutes.</p>
            <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex items-center gap-3 flex-wrap">@csrf
                <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" autofocus placeholder="••••••"
                       class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm tracking-[6px] font-bold w-40" style="letter-spacing:6px">
                <button class="btn-primary">Verify &amp; enable</button>
            </form>
            <div class="flex items-center gap-4 mt-3">
                <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<button class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Resend code</button></form>
                <form method="POST" action="{{ route('two-factor.cancel') }}">@csrf<button class="text-sm text-gray-400 hover:text-gray-600">Cancel</button></form>
            </div>

        @else
            {{-- Step 1: request a code --}}
            <p class="text-sm text-gray-700 mb-4"><span class="badge-gray">Disabled</span> Turn this on to require an emailed code at login.</p>
            <p class="text-[12px] text-gray-400 mb-4">We'll first email a code to <b>{{ $user->email }}</b> to confirm you can receive it. Make sure email is configured under Settings &rarr; Email.</p>
            <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<button class="btn-primary">Enable two-factor</button></form>
        @endif
    </div>
</div>
@endsection
