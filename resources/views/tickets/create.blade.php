<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-[#111111]">
            Buat Ticket
        </h2>

        <div class="app-card p-6">
            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- CUSTOMER -->
                <div>
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Pelanggan</label>
                    <div class="relative space-y-2">
                        <input type="text"
                               id="customer_search"
                               placeholder="🔍 Cari nama atau alamat pelanggan..."
                               class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                               autocomplete="off">
                        <select name="customer_id"
                                id="customer_select"
                                class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
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
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date" name="tanggal"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- JUDUL -->
                <div>
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Judul</label>
                    <input type="text" name="title"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- MASALAH -->
                <div>
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Masalah</label>
                    <textarea name="problem"
                              class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                              rows="3" required></textarea>
                </div>

                <!-- FOTO MASALAH -->
                <div>
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Foto Masalah (Opsional)</label>
                    <input type="file" name="foto_masalah" accept="image/*"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm file:bg-[#F4F4F5] file:border-[#E4E4E7] file:text-[#111111] file:rounded-md file:px-3 file:py-1 file:mr-3 file:text-xs">
                    @error('foto_masalah')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TEKNISI -->
                <div>
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Teknisi</label>
                    <select name="assigned_to"
                            class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                            required>
                        <option value="">-- Pilih Teknisi --</option>
                        @foreach($teknisi as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="flex gap-2 pt-4">
                    <button type="submit" class="btn-minimal">
                        Simpan Tiket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn-minimal-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
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