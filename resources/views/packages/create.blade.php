<x-app-layout>
    <div class="p-6 max-w-lg">

        <h2 class="text-xl font-bold mb-4">Tambah Paket</h2>

        <form action="{{ route('packages.store') }}" method="POST">
            @csrf

            <!-- Nama Paket -->
            <div class="mb-3">
                <label class="block mb-1">Nama Paket</label>
                <input type="text" name="name"
                       class="w-full border p-2 rounded"
                       placeholder="Contoh: Internet 20 Mbps"
                       required>
            </div>

            <!-- Harga -->
            <div class="mb-3">
                <label class="block mb-1">Harga</label>
                <input type="number" name="price"
                       class="w-full border p-2 rounded"
                       placeholder="Contoh: 150000"
                       required>
            </div>

            <!-- Speed -->
            <div class="mb-3">
                <label class="block mb-1">Kecepatan (Mbps)</label>
                <input type="text" name="speed"
                       class="w-full border p-2 rounded"
                       placeholder="Contoh: 20 Mbps"
                       required>
            </div>

            <!-- Button -->
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan
            </button>

        </form>
    </div>
</x-app-layout>