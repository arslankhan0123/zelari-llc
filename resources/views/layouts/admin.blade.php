<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ zstore('name', 'Zelari') }} Admin</title>
    @include('partials.theme-vars')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="icon" href="{{ zbrand('icon') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        :root {
            --sidebar-w: 260px;
            /* Aliases → config/theme.php via partials/theme-vars */
            --indigo: var(--brand);
            --indigo-dark: var(--brand-dark);
            --violet: var(--brand-light);
        }

        /* ── Sidebar ─────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(160deg, #0f0c29 0%, #1a1750 50%, #24243e 100%);
            transition: width .3s cubic-bezier(.4, 0, .2, 1);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 40;
            overflow: hidden;
            box-shadow: 4px 0 30px rgba(0, 0, 0, .4);
        }

        #sidebar.collapsed {
            width: 72px;
        }

        #sidebar.collapsed .nav-label,
        #sidebar.collapsed .brand-text,
        #sidebar.collapsed .user-info {
            display: none !important;
        }

        #sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 12px;
        }

        #sidebar.collapsed .submenu {
            display: none !important;
        }

        #sidebar.collapsed .collapse-arrow {
            display: none;
        }

        /* Sidebar brand logo glow */
        .brand-logo {
            background: linear-gradient(135deg, var(--indigo), var(--violet));
            box-shadow: 0 0 20px rgba(99, 102, 241, .6), 0 0 40px rgba(139, 92, 246, .3);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, .6), 0 0 40px rgba(139, 92, 246, .3);
            }

            50% {
                box-shadow: 0 0 30px rgba(99, 102, 241, .9), 0 0 60px rgba(139, 92, 246, .5);
            }
        }

        /* Nav items */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            margin: 2px 8px;
            border-radius: 10px;
            cursor: pointer;
            color: rgba(255, 255, 255, .65);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all .22s ease;
            position: relative;
            white-space: nowrap;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
            transform: translateX(3px);
        }

        .nav-item.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(99, 102, 241, .35), rgba(139, 92, 246, .15));
            border-left: 3px solid var(--indigo);
            padding-left: 13px;
        }

        .nav-item.active .nav-icon {
            color: var(--indigo);
            filter: drop-shadow(0 0 6px rgba(99, 102, 241, .8));
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Submenu */
        .submenu {
            overflow: hidden;
            transition: max-height .3s ease;
        }

        .sub-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px 9px 48px;
            margin: 1px 8px;
            border-radius: 8px;
            color: rgba(255, 255, 255, .5);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s;
        }

        .sub-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, .06);
        }

        .sub-item.active {
            color: #a5b4fc;
            background: rgba(99, 102, 241, .15);
        }

        /* Sidebar user bottom */
        .sidebar-user {
            border-top: 1px solid rgba(255, 255, 255, .07);
            padding: 14px 16px;
        }

        /* ── Main area ───────────────────────── */
        #main-wrap {
            margin-left: var(--sidebar-w);
            transition: margin-left .3s cubic-bezier(.4, 0, .2, 1);
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
        }

        #main-wrap.sidebar-collapsed {
            margin-left: 72px;
        }

        /* ── Header ──────────────────────────── */
        #topbar {
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 1px 20px rgba(0, 0, 0, .06);
        }

        /* Notification badge */
        .notif-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            border: 2px solid white;
            animation: notif-ping 1.5s ease-in-out infinite;
        }

        @keyframes notif-ping {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: .7;
            }
        }

        /* Prevent Alpine.js flicker on init */
        [x-cloak] {
            display: none !important;
        }

        /* Profile dropdown */
        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 240px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15), 0 4px 16px rgba(0, 0, 0, .08);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .06);
            z-index: 100;
        }

        /* ── Cards ───────────────────────────── */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .06);
            transition: all .3s cubic-bezier(.4, 0, .2, 1);
            transform: perspective(600px) rotateX(0deg) translateY(0);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 60%, rgba(255, 255, 255, .5));
            pointer-events: none;
        }

        .stat-card:hover {
            transform: perspective(600px) rotateX(-2deg) translateY(-6px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .12), 0 8px 24px rgba(0, 0, 0, .07);
        }

        /* ── Tables ──────────────────────────── */
        .data-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .data-table th {
            background: #f8fafc;
            padding: 13px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: background-color .15s ease;
            background: white;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Badges */
        .badge-active {
            background: #dcfce7;
            color: #16a34a;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-yellow { background:#fef9c3;color:#a16207;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-blue   { background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-indigo { background:#e0e7ff;color:#4338ca;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-green  { background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-red    { background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-draft  { background:#f1f5f9;color:#64748b;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }

        /* Form controls */
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            color: #1e293b;
            background: white;
            transition: all .2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--indigo);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }


        /* Brand overrides for Tailwind's indigo utilities used throughout the admin pages.
           Scoped under body so they beat the CDN utilities without !important. */
        body .text-indigo-600, body .text-indigo-700, body .hover\:text-indigo-600:hover, body .hover\:text-indigo-800:hover { color: var(--brand); }
        body .text-indigo-300 { color: var(--brand-light); }
        body .bg-indigo-600, body .hover\:bg-indigo-700:hover { background-color: var(--brand); }
        body .bg-indigo-50, body .hover\:bg-indigo-50:hover, body .bg-indigo-50\/60 { background-color: var(--brand-soft); }
        body .hover\:bg-indigo-100:hover { background-color: var(--brand-soft); filter: brightness(.97); }
        body .bg-indigo-100 { background-color: var(--brand-soft); }
        body .border-indigo-100, body .border-indigo-200, body .border-indigo-300 { border-color: rgba(var(--shadow-rgb), .18); }
        body .text-indigo-600.rounded, body .badge-indigo { color: var(--brand); }
        body .focus\:ring-indigo-500:focus, body .form-input:focus { --tw-ring-color: var(--brand); border-color: var(--brand); }
        body input[type=checkbox].text-indigo-600, body input[type=radio].text-indigo-600 { accent-color: var(--brand); }
        .btn-primary {
            background: var(--brand-grad);
            color: white;
            border: none;
            cursor: pointer;
            padding: 11px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all .25s;
            box-shadow: 0 4px 14px rgba(99, 102, 241, .4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, .5);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, .4);
        }

        .btn-ghost {
            background: transparent;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-ghost:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #374151;
        }

        /* Page header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Alert banners */
        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #15803d;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Sidebar aurora background blobs */
        .sb-blob {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(60px);
            opacity: .12;
        }

        .sb-blob-1 {
            width: 200px;
            height: 200px;
            background: var(--brand);
            top: -60px;
            right: -60px;
            animation: sbfloat 8s ease-in-out infinite;
        }

        .sb-blob-2 {
            width: 150px;
            height: 150px;
            background: var(--brand-light);
            bottom: 100px;
            left: -40px;
            animation: sbfloat 10s ease-in-out infinite reverse;
        }

        @keyframes sbfloat {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(20px, -20px);
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ── Mobile ──────────────────────────── */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.mobile-open {
                transform: translateX(0);
                width: var(--sidebar-w) !important;
            }

            #sidebar.mobile-open .nav-label,
            #sidebar.mobile-open .brand-text,
            #sidebar.mobile-open .user-info {
                display: flex !important;
            }

            #sidebar.mobile-open .nav-item {
                justify-content: flex-start;
                padding: 11px 16px;
            }

            #sidebar.mobile-open .submenu {
                display: block !important;
            }

            #main-wrap,
            #main-wrap.sidebar-collapsed {
                margin-left: 0;
            }

            .mobile-overlay {
                display: block;
            }
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 39;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100" x-data="{
        sidebarOpen: window.innerWidth > 768,
        sidebarMobile: false,
        profileOpen: false,
        notifOpen: false,
        catOpen: @yield('cat_open', 'false'),
        prodOpen: @yield('prod_open', 'false'),
        orderOpen: @yield('order_open', 'false'),
        custOpen: @yield('cust_open', 'false')
      }" @resize.window="sidebarOpen = window.innerWidth > 768">

    <!-- Mobile overlay -->
    <div class="mobile-overlay" :class="{ 'block': sidebarMobile }" @click="sidebarMobile=false"></div>

    <!-- ════════ SIDEBAR ════════ -->
    <aside id="sidebar" :class="{ 'collapsed': !sidebarOpen && window.innerWidth > 768, 'mobile-open': sidebarMobile }">

        <!-- Aurora blobs -->
        <div class="sb-blob sb-blob-1"></div>
        <div class="sb-blob sb-blob-2"></div>

        <!-- Brand -->
        <div class="flex items-center gap-3 px-4 py-5 relative">
            <div class="brand-logo w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                <img src="{{ zbrand('icon') }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="brand-text">
                <div class="text-white font-bold text-lg leading-tight truncate max-w-[150px]">{{ zstore('name', 'Zelari') }}</div>
                <div class="text-indigo-300 text-xs font-medium">Admin Panel</div>
            </div>
            <!-- Collapse toggle (desktop) -->
            <button class="collapse-arrow ml-auto text-white/40 hover:text-white/80 transition-colors hidden md:block"
                @click="sidebarOpen = !sidebarOpen">
                <svg :class="{ 'rotate-180': !sidebarOpen }" class="w-5 h-5 transition-transform" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Nav label -->
        <div class="nav-label px-5 mb-1 mt-2">
            <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Main</span>
        </div>

        <!-- Navigation -->
        <nav class="px-2 space-y-0.5 overflow-y-auto" style="height:calc(100vh - 180px)">

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="nav-label">Dashboard</span>
            </a>

            <!-- Catalog group -->
            <div class="nav-label px-5 mb-1 mt-4">
                <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Catalog</span>
            </div>

            <!-- Categories -->
            <div>
                <button @click="catOpen = !catOpen"
                    class="nav-item w-full {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="nav-label flex-1 text-left">Categories</span>
                    <svg :class="{ 'rotate-180': catOpen }" class="nav-label w-4 h-4 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="submenu" :style="catOpen ? 'max-height:200px' : 'max-height:0'">
                    <a href="{{ route('admin.categories.index') }}"
                        class="sub-item {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        All Categories
                    </a>
                    <a href="{{ route('admin.categories.create') }}"
                        class="sub-item {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        Add New
                    </a>
                </div>
            </div>

            <!-- Products -->
            <div>
                <button @click="prodOpen = !prodOpen"
                    class="nav-item w-full {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="nav-label flex-1 text-left">Products</span>
                    <svg :class="{ 'rotate-180': prodOpen }" class="nav-label w-4 h-4 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="submenu" :style="prodOpen ? 'max-height:200px' : 'max-height:0'">
                    <a href="{{ route('admin.products.index') }}"
                        class="sub-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        All Products
                    </a>
                    <a href="{{ route('admin.products.create') }}"
                        class="sub-item {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        Add New
                    </a>
                </div>
            </div>

            <!-- Orders group -->
            <div class="nav-label px-5 mb-1 mt-4">
                <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Commerce</span>
            </div>

            <!-- Orders -->
            <div>
                <button @click="orderOpen = !orderOpen"
                    class="nav-item w-full {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="nav-label flex-1 text-left">Orders</span>
                    <svg :class="{ 'rotate-180': orderOpen }" class="nav-label w-4 h-4 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="submenu" :style="orderOpen ? 'max-height:200px' : 'max-height:0'">
                    <a href="{{ route('admin.orders.index') }}"
                        class="sub-item {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        All Orders
                    </a>
                    <a href="{{ route('admin.orders.create') }}"
                        class="sub-item {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        New Order
                    </a>
                </div>
            </div>

            <!-- Customers -->
            <div>
                <button @click="custOpen = !custOpen"
                    class="nav-item w-full {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="nav-label flex-1 text-left">Customers</span>
                    <svg :class="{ 'rotate-180': custOpen }" class="nav-label w-4 h-4 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="submenu" :style="custOpen ? 'max-height:200px' : 'max-height:0'">
                    <a href="{{ route('admin.customers.index') }}"
                        class="sub-item {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        All Customers
                    </a>
                    <a href="{{ route('admin.customers.create') }}"
                        class="sub-item {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                        Add Customer
                    </a>
                </div>
            </div>

            <!-- Website -->
            <div class="nav-label px-5 mb-1 mt-4">
                <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Website</span>
            </div>
            <a href="{{ route('admin.content.index') }}"
                class="nav-item {{ request()->routeIs('admin.content.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <span class="nav-label">Website Content</span>
            </a>

            <!-- Payments -->
            <div class="nav-label px-5 mb-1 mt-4">
                <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Payments</span>
            </div>
            <a href="{{ route('admin.payments.index') }}"
                class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="nav-label">All Payments</span>
            </a>

            <!-- Settings -->
            <div class="nav-label px-5 mb-1 mt-4">
                <span class="text-white/30 text-xs font-semibold uppercase tracking-widest">Account</span>
            </div>
            <a href="{{ route('profile.edit') }}"
                class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="nav-label">My Profile</span>
            </a>
            @if(auth()->user()?->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}"
                class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="nav-label">Users &amp; Roles</span>
            </a>
            @endif
            <a href="{{ route('admin.settings.index') }}"
                class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="nav-label">Settings</span>
            </a>
        </nav>

        <!-- Sidebar bottom user -->
        <div class="sidebar-user">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm bg-brand-grad">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="user-info min-w-0">
                    <div class="text-white text-sm font-semibold truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                    <div class="text-white/40 text-xs truncate">{{ Auth::user()->email ?? '' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ════════ MAIN WRAPPER ════════ -->
    <div id="main-wrap" :class="{ 'sidebar-collapsed': !sidebarOpen && window.innerWidth > 768 }">

        <!-- ── TOP BAR ── -->
        <header id="topbar">
            <div class="flex items-center gap-4">
                <!-- Mobile hamburger -->
                <button
                    class="md:hidden w-9 h-9 rounded-lg flex items-center justify-center hover:bg-gray-100 transition"
                    @click="sidebarMobile = !sidebarMobile">
                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Page title -->
                <div>
                    <h1 class="text-gray-800 font-semibold text-lg">@yield('title', 'Dashboard')</h1>
                    <div class="text-gray-400 text-xs hidden sm:block">
                        @yield('breadcrumb', 'Welcome back, ' . (Auth::user()->name ?? 'Admin'))
                    </div>
                </div>
            </div>

            <!-- Right controls -->
            <div class="flex items-center gap-3">

                <!-- Notification bell -->
                <div class="relative" @click.outside="notifOpen=false">
                    <button
                        class="relative w-9 h-9 rounded-lg hover:bg-gray-100 flex items-center justify-center transition"
                        @click="notifOpen=!notifOpen">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="notif-dot"></span>
                    </button>
                    <!-- Notif dropdown -->
                    <div x-show="notifOpen" x-transition x-cloak class="profile-dropdown" style="width:300px">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <span class="font-semibold text-gray-800 text-sm">Notifications</span>
                            <span class="text-xs text-indigo-600 font-medium cursor-pointer">Mark all read</span>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="flex gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-700">New order received</div>
                                    <div class="text-xs text-gray-400">2 minutes ago</div>
                                </div>
                            </div>
                            <div class="flex gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                <div
                                    class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-700">Payment confirmed</div>
                                    <div class="text-xs text-gray-400">1 hour ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100">
                            <a href="#" class="text-xs text-indigo-600 font-medium">View all notifications →</a>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="w-px h-6 bg-gray-200"></div>

                <!-- Profile -->
                <div class="relative" @click.outside="profileOpen=false">
                    <button class="flex items-center gap-2.5 hover:bg-gray-50 rounded-xl px-2.5 py-1.5 transition"
                        @click="profileOpen=!profileOpen">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm bg-brand-grad">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="text-sm font-semibold text-gray-700 leading-tight">
                                {{ Auth::user()->name ?? 'Admin' }}
                            </div>
                            <div class="text-xs text-gray-400">{{ auth()->user()->role_name ?? 'Administrator' }}</div>
                        </div>
                        <svg :class="{ 'rotate-180': profileOpen }" class="w-4 h-4 text-gray-400 transition-transform"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Profile dropdown -->
                    <div x-show="profileOpen" x-transition x-cloak class="profile-dropdown">
                        <!-- Header -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold bg-brand-grad">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-sm">{{ Auth::user()->name }}</div>
                                    <div class="text-gray-400 text-xs">{{ Auth::user()->email }}</div>
                                </div>
                            </div>
                        </div>
                        <!-- Menu items -->
                        <div class="py-2">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Update Profile
                            </a>
                            <a href="{{ route('profile.edit') }}#password"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Change Password
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 text-sm text-red-600 w-full transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ── PAGE CONTENT ── -->
        <main class="flex-1 p-6">

            <!-- Flash messages -->
            @if(session('success'))
                <div class="alert-success" x-data="{show:true}" x-show="show">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button @click="show=false" class="ml-auto text-green-400 hover:text-green-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error" x-data="{show:true}" x-show="show">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                    <button @click="show=false" class="ml-auto">✕</button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- ── FOOTER ── -->
        <footer class="px-6 py-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>© {{ date('Y') }} {{ zstore('name', 'Zelari') }} — All rights reserved.</span>
                <span>{{ zstore('tagline', '') }}</span>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>

</html>