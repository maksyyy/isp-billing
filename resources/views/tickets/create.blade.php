<x-app-layout>
    <div class="p-6">

        <h2 class="text-2xl font-bold mb-4 text-gray-800">
            Buat Ticket
        </h2>

        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- CUSTOMER -->
            <div class="mb-4">
                <label class="block font-semibold mb-1 text-gray-700">Pelanggan</label>
                <div class="relative">
                    <input type="text"
                           id="customer_search"
                           placeholder="🔍 Cari nama atau alamat pelanggan..."
                           class="w-full border border-slate-200 px-3 py-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all mb-2"
                           autocomplete="off">
                    <select name="customer_id"
                            id="customer_select"
                            class="w-full border border-slate-200 px-3 py-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            required>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}{{ $c->address ? ' (📍 '.$c->address.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
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
                <label class="block font-semibold mb-1 text-gray-700">Masalah</label>
                <textarea name="problem" class="w-full border p-2" required></textarea>
            </div>

            <!-- FOTO MASALAH -->
            <div>
                <label class="block font-semibold mb-1 text-gray-700">Foto Masalah (Opsional)</label>
                <input type="file" name="foto_masalah" accept="image/*" class="w-full border px-3 py-2 rounded">
                @error('foto_masalah')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
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

            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                Simpan Tiket
            </button>

        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('customer_search');
            const selectEl = document.getElementById('customer_select');
            if (searchInput && selectEl) {
                const originalOptions = Array.from(selectEl.options);
                
                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.toLowerCase().trim();
                    
                    originalOptions.forEach((opt, index) => {
                        if (index === 0) {
                            opt.hidden = false;
                            opt.style.display = '';
                            return;
                        }
                        
                        const text = opt.text.toLowerCase();
                        if (text.includes(query)) {
                            opt.hidden = false;
                            opt.style.display = '';
                        } else {
                            opt.hidden = true;
                            opt.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>