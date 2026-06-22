<x-app-layout>
<div class="p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">
            🎫 Ticket
        </h2>

        @if(in_array(auth()->user()->role, ['admin','noc']))
            <a href="{{ route('tickets.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
               + Buat Ticket
            </a>
        @endif
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-3 text-left">Judul</th>
                    <th class="p-3 text-left">Customer</th>
                    <th class="p-3 text-left">Alamat</th>
                    <th class="p-3 text-left">Teknisi</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Selesai</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Foto Masalah</th>
                    <th class="p-3 text-left">Bukti</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($tickets as $t)
                <tr class="border-t hover:bg-gray-50 ">

                    <!-- JUDUL -->
                    <td class="p-3 font-semibold">{{ $t->title }}</td>

                    <!-- CUSTOMER -->
                    <td class="p-3">
                        <div class="font-semibold text-gray-800">{{ $t->customer->name ?? '-' }}</div>
                    </td>

                    <!-- ALAMAT -->
                    <td class="p-3">
                        @if($t->customer && $t->customer->address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($t->customer->address) }}" 
                               target="_blank" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline gap-1 font-medium bg-blue-50 px-2 py-1 rounded text-xs transition border border-blue-100"
                               title="Buka di Google Maps">
                                📍 <span class="max-w-[180px] truncate block">{{ $t->customer->address }}</span>
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    <!-- TEKNISI -->
                    <td class="p-3">{{ $t->teknisi->name ?? '-' }}</td>

                    <!-- TANGGAL BUAT -->
                    <td class="p-3">
                        {{ $t->tanggal ?? $t->created_at->format('d-m-Y') }}
                    </td>

                    <!-- TANGGAL SELESAI + JAM -->
                    <td class="p-3">
                        @if($t->tanggal_selesai)
                            <span class="text-green-600 font-semibold">
                                {{ \Carbon\Carbon::parse($t->tanggal_selesai)->format('d-m-Y H:i') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>

                    <!-- STATUS -->
                    <td class="p-3">
                        @if($t->status == 'open')
                            <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs">
                                Open
                            </span>
                        @else
                            <span class="bg-green-200 text-green-800 px-2 py-1 rounded text-xs">
                                Done
                            </span>
                        @endif
                    </td>

                    <!-- FOTO MASALAH -->
                    <td class="p-3">
                        @if($t->foto_masalah)
                            <a href="{{ asset('storage/'.$t->foto_masalah) }}" target="_blank">
                                <img src="{{ asset('storage/'.$t->foto_masalah) }}"
                                     class="w-12 h-12 object-cover rounded border hover:scale-110 transition">
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
 
                    <!-- BUKTI FOTO -->
                    <td class="p-3">
                        @if($t->bukti)
                            <a href="{{ asset('storage/'.$t->bukti) }}" target="_blank">
                                <img src="{{ asset('storage/'.$t->bukti) }}"
                                     class="w-12 h-12 object-cover rounded border hover:scale-110 transition">
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">Belum ada</span>
                        @endif
                    </td>

                    <!-- AKSI -->
                    <td class="p-3 space-y-1">

                        <!-- TEKNISI -->
                        @if(auth()->user()->role == 'teknisi' && $t->status == 'open')
                        <form action="{{ route('tickets.selesai', $t->id) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="flex items-center gap-2">
                            @csrf

                            <input type="file" name="bukti"
                                   class="text-xs border rounded px-2 py-1" required>

                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                Selesai
                            </button>
                        </form>
                        @endif

                        <!-- ADMIN / NOC -->
                        @if(in_array(auth()->user()->role, ['admin','noc']))

                            <a href="{{ route('tickets.edit', $t->id) }}"
                               class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                Edit
                            </a>

                            <form action="{{ route('tickets.destroy', $t->id) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')

                                <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </form>

                        @endif

                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="10" class="p-4 text-center text-gray-500">
                        Belum ada ticket
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