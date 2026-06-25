<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#111111] leading-tight">
            Edit Customer
        </h2>
    </x-slot>

    <div class="p-6 flex justify-center">
        <!-- CARD -->
        <div class="w-full max-w-2xl app-card p-6">
            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- ID Pelanggan -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">ID Pelanggan</label>
                    <input type="text"
                           name="customer_code"
                           value="{{ $customer->customer_code }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- Nama -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama</label>
                    <input type="text"
                           name="name"
                           value="{{ $customer->name }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                           required>
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">No HP</label>
                    <input type="text"
                           name="phone"
                           value="{{ $customer->phone }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                </div>

                <!-- IP Address -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">IP Address</label>
                    <input type="text"
                           name="ip"
                           value="{{ old('ip', $customer->ip) }}"
                           class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm @error('ip') border-red-500/50 @enderror"
                           placeholder="Contoh: 192.168.1.100">
                    @error('ip')
                        <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Alamat</label>
                    <textarea name="address"
                              rows="3"
                              class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">{{ $customer->address }}</textarea>
                </div>

                <!-- Paket -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Paket</label>
                    <select name="package_id"
                            class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}"
                                {{ $customer->package_id == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} - Rp {{ number_format($p->price) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="flex gap-2">
                    <button type="submit" class="btn-minimal">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn-minimal-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>