<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ISP Billing') }}</title>

        <!-- Premium Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --color-space-bg: #030014;
                --color-card-bg: rgba(10, 11, 25, 0.65);
                --color-glass-border: rgba(255, 255, 255, 0.08);
                --glow-indigo: rgba(99, 102, 241, 0.15);
                --glow-cyan: rgba(6, 182, 212, 0.15);
            }

            body {
                background-color: var(--color-space-bg);
                color: #f8fafc;
                font-family: 'Plus Jakarta Sans', sans-serif;
                overflow-x: hidden;
            }

            h1, h2, h3, h4, .font-heading {
                font-family: 'Outfit', sans-serif;
            }

            /* Glassmorphism Panel */
            .glass-panel {
                background: var(--color-card-bg);
                backdrop-filter: blur(16px) saturate(180%);
                -webkit-backdrop-filter: blur(16px) saturate(180%);
                border: 1px solid var(--color-glass-border);
            }

            /* Grid Background Overlay */
            .grid-bg {
                background-image: 
                    linear-gradient(to right, rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
                background-size: 50px 50px;
                background-position: center;
            }

            /* Ambient Glowing Beams */
            .glow-orb-indigo {
                background: radial-gradient(circle, var(--glow-indigo) 0%, rgba(3, 0, 20, 0) 70%);
                filter: blur(90px);
            }
            .glow-orb-cyan {
                background: radial-gradient(circle, var(--glow-cyan) 0%, rgba(3, 0, 20, 0) 70%);
                filter: blur(90px);
            }

            /* Soft Hover Lift & Glow */
            .hover-lift {
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .hover-lift:hover {
                transform: translateY(-4px);
                border-color: rgba(99, 102, 241, 0.3);
                background: rgba(255, 255, 255, 0.04);
            }
        </style>
    </head>
    <body class="grid-bg antialiased selection:bg-indigo-500 selection:text-white">
        
        <!-- Ambient Glowing Orbs -->
        <div class="absolute top-0 left-1/4 w-[500px] h-[500px] glow-orb-indigo pointer-events-none z-0"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] glow-orb-cyan pointer-events-none z-0"></div>

        <div class="min-h-screen relative z-10">
            <div class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
                
                <!-- Left Screen Banner (Desktop Only) -->
                <section class="hidden lg:flex bg-gradient-to-br from-[#060417] to-[#0D0B24] border-r border-slate-900 text-white p-16 flex-col justify-between relative overflow-hidden">
                    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <a href="/" class="inline-block hover:opacity-90 transition-opacity">
                            <x-company-logo class="text-white" :showText="true" markClass="h-10 w-10 text-white" textClass="text-lg text-white" />
                        </a>

                        <div class="mt-24 max-w-xl">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-950/60 border border-indigo-800/40 text-xs font-semibold text-cyan-400 mb-6">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                Multi-Tenant & Multi-User Architecture
                            </div>
                            <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                                Satu sistem handal untuk billing ISP, monitoring jaringan, dan tim operasional.
                            </h1>
                            <p class="mt-6 text-slate-400 leading-relaxed text-sm">
                                Master dapat membuat banyak admin penyewa (tenant). Setiap admin dapat mengelola sub-user dengan pembagian hak akses teratur untuk divisi Finance, NOC, dan Teknisi.
                            </p>
                        </div>
                    </div>

                    <!-- Roles Grid -->
                    <div class="grid grid-cols-3 gap-4 text-xs relative z-10">
                        <!-- Card 1 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift">
                            <p class="font-bold text-indigo-400">Master Admin</p>
                            <p class="text-slate-400 mt-1.5 leading-relaxed">Kelola penyewa tenant & monitoring branding sistem.</p>
                        </div>
                        <!-- Card 2 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift">
                            <p class="font-bold text-cyan-400">Admin Tenant</p>
                            <p class="text-slate-400 mt-1.5 leading-relaxed">Atur paket internet, tagihan pelanggan, & log tim.</p>
                        </div>
                        <!-- Card 3 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift">
                            <p class="font-bold text-purple-400">Tim Operasional</p>
                            <p class="text-slate-400 mt-1.5 leading-relaxed">Penyelesaian tiket lapangan, NOC & PRTG Sync.</p>
                        </div>
                    </div>
                </section>

                <!-- Right Screen Form Area -->
                <main class="flex items-center justify-center p-6 relative overflow-hidden">
                    <div class="w-full max-w-md relative z-10">
                        <!-- Brand logo visible only on mobile -->
                        <div class="mb-8 lg:hidden flex justify-center">
                            <a href="/">
                                <x-company-logo class="text-white" :showText="true" markClass="h-10 w-10 text-white" textClass="text-lg text-white" />
                            </a>
                        </div>

                        <!-- Main Glassmorphic Wrapper -->
                        <div class="glass-panel shadow-2xl rounded-2xl p-8 backdrop-blur-md">
                            {{ $slot }}
                        </div>
                    </div>
                </main>

            </div>
        </div>
    </body>
</html>

