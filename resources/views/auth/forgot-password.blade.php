<x-guest-layout>
    <div class="mb-6">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Lupa Password</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Setel Ulang Password</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Masukkan alamat email Anda, dan kami akan mengirimkan tautan untuk menyetel ulang password baru Anda.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs text-green-400 font-bold" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="nama@email.com"
                   required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-400" />
        </div>

        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-sm text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center cursor-pointer">
            Kirim Tautan Reset Password
        </button>

        <p class="text-center text-xs sm:text-sm text-slate-400 pt-2 border-t border-slate-900/60">
            Kembali ke 
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">
                Masuk
            </a>
        </p>
    </form>
</x-guest-layout>
