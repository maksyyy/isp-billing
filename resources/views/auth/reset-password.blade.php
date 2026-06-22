<x-guest-layout>
    <div class="mb-8">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Atur Ulang</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Kata Sandi Baru</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Silakan masukkan email Anda dan tentukan kata sandi baru Anda di bawah ini.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="nama@email.com"
                   required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi Baru</label>
            <input id="password" type="password" name="password"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Konfirmasi Kata Sandi Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="w-full bg-[#0B0F19]/60 border border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/30 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 transition-all shadow-inner"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-400" />
        </div>

        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-sm text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center cursor-pointer">
            Perbarui Kata Sandi
        </button>
    </form>
</x-guest-layout>
