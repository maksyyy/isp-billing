<x-guest-layout>
    <!-- Header / Title -->
    <div class="mb-8">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Masuk Portal</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Selamat Datang</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Silakan masuk untuk mengelola billing, penagihan pelanggan, tiket operasional, dan monitoring PRTG.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                Alamat Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="nama@email.com"
                   required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                Kata Sandi
            </label>
            <input id="password" type="password" name="password"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Options Container -->
        <div class="flex items-center justify-between text-xs sm:text-sm">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox"
                       class="rounded border-slate-800 bg-[#0B0F19]/60 text-indigo-600 focus:ring-indigo-500/30 focus:ring-offset-slate-900"
                       name="remember">
                <span class="ms-2 text-slate-400 hover:text-slate-300 transition-colors">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <!-- Action Button -->
        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-sm text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center">
            Masuk Portal &rarr;
        </button>

        <!-- Footer Redirection -->
        @if (Route::has('register'))
            <p class="text-center text-xs sm:text-sm text-slate-400 pt-2 border-t border-slate-900/60">
                Belum punya akun admin? 
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">
                    Daftar Sekarang
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>

