<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Pusat Evaluasi & Presentasi Proyek
        </h2>
    </x-slot>

    <!-- Interactive Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <div class="max-w-6xl mx-auto space-y-6 p-6">
        
        <!-- Header Info Card -->
        <div class="bg-gradient-to-r from-indigo-500/10 via-purple-500/5 to-transparent border border-indigo-500/20 backdrop-blur-md rounded-md p-6 relative overflow-hidden shadow-xs">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-indigo-500/5 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-[8px] font-bold text-indigo-700 uppercase tracking-wider mb-3">
                    🚀 Live Evaluation Control Panel
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#111111] font-heading">
                    Pusat Penilaian Final Project
                </h1>
                <p class="mt-2 text-[#71717A] text-xs leading-relaxed max-w-3xl font-light">
                    Halaman khusus ini dirancang sebagai sarana penilaian presentasi proyek **ISP Billing & Management System**. 
                    Melalui halaman ini, dosen penguji / evaluator dapat memverifikasi kualitas desain antarmuka, fungsionalitas CRUD secara real-time, dan optimasi arsitektur basis data secara langsung.
                </p>
            </div>
        </div>

        <!-- TABS NAVIGASI -->
        <div class="border-b border-[#E4E4E7] flex flex-wrap gap-2">
            <button onclick="switchTab('frontend-tab', this)" class="tab-btn active px-4 py-2.5 text-xs font-bold uppercase tracking-wider border-b-2 border-indigo-600 text-indigo-600 focus:outline-none transition-all">
                🎨 1. Frontend (Fitur, Desain)
            </button>
            <button onclick="switchTab('crud-tab', this)" class="tab-btn px-4 py-2.5 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-[#71717A] hover:text-[#111111] focus:outline-none transition-all">
                ⚡ 2. Fungsional CRUD Demo
            </button>
            <button onclick="switchTab('database-tab', this)" class="tab-btn px-4 py-2.5 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-[#71717A] hover:text-[#111111] focus:outline-none transition-all">
                🗄️ 3. Database & Kamus Data
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: FRONTEND & DESAIN                       -->
        <!-- ============================================== -->
        <div id="frontend-tab" class="tab-panel space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Kiri: Tokens & Interactive Elements -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Design Tokens Card -->
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider mb-4 border-b border-[#F4F4F5] pb-2">
                            Design System Tokens
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <span class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Tipografi Sistem</span>
                                <div class="p-3 bg-[#F4F4F5] rounded border border-[#E4E4E7]">
                                    <p class="font-sans text-xs font-bold text-[#111111]">Geist Sans / Jakarta Sans (Body)</p>
                                    <p class="font-mono text-[10px] text-[#71717A] mt-1">Geist Mono (Code & Data Numbers)</p>
                                </div>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Color Palette</span>
                                <div class="flex gap-2">
                                    <div class="flex-1 text-center">
                                        <div class="h-8 rounded bg-[#FAF9F6] border border-[#E4E4E7] mb-1"></div>
                                        <span class="text-[8px] font-mono text-[#71717A]">#FAF9F6</span>
                                    </div>
                                    <div class="flex-1 text-center">
                                        <div class="h-8 rounded bg-[#FFFFFF] border border-[#E4E4E7] mb-1"></div>
                                        <span class="text-[8px] font-mono text-[#71717A]">#FFFFFF</span>
                                    </div>
                                    <div class="flex-1 text-center">
                                        <div class="h-8 rounded bg-[#6366F1] mb-1"></div>
                                        <span class="text-[8px] font-mono text-[#71717A]">#6366F1</span>
                                    </div>
                                    <div class="flex-1 text-center">
                                        <div class="h-8 rounded bg-[#111111] mb-1"></div>
                                        <span class="text-[8px] font-mono text-[#71717A]">#111111</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Micro-Animations Testing -->
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider mb-4 border-b border-[#F4F4F5] pb-2">
                            Uji Micro-Animations & Feedback
                        </h3>
                        <div class="space-y-4">
                            <!-- Click Active Scale feedback -->
                            <div>
                                <span class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Spring Click Feedback</span>
                                <button onclick="triggerToast('Tactile feedback: scale down on press.', 'info')" class="w-full btn-minimal flex items-center justify-center gap-2 transform active:scale-95 transition-transform duration-100 cursor-pointer py-2.5 text-xs font-bold">
                                    Tekan Tombol Ini (Scale Feedback)
                                </button>
                            </div>
                            
                            <!-- Spawner Alerts -->
                            <div>
                                <span class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Interactive Toasts</span>
                                <div class="flex gap-2">
                                    <button onclick="triggerToast('Sukses! CRUD operasi berhasil.', 'success')" class="flex-1 px-3 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[10px] font-bold rounded border border-emerald-300 transition-colors cursor-pointer text-center">
                                        🟢 Sukses Toast
                                    </button>
                                    <button onclick="triggerToast('Koneksi terputus ke Router!', 'error')" class="flex-1 px-3 py-2 bg-rose-100 hover:bg-rose-200 text-rose-800 text-[10px] font-bold rounded border border-rose-300 transition-colors cursor-pointer text-center">
                                        🔴 Error Toast
                                    </button>
                                </div>
                            </div>

                            <!-- Skeleton loader toggler -->
                            <div>
                                <span class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-1.5">Skeleton Loader Toggle</span>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-[#71717A]">Muat skeleton pada kartu</span>
                                    <button onclick="toggleSkeleton()" class="px-2.5 py-1 bg-[#F4F4F5] border border-[#E4E4E7] hover:bg-[#E4E4E7] text-[9px] font-bold uppercase rounded cursor-pointer transition-colors">
                                        Toggle Skeleton
                                    </button>
                                </div>
                                <div class="p-4 bg-[#FAF9F6] border border-[#E4E4E7] rounded-md relative">
                                    <div id="skeleton-card" class="space-y-2 hidden">
                                        <div class="h-4 bg-[#E4E4E7] rounded w-2/3 animate-pulse"></div>
                                        <div class="h-3 bg-[#E4E4E7] rounded w-full animate-pulse"></div>
                                        <div class="h-3 bg-[#E4E4E7] rounded w-5/6 animate-pulse"></div>
                                    </div>
                                    <div id="actual-card">
                                        <h4 class="font-bold text-xs text-[#111111]">Kartu Data Pelanggan</h4>
                                        <p class="text-[11px] text-[#71717A] mt-1">Status Keaktifan: <span class="inline-flex px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold text-[9px]">AKTIF</span></p>
                                        <p class="text-[11px] text-[#71717A]">IP: 192.168.100.55 | Paket: Home 10 Mbps</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Simulasi Responsive Design & Theme Switcher (Double Column Width) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <div class="flex items-center justify-between border-b border-[#F4F4F5] pb-2 mb-4">
                            <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider">
                                Simulasi Skema Warna & Responsivitas (Interactive Frame Mockup)
                            </h3>
                            <!-- Local Mode Switcher -->
                            <button onclick="toggleMockTheme()" class="px-3 py-1.5 bg-[#FAF9F6] border border-[#E4E4E7] hover:bg-[#F4F4F5] text-xs font-bold rounded flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors">
                                🌗 Switch Mock Theme
                            </button>
                        </div>
                        <p class="text-[11px] text-[#71717A] leading-relaxed mb-4">
                            Kotak di bawah ini merupakan sebuah simulasi visual antarmuka sistem. 
                            Anda dapat mengubah skema warna mock-up ini antara <strong>Mode Terang (Light)</strong> dan <strong>Mode Gelap (Dark)</strong> menggunakan tombol di atas, yang mendemonstrasikan kelayakan desain layout adaptif dan transisi warna di aplikasi.
                        </p>

                        <!-- Mockup Frame -->
                        <div id="mock-frame" class="border border-[#E4E4E7] rounded-lg p-5 transition-all duration-300 bg-[#FAF9F6] text-[#111111] relative">
                            <!-- Inner Header -->
                            <div class="flex items-center justify-between border-b pb-3 mb-4 transition-colors duration-300 border-[#E4E4E7]" id="mock-header">
                                <div class="flex items-center gap-2">
                                    <div class="w-3.5 h-3.5 rounded bg-indigo-600 flex items-center justify-center text-[8px] text-white font-extrabold">I</div>
                                    <span class="text-xs font-bold tracking-tight">ISP Billing Mock Console</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-[9px] uppercase tracking-wider font-bold opacity-75">Router Active</span>
                                </div>
                            </div>

                            <!-- Mock Body Content Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <!-- Col 1: Card -->
                                <div class="p-4 border rounded transition-colors duration-300 border-[#E4E4E7] bg-white text-[#111111]" id="mock-card-1">
                                    <span class="text-[8px] uppercase tracking-wider font-bold opacity-60">Total Income</span>
                                    <h4 class="text-lg font-black mt-1 font-mono">Rp 4.750.000</h4>
                                    <div class="text-[8px] text-emerald-600 font-bold mt-1">+12.4% vs last month</div>
                                </div>
                                <!-- Col 2: Card -->
                                <div class="p-4 border rounded transition-colors duration-300 border-[#E4E4E7] bg-white text-[#111111]" id="mock-card-2">
                                    <span class="text-[8px] uppercase tracking-wider font-bold opacity-60">Active Users</span>
                                    <h4 class="text-lg font-black mt-1 font-mono">142 Pelanggan</h4>
                                    <div class="text-[8px] text-indigo-600 font-bold mt-1">Quota limit: 200 max</div>
                                </div>
                                <!-- Col 3: Card -->
                                <div class="p-4 border rounded transition-colors duration-300 border-[#E4E4E7] bg-white text-[#111111]" id="mock-card-3">
                                    <span class="text-[8px] uppercase tracking-wider font-bold opacity-60">Open Tickets</span>
                                    <h4 class="text-lg font-black mt-1 font-mono">2 Laporan</h4>
                                    <div class="text-[8px] text-rose-500 font-bold mt-1">Need field technician</div>
                                </div>
                            </div>

                            <!-- Mock Chart/Info Area -->
                            <div class="mt-4 p-4 border rounded transition-colors duration-300 border-[#E4E4E7] bg-white text-[#111111] flex flex-col md:flex-row items-center justify-between gap-3" id="mock-card-4">
                                <div class="space-y-1">
                                    <h5 class="text-xs font-bold">MikroTik RouterOS Integration</h5>
                                    <p class="text-[10px] opacity-75">Otomatisasi filter isolir IP pelanggan berdasarkan jatuh tempo pembayaran invoice.</p>
                                </div>
                                <button class="btn-minimal px-3 py-1.5 text-[9px] font-bold uppercase tracking-wider shrink-0 cursor-pointer">
                                    Sync Router
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: FUNGSIONAL CRUD DEMO                    -->
        <!-- ============================================== -->
        <div id="crud-tab" class="tab-panel hidden space-y-6">
            
            <!-- Grid 1: Database Record Counts Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($counts as $table => $count)
                    <div class="app-card p-4 bg-white border border-[#E4E4E7] shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#71717A]">{{ $table }}</span>
                            <h4 id="count-{{ $table }}" class="text-xl font-extrabold text-[#111111] font-mono mt-1">{{ $count }}</h4>
                        </div>
                        <div class="text-xs text-[#71717A]">
                            📁
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Main Split CRUD Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kiri: Package CRUD Live Simulator (Double Column Width) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 border-b border-[#F4F4F5] pb-4">
                            <div>
                                <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider">
                                    Simulasi Operasi CRUD Paket
                                </h3>
                                <p class="text-xs text-[#71717A] font-light mt-1">
                                    Uji operasi Create, Read, Update, Delete secara langsung. Data disimpan di database dan di-update di sini secara real-time via AJAX Fetch.
                                </p>
                            </div>
                            <button onclick="createDemoPackage()" class="btn-minimal px-4 py-2 text-xs font-bold flex items-center gap-1 shrink-0">
                                ➕ Buat Paket Demo Cepat
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="app-table">
                                <thead>
                                    <tr>
                                        <th class="p-3 w-16 text-center">ID</th>
                                        <th class="p-3">Nama Paket</th>
                                        <th class="p-3">Harga Bulanan</th>
                                        <th class="p-3">Speed</th>
                                        <th class="p-3 text-center w-48">Aksi Simulasi</th>
                                    </tr>
                                </thead>
                                <tbody id="packages-table-body">
                                    @forelse($packages as $p)
                                        <tr id="package-row-{{ $p->id }}">
                                            <td class="p-3 text-center font-mono text-xs text-[#71717A]">{{ $p->id }}</td>
                                            <td class="p-3 font-semibold text-[#111111]">{{ $p->name }}</td>
                                            <td class="p-3 font-mono text-xs text-[#111111]">
                                                Rp <span id="package-price-{{ $p->id }}">{{ number_format($p->price, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="p-3 font-mono text-xs text-[#71717A]">{{ $p->speed }}</td>
                                            <td class="p-3 text-center">
                                                <div class="inline-flex items-center gap-1.5 justify-center">
                                                    <!-- Update Action -->
                                                    <button onclick="updateDemoPackage({{ $p->id }})" class="btn-minimal-secondary px-2.5 py-1 text-[9px] font-bold uppercase transition-transform active:scale-95 cursor-pointer" title="Perbarui harga secara acak">
                                                        ⚡ Ubah Harga
                                                    </button>
                                                    <!-- Delete Action -->
                                                    <button onclick="deleteDemoPackage({{ $p->id }})" class="inline-flex items-center justify-center p-1 px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded text-[9px] font-bold uppercase transition-transform active:scale-95 cursor-pointer" title="Hapus paket">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="no-packages-row">
                                            <td colspan="5" class="p-6 text-center text-[#71717A] font-semibold text-xs font-mono">
                                                [Belum ada paket terdaftar]
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Simulasikan Invoice & Monitoring Backbone (Single Column) -->
                <div class="space-y-6">
                    
                    <!-- Fast Invoice Generation -->
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider mb-2 border-b border-[#F4F4F5] pb-2">
                            Simulasi Generate Invoice
                        </h3>
                        <p class="text-xs text-[#71717A] font-light mb-4 leading-relaxed">
                            Simulasikan pembuatan tagihan otomatis untuk pelanggan yang aktif. Data invoice baru akan ditambahkan di database.
                        </p>
                        <button onclick="createDemoInvoice()" class="w-full btn-minimal text-xs font-bold py-2.5 flex items-center justify-center gap-1.5 active:scale-95 transition-transform duration-100 cursor-pointer">
                            🧾 Generate Invoice Baru
                        </button>
                    </div>

                    <!-- Backbone device ping simulator -->
                    <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                        <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider mb-2 border-b border-[#F4F4F5] pb-2">
                            Simulasi Ping Backbone
                        </h3>
                        <p class="text-xs text-[#71717A] font-light mb-4 leading-relaxed">
                            Kirimkan sinyal ping buatan untuk memeriksa latensi konektivitas perangkat backbone server secara langsung.
                        </p>
                        
                        <div class="space-y-3">
                            @forelse($backboneDevices as $d)
                                <div class="p-3 border border-[#E4E4E7] rounded flex items-center justify-between bg-[#FAF9F6]">
                                    <div class="min-w-0">
                                        <h5 class="text-xs font-bold text-[#111111] truncate">{{ $d->name }}</h5>
                                        <p class="text-[9px] font-mono text-[#71717A] mt-0.5">{{ $d->ip }}</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span id="ping-status-badge-{{ $d->id }}" class="inline-flex px-1.5 py-0.2 rounded text-[8px] font-bold uppercase {{ $d->status === 'up' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                                {{ strtoupper($d->status) }}
                                            </span>
                                            <span id="ping-latency-{{ $d->id }}" class="text-[8px] font-mono text-[#71717A]">
                                                {{ $d->last_ping_at ? 'checked' : 'never checked' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <button onclick="pingBackboneDevice({{ $d->id }}, this)" class="btn-minimal-secondary px-2.5 py-1 text-[9px] font-bold uppercase transition-transform active:scale-95 cursor-pointer shrink-0">
                                        Ping
                                    </button>
                                </div>
                            @empty
                                <div class="text-center p-4 text-xs font-mono text-[#71717A]">
                                    [Belum ada perangkat backbone]
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: DATABASE & KAMUS DATA                   -->
        <!-- ============================================== -->
        <div id="database-tab" class="tab-panel hidden space-y-6">
            
            <!-- Connection details & audits -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info DB Connection -->
                <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                    <h3 class="text-xs font-bold text-[#71717A] uppercase tracking-wider mb-3">Database Connection</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between border-b pb-1 border-[#F4F4F5]">
                            <span class="text-xs text-[#71717A]">Driver Default</span>
                            <span class="text-xs font-mono font-bold text-indigo-600">{{ strtoupper($dbDriver) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-1 border-[#F4F4F5]">
                            <span class="text-xs text-[#71717A]">Database Name</span>
                            <span class="text-xs font-mono font-bold text-[#111111] truncate max-w-[150px]" title="{{ $dbName }}">{{ $dbName }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-1 border-[#F4F4F5]">
                            <span class="text-xs text-[#71717A]">Host Address</span>
                            <span class="text-xs font-mono font-bold text-[#111111]">{{ $dbHost }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-[#71717A]">Connection Status</span>
                            <span class="text-xs font-bold text-emerald-600 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Connected
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Cast Audit Encryption -->
                <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                    <h3 class="text-xs font-bold text-[#71717A] uppercase tracking-wider mb-3">Kolom Sensitif Terenkripsi</h3>
                    <div class="space-y-2">
                        @foreach ($encryptionAudit as $col => $ok)
                            <div class="flex justify-between border-b pb-1 border-[#F4F4F5]">
                                <span class="text-xs font-mono text-[#71717A]">{{ $col }}</span>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded {{ $ok ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                    {{ $ok ? 'Encrypted (AES-256)' : 'Plaintext / Error' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Info Index Performance Audit -->
                <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                    <h3 class="text-xs font-bold text-[#71717A] uppercase tracking-wider mb-3">Optimal Index Performance</h3>
                    <div class="space-y-2 h-[120px] overflow-y-auto pr-1">
                        @foreach ($indexAudit as $table => $indexes)
                            <div class="border-b pb-1.5 border-[#F4F4F5] last:border-0 mb-1">
                                <span class="text-xs font-bold text-[#111111] block mb-1">Tabel: {{ $table }}</span>
                                <div class="flex flex-wrap gap-1">
                                    @if(is_array($indexes))
                                        @forelse($indexes as $idx)
                                            @if($idx != 'PRIMARY')
                                                <span class="text-[8px] font-mono bg-indigo-50 border border-indigo-100 text-indigo-700 px-1 rounded">
                                                    {{ $idx }}
                                                </span>
                                            @endif
                                        @empty
                                            <span class="text-[8px] font-mono text-[#71717A]">No indexes found</span>
                                        @endforelse
                                    @else
                                        <span class="text-[8px] font-mono text-rose-600">Error query schema</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Searchable Data Dictionary Section -->
            <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#F4F4F5] pb-4 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider">
                            Kamus Data (Data Dictionary) Interaktif
                        </h3>
                        <p class="text-xs text-[#71717A] font-light mt-1">
                            Cari data kamus tabel atau kolom secara instan untuk verifikasi struktur skema.
                        </p>
                    </div>
                    <!-- Live Search Input -->
                    <div class="w-full md:w-80">
                        <input type="text" id="dict-search-input" onkeyup="filterDataDictionary()" placeholder="Cari nama tabel, tipe data, atau deskripsi..." class="w-full text-xs px-3.5 py-2 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-indigo-500/20 focus:border-indigo-500 rounded-md text-[#111111] font-medium transition-all shadow-sm">
                    </div>
                </div>

                <!-- Dictionary Tables list -->
                <div class="space-y-6" id="dictionary-list-container">
                    @foreach ($dataDictionary as $tableName => $tableData)
                        <div class="border border-[#E4E4E7] rounded-md p-4 dictionary-table-card" data-table-name="{{ $tableName }}" data-table-desc="{{ $tableData['desc'] }}">
                            <div class="flex items-center gap-2 border-b border-[#F4F4F5] pb-2 mb-3">
                                <span class="text-base">📊</span>
                                <h4 class="font-extrabold text-sm text-[#111111]">Tabel: <span class="text-indigo-600">{{ $tableName }}</span></h4>
                                <span class="text-xs text-[#71717A] font-light font-sans ml-2">({{ $tableData['desc'] }})</span>
                            </div>

                            <table class="w-full border-collapse text-[11px] leading-relaxed">
                                <thead>
                                    <tr class="bg-[#F4F4F5] text-left text-[#71717A]">
                                        <th class="p-2 font-bold uppercase tracking-wider w-1/4">Nama Kolom</th>
                                        <th class="p-2 font-bold uppercase tracking-wider w-1/6">Tipe Data</th>
                                        <th class="p-2 font-bold uppercase tracking-wider w-1/12">Nullable</th>
                                        <th class="p-2 font-bold uppercase tracking-wider">Keterangan / Deskripsi Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tableData['columns'] as $colName => $colInfo)
                                        <tr class="border-b last:border-0 border-[#F4F4F5] hover:bg-[#FAF9F6] transition-colors dictionary-column-row" data-col-name="{{ $colName }}" data-col-desc="{{ $colInfo['desc'] }}" data-col-type="{{ $colInfo['type'] }}">
                                            <td class="p-2 font-bold font-mono text-[#111111]">{{ $colName }}</td>
                                            <td class="p-2 font-mono text-[#71717A]">{{ $colInfo['type'] }}</td>
                                            <td class="p-2 text-center text-[#71717A]">
                                                <span class="inline-flex px-1.5 py-0.2 rounded text-[8px] font-mono font-bold {{ $colInfo['null'] === 'NO' ? 'bg-[#F4F4F5] text-[#111111]' : 'bg-yellow-50 text-yellow-800' }}">
                                                    {{ $colInfo['null'] }}
                                                </span>
                                            </td>
                                            <td class="p-2 text-[#71717A] font-sans font-light">{{ $colInfo['desc'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    <div id="no-search-results" class="hidden text-center p-10 text-xs font-mono text-[#71717A] border border-dashed rounded-lg">
                        [Pencarian tidak ditemukan. Silakan masukkan kata kunci lain]
                    </div>
                </div>
            </div>

            <!-- Mermaid visual representation of ERD -->
            <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs">
                <h3 class="text-sm font-bold text-[#111111] uppercase tracking-wider mb-2 border-b border-[#F4F4F5] pb-2">
                    Skema Relasi Diagram (ERD conceptual layout)
                </h3>
                <p class="text-xs text-[#71717A] font-light mb-4">
                    Berikut skema visual relasi utama antar entitas di database **ISP Billing** yang memetakan keterkaitan data.
                </p>
                <div class="p-4 bg-[#F4F4F5] rounded-md border overflow-x-auto text-[11px] font-mono leading-relaxed max-h-[350px] scrollbar-thin">
                    <pre class="text-indigo-900">
erDiagram
    users ||--o{ packages : "owns"
    users ||--o{ customers : "owns"
    users ||--o{ invoices : "owns"
    users ||--o{ tickets : "owns"
    users ||--o{ presensis : "has"
    users ||--o{ backbone_devices : "owns"
    packages ||--o{ customers : "has"
    customers ||--o{ invoices : "has"
    customers ||--o{ payments : "makes"
    customers ||--o{ tickets : "opens"
    invoices ||--o{ payments : "paid_by"
                    </pre>
                </div>
            </div>
        </div>

    </div>

    <!-- Embedded Scripts for dynamic interactions without rebuild -->
    <script>
        // ----------------------------------------------------
        // TABS NAVIGATOR LOGIC
        // ----------------------------------------------------
        function switchTab(tabId, button) {
            // Hide all tab panels
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden');
            });
            // Show selected tab panel
            document.getElementById(tabId).classList.remove('hidden');

            // Deactivate all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-[#71717A]');
            });
            // Activate current tab button
            button.classList.remove('border-transparent', 'text-[#71717A]');
            button.classList.add('active', 'border-indigo-600', 'text-indigo-600');
        }

        // ----------------------------------------------------
        // TOAST FEEDBACK LOGIC
        // ----------------------------------------------------
        function triggerToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            toast.className = `p-4 rounded-md shadow-lg border text-xs font-bold transition-all duration-300 transform translate-y-2 opacity-0 flex items-center justify-between gap-3 w-72`;
            
            if (type === 'success') {
                toast.className += ' bg-emerald-50 border-emerald-200 text-emerald-800';
                toast.innerHTML = `<span>🟢 ${message}</span>`;
            } else if (type === 'error') {
                toast.className += ' bg-rose-50 border-rose-200 text-rose-800';
                toast.innerHTML = `<span>🔴 ${message}</span>`;
            } else {
                toast.className += ' bg-indigo-50 border-indigo-200 text-indigo-800';
                toast.innerHTML = `<span>🔵 ${message}</span>`;
            }

            container.appendChild(toast);
            
            // Fade in
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // ----------------------------------------------------
        // FRONTEND TAB SIMULATORS
        // ----------------------------------------------------
        function toggleSkeleton() {
            const skeleton = document.getElementById('skeleton-card');
            const actual = document.getElementById('actual-card');
            
            if (skeleton.classList.contains('hidden')) {
                skeleton.classList.remove('hidden');
                actual.classList.add('hidden');
            } else {
                skeleton.classList.add('hidden');
                actual.classList.remove('hidden');
            }
        }

        function toggleMockTheme() {
            const frame = document.getElementById('mock-frame');
            const header = document.getElementById('mock-header');
            const card1 = document.getElementById('mock-card-1');
            const card2 = document.getElementById('mock-card-2');
            const card3 = document.getElementById('mock-card-3');
            const card4 = document.getElementById('mock-card-4');
            
            if (frame.classList.contains('bg-[#FAF9F6]')) {
                // Switch to Dark
                frame.classList.remove('bg-[#FAF9F6]', 'text-[#111111]');
                frame.classList.add('bg-slate-900', 'text-slate-100');
                header.classList.replace('border-[#E4E4E7]', 'border-slate-800');
                
                [card1, card2, card3, card4].forEach(card => {
                    card.classList.remove('bg-white', 'text-[#111111]', 'border-[#E4E4E7]');
                    card.classList.add('bg-slate-800', 'text-slate-100', 'border-slate-700/50');
                });
                triggerToast('Mode Gelap diaktifkan pada Mock UI Frame!', 'info');
            } else {
                // Switch to Light
                frame.classList.remove('bg-slate-900', 'text-slate-100');
                frame.classList.add('bg-[#FAF9F6]', 'text-[#111111]');
                header.classList.replace('border-slate-800', 'border-[#E4E4E7]');
                
                [card1, card2, card3, card4].forEach(card => {
                    card.classList.remove('bg-slate-800', 'text-slate-100', 'border-slate-700/50');
                    card.classList.add('bg-white', 'text-[#111111]', 'border-[#E4E4E7]');
                });
                triggerToast('Mode Terang diaktifkan pada Mock UI Frame!', 'info');
            }
        }

        // ----------------------------------------------------
        // LIVE CRUD SIMULATION OPERATIONS
        // ----------------------------------------------------
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Helper to update record stat counts on UI
        function incrementStatCount(type, val = 1) {
            const el = document.getElementById(`count-${type}`);
            if (el) {
                let currentVal = parseInt(el.innerText) || 0;
                el.innerText = currentVal + val;
            }
        }

        function decrementStatCount(type, val = 1) {
            const el = document.getElementById(`count-${type}`);
            if (el) {
                let currentVal = parseInt(el.innerText) || 0;
                el.innerText = Math.max(0, currentVal - val);
            }
        }

        function createDemoPackage() {
            fetch("{{ route('evaluation.packages.create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    triggerToast(data.message, 'success');
                    incrementStatCount('packages');

                    // Append row dynamically
                    const tbody = document.getElementById('packages-table-body');
                    const noRow = document.getElementById('no-packages-row');
                    if (noRow) noRow.remove();

                    // Format price
                    const formattedPrice = new Intl.NumberFormat('id-ID').format(data.data.price);

                    const tr = document.createElement('tr');
                    tr.id = `package-row-${data.data.id}`;
                    tr.className = "bg-indigo-50/50 transition-all duration-300";
                    tr.innerHTML = `
                        <td class="p-3 text-center font-mono text-xs text-[#71717A]">${data.data.id}</td>
                        <td class="p-3 font-semibold text-[#111111]">${data.data.name}</td>
                        <td class="p-3 font-mono text-xs text-[#111111]">
                            Rp <span id="package-price-${data.data.id}">${formattedPrice}</span>
                        </td>
                        <td class="p-3 font-mono text-xs text-[#71717A]">${data.data.speed}</td>
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1.5 justify-center">
                                <button onclick="updateDemoPackage(${data.data.id})" class="btn-minimal-secondary px-2.5 py-1 text-[9px] font-bold uppercase transition-transform active:scale-95 cursor-pointer">
                                    ⚡ Ubah Harga
                                </button>
                                <button onclick="deleteDemoPackage(${data.data.id})" class="inline-flex items-center justify-center p-1 px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded text-[9px] font-bold uppercase transition-transform active:scale-95 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.insertBefore(tr, tbody.firstChild);

                    // Fade background back to normal
                    setTimeout(() => {
                        tr.classList.remove('bg-indigo-50/50');
                    }, 1000);
                } else {
                    triggerToast(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                triggerToast('Terjadi kesalahan memproses request.', 'error');
            });
        }

        function updateDemoPackage(id) {
            fetch(`/evaluasi/packages/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    triggerToast(data.message, 'success');
                    const row = document.getElementById(`package-row-${id}`);
                    row.classList.add('bg-yellow-50');

                    // Update price span
                    const formattedPrice = new Intl.NumberFormat('id-ID').format(data.data.price);
                    document.getElementById(`package-price-${id}`).innerText = formattedPrice;

                    setTimeout(() => {
                        row.classList.remove('bg-yellow-50');
                    }, 1000);
                } else {
                    triggerToast(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                triggerToast('Gagal merubah data paket.', 'error');
            });
        }

        function deleteDemoPackage(id) {
            if(!confirm('Apakah Anda yakin ingin menghapus paket demo ini?')) return;
            
            fetch(`/evaluasi/packages/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    triggerToast(data.message, 'success');
                    decrementStatCount('packages');
                    
                    const row = document.getElementById(`package-row-${id}`);
                    row.classList.add('opacity-0', 'scale-95');
                    
                    setTimeout(() => {
                        row.remove();
                        // Check if tbody is empty
                        const tbody = document.getElementById('packages-table-body');
                        if (tbody.children.length === 0) {
                            const tr = document.createElement('tr');
                            tr.id = "no-packages-row";
                            tr.innerHTML = `
                                <td colspan="5" class="p-6 text-center text-[#71717A] font-semibold text-xs font-mono">
                                    [Belum ada paket terdaftar]
                                </td>
                            `;
                            tbody.appendChild(tr);
                        }
                    }, 300);
                } else {
                    triggerToast(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                triggerToast('Gagal menghapus paket.', 'error');
            });
        }

        function createDemoInvoice() {
            fetch("{{ route('evaluation.invoices.create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    triggerToast(data.message, 'success');
                    incrementStatCount('invoices');
                } else {
                    triggerToast(data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                triggerToast('Gagal meng-generate invoice.', 'error');
            });
        }

        function pingBackboneDevice(id, btn) {
            const originalText = btn.innerText;
            btn.innerText = 'Pinging...';
            btn.disabled = true;

            fetch(`/evaluasi/backbone/${id}/ping`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.innerText = originalText;
                btn.disabled = false;

                if (data.success) {
                    // Update UI elements
                    const badge = document.getElementById(`ping-status-badge-${id}`);
                    const latencySpan = document.getElementById(`ping-latency-${id}`);
                    
                    if (data.data.status === 'up') {
                        badge.innerText = 'UP';
                        badge.className = 'inline-flex px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200';
                        triggerToast(`Konektivitas ${data.data.name} OK! latency: ${data.data.ping}`, 'success');
                    } else {
                        badge.innerText = 'DOWN';
                        badge.className = 'inline-flex px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-rose-100 text-rose-800 border border-rose-200';
                        triggerToast(`Konektivitas ${data.data.name} TERPUTUS! latency: ${data.data.ping}`, 'error');
                    }
                    latencySpan.innerText = `Checked: ${data.data.ping} at ${data.data.time}`;
                } else {
                    triggerToast('Ping simlasi gagal.', 'error');
                }
            })
            .catch(err => {
                btn.innerText = originalText;
                btn.disabled = false;
                console.error(err);
                triggerToast('Error pinging server.', 'error');
            });
        }

        // ----------------------------------------------------
        // DATA DICTIONARY SEARCH FILTER
        // ----------------------------------------------------
        function filterDataDictionary() {
            const query = document.getElementById('dict-search-input').value.toLowerCase();
            const cards = document.querySelectorAll('.dictionary-table-card');
            let foundAny = false;

            cards.forEach(card => {
                const tableName = card.getAttribute('data-table-name').toLowerCase();
                const tableDesc = card.getAttribute('data-table-desc').toLowerCase();
                
                let matchesTable = tableName.includes(query) || tableDesc.includes(query);
                let matchesColumn = false;
                
                const rows = card.querySelectorAll('.dictionary-column-row');
                rows.forEach(row => {
                    const colName = row.getAttribute('data-col-name').toLowerCase();
                    const colDesc = row.getAttribute('data-col-desc').toLowerCase();
                    const colType = row.getAttribute('data-col-type').toLowerCase();

                    if (colName.includes(query) || colDesc.includes(query) || colType.includes(query)) {
                        row.classList.remove('hidden');
                        matchesColumn = true;
                    } else {
                        // If card matches as a whole, don't hide columns, otherwise hide unmatched columns
                        if (matchesTable) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    }
                });

                if (matchesTable || matchesColumn) {
                    card.classList.remove('hidden');
                    foundAny = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResults = document.getElementById('no-search-results');
            if (foundAny) {
                noResults.classList.add('hidden');
            } else {
                noResults.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
