<x-app-layout>
    <div class="p-6">

        <h2 class="text-xl font-bold mb-4">Data Paket</h2>

        <!-- Tombol Tambah -->
        <a href="{{ route('packages.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 mb-4 inline-block">
           + Tambah Paket
        </a>

        <!-- TABLE -->
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full border-collapse">

                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left">No</th>
                        <th class="p-2 text-left">Nama</th>
                        <th class="p-2 text-left">Harga</th>
                        <th class="p-2 text-left">Speed</th>
                        <th class="p-2 text-left">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($packages as $index => $p)
                    <tr class="border-t">
                        <td class="p-2">{{ $index + 1 }}</td>
                        <td class="p-2">{{ $p->name }}</td>
                        <td class="p-2">
                            Rp {{ number_format($p->price, 0, ',', '.') }}
                        </td>
                        <td class="p-2">{{ $p->speed }}</td>

                        <td class="p-2">

                            <!-- EDIT -->
                            <a href="{{ route('packages.edit', $p->id) }}"
                               class="bg-yellow-400 px-2 py-1 rounded text-white">
                                Edit
                            </a>

                            <!-- DELETE -->
                            <form action="{{ route('packages.destroy', $p->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Yakin mau hapus paket ini?')">

                                @csrf
                                @method('DELETE')

                                <button class="bg-red-500 px-2 py-1 rounded text-white">
                                    Hapus
                                </button>
                            </form>

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
</x-app-layout>