<x-guest-layout>
    @section('title', 'Verify your email')
    @section('art_title')One more step <span>— verify your email.</span>@endsection
    @section('art_text')We sent a 6-digit code to your inbox. Enter it to finish creating your account.@endsection
    <h2>Verify your email</h2>
    <p class="lead">We emailed a 6-digit code to <b>{{ $email }}</b>. Enter it below to finish signing up.</p>

    @if (session('status'))
        <div class="status-ok"><i class="fa-solid fa-circle-check"></i><div>{{ session('status') }}</div></div>
    @endif
    @if ($errors->has('code'))
        <div class="status-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i><div>{{ $errors->first('code') }}</div></div>
    @endif

    <form method="POST" action="{{ route('register.verify.submit') }}" novalidate>
        @csrf
        <div class="fld">
            <label for="code">Verification code</label>
            <div class="ctl"><i class="fa-solid fa-shield-halved lead-ic"></i>
                <input id="code" class="inp {{ $errors->has('code') ? 'err' : '' }}" type="text" name="code"
                       inputmode="numeric" autocomplete="one-time-code" autofocus maxlength="6"
                       placeholder="••••••" style="letter-spacing:6px;font-weight:700"></div>
            @error('code')<div class="ferr">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-brand"><i class="fa-solid fa-circle-check"></i> Verify &amp; create account</button>
    </form>

    <div class="alt" style="margin-top:18px">DIDN'T GET IT?</div>
    <div style="display:flex;gap:16px;align-items:center;justify-content:center">
        <form method="POST" action="{{ route('register.resend') }}">@csrf<button type="submit" class="link" style="background:none;border:0;cursor:pointer">Resend code</button></form>
        <a href="{{ route('register') }}" class="link">Use a different email</a>
    </div>
</x-guest-layout>
