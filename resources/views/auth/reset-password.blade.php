<x-guest-layout>
    @section('title', 'Reset password')
    @section('art_title')Enter your code <span>and choose a new password.</span>@endsection
    @section('art_text')We emailed you a 6-digit code. Enter it with a new password to get back in. Pick something you don't use anywhere else.@endsection
    <h2>Reset your password</h2>
    <p class="lead">Enter the code we emailed you and choose a new password.</p>

    @if (session('status'))
        <div class="status-ok"><i class="fa-solid fa-circle-check"></i><div>{{ session('status') }}</div></div>
    @endif
    @if ($errors->has('email'))
        <div class="status-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i><div>{{ $errors->first('email') }}</div></div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="email">Email address</label>
            <div class="ctl"><i class="fa-regular fa-envelope lead-ic"></i>
                <input id="email" class="inp {{ $errors->has('email') ? 'err' : '' }}" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username" placeholder="you@example.com"></div>
        </div>
        <div class="fld">
            <label for="code">Verification code</label>
            <div class="ctl"><i class="fa-solid fa-shield-halved lead-ic"></i>
                <input id="code" class="inp {{ $errors->has('code') ? 'err' : '' }}" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus maxlength="6" placeholder="••••••" style="letter-spacing:6px;font-weight:700"></div>
            @error('code')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld">
            <label for="password">New password <span class="opt">· at least 8 characters</span></label>
            <div class="ctl"><i class="fa-solid fa-lock lead-ic"></i>
                <input id="password" class="inp {{ $errors->has('password') ? 'err' : '' }}" type="password" name="password" required autocomplete="new-password" placeholder="Create a new password" oninput="zStrength(this.value)">
                <button type="button" class="eye" onclick="togglePwd('password', this)" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
            <div class="strength" id="pwStrength" data-s="0"><i></i><i></i><i></i><i></i></div>
            @error('password')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld">
            <label for="password_confirmation">Confirm new password</label>
            <div class="ctl"><i class="fa-solid fa-lock lead-ic"></i>
                <input id="password_confirmation" class="inp" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat the new password">
                <button type="button" class="eye" onclick="togglePwd('password_confirmation', this)" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
        </div>
        <button type="submit" class="btn-brand"><i class="fa-solid fa-key"></i> Reset password</button>
    </form>

    <div class="alt">DIDN'T GET A CODE?</div>
    <p class="auth-switch"><a href="{{ route('password.request') }}" class="link">Request a new code</a> &nbsp;·&nbsp; <a href="{{ route('login') }}" class="link">Back to log in</a></p>

    <script>
    function zStrength(v){ let s=0; if(v.length>=8) s++; if(/[A-Z]/.test(v)&&/[a-z]/.test(v)) s++; if(/\d/.test(v)) s++; if(/[^A-Za-z0-9]/.test(v)) s++; if(v.length<8) s=Math.min(s,1); document.getElementById('pwStrength').dataset.s=v?s:0; }
    </script>
</x-guest-layout>
