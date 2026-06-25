<x-app-layout>
    <div class="p-6 max-w-lg mx-auto">
        <h2 class="text-xl font-bold mb-6 text-[#111111]">Edit Paket</h2>

        <div class="app-card p-6">
            <form action="{{ route('packages.update', $package->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Paket -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama Paket</label>
                    <input type="text" name="name" value="{{ $package->name }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- Harga -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Harga</label>
                    <input type="number" name="price" value="{{ $package->price }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- Speed -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Kecepatan (Mbps)</label>
                    <input type="text" name="speed" value="{{ $package->speed }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- Button -->
                <div class="flex gap-2">
                    <button type="submit" class="btn-minimal">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn-minimal-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>