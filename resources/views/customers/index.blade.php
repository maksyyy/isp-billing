<x-app-layout>
<div class="p-6">

    <!-- PREMIUM TOGGLE CUSTOM CSS -->
    <style>
        .switch-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .switch-label {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .switch-input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 34px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .switch-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .switch-input:checked + .switch-slider {
            background-color: #10b981;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
        }

        .switch-input:checked + .switch-slider:before {
            transform: translateX(24px);
        }

        .switch-input:disabled + .switch-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .status-glow-active {
            text-shadow: 0 0 6px rgba(16, 185, 129, 0.3);
        }

        .status-glow-inactive {
            text-shadow: 0 0 6px rgba(239, 68, 68, 0.3);
        }
    </style>

    <h2 class="text-2xl font-bold mb-4 text-gray-800">Data Pelanggan</h2>

    <!-- NOTIFIKASI STATUS -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 font-semibold shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 font-semibold shadow-sm">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- BUTTONS -->
    @if(in_array(auth()->user()->role, ['admin','finance']))
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <a href="{{ route('customers.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow font-semibold text-sm">
            + Tambah Pelanggan
        </a>
        
        <form action="{{ route('customers.sync-mikrotik') }}" method="POST" class="inline" onsubmit="return confirm('Sinkronisasi MikroTik → Database:\n\nProses ini akan mengambil semua data dari address-list MikroTik dan mendaftarkannya sebagai pelanggan di database.\n\nLanjutkan?')">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded shadow font-semibold text-sm flex items-center gap-1.5 cursor-pointer">
                🔌 Sinkron MikroTik → Database
            </button>
        </form>

        <form action="{{ route('customers.sync-prtg') }}" method="POST" class="inline" onsubmit="return confirm('Sinkronisasi Database → PRTG:\n\nProses ini akan mendaftarkan semua pelanggan aktif dari database ke PRTG monitoring.\n\nLanjutkan?')">
            @csrf
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow font-semibold text-sm flex items-center gap-1.5 cursor-pointer">
                📡 Sinkron Database → PRTG
            </button>
        </form>
    </div>
    @endif

    <!-- TABLE -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">

        <table class="w-full border border-gray-200">

            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-3 border text-center w-12">No</th>
                    <th class="p-3 border text-left">Nama</th>
                    <th class="p-3 border text-left">ID Pelanggan</th>
                    <th class="p-3 border text-left">Phone</th>
                    <th class="p-3 border text-left">IP Address</th>
                    <th class="p-3 border text-left w-64">Alamat</th>
                    <th class="p-3 border text-left">Paket</th>
                    <th class="p-3 border text-center w-28">Internet</th>

                    @if(in_array(auth()->user()->role, ['admin','finance']))
                    <th class="p-3 border text-center w-40">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($customers as $index => $c)
                <tr class="hover:bg-gray-50 transition">

                    <td class="p-3 border text-center">
                        {{ $index + 1 + ($customers->currentPage() - 1) * $customers->perPage() }}
                    </td>

                    <td class="p-3 border">
                        <a href="{{ route('customers.history', $c->id) }}"
                           class="text-blue-600 hover:underline font-medium">
                            {{ $c->name }}
                        </a>
                    </td>

                    <td class="p-3 border font-semibold text-gray-800">
                        {{ $c->customer_code }}
                    </td>

                    <td class="p-3 border">
                        {{ $c->phone }}
                    </td>

                    <td class="p-3 border font-mono text-sm text-gray-600">
                        {{ $c->ip ?? '-' }}
                    </td>

                    <td class="p-3 border max-w-xs">
                        @if($c->address)
                            @php
                                $gmapsUrl = (str_starts_with($c->address, 'http://') || str_starts_with($c->address, 'https://'))
                                    ? $c->address
                                    : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($c->address);
                            @endphp
                            <a href="{{ $gmapsUrl }}" target="_blank"
                               class="text-blue-600 hover:underline block truncate"
                               title="Lihat di Google Maps">
                                📍 {{ \Illuminate\Support\Str::limit($c->address, 35) }}
                            </a>
                        @else
                            <span class="text-gray-400 italic">Tidak ada</span>
                        @endif
                    </td>

                    <td class="p-3 border">
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                            {{ $c->package->name ?? '-' }}
                        </span>
                    </td>

                    <!-- INTERNET NETWORK TOGGLE -->
                    <td class="p-3 border text-center">
                        <div class="switch-container">
                            <label class="switch-label">
                                <input type="checkbox" 
                                       class="switch-input customer-toggle" 
                                       data-id="{{ $c->id }}"
                                       {{ $c->is_active ? 'checked' : '' }}
                                       {{ in_array(auth()->user()->role, ['admin','finance']) ? '' : 'disabled' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                        <div class="mt-1 text-[10px] font-extrabold uppercase tracking-wider text-center" id="status-text-{{ $c->id }}">
                            @if($c->is_active)
                                <span class="text-emerald-500 status-glow-active">● AKTIF</span>
                            @else
                                <span class="text-rose-500 status-glow-inactive">○ OFF</span>
                            @endif
                        </div>
                    </td>

                    @if(in_array(auth()->user()->role, ['admin','finance']))
                    <td class="p-3 border text-center space-x-1">

                        <a href="{{ route('customers.edit', $c->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-sm shadow">
                            Edit
                        </a>

                        <form action="{{ route('customers.destroy', $c->id) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('Yakin hapus?')">
                            @csrf
                            @method('DELETE')

                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm shadow">
                                Hapus
                            </button>
                        </form>

                    </td>
                    @endif

                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center p-6 text-gray-500">
                        Belum ada data pelanggan
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>

<!-- PREMIUM SYNC TOAST NOTIFICATION CONTAINER -->
<div id="sync-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 ease-out bg-slate-900 text-white px-5 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-800 max-w-sm">
    <div id="sync-toast-icon" class="w-8 h-8 rounded-full flex items-center justify-center text-lg shrink-0"></div>
    <div class="flex-1 min-w-0">
        <p id="sync-toast-title" class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400"></p>
        <p id="sync-toast-message" class="text-sm font-semibold text-slate-200 mt-0.5"></p>
        <div id="sync-toast-details" class="flex gap-2 mt-2"></div>
    </div>
</div>

<!-- AJAX INTERNET TOGGLE LOGIC -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.customer-toggle');
    const toast = document.getElementById('sync-toast');
    const toastIcon = document.getElementById('sync-toast-icon');
    const toastTitle = document.getElementById('sync-toast-title');
    const toastMsg = document.getElementById('sync-toast-message');
    const toastDetails = document.getElementById('sync-toast-details');

    let toastTimeout = null;

    const showToast = (title, message, icon, type, mtSynced, prtgSynced) => {
        clearTimeout(toastTimeout);

        // Styling dan Icon berdasarkan tipe
        toastIcon.innerHTML = icon;
        toastIcon.className = `w-8 h-8 rounded-full flex items-center justify-center text-lg shrink-0 ${
            type === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'
        }`;
        
        toastTitle.innerText = title;
        toastMsg.innerText = message;

        // Feedback detail sync
        toastDetails.innerHTML = '';
        if (mtSynced !== undefined) {
            const mtBadge = document.createElement('span');
            mtBadge.className = `text-[9px] px-2 py-0.5 rounded-md font-bold ${
                mtSynced ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
            }`;
            mtBadge.innerText = `MikroTik: ${mtSynced ? 'SYNCED' : 'FAILED/SKIP'}`;
            toastDetails.appendChild(mtBadge);
        }

        if (prtgSynced !== undefined) {
            const prtgBadge = document.createElement('span');
            prtgBadge.className = `text-[9px] px-2 py-0.5 rounded-md font-bold ${
                prtgSynced ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
            }`;
            prtgBadge.innerText = `PRTG: ${prtgSynced ? 'SYNCED' : 'FAILED/SKIP'}`;
            toastDetails.appendChild(prtgBadge);
        }

        // Tampilkan Toast dengan animasi premium slide-up & fade-in
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        toastTimeout = setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 5000);
    };

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const customerId = this.dataset.id;
            const isChecked = this.checked;
            const statusLabel = document.getElementById(`status-text-${customerId}`);

            // Nonaktifkan sakelar sementara untuk mencegah double-click
            this.disabled = true;

            // Optimistic UI update
            if (isChecked) {
                statusLabel.innerHTML = '<span class="text-emerald-500 status-glow-active">● AKTIF</span>';
            } else {
                statusLabel.innerHTML = '<span class="text-rose-500 status-glow-inactive">○ OFF</span>';
            }

            // Tampilkan toast sinkronisasi
            showToast('SINKRONISASI JARINGAN', 'Sedang menghubungi MikroTik & PRTG...', '🔄', 'success');

            // Kirim AJAX Request
            fetch(`/customers/${customerId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal melakukan sinkronisasi dengan server.');
                return res.json();
            })
            .then(data => {
                // Kembalikan tombol ke keadaan aktif
                this.disabled = false;

                // Terapkan hasil sync di database ke checkbox (jika beda karena gagal)
                this.checked = data.is_active;

                if (data.is_active) {
                    statusLabel.innerHTML = '<span class="text-emerald-500 status-glow-active">● AKTIF</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-rose-500 status-glow-inactive">○ OFF</span>';
                }

                showToast(
                    'SINKRONISASI SELESAI', 
                    data.message, 
                    data.is_active ? '✅' : '🚫', 
                    'success',
                    data.mikrotik_synced,
                    data.prtg_synced
                );
            })
            .catch(err => {
                this.disabled = false;
                // Revert checkbox
                this.checked = !isChecked;
                if (!isChecked) {
                    statusLabel.innerHTML = '<span class="text-emerald-500 status-glow-active">● AKTIF</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-rose-500 status-glow-inactive">○ OFF</span>';
                }

                showToast('ERROR SINKRONISASI', err.message || 'Gagal mengubah status jaringan.', '❌', 'error');
            });
        });
    });
});
</script>
</x-app-layout>