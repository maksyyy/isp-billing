<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-extrabold text-slate-800 leading-tight">
            ⚙️ Pengaturan Sistem
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        
        <!-- Top Info Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-indigo-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-56 h-56 bg-cyan-500/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-xs font-semibold text-cyan-400 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    Unified Control Console Enabled
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Pusat Kendali Pengaturan</h1>
                <p class="mt-2 text-slate-300 text-sm leading-relaxed">
                    Kelola nama dan logo identitas perusahaan Anda, organisasikan daftar staf operasional berserta hak akses mereka, serta konfigurasikan detail monitoring gateway PRTG dalam satu panel terintegrasi.
                </p>
            </div>
        </div>

        <!-- System Alerts -->
        @if(session('success'))
            <div class="bg-emerald-500/5 border border-emerald-500/20 text-emerald-700 px-5 py-4 rounded-2xl flex items-start gap-3 shadow-sm">
                <span class="text-lg bg-emerald-500/10 text-emerald-600 w-7 h-7 rounded-xl flex items-center justify-center font-bold shrink-0">✓</span>
                <div>
                    <p class="font-bold text-sm text-emerald-800">Berhasil Disimpan</p>
                    <p class="text-xs text-emerald-700/80 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Tab Navigation Buttons -->
        @php
            $tabClass = "flex items-center gap-2 px-5 py-3 border-b-2 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer";
            $activeTabClass = "border-indigo-600 text-indigo-600 bg-indigo-50/20";
            $inactiveTabClass = "border-transparent text-slate-400 hover:text-slate-700 hover:border-slate-300";
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <!-- Tabs Strip Header -->
            <div class="flex border-b border-slate-200/80 bg-slate-50/50 px-4 overflow-x-auto shrink-0">
                <button onclick="switchTab('branding')" id="tab-btn-branding" class="{{ $tabClass }} {{ $activeTab == 'branding' ? $activeTabClass : $inactiveTabClass }}">
                    🎨 Branding Logo
                </button>
                @if(auth()->user()->role != 'master')
                <button onclick="switchTab('staff')" id="tab-btn-staff" class="{{ $tabClass }} {{ $activeTab == 'staff' ? $activeTabClass : $inactiveTabClass }}">
                    👥 Manajemen Staf
                </button>
                @endif
                <button onclick="switchTab('prtg')" id="tab-btn-prtg" class="{{ $tabClass }} {{ $activeTab == 'prtg' ? $activeTabClass : $inactiveTabClass }}">
                    📡 Integrasi PRTG
                </button>
                <button onclick="switchTab('mikrotik')" id="tab-btn-mikrotik" class="{{ $tabClass }} {{ $activeTab == 'mikrotik' ? $activeTabClass : $inactiveTabClass }}">
                    🔌 Integrasi MikroTik
                </button>
                <button onclick="switchTab('telegram')" id="tab-btn-telegram" class="{{ $tabClass }} {{ $activeTab == 'telegram' ? $activeTabClass : $inactiveTabClass }}">
                    🤖 Integrasi Telegram
                </button>
                @if(in_array(auth()->user()->role, ['admin','noc']))
                    <button onclick="switchTab('attendance')" id="tab-btn-attendance" class="{{ $tabClass }} {{ $activeTab == 'attendance' ? $activeTabClass : $inactiveTabClass }}">
                        📅 Kehadiran Staf
                    </button>
                @endif
            </div>

            <!-- TAB CONTENT 1: BRANDING -->
            <div id="tab-content-branding" class="p-6 {{ $activeTab == 'branding' ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-8 items-start">
                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            Identitas & Branding
                        </h3>
                        <form action="{{ route('settings.branding') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Perusahaan / ISP</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       required>
                                @error('company_name')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Wilayah Waktu (Timezone) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Wilayah Waktu (Timezone)</label>
                                <select name="timezone" class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all cursor-pointer" required>
                                    <option value="Asia/Jakarta" @selected(old('timezone', $timezone) == 'Asia/Jakarta')>WIB - Waktu Indonesia Barat (Asia/Jakarta)</option>
                                    <option value="Asia/Makassar" @selected(old('timezone', $timezone) == 'Asia/Makassar')>WITA - Waktu Indonesia Tengah (Asia/Makassar)</option>
                                    <option value="Asia/Jayapura" @selected(old('timezone', $timezone) == 'Asia/Jayapura')>WIT - Waktu Indonesia Timur (Asia/Jayapura)</option>
                                </select>
                                @error('timezone')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unggah File Logo (.png)</label>
                                <input type="file" name="company_logo" accept="image/png,image/jpeg,image/webp"
                                       class="w-full text-xs px-4 py-3 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-600 font-semibold file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-indigo-50 file:text-indigo-600 file:hover:bg-indigo-100 file:transition-colors cursor-pointer">
                                <p class="text-[10px] text-slate-400 font-medium mt-2 leading-normal">
                                    Format disarankan: PNG transparan (Rasio 1:1). Maksimal berkas 2 MB.
                                </p>
                                @error('company_logo')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <span>Simpan Branding</span>
                            </button>
                        </form>
                    </div>

                    <!-- Previews -->
                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                            Pratinjau Logo
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Light Preview -->
                            <div class="border border-slate-100 rounded-xl p-4 bg-slate-50 flex flex-col justify-between min-h-[130px]">
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-600 font-bold text-[8px] uppercase tracking-wider mb-3">Invoice & Cetak (Light)</span>
                                    <div class="p-3 bg-white border border-slate-200/60 rounded-xl inline-block">
                                        <x-company-logo class="text-slate-800" mark-class="h-10 w-10 text-slate-800" text-class="text-sm text-slate-800" />
                                    </div>
                                </div>
                                <p class="text-[9px] text-slate-400 mt-2">Diterapkan pada cetakan tagihan PDF pelanggan.</p>
                            </div>
                            <!-- Dark Preview -->
                            <div class="border border-slate-900 rounded-xl p-4 bg-[#090D1A] flex flex-col justify-between min-h-[130px] text-white">
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded bg-white/10 text-slate-300 font-bold text-[8px] uppercase tracking-wider mb-3">Sidebar & Panel (Dark)</span>
                                    <div class="p-3 bg-white/5 border border-white/5 rounded-xl inline-block">
                                        <x-company-logo class="text-white" mark-class="h-10 w-10 text-white" text-class="text-sm text-white" />
                                    </div>
                                </div>
                                <p class="text-[9px] text-slate-400 mt-2">Tampil bersinar di layout portal manajemen.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 2: MANAJEMEN STAF -->
            @if(auth()->user()->role != 'master')
            <div id="tab-content-staff" class="p-6 {{ $activeTab == 'staff' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            {{ $staffTitle }}
                        </h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Kelola daftar pengguna sistem serta hak akses operasional tim.</p>
                    </div>
                    <!-- Add User Button -->
                    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah User</span>
                    </a>
                </div>

                <!-- Users List Table -->
                <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                    <th class="px-5 py-3.5">Nama & Profil</th>
                                    <th class="px-5 py-3.5">Alamat Email</th>
                                    <th class="px-5 py-3.5">Hak Akses Role</th>
                                    @if(auth()->user()->role == 'master')
                                        <th class="px-5 py-3.5 text-center">Jumlah Sub-Staf</th>
                                    @endif
                                    <th class="px-5 py-3.5 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($users as $u)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <!-- Custom letter avatar -->
                                                <div class="w-9 h-9 bg-indigo-100 text-indigo-700 font-bold rounded-xl flex items-center justify-center text-sm shadow-sm shrink-0">
                                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="block font-bold text-slate-800 text-sm truncate">{{ $u->name }}</span>
                                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">ID: #USR-0{{ $u->id }} @if($u->phone) | HP: {{ $u->phone }} @endif</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 text-sm font-semibold">{{ $u->email }}</td>
                                        <td class="px-5 py-4">
                                            @php
                                                $roleColors = [
                                                    'master' => 'bg-amber-500/5 text-amber-600 border-amber-500/10',
                                                    'admin' => 'bg-indigo-500/5 text-indigo-600 border-indigo-500/10',
                                                    'finance' => 'bg-emerald-500/5 text-emerald-600 border-emerald-500/10',
                                                    'teknisi' => 'bg-cyan-500/5 text-cyan-600 border-cyan-500/10',
                                                    'noc' => 'bg-rose-500/5 text-rose-600 border-rose-500/10',
                                                ];
                                                $badgeColor = $roleColors[$u->role] ?? 'bg-slate-500/5 text-slate-600 border-slate-500/10';
                                            @endphp
                                            <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border {{ $badgeColor }}">
                                                {{ $u->role }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->role == 'master')
                                            <td class="px-5 py-4 text-center">
                                                <span class="inline-flex px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 font-bold text-xs rounded-md">
                                                    {{ $u->sub_users_count ?? 0 }} Tim
                                                </span>
                                            </td>
                                        @endif
                                        <td class="px-5 py-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Edit button -->
                                                <a href="{{ route('users.edit', $u->id) }}" class="p-2 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 rounded-xl transition-all" title="Edit Staf">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </a>
                                                <!-- Delete form button -->
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="p-2 border border-rose-100 hover:border-rose-300 bg-white hover:bg-rose-50 text-rose-500 hover:text-rose-600 rounded-xl transition-all cursor-pointer" title="Hapus Staf">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center text-slate-400 font-semibold">
                                            <div class="text-3xl mb-2">👥</div>
                                            Belum ada staf user terdaftar.
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
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            Konfigurasi PRTG Core API
                        </h3>

                        <form action="{{ route('settings.prtg') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PRTG Server URL</label>
                                <input type="text" name="prtg_url" value="{{ old('prtg_url', $prtgUrl) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="Contoh: http://192.168.1.10:8080 atau https://prtg.company.net">
                                <p class="text-[9px] text-slate-400 font-medium mt-1 leading-normal">
                                    Biarkan kosong untuk menggunakan URL default dari file konfigurasi system (.env).
                                </p>
                                @error('prtg_url')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PRTG Username</label>
                                <input type="text" name="prtg_username" value="{{ old('prtg_username', $prtgUsername) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="Contoh: prtgadmin">
                                @error('prtg_username')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PRTG Passhash / Password</label>
                                <input type="password" name="prtg_password" value="{{ old('prtg_password', $prtgPassword) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="••••••••••••••">
                                <p class="text-[10px] text-slate-400 font-medium mt-2 leading-normal">
                                    PRTG merekomendasikan penggunaan <strong>Passhash</strong> alih-alih password teks asli untuk keamanan API. Anda dapat menyalin Passhash ini dari profil user di PRTG.
                                </p>
                                @error('prtg_password')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Simpan Kredensial PRTG</span>
                            </button>
                        </form>
                    </div>

                    <!-- Integration Info -->
                    <div class="p-5 border border-rose-500/10 bg-rose-500/5 rounded-2xl space-y-3">
                        <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                            <span>📡</span>
                            <h4>Mengapa Integrasi PRTG Dibutuhkan?</h4>
                        </div>
                        <p class="text-slate-600 text-xs leading-relaxed font-semibold">
                            Kredensial API PRTG ini digunakan secara real-time oleh panel dasbor tim **NOC (Network Operations Center)** sistem Anda.
                        </p>
                        <p class="text-slate-600 text-xs leading-relaxed font-semibold">
                            Setiap admin tenant dapat menghubungkan infrastruktur pemantauan jaringan mereka sendiri. Dasbor NOC Anda akan secara dinamis menyinkronkan status up/down sensor, grafik bandwidth gelombang optik, dan metrik latensi ping gateway langsung dari server PRTG yang disepakati.
                        </p>
                        <div class="p-3 bg-white border border-rose-100 rounded-xl flex items-center gap-2 text-[10px] text-rose-600 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
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
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span>
                            Konfigurasi API MikroTik RouterOS
                        </h3>

                        <form action="{{ route('settings.mikrotik') }}" method="POST" id="mikrotik-settings-form" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Host / IP Address MikroTik</label>
                                <input type="text" id="mikrotik_host" name="mikrotik_host" value="{{ old('mikrotik_host', $mikrotikHost) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="Contoh: 192.168.88.1 atau router.myisp.net">
                                @error('mikrotik_host')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username API</label>
                                    <input type="text" id="mikrotik_username" name="mikrotik_username" value="{{ old('mikrotik_username', $mikrotikUsername) }}"
                                           class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                           placeholder="Contoh: admin">
                                    @error('mikrotik_username')
                                        <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Port API</label>
                                    <input type="number" id="mikrotik_port" name="mikrotik_port" value="{{ old('mikrotik_port', $mikrotikPort) }}"
                                           class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                           placeholder="Default: 8728">
                                    @error('mikrotik_port')
                                        <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password API</label>
                                <input type="password" id="mikrotik_password" name="mikrotik_password" value="{{ old('mikrotik_password', $mikrotikPassword) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="••••••••••••••">
                                @error('mikrotik_password')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3">
                                <button type="button" onclick="testMikrotikConnection()" class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 cursor-pointer">
                                    <span>⚡ Test Koneksi</span>
                                </button>

                                <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                                    <span>Simpan Konfigurasi</span>
                                </button>
                            </div>
                        </form>

                        <!-- Connection Test Result Box -->
                        <div id="test-connection-result" class="hidden p-4 rounded-xl border text-xs font-semibold flex items-center gap-2 transition-all">
                            <!-- JS will populate -->
                        </div>
                    </div>

                    <!-- Integration Info -->
                    <div class="space-y-4">
                        <div class="p-5 border border-violet-500/10 bg-violet-500/5 rounded-2xl space-y-3">
                            <div class="flex items-center gap-2 text-violet-700 font-bold text-sm">
                                <span>🔌</span>
                                <h4>Sinkronisasi Firewall Address-List</h4>
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed font-semibold">
                                Layanan integrasi ini secara otomatis memetakan pelanggan terdaftar berdasarkan <strong>Alamat IP</strong> dan <strong>ID Pelanggan (4 digit angka)</strong> dari router MikroTik Anda.
                            </p>
                            <p class="text-slate-600 text-xs leading-relaxed font-semibold">
                                Panel dasbor akan mendeteksi status keaktifan user dan memantau status isolir firewall secara terpusat untuk meminimalisir intervensi manual.
                            </p>
                            <div class="p-3 bg-white border border-violet-100 rounded-xl flex items-center gap-2 text-[10px] text-violet-600 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
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
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            Konfigurasi Telegram Bot API
                        </h3>

                        <form action="{{ route('settings.telegram') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Telegram Bot Token</label>
                                <input type="password" name="telegram_bot_token" value="{{ old('telegram_bot_token', $telegramBotToken) }}"
                                       class="w-full text-sm px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all"
                                       placeholder="Contoh: 1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                                <p class="text-[10px] text-slate-400 font-medium mt-1 leading-normal">
                                    Biarkan kosong jika ingin menggunakan token bot global bawaan sistem.
                                </p>
                                @error('telegram_bot_token')
                                    <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Simpan Token Telegram</span>
                            </button>
                        </form>

                        @if($telegramBotToken)
                            <div class="mt-6 pt-6 border-t border-slate-200">
                                <h4 class="text-sm font-bold text-slate-800 mb-2 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                    Webhook Telegram Bot
                                </h4>
                                <p class="text-xs text-slate-500 mb-4 leading-normal">
                                    Aktifkan webhook agar bot Telegram dapat merespons chat secara otomatis dan menampilkan menu tombol kustom (Reply Keyboard) untuk mempermudah teknisi mendapatkan Chat ID mereka.
                                </p>
                                
                                @if(str_contains(url('/'), 'localhost') || str_contains(url('/'), '127.0.0.1'))
                                    <div class="mb-4 p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800 font-semibold leading-relaxed">
                                        ⚠️ <strong>Catatan Pengembang:</strong> Sistem mendeteksi Anda berjalan di <strong>Localhost</strong>. Telegram membutuhkan URL HTTPS publik yang aktif agar webhook dapat terhubung secara real-time. Jika Anda ingin mencobanya secara lokal, Anda dapat menggunakan aplikasi <strong>Ngrok</strong> atau <strong>LocalTunnel</strong>.
                                    </div>
                                @endif

                                <form action="{{ route('settings.telegram.webhook') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-lg shadow-sky-600/10 cursor-pointer">
                                        ⚡ Aktifkan Webhook Bot
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Integration Info / Instructions -->
                    <div class="p-5 border border-sky-500/10 bg-sky-500/5 rounded-2xl space-y-4">
                        <div class="flex items-center gap-2 text-sky-700 font-bold text-sm">
                            <span>🤖</span>
                            <h4>Panduan Integrasi Telegram Bot</h4>
                        </div>
                        <div class="text-slate-600 text-xs leading-relaxed space-y-3 font-semibold">
                            <p>Untuk menggunakan bot Telegram sendiri guna mengirim notifikasi tiket:</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Buka aplikasi Telegram dan cari `@BotFather`.</li>
                                <li>Kirim pesan `/newbot` lalu ikuti langkah pembuatan bot.</li>
                                <li>Setelah selesai, `@BotFather` akan memberikan **API Token**. Salin token tersebut dan tempel pada input di samping.</li>
                                <li>Pastikan bot Anda telah dijalankan (di-klik **Start** / dikirim pesan `/start`) oleh masing-masing teknisi Anda.</li>
                                <li>Setiap teknisi harus menambahkan **Telegram Chat ID** mereka sendiri pada menu **Profil** mereka untuk menerima pesan notifikasi.</li>
                            </ol>
                        </div>
                        <div class="p-3 bg-white border border-sky-100 rounded-xl flex items-center gap-2 text-[10px] text-sky-600 font-bold">
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
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Log Kehadiran Staf
                    </h3>
                    <p class="text-xs text-slate-400 font-medium mt-1">Daftar rekapitulasi kehadiran staf operasional yang melakukan scan presensi biometrik.</p>
                </div>

                <!-- Filters & Export Panel -->
                <div class="bg-slate-50 border border-slate-200/60 rounded-2xl p-4 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <form action="{{ route('settings.index') }}" method="GET" class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="hidden" name="tab" value="attendance">
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Filter Karyawan</label>
                            <select name="employee_id" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500/30 rounded-xl font-semibold text-slate-700 transition-all">
                                <option value="">-- Semua Karyawan --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" @selected($selectedEmployee == $staff->id)>{{ $staff->name }} ({{ strtoupper($staff->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Filter Bulan</label>
                            <input type="month" name="month" value="{{ $selectedMonth }}" class="w-full text-xs px-3.5 py-2 bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500/30 rounded-xl font-semibold text-slate-700 transition-all">
                        </div>

                        <div class="sm:col-span-2 flex gap-2 justify-end">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                                🔍 Terapkan Filter
                            </button>
                            <a href="{{ route('settings.index', ['tab' => 'attendance']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-[10px] uppercase tracking-wider rounded-xl transition-all cursor-pointer">
                                🔄 Reset
                            </a>
                        </div>
                    </form>
                    
                    <div class="flex justify-end">
                        <button onclick="exportAttendanceToCSV()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md shadow-emerald-600/10 cursor-pointer w-full md:w-auto justify-center">
                            📥 Ekspor ke Excel (CSV)
                        </button>
                    </div>
                </div>

                <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                    <th class="px-5 py-3.5">Nama Staf</th>
                                    <th class="px-5 py-3.5">Tanggal</th>
                                    <th class="px-5 py-3.5">Jam Masuk</th>
                                    <th class="px-5 py-3.5">Foto Masuk</th>
                                    <th class="px-5 py-3.5">Jam Keluar</th>
                                    <th class="px-5 py-3.5">Foto Keluar</th>
                                    <th class="px-5 py-3.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body" class="divide-y divide-slate-100">
                                @forelse($todayAttendance as $att)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($att->user->face_photo)
                                                    <img src="{{ asset('storage/' . $att->user->face_photo) }}" class="w-9 h-9 object-cover rounded-full border shadow-sm shrink-0">
                                                @else
                                                    <div class="w-9 h-9 bg-indigo-100 text-indigo-700 font-bold rounded-full flex items-center justify-center text-sm shadow-sm shrink-0">
                                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="block font-bold text-slate-800 text-sm staff-name">{{ $att->user->name }}</span>
                                                    <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5 staff-role">{{ $att->user->role }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 font-semibold text-xs whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($att->tanggal)->format('d-m-Y') }}
                                        </td>
                                        <td class="px-5 py-4 text-green-700 font-bold text-sm">
                                            {{ \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <a href="{{ asset('storage/' . $att->foto_masuk) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $att->foto_masuk) }}" class="w-10 h-10 object-cover rounded border shadow-sm hover:scale-110 transition shrink-0">
                                            </a>
                                        </td>
                                        <td class="px-5 py-4 text-rose-700 font-bold text-sm">
                                            {{ $att->jam_keluar ? \Carbon\Carbon::parse($att->jam_keluar)->format('H:i') : '-' }}
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($att->foto_keluar)
                                                <a href="{{ asset('storage/' . $att->foto_keluar) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $att->foto_keluar) }}" class="w-10 h-10 object-cover rounded border shadow-sm hover:scale-110 transition shrink-0">
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-xs italic">Belum Keluar</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm attendance-status {{ $att->status === 'Hadir' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $att->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-12 text-center text-slate-400 font-semibold">
                                            <div class="text-3xl mb-2">📅</div>
                                            Belum ada staf yang melakukan presensi sesuai filter yang dipilih.
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
                
                if (t === tabId) {
                    btn.classList.add('border-indigo-600', 'text-indigo-600', 'bg-indigo-50/20');
                    btn.classList.remove('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:border-slate-300');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-indigo-600', 'text-indigo-600', 'bg-indigo-50/20');
                    btn.classList.add('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:border-slate-300');
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
            resultBox.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800');
            resultBox.classList.add('bg-slate-50', 'border-slate-200', 'text-slate-600');
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
                resultBox.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-600');
                if (data.success) {
                    resultBox.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
                    resultBox.innerHTML = '🟢 ' + data.message;
                } else {
                    resultBox.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
                    resultBox.innerHTML = '🔴 ' + data.message;
                }
            })
            .catch(err => {
                resultBox.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-600');
                resultBox.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
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
