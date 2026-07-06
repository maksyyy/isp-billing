<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6 text-[#111111] flex items-center gap-2 font-heading">
            📅 Kios Presensi Mandiri Karyawan
        </h2>

        <!-- ALERT STATUS -->
        @if(session('success'))
            <div class="bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] px-4 py-3 rounded-md mb-6 font-medium shadow-sm flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] px-4 py-3 rounded-md mb-6 font-medium shadow-sm flex items-center gap-2">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8">

            <!-- ============================================ -->
            <!-- ✍️ FORM PRESENSI MANUAL CEPAT                -->
            <!-- ============================================ -->
            <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs rounded-xl flex flex-col justify-between">
                <div class="w-full">
                    <div class="border-b border-[#F4F4F5] pb-3 mb-6">
                        <h3 class="font-bold text-lg text-[#111111] font-heading flex items-center gap-2">
                            📝 Form Kehadiran Staf
                        </h3>
                        <p class="text-xs text-[#71717A] font-light mt-1">
                            Silakan pilih nama Anda pada daftar di bawah untuk mencatat kehadiran masuk atau keluar hari ini.
                        </p>
                    </div>

                    <form action="{{ route('presensi.store') }}" method="POST" class="space-y-6" onsubmit="return confirmPresensi(this);">
                        @csrf
                        <input type="hidden" name="is_manual" value="1">
                        <input type="hidden" name="photo" value="">
                        
                        <div>
                            <label class="block text-[9px] font-bold text-[#71717A] uppercase tracking-wider mb-2">Pilih Nama Anda</label>
                            <select name="user_id" id="user_id_select" required class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] font-semibold transition-all shadow-sm cursor-pointer">
                                <option value="">-- Pilih Nama Karyawan --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->name }} ({{ strtoupper($emp->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Check-In Action Button -->
                        <div class="pt-2">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-650 hover:to-emerald-700 text-[#FFFFFF] font-bold text-xs px-6 py-3 rounded-md shadow-xs transition-transform active:scale-[0.98] border-0 cursor-pointer">
                                🚪 Catat Kehadiran Hari Ini
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 pt-4 border-t border-[#F4F4F5]">
                    <span class="block text-[8px] font-extrabold text-[#71717A] uppercase tracking-widest">Informasi Sistem</span>
                    <p class="text-[10px] text-[#71717A] mt-1 font-light leading-relaxed">
                        Sistem kehadiran ini mencatat jam check-in (masuk) dan check-out (pulang) secara berurutan. Presensi pertama yang dikirim akan dicatat sebagai masuk, dan presensi berikutnya pada hari yang sama dicatat sebagai pulang.
                    </p>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- 📊 REKAP KEHADIRAN HARI INI (SUMMARY PANEL) -->
            <!-- ============================================ -->
            <div class="app-card p-6 bg-white border border-[#E4E4E7] shadow-xs rounded-xl">
                <h3 class="font-bold text-lg text-[#111111] border-b border-[#E4E4E7] pb-3 mb-6 flex items-center gap-2 font-heading">
                    ⏱️ Rekap Kehadiran Hari Ini
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <!-- Tepat Waktu -->
                    <div class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#047857] uppercase tracking-wide">Tepat Waktu</span>
                        <p class="text-2xl font-extrabold text-[#047857] mt-2 font-mono">
                            {{ $logs->where('status', 'Hadir')->count() }}
                        </p>
                        <span class="text-[10px] text-[#065F46]/80 mt-1">Staf tepat waktu</span>
                    </div>

                    <!-- Terlambat -->
                    <div class="bg-[#FFFBEB] border border-[#FDE68A] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#B45309] uppercase tracking-wide">Terlambat</span>
                        <p class="text-2xl font-extrabold text-[#B45309] mt-2 font-mono">
                            {{ $logs->where('status', 'Terlambat')->count() }}
                        </p>
                        <span class="text-[10px] text-[#92400E]/80 mt-1">Melewati batas masuk</span>
                    </div>

                    <!-- Lembur / Extra Time -->
                    <div class="bg-[#FFF7ED] border border-[#FFEDD5] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-[#C2410C] uppercase tracking-wide">Lembur</span>
                        <p class="text-2xl font-extrabold text-[#C2410C] mt-2 font-mono">
                            {{ $logs->where('lembur', '>', 0)->count() }}
                        </p>
                        <span class="text-[10px] text-[#9A3412]/80 mt-1">Checkout > 16:00</span>
                    </div>
                </div>

                <div class="border-t border-[#E4E4E7] pt-4">
                    <h4 class="font-bold text-xs text-[#71717A] uppercase tracking-wider mb-2">Ketentuan Jam Kerja</h4>
                    <ul class="text-xs text-[#71717A] space-y-1.5 font-light">
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
        <div class="app-card p-6 mt-8 bg-white border border-[#E4E4E7] shadow-xs rounded-xl">
            <h3 class="font-bold text-lg text-[#111111] mb-6 flex items-center gap-2 font-heading">
                📋 Live Board Kehadiran Staf Hari Ini
            </h3>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nama Staf</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-indigo-50 border border-indigo-150 text-indigo-600 font-bold rounded-md flex items-center justify-center text-xs shadow-xs">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-bold text-sm text-[#111111]">{{ $log->user->name }}</span>
                                            <span class="block text-[9px] text-[#71717A] font-bold uppercase tracking-wider">{{ $log->user->role }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-[#059669] font-bold font-mono text-xs">
                                    {{ \Carbon\Carbon::parse($log->jam_masuk)->format('H:i') }} WIB
                                </td>
                                <td class="p-3 text-[#DC2626] font-bold font-mono text-xs">
                                    {{ $log->jam_keluar ? \Carbon\Carbon::parse($log->jam_keluar)->format('H:i') . ' WIB' : '-' }}
                                    @if($log->lembur > 0)
                                        @php
                                            $hours = floor($log->lembur / 60);
                                            $minutes = $log->lembur % 60;
                                            $durationStr = ($hours > 0 ? $hours . ' jam ' : '') . ($minutes > 0 ? $minutes . ' menit' : '');
                                        @endphp
                                        <span class="block text-[9px] text-indigo-600 font-extrabold uppercase mt-1">
                                            🔥 Lembur: {{ $durationStr }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    @if($log->status === 'Hadir')
                                        <span class="inline-flex px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider shadow-xs">
                                            {{ $log->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A] shadow-xs">
                                            {{ $log->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-[#71717A] font-semibold font-mono text-xs">
                                    [Belum ada staf yang melakukan presensi hari ini]
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function confirmPresensi(form) {
            const select = document.getElementById('user_id_select');
            if (!select.value) {
                alert("Silakan pilih nama karyawan terlebih dahulu.");
                return false;
            }
            const empName = select.options[select.selectedIndex].text.trim();
            return confirm(`Apakah Anda yakin ingin mengirim presensi kehadiran untuk "${empName}"?`);
        }
    </script>
</x-app-layout>
