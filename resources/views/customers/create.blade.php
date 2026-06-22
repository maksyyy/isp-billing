<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Pelanggan
        </h2>
    </x-slot>

    <div class="p-6 max-w-2xl mx-auto">

        <div class="bg-white shadow-md rounded-lg p-6">

            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <!-- ID Pelanggan -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">ID Pelanggan</label>
                    <input type="text"
                           name="customer_code"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                           placeholder="Contoh: CUST-001 / bebas"
                           required>
                </div>

                <!-- Nama -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Nama</label>
                    <input type="text"
                           name="name"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                           placeholder="Nama pelanggan"
                           required>
                </div>

                <!-- No HP -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">No HP</label>
                    <input type="text"
                           name="phone"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                           placeholder="08xxxxxxxxxx">
                </div>

                <!-- IP Address -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">IP Address</label>
                    <input type="text"
                           name="ip"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400 @error('ip') border-red-500 @enderror"
                           placeholder="Contoh: 192.168.1.100">
                    @error('ip')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Alamat</label>
                    <textarea name="address"
                              class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                              rows="3"
                              placeholder="Alamat lengkap"></textarea>
                </div>

                <!-- Paket -->
                <div class="mb-6">
                    <label class="block font-semibold mb-1">Paket</label>
                    <select name="package_id"
                            class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} - Rp {{ number_format($p->price) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Simpan
                    </button>

                    <a href="{{ route('customers.index') }}"
                       class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                        Batal
                    </a>
                </div>

            </form>

        </div>

    </div>
</x-app-layout>