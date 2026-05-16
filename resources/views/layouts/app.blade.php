<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-blue-50 text-blue-950">
<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <div class="w-64 bg-blue-700 text-white min-h-screen flex flex-col justify-between shadow-xl">

        <!-- TOP MENU -->
        <div class="p-5">

            <a href="{{ route('dashboard') }}" class="block mb-8">
                <x-company-logo class="text-white" mark-class="h-12 w-12" text-class="text-xl" />
            </a>

            @php
                $user = auth()->user();
                $role = $user->role;
            @endphp

            <ul class="space-y-3">

                <!-- DASHBOARD -->
                
                @if($role == 'admin' || $role == 'finance' || $role == 'noc')
                <li>
                    <a href="{{ route('api.page') }}"
                    class="block px-4 py-2 rounded hover:bg-blue-600
                    {{ request()->routeIs('api.page') ? 'bg-white text-blue-700' : '' }}">
                    ⚡ API Dashboard
                    </a>
                </li>
                @endif

                @if($role == 'master')
                <li>
                    <a href="{{ route('dashboard') }}"
                    class="block px-4 py-2 rounded hover:bg-blue-600
                    {{ request()->routeIs('dashboard') ? 'bg-white text-blue-700' : '' }}">
                    Master Dashboard
                    </a>
                </li>

                <li>
                    <a href="{{ route('users.index') }}"
                       class="block px-4 py-2 hover:bg-blue-600 rounded">
                        Daftar Admin
                    </a>
                </li>

                <li>
                    <a href="{{ route('branding.edit') }}"
                       class="block px-4 py-2 hover:bg-blue-600 rounded">
                        Logo Perusahaan
                    </a>
                </li>
                @endif

                <!-- ADMIN -->
                @if($role == 'admin')

                    <li>
                        <a href="{{ route('packages.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            📦 Paket
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            💰 Invoice
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            🎫 Ticket
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('users.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            ⚙️ Setting User
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('branding.edit') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            Logo Perusahaan
                        </a>
                    </li>

                @endif

                <!-- FINANCE -->
                @if($role == 'finance')

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            💰 Invoice
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                @endif

                <!-- TEKNISI -->
                @if($role == 'teknisi')

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            💰 Input Pembayaran
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            🎫 Ticket Saya
                        </a>
                    </li>

                @endif

                <!-- NOC -->
                @if($role == 'noc')

                    <li>
                        <a href="/noc" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            📡 Monitoring
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-blue-600 rounded">
                            🎫 Ticket
                        </a>
                    </li>

                @endif

            </ul>
        </div>

        <!-- BOTTOM USER INFO -->
        <div class="p-5 border-t border-blue-500/40">

            <!-- USER -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-white text-blue-700 rounded-full flex items-center justify-center text-lg font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-blue-100 capitalize">{{ $user->role }}</p>
                </div>
            </div>

            <!-- LOGOUT -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded font-semibold">
                    🚪 Logout
                </button>
            </form>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="flex-1 flex flex-col">
        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-blue-100 bg-white px-6 py-4 text-sm text-blue-700">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'ISP Billing') }}. Semua hak cipta dilindungi.</p>
        </footer>
    </div>

</div>
</body>
</html>
