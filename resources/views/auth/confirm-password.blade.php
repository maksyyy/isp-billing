<x-guest-layout>
    <div class="mb-6">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Keamanan</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Konfirmasi Kata Sandi</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Ini adalah area aman aplikasi. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi</label>
            <input id="password" type="password" name="password"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-400" />
        </div>

        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-sm text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center cursor-pointer">
            Konfirmasi
        </button>
    </form>
</x-guest-layout>
