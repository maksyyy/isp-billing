<x-app-layout>
<div class="p-6">

    <h2 class="text-3xl font-bold tracking-tight text-[#111111] mb-6">Data Pelanggan</h2>

    <!-- NOTIFIKASI STATUS -->
    @if(session('success'))
        <div class="bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-md mb-4 text-xs font-semibold">
            [OK] {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#FEE2E2] border border-[#FECACA] text-[#B91C1C] px-4 py-3 rounded-md mb-4 text-xs font-semibold">
            [ERROR] {{ session('error') }}
        </div>
    @endif

    <!-- BUTTONS -->
    @if(in_array(auth()->user()->role, ['admin','finance']))
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('customers.create') }}" class="btn-minimal">
            + Tambah Pelanggan
        </a>
        
        <form action="{{ route('customers.sync-mikrotik') }}" method="POST" class="inline" onsubmit="return confirm('Sinkronisasi MikroTik -> Database:\n\nProses ini akan mendaftarkan/memperbarui pelanggan dari address-list MikroTik.\n\nPERINGATAN: Pelanggan di database yang tidak ada di MikroTik akan DIHAPUS dari sistem beserta seluruh tagihan, pembayaran, dan tiket mereka.\n\nLanjutkan?')">
            @csrf
            <button type="submit" class="btn-minimal-secondary">
                Sinkron MikroTik -> Database
            </button>
        </form>

        <form action="{{ route('customers.sync-prtg') }}" method="POST" class="inline" onsubmit="return confirm('Sinkronisasi Database -> PRTG:\n\nProses ini akan mendaftarkan semua pelanggan aktif dari database ke PRTG monitoring.\n\nLanjutkan?')">
            @csrf
            <button type="submit" class="btn-minimal-secondary">
                Sinkron Database -> PRTG
            </button>
        </form>
    </div>
    @endif

    <!-- TABLE -->
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3 text-left">Nama</th>
                        <th class="p-3 text-left">ID Pelanggan</th>
                        <th class="p-3 text-left">Phone</th>
                        <th class="p-3 text-left">IP Address</th>
                        <th class="p-3 text-left w-64">Alamat</th>
                        <th class="p-3 text-left">Paket</th>
                        <th class="p-3 text-center w-28">Internet</th>
                        @if(in_array(auth()->user()->role, ['admin','finance']))
                        <th class="p-3 text-center w-40">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $index => $c)
                    <tr>
                        <td class="p-3 text-center font-mono text-xs">
                             {{ $index + 1 + ($customers->currentPage() - 1) * $customers->perPage() }}
                        </td>

                        <td class="p-3 font-semibold text-[#111111]">
                            <a href="{{ route('customers.history', $c->id) }}" class="text-[#6366F1] hover:text-[#8B5CF6] hover:underline">
                                {{ $c->name }}
                            </a>
                        </td>

                        <td class="p-3 font-mono text-xs text-[#111111]">
                            {{ $c->customer_code }}
                        </td>

                        <td class="p-3 text-xs text-[#71717A]">
                            {{ $c->phone }}
                        </td>

                        <td class="p-3 font-mono text-xs text-[#71717A]">
                            {{ $c->ip ?? '-' }}
                        </td>

                        <td class="p-3 max-w-xs text-xs">
                            @if($c->address)
                                @php
                                    $gmapsUrl = (str_starts_with($c->address, 'http://') || str_starts_with($c->address, 'https://'))
                                        ? $c->address
                                        : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($c->address);
                                @endphp
                                <a href="{{ $gmapsUrl }}" target="_blank" class="text-xs hover:underline block truncate text-[#71717A]" title="Lihat di Google Maps">
                                    [MAP] {{ \Illuminate\Support\Str::limit($c->address, 35) }}
                                </a>
                            @else
                                <span class="text-[#71717A] italic">Tidak ada</span>
                            @endif
                        </td>

                        <td class="p-3 text-xs">
                            <span class="inline-flex px-2 py-0.5 bg-[#8B5CF6]/10 text-[#8B5CF6] border border-[#8B5CF6]/20 rounded text-[10px] font-bold">
                                {{ $c->package->name ?? '-' }}
                            </span>
                        </td>

                        <!-- INTERNET NETWORK TOGGLE (Two-tone toggle) -->
                        <td class="p-3 text-center">
                            <div class="inline-flex flex-col items-center justify-center">
                                <label class="two-tone-switch">
                                    <input type="checkbox" 
                                           class="customer-toggle" 
                                           data-id="{{ $c->id }}"
                                           {{ $c->is_active ? 'checked' : '' }}
                                           {{ in_array(auth()->user()->role, ['admin','finance']) ? '' : 'disabled' }}>
                                    <span class="two-tone-slider"></span>
                                </label>
                                <div class="mt-1 text-[8px] font-bold uppercase tracking-wider text-center" id="status-text-{{ $c->id }}">
                                    @if($c->is_active)
                                        <span class="text-[#15803D]">● AKTIF</span>
                                    @else
                                        <span class="text-[#B91C1C]">○ OFF</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        @if(in_array(auth()->user()->role, ['admin','finance']))
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1.5 justify-center">
                                <a href="{{ route('customers.edit', $c->id) }}" class="btn-minimal-secondary px-2.5 py-1 text-[10px] font-bold">
                                    Edit
                                </a>

                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center justify-center px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-[10px] font-bold transition-all cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center p-8 text-[#71717A] font-mono text-xs">
                            [Belum ada data pelanggan]
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>

<!-- PREMIUM SYNC TOAST NOTIFICATION CONTAINER -->
<div id="sync-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 ease-out bg-[#FFFFFF] text-[#111111] px-5 py-4 rounded-md shadow-2xl border border-[#E4E4E7] max-w-sm backdrop-blur-md">
    <div class="flex items-start gap-3">
        <div id="sync-toast-icon" class="w-8 h-8 rounded-md flex items-center justify-center text-xs font-bold font-mono bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] shrink-0"></div>
        <div class="flex-1 min-w-0">
            <p id="sync-toast-title" class="text-[9px] font-bold uppercase tracking-wider text-[#71717A]"></p>
            <p id="sync-toast-message" class="text-xs font-medium text-[#111111] mt-0.5"></p>
            <div id="sync-toast-details" class="flex gap-2 mt-2"></div>
        </div>
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

        toastIcon.innerHTML = icon;
        toastTitle.innerText = title;
        toastMsg.innerText = message;

        // Feedback detail sync
        toastDetails.innerHTML = '';
        if (mtSynced !== undefined) {
            const mtBadge = document.createElement('span');
            mtBadge.className = `text-[8px] px-1.5 py-0.5 rounded font-mono font-bold border ${
                mtSynced ? 'bg-[#DCFCE7] border-[#BBF7D0] text-[#15803D]' : 'bg-[#FEF3C7] border-[#FDE68A] text-[#D97706]'
            }`;
            mtBadge.innerText = `MIKROTIK: ${mtSynced ? 'SYNCED' : 'FAILED/SKIP'}`;
            toastDetails.appendChild(mtBadge);
        }

        if (prtgSynced !== undefined) {
            const prtgBadge = document.createElement('span');
            prtgBadge.className = `text-[8px] px-1.5 py-0.5 rounded font-mono font-bold border ${
                prtgSynced ? 'bg-[#DCFCE7] border-[#BBF7D0] text-[#15803D]' : 'bg-[#FEF3C7] border-[#FDE68A] text-[#D97706]'
            }`;
            prtgBadge.innerText = `PRTG: ${prtgSynced ? 'SYNCED' : 'FAILED/SKIP'}`;
            toastDetails.appendChild(prtgBadge);
        }

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

            this.disabled = true;

            if (isChecked) {
                statusLabel.innerHTML = '<span class="text-[#15803D]">● AKTIF</span>';
            } else {
                statusLabel.innerHTML = '<span class="text-[#B91C1C]">○ OFF</span>';
            }

            showToast('SINKRONISASI JARINGAN', 'Sedang menghubungi MikroTik & PRTG...', '[SYNC]', 'success');

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
                this.disabled = false;
                this.checked = data.is_active;

                if (data.is_active) {
                    statusLabel.innerHTML = '<span class="text-[#15803D]">● AKTIF</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-[#B91C1C]">○ OFF</span>';
                }

                showToast(
                    'SINKRONISASI SELESAI', 
                    data.message, 
                    data.is_active ? '[OK]' : '[OFF]', 
                    'success',
                    data.mikrotik_synced,
                    data.prtg_synced
                );
            })
            .catch(err => {
                this.disabled = false;
                this.checked = !isChecked;
                if (!isChecked) {
                    statusLabel.innerHTML = '<span class="text-[#15803D]">● AKTIF</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-[#B91C1C]">○ OFF</span>';
                }

                showToast('ERROR SINKRONISASI', err.message || 'Gagal mengubah status jaringan.', '[ERR]', 'error');
            });
        });
    });
});
</script>
</x-app-layout>