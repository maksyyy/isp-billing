<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#111111] leading-tight">
            Edit Ticket
        </h2>
    </x-slot>

    <div class="p-6 flex justify-center">
        <!-- CARD -->
        <div class="w-full max-w-2xl app-card p-6">
            <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- JUDUL -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Judul</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $ticket->title) }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                    @error('title')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PELANGGAN -->
                <div class="mb-4">
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
                                <option value="{{ $c->id }}"
                                    {{ old('customer_id', $ticket->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}{{ $c->address ? ' (📍 '.$c->address.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('customer_id')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TANGGAL -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date"
                           name="tanggal"
                           value="{{ old('tanggal', $ticket->tanggal) }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                    @error('tanggal')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- MASALAH -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Masalah</label>
                    <textarea name="problem"
                              rows="4"
                              class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                              required>{{ old('problem', $ticket->description) }}</textarea>
                    @error('problem')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- FOTO MASALAH -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Foto Masalah (Opsional)</label>
                    @if($ticket->foto_masalah)
                        <div class="mb-3">
                            <p class="text-xs text-[#71717A] mb-1">Foto Masalah Saat Ini:</p>
                            <img src="{{ asset('storage/' . $ticket->foto_masalah) }}" class="w-48 object-cover rounded border border-[#E4E4E7] shadow-sm">
                        </div>
                    @endif
                    <input type="file" name="foto_masalah" accept="image/*"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm file:bg-[#F4F4F5] file:border-[#E4E4E7] file:text-[#111111] file:rounded-md file:px-3 file:py-1 file:mr-3 file:text-xs">
                    @error('foto_masalah')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TEKNISI -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Teknisi</label>
                    <select name="assigned_to"
                            class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                            required>
                        <option value="">-- Pilih Teknisi --</option>
                        @foreach($teknisi as $t)
                            <option value="{{ $t->id }}"
                                {{ old('assigned_to', $ticket->assigned_to) == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BUTTON -->
                <div class="flex gap-2">
                    <button type="submit" class="btn-minimal">
                        Simpan Perubahan
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
