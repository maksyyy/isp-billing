<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ISP Billing') }} - Control Panel</title>

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        /* Sidebar styling with deep solid dark-navy & glass borders */
        .sidebar-glass {
            background: #090D1A;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Nav link transitions */
        .nav-link-item {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom scrollbar for sidebar navigation menu */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-800 bg-[#F8FAFC] h-full overflow-hidden">
<div class="flex h-full min-h-screen overflow-hidden relative">

    <!-- BACKDROP OVERLAY FOR MOBILE SIDEBAR -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 hidden opacity-0 transition-opacity duration-300 ease-in-out lg:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar-menu" class="w-64 sidebar-glass text-slate-300 h-full flex flex-col justify-between shadow-2xl fixed inset-y-0 left-0 -translate-x-full lg:translate-x-0 lg:static z-40 transition-transform duration-300 ease-in-out">
        
        <!-- TOP MENU AREA -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Sidebar Header / Branding -->
            <div class="p-6 border-b border-white/[0.03]">
                <a href="{{ route('dashboard') }}" class="block hover:opacity-90 transition-opacity">
                    <x-company-logo class="text-white" mark-class="h-10 w-10 text-white" text-class="text-base text-white" />
                </a>
            </div>

            <!-- Scrollable Navigation Items -->
            <div class="p-4 flex-1 overflow-y-auto sidebar-scroll">
                @php
                    $user = auth()->user();
                    $role = $user->role;
                    
                    // Style list menu navigasi menjadi pill/capsule premium
                    $baseClass = "nav-link-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200";
                    $activeClass = "bg-indigo-600 text-white shadow-lg shadow-indigo-600/25";
                    $inactiveClass = "text-slate-400 hover:bg-white/5 hover:text-slate-200";
                @endphp

                <ul class="space-y-1.5">

                    <!-- DASHBOARD (Unified React) -->
                    @if($role == 'admin' || $role == 'finance' || $role == 'noc')
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ $baseClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    @endif

                    <!-- BACKBONE MONITOR / ALERTS (ADMIN & NOC ONLY) -->
                    @if($role == 'admin' || $role == 'noc')
                    <li>
                        <a href="{{ route('backbone.index') }}" class="{{ $baseClass }} {{ request()->routeIs('backbone.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Sub-second Alerts</span>
                        </a>
                    </li>
                    @endif

                    <!-- MASTER ADMIN ONLY -->
                    @if($role == 'master')
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ $baseClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('settings.index') }}" class="{{ $baseClass }} {{ request()->routeIs('settings.*') || request()->routeIs('users.*') || request()->routeIs('branding.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                    @endif

                    <!-- ADMIN TENANT ONLY -->
                    @if($role == 'admin')
                    <li>
                        <a href="{{ route('packages.index') }}" class="{{ $baseClass }} {{ request()->routeIs('packages.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Layanan Paket</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="{{ $baseClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Pelanggan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('invoices.index') }}" class="{{ $baseClass }} {{ request()->routeIs('invoices.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Invoice Tagihan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="{{ $baseClass }} {{ request()->routeIs('tickets.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span>Tiket Aduan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('settings.index') }}" class="{{ $baseClass }} {{ request()->routeIs('settings.*') || request()->routeIs('users.*') || request()->routeIs('branding.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                    @endif

                    <!-- FINANCE ONLY -->
                    @if($role == 'finance')
                    <li>
                        <a href="{{ route('invoices.index') }}" class="{{ $baseClass }} {{ request()->routeIs('invoices.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Invoice Tagihan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="{{ $baseClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Pelanggan</span>
                        </a>
                    </li>
                    @endif

                    <!-- TEKNISI ONLY -->
                    @if($role == 'teknisi')
                    <li>
                        <a href="{{ route('invoices.index') }}" class="{{ $baseClass }} {{ request()->routeIs('invoices.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Input Pembayaran</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="{{ $baseClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Pelanggan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="{{ $baseClass }} {{ request()->routeIs('tickets.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span>Tiket Aduan</span>
                        </a>
                    </li>
                    @endif

                    <!-- NOC ONLY -->
                    @if($role == 'noc')
                    <li>
                        <a href="{{ route('customers.index') }}" class="{{ $baseClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Pelanggan</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="{{ $baseClass }} {{ request()->routeIs('tickets.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span>Tiket Aduan</span>
                        </a>
                    </li>
                    @endif

                    <!-- PRESENSI MANDIRI (ADMIN & NOC ONLY) -->
                    @if($role == 'admin' || $role == 'noc')
                    <li>
                        <a href="{{ route('presensi.index') }}" class="{{ $baseClass }} {{ request()->routeIs('presensi.*') ? $activeClass : $inactiveClass }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm-3.75 5.625c0-1.036.84-1.875 1.875-1.875h0c1.036 0 1.875.84 1.875 1.875v.375H7.125v-.375z" />
                            </svg>
                            <span>Presensi Mandiri</span>
                        </a>
                    </li>
                    @endif

                </ul>
            </div>
        </div>

        <!-- BOTTOM USER INFO -->
        <div class="p-4 border-t border-slate-900/60 bg-[#060913]/30">

            <!-- Profile Info Card -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white rounded-xl flex items-center justify-center text-sm font-extrabold shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-white leading-tight truncate">{{ $user->name }}</p>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $user->role }}</p>
                </div>
            </div>

            <!-- Logout Action -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0B0F19] border border-slate-800/80 hover:bg-rose-500/10 hover:border-rose-500/30 hover:text-rose-400 text-slate-400 rounded-xl transition-all duration-200 text-[11px] font-bold uppercase tracking-wider cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </button>
            </form>

        </div>

    </aside>

    <!-- MAIN CONTENT VIEW AREA -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        
        <!-- TOP STICKY HEADER BAR -->
        <header class="h-16 border-b border-slate-200/80 bg-white/80 backdrop-blur-md px-6 flex items-center justify-between sticky top-0 z-20 shrink-0">
            <!-- Left Header Section -->
            <div class="flex items-center gap-4 min-w-0">
                <!-- Mobile Sidebar Toggle -->
                <button id="sidebar-toggle" class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer" aria-label="Toggle Navigation Sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <!-- Dynamic Header Page Title or Breadcrumb -->
                <div class="min-w-0">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <h2 class="text-lg font-bold text-slate-800 leading-tight truncate">
                            {{ config('app.name', 'ISP Billing') }} Console
                        </h2>
                    @endif
                </div>
            </div>

            <!-- Right Header Section (Quick Actions & Notifications) -->
            <div class="flex items-center gap-3 shrink-0">
                @php
                    $currentRoute = request()->route() ? request()->route()->getName() : null;
                    $searchRoutes = ['customers.index', 'invoices.index', 'packages.index', 'tickets.index', 'settings.index', 'users.index'];
                    $searchAction = in_array($currentRoute, $searchRoutes) ? url()->current() : route('customers.index');
                @endphp
                <!-- Functional Search bar (Global Header) -->
                <form action="{{ $searchAction }}" method="GET" class="relative w-32 sm:w-48 md:w-56">
                    @if($searchAction == url()->current() && request()->has('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @elseif($currentRoute === 'settings.index' || $currentRoute === 'users.index')
                        <input type="hidden" name="tab" value="staff">
                    @endif
                    <input type="text" name="search" id="global-search-input" value="{{ request('search') }}" placeholder="Cari data..." class="w-full text-xs pl-8 pr-3 sm:pr-10 py-1.5 bg-slate-100/80 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500/30 rounded-xl text-slate-600 font-medium transition-all">
                    <span class="absolute left-2.5 top-2 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <span class="absolute right-2 top-1.5 px-1 py-0.5 bg-white border border-slate-200 text-[8px] text-slate-400 font-bold rounded-md hidden sm:block">
                        ⌘ K
                    </span>
                </form>

                <!-- Network Status indicator -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/5 border border-emerald-500/10 text-[10px] text-emerald-600 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>GATEWAY LIVE</span>
                </div>

                <!-- Alert/Notifications Bell -->
                <div class="relative">
                    <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-transparent hover:border-slate-200/80 rounded-xl transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-1.8 h-1.8 bg-indigo-600 rounded-full border border-white"></span>
                    </button>
                </div>
                
                <!-- Quick Settings Link / Profile Avatar -->
                <a href="{{ route('profile.edit') }}" class="p-1 bg-slate-50 border border-slate-200/80 hover:bg-slate-100 rounded-xl transition-all flex items-center justify-center gap-2">
                    <div class="w-7 h-7 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg flex items-center justify-center">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </a>
            </div>
        </header>

        <!-- SCROLLABLE WORKSPACE CONTENT -->
        <div class="flex-1 overflow-y-auto min-h-0 flex flex-col justify-between">
            <!-- Slot Content View -->
            <main class="flex-1 p-6 lg:p-8">
                {{ $slot }}
            </main>

            <!-- Footer Section -->
            <footer class="border-t border-slate-200 bg-white px-6 lg:px-8 py-4 text-xs text-slate-400 font-semibold flex items-center justify-between shrink-0">
                <div>
                    &copy; {{ date('Y') }} {{ config('app.name', 'ISP Billing') }} Portal. Semua Hak Cipta Dilindungi.
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-indigo-500 bg-indigo-500/5 px-2.5 py-1 rounded-lg border border-indigo-500/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Secure Console v2.6
                </div>
            </footer>
        </div>

    </div>

</div>

<!-- RESPONSIVE SIDEBAR LOGIC (VANILLA JS) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar-menu');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (toggleBtn && sidebar && backdrop) {
            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.add('opacity-100');
                }, 50);
            };

            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('opacity-100');
                setTimeout(() => {
                    backdrop.classList.add('hidden');
                }, 300);
            };

            toggleBtn.addEventListener('click', openSidebar);
            backdrop.addEventListener('click', closeSidebar);
        }

        // Keyboard shortcut (⌘ K or Ctrl K) to focus search input
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('global-search-input')?.focus();
            }
        });
    });
</script>
</body>
</html>
