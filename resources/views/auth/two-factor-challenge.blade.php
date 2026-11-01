<x-guest-layout>
    @section('title', 'Two-factor verification')
    <h2>Verify it's you</h2>
    <p class="lead">We emailed a 6-digit code to your inbox. Enter it below to finish signing in.</p>

    @if ($errors->has('code'))
        <div class="status-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i><div>{{ $errors->first('code') }}</div></div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="code">Verification code</label>
            <div class="ctl"><i class="fa-solid fa-shield-halved lead-ic"></i>
                <input id="code" class="inp {{ $errors->has('code') ? 'err' : '' }}" type="text" name="code"
                       inputmode="numeric" autocomplete="one-time-code" autofocus maxlength="6"
                       placeholder="••••••" style="letter-spacing:6px;font-weight:700">
            </div>
            @error('code')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-brand"><i class="fa-solid fa-arrow-right-to-bracket"></i> Verify &amp; sign in</button>
    </form>

    <div class="alt">TROUBLE?</div>
    <p class="auth-switch">Didn't get a code? <a href="{{ route('login') }}" class="link">Back to login</a></p>
</x-guest-layout>
