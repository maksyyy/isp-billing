<x-app-layout>
<div class="p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-normal italic font-serif tracking-tight text-[#FAF9F6]">
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
                    <td class="p-3 font-semibold text-[#FAF9F6]">{{ $t->title }}</td>

                    <!-- CUSTOMER -->
                    <td class="p-3 text-xs">
                        <div class="font-semibold text-[#FAF9F6]">{{ $t->customer->name ?? '-' }}</div>
                    </td>

                    <!-- ALAMAT -->
                    <td class="p-3 text-xs">
                        @if($t->customer && $t->customer->address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($t->customer->address) }}" 
                               target="_blank" 
                               class="text-xs hover:underline text-[#8E8E90]"
                               title="Buka di Google Maps">
                                [MAP] <span class="max-w-[150px] truncate block inline-block align-bottom">{{ $t->customer->address }}</span>
                            </a>
                        @else
                            <span class="text-[#8E8E90]">-</span>
                        @endif
                    </td>

                    <!-- TEKNISI -->
                    <td class="p-3 text-xs text-[#8E8E90]">{{ $t->teknisi->name ?? '-' }}</td>

                    <!-- TANGGAL BUAT -->
                    <td class="p-3 text-xs text-[#8E8E90] font-mono">
                        {{ $t->tanggal ?? $t->created_at->format('d-m-Y') }}
                    </td>

                    <!-- TANGGAL SELESAI -->
                    <td class="p-3 text-xs text-[#8E8E90] font-mono">
                        @if($t->tanggal_selesai)
                            <span class="text-[#10B981] font-semibold font-mono">
                                {{ \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>

                    <!-- STATUS -->
                    <td class="p-3 text-xs">
                        @if($t->status == 'open')
                            <span class="inline-flex px-2 py-0.5 bg-[#2E200C]/50 text-[#F59E0B] border border-[#F59E0B]/20 rounded text-[10px] font-bold uppercase tracking-wider">
                                Open
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 bg-[#0C2D1F]/50 text-[#10B981] border border-[#10B981]/20 rounded text-[10px] font-bold uppercase tracking-wider">
                                Done
                            </span>
                        @endif
                    </td>

                    <!-- FOTO MASALAH -->
                    <td class="p-3 text-center">
                        @if($t->foto_masalah)
                            <a href="{{ asset('storage/'.$t->foto_masalah) }}" target="_blank" class="inline-block">
                                <img src="{{ asset('storage/'.$t->foto_masalah) }}"
                                     class="w-10 h-10 object-cover rounded-md border border-[#222226] hover:scale-105 transition">
                            </a>
                        @else
                            <span class="text-[#8E8E90] text-xs">-</span>
                        @endif
                    </td>
 
                    <!-- BUKTI FOTO -->
                    <td class="p-3 text-center">
                        @if($t->bukti)
                            <a href="{{ asset('storage/'.$t->bukti) }}" target="_blank" class="inline-block">
                                <img src="{{ asset('storage/'.$t->bukti) }}"
                                     class="w-10 h-10 object-cover rounded-md border border-[#222226] hover:scale-105 transition">
                            </a>
                        @else
                            <span class="text-[#8E8E90] text-[10px] font-mono">[KOSONG]</span>
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
                                       class="text-[9px] border border-[#222226] rounded px-1 py-0.5 bg-[#0B0B0D] w-28 text-xs text-[#FAF9F6] focus:border-[#FAF9F6]/40 focus:ring-0" required>

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
                    <td colspan="10" class="p-8 text-center text-[#8E8E90] font-mono text-xs">
                        [Belum ada tiket aduan]
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>

</div>
</x-app-layout>