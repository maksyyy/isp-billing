<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-[#111111] flex items-center gap-2">
            📅 Kios Presensi Biometrik Otomatis
        </h2>

        <!-- ALERT STATUS -->
        @if(session('success'))
            <div class="bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] px-4 py-3 rounded-md mb-6 font-medium shadow-sm">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] px-4 py-3 rounded-md mb-6 font-medium shadow-sm">
                ❌ {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8">

            <!-- ============================================ -->
            <!-- 📷 PEMINDAI WAJAH BIOMETRIK (SCANNER PANEL) -->
            <!-- ============================================ -->
            <div class="app-card p-6 flex flex-col items-center">
                <!-- Premium Navigation Tabs -->
                <div class="flex border-b border-[#E4E4E7] w-full mb-6 text-xs font-bold text-center">
                    <button type="button" id="tab-auto" class="flex-1 pb-3 text-[#6366F1] border-b-2 border-[#6366F1] focus:outline-none transition-all cursor-pointer">
                        ⚡ Scan Otomatis
                    </button>
                    <button type="button" id="tab-directed" class="flex-1 pb-3 text-[#71717A] border-b-2 border-transparent hover:text-[#111111] focus:outline-none transition-all cursor-pointer">
                        👤 Pilih Nama + Scan
                    </button>
                    <button type="button" id="tab-manual" class="flex-1 pb-3 text-[#71717A] border-b-2 border-transparent hover:text-[#111111] focus:outline-none transition-all cursor-pointer">
                        ✏️ Presensi Manual
                    </button>
                </div>

                <div id="panel-header" class="text-center w-full max-w-sm mb-4">
                    <h3 id="panel-title" class="font-bold text-lg text-[#111111] mb-1">Stasiun Pemindai Hands-Free</h3>
                    <p id="panel-desc" class="text-xs text-[#71717A]">
                        Kamera biometrik akan mendeteksi dan mengenali wajah Anda secara otomatis. Cukup berdiri tegak di depan kamera.
                    </p>
                </div>

                <!-- Dropdown Pilih Karyawan (Directed Biometric, Hidden by Default) -->
                <div id="employee-select-container" class="mb-6 w-full max-w-xs hidden">
                    <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-widest mb-1.5 text-center">Pilih Nama Anda</label>
                    <select id="user_id_select" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md font-bold text-[#111111] transition-all shadow-sm cursor-pointer">
                        <option value="" data-face="false">-- Pilih Nama Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-face="{{ $emp->face_photo ? 'true' : 'false' }}">
                                {{ $emp->name }} ({{ strtoupper($emp->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Manual Submission Container (Hidden by Default) -->
                <div id="manual-submission-container" class="w-full max-w-xs hidden mb-6 text-center">
                    <label class="block text-[10px] font-bold text-[#71717A] uppercase tracking-widest mb-1.5">Pilih Nama Anda</label>
                    <select id="user_id_select_manual" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md font-bold text-[#111111] transition-all shadow-sm cursor-pointer mb-4">
                        <option value="">-- Pilih Nama Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} ({{ strtoupper($emp->role) }})
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="button" id="btn-submit-manual" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-[#10B981] to-[#059669] text-[#FFFFFF] font-bold text-xs px-6 py-2.5 rounded-full shadow-[0_4px_20px_rgba(16,185,129,0.25)] cursor-pointer transition-all hover:scale-105 active:scale-95 border-0">
                        ✍️ Kirim Presensi Manual
                    </button>
                </div>

                <!-- Scanner Container -->
                <div id="scanner-visual-container" class="relative w-72 h-72 rounded-full overflow-hidden border-4 border-[#8B5CF6] shadow-[0_4px_20px_rgba(139,92,246,0.15)] bg-[#FFFFFF] flex items-center justify-center mb-2 group">
                    <video id="webcam" class="w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                    <canvas id="canvas" class="hidden absolute w-full h-full object-cover transform -scale-x-100"></canvas>
                    
                    <!-- Circular Overlay -->
                    <div class="absolute inset-0 border-[24px] border-[#FFFFFF]/60 pointer-events-none rounded-full"></div>
                    
                    <!-- Scanner Laser Red Line (Animation) -->
                    <div id="scanner-laser" class="hidden absolute left-0 right-0 h-1 bg-[#8B5CF6] shadow-[0_0_8px_#8B5CF6] z-10 animate-scan"></div>
                    
                    <!-- Target Reticle Overlay -->
                    <div class="absolute inset-8 border border-[#8B5CF6]/40 rounded-full border-dashed pointer-events-none"></div>

                    <!-- Biometric Status Text Overlay -->
                    <div id="biometric-status-overlay" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#8B5CF6]/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#8B5CF6] uppercase hidden z-20 shadow-lg">
                        ⚡ Memindai Wajah...
                    </div>

                    <!-- Placeholder -->
                    <div id="camera-placeholder" class="absolute text-center text-[#71717A] p-4">
                        <span class="text-4xl block animate-bounce mb-2">🎥</span>
                        <span class="text-sm font-semibold block text-[#111111]">Kamera Belum Aktif</span>
                        <p class="text-[10px] text-[#71717A] mt-1">Stasiun presensi mendeteksi pilihan Anda</p>
                    </div>
                </div>

                <!-- Tombol Mulai Scan Wajah -->
                <div id="btn-start-scan-container" class="mt-4 mb-2 flex flex-col items-center">
                    <button type="button" id="btn-start-scan" class="flex items-center gap-2 bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] text-[#FFFFFF] font-bold text-xs px-6 py-2.5 rounded-full shadow-[0_4px_20px_rgba(99,102,241,0.25)] cursor-pointer transition-all hover:scale-105 active:scale-95 border-0">
                        🔍 Mulai Pindai Wajah
                    </button>
                </div>

                <!-- Auto status text & Reset button -->
                <div class="text-center min-h-[40px] flex flex-col items-center justify-center">
                    <div id="status-instructions" class="text-xs text-[#6366F1] font-bold animate-pulse hidden max-w-xs">
                        Kamera aktif, mohon menghadap ke arah kamera...
                    </div>
                    <button type="button" id="btn-reset" class="hidden text-xs text-[#B91C1C] font-bold hover:underline mt-2 flex items-center gap-1 cursor-pointer">
                        ✕ Batalkan & Pindai Ulang
                    </button>
                </div>

                <!-- Form Absensi Hidden -->
                <form id="attendance-form" action="{{ route('presensi.store') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="user_id" id="user_id_input">
                    <input type="hidden" name="photo" id="photo_input">
                    <input type="hidden" name="is_manual" id="is_manual_input" value="0">
                </form>

            </div>

            <!-- ============================================ -->
            <!-- 📊 REKAP KEHADIRAN HARI INI (SUMMARY PANEL) -->
            <!-- ============================================ -->
            <div class="app-card p-6">
                <h3 class="font-bold text-lg text-[#111111] border-b border-[#E4E4E7] pb-3 mb-6 flex items-center gap-2">
                    ⏱️ Rekap Kehadiran Hari Ini
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <!-- Tepat Waktu -->
                    <div class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#047857] uppercase tracking-wide">Tepat Waktu</span>
                        <p class="text-2xl font-extrabold text-[#047857] mt-2">
                            {{ $logs->where('status', 'Hadir')->count() }}
                        </p>
                        <span class="text-[10px] text-[#065F46]/80 mt-1">Staf tepat waktu</span>
                    </div>

                    <!-- Terlambat -->
                    <div class="bg-[#FFFBEB] border border-[#FDE68A] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#B45309] uppercase tracking-wide">Terlambat</span>
                        <p class="text-2xl font-extrabold text-[#B45309] mt-2">
                            {{ $logs->where('status', 'Terlambat')->count() }}
                        </p>
                        <span class="text-[10px] text-[#92400E]/80 mt-1">Melewati batas masuk</span>
                    </div>

                    <!-- Lembur / Extra Time -->
                    <div class="bg-[#FFF7ED] border border-[#FFEDD5] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#C2410C] uppercase tracking-wide">Lembur</span>
                        <p class="text-2xl font-extrabold text-[#C2410C] mt-2">
                            {{ $logs->where('lembur', '>', 0)->count() }}
                        </p>
                        <span class="text-[10px] text-[#9A3412]/80 mt-1">Checkout > 16:00</span>
                    </div>
                </div>

                <div class="border-t border-[#E4E4E7] pt-4">
                    <h4 class="font-bold text-xs text-[#71717A] uppercase tracking-wider mb-2">Ketentuan Jam Kerja</h4>
                    <ul class="text-xs text-[#71717A] space-y-1">
                        <li>⏰ Jam Masuk Standar: <strong class="text-[#111111]">08:00 - 08:30 WIB</strong></li>
                        <li>⚠️ Batas Toleransi Terlambat: <strong class="text-[#111111]">> 08:30 WIB</strong></li>
                        <li>🏠 Jam Keluar Kantor: <strong class="text-[#111111]">16:00 WIB</strong></li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- ============================================ -->
        <!-- 📅 LIVE BOARD ABSENSI HARI INI (HISTORY TABLE) -->
        <!-- ============================================ -->
        <div class="app-card p-6 mt-8">
            <h3 class="font-bold text-lg text-[#111111] mb-6 flex items-center gap-2">
                📋 Live Board Kehadiran Staf Hari Ini
            </h3>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nama Staf</th>
                            <th>Jam Masuk</th>
                            <th>Foto Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Foto Keluar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($log->user->face_photo)
                                            <img src="{{ asset('storage/' . $log->user->face_photo) }}" class="w-8 h-8 object-cover rounded-full border border-[#E4E4E7] shadow-sm">
                                        @else
                                            <div class="w-8 h-8 bg-[#8B5CF6]/10 text-[#8B5CF6] font-bold rounded-full flex items-center justify-center text-xs shadow-sm">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="block font-bold text-sm text-[#111111]">{{ $log->user->name }}</span>
                                            <span class="block text-[9px] text-[#71717A] font-bold uppercase tracking-wider">{{ $log->user->role }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-[#059669] font-bold">
                                    {{ \Carbon\Carbon::parse($log->jam_masuk)->format('H:i') }} WIB
                                </td>
                                <td>
                                    @if($log->foto_masuk)
                                        <a href="{{ asset('storage/' . $log->foto_masuk) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $log->foto_masuk) }}" class="w-10 h-10 object-cover rounded border border-[#E4E4E7] shadow-sm hover:scale-110 transition">
                                        </a>
                                    @else
                                        <span class="px-2 py-1 bg-[#F4F4F5] border border-[#E4E4E7] text-[10px] font-extrabold tracking-wider text-[#71717A] rounded uppercase">
                                            ✏️ Manual
                                        </span>
                                    @endif
                                </td>
                                 <td class="text-[#DC2626] font-bold">
                                     {{ $log->jam_keluar ? \Carbon\Carbon::parse($log->jam_keluar)->format('H:i') . ' WIB' : '-' }}
                                     @if($log->lembur > 0)
                                         @php
                                             $hours = floor($log->lembur / 60);
                                             $minutes = $log->lembur % 60;
                                             $durationStr = ($hours > 0 ? $hours . ' jam ' : '') . ($minutes > 0 ? $minutes . ' menit' : '');
                                         @endphp
                                         <span class="block text-[10px] text-[#4F46E5] font-extrabold uppercase mt-1">
                                             🔥 Lembur: {{ $durationStr }}
                                         </span>
                                     @endif
                                 </td>
                                <td>
                                    @if($log->foto_keluar)
                                        <a href="{{ asset('storage/' . $log->foto_keluar) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $log->foto_keluar) }}" class="w-10 h-10 object-cover rounded border border-[#E4E4E7] shadow-sm hover:scale-110 transition">
                                        </a>
                                    @elseif($log->jam_keluar)
                                        <span class="px-2 py-1 bg-[#F4F4F5] border border-[#E4E4E7] text-[10px] font-extrabold tracking-wider text-[#71717A] rounded uppercase">
                                            ✏️ Manual
                                        </span>
                                    @else
                                        <span class="text-[#71717A] text-xs italic">Belum Keluar</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($log->status === 'Hadir')
                                        <span class="status-badge-active">
                                            {{ $log->status }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A] shadow-sm">
                                            {{ $log->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500 italic">
                                    Belum ada staf yang melakukan presensi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Scanner Laser CSS Keyframes -->
    <style>
        .animate-scan {
            animation: scanning 2s linear infinite;
        }

        @keyframes scanning {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
    </style>

    <!-- LIVE CAMERA SCRIPT -->
    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const laser = document.getElementById('scanner-laser');
        const placeholder = document.getElementById('camera-placeholder');
        const instructions = document.getElementById('status-instructions');
        
        const btnStartScan = document.getElementById('btn-start-scan');
        const selectUser = document.getElementById('user_id_select');
        
        const userIdInput = document.getElementById('user_id_input');
        const btnReset = document.getElementById('btn-reset');
        
        const attForm = document.getElementById('attendance-form');
        const photoInput = document.getElementById('photo_input');
        
        const statusOverlay = document.getElementById('biometric-status-overlay');
        
        // Tab DOM elements
        const tabAuto = document.getElementById('tab-auto');
        const tabDirected = document.getElementById('tab-directed');
        const tabManual = document.getElementById('tab-manual');
        
        const panelTitle = document.getElementById('panel-title');
        const panelDesc = document.getElementById('panel-desc');
        
        const employeeSelectContainer = document.getElementById('employee-select-container');
        const manualSubmissionContainer = document.getElementById('manual-submission-container');
        const scannerVisualContainer = document.getElementById('scanner-visual-container');
        const btnStartScanContainer = document.getElementById('btn-start-scan-container');
        
        const isManualInput = document.getElementById('is_manual_input');
        const selectUserManual = document.getElementById('user_id_select_manual');
        const btnSubmitManual = document.getElementById('btn-submit-manual');
        
        let activeTab = 'auto'; // 'auto', 'directed', 'manual'
        let stream = null;
        let scanInterval = null;
        let scanTimeout = null;
        let isProcessingMatch = false;
        let targetUserId = null;
        let manualScanTimeout = null;
        
        let livenessState = "idle";
        let livenessHistory = [];
        let livenessTimeout = null;

        function getRegionAverage(sig, xStart, xEnd, yStart, yEnd) {
            let sum = 0;
            let count = 0;
            const size = 32;
            for (let y = yStart; y <= yEnd; y++) {
                for (let x = xStart; x <= xEnd; x++) {
                    sum += sig[y * size + x];
                    count++;
                }
            }
            return sum / count;
        }

        // Data Karyawan Terdaftar dengan wajah dari PHP Blade
        const employees = [
            @foreach($employees as $emp)
                @if($emp->face_photo)
                    {
                        id: "{{ $emp->id }}",
                        name: "{{ $emp->name }}",
                        role: "{{ strtoupper($emp->role) }}",
                        photoUrl: "{{ asset('storage/' . $emp->face_photo) }}",
                        signature: null
                    },
                @endif
            @endforeach
        ];

        // Helper: Ekstrak signature piksel 32x32 untuk akurasi lebih tinggi (1024 titik detail)
        function getImageSignature(imgOrVideo, width = 32, height = 32) {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = width;
            tempCanvas.height = height;
            const ctx = tempCanvas.getContext('2d');
            
            // Crop center square to focus on the face and exclude the outer background
            const cropFactor = 0.58;
            let sx = 0, sy = 0;
            if (imgOrVideo instanceof HTMLVideoElement) {
                const vWidth = imgOrVideo.videoWidth || 350;
                const vHeight = imgOrVideo.videoHeight || 350;
                const size = Math.min(vWidth, vHeight);
                const cropSize = size * cropFactor;
                sx = (vWidth - cropSize) / 2;
                sy = (vHeight - cropSize) / 2;
                
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(imgOrVideo, sx, sy, cropSize, cropSize, 0, 0, width, height);
                ctx.setTransform(1, 0, 0, 1, 0, 0);
            } else if (imgOrVideo instanceof HTMLImageElement) {
                const iWidth = imgOrVideo.naturalWidth || imgOrVideo.width || 350;
                const iHeight = imgOrVideo.naturalHeight || imgOrVideo.height || 350;
                const size = Math.min(iWidth, iHeight);
                const cropSize = size * cropFactor;
                sx = (iWidth - cropSize) / 2;
                sy = (iHeight - cropSize) / 2;
                ctx.drawImage(imgOrVideo, sx, sy, cropSize, cropSize, 0, 0, width, height);
            } else {
                ctx.drawImage(imgOrVideo, 0, 0, width, height);
            }
            
            const imgData = ctx.getImageData(0, 0, width, height);
            const data = imgData.data;
            
            const signature = [];
            for (let i = 0; i < data.length; i += 4) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                // Formula Grayscale standard
                const gray = 0.299 * r + 0.587 * g + 0.114 * b;
                signature.push(gray);
            }
            
            // Normalisasi ke rentang 0-1
            const max = Math.max(...signature);
            const min = Math.min(...signature);
            const range = max - min || 1;
            const normalized = signature.map(val => (val - min) / range);
            
            // Apply Sobel edge detection to highlight structural details (eyes, nose, mouth)
            const edgeSig = new Array(normalized.length).fill(0);
            for (let y = 1; y < height - 1; y++) {
                for (let x = 1; x < width - 1; x++) {
                    const idx = y * width + x;
                    const gx = 
                        (normalized[(y-1)*width + (x+1)] + 2 * normalized[y*width + (x+1)] + normalized[(y+1)*width + (x+1)]) -
                        (normalized[(y-1)*width + (x-1)] + 2 * normalized[y*width + (x-1)] + normalized[(y+1)*width + (x-1)]);
                    const gy = 
                        (normalized[(y+1)*width + (x-1)] + 2 * normalized[(y+1)*width + x] + normalized[(y+1)*width + (x+1)]) -
                        (normalized[(y-1)*width + (x-1)] + 2 * normalized[(y-1)*width + x] + normalized[(y-1)*width + (x+1)]);
                    edgeSig[idx] = Math.sqrt(gx * gx + gy * gy);
                }
            }
            
            const eMax = Math.max(...edgeSig);
            const eMin = Math.min(...edgeSig);
            const eRange = eMax - eMin || 1;
            
            const combined = [];
            for (let i = 0; i < normalized.length; i++) {
                const normEdge = (edgeSig[i] - eMin) / eRange;
                combined.push(0.25 * normalized[i] + 0.75 * normEdge);
            }
            return combined;
        }

        // Helper: Hitung tingkat kemiripan (similarity %)
        function getSimilarity(sig1, sig2) {
            if (!sig1 || !sig2 || sig1.length !== sig2.length) return 0;
            
            const size = 32; // 32x32 grid
            let minDiff = Infinity;
            
            for (let dy = -2; dy <= 2; dy++) {
                for (let dx = -2; dx <= 2; dx++) {
                    let diff = 0;
                    let count = 0;
                    
                    for (let y = 0; y < size; y++) {
                        for (let x = 0; x < size; x++) {
                            const ny = y + dy;
                            const nx = x + dx;
                            
                            if (ny >= 0 && ny < size && nx >= 0 && nx < size) {
                                const idx1 = y * size + x;
                                const idx2 = ny * size + nx;
                                diff += Math.abs(sig1[idx1] - sig2[idx2]);
                                count++;
                            }
                        }
                    }
                    
                    if (count > 0) {
                        const avgDiff = diff / count;
                        if (avgDiff < minDiff) {
                            minDiff = avgDiff;
                        }
                    }
                }
            }
            
            return Math.max(0, Math.min(100, (1 - minDiff) * 100));
        }

        // Preload foto wajah untuk menghasilkan signature
        function preloadSignatures() {
            employees.forEach(emp => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = () => {
                    emp.signature = getImageSignature(img);
                    console.log(`[Biometrik] Loaded signature for ${emp.name}`);
                };
                img.src = emp.photoUrl;
            });
        }

        // Inisialisasi kamera
        async function startBiometricKiosk() {
            instructions.classList.remove('hidden');
            instructions.innerHTML = "🔄 Mengaktifkan Pemindai Biometrik...";
            
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { width: 350, height: 350 } });
                video.srcObject = stream;
                placeholder.classList.add('hidden');
                laser.classList.remove('hidden');
                statusOverlay.classList.remove('hidden');
                statusOverlay.innerHTML = "⚡ MEMINDAI WAJAH...";
                statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#8B5CF6]/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#8B5CF6] uppercase z-20 shadow-lg";
                
                instructions.innerHTML = "🎯 Stasiun Biometrik Aktif. Hadapkan wajah Anda ke kamera...";
                
                if (scanInterval) clearInterval(scanInterval);
                scanInterval = setInterval(performLiveFaceScan, 800);
                
            } catch (err) {
                console.error("Gagal menginisialisasi kamera:", err);
                instructions.innerHTML = "⚠️ Gagal mengakses kamera. Silakan gunakan input manual.";
                placeholder.innerHTML = `<span class="text-4xl block mb-2">⚠️</span><span class="text-xs font-semibold text-[#B91C1C]">Izin Kamera Ditolak</span>`;
                btnStartScan.classList.remove('hidden');
            }
        }

        // Bandingkan live frame dengan database
        function performLiveFaceScan() {
            if (isProcessingMatch || !stream) return;
            
            const liveSig = getImageSignature(video);
            
            if (activeTab === 'directed' && targetUserId) {
                const threshold = 76; // Lenient threshold since only matching against self
                const emp = employees.find(e => e.id == targetUserId);
                if (emp && emp.signature) {
                    const sim = getSimilarity(liveSig, emp.signature);
                    
                    statusOverlay.innerHTML = `⚡ MENCOCOKKAN WAJAH: ${sim.toFixed(1)}%`;
                    
                    if (sim >= threshold) {
                        if (manualScanTimeout) clearTimeout(manualScanTimeout);
                        startLivenessChallenge(emp.id, emp.name, sim);
                    }
                }
            } else if (activeTab === 'auto') {
                const threshold = 84; // Strict threshold to prevent mix-ups
                let bestMatch = null;
                let maxSimilarity = 0;
                
                employees.forEach(emp => {
                    if (!emp.signature) return;
                    const sim = getSimilarity(liveSig, emp.signature);
                    if (sim > maxSimilarity) {
                        maxSimilarity = sim;
                        bestMatch = emp;
                    }
                });
                
                if (bestMatch && maxSimilarity >= threshold) {
                    startLivenessChallenge(bestMatch.id, bestMatch.name, maxSimilarity);
                }
            }
        }

        // Lock wajah dan jalankan tantangan keaktifan (anti-spoofing)
        function startLivenessChallenge(userId, userName, score) {
            isProcessingMatch = true;
            livenessState = "prompt_blink";
            livenessHistory = [];
            
            if (scanInterval) clearInterval(scanInterval);
            
            laser.className = "absolute left-0 right-0 h-1 bg-[#6366F1] shadow-[0_0_8px_#6366F1] z-10 animate-scan";
            
            statusOverlay.innerHTML = `⚡ KEDIPKAN MATA ANDA! 😉`;
            statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#6366F1]/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#6366F1] uppercase z-20 shadow-lg animate-pulse";
            
            instructions.innerHTML = `🔒 Wajah cocok dengan <strong>${userName}</strong> (${score.toFixed(1)}%).<br><span class="text-[#6366F1] font-bold">Harap berkedip sekali untuk verifikasi keaktifan wajah!</span>`;
            btnReset.classList.remove('hidden');
            
            scanInterval = setInterval(() => {
                if (livenessState !== "prompt_blink") return;
                
                const liveSig = getImageSignature(video);
                
                const L = getRegionAverage(liveSig, 7, 12, 9, 13);
                const R = getRegionAverage(liveSig, 20, 25, 9, 13);
                const F = getRegionAverage(liveSig, 12, 20, 3, 7);
                
                if (livenessHistory.length >= 5) {
                    let sumL = 0, sumR = 0, sumF = 0;
                    livenessHistory.forEach(h => {
                        sumL += h.L;
                        sumR += h.R;
                        sumF += h.F;
                    });
                    const avgL = sumL / livenessHistory.length;
                    const avgR = sumR / livenessHistory.length;
                    const avgF = sumF / livenessHistory.length;
                    
                    const diffL = Math.abs(L - avgL);
                    const diffR = Math.abs(R - avgR);
                    const diffF = Math.abs(F - avgF);
                    
                    const eyeChange = Math.max(diffL, diffR);
                    
                    if (eyeChange > 0.045 && diffF < 0.03) {
                        livenessState = "liveness_verified";
                        clearInterval(scanInterval);
                        if (livenessTimeout) clearTimeout(livenessTimeout);
                        
                        laser.className = "absolute left-0 right-0 h-1 bg-emerald-500 shadow-[0_0_8px_#10b981] z-10 animate-scan";
                        
                        statusOverlay.innerHTML = `🎯 KEAKTIFAN TERVERIFIKASI`;
                        statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#ECFDF5]/90 border border-emerald-550 border-emerald-400/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-emerald-600 uppercase z-20 shadow-lg";
                        
                        instructions.innerHTML = `✅ Keaktifan terverifikasi! Mengirim presensi...`;
                        
                        scanTimeout = setTimeout(() => {
                            submitAttendance(userId);
                        }, 1200);
                        return;
                    }
                }
                
                livenessHistory.push({ L, R, F });
                if (livenessHistory.length > 5) {
                    livenessHistory.shift();
                }
            }, 100);
            
            livenessTimeout = setTimeout(() => {
                if (livenessState === "prompt_blink") {
                    livenessState = "liveness_failed";
                    clearInterval(scanInterval);
                    
                    laser.className = "hidden absolute left-0 right-0 h-1 bg-rose-500 shadow-[0_0_8px_#f43f5e] z-10 animate-scan";
                    laser.classList.add('hidden');
                    
                    statusOverlay.innerHTML = `❌ VERIFIKASI KEAKTIFAN GAGAL`;
                    statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FEF2F2]/90 border border-red-400/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#B91C1C] uppercase z-20 shadow-lg";
                    
                    instructions.innerHTML = `⚠️ <span class="text-[#B91C1C] font-bold">Verifikasi Gagal: Wajah terdeteksi tidak aktif!</span> Memulai ulang pemindaian dalam 3 detik...`;
                    
                    scanTimeout = setTimeout(() => {
                        resetState();
                        startBiometricKiosk();
                    }, 3000);
                }
            }, 8000);
        }

        // Capture gambar akhir dan kirim data ke controller
        function submitAttendance(userId) {
            const context = canvas.getContext('2d');
            canvas.width = 350;
            canvas.height = 350;
            
            try {
                const vWidth = video.videoWidth || 350;
                const vHeight = video.videoHeight || 350;
                const size = Math.min(vWidth, vHeight);
                const sx = (vWidth - size) / 2;
                const sy = (vHeight - size) / 2;
                
                context.translate(350, 0);
                context.scale(-1, 1);
                context.drawImage(video, sx, sy, size, size, 0, 0, 350, 350);
                context.setTransform(1, 0, 0, 1, 0, 0);
            } catch (e) {
                context.fillStyle = "#FAF9F6";
                context.fillRect(0, 0, 350, 350);
                context.fillStyle = "#111111";
                context.font = "14px Outfit";
                context.fillText("Biometric Verification Verified", 50, 175);
            }
            
            const snap = canvas.toDataURL('image/png');
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            
            photoInput.value = snap;
            userIdInput.value = userId;
            isManualInput.value = "0";
            attForm.submit();
        }

        // Batal & Reset Mode Pemindai
        function resetState() {
            if (scanInterval) clearInterval(scanInterval);
            if (scanTimeout) clearTimeout(scanTimeout);
            if (manualScanTimeout) clearTimeout(manualScanTimeout);
            if (livenessTimeout) clearTimeout(livenessTimeout);
            
            livenessState = "idle";
            livenessHistory = [];
            targetUserId = null;
            selectUser.value = "";
            isProcessingMatch = false;
            laser.className = "hidden absolute left-0 right-0 h-1 bg-red-500 shadow-[0_0_8px_#f43f5e] z-10 animate-scan";
            laser.classList.add('hidden');
            
            statusOverlay.classList.add('hidden');
            btnReset.classList.add('hidden');
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
            placeholder.classList.remove('hidden');
            instructions.classList.add('hidden');
            
            btnStartScan.classList.remove('hidden');
        }

        // Switch Mode / Tab
        function switchTab(tabName) {
            activeTab = tabName;
            resetState();
            
            [tabAuto, tabDirected, tabManual].forEach(tab => {
                tab.classList.remove('text-[#6366F1]', 'border-[#6366F1]');
                tab.classList.add('text-[#71717A]', 'border-transparent');
            });
            
            const activeElement = document.getElementById(`tab-${tabName}`);
            activeElement.classList.add('text-[#6366F1]', 'border-[#6366F1]');
            activeElement.classList.remove('text-[#71717A]', 'border-transparent');
            
            if (tabName === 'auto') {
                panelTitle.innerText = "Stasiun Pemindai Hands-Free";
                panelDesc.innerText = "Kamera biometrik akan mendeteksi dan mengenali wajah Anda secara otomatis. Cukup berdiri tegak di depan kamera.";
                
                employeeSelectContainer.classList.add('hidden');
                manualSubmissionContainer.classList.add('hidden');
                
                scannerVisualContainer.classList.remove('hidden');
                btnStartScanContainer.classList.remove('hidden');
            } else if (tabName === 'directed') {
                panelTitle.innerText = "Pemindaian Terarah Berbasis Nama";
                panelDesc.innerText = "Pilih nama Anda terlebih dahulu di bawah, lalu hadapkan wajah Anda ke kamera untuk verifikasi biometrik.";
                
                employeeSelectContainer.classList.remove('hidden');
                manualSubmissionContainer.classList.add('hidden');
                
                scannerVisualContainer.classList.remove('hidden');
                btnStartScanContainer.classList.remove('hidden');
            } else if (tabName === 'manual') {
                panelTitle.innerText = "Presensi Manual Tanpa Kamera";
                panelDesc.innerText = "Pilih nama Anda di bawah, kemudian klik tombol kirim untuk melakukan presensi langsung tanpa verifikasi wajah.";
                
                employeeSelectContainer.classList.add('hidden');
                manualSubmissionContainer.classList.remove('hidden');
                
                scannerVisualContainer.classList.add('hidden');
                btnStartScanContainer.classList.add('hidden');
            }
        }
        
        tabAuto.addEventListener('click', () => switchTab('auto'));
        tabDirected.addEventListener('click', () => switchTab('directed'));
        tabManual.addEventListener('click', () => switchTab('manual'));

        // Trigger manual dropdown selection for Directed Scan
        selectUser.addEventListener('change', async () => {
            if (!selectUser.value) {
                resetState();
                return;
            }
            
            const selectedOption = selectUser.options[selectUser.selectedIndex];
            
            if (selectedOption.getAttribute('data-face') === 'false') {
                alert("Presensi ditolak! Wajah Anda belum terdaftar di sistem. Harap hubungi Admin untuk melakukan registrasi wajah terlebih dahulu.");
                selectUser.value = "";
                return;
            }

            if (scanInterval) clearInterval(scanInterval);
            if (manualScanTimeout) clearTimeout(manualScanTimeout);
            if (scanTimeout) clearTimeout(scanTimeout);
            isProcessingMatch = false;
            
            targetUserId = selectUser.value;
            instructions.classList.remove('hidden');
            instructions.innerHTML = `🔄 Mengaktifkan kamera untuk verifikasi <strong>${selectedOption.text.split('(')[0].trim()}</strong>...`;
            
            try {
                if (!stream) {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { width: 350, height: 350 } });
                    video.srcObject = stream;
                    placeholder.classList.add('hidden');
                }
                
                laser.classList.remove('hidden');
                laser.className = "absolute left-0 right-0 h-1 bg-[#8B5CF6] shadow-[0_0_8px_#8B5CF6] z-10 animate-scan";
                
                statusOverlay.classList.remove('hidden');
                statusOverlay.innerHTML = "⚡ MENCOCOKKAN WAJAH...";
                statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#8B5CF6]/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#8B5CF6] uppercase z-20 shadow-lg";
                
                instructions.innerHTML = `🎯 Harap hadapkan wajah Anda ke kamera untuk verifikasi <strong>${selectedOption.text.split('(')[0].trim()}</strong>...`;
                btnReset.classList.remove('hidden');
                
                scanInterval = setInterval(performLiveFaceScan, 800);
                
                manualScanTimeout = setTimeout(() => {
                    if (isProcessingMatch) return; 
                    
                    if (scanInterval) clearInterval(scanInterval);
                    instructions.innerHTML = `⚠️ <span class="text-[#B91C1C] font-bold">Verifikasi Wajah Gagal!</span> Wajah tidak cocok dengan data terdaftar <strong>${selectedOption.text.split('(')[0].trim()}</strong>. Silakan coba lagi.`;
                    
                    statusOverlay.innerHTML = "❌ VERIFIKASI GAGAL";
                    statusOverlay.className = "absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#FEF2F2]/90 border border-red-450 border-red-400/40 backdrop-blur-sm rounded-full px-4 py-1.5 text-[9px] font-extrabold tracking-widest text-[#B91C1C] uppercase z-20 shadow-lg";
                    
                    laser.classList.add('hidden');
                    isProcessingMatch = true; 
                }, 10000);
                
            } catch (err) {
                console.error("Gagal mengaktifkan kamera manual:", err);
                alert("Gagal mengakses kamera. Harap pastikan izin kamera diizinkan.");
                resetState();
            }
        });

        // Submit manual attendance handler
        btnSubmitManual.addEventListener('click', () => {
            const val = selectUserManual.value;
            if (!val) {
                alert("Silakan pilih nama karyawan terlebih dahulu.");
                return;
            }
            const empName = selectUserManual.options[selectUserManual.selectedIndex].text.split('(')[0].trim();
            if (confirm(`Apakah Anda yakin ingin melakukan presensi secara manual untuk "${empName}"?`)) {
                userIdInput.value = val;
                photoInput.value = "";
                isManualInput.value = "1";
                attForm.submit();
            }
        });

        // Event listener untuk tombol scan wajah baru
        btnStartScan.addEventListener('click', () => {
            btnStartScan.classList.add('hidden');
            startBiometricKiosk();
        });

        btnReset.addEventListener('click', resetState);

        // Preload signatures saja saat halaman siap
        window.addEventListener('DOMContentLoaded', preloadSignatures);
    </script>
</x-app-layout>
