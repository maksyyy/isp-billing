<x-app-layout>
    <div class="p-6">

        <h2 class="text-2xl font-bold mb-4 text-gray-800">
            Buat Ticket
        </h2>

        <form action="{{ route('tickets.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- CUSTOMER -->
            <div>
                <label>Pelanggan</label>
                <select name="customer_id" class="w-full border p-2" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- TANGGAL -->
            <div>
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="w-full border px-3 py-2 rounded" required>
            </div>

            <!-- JUDUL -->
            <div>
                <label>Judul</label>
                <input type="text" name="title" class="w-full border px-3 py-2 rounded" required>
            </div>

            <!-- MASALAH -->
            <div>
                <label>Masalah</label>
                <textarea name="problem" class="w-full border p-2" required></textarea>
            </div>

            <!-- TEKNISI -->
            <div>
                <label>Teknisi</label>
                <select name="assigned_to" class="w-full border p-2" required>
                    <option value="">-- Pilih Teknisi --</option>
                    @foreach($teknisi as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Simpan
            </button>

        </form>

    </div>
</x-app-layout>