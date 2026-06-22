<x-guest-layout>
    <div class="mb-6">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Verifikasi</span>
        <h2 class="text-3xl font-extrabold text-white mt-2 tracking-tight">Verifikasi Email</h2>
        <p class="text-slate-400 mt-2 text-xs sm:text-sm leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan yang baru.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-bold text-xs text-green-400">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500 font-bold text-xs text-white hover:brightness-110 transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.99] flex items-center justify-center cursor-pointer uppercase tracking-wider">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#0B0F19] border border-slate-800 text-slate-400 hover:text-white transition-all text-xs font-bold uppercase tracking-wider cursor-pointer">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
