<x-app-layout>
    <div class="p-6">

        <h2 class="text-3xl font-bold tracking-tight text-[#111111] mb-6">
            Riwayat - {{ $customer->name }}
        </h2>

        <div class="mb-6">
            <a href="{{ route('customers.index') }}" class="btn-minimal-secondary">
                ← Kembali
            </a>
        </div>

        <!-- ========================= -->
        <!-- 💰 RIWAYAT PEMBAYARAN -->
        <!-- ========================= -->
        <div class="app-card overflow-hidden mb-6">

            <div class="p-4 bg-[#F4F4F5] font-bold text-xs uppercase tracking-wider text-[#6366F1] border-b border-[#E4E4E7]">
                Riwayat Pembayaran
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="p-3 text-left">Tanggal</th>
                            <th class="p-3 text-left">Jumlah</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $inv)
                        @php
                            $isLunas = $inv->paid_amount >= $inv->amount;
                            $sisa = $inv->amount - $inv->paid_amount;
                        @endphp

                        <tr>
                            <!-- TANGGAL -->
                            <td class="p-3 text-xs font-mono text-[#111111]">
                                {{ $inv->created_at->format('d-m-Y') }}
                            </td>

                            <!-- JUMLAH -->
                            <td class="p-3">
                                <div>
                                    <strong class="text-[#6366F1] font-mono text-sm">
                                        Rp {{ number_format($inv->paid_amount) }}
                                    </strong>
                                    <br>
                                    <small class="text-[#71717A] text-[10px]">
                                        dari Rp {{ number_format($inv->amount) }}
                                    </small>
                                </div>
                            </td>

                            <!-- STATUS DINAMIS -->
                            <td class="p-3 text-xs">
                                @if($isLunas)
                                    <span class="inline-flex px-2 py-0.5 bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0] rounded text-[10px] font-bold uppercase tracking-wider">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A] rounded text-[10px] font-bold uppercase tracking-wider">
                                        Belum Lunas
                                    </span>
                                    <br>
                                    <small class="text-[#71717A] text-[10px]">
                                        Sisa: Rp {{ number_format($sisa) }}
                                    </small>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td class="p-3 text-center">
                                <a href="{{ route('invoices.print', $inv->id) }}" class="btn-minimal px-3 py-1.5 text-[10px]">
                                    Cetak
                                </a>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center p-8 text-[#71717A] font-mono text-xs">
                                [Belum ada riwayat pembayaran]
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ========================= -->
        <!-- 🛠️ RIWAYAT PERBAIKAN -->
        <!-- ========================= -->
        <div class="app-card overflow-hidden">

            <div class="p-4 bg-[#F4F4F5] font-bold text-xs uppercase tracking-wider text-[#6366F1] border-b border-[#E4E4E7]">
                Riwayat Perbaikan
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="p-3 text-left">Tanggal Selesai</th>
                            <th class="p-3 text-left">Masalah</th>
                            <th class="p-3 text-left">Teknisi</th>
                            <th class="p-3 text-center w-28">Bukti</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tickets as $t)
                        <tr>

                            <td class="p-3 text-xs font-mono text-[#111111]">
                                {{ $t->tanggal_selesai ? \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') : '-' }}
                            </td>

                            <td class="p-3 text-xs text-[#111111]">
                                {{ $t->description }}
                            </td>

                            <td class="p-3 text-xs text-[#71717A]">
                                {{ $t->teknisi->name ?? '-' }}
                            </td>

                            <td class="p-3 text-center">
                                @if($t->bukti)
                                    <a href="{{ asset('storage/'.$t->bukti) }}" target="_blank" class="inline-block">
                                        <img src="{{ asset('storage/'.$t->bukti) }}"
                                             class="w-10 h-10 object-cover rounded-md border border-[#E4E4E7] hover:scale-105 transition">
                                    </a>
                                @else
                                    <span class="text-[#71717A] text-xs">
                                        -
                                    </span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center p-8 text-[#71717A] font-mono text-xs">
                                [Belum ada riwayat perbaikan]
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-app-layout>