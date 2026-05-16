<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ISP Billing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-blue-950 antialiased">
        <div class="min-h-screen bg-blue-50">
            <div class="grid min-h-screen lg:grid-cols-[1fr_520px]">
                <section class="hidden lg:flex bg-blue-700 text-white p-10 flex-col justify-between">
                    <div>
                        <a href="/">
                            <x-company-logo class="text-white" />
                        </a>

                        <div class="mt-16 max-w-xl">
                            <p class="text-sm font-semibold text-blue-100 uppercase">Multi Admin</p>
                            <h1 class="mt-3 text-4xl font-bold leading-tight">
                                Satu sistem biru-putih untuk billing ISP, monitoring, dan tim operasional.
                            </h1>
                            <p class="mt-5 text-blue-100">
                                Master dapat membuat banyak admin penyewa. Setiap admin dapat mengatur sub-user seperti
                                finance, NOC, dan teknisi.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div class="rounded-lg bg-white/15 p-4">
                            <p class="font-semibold">Master</p>
                            <p class="text-blue-100 mt-1">Pantau admin.</p>
                        </div>
                        <div class="rounded-lg bg-white/15 p-4">
                            <p class="font-semibold">Admin</p>
                            <p class="text-blue-100 mt-1">Kelola tenant.</p>
                        </div>
                        <div class="rounded-lg bg-white/15 p-4">
                            <p class="font-semibold">NOC</p>
                            <p class="text-blue-100 mt-1">Monitoring device.</p>
                        </div>
                    </div>
                </section>

                <main class="flex items-center justify-center p-6">
                    <div class="w-full max-w-md">
                        <div class="mb-8 lg:hidden">
                            <a href="/">
                                <x-company-logo class="text-blue-950" />
                            </a>
                        </div>

                        <div class="bg-white shadow-xl rounded-xl border border-blue-100 p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
