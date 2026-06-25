<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Console Control Panel
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6 p-6">
        
        <!-- Top Info Header Card (Two-tone theme) -->
        <div class="bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] backdrop-blur-md rounded-md p-6 lg:p-8 relative overflow-hidden shadow-sm">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-[#8B5CF6]/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-56 h-56 bg-[#6366F1]/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-[#FAF9F6] border border-[#E4E4E7] text-[8px] font-bold text-[#111111] uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#6366F1] animate-pulse"></span>
                    Unified Control Console Enabled
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-[#111111]">Pusat Kendali Pengaturan</h1>
                <p class="mt-2 text-[#71717A] text-xs leading-relaxed max-w-2xl font-light">
                    Kelola nama dan logo identitas perusahaan Anda, organisasikan daftar staf operasional berserta hak akses mereka, serta konfigurasikan detail monitoring gateway PRTG dalam satu panel terintegrasi.
                </p>
            </div>
        </div>

        <!-- System Alerts -->
        @if(session('success'))
            <div class="bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-md flex items-start gap-2">
                <span class="text-xs font-bold text-[#15803D] shrink-0 font-mono">[OK]</span>
                <div>
                    <p class="font-bold text-xs text-[#15803D] uppercase tracking-wider">Berhasil Disimpan</p>
                    <p class="text-xs text-[#15803D]/90 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Tab Navigation -->
        @php
            $tabClass = "flex items-center gap-2 px-5 py-3 border-b-2 font-bold text-[10px] uppercase tracking-wider transition-all cursor-pointer border-transparent text-[#71717A] hover:text-[#111111] hover:border-[#E4E4E7]";
            $activeTabClass = "border-[#6366F1] text-[#6366F1] bg-[#6366F1]/5";
        @endphp

        <div class="app-card overflow-hidden">
            
            <!-- Tabs Strip Header -->
            <div class="flex border-b border-[#E4E4E7] bg-[#F4F4F5] px-4 overflow-x-auto shrink-0">
                <button onclick="switchTab('branding')" id="tab-btn-branding" class="{{ $tabClass }} {{ $activeTab == 'branding' ? $activeTabClass : '' }}">
                    🎨 Branding Logo
                </button>
                @if(auth()->user()->role != 'master')
                <button onclick="switchTab('staff')" id="tab-btn-staff" class="{{ $tabClass }} {{ $activeTab == 'staff' ? $activeTabClass : '' }}">
                    👥 Manajemen Staf
                </button>
                @endif
                <button onclick="switchTab('prtg')" id="tab-btn-prtg" class="{{ $tabClass }} {{ $activeTab == 'prtg' ? $activeTabClass : '' }}">
                    📡 Integrasi PRTG
                </button>
                <button onclick="switchTab('mikrotik')" id="tab-btn-mikrotik" class="{{ $tabClass }} {{ $activeTab == 'mikrotik' ? $activeTabClass : '' }}">
                    🔌 Integrasi MikroTik
                </button>
                <button onclick="switchTab('telegram')" id="tab-btn-telegram" class="{{ $tabClass }} {{ $activeTab == 'telegram' ? $activeTabClass : '' }}">
                    🤖 Integrasi Telegram
                </button>
                @if(in_array(auth()->user()->role, ['admin','noc']))
                    <button onclick="switchTab('attendance')" id="tab-btn-attendance" class="{{ $tabClass }} {{ $activeTab == 'attendance' ? $activeTabClass : '' }}">
                        📅 Kehadiran Staf
                    </button>
                @endif
            </div>

            <!-- TAB CONTENT 1: BRANDING -->
            <div id="tab-content-branding" class="p-6 {{ $activeTab == 'branding' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-8 items-start">
                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#6366F1]"></span>
                            Identitas & Branding
                        </h3>
                        <form action="{{ route('settings.branding') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama Perusahaan / ISP</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       required>
                                @error('company_name')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Wilayah Waktu (Timezone) -->
                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Wilayah Waktu (Timezone)</label>
                                <select name="timezone" class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all cursor-pointer shadow-sm" required>
                                    <option value="Asia/Jakarta" @selected(old('timezone', $timezone) == 'Asia/Jakarta')>WIB - Waktu Indonesia Barat (Asia/Jakarta)</option>
                                    <option value="Asia/Makassar" @selected(old('timezone', $timezone) == 'Asia/Makassar')>WITA - Waktu Indonesia Tengah (Asia/Makassar)</option>
                                    <option value="Asia/Jayapura" @selected(old('timezone', $timezone) == 'Asia/Jayapura')>WIT - Waktu Indonesia Timur (Asia/Jayapura)</option>
                                </select>
                                @error('timezone')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Unggah File Logo (.png)</label>
                                <input type="file" name="company_logo" accept="image/png,image/jpeg,image/webp"
                                       class="w-full text-xs px-4 py-2 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold file:mr-4 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-[#F4F4F5] file:text-[#6366F1] file:hover:bg-[#E4E4E7] file:transition-colors cursor-pointer shadow-sm">
                                <p class="text-[9px] text-[#71717A] font-medium mt-2 leading-normal">
                                    Format disarankan: PNG transparan (Rasio 1:1). Maksimal berkas 2 MB.
                                </p>
                                @error('company_logo')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-minimal w-full">
                                Simpan Branding
                            </button>
                        </form>
                    </div>

                    <!-- Previews -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                            Pratinjau Logo
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Light Preview -->
                            <div class="border border-[#E4E4E7] rounded-md p-4 bg-[#FFFFFF] flex flex-col justify-between min-h-[130px] shadow-sm">
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] font-bold text-[8px] uppercase tracking-wider mb-3">Invoice & Cetak (Light)</span>
                                    <div class="p-3 bg-white border border-slate-200/60 rounded-md inline-block">
                                        <x-company-logo class="text-slate-800" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-slate-800" />
                                    </div>
                                </div>
                                <p class="text-[9px] text-[#71717A] mt-2">Diterapkan pada cetakan tagihan PDF pelanggan.</p>
                            </div>
                            <!-- Sidebar Preview -->
                            <div class="border border-[#E4E4E7] rounded-md p-4 bg-[#FFFFFF] flex flex-col justify-between min-h-[130px] text-[#111111] shadow-sm">
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] font-bold text-[8px] uppercase tracking-wider mb-3">Sidebar & Panel (Light)</span>
                                    <div class="p-3 bg-[#F4F4F5] border border-[#E4E4E7] rounded-md inline-block">
                                        <x-company-logo class="text-[#111111]" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-[#111111]" />
                                    </div>
                                </div>
                                <p class="text-[9px] text-[#71717A] mt-2">Tampil minimalis bersih di navigasi sidebar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 2: MANAJEMEN STAF -->
            @if(auth()->user()->role != 'master')
            <div id="tab-content-staff" class="p-6 {{ $activeTab == 'staff' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
                    <div>
                        <h3 class="text-sm font-bold text-[#111111] flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#15803D]"></span>
                            {{ $staffTitle }}
                        </h3>
                        <p class="text-xs text-[#71717A] font-light mt-1">Kelola daftar pengguna sistem serta hak akses operasional tim.</p>
                    </div>
                    <!-- Add User Button -->
                    <a href="{{ route('users.create') }}" class="btn-minimal">
                        + Tambah User
                    </a>
                </div>

                <!-- Users List Table -->
                <div class="border border-[#E4E4E7] rounded-md overflow-hidden bg-transparent">
                    <div class="overflow-x-auto">
                        <table class="app-table">
                            <thead>
                                <tr>
                                    <th class="p-3">Nama & Profil</th>
                                    <th class="p-3">Alamat Email</th>
                                    <th class="p-3">Hak Akses Role</th>
                                    @if(auth()->user()->role == 'master')
                                        <th class="p-3 text-center">Jumlah Sub-Staf</th>
                                    @endif
                                    <th class="p-3 text-center w-28">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $u)
                                    <tr>
                                        <td class="p-3">
                                            <div class="flex items-center gap-3">
                                                <!-- Custom letter avatar -->
                                                <div class="w-8 h-8 bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] font-bold rounded-md flex items-center justify-center text-xs shrink-0">
                                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="block font-bold text-[#111111] text-xs truncate">{{ $u->name }}</span>
                                                    <span class="block text-[8px] text-[#71717A] font-mono mt-0.5">ID: #USR-0{{ $u->id }} @if($u->phone) | HP: {{ $u->phone }} @endif</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3 text-[#111111] text-xs font-semibold">{{ $u->email }}</td>
                                        <td class="p-3">
                                            @php
                                                $roleColors = [
                                                    'master' => 'bg-[#FEF3C7] text-[#D97706] border-[#FDE68A]',
                                                    'admin' => 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]',
                                                    'finance' => 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]',
                                                    'teknisi' => 'bg-[#F3E8FF] text-[#6B21A8] border-[#E9D5FF]',
                                                    'noc' => 'bg-[#FEE2E2] text-[#B91C1C] border-[#FECACA]',
                                                ];
                                                $badgeColor = $roleColors[$u->role] ?? 'bg-[#F4F4F5] text-[#71717A] border-[#E4E4E7]';
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider rounded border {{ $badgeColor }}">
                                                {{ $u->role }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->role == 'master')
                                            <td class="p-3 text-center">
                                                <span class="inline-flex px-2 py-0.5 bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] font-mono text-[10px] rounded">
                                                    {{ $u->sub_users_count ?? 0 }} Tim
                                                </span>
                                            </td>
                                        @endif
                                        <td class="p-3 text-center">
                                            <div class="inline-flex items-center gap-1.5 justify-center">
                                                <!-- Edit button -->
                                                <a href="{{ route('users.edit', $u->id) }}" class="btn-minimal-secondary px-2.5 py-1 text-[10px]" title="Edit Staf">
                                                    Edit
                                                </a>
                                                <!-- Delete form button -->
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex items-center justify-center px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-[10px] font-bold transition-all cursor-pointer" title="Hapus Staf">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-[#71717A] font-mono text-xs">
                                            [Belum ada staf user terdaftar]
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- TAB CONTENT 3: INTEGRASI PRTG -->
            <div id="tab-content-prtg" class="p-6 {{ $activeTab == 'prtg' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.1fr] gap-8 items-start">
                    
                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-550 bg-rose-600"></span>
                            Konfigurasi PRTG Core API
                        </h3>

                        <form action="{{ route('settings.prtg') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">PRTG Server URL</label>
                                <input type="text" name="prtg_url" value="{{ old('prtg_url', $prtgUrl) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="Contoh: http://192.168.1.10:8080 atau https://prtg.company.net">
                                <p class="text-[9px] text-[#71717A] font-medium mt-1 leading-normal">
                                    Biarkan kosong untuk menggunakan URL default dari file konfigurasi system (.env).
                                </p>
                                @error('prtg_url')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">PRTG Username</label>
                                <input type="text" name="prtg_username" value="{{ old('prtg_username', $prtgUsername) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="Contoh: prtgadmin">
                                @error('prtg_username')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">PRTG Passhash / Password</label>
                                <input type="password" name="prtg_password" value="{{ old('prtg_password', $prtgPassword) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="••••••••••••••">
                                <p class="text-[9px] text-[#71717A] font-medium mt-2 leading-normal">
                                    PRTG merekomendasikan penggunaan <strong>Passhash</strong> alih-alih password teks asli untuk keamanan API. Anda dapat menyalin Passhash ini dari profil user di PRTG.
                                </p>
                                @error('prtg_password')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-minimal w-full">
                                Simpan Kredensial PRTG
                            </button>
                        </form>
                    </div>

                    <!-- Integration Info -->
                    <div class="p-5 border border-rose-200 bg-rose-50/50 rounded-md space-y-3 shadow-sm">
                        <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                            <span>📡</span>
                            <h4>Mengapa Integrasi PRTG Dibutuhkan?</h4>
                        </div>
                        <p class="text-[#71717A] text-xs leading-relaxed font-light">
                            Kredensial API PRTG ini digunakan secara real-time oleh panel dasbor tim **NOC (Network Operations Center)** sistem Anda.
                        </p>
                        <p class="text-[#71717A] text-xs leading-relaxed font-light">
                            Setiap admin tenant dapat menghubungkan infrastruktur pemantauan jaringan mereka sendiri. Dasbor NOC Anda akan secara dinamis menyinkronkan status up/down sensor, grafik bandwidth gelombang optik, dan metrik latensi ping gateway langsung dari server PRTG yang disepakati.
                        </p>
                        <div class="p-3 bg-rose-100/50 border border-rose-200 rounded flex items-center gap-2 text-[10px] text-rose-700 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                            <span>SINKRONISASI AKTIF PER TENANT</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB CONTENT: INTEGRASI MIKROTIK -->
            <div id="tab-content-mikrotik" class="p-6 {{ $activeTab == 'mikrotik' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.1fr] gap-8 items-start">
                    
                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-600"></span>
                            Konfigurasi API MikroTik RouterOS
                        </h3>

                        <form action="{{ route('settings.mikrotik') }}" method="POST" id="mikrotik-settings-form" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Host / IP Address MikroTik</label>
                                <input type="text" id="mikrotik_host" name="mikrotik_host" value="{{ old('mikrotik_host', $mikrotikHost) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="Contoh: 192.168.88.1 atau router.myisp.net">
                                @error('mikrotik_host')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Username API</label>
                                    <input type="text" id="mikrotik_username" name="mikrotik_username" value="{{ old('mikrotik_username', $mikrotikUsername) }}"
                                           class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                           placeholder="Contoh: admin">
                                    @error('mikrotik_username')
                                        <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Port API</label>
                                    <input type="number" id="mikrotik_port" name="mikrotik_port" value="{{ old('mikrotik_port', $mikrotikPort) }}"
                                           class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                           placeholder="Default: 8728">
                                    @error('mikrotik_port')
                                        <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Password API</label>
                                <input type="password" id="mikrotik_password" name="mikrotik_password" value="{{ old('mikrotik_password', $mikrotikPassword) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="••••••••••••••">
                                @error('mikrotik_password')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3">
                                <button type="button" onclick="testMikrotikConnection()" class="flex-1 btn-minimal-secondary">
                                    ⚡ Test Koneksi
                                </button>

                                <button type="submit" class="flex-1 btn-minimal">
                                    Simpan Konfigurasi
                                </button>
                            </div>
                        </form>

                        <!-- Connection Test Result Box -->
                        <div id="test-connection-result" class="hidden p-4 border border-[#E4E4E7] text-xs font-semibold flex items-center gap-2 bg-[#F4F4F5] text-[#111111] rounded shadow-sm transition-all">
                            <!-- JS will populate -->
                        </div>
                    </div>

                    <!-- Integration Info -->
                    <div class="space-y-4">
                        <div class="p-5 border border-[#E9D5FF] bg-violet-50/50 rounded-md space-y-3 shadow-sm">
                            <div class="flex items-center gap-2 text-violet-700 font-bold text-sm">
                                <span>🔌</span>
                                <h4>Sinkronisasi Firewall Address-List</h4>
                            </div>
                            <p class="text-[#71717A] text-xs leading-relaxed font-light">
                                Layanan integrasi ini secara otomatis memetakan pelanggan terdaftar berdasarkan <strong>Alamat IP</strong> dan <strong>ID Pelanggan (4 digit angka)</strong> dari router MikroTik Anda.
                            </p>
                            <p class="text-[#71717A] text-xs leading-relaxed font-light">
                                Panel dasbor akan mendeteksi status keaktifan user dan memantau status isolir firewall secara terpusat untuk meminimalisir intervensi manual.
                            </p>
                            <div class="p-3 bg-[#F3E8FF]/50 border border-[#E9D5FF] text-[#6B21A8] rounded flex items-center gap-2 text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#8B5CF6] animate-pulse"></span>
                                <span>PPPoE / Hotspot Active Monitor</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB CONTENT: INTEGRASI TELEGRAM -->
            <div id="tab-content-telegram" class="p-6 {{ $activeTab == 'telegram' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.1fr] gap-8 items-start">
                    
                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            Konfigurasi Telegram Bot API
                        </h3>

                        <form action="{{ route('settings.telegram') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Telegram Bot Token</label>
                                <input type="password" name="telegram_bot_token" value="{{ old('telegram_bot_token', $telegramBotToken) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm"
                                       placeholder="Contoh: 1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                                <p class="text-[9px] text-[#71717A] font-medium mt-1 leading-normal">
                                    Biarkan kosong jika ingin menggunakan token bot global bawaan sistem.
                                </p>
                                @error('telegram_bot_token')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-minimal w-full">
                                Simpan Token Telegram
                            </button>
                        </form>

                        @if($telegramBotToken)
                            <div class="mt-6 pt-6 border-t border-[#E4E4E7]">
                                <h4 class="text-sm font-bold text-[#111111] mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                    Webhook Telegram Bot
                                </h4>
                                <p class="text-xs text-[#71717A] mb-4 leading-normal font-light">
                                    Aktifkan webhook agar bot Telegram dapat merespons chat secara otomatis dan menampilkan menu tombol kustom (Reply Keyboard) untuk mempermudah teknisi mendapatkan Chat ID mereka.
                                </p>
                                
                                @if(str_contains(url('/'), 'localhost') || str_contains(url('/'), '127.0.0.1'))
                                    <div class="mb-4 p-3.5 bg-[#FEF3C7] border border-[#FDE68A] rounded-md text-[11px] text-[#B45309] font-semibold leading-relaxed">
                                        ⚠️ <strong>Catatan Pengembang:</strong> Sistem mendeteksi Anda berjalan di <strong>Localhost</strong>. Telegram membutuhkan URL HTTPS publik yang aktif agar webhook dapat terhubung secara real-time. Jika Anda ingin mencobanya secara lokal, Anda dapat menggunakan aplikasi <strong>Ngrok</strong> atau <strong>LocalTunnel</strong>.
                                    </div>
                                @endif

                                <form action="{{ route('settings.telegram.webhook') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-minimal w-full">
                                        ⚡ Webhook Bot
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Integration Info / Instructions -->
                    <div class="p-5 border border-sky-200 bg-sky-50/50 rounded-md space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sky-700 font-bold text-sm">
                            <span>🤖</span>
                            <h4>Panduan Integrasi Telegram Bot</h4>
                        </div>
                        <div class="text-[#71717A] text-xs leading-relaxed space-y-3 font-light">
                            <p>Untuk menggunakan bot Telegram sendiri guna mengirim notifikasi tiket:</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Buka aplikasi Telegram dan cari `@BotFather`.</li>
                                <li>Kirim pesan `/newbot` lalu ikuti langkah pembuatan bot.</li>
                                <li>Setelah selesai, `@BotFather` akan memberikan **API Token**. Salin token tersebut dan tempel pada input di samping.</li>
                                <li>Pastikan bot Anda telah dijalankan (di-klik **Start** / dikirim pesan `/start`) oleh masing-masing teknisi Anda.</li>
                                <li>Setiap teknisi harus menambahkan **Telegram Chat ID** mereka sendiri pada menu **Profil** mereka untuk menerima pesan notifikasi.</li>
                            </ol>
                        </div>
                        <div class="p-3 bg-sky-100/50 border border-sky-200 rounded flex items-center gap-2 text-[10px] text-sky-700 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                            <span>MENDUKUNG MULTI-BOT PER ADMIN TENANT</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB CONTENT 4: KEHADIRAN STAF -->
            @if(in_array(auth()->user()->role, ['admin','noc']))
            <div id="tab-content-attendance" class="p-6 {{ $activeTab == 'attendance' ? '' : 'hidden' }}">
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-[#111111] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#6366F1]"></span>
                        Log Kehadiran Staf
                    </h3>
                    <p class="text-xs text-[#71717A] font-light mt-1">Daftar rekapitulasi kehadiran staf operasional yang melakukan scan presensi biometrik.</p>
                </div>

                <!-- Filters & Export Panel -->
                <div class="bg-[#F4F4F5] border border-[#E4E4E7] rounded-md p-4 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end shadow-sm">
                    <form action="{{ route('settings.index') }}" method="GET" class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="hidden" name="tab" value="attendance">
                        
                        <div>
                            <label class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Filter Karyawan</label>
                            <select name="employee_id" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm">
                                <option value="">-- Semua Karyawan --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" @selected($selectedEmployee == $staff->id)>{{ $staff->name }} ({{ strtoupper($staff->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Filter Bulan</label>
                            <input type="month" name="month" value="{{ $selectedMonth }}" class="w-full text-xs px-3.5 py-2 bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1]/60 focus:ring-1 focus:ring-[#6366F1]/20 rounded-md text-[#111111] font-semibold transition-all shadow-sm">
                        </div>

                        <div class="sm:col-span-2 flex gap-2 justify-end">
                            <button type="submit" class="btn-minimal-secondary text-[10px] py-2">
                                🔍 Terapkan Filter
                            </button>
                            <a href="{{ route('settings.index', ['tab' => 'attendance']) }}" class="btn-minimal-secondary text-[10px] py-2">
                                🔄 Reset
                            </a>
                        </div>
                    </form>
                    
                    <div class="flex justify-end">
                        <button onclick="exportAttendanceToCSV()" class="btn-minimal w-full md:w-auto text-[10px] py-2">
                            📥 Ekspor ke Excel (CSV)
                        </button>
                    </div>
                </div>

                <div class="border border-[#E4E4E7] rounded-md overflow-hidden bg-transparent">
                    <div class="overflow-x-auto">
                        <table class="app-table">
                            <thead>
                                <tr>
                                    <th class="p-3">Nama Staf</th>
                                    <th class="p-3">Tanggal</th>
                                    <th class="p-3">Jam Masuk</th>
                                    <th class="p-3">Foto Masuk</th>
                                    <th class="p-3">Jam Keluar</th>
                                    <th class="p-3">Foto Keluar</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                @forelse($todayAttendance as $att)
                                    <tr>
                                        <td class="p-3">
                                            <div class="flex items-center gap-3">
                                                @if($att->user->face_photo)
                                                    <img src="{{ asset('storage/' . $att->user->face_photo) }}" class="w-8 h-8 object-cover rounded-full border border-[#E4E4E7] shadow-sm shrink-0">
                                                @else
                                                    <div class="w-8 h-8 bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] font-bold rounded-full flex items-center justify-center text-xs shadow-sm shrink-0">
                                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="block font-bold text-[#111111] text-xs staff-name">{{ $att->user->name }}</span>
                                                    <span class="block text-[8px] text-[#71717A] font-bold uppercase tracking-wider mt-0.5 staff-role">{{ $att->user->role }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3 text-[#111111] font-mono text-xs whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($att->tanggal)->format('d-m-Y') }}
                                        </td>
                                        <td class="p-3 text-[#15803D] font-mono font-bold text-xs">
                                            {{ \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') }} WIB
                                        </td>
                                        <td class="p-3">
                                            <a href="{{ asset('storage/' . $att->foto_masuk) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $att->foto_masuk) }}" class="w-10 h-10 object-cover rounded border border-[#E4E4E7] shadow-sm hover:scale-110 transition shrink-0">
                                            </a>
                                        </td>
                                        <td class="p-3 text-rose-600 font-mono font-bold text-xs">
                                            {{ $att->jam_keluar ? \Carbon\Carbon::parse($att->jam_keluar)->format('H:i') . ' WIB' : '-' }}
                                        </td>
                                        <td class="p-3">
                                            @if($att->foto_keluar)
                                                <a href="{{ asset('storage/' . $att->foto_keluar) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $att->foto_keluar) }}" class="w-10 h-10 object-cover rounded border border-[#E4E4E7] shadow-sm hover:scale-110 transition shrink-0">
                                                </a>
                                            @else
                                                <span class="text-[#71717A] text-xs italic">Belum Keluar</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="attendance-status {{ $att->status === 'Hadir' ? 'status-badge-active' : 'status-badge-inactive' }}">
                                                {{ $att->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-[#71717A] font-mono text-xs">
                                            [Belum ada staf yang melakukan presensi sesuai filter yang dipilih]
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>

    </div>

    <!-- TABS NAVIGATION MECHANISM (JAVASCRIPT) -->
    <script>
        function switchTab(tabId) {
            // Target elements
            const tabs = ['branding', 'staff', 'prtg', 'attendance', 'mikrotik', 'telegram'];
            
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                
                if (!btn || !content) return;

                if (t === tabId) {
                    btn.className = "flex items-center gap-2 px-5 py-3 border-b-2 font-bold text-[10px] uppercase tracking-wider transition-all cursor-pointer border-[#6366F1] text-[#6366F1] bg-[#6366F1]/5";
                    content.classList.remove('hidden');
                } else {
                    btn.className = "flex items-center gap-2 px-5 py-3 border-b-2 font-bold text-[10px] uppercase tracking-wider transition-all cursor-pointer border-transparent text-[#71717A] hover:text-[#111111] hover:border-[#E4E4E7]";
                    content.classList.add('hidden');
                }
            });

            // Update URL hash without jumping page
            window.history.pushState(null, null, '?tab=' + tabId);
        }

        function testMikrotikConnection() {
            const host = document.getElementById('mikrotik_host').value;
            const username = document.getElementById('mikrotik_username').value;
            const password = document.getElementById('mikrotik_password').value;
            const port = document.getElementById('mikrotik_port').value || 8728;
            
            const resultBox = document.getElementById('test-connection-result');
            resultBox.classList.remove('hidden', 'bg-[#DCFCE7]', 'border-[#BBF7D0]', 'text-[#15803D]', 'bg-[#FEE2E2]', 'border-[#FECACA]', 'text-[#B91C1C]');
            resultBox.classList.add('bg-[#FFFFFF]', 'border-[#E4E4E7]', 'text-[#111111]');
            resultBox.innerHTML = '⏳ Menghubungi router MikroTik...';
            resultBox.classList.remove('hidden');

            fetch('/api/mikrotik/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ host, username, password, port })
            })
            .then(res => res.json())
            .then(data => {
                resultBox.classList.remove('bg-[#FFFFFF]', 'border-[#E4E4E7]', 'text-[#111111]');
                if (data.success) {
                    resultBox.classList.add('bg-[#DCFCE7]', 'border-[#BBF7D0]', 'text-[#15803D]');
                    resultBox.innerHTML = '🟢 ' + data.message;
                } else {
                    resultBox.classList.add('bg-[#FEE2E2]', 'border-[#FECACA]', 'text-[#B91C1C]');
                    resultBox.innerHTML = '🔴 ' + data.message;
                }
            })
            .catch(err => {
                resultBox.classList.remove('bg-[#FFFFFF]', 'border-[#E4E4E7]', 'text-[#111111]');
                resultBox.classList.add('bg-[#FEE2E2]', 'border-[#FECACA]', 'text-[#B91C1C]');
                resultBox.innerHTML = '🔴 Terjadi kesalahan komunikasi dengan server.';
                console.error(err);
            });
        }

        function exportAttendanceToCSV() {
            const rows = document.querySelectorAll('#attendance-table-body tr');
            if (rows.length === 0 || rows[0].querySelector('td[colspan]')) {
                alert('Tidak ada data presensi yang bisa diekspor.');
                return;
            }

            // UTF-8 BOM to prevent MS Excel character encoding issues
            let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
            csvContent += "Nama Staf,Role,Tanggal,Jam Masuk,Jam Keluar,Status Kehadiran\n";

            rows.forEach(row => {
                const name = row.querySelector('.staff-name')?.innerText.trim() || '';
                const role = row.querySelector('.staff-role')?.innerText.trim() || '';
                const date = row.cells[1]?.innerText.trim() || '';
                const jamMasuk = row.cells[2]?.innerText.trim() || '';
                const jamKeluar = row.cells[4]?.innerText.trim() || '';
                const status = row.querySelector('.attendance-status')?.innerText.trim() || '';

                // Escape comma and double quotes
                const cleanName = `"${name.replace(/"/g, '""')}"`;
                const cleanRole = `"${role.replace(/"/g, '""')}"`;
                const cleanDate = `"${date.replace(/"/g, '""')}"`;
                const cleanJamMasuk = `"${jamMasuk.replace(/"/g, '""')}"`;
                const cleanJamKeluar = `"${jamKeluar.replace(/"/g, '""')}"`;
                const cleanStatus = `"${status.replace(/"/g, '""')}"`;

                csvContent += `${cleanName},${cleanRole},${cleanDate},${cleanJamMasuk},${cleanJamKeluar},${cleanStatus}\n`;
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            
            // Format file name based on active filter if any
            const empNameSelect = document.querySelector('select[name="employee_id"]');
            const empName = empNameSelect && empNameSelect.selectedIndex > 0 ? empNameSelect.options[empNameSelect.selectedIndex].text.split('(')[0].trim() : 'Semua';
            const monthVal = document.querySelector('input[name="month"]')?.value || 'all';
            const fileName = `rekap_kehadiran_${empName.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_${monthVal}.csv`;
            
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</x-app-layout>
