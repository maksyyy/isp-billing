<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ISP Billing Platform: Solusi manajemen billing otomatis dan monitoring jaringan terintegrasi PRTG. Kelola pelanggan, tagihan bulanan, router, tiket, dan performa NOC secara cerdas dalam satu portal.">
    <meta name="keywords" content="isp billing, billing isp, rt rw net, mikrotik billing, prtg integration, manajemen pelanggan isp, tagihan isp otomatis, router gateway, noc monitoring">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('app.name', 'ISP Billing') }} - Intelligent Billing & Network Monitoring">
    <meta property="og:description" content="Solusi manajemen billing otomatis dan monitoring jaringan terintegrasi PRTG. Kelola pelanggan, tagihan bulanan, router, tiket, dan performa NOC secara cerdas dalam satu portal.">
    <meta property="og:image" content="{{ asset('company-logo.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ config('app.name', 'ISP Billing') }} - Intelligent Billing & Network Monitoring">
    <meta property="twitter:description" content="Solusi manajemen billing otomatis dan monitoring jaringan terintegrasi PRTG. Kelola pelanggan, tagihan bulanan, router, tiket, dan performa NOC secara cerdas dalam satu portal.">
    <meta property="twitter:image" content="{{ asset('company-logo.png') }}">

    <title>{{ config('app.name', 'ISP Billing') }} - Intelligent Billing & Network Monitoring</title>
    
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
            --glow-purple: rgba(168, 85, 247, 0.03);
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

        /* Soft Hover Lift & Glow */
        .hover-lift {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hover-lift:hover {
            transform: translateY(-6px);
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.06);
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
        .glow-orb-purple {
            background: radial-gradient(circle, var(--glow-purple) 0%, rgba(250, 249, 246, 0) 70%);
            filter: blur(90px);
        }

        /* Heartbeat Radar Signal Pulse */
        .ping-node {
            position: relative;
        }
        .ping-node::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: currentColor;
            border-radius: 50%;
            animation: ping-ring 2.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
        }

        @keyframes ping-ring {
            0% { transform: scale(0.5); opacity: 1; }
            80%, 100% { transform: scale(3); opacity: 0; }
        }

        /* Gradient Highlight Border */
        .gradient-border-wrap {
            position: relative;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            padding: 1px;
            border-radius: 16px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #FAF9F6;
        }
        ::-webkit-scrollbar-thumb {
            background: #E4E4E7;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #D4D4D8;
        }
    </style>
    <!-- Google AdSense (Lazy Loaded) -->
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            let adsenseLoaded = false;
            function loadAdSense() {
                if (adsenseLoaded) return;
                adsenseLoaded = true;
                
                // Remove event listeners
                window.removeEventListener('scroll', loadAdSense);
                window.removeEventListener('mousemove', loadAdSense);
                window.removeEventListener('touchstart', loadAdSense);
                
                // Load script dynamically
                let script = document.createElement('script');
                script.async = true;
                script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7137805859216450';
                script.crossOrigin = 'anonymous';
                document.head.appendChild(script);
            }
            
            // Trigger load on user interaction
            window.addEventListener('scroll', loadAdSense, { passive: true });
            window.addEventListener('mousemove', loadAdSense, { passive: true });
            window.addEventListener('touchstart', loadAdSense, { passive: true });
            
            // Or fallback load after 8 seconds of idle time
            setTimeout(loadAdSense, 8000);
        });
    </script>
</head>
<body class="grid-bg min-h-screen antialiased selection:bg-[#6366F1] selection:text-white">

    <!-- Ambient Glowing Orbs Background -->
    <div class="absolute top-0 left-1/4 w-[500px] h-[500px] glow-orb-indigo pointer-events-none z-0"></div>
    <div class="absolute top-[600px] right-1/4 w-[600px] h-[600px] glow-orb-cyan pointer-events-none z-0"></div>
    <div class="absolute top-[1400px] left-1/3 w-[500px] h-[500px] glow-orb-purple pointer-events-none z-0"></div>
    <div class="absolute bottom-[200px] right-10 w-[500px] h-[500px] glow-orb-indigo pointer-events-none z-0"></div>

    <!-- 1. Floating Premium Navigation Bar -->
    <header class="fixed top-5 left-1/2 -translate-x-1/2 z-50 w-[94%] max-w-7xl">
        <div class="glass-panel px-6 py-4 flex items-center justify-between rounded-2xl shadow-xl backdrop-blur-md">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" aria-label="{{ config('app.name', 'ISP Billing') }} Home" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                <x-company-logo class="text-[#111111]" :showText="true" markClass="h-10 w-10 text-slate-800" textClass="text-lg text-[#111111] font-heading font-bold" />
            </a>
            
            <!-- Quick Anchors -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#71717A]">
                <a href="#features" class="hover:text-[#6366F1] transition-colors">Fitur Utama</a>
                <a href="#monitoring" class="hover:text-[#6366F1] transition-colors">PRTG Status</a>
                <a href="#pricing" class="hover:text-[#6366F1] transition-colors">Paket Harga</a>
                <a href="#faq" class="hover:text-[#6366F1] transition-colors">FAQ</a>
            </nav>

            <!-- Access CTA -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="rounded-xl bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] hover:opacity-95 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-lg shadow-[#6366F1]/10 transition-all">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold text-[#71717A] hover:text-[#111111] transition-colors">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-xl bg-[#6366F1] hover:bg-[#5558DD] border border-[#6366F1]/40 px-5 py-2.5 text-xs sm:text-sm font-bold text-white transition-all shadow-md shadow-[#6366F1]/10">
                                Daftar Admin
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- 2. Cosmic Hero Section -->
    <main class="relative z-10 pt-40 pb-20 px-6 max-w-7xl mx-auto">
        <section class="text-center max-w-4xl mx-auto mb-20">
            <!-- Modern Tech Pill -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#6366F1]/10 border border-[#6366F1]/20 text-xs font-semibold text-[#6366F1] mb-8 shadow-inner shadow-[#6366F1]/5">
                <span class="w-2 h-2 rounded-full bg-[#6366F1] animate-pulse"></span>
                <span>Automated ISP Core Billing & Live Monitoring</span>
            </div>

            <!-- Main Punchline -->
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6">
                Kelola Billing & Monitoring ISP 
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#6366F1] via-[#8B5CF6] to-[#EC4899]">
                    Satu Portal Cerdas.
                </span>
            </h1>

            <p class="text-base sm:text-lg md:text-xl text-[#71717A] max-w-2xl mx-auto mb-10 leading-relaxed">
                Kelola ribuan pelanggan, otomatisasi invoice tagihan bulanan, sinkronisasi profil router, serta monitoring kualitas jaringan PRTG terpadu untuk tim Admin, Finance, NOC, dan Teknisi.
            </p>

            <!-- Action CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] font-bold hover:opacity-95 transition-all shadow-lg shadow-[#6366F1]/20 flex items-center justify-center gap-2 text-white">
                    Mulai Kelola Billing <span class="text-lg leading-none">&rarr;</span>
                </a>
                <a href="#features" class="w-full sm:w-auto px-8 py-4 rounded-xl border border-[#E4E4E7] bg-white hover:bg-[#F4F4F5] text-[#111111] font-semibold transition-all shadow-sm flex items-center justify-center">
                    Pelajari Fitur
                </a>
            </div>
        </section>

        <!-- 3. Interactive Floating Dashboard Mockup -->
        <section class="max-w-5xl mx-auto mb-32 relative">
            <div class="gradient-border-wrap shadow-xl">
                <!-- Mockup App Frame -->
                <div class="glass-panel rounded-[15px] overflow-hidden">
                    
                    <!-- App Frame Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-[#E4E4E7] bg-[#F4F4F5]">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="ml-4 text-xs font-semibold text-[#71717A] tracking-wider uppercase">Live Dashboard Mockup</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-[11px] font-bold text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ping-node text-emerald-500"></span>
                                Live System Active
                            </span>
                        </div>
                    </div>

                    <!-- Mockup App Content -->
                    <div class="p-6 bg-[#FAF9F6]">
                        
                        <!-- Grid 4 Stats Box (Replika Data Asli) -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <!-- Card 1 -->
                            <div class="bg-white border border-[#E4E4E7] p-4 rounded-xl text-left shadow-xs">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#71717A]">Pelanggan Aktif</p>
                                <div class="flex items-baseline justify-between mt-2">
                                    <p class="text-2xl sm:text-3xl font-extrabold text-[#111111]">1.248</p>
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded">+8.3%</span>
                                </div>
                            </div>
                            <!-- Card 2 -->
                            <div class="bg-white border border-[#E4E4E7] p-4 rounded-xl text-left shadow-xs">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#71717A]">Invoice Bulan Ini</p>
                                <div class="flex items-baseline justify-between mt-2">
                                    <p class="text-2xl sm:text-3xl font-extrabold text-[#111111]">932</p>
                                    <span class="text-[10px] font-bold text-cyan-600 bg-cyan-50 border border-cyan-100 px-1.5 py-0.5 rounded">99.8%</span>
                                </div>
                            </div>
                            <!-- Card 3 -->
                            <div class="bg-white border border-[#E4E4E7] p-4 rounded-xl text-left shadow-xs">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#71717A]">Total Uptime NOC</p>
                                <div class="flex items-baseline justify-between mt-2">
                                    <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600">99.98%</p>
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded">SLA</span>
                                </div>
                            </div>
                            <!-- Card 4 -->
                            <div class="bg-white border border-[#E4E4E7] p-4 rounded-xl text-left shadow-xs">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#71717A]">Tiket Terbuka</p>
                                <div class="flex items-baseline justify-between mt-2">
                                    <p class="text-2xl sm:text-3xl font-extrabold text-rose-600">18</p>
                                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-1.5 py-0.5 rounded">Prioritas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sparkline Wave Graph (SVG) -->
                        <div class="relative w-full h-[220px] bg-white border border-[#E4E4E7] rounded-xl p-4 flex flex-col justify-between overflow-hidden shadow-xs">
                            <!-- Background dashed grid lines -->
                            <div class="absolute inset-0 flex flex-col justify-between py-6 px-4 opacity-5 pointer-events-none">
                                <div class="border-b border-dashed border-slate-400 w-full"></div>
                                <div class="border-b border-dashed border-slate-400 w-full"></div>
                                <div class="border-b border-dashed border-slate-400 w-full"></div>
                            </div>

                            <!-- Real-time SVG Network Stream Graph -->
                            <svg class="absolute bottom-4 left-0 w-full h-[150px] pointer-events-none" viewBox="0 0 1000 150" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="gradient-line" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#6366F1" stop-opacity="1"/>
                                        <stop offset="100%" stop-color="#8B5CF6" stop-opacity="0.3"/>
                                    </linearGradient>
                                    <linearGradient id="area-glow" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#6366F1" stop-opacity="0.12"/>
                                        <stop offset="100%" stop-color="#6366F1" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <path d="M0,150 L0,70 Q 120,40 250,90 T 500,60 T 750,110 L 900,45 L 1000,55 L 1000,150 Z" fill="url(#area-glow)" />
                                <path d="M0,70 Q 120,40 250,90 T 500,60 T 750,110 L 900,45 L 1000,55" fill="none" stroke="url(#gradient-line)" stroke-width="4" />
                                
                                <circle cx="900" cy="45" r="5" fill="#6366F1" />
                                <circle cx="900" cy="45" r="12" fill="none" stroke="#6366F1" stroke-width="1.5" class="animate-ping" style="transform-origin: 900px 45px;" />
                            </svg>

                            <div class="flex justify-between items-center text-xs text-[#71717A] font-semibold z-10">
                                <span>Main Uplink: Fiber Backbone 10 Gbps (Active Transit)</span>
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#6366F1] inline-block animate-pulse"></span> RX/TX Syncing</span>
                            </div>

                            <div class="flex justify-between text-[10px] text-[#71717A] font-bold z-10 pt-2 border-t border-[#E4E4E7]">
                                <span>09:00 WIB</span>
                                <span>11:00 WIB</span>
                                <span>13:00 WIB</span>
                                <span>15:00 WIB (Current)</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Bento Box Feature Grid Section -->
        <section id="features" class="py-20 border-t border-[#E4E4E7]">
            <div class="text-center mb-16">
                <span class="text-xs text-[#6366F1] font-bold uppercase tracking-widest">Sistem Multi-Role</span>
                <h2 class="text-3xl md:text-5xl font-extrabold mt-3 mb-4">Fitur Tangguh Tim Operasional</h2>
                <p class="text-[#71717A] max-w-xl mx-auto text-sm sm:text-base">
                    Sistem dirancang dengan pembagian hak akses teratur sesuai fungsi kerja masing-masing divisi ISP.
                </p>
            </div>

            <!-- Bento Layout Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Bento Box Card 1 (Wide Feature - Finance) -->
                <div class="glass-panel p-8 rounded-2xl md:col-span-2 flex flex-col justify-between min-h-[320px] hover-lift">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center text-[#6366F1] mb-6">
                            <!-- Money / Invoice Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.536-.22-2.121-.659-1.171-.879-1.171-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0M9.75 21.75H14.25m-6-19.5H16.5" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-3">Finance: Otomatisasi Tagihan</h3>
                        <p class="text-sm text-[#71717A] leading-relaxed max-w-xl">
                            Kelola pembuatan tagihan (Invoice) secara otomatis setiap bulannya. Lacak status pembayaran, rekapitulasi income kas masuk, cetak kuitansi massal, dan filter piutang secara instan.
                        </p>
                    </div>
                    <div class="mt-8 flex items-center justify-between border-t border-[#E4E4E7] pt-4 text-xs text-[#71717A]">
                        <span>Laporan Pembayaran Real-Time</span>
                        <a href="{{ route('login') }}" aria-label="Eksplorasi Modul Finance ISP Billing" class="font-bold text-[#6366F1] hover:text-[#8B5CF6] transition-colors flex items-center gap-1">Eksplorasi Modul &rarr;</a>
                    </div>
                </div>

                <!-- Bento Box Card 2 (NOC / Monitoring) -->
                <div class="glass-panel p-8 rounded-2xl flex flex-col justify-between min-h-[320px] hover-lift">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-[#8B5CF6]/10 border border-[#8B5CF6]/20 flex items-center justify-center text-[#8B5CF6] mb-6">
                            <!-- Server / Graph Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-3">NOC: Integrasi PRTG</h3>
                        <p class="text-sm text-[#71717A] leading-relaxed">
                            Hubungkan sistem penagihan dengan software monitoring jaringan PRTG. Pantau status keaktifan perangkat OLT, core switch, dan router gateway langsung dari dasbor Anda.
                        </p>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="px-2 py-1 rounded bg-[#F4F4F5] border border-[#E4E4E7] text-[10px] font-bold text-[#71717A]">Sensor Status</span>
                        <span class="px-2 py-1 rounded bg-[#F4F4F5] border border-[#E4E4E7] text-[10px] font-bold text-[#71717A]">Traffic Ingestion</span>
                    </div>
                </div>

                <!-- Bento Box Card 3 (Teknisi / Support) -->
                <div class="glass-panel p-8 rounded-2xl flex flex-col justify-between min-h-[320px] hover-lift">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 mb-6">
                            <!-- Wrench Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.653 2.653 0 0 0 21 17.25l-5.83-5.83m0 0a2.906 2.906 0 1 1-3.701-3.701m3.701 3.701a2.903 2.903 0 0 1-3.701-3.7m0 0L5.67 3.67A2.653 2.653 0 0 0 3 7.25l5.83 5.83m0 0a2.906 2.906 0 1 1 3.701 3.701m-3.701-3.701a2.903 2.903 0 0 0 3.701 3.7" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-3">Teknisi: Tiket & Work order</h3>
                        <p class="text-sm text-[#71717A] leading-relaxed">
                            Menerima disposisi aduan gangguan pelanggan langsung melalui aplikasi. Teknisi dapat memperbarui status perbaikan dari lapangan secara instan setelah masalah selesai teratasi.
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs text-[#71717A]">Response time terjamin</span>
                    </div>
                </div>

                <!-- Bento Box Card 4 (Wide Feature - Admin) -->
                <div class="glass-panel p-8 rounded-2xl md:col-span-2 flex flex-col justify-between min-h-[320px] hover-lift">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 mb-6">
                            <!-- Shield Check Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-3">Admin: Kontrol Sentral & Branding</h3>
                        <p class="text-sm text-[#71717A] leading-relaxed max-w-xl">
                            Mengatur paket internet, memantau log aktivitas semua tim operasional, serta mengubah logo dan nama ISP secara dinamis untuk kustomisasi penuh (Branding Management).
                        </p>
                    </div>
                    <div class="mt-8 flex items-center justify-between border-t border-[#E4E4E7] pt-4 text-xs text-[#71717A]">
                        <span>Multi-Tenant & Multi-User</span>
                        <a href="{{ route('register') }}" aria-label="Registrasi Admin Baru Portal ISP Billing" class="font-bold text-[#8B5CF6] hover:text-[#6366F1] transition-colors flex items-center gap-1">Registrasi Admin Baru &rarr;</a>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. PRTG Network Live Monitoring Simulator Section -->
        <section id="monitoring" class="py-20 border-t border-[#E4E4E7]">
            <div class="grid grid-cols-1 lg:grid-cols-[0.8fr_1.2fr] items-center gap-12">
                <div>
                    <span class="text-xs text-[#6366F1] font-bold uppercase tracking-widest">Network Integrations</span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold mt-3 mb-6">Integrasi PRTG Core Monitoring</h2>
                    <p class="text-[#71717A] text-sm sm:text-base leading-relaxed mb-6">
                        ISP Billing terhubung langsung dengan sistem sensor monitoring jaringan PRTG. Anda bisa mendeteksi perangkat mati (offline) atau mengalami packet loss ekstrim sebelum pelanggan mengirimkan tiket keluhan.
                    </p>
                    
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs mt-0.5">&check;</span>
                            <span class="text-sm text-[#3F3F46]"><b>Auto-Mapping:</b> Menghubungkan sensor port ke profil pelanggan.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs mt-0.5">&check;</span>
                            <span class="text-sm text-[#3F3F46]"><b>Sub-second Alerts:</b> Notifikasi instan saat perangkat backbone down.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs mt-0.5">&check;</span>
                            <span class="text-sm text-[#3F3F46]"><b>PRTG Sync APIs:</b> Mendukung endpoint kustom untuk visualisasi sensor.</span>
                        </li>
                    </ul>
                </div>

                <!-- Simulation Status Panel -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden bg-white">
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#6366F1]/5 rounded-full blur-3xl"></div>
                    
                    <div class="flex items-center justify-between border-b border-[#E4E4E7] pb-4 mb-6">
                        <h4 class="font-bold text-sm">Simulasi PRTG Sensor Node</h4>
                        <span class="text-[10px] text-[#71717A] bg-[#F4F4F5] border border-[#E4E4E7] px-2 py-1 rounded">Update berkala (30s)</span>
                    </div>

                    <!-- Sensor Status List -->
                    <div class="space-y-3">
                        <!-- Sensor 1 -->
                        <div class="flex items-center justify-between bg-[#FAF9F6] border border-[#E4E4E7] px-4 py-3 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 text-emerald-400 ping-node"></span>
                                <div>
                                    <p class="text-xs font-bold">CORE-ROUTER-SBY</p>
                                    <p class="text-[9px] text-[#71717A]">Router Mikrotik CCR2116</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-0.5 rounded">UP (0.9ms)</span>
                        </div>
                        <!-- Sensor 2 -->
                        <div class="flex items-center justify-between bg-[#FAF9F6] border border-[#E4E4E7] px-4 py-3 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 text-emerald-400 ping-node"></span>
                                <div>
                                    <p class="text-xs font-bold">OLT-GPON-CENTRAL</p>
                                    <p class="text-[9px] text-[#71717A]">OLT ZTE C320 - 16 Port</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-0.5 rounded">UP (1.2ms)</span>
                        </div>
                        <!-- Sensor 3 -->
                        <div class="flex items-center justify-between bg-[#FAF9F6] border border-[#E4E4E7] px-4 py-3 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 text-amber-400 ping-node"></span>
                                <div>
                                    <p class="text-xs font-bold">DIST-SWITCH-SDA</p>
                                    <p class="text-[9px] text-[#71717A]">Switch Cisco Catalyst 3850</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2.5 py-0.5 rounded">WARN (42ms)</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Pricing Section (Interactive Billing Switcher) -->
        <section id="pricing" class="py-20 border-t border-[#E4E4E7]">
            <div class="text-center mb-12">
                <span class="text-xs text-[#6366F1] font-bold uppercase tracking-widest">Paket Layanan</span>
                <h2 class="text-3xl md:text-5xl font-extrabold mt-3 mb-4">Investasi Terjangkau untuk Skala ISP Anda</h2>
                <p class="text-[#71717A] max-w-xl mx-auto text-sm sm:text-base mb-8">
                    Pilih paket yang paling sesuai dengan jumlah subscriber Anda. Tingkatkan kapasitas kapan saja seiring bertumbuhnya bisnis.
                </p>

                <!-- Billing Cycle Switch Toggle Button -->
                <div class="inline-flex items-center gap-3 p-1 rounded-xl bg-[#F4F4F5] border border-[#E4E4E7]">
                    <button id="toggle-monthly" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#6366F1] text-white transition-all shadow-md">
                        Bulanan
                    </button>
                    <button id="toggle-yearly" class="px-4 py-2 text-xs font-bold rounded-lg text-[#71717A] hover:text-[#111111] transition-all">
                        Tahunan (Hemat 20%)
                    </button>
                </div>
            </div>

            <!-- Pricing Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Plan 1 -->
                <div class="glass-panel p-8 rounded-2xl flex flex-col justify-between hover-lift">
                    <div>
                        <span class="text-xs font-bold text-[#71717A] uppercase tracking-widest">Startup ISP</span>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold" id="price-startup">Rp 299k</span>
                            <span class="text-xs text-[#71717A]" id="period-startup">/ bulan</span>
                        </div>
                        <p class="text-xs text-[#71717A] mt-2 leading-relaxed">Cocok untuk pengusaha RT/RW Net baru atau ISP berskala kecil.</p>
                        
                        <div class="border-t border-[#E4E4E7] my-6"></div>
                        
                        <ul class="space-y-3.5 text-xs text-[#3F3F46]">
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Hingga 250 Pelanggan</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> 2 Router Gateway</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Fitur Billing Otomatis</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Akun NOC & Teknisi Standar</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" aria-label="Mulai Sekarang dengan Paket Startup ISP" class="mt-8 w-full py-3 rounded-xl bg-[#FAF9F6] border border-[#E4E4E7] hover:bg-[#F4F4F5] text-center font-bold text-xs text-[#111111] transition-colors">
                        Mulai Sekarang
                    </a>
                </div>

                <!-- Plan 2 (Featured) -->
                <div class="relative glass-panel p-8 rounded-2xl flex flex-col justify-between hover-lift border-[#6366F1]/50 shadow-lg shadow-[#6366F1]/5">
                    <!-- Ribbon Accent -->
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] text-[10px] font-extrabold uppercase tracking-wider text-white shadow-md">
                        Paling Populer
                    </div>

                    <div>
                        <span class="text-xs font-bold text-[#6366F1] uppercase tracking-widest">Growth ISP</span>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold" id="price-growth">Rp 599k</span>
                            <span class="text-xs text-[#71717A]" id="period-growth">/ bulan</span>
                        </div>
                        <p class="text-xs text-[#71717A] mt-2 leading-relaxed">Solusi lengkap untuk ISP lokal berkembang dengan banyak tim operasional.</p>
                        
                        <div class="border-t border-[#E4E4E7] my-6"></div>
                        
                        <ul class="space-y-3.5 text-xs text-[#3F3F46]">
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Hingga 1.000 Pelanggan</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> 5 Router Gateway</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Integrasi Penuh Sensor PRTG</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Smart Helpdesk & Tiket Operasional</li>
                            <li class="flex items-center gap-2"><span class="text-[#6366F1] font-bold">&check;</span> Custom Branding Logo & Nama ISP</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" aria-label="Coba Demo Gratis Paket Growth ISP" class="mt-8 w-full py-3 rounded-xl bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] hover:opacity-95 text-center font-bold text-xs text-white transition-all shadow-md shadow-[#6366F1]/10">
                        Coba Demo Gratis
                    </a>
                </div>

                <!-- Plan 3 -->
                <div class="glass-panel p-8 rounded-2xl flex flex-col justify-between hover-lift">
                    <div>
                        <span class="text-xs font-bold text-[#8B5CF6] uppercase tracking-widest">Enterprise ISP</span>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold" id="price-enterprise">Rp 999k</span>
                            <span class="text-xs text-[#71717A]" id="period-enterprise">/ bulan</span>
                        </div>
                        <p class="text-xs text-[#71717A] mt-2 leading-relaxed">Kapasitas tanpa batas untuk ISP besar skala regional / nasional.</p>
                        
                        <div class="border-t border-[#E4E4E7] my-6"></div>
                        
                        <ul class="space-y-3.5 text-xs text-[#3F3F46]">
                            <li class="flex items-center gap-2"><span class="text-[#8B5CF6] font-bold">&check;</span> Pelanggan Tanpa Batas</li>
                            <li class="flex items-center gap-2"><span class="text-[#8B5CF6] font-bold">&check;</span> Router Gateway Unlimited</li>
                            <li class="flex items-center gap-2"><span class="text-[#8B5CF6] font-bold">&check;</span> Dukungan Premium 24/7 SLA</li>
                            <li class="flex items-center gap-2"><span class="text-[#8B5CF6] font-bold">&check;</span> Skema Cluster High-Availability</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" aria-label="Hubungi Sales Paket Enterprise ISP" class="mt-8 w-full py-3 rounded-xl bg-[#FAF9F6] border border-[#E4E4E7] hover:bg-[#F4F4F5] text-center font-bold text-xs text-[#111111] transition-colors">
                        Hubungi Sales
                    </a>
                </div>
            </div>
        </section>

        <!-- 7. Interactive FAQ Accordion Section -->
        <section id="faq" class="py-20 border-t border-[#E4E4E7] max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <span class="text-xs text-[#6366F1] font-bold uppercase tracking-widest">Bantuan</span>
                <h2 class="text-3xl md:text-5xl font-extrabold mt-3 mb-4">Pertanyaan yang Sering Diajukan</h2>
            </div>

            <!-- FAQ Accordion List -->
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="glass-panel rounded-2xl overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex items-center justify-between text-left font-bold text-sm sm:text-base hover:bg-[#F4F4F5]/50 transition-colors">
                        <span>Bagaimana sistem billing otomatis bekerja untuk memutus pelanggan yang telat membayar?</span>
                        <span class="faq-icon text-xl text-[#71717A] leading-none transition-transform">&plus;</span>
                    </button>
                    <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-out px-6 bg-[#FAF9F6]/50">
                        <p class="py-5 text-xs sm:text-sm text-[#71717A] leading-relaxed">
                            Sistem secara rutin memeriksa jatuh tempo invoice setiap hari. Jika tagihan melewati tanggal jatuh tempo yang ditentukan (misalnya H+1), server penagihan akan mengirim API request ke AAA RADIUS / Mikrotik untuk mengubah profil speed pelanggan menjadi isolir atau nonaktif secara otomatis tanpa intervensi manual.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="glass-panel rounded-2xl overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex items-center justify-between text-left font-bold text-sm sm:text-base hover:bg-[#F4F4F5]/50 transition-colors">
                        <span>Apakah saya bisa mencustomize logo kuitansi dan nama portal pelanggan?</span>
                        <span class="faq-icon text-xl text-[#71717A] leading-none transition-transform">&plus;</span>
                    </button>
                    <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-out px-6 bg-[#FAF9F6]/50">
                        <p class="py-5 text-xs sm:text-sm text-[#71717A] leading-relaxed">
                            Ya, tentu saja. Melalui menu Branding Management di akun Super Admin / Master Admin, Anda dapat mengunggah file logo perusahaan (.png) dan nama ISP Anda. Logo tersebut otomatis teraplikasikan di berkas invoice cetak, dashboard login, serta footer e-mail notifikasi ke pelanggan.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="glass-panel rounded-2xl overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex items-center justify-between text-left font-bold text-sm sm:text-base hover:bg-[#F4F4F5]/50 transition-colors">
                        <span>Bagaimana integrasi dengan PRTG Network Monitoring berjalan?</span>
                        <span class="faq-icon text-xl text-[#71717A] leading-none transition-transform">&plus;</span>
                    </button>
                    <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-out px-6 bg-[#FAF9F6]/50">
                        <p class="py-5 text-xs sm:text-sm text-[#71717A] leading-relaxed">
                            Aplikasi kami menggunakan PRTG API token untuk menarik status sensor perangkat secara berkala. Dashboard NOC di dalam sistem billing Anda akan menampilkan metrik real-time seperti Ping RTT, Bandwidth In/Out, serta status keaktifan perangkat jaringan utama.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- 8. Premium Minimalist Footer -->
    <footer class="relative z-10 border-t border-[#E4E4E7] bg-white py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <a href="{{ url('/') }}" aria-label="{{ config('app.name', 'ISP Billing') }} Home" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                <x-company-logo class="text-[#111111]" :showText="true" markClass="h-8 w-8 text-slate-800" textClass="text-sm text-[#111111]" />
            </a>
            
            <div class="flex flex-wrap justify-center gap-8 text-xs text-[#71717A] font-semibold">
                <a href="#features" class="hover:text-[#111111] transition-colors">Fitur</a>
                <a href="#monitoring" class="hover:text-[#111111] transition-colors">PRTG</a>
                <a href="#pricing" class="hover:text-[#111111] transition-colors">Harga</a>
                <a href="{{ route('login') }}" class="hover:text-[#111111] transition-colors">Portal Admin</a>
            </div>

            <div class="text-xs text-[#A1A1AA]">
                &copy; {{ date('Y') }} {{ config('app.name', 'ISP Billing') }} Platform. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <!-- Interactive Pricing & FAQ Accordion Engine (Lightweight Vanilla JS) -->
    <script>
        // FAQ Accordion Handler
        const faqToggles = document.querySelectorAll('.faq-toggle');
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const content = toggle.nextElementSibling;
                const icon = toggle.querySelector('.faq-icon');
                
                // Close other open FAQ items if needed
                document.querySelectorAll('.faq-content').forEach(item => {
                    if(item !== content) {
                        item.style.maxHeight = null;
                        item.previousElementSibling.querySelector('.faq-icon').innerHTML = '&plus;';
                        item.previousElementSibling.querySelector('.faq-icon').style.transform = 'rotate(0deg)';
                    }
                });

                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    icon.innerHTML = '&plus;';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.innerHTML = '&minus;';
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        });

        // Pricing Switcher Handler
        const toggleMonthlyBtn = document.getElementById('toggle-monthly');
        const toggleYearlyBtn = document.getElementById('toggle-yearly');
        
        const priceStartup = document.getElementById('price-startup');
        const priceGrowth = document.getElementById('price-growth');
        const priceEnterprise = document.getElementById('price-enterprise');

        const periodStartup = document.getElementById('period-startup');
        const periodGrowth = document.getElementById('period-growth');
        const periodEnterprise = document.getElementById('period-enterprise');

        toggleMonthlyBtn.addEventListener('click', () => {
            // UI Toggle States
            toggleMonthlyBtn.classList.add('bg-[#6366F1]', 'text-white');
            toggleMonthlyBtn.classList.remove('text-[#71717A]');
            toggleYearlyBtn.classList.remove('bg-[#6366F1]', 'text-white');
            toggleYearlyBtn.classList.add('text-[#71717A]');

            // Values (Monthly)
            priceStartup.innerText = 'Rp 299k';
            priceGrowth.innerText = 'Rp 599k';
            priceEnterprise.innerText = 'Rp 999k';

            periodStartup.innerText = '/ bulan';
            periodGrowth.innerText = '/ bulan';
            periodEnterprise.innerText = '/ bulan';
        });

        toggleYearlyBtn.addEventListener('click', () => {
            // UI Toggle States
            toggleYearlyBtn.classList.add('bg-[#6366F1]', 'text-white');
            toggleYearlyBtn.classList.remove('text-[#71717A]');
            toggleMonthlyBtn.classList.remove('bg-[#6366F1]', 'text-white');
            toggleMonthlyBtn.classList.add('text-[#71717A]');

            // Values (Yearly with 20% discount applied: e.g. Rp 299k * 12 * 0.8 = Rp 2.87M, or show monthly equivalent: Rp 239k)
            priceStartup.innerText = 'Rp 239k';
            priceGrowth.innerText = 'Rp 479k';
            priceEnterprise.innerText = 'Rp 799k';

            periodStartup.innerText = '/ bulan (ditagih tahunan)';
            periodGrowth.innerText = '/ bulan (ditagih tahunan)';
            periodEnterprise.innerText = '/ bulan (ditagih tahunan)';
        });
    </script>
</body>
</html>

