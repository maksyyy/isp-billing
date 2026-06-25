<x-app-layout>
<div class="p-6">
    <div class="max-w-2xl app-card p-6 mx-auto">
        <p class="text-xs font-extrabold text-[#6366F1] uppercase tracking-wider">
            {{ auth()->user()->role == 'master' ? 'Admin Penyewa' : 'Sub User' }}
        </p>
        <h2 class="text-2xl font-bold mt-1 text-[#111111]">
            Tambah {{ auth()->user()->role == 'master' ? 'Admin Baru' : 'Sub User Baru' }}
        </h2>
        <p class="text-[#71717A] text-xs mt-1 mb-6">
            {{ auth()->user()->role == 'master'
                ? 'Admin baru dapat mengelola tim finance, NOC, dan teknisi masing-masing.'
                : 'Sub-user akan otomatis berada di bawah akun admin Anda.' }}
        </p>

        <form id="create-user-form" action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('name')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('email')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('phone')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Telegram Chat ID (Opsional)</label>
                <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                @error('telegram_chat_id')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('password')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

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

            <!-- SCAN WAJAH (WEBCAM) -->
            <div id="face-scan-container" class="border border-[#E4E4E7] p-4 rounded-md bg-[#F4F4F5] mb-4 transition-all duration-300 shadow-sm">
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">
                    Registrasi Wajah (Webcam Scan) <span class="text-[#B91C1C] font-bold">* Wajib</span>
                </label>
                
                <div class="flex flex-col items-center gap-3">
                    <!-- Video player for live feed -->
                    <div class="relative w-64 h-64 bg-[#FFFFFF] rounded-md overflow-hidden border border-dashed border-[#E4E4E7] flex items-center justify-center shadow-inner">
                        <video id="webcam" class="w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                        <canvas id="canvas" class="hidden absolute w-full h-full object-cover transform -scale-x-100"></canvas>
                        
                        <!-- Laser line animation -->
                        <div id="scanner-laser" class="hidden absolute left-0 right-0 h-1 bg-[#8B5CF6] shadow-[0_0_8px_#8B5CF6] z-10 animate-scan"></div>
                        
                        <!-- Circular border frame indicator -->
                        <div class="absolute inset-4 border border-[#6366F1]/20 rounded-md pointer-events-none"></div>
                        
                        <!-- Status text overlay -->
                        <div id="biometric-status-overlay" class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#8B5CF6]/40 backdrop-blur-sm rounded-full px-3 py-1 text-[9px] font-extrabold tracking-widest text-[#8B5CF6] uppercase hidden z-20 shadow-lg">
                            ⚡ MEMINDAI WAJAH...
                        </div>
 
                        <div id="camera-placeholder" class="absolute text-center text-[#71717A] p-2">
                            <span class="text-3xl">📷</span>
                            <p class="text-xs mt-1">Kamera belum aktif</p>
                        </div>
                    </div>
 
                     <!-- Capture Buttons -->
                    <div class="flex gap-2">
                        <button type="button" id="btn-start-camera" class="btn-minimal-secondary px-3 py-1.5">
                            Aktifkan Kamera
                        </button>
                        <button type="button" id="btn-capture" class="hidden btn-minimal px-3 py-1.5">
                            Mulai Scan Wajah
                        </button>
                        <button type="button" id="btn-retake" class="hidden bg-[#FEE2E2] text-[#B91C1C] border border-[#FECACA] rounded-md px-3 py-1.5 font-bold uppercase tracking-wider text-xs hover:bg-[#FECACA] active:scale-95 transition-all shadow-sm">
                            Ambil Ulang
                        </button>
                    </div>
 
                    <!-- Hidden input to store base64 string -->
                    <input type="hidden" name="face_photo" id="face_photo_input">
                    
                    @error('face_photo')
                        <p class="text-[#B91C1C] text-sm mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
 
            <!-- CUSTOMER LIMIT -->
            <div id="customer-limit-container" class="mb-4 hidden transition-all duration-300">
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Limit Jumlah Pelanggan</label>
                <select name="customer_limit" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                    <option value="200" @selected(old('customer_limit') == 200)>200</option>
                    <option value="500" @selected(old('customer_limit') == 500)>500</option>
                    <option value="1000" @selected(old('customer_limit') == 1000)>1000</option>
                    <option value="2000" @selected(old('customer_limit') == 2000)>2000</option>
                    <option value="3000" @selected(old('customer_limit') == 3000)>3000</option>
                    <option value="4000" @selected(old('customer_limit') == 4000)>4000</option>
                    <option value="5000" @selected(old('customer_limit') == 5000)>5000</option>
                </select>
                @error('customer_limit')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
 
            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Role</label>
                <select name="role" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 pt-4">
                <button class="btn-minimal">
                    Simpan
                </button>
                <a href="{{ route('users.index') }}" class="btn-minimal-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const facePhotoInput = document.getElementById('face_photo_input');
        const placeholder = document.getElementById('camera-placeholder');
        
        const btnStart = document.getElementById('btn-start-camera');
        const btnCapture = document.getElementById('btn-capture');
        const btnRetake = document.getElementById('btn-retake');
        
        const laser = document.getElementById('scanner-laser');
        const statusOverlay = document.getElementById('biometric-status-overlay');
        let stream = null;
        let scanInterval = null;

        // Data karyawan terdaftar untuk validasi duplikasi wajah
        const existingUsers = [
            @foreach($existingUsers as $ex)
                {
                    id: "{{ $ex->id }}",
                    name: "{{ $ex->name }}",
                    photoUrl: "{{ asset('storage/' . $ex->face_photo) }}",
                    signature: null
                },
            @endforeach
        ];

        // Helper: Ekstrak signature piksel 32x32 untuk akurasi lebih tinggi (1024 titik detail)
        function getImageSignature(imgOrVideo, width = 32, height = 32) {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = width;
            tempCanvas.height = height;
            const ctx = tempCanvas.getContext('2d');
            
            // Crop center square to focus on the face and exclude the outer background
            // We use a tighter crop factor (0.58) to zoom into the face region and exclude background/hair/clothing.
            const cropFactor = 0.58;
            let sx = 0, sy = 0;
            if (imgOrVideo instanceof HTMLVideoElement) {
                const vWidth = imgOrVideo.videoWidth || 300;
                const vHeight = imgOrVideo.videoHeight || 300;
                const size = Math.min(vWidth, vHeight);
                const cropSize = size * cropFactor;
                sx = (vWidth - cropSize) / 2;
                sy = (vHeight - cropSize) / 2;
                
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(imgOrVideo, sx, sy, cropSize, cropSize, 0, 0, width, height);
                ctx.setTransform(1, 0, 0, 1, 0, 0);
            } else if (imgOrVideo instanceof HTMLImageElement) {
                const iWidth = imgOrVideo.naturalWidth || imgOrVideo.width || 300;
                const iHeight = imgOrVideo.naturalHeight || imgOrVideo.height || 300;
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
                const gray = 0.299 * r + 0.587 * g + 0.114 * b;
                signature.push(gray);
            }
            
            const max = Math.max(...signature);
            const min = Math.min(...signature);
            const range = max - min || 1;
            const normalized = signature.map(val => (val - min) / range);
            
            // Apply Sobel edge detection to highlight structural details (eyes, nose, mouth)
            // and reduce false positive matches between different people.
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
            
            // Combine 25% grayscale and 75% structural edges for highly robust facial signature
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
            
            // Shift live frame relative to registered image in X and Y to find the best match (tolerates head movement)
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
            existingUsers.forEach(usr => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = () => {
                    usr.signature = getImageSignature(img);
                    console.log(`[Biometrik] Loaded signature for existing user: ${usr.name}`);
                };
                img.src = usr.photoUrl;
            });
        }

        // Mulai preloading signature saat halaman siap
        preloadSignatures();

        btnStart.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { width: 300, height: 300 } });
                video.srcObject = stream;
                placeholder.classList.add('hidden');
                btnStart.classList.add('hidden');
                btnCapture.classList.remove('hidden');
            } catch (err) {
                console.error("Gagal mengakses webcam:", err);
                alert("Gagal mengakses kamera. Harap pastikan izin kamera diberikan dan Anda mengakses via HTTPS / localhost.");
            }
        });

        btnCapture.addEventListener('click', () => {
            // Tampilkan laser dan status pemindaian
            laser.classList.remove('hidden');
            laser.className = "absolute left-0 right-0 h-1 bg-[#8B5CF6] shadow-[0_0_8px_#8B5CF6] z-10 animate-scan";
            statusOverlay.classList.remove('hidden');
            statusOverlay.innerHTML = "⚡ MEMINDAI WAJAH: 0%";
            statusOverlay.className = "absolute bottom-4 left-1/2 -translate-x-1/2 bg-[#FFFFFF]/90 border border-[#8B5CF6]/40 backdrop-blur-sm rounded-full px-3 py-1 text-[9px] font-extrabold tracking-widest text-[#8B5CF6] uppercase z-20 shadow-lg";
            
            btnCapture.classList.add('hidden');
            
            let progress = 0;
            let duplicateFound = null;
            let maxSimilarity = 0;
            const threshold = 76; // Ambang batas kemiripan duplikasi wajah

            if (scanInterval) clearInterval(scanInterval);

            scanInterval = setInterval(() => {
                progress += 5;
                statusOverlay.innerHTML = `⚡ MEMINDAI WAJAH: ${progress}%`;
                
                // Ambil signature wajah dari video secara langsung (otomatis di-mirror di getImageSignature)
                const currentSig = getImageSignature(video);

                existingUsers.forEach(usr => {
                    if (!usr.signature) return;
                    const sim = getSimilarity(currentSig, usr.signature);
                    if (sim > maxSimilarity) {
                        maxSimilarity = sim;
                        duplicateFound = usr;
                    }
                });

                // Jika terdeteksi kemiripan yang sangat tinggi sebelum selesai
                if (duplicateFound && maxSimilarity >= threshold) {
                    clearInterval(scanInterval);
                    
                    statusOverlay.innerHTML = "❌ DUPLIKAT TERDETEKSI";
                    statusOverlay.className = "absolute bottom-4 left-1/2 -translate-x-1/2 bg-[#FEE2E2]/90 border border-red-455 border-red-400/40 backdrop-blur-sm rounded-full px-3 py-1 text-[9px] font-extrabold tracking-widest text-[#B91C1C] uppercase z-20 shadow-lg";
                    laser.classList.add('hidden');
                    
                    alert(`⚠️ Pendaftaran Wajah Ditolak!\n\nWajah yang dideteksi sangat mirip dengan karyawan lain (${duplicateFound.name} - ${maxSimilarity.toFixed(1)}% Match).\n\nMohon pastikan wajah yang didaftarkan unik dan bukan milik karyawan lain.`);
                    
                    // Reset kamera dan tombol
                    statusOverlay.classList.add('hidden');
                    btnCapture.classList.remove('hidden');
                    return;
                }

                if (progress >= 100) {
                    clearInterval(scanInterval);
                    
                    // Selesai pindai: Ambil foto final
                    const context = canvas.getContext('2d');
                    canvas.width = 300;
                    canvas.height = 300;
                    
                    // Crop center square
                    const vWidth = video.videoWidth || 300;
                    const vHeight = video.videoHeight || 300;
                    const size = Math.min(vWidth, vHeight);
                    const sx = (vWidth - size) / 2;
                    const sy = (vHeight - size) / 2;
                    
                    // Mirror draw
                    context.translate(300, 0);
                    context.scale(-1, 1);
                    context.drawImage(video, sx, sy, size, size, 0, 0, 300, 300);
                    context.setTransform(1, 0, 0, 1, 0, 0);
                    
                    // Simpan data
                    const dataUrl = canvas.toDataURL('image/png');
                    facePhotoInput.value = dataUrl;
                    
                    // Tampilkan hasil
                    canvas.classList.remove('hidden');
                    video.classList.add('hidden');
                    
                    laser.classList.add('hidden');
                    statusOverlay.classList.add('hidden');
                    btnRetake.classList.remove('hidden');

                    // Matikan kamera
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                }
            }, 100);
        });

        btnRetake.addEventListener('click', async () => {
            facePhotoInput.value = '';
            canvas.classList.add('hidden');
            video.classList.remove('hidden');
            btnRetake.classList.add('hidden');
            btnCapture.classList.remove('hidden');

            // Sembunyikan laser & status
            laser.classList.add('hidden');
            statusOverlay.classList.add('hidden');
            if (scanInterval) clearInterval(scanInterval);

            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { width: 300, height: 300 } });
                video.srcObject = stream;
            } catch (err) {
                console.error("Gagal menyalakan ulang kamera:", err);
            }
        });

        // Toggle fields based on role
        const roleSelect = document.querySelector('select[name="role"]');
        const faceScanContainer = document.getElementById('face-scan-container');
        const customerLimitContainer = document.getElementById('customer-limit-container');

        function toggleRoleFields() {
            if (roleSelect && roleSelect.value === 'admin') {
                faceScanContainer.classList.add('hidden');
                customerLimitContainer.classList.remove('hidden');
            } else {
                faceScanContainer.classList.remove('hidden');
                customerLimitContainer.classList.add('hidden');
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleRoleFields);
            // Run on page load
            toggleRoleFields();
        }

        // Validasi form saat submit
        const form = document.getElementById('create-user-form');
        form.addEventListener('submit', (e) => {
            if (roleSelect && roleSelect.value === 'admin') {
                return; // Bypass validation for admin role
            }
            if (!facePhotoInput.value) {
                e.preventDefault();
                alert("⚠️ Pendaftaran Wajah Wajib! Harap aktifkan kamera dan lakukan pemindaian wajah terlebih dahulu sebelum menyimpan user.");
                
                // Scroll ke area scan wajah
                btnStart.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Beri efek visual border merah berkedip
                const scanContainer = btnStart.closest('.border');
                if (scanContainer) {
                    scanContainer.classList.add('border-red-400', 'ring-2', 'ring-red-200');
                    setTimeout(() => {
                        scanContainer.classList.remove('border-red-400', 'ring-2', 'ring-red-200');
                    }, 3000);
                }
            }
        });
    </script>
</x-app-layout>
