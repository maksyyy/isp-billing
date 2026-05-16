<x-app-layout>
<div class="p-6">
    <div class="max-w-3xl bg-white rounded-lg shadow-sm border border-blue-100 p-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">Branding</p>
        <h1 class="text-2xl font-bold mt-1 text-blue-950">Logo Perusahaan</h1>
        <p class="text-blue-700/70 mt-2">
            Logo ini akan tampil di halaman awal, login/register, dan sidebar aplikasi.
        </p>

        @if(session('success'))
            <div class="mt-5 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-900 mb-3">Preview Logo</p>
            <x-company-logo class="text-blue-950" mark-class="h-16 w-16" text-class="text-2xl" />
        </div>

        <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-blue-950 mb-1">Upload Logo</label>
                <input type="file" name="company_logo" accept="image/png,image/jpeg,image/webp"
                       class="block w-full rounded border border-blue-200 bg-white p-2 text-blue-950 focus:border-blue-500 focus:ring-blue-500"
                       required>
                <p class="text-xs text-blue-700/70 mt-2">Format: PNG, JPG, JPEG, atau WEBP. Maksimal 2 MB.</p>
                @error('company_logo')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                Simpan Logo
            </button>
        </form>
    </div>
</div>
</x-app-layout>
