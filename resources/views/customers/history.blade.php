<x-app-layout>
    <div class="p-6">

        <h2 class="text-xl font-bold mb-4">
            Riwayat - {{ $customer->name }}
        </h2>

        <a href="{{ route('customers.index') }}"
           class="bg-gray-500 text-white px-3 py-2 rounded mb-4 inline-block">
            ← Kembali
        </a>

        <!-- ========================= -->
        <!-- 💰 RIWAYAT PEMBAYARAN -->
        <!-- ========================= -->
        <div class="bg-white shadow rounded overflow-hidden mb-6">

            <div class="p-3 bg-gray-100 font-semibold">
                Riwayat Pembayaran
            </div>

            <table class="w-full border text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2">Tanggal</th>
                        <th class="p-2">Jumlah</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $inv)
                    @php
                        $isLunas = $inv->paid_amount >= $inv->amount;
                        $sisa = $inv->amount - $inv->paid_amount;
                    @endphp

                    <tr class="border">

                        <!-- TANGGAL -->
                        <td class="p-2">
                            {{ $inv->created_at->format('d-m-Y') }}
                        </td>

                        <!-- JUMLAH (FIX UTAMA 🔥) -->
                        <td class="p-2">
                            <div>
                                <strong class="text-blue-600">
                                    Rp {{ number_format($inv->paid_amount) }}
                                </strong>
                                <br>
                                <small class="text-gray-400">
                                    dari Rp {{ number_format($inv->amount) }}
                                </small>
                            </div>
                        </td>

                        <!-- STATUS DINAMIS -->
                        <td class="p-2">
                            @if($isLunas)
                                <span class="text-green-600 font-bold">
                                    Lunas
                                </span>
                            @else
                                <span class="text-orange-500 font-bold">
                                    Belum Lunas
                                </span>
                                <br>
                                <small class="text-gray-500">
                                    Sisa: Rp {{ number_format($sisa) }}
                                </small>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td class="p-2">
                            <a href="{{ route('invoices.print', $inv->id) }}"
                               class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                Cetak
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center p-4 text-gray-400">
                            Belum ada riwayat pembayaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

        <!-- ========================= -->
        <!-- 🛠️ RIWAYAT PERBAIKAN -->
        <!-- ========================= -->
        <div class="bg-white shadow rounded overflow-hidden">

            <div class="p-3 bg-gray-100 font-semibold">
                Riwayat Perbaikan
            </div>

            <table class="w-full border text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2">Tanggal Selesai</th>
                        <th class="p-2">Masalah</th>
                        <th class="p-2">Teknisi</th>
                        <th class="p-2">Bukti</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tickets as $t)
                    <tr class="border">

                        <td class="p-2">
                            {{ $t->tanggal_selesai ? \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') : '-' }}
                        </td>

                        <td class="p-2">
                            {{ $t->description }}
                        </td>

                        <td class="p-2">
                            {{ $t->teknisi->name ?? '-' }}
                        </td>

                        <td class="p-2">
                            @if($t->bukti)
                                <a href="{{ asset('storage/'.$t->bukti) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$t->bukti) }}"
                                         class="w-12 h-12 object-cover rounded border hover:scale-110 transition">
                                </a>
                            @else
                                <span class="text-gray-400 text-xs">
                                    Tidak ada
                                </span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center p-4 text-gray-400">
                            Belum ada riwayat perbaikan
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

    </div>
</x-app-layout>