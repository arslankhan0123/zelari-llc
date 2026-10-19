<x-guest-layout>
    @section('title', 'Create account')
    @section('art_title')Create your account in <span>under a minute.</span>@endsection
    @section('art_text')Save your details once, then breeze through checkout every time. Track orders and download invoices from one place.@endsection
    <h2>Create your account</h2>
    <p class="lead">Already a customer? Use the same email you ordered with and your past orders will appear in your account.</p>

    @if ($errors->any() && !$errors->hasAny(['name','email','password']))
        <div class="status-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="name">Full name</label>
            <div class="ctl"><i class="fa-regular fa-user lead-ic"></i>
                <input id="name" class="inp {{ $errors->has('name') ? 'err' : '' }}" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your full name"></div>
            @error('name')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld">
            <label for="email">Email address</label>
            <div class="ctl"><i class="fa-regular fa-envelope lead-ic"></i>
                <input id="email" class="inp {{ $errors->has('email') ? 'err' : '' }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com"></div>
            @error('email')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld">
            <label for="password">Password <span class="opt">· at least 8 characters</span></label>
            <div class="ctl"><i class="fa-solid fa-lock lead-ic"></i>
                <input id="password" class="inp {{ $errors->has('password') ? 'err' : '' }}" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" oninput="zStrength(this.value)">
                <button type="button" class="eye" onclick="togglePwd('password', this)" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
            <div class="strength" id="pwStrength" data-s="0"><i></i><i></i><i></i><i></i></div>
            @error('password')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld">
            <label for="password_confirmation">Confirm password</label>
            <div class="ctl"><i class="fa-solid fa-lock lead-ic"></i>
                <input id="password_confirmation" class="inp" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat the password">
                <button type="button" class="eye" onclick="togglePwd('password_confirmation', this)" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
        </div>
        <p style="font-size:12.5px;color:var(--muted);margin:0 0 6px;line-height:1.5">By creating an account you agree to receive order updates by email. We never share your details.</p>
        <button type="submit" class="btn-brand"><i class="fa-solid fa-user-plus"></i> Create account</button>
    </form>

    <div class="alt">ALREADY REGISTERED?</div>
    <p class="auth-switch">Have an account? <a href="{{ route('login') }}" class="link">Log in</a></p>

    <script>
    function zStrength(v){ let s=0; if(v.length>=8) s++; if(/[A-Z]/.test(v)&&/[a-z]/.test(v)) s++; if(/\d/.test(v)) s++; if(/[^A-Za-z0-9]/.test(v)) s++; if(v.length<8) s=Math.min(s,1); document.getElementById('pwStrength').dataset.s=v?s:0; }
    </script>
</x-guest-layout>
