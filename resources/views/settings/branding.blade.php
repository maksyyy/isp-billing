<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Console Control Panel
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6 p-6">
        
        <!-- Header Info Panel -->
        <div class="bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] backdrop-blur-md rounded-md p-6 lg:p-8 relative overflow-hidden shadow-sm">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#8B5CF6]/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-[#6366F1]/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-[#FAF9F6] border border-[#E4E4E7] text-[8px] font-bold text-[#111111] uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#6366F1] animate-pulse"></span>
                    Tenant Isolation Branding Enabled
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-[#111111]">Kustomisasi Branding Sistem</h1>
                <p class="mt-2 text-[#71717A] text-xs leading-relaxed font-light">
                    Setiap admin tenant memiliki profil branding terisolasi. Logo dan nama perusahaan yang Anda simpan di sini hanya akan terlihat oleh akun Anda, staf operasional Anda, serta lembar invoice cetak pelanggan Anda.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-md flex items-start gap-2">
                <span class="text-xs font-bold text-[#15803D] shrink-0 font-mono">[OK]</span>
                <div>
                    <p class="font-bold text-xs text-[#15803D] uppercase tracking-wider">Berhasil Disimpan</p>
                    <p class="text-xs text-[#15803D]/90 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Main Form & Preview Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-6 items-start">
            
            <!-- FORM PANEL -->
            <div class="app-card p-6">
                <h3 class="text-sm font-bold text-[#111111] mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#6366F1]"></span>
                    Pengaturan Branding
                </h3>

                <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Nama Perusahaan -->
                    <div>
                        <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama Perusahaan / ISP</label>
                        <input type="text" 
                               name="company_name" 
                               value="{{ old('company_name', $companyName) }}"
                               class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                               placeholder="Contoh: My Fiber Net" 
                               required>
                        @error('company_name')
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Logo -->
                    <div>
                        <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Unggah File Logo (.png)</label>
                        <div class="relative group">
                            <input type="file" 
                                   name="company_logo" 
                                   accept="image/png,image/jpeg,image/webp"
                                   class="w-full text-xs px-4 py-2 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold file:mr-4 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-[#F4F4F5] file:text-[#6366F1] file:hover:bg-[#E4E4E7] file:transition-colors cursor-pointer shadow-sm">
                        </div>
                        <p class="text-[9px] text-[#71717A] font-medium mt-2 leading-normal">
                            Disarankan format <strong>PNG transparan</strong> dengan rasio persegi (1:1). Ukuran file maksimal 2 MB.
                        </p>
                        @error('company_logo')
                            <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-minimal w-full">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- PREVIEW PANEL -->
            <div class="app-card p-6 space-y-5">
                <h3 class="text-sm font-bold text-[#111111] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                    Pratinjau Tampilan Logo
                </h3>

                <p class="text-xs text-[#71717A] font-light leading-normal">
                    Berikut adalah pratinjau bagaimana logo dan nama perusahaan Anda akan ditampilkan pada berbagai elemen tata letak portal dan invoice:
                </p>

                <!-- Grid Preview styles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Preview 1: Light Theme Header / Invoice -->
                    <div class="border border-[#E4E4E7] rounded-md p-4 bg-[#FFFFFF] flex flex-col justify-between min-h-[140px] shadow-sm">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] font-bold text-[8px] uppercase tracking-wider mb-3">Invoice & Cetak (Light)</span>
                            <div class="p-3 bg-white border border-slate-200/60 rounded-md inline-block">
                                <x-company-logo class="text-slate-800" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-slate-800" />
                            </div>
                        </div>
                        <p class="text-[9px] text-[#71717A] mt-3">Kontras tinggi pada kertas tagihan cetak.</p>
                    </div>

                    <!-- Preview 2: Sidebar (Matches white layout) -->
                    <div class="border border-[#E4E4E7] rounded-md p-4 bg-[#FFFFFF] flex flex-col justify-between min-h-[140px] text-[#111111] shadow-sm">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] font-bold text-[8px] uppercase tracking-wider mb-3">Sidebar & Panel (Light)</span>
                            <div class="p-3 bg-[#F4F4F5] border border-[#E4E4E7] rounded-md inline-block">
                                <x-company-logo class="text-[#111111]" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-[#111111]" />
                            </div>
                        </div>
                        <p class="text-[9px] text-[#71717A] mt-3">Tampilan minimalis bersih di navigasi sidebar.</p>
                    </div>

                </div>

                <!-- Active tenant indicator info -->
                <div class="p-4 bg-[#8B5CF6]/5 border border-[#8B5CF6]/20 rounded-md flex gap-3 text-xs">
                    <span class="text-[#6366F1] text-base">💡</span>
                    <p class="text-[#71717A] leading-relaxed font-light">
                        Semua sub-user (Finance, NOC, Teknisi) yang terdaftar di bawah hak kelola akun Anda secara otomatis mewarisi logo dan nama perusahaan ini untuk memastikan kesatuan identitas tim Anda.
                    </p>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
