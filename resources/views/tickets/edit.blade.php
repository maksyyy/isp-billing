<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Ticket
        </h2>
    </x-slot>

    <div class="p-6 flex justify-center">

        <!-- CARD -->
        <div class="w-full max-w-2xl bg-white shadow-lg rounded-lg p-6">

            <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- JUDUL -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-gray-700">Judul</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $ticket->title) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PELANGGAN -->
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
                                class="w-full border border-slate-200 px-3 py-2 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all @error('customer_id') border-red-500 @enderror"
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
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TANGGAL -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-gray-700">Tanggal</label>
                    <input type="date"
                           name="tanggal"
                           value="{{ old('tanggal', $ticket->tanggal) }}"
                           class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 @error('tanggal') border-red-500 @enderror"
                           required>
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- MASALAH -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-gray-700">Masalah</label>
                    <textarea name="problem"
                              rows="4"
                              class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 @error('problem') border-red-500 @enderror"
                              required>{{ old('problem', $ticket->description) }}</textarea>
                    @error('problem')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- FOTO MASALAH -->
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-gray-700">Foto Masalah (Opsional)</label>
                    @if($ticket->foto_masalah)
                        <div class="mb-2">
                            <p class="text-xs text-gray-500 mb-1">Foto Masalah Saat Ini:</p>
                            <img src="{{ asset('storage/' . $ticket->foto_masalah) }}" class="w-48 object-cover rounded border shadow-sm">
                        </div>
                    @endif
                    <input type="file" name="foto_masalah" accept="image/*" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 @error('foto_masalah') border-red-500 @enderror">
                    @error('foto_masalah')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TEKNISI -->
                <div class="mb-6">
                    <label class="block font-semibold mb-1 text-gray-700">Teknisi</label>
                    <select name="assigned_to"
                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 @error('assigned_to') border-red-500 @enderror"
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
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BUTTON -->
                <div class="flex justify-between items-center">

                    <a href="{{ route('tickets.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">
                        ← Kembali
                    </a>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition font-semibold shadow">
                        Simpan Perubahan
                    </button>

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
