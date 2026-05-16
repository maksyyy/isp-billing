<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ISP Billing') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-50 text-blue-950 antialiased">
    <main class="min-h-screen">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <x-company-logo class="text-blue-950" />

            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-white">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>

        <section class="mx-auto grid max-w-7xl items-center gap-10 px-6 py-16 lg:grid-cols-[1.05fr_0.95fr]">
            <div>
                <p class="text-sm font-bold uppercase text-blue-600">ISP Billing Platform</p>
                <h1 class="mt-4 max-w-3xl text-4xl font-extrabold leading-tight text-blue-950 md:text-6xl">
                    Sistem billing ISP yang rapi untuk banyak admin dan tim operasional.
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-blue-800/80">
                    Kelola pelanggan, invoice, pembayaran, ticket, NOC, finance, dan teknisi dari satu aplikasi
                    dengan tampilan biru-putih yang bersih.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}"
                       class="rounded bg-blue-600 px-5 py-3 font-semibold text-white shadow hover:bg-blue-700">
                        Mulai Kelola Billing
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded border border-blue-200 bg-white px-5 py-3 font-semibold text-blue-700 hover:border-blue-400">
                        Daftar Admin
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-blue-100 bg-white p-5 shadow-xl">
                <div class="rounded-lg bg-blue-600 p-5 text-white">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">Ringkasan Bulanan</p>
                        <span class="rounded bg-white/20 px-3 py-1 text-xs">Live</span>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded bg-white p-4 text-blue-950">
                            <p class="text-sm text-blue-700">Pelanggan</p>
                            <p class="mt-2 text-3xl font-bold">1.248</p>
                        </div>
                        <div class="rounded bg-white p-4 text-blue-950">
                            <p class="text-sm text-blue-700">Invoice</p>
                            <p class="mt-2 text-3xl font-bold">932</p>
                        </div>
                        <div class="rounded bg-white p-4 text-blue-950">
                            <p class="text-sm text-blue-700">Device Online</p>
                            <p class="mt-2 text-3xl font-bold">884</p>
                        </div>
                        <div class="rounded bg-white p-4 text-blue-950">
                            <p class="text-sm text-blue-700">Ticket Open</p>
                            <p class="mt-2 text-3xl font-bold">18</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
