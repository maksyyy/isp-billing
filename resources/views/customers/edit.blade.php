<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Customer
        </h2>
    </x-slot>

    <div class="p-6 flex justify-center">

        <!-- CARD -->
        <div class="w-full max-w-2xl bg-white shadow-lg rounded-lg p-6">

            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- ID Pelanggan -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">ID Pelanggan</label>
                    <input type="text"
                           name="customer_code"
                           value="{{ $customer->customer_code }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400"
                           required>
                </div>

                <!-- Nama -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Nama</label>
                    <input type="text"
                           name="name"
                           value="{{ $customer->name }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400"
                           required>
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">No HP</label>
                    <input type="text"
                           name="phone"
                           value="{{ $customer->phone }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400">
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Alamat</label>
                    <textarea name="address"
                              rows="3"
                              class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400">{{ $customer->address }}</textarea>
                </div>

                <!-- Paket -->
                <div class="mb-6">
                    <label class="block font-semibold mb-1">Paket</label>
                    <select name="package_id"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400">
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}"
                                {{ $customer->package_id == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} - Rp {{ number_format($p->price) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="flex justify-between">

                    <a href="{{ route('customers.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        ← Kembali
                    </a>

                    <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-app-layout>