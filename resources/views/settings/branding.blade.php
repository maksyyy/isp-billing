<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-extrabold text-slate-800 leading-tight">
            🎨 Branding & Logo Perusahaan
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header Info Panel -->
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-2xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-cyan-500/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-xs font-semibold text-cyan-400 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    Tenant Isolation Branding Enabled
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Kustomisasi Branding Sistem Anda</h1>
                <p class="mt-2 text-slate-300 text-sm leading-relaxed">
                    Setiap admin tenant memiliki profil branding terisolasi. Logo dan nama perusahaan yang Anda simpan di sini hanya akan terlihat oleh akun Anda, staf operasional Anda, serta lembar invoice cetak pelanggan Anda.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/5 border border-emerald-500/20 text-emerald-700 px-5 py-4 rounded-2xl flex items-start gap-3 shadow-sm">
                <span class="text-lg bg-emerald-500/10 text-emerald-600 w-7 h-7 rounded-xl flex items-center justify-center font-bold shrink-0">✓</span>
                <div>
                    <p class="font-bold text-sm text-emerald-800">Berhasil Disimpan</p>
                    <p class="text-xs text-emerald-700/80 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Main Form & Preview Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-6 items-start">
            
            <!-- FORM PANEL -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Pengaturan Branding
                </h3>

                <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Nama Perusahaan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Perusahaan / ISP</label>
                        <input type="text" 
                               name="company_name" 
                               value="{{ old('company_name', $companyName) }}"
                               class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                               placeholder="Contoh: My Fiber Net" 
                               required>
                        @error('company_name')
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Logo -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unggah File Logo (.png)</label>
                        <div class="relative group">
                            <input type="file" 
                                   name="company_logo" 
                                   accept="image/png,image/jpeg,image/webp"
                                   class="w-full text-xs px-4 py-3 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-600 font-semibold file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-indigo-50 file:text-indigo-600 file:hover:bg-indigo-100 file:transition-colors cursor-pointer">
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium mt-2 leading-normal">
                            Disarankan format <strong>PNG transparan</strong> dengan rasio persegi (1:1). Ukuran file maksimal 2 MB.
                        </p>
                        @error('company_logo')
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </form>
            </div>

            <!-- PREVIEW PANEL -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                    Pratinjau Tampilan Logo
                </h3>

                <p class="text-xs text-slate-400 font-medium leading-normal">
                    Berikut adalah pratinjau bagaimana logo dan nama perusahaan Anda akan ditampilkan pada berbagai elemen tata letak portal dan invoice:
                </p>

                <!-- Grid Preview styles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Preview 1: Light Theme Header / Invoice -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-slate-50 flex flex-col justify-between min-h-[140px]">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-600 font-bold text-[8px] uppercase tracking-wider mb-3">Invoice & Cetak (Light)</span>
                            <div class="p-3 bg-white border border-slate-200/60 rounded-xl inline-block">
                                <x-company-logo class="text-slate-800" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-slate-800" />
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-3">Kontras tinggi pada kertas tagihan cetak.</p>
                    </div>

                    <!-- Preview 2: Dark Theme Sidebar (Matches app.blade.php) -->
                    <div class="border border-slate-900 rounded-xl p-4 bg-[#090D1A] flex flex-col justify-between min-h-[140px] text-white">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded bg-white/10 text-slate-300 font-bold text-[8px] uppercase tracking-wider mb-3">Sidebar & Panel (Dark)</span>
                            <div class="p-3 bg-white/5 border border-white/5 rounded-xl inline-block">
                                <x-company-logo class="text-white" mark-class="h-10 w-10 text-white" text-class="text-sm text-white" />
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-3">Tampilan futuristik bersinar di kontrol panel.</p>
                    </div>

                </div>

                <!-- Active tenant indicator info -->
                <div class="p-4 bg-indigo-500/5 border border-indigo-500/10 rounded-xl flex gap-3 text-xs">
                    <span class="text-indigo-500 text-base">💡</span>
                    <p class="text-indigo-900/80 leading-relaxed font-semibold">
                        Semua sub-user (Finance, NOC, Teknisi) yang terdaftar di bawah hak kelola akun Anda secara otomatis mewarisi logo dan nama perusahaan ini untuk memastikan kesatuan identitas tim Anda.
                    </p>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
