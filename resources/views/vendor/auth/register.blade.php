<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Become a vendor · {{ zstore('name','Zelari') }}</title>
    <link rel="icon" href="{{ zbrand('icon') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background:linear-gradient(135deg,#0f0c29,#302b63 55%,#6d28d9)">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-800">Become a vendor</h1>
            <p class="text-gray-500 text-sm mt-1">Apply to sell on {{ zstore('name','Zelari') }}. Approval is quick.</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm mb-4">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('vendor.register.submit') }}" class="space-y-4">
            @csrf
            @php $inp='w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none'; @endphp
            <div>
                <label class="text-sm font-medium text-gray-600">Store name</label>
                <input name="store_name" value="{{ old('store_name') }}" class="{{ $inp }} mt-1" required placeholder="e.g. Acme Gadgets">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Your name</label>
                    <input name="name" value="{{ old('name') }}" class="{{ $inp }} mt-1" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Phone</label>
                    <input name="phone" value="{{ old('phone') }}" class="{{ $inp }} mt-1" placeholder="Optional">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="{{ $inp }} mt-1" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Password</label>
                    <input type="password" name="password" class="{{ $inp }} mt-1" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Confirm password</label>
                    <input type="password" name="password_confirmation" class="{{ $inp }} mt-1" required>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">About your store</label>
                <textarea name="description" rows="3" class="{{ $inp }} mt-1" placeholder="Optional">{{ old('description') }}</textarea>
            </div>
            <button class="w-full py-3 rounded-xl text-white font-semibold text-sm transition hover:opacity-90" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Submit application</button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-5">Already a vendor? <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Log in</a></p>
    </div>
</body>
</html>
