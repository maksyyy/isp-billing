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

<body class="font-sans antialiased">
<div class="flex">

    <!-- SIDEBAR -->
    <div class="w-64 bg-gray-900 text-white min-h-screen flex flex-col justify-between">

        <!-- TOP MENU -->
        <div class="p-5">

            <h2 class="text-2xl font-bold mb-8 text-center">ISP Billing</h2>

            @php
                $user = auth()->user();
                $role = $user->role;
            @endphp

            <ul class="space-y-3">

                <!-- DASHBOARD -->
                
                @if($role == 'admin' || $role == 'finance')
                <li>
                    <a href="{{ route('api.page') }}"
                    class="block px-4 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('api.page') ? 'bg-gray-700' : '' }}">
                    ⚡ API Dashboard
                    </a>
                </li>
                @endif

                <!-- ADMIN -->
                @if($role == 'admin')

                    <li>
                        <a href="{{ route('packages.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            📦 Paket
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            💰 Invoice
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            🎫 Ticket
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('users.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            ⚙️ Setting User
                        </a>
                    </li>

                @endif

                <!-- FINANCE -->
                @if($role == 'finance')

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            💰 Invoice
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                @endif

                <!-- TEKNISI -->
                @if($role == 'teknisi')

                    <li>
                        <a href="{{ route('invoices.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            💰 Input Pembayaran
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('customers.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            👤 Pelanggan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            🎫 Ticket Saya
                        </a>
                    </li>

                @endif

                <!-- NOC -->
                @if($role == 'noc')

                    <li>
                        <a href="/noc" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            📡 Monitoring
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tickets.index') }}" class="block px-4 py-2 hover:bg-gray-700 rounded">
                            🎫 Ticket
                        </a>
                    </li>

                @endif

            </ul>
        </div>

        <!-- BOTTOM USER INFO -->
        <div class="p-5 border-t border-gray-700">

            <!-- USER -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center text-lg font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ $user->role }}</p>
                </div>
            </div>

            <!-- LOGOUT -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-4 py-2 bg-red-500 hover:bg-red-600 rounded">
                    🚪 Logout
                </button>
            </form>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="flex-1">
        <main>
            {{ $slot }}
        </main>
    </div>

</div>
</body>
</html>