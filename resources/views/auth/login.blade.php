<x-guest-layout>
    <!-- Header / Title -->
    <div class="mb-8">
        <span class="text-xs font-bold uppercase tracking-widest text-[#6366F1]">Masuk Portal</span>
        <h2 class="text-3xl font-extrabold text-[#111111] mt-2 tracking-tight">Selamat Datang</h2>
        <p class="text-[#71717A] mt-2 text-xs sm:text-sm leading-relaxed">
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
            <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-[#71717A] mb-2">
                Alamat Email
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1] focus:ring-[#6366F1]/20 rounded-xl px-4 py-3 text-sm text-[#111111] placeholder-slate-400 transition-all shadow-inner"
                   placeholder="nama@email.com"
                   required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500" />
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-[#71717A] mb-2">
                Kata Sandi
            </label>
            <input id="password" type="password" name="password"
                   class="w-full bg-[#FFFFFF] border border-[#E4E4E7] focus:border-[#6366F1] focus:ring-[#6366F1]/20 rounded-xl px-4 py-3 text-sm text-[#111111] placeholder-slate-400 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500" />
        </div>

        <!-- Options Container -->
        <div class="flex items-center justify-between text-xs sm:text-sm">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox"
                       class="rounded border-[#E4E4E7] bg-white text-[#6366F1] focus:ring-[#6366F1]/20 focus:ring-offset-white"
                       name="remember">
                <span class="ms-2 text-[#71717A] hover:text-[#111111] transition-colors">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-[#6366F1] hover:text-[#8B5CF6] transition-colors" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <!-- Action Button -->
        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#6366F1] to-[#8B5CF6] font-bold text-sm text-white hover:opacity-95 transition-all shadow-lg shadow-[#6366F1]/10 active:scale-[0.99] flex items-center justify-center border-0">
            Masuk Portal &rarr;
        </button>

        <!-- Footer Redirection -->
        @if (Route::has('register'))
            <p class="text-center text-xs sm:text-sm text-[#71717A] pt-2 border-t border-[#E4E4E7]">
                Belum punya akun admin? 
                <a href="{{ route('register') }}" class="text-[#6366F1] hover:text-[#8B5CF6] font-bold transition-colors">
                    Daftar Sekarang
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>

