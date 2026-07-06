<x-app-layout>
<div class="p-6">
    <div class="max-w-2xl app-card p-6 mx-auto bg-white border border-[#E4E4E7] shadow-xs rounded-xl">
        <p class="text-xs font-extrabold text-[#6366F1] uppercase tracking-wider">
            {{ auth()->user()->role == 'master' ? 'Admin Penyewa' : 'Sub User' }}
        </p>
        <h2 class="text-2xl font-bold mt-1 text-[#111111]">
            Tambah {{ auth()->user()->role == 'master' ? 'Admin Baru' : 'Sub User Baru' }}
        </h2>
        <p class="text-[#71717A] text-xs mt-1 mb-6 font-light">
            {{ auth()->user()->role == 'master'
                ? 'Admin baru dapat mengelola tim finance, NOC, dan teknisi masing-masing.'
                : 'Sub-user akan otomatis berada di bawah akun admin Anda.' }}
        </p>

        <form id="create-user-form" action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('name')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('email')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('phone')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Telegram Chat ID (Opsional)</label>
                <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id') }}"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                @error('telegram_chat_id')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm"
                       required>
                @error('password')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Role</label>
                <select name="role" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- CUSTOMER LIMIT -->
            <div id="customer-limit-container" class="mb-4 hidden transition-all duration-300">
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Limit Jumlah Pelanggan</label>
                <select name="customer_limit" class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
                    <option value="200" @selected(old('customer_limit') == 200)>200</option>
                    <option value="500" @selected(old('customer_limit') == 500)>500</option>
                    <option value="1000" @selected(old('customer_limit') == 1000)>1000</option>
                    <option value="2000" @selected(old('customer_limit') == 2000)>2000</option>
                    <option value="3000" @selected(old('customer_limit') == 3000)>3000</option>
                    <option value="4000" @selected(old('customer_limit') == 4000)>4000</option>
                    <option value="5000" @selected(old('customer_limit') == 5000)>5000</option>
                </select>
                @error('customer_limit')
                    <p class="text-[#B91C1C] text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 pt-4">
                <button class="btn-minimal">
                    Simpan User
                </button>
                <a href="{{ route('settings.index', ['tab' => 'staff']) }}" class="btn-minimal-secondary flex items-center justify-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle fields based on role
    const roleSelect = document.querySelector('select[name="role"]');
    const customerLimitContainer = document.getElementById('customer-limit-container');

    function toggleRoleFields() {
        if (roleSelect && roleSelect.value === 'admin') {
            customerLimitContainer.classList.remove('hidden');
        } else {
            customerLimitContainer.classList.add('hidden');
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', toggleRoleFields);
        toggleRoleFields();
    }
</script>
</x-app-layout>
