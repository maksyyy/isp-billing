<x-app-layout>
<div class="p-6">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">Master Panel</p>
        <h1 class="text-2xl font-bold mt-1">Kelola Admin Penyewa</h1>
        <p class="text-gray-600 mt-2">
            Pantau daftar admin yang menyewa website dan buat admin baru dari satu tempat.
        </p>

        <a href="{{ route('users.index') }}"
           class="inline-block mt-5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            Lihat Daftar Admin
        </a>
    </div>
</div>
</x-app-layout>
