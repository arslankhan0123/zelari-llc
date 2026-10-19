<x-guest-layout>
    @section('title', 'Forgot password')
    @section('art_title')Locked out? <span>We'll get you back in.</span>@endsection
    @section('art_text')Enter the email you use for your account and we'll send you a secure link to choose a new password.@endsection
    <h2>Forgot password?</h2>
    <p class="lead">Enter your email address and we'll send you a link to reset your password.</p>

    @if (session('status'))
        <div class="status-ok"><i class="fa-solid fa-circle-check"></i><div><b>Email sent.</b> {{ session('status') }}</div></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="email">Email address</label>
            <div class="ctl"><i class="fa-regular fa-envelope lead-ic"></i>
                <input id="email" class="inp {{ $errors->has('email') ? 'err' : '' }}" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"></div>
            @error('email')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-brand"><i class="fa-regular fa-paper-plane"></i> Send reset link</button>
    </form>

    <p style="font-size:12.5px;color:var(--muted);margin:18px 0 0;line-height:1.55;display:flex;gap:9px;align-items:flex-start"><i class="fa-regular fa-clock" style="margin-top:3px;color:var(--faint)"></i><span>Reset links expire after <b>{{ (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) }} minutes</b>. If you don't see the email within a few minutes, check your spam folder.</span></p>

    <div class="alt">REMEMBERED IT?</div>
    <p class="auth-switch"><a href="{{ route('login') }}" class="link"><i class="fa-solid fa-arrow-left" style="font-size:12px"></i> Back to log in</a></p>
</x-guest-layout>
