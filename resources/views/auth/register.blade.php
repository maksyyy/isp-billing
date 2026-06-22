<x-guest-layout>
    <div class="mb-8">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Akun Admin</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Daftarkan Admin Baru</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Akun ini akan menjadi admin utama yang dapat membuat sub-user finance, NOC, dan teknisi.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Admin</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="Nama Lengkap Anda"
                   required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs text-rose-400" />
        </div>

        <div>
            <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="nama@email.com"
                   required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-400" />
        </div>

        <div>
            <label for="phone" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">No. HP</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="0812xxxxxxxx"
                   required autocomplete="tel">
            <x-input-error :messages="$errors->get('phone')" class="mt-2 text-xs text-rose-400" />
        </div>

        <div>
            <label for="customer_limit" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Paket Jumlah User (Pelanggan)</label>
            <div class="relative">
                <select id="customer_limit" name="customer_limit"
                        class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white transition-all shadow-inner cursor-pointer appearance-none">
                    <option value="200" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 200 || !old('customer_limit'))>200 User</option>
                    <option value="500" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 500)>500 User</option>
                    <option value="1000" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 1000)>1000 User</option>
                    <option value="2000" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 2000)>2000 User</option>
                    <option value="3000" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 3000)>3000 User</option>
                    <option value="4000" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 4000)>4000 User</option>
                    <option value="5000" class="bg-[#0B0F19] text-white" @selected(old('customer_limit') == 5000)>5000 User</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('customer_limit')" class="mt-2 text-xs text-rose-400" />
        </div>

        <div>
            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Password</label>
            <input id="password" type="password" name="password"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-400" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-400" />
        </div>

        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-sm text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center cursor-pointer">
            Buat Akun Admin
        </button>

        <p class="text-center text-xs sm:text-sm text-slate-400 pt-2 border-t border-slate-900/60">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">
                Masuk
            </a>
        </p>
    </form>
</x-guest-layout>
