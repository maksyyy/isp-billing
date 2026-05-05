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
                    <tr class="border">

                        <td class="p-2">
                            {{ $inv->created_at->format('d-m-Y') }}
                        </td>

                        <td class="p-2">
                            Rp {{ number_format($inv->amount) }}
                        </td>

                        <td class="p-2">
                            <span class="text-green-600 font-bold">
                                Lunas
                            </span>
                        </td>

                        <td class="p-2">
                            <a href="{{ route('invoices.print', $inv->id) }}"
                               class="bg-blue-500 text-white px-3 py-1 rounded text-xs">
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

                        <!-- TANGGAL -->
                        <td class="p-2">
                            {{ $t->tanggal_selesai ? \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') : '-' }}
                        </td>

                        <!-- MASALAH -->
                        <td class="p-2">
                            {{ $t->description }}
                        </td>

                        <!-- TEKNISI -->
                        <td class="p-2">
                            {{ $t->teknisi->name ?? '-' }}
                        </td>

                        <!-- BUKTI -->
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