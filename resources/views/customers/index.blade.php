<x-app-layout>
<div class="p-6">

    <h2 class="text-2xl font-bold mb-4 text-gray-800">Data Pelanggan</h2>

    <!-- 🔍 SEARCH -->
    <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search"
               value="{{ request('search') }}"
               placeholder="Cari nama / ID / phone..."
               class="border px-3 py-2 rounded w-64">

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded">
            Cari
        </button>
    </form>

    <!-- BUTTON -->
    @if(in_array(auth()->user()->role, ['admin','finance']))
    <a href="{{ route('customers.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block shadow">
        + Tambah Pelanggan
    </a>
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
                    <th class="p-3 border text-left w-64">Alamat</th>
                    <th class="p-3 border text-left">Paket</th>

                    @if(in_array(auth()->user()->role, ['admin','finance']))
                    <th class="p-3 border text-center w-40">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($customers as $index => $c)
                <tr class="hover:bg-gray-50 transition">

                    <td class="p-3 border text-center">
                        {{ $index + 1 }}
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

                    <td class="p-3 border max-w-xs">
                        @if($c->address)
                            <a href="{{ $c->address }}" target="_blank"
                               class="text-blue-600 hover:underline block truncate">
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
                    <td colspan="7" class="text-center p-6 text-gray-500">
                        Belum ada data pelanggan
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>
</x-app-layout>