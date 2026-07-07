<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Halaman Akses ISP Billing Platform - Silakan masuk atau mendaftar untuk mengelola billing pelanggan, router gateway, monitoring PRTG, dan layanan internet Anda.">
        <link rel="canonical" href="{{ url()->current() }}">

        <title>{{ config('app.name', 'ISP Billing') }}</title>

        <!-- Premium Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&family=Instrument+Serif:ital,wght@0,400;0,900;1,400;1,900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --color-space-bg: #FAF9F6;
                --color-card-bg: #FFFFFF;
                --color-glass-border: #E4E4E7;
                --glow-indigo: rgba(99, 102, 241, 0.03);
                --glow-cyan: rgba(139, 92, 246, 0.03);
            }

            body {
                background-color: var(--color-space-bg);
                color: #111111;
                font-family: 'Geist', 'Plus Jakarta Sans', sans-serif;
                overflow-x: hidden;
            }

            h1, h2, h3, h4, .font-heading {
                font-family: 'Geist', sans-serif;
                font-weight: 600;
                letter-spacing: -0.02em;
            }

            /* Glassmorphism Panel */
            .glass-panel {
                background: var(--color-card-bg);
                border: 1px solid var(--color-glass-border);
                box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
            }

            /* Grid Background Overlay */
            .grid-bg {
                background-image: 
                    linear-gradient(to right, rgba(0, 0, 0, 0.015) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(0, 0, 0, 0.015) 1px, transparent 1px);
                background-size: 50px 50px;
                background-position: center;
            }

            /* Ambient Glowing Beams */
            .glow-orb-indigo {
                background: radial-gradient(circle, var(--glow-indigo) 0%, rgba(250, 249, 246, 0) 70%);
                filter: blur(90px);
            }
            .glow-orb-cyan {
                background: radial-gradient(circle, var(--glow-cyan) 0%, rgba(250, 249, 246, 0) 70%);
                filter: blur(90px);
            }

            /* Soft Hover Lift & Glow */
            .hover-lift {
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .hover-lift:hover {
                transform: translateY(-4px);
                border-color: rgba(99, 102, 241, 0.2);
                box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.06);
            }
        </style>
    </head>
    <body class="grid-bg antialiased selection:bg-[#6366F1] selection:text-white">
        
        <!-- Ambient Glowing Orbs -->
        <div class="absolute top-0 left-1/4 w-[500px] h-[500px] glow-orb-indigo pointer-events-none z-0"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] glow-orb-cyan pointer-events-none z-0"></div>

        <div class="min-h-screen relative z-10">
            <div class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
                
                <!-- Left Screen Banner (Desktop Only) -->
                <section class="hidden lg:flex bg-gradient-to-br from-[#FAF9F6] to-[#FFFFFF] border-r border-[#E4E4E7] text-[#111111] p-16 flex-col justify-between relative overflow-hidden">
                    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <a href="/" aria-label="{{ config('app.name', 'ISP Billing') }} Home" class="inline-block hover:opacity-90 transition-opacity">
                            <x-company-logo class="text-[#111111]" :showText="true" markClass="h-10 w-10 text-slate-800" textClass="text-lg text-[#111111] font-heading font-bold" />
                        </a>

                        <div class="mt-24 max-w-xl">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#6366F1]/10 border border-[#6366F1]/20 text-xs font-semibold text-[#6366F1] mb-6">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#6366F1] animate-pulse"></span>
                                Multi-Tenant & Multi-User Architecture
                            </div>
                            <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                                Satu sistem handal untuk billing ISP, monitoring jaringan, dan tim operasional.
                            </h1>
                            <p class="mt-6 text-[#71717A] leading-relaxed text-sm">
                                Master dapat membuat banyak admin penyewa (tenant). Setiap admin dapat mengelola sub-user dengan pembagian hak akses teratur untuk divisi Finance, NOC, dan Teknisi.
                            </p>
                        </div>
                    </div>

                    <!-- Roles Grid -->
                    <div class="grid grid-cols-3 gap-4 text-xs relative z-10">
                        <!-- Card 1 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift bg-white">
                            <p class="font-bold text-[#6366F1]">Master Admin</p>
                            <p class="text-[#71717A] mt-1.5 leading-relaxed">Kelola penyewa tenant & monitoring branding sistem.</p>
                        </div>
                        <!-- Card 2 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift bg-white">
                            <p class="font-bold text-[#8B5CF6]">Admin Tenant</p>
                            <p class="text-[#71717A] mt-1.5 leading-relaxed">Atur paket internet, tagihan pelanggan, & log tim.</p>
                        </div>
                        <!-- Card 3 -->
                        <div class="glass-panel p-4 rounded-xl hover-lift bg-white">
                            <p class="font-bold text-[#EC4899]">Tim Operasional</p>
                            <p class="text-[#71717A] mt-1.5 leading-relaxed">Penyelesaian tiket lapangan, NOC & PRTG Sync.</p>
                        </div>
                    </div>
                </section>

                <!-- Right Screen Form Area -->
                <main class="flex items-center justify-center p-6 relative overflow-hidden">
                    <div class="w-full max-w-md relative z-10">
                        <!-- Brand logo visible only on mobile -->
                        <div class="mb-8 lg:hidden flex justify-center">
                            <a href="/">
                                <x-company-logo class="text-[#111111]" :showText="true" markClass="h-10 w-10 text-slate-800" textClass="text-lg text-[#111111] font-heading font-bold" />
                            </a>
                        </div>

                        <!-- Main Glassmorphic Wrapper -->
                        <div class="glass-panel shadow-xl rounded-2xl p-8 backdrop-blur-md bg-white">
                            {{ $slot }}
                        </div>
                    </div>
                </main>

            </div>
        </div>
    </body>
</html>

