<x-guest-layout>
    <div class="mb-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">Masuk Dashboard</p>
        <h2 class="text-2xl font-bold mt-1">Selamat datang kembali</h2>
        <p class="text-gray-500 mt-2">Masuk untuk mengelola billing, pelanggan, invoice, dan monitoring tim.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
            <input id="password" type="password" name="password"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-700 font-semibold" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow">
            Masuk
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-gray-500">
                Belum punya akun admin?
                <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Daftar sekarang
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
