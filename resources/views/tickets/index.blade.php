<x-app-layout>
<div class="p-6">

    <!-- ALERTS -->
    @if(session('success'))
        <div class="bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-md mb-6 flex items-start gap-2 shadow-xs">
            <span class="text-xs font-bold text-[#15803D] shrink-0 font-mono">[OK]</span>
            <div>
                <p class="font-bold text-xs text-[#15803D] uppercase tracking-wider">Berhasil</p>
                <p class="text-xs text-[#15803D]/90 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] px-4 py-3 rounded-md mb-6 flex items-start gap-2 shadow-xs">
            <span class="text-xs font-bold text-[#991B1B] shrink-0 font-mono">[ERROR]</span>
            <div>
                <p class="font-bold text-xs text-[#991B1B] uppercase tracking-wider">Gagal Validasi</p>
                <ul class="list-disc pl-4 mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li class="text-xs text-[#991B1B]/90">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold tracking-tight text-[#111111]">
            Tiket Aduan
        </h2>

        @if(in_array(auth()->user()->role, ['admin','noc']))
            <a href="{{ route('tickets.create') }}" class="btn-minimal">
               + Buat Tiket
            </a>
        @endif
    </div>

    <!-- TABLE -->
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Alamat</th>
                        <th class="p-3">Teknisi</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Selesai</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Foto Masalah</th>
                        <th class="p-3">Bukti</th>
                        <th class="p-3 text-center w-48">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <!-- JUDUL -->
                        <td class="p-3 font-semibold text-[#111111]">{{ $t->title }}</td>

                        <!-- CUSTOMER -->
                        <td class="p-3 text-xs">
                            <div class="font-semibold text-[#111111]">{{ $t->customer->name ?? '-' }}</div>
                        </td>

                        <!-- ALAMAT -->
                        <td class="p-3 text-xs">
                            @if($t->customer && $t->customer->address)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($t->customer->address) }}" 
                                   target="_blank" 
                                   class="text-xs hover:underline text-[#71717A]"
                                   title="Buka di Google Maps">
                                    [MAP] <span class="max-w-[150px] truncate block inline-block align-bottom">{{ $t->customer->address }}</span>
                                </a>
                            @else
                                <span class="text-[#71717A]">-</span>
                            @endif
                        </td>

                        <!-- TEKNISI -->
                        <td class="p-3 text-xs text-[#71717A]">{{ $t->teknisi->name ?? '-' }}</td>

                        <!-- TANGGAL BUAT -->
                        <td class="p-3 text-xs text-[#71717A] font-mono">
                            {{ $t->tanggal ?? $t->created_at->format('d-m-Y') }}
                        </td>

                        <!-- TANGGAL SELESAI -->
                        <td class="p-3 text-xs text-[#71717A] font-mono">
                            @if($t->tanggal_selesai)
                                <span class="text-[#15803D] font-semibold font-mono">
                                    {{ \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <!-- STATUS -->
                        <td class="p-3 text-xs">
                            @if($t->status == 'open')
                                <span class="inline-flex px-2 py-0.5 bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A] rounded text-[10px] font-bold uppercase tracking-wider">
                                    Open
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0] rounded text-[10px] font-bold uppercase tracking-wider">
                                    Done
                                </span>
                            @endif
                        </td>

                        <!-- FOTO MASALAH -->
                        <td class="p-3 text-center">
                            @if($t->foto_masalah)
                                <a href="{{ asset('storage/'.$t->foto_masalah) }}" target="_blank" class="inline-block">
                                    <img src="{{ asset('storage/'.$t->foto_masalah) }}"
                                         class="w-10 h-10 object-cover rounded-md border border-[#E4E4E7] hover:scale-105 transition">
                                </a>
                            @else
                                <span class="text-[#71717A] text-xs">-</span>
                            @endif
                        </td>
 
                        <!-- BUKTI FOTO -->
                        <td class="p-3 text-center">
                            @if($t->bukti)
                                <a href="{{ asset('storage/'.$t->bukti) }}" target="_blank" class="inline-block">
                                    <img src="{{ asset('storage/'.$t->bukti) }}"
                                         class="w-10 h-10 object-cover rounded-md border border-[#E4E4E7] hover:scale-105 transition">
                                </a>
                            @else
                                <span class="text-[#71717A] text-[10px] font-mono">[KOSONG]</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td class="p-3 text-center">
                            <div class="inline-flex flex-col gap-1 items-center justify-center w-full">
                                <!-- TEKNISI -->
                                @if(auth()->user()->role == 'teknisi' && $t->status == 'open')
                                <form action="{{ route('tickets.selesai', $t->id) }}"
                                      method="POST"
                                      enctype="multipart/form-data"
                                      class="flex items-center gap-1.5 w-full">
                                    @csrf

                                    <input type="file" name="bukti"
                                           class="text-[9px] border border-[#E4E4E7] rounded px-1 py-0.5 bg-[#FFFFFF] w-28 text-xs text-[#111111] focus:border-[#6366F1]/40 focus:ring-0 shadow-sm" title="Foto bukti perbaikan (opsional)">

                                    <button class="btn-minimal px-2 py-1 text-[10px]">
                                        Selesai
                                    </button>
                                </form>
                                @endif

                                <!-- ADMIN / NOC -->
                                @if(in_array(auth()->user()->role, ['admin','noc']))
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <a href="{{ route('tickets.edit', $t->id) }}" class="btn-minimal-secondary px-2.5 py-1 text-[10px] font-bold">
                                        Edit
                                    </a>

                                    <form action="{{ route('tickets.destroy', $t->id) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Yakin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center justify-center px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-[10px] font-bold transition-all cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-[#71717A] font-mono text-xs">
                            [Belum ada tiket aduan]
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>

</div>
</x-app-layout>