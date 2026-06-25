<x-app-layout>
    <div class="p-6 max-w-lg mx-auto">
        <h2 class="text-xl font-bold mb-6 text-[#111111]">Tambah Paket</h2>

        <div class="app-card p-6">
            <form action="{{ route('packages.store') }}" method="POST">
                @csrf

                <!-- Nama Paket -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama Paket</label>
                    <input type="text" name="name"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           placeholder="Contoh: Internet 20 Mbps"
                           required>
                </div>

                <!-- Harga -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Harga</label>
                    <input type="number" name="price"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           placeholder="Contoh: 150000"
                           required>
                </div>

                <!-- Speed -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Kecepatan (Mbps)</label>
                    <input type="text" name="speed"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           placeholder="Contoh: 20 Mbps"
                           required>
                </div>

                <!-- Button -->
                <div class="flex gap-2">
                    <button type="submit" class="btn-minimal">
                        Simpan
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn-minimal-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>