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
                <button onclick="switchTab('staff')" id="tab-btn-staff" class="{{ $tabClass }} {{ $activeTab == 'staff' ? $activeTabClass : $inactiveTabClass }}">
                    👥 Manajemen Staf
                </button>
                <button onclick="switchTab('prtg')" id="tab-btn-prtg" class="{{ $tabClass }} {{ $activeTab == 'prtg' ? $activeTabClass : $inactiveTabClass }}">
                    📡 Integrasi PRTG
                </button>
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
                                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">ID: #USR-0{{ $u->id }}</span>
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

        </div>

    </div>

    <!-- TABS NAVIGATION MECHANISM (JAVASCRIPT) -->
    <script>
        function switchTab(tabId) {
            // Target elements
            const tabs = ['branding', 'staff', 'prtg'];
            
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
    </script>
</x-app-layout>
