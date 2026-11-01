<x-guest-layout>
    @section('title', 'Two-factor verification')
    <h2>Verify it's you</h2>
    <p class="text-sm text-gray-500 mb-4">We emailed a 6-digit code to your inbox. Enter it below to finish signing in.</p>
    @if($errors->any())<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:12px">{{ $errors->first('code') }}</div>@endif
    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf
        <label class="block text-sm font-medium mb-1" for="code">Verification code</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus
               style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:16px;letter-spacing:4px;text-align:center" required>
        <button type="submit" class="btn-g" style="width:100%;margin-top:14px;height:48px">Verify &amp; sign in</button>
    </form>
    <p class="text-xs text-gray-400 mt-4"><a href="{{ route('login') }}">Back to login</a></p>
</x-guest-layout>
