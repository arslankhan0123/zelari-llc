@extends('layouts.admin')
@section('title', 'My Profile')
@section('breadcrumb', 'Update your account details and password')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- Update Name & Email -->
    <div class="xl:col-span-2 space-y-6">

        <!-- Profile info card -->
        <div class="bg-white rounded-2xl p-6" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-bold"
                     style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 24px rgba(99,102,241,.4);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ Auth::user()->name }}</h2>
                    <p class="text-gray-500 text-sm">{{ Auth::user()->email }}</p>
                    <span class="inline-flex items-center gap-1.5 mt-1 text-xs font-medium text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block"></span>
                        Administrator
                    </span>
                </div>
            </div>

            <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Account Information</h3>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('patch')

                @if (session('status') === 'profile-updated')
                    <div class="alert-success mb-4">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Profile updated successfully!
                    </div>
                @endif

                <div class="mb-5">
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-input @error('name') border-red-400 @enderror"
                           required autocomplete="name">
                    @error('name') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-input @error('email') border-red-400 @enderror"
                           required autocomplete="username">
                    @error('email') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                            <p class="text-amber-700 text-xs">
                                Your email is unverified.
                                <button form="send-verification" class="font-semibold underline text-amber-800 hover:no-underline">
                                    Click to resend verification.
                                </button>
                            </p>
                        </div>
                        @if (session('status') === 'verification-link-sent')
                            <p class="text-green-600 text-xs mt-2">A new verification link has been sent.</p>
                        @endif
                    @endif
                </div>

                <button type="submit" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl p-6" id="password" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-1">Change Password</h3>
            <p class="text-gray-400 text-sm mb-5">Use a strong password of at least 8 characters.</p>

            @if (session('status') === 'password-updated')
                <div class="alert-success mb-4">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Password updated successfully!
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf @method('put')

                <div class="mb-5">
                    <label class="form-label">Current Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="current_password" id="currentPwd"
                               class="form-input pr-11 @error('current_password') border-red-400 @enderror"
                               autocomplete="current-password" placeholder="Enter current password">
                        <button type="button" onclick="togglePwd('currentPwd','eyeI')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eyeI" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('current_password') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label">New Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="newPwd"
                               class="form-input pr-11 @error('password') border-red-400 @enderror"
                               autocomplete="new-password" placeholder="Minimum 8 characters">
                        <button type="button" onclick="togglePwd('newPwd','eyeN')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eyeN" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="form-label">Confirm New Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="confPwd"
                               class="form-input pr-11"
                               autocomplete="new-password" placeholder="Repeat new password">
                        <button type="button" onclick="togglePwd('confPwd','eyeC')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eyeC" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Update Password
                </button>
            </form>
        </div>

        <!-- Danger zone: Delete Account -->
        <div class="bg-white rounded-2xl p-6 border border-red-100" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-red-600 mb-1">Danger Zone</h3>
            <p class="text-gray-400 text-sm mb-4">Once you delete your account, all data will be permanently removed.</p>
            <button type="button"
                    onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                    class="btn-danger">
                Delete My Account
            </button>
        </div>
    </div>

    <!-- Right sidebar -->
    <div class="space-y-5">
        <!-- Avatar / quick info -->
        <div class="bg-white rounded-2xl p-5 text-center" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <div class="w-20 h-20 rounded-2xl mx-auto mb-3 flex items-center justify-center text-white text-3xl font-bold"
                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 24px rgba(99,102,241,.4);">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <h3 class="font-bold text-gray-800">{{ Auth::user()->name }}</h3>
            <p class="text-gray-400 text-sm">{{ Auth::user()->email }}</p>
            <p class="text-xs text-gray-400 mt-1">Member since {{ Auth::user()->created_at->format('M Y') }}</p>
        </div>

        <!-- Security tips -->
        <div class="rounded-2xl p-5" style="background:linear-gradient(135deg,rgba(99,102,241,.06),rgba(139,92,246,.06));border:1px solid rgba(99,102,241,.15)">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3 class="font-semibold text-indigo-700">Security Tips</h3>
            </div>
            <ul class="space-y-2 text-xs text-indigo-600">
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Use at least 12 characters with a mix of letters, numbers, and symbols.</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Never reuse passwords from other accounts.</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>Log out from shared devices after use.</li>
            </ul>
        </div>

        <!-- Quick nav -->
        <div class="bg-white rounded-2xl p-5" style="box-shadow:0 2px 16px rgba(0,0,0,.06)">
            <h3 class="font-semibold text-gray-700 mb-3">Quick Links</h3>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 text-sm text-gray-600 transition">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 text-sm text-gray-600 transition">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categories
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete account modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);backdrop-filter:blur(4px)">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <div class="text-center mb-5">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="font-bold text-gray-800 text-lg">Delete Account?</h3>
            <p class="text-gray-500 text-sm mt-1">This action is permanent and cannot be undone.</p>
        </div>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf @method('delete')
            <div class="mb-4">
                <label class="form-label text-xs">Enter your password to confirm</label>
                <input type="password" name="password" class="form-input" placeholder="Your current password" required>
                @error('userPassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="btn-ghost flex-1">Cancel</button>
                <button type="submit" class="btn-danger flex-1">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- Verify email form (hidden) -->
<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
    @csrf
</form>

@push('scripts')
<script>
function togglePwd(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>
@endpush

@endsection
