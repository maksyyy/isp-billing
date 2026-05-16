<x-guest-layout>
    <div class="mb-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">Akun Admin</p>
        <h2 class="text-2xl font-bold mt-1">Daftarkan admin baru</h2>
        <p class="text-gray-500 mt-2">
            Akun ini akan menjadi admin utama yang dapat membuat sub-user finance, NOC, dan teknisi.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Admin</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
            <input id="password" type="password" name="password"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow">
            Buat Akun Admin
        </button>

        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                Masuk
            </a>
        </p>
    </form>
</x-guest-layout>
