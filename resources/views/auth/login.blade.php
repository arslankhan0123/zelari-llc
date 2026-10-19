<x-guest-layout>
    @section('title', 'Log in')
    <h2>Welcome back</h2>
    <p class="lead">Log in to track orders, download invoices and check out faster.</p>

    @if (session('status'))
        <div class="status-ok"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if ($errors->has('email') && !old('email'))
        <div class="status-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i><div>{{ $errors->first('email') }}</div></div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="email">Email address</label>
            <div class="ctl"><i class="fa-regular fa-envelope lead-ic"></i>
                <input id="email" class="inp {{ $errors->has('email') ? 'err' : '' }}" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com"></div>
            @if(old('email')) @error('email')<div class="ferr">{{ $message }}</div>@enderror @endif
        </div>
        <div class="fld">
            <div class="row-between">
                <label for="password">Password</label>
                @if (Route::has('password.request'))<a href="{{ route('password.request') }}" class="link" style="font-size:12.5px">Forgot password?</a>@endif
            </div>
            <div class="ctl"><i class="fa-solid fa-lock lead-ic"></i>
                <input id="password" class="inp {{ $errors->has('password') ? 'err' : '' }}" type="password" name="password" required autocomplete="current-password" placeholder="Your password">
                <button type="button" class="eye" onclick="togglePwd('password', this)" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
            @error('password')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <div class="fld" style="margin-bottom:8px">
            <label class="chk"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Keep me logged in on this device</label>
        </div>
        <button type="submit" class="btn-brand"><i class="fa-solid fa-arrow-right-to-bracket"></i> Log in</button>
    </form>

    @if (Route::has('register'))
    <div class="alt">NEW HERE?</div>
    <p class="auth-switch">Don't have an account yet? <a href="{{ route('register') }}" class="link">Create one — it's free</a></p>
    @endif
</x-guest-layout>
