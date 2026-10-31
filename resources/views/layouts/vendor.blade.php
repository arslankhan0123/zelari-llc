<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Vendor') · {{ zstore('name','Zelari') }}</title>
    <link rel="icon" href="{{ zbrand('icon') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <style> body { font-family:'Inter',sans-serif; } </style>
</head>
<body class="bg-slate-100" x-data="{ open: window.innerWidth > 768 }">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-60 flex-shrink-0 text-white hidden md:flex flex-col" style="background:linear-gradient(160deg,#0f0c29,#1a1750 55%,#24243e)">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center overflow-hidden">
                <img src="{{ zbrand('icon') }}" class="w-full h-full object-contain" alt="">
            </div>
            <div>
                <div class="font-bold leading-tight">{{ zstore('name','Zelari') }}</div>
                <div class="text-indigo-300 text-xs">Vendor Panel</div>
            </div>
        </div>
        <nav class="p-3 space-y-1 flex-1">
            @php $ni = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition'; @endphp
            <a href="{{ route('vendor.dashboard') }}" class="{{ $ni }} {{ request()->routeIs('vendor.dashboard') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('vendor.products.index') }}" class="{{ $ni }} {{ request()->routeIs('vendor.products.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Products
            </a>
            @foreach(['Orders'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z','Earnings'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2','Store profile'=>'M3 3h18v4H3V3zm0 6h18v12H3V9z'] as $lbl=>$ic)
            <span class="{{ $ni }} text-white/40 cursor-default justify-between">
                <span class="flex items-center gap-3"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic }}"/></svg>{{ $lbl }}</span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-white/10">SOON</span>
            </span>
            @endforeach
        </nav>
        <div class="p-3 border-t border-white/10">
            <div class="flex items-center gap-3 px-2 mb-2">
                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'V',0,1)) }}</div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                    <div class="text-white/40 text-xs truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-white/70 hover:bg-white/10 hover:text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-800">@yield('title','Dashboard')</h1>
            <a href="{{ route('home') }}" target="_blank" class="text-sm text-gray-400 hover:text-gray-600">View storefront ↗</a>
        </header>
        <main class="flex-1 p-6">
            @if(session('success'))<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm mb-5">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
