<x-app-layout>
<div class="p-6">
    <div class="max-w-2xl bg-white rounded-lg shadow p-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">
            {{ auth()->user()->role == 'master' ? 'Admin Penyewa' : 'Sub User' }}
        </p>
        <h2 class="text-2xl font-bold mt-1">
            Tambah {{ auth()->user()->role == 'master' ? 'Admin Baru' : 'Sub User Baru' }}
        </h2>
        <p class="text-gray-500 mt-1 mb-6">
            {{ auth()->user()->role == 'master'
                ? 'Admin baru dapat mengelola tim finance, NOC, dan teknisi masing-masing.'
                : 'Sub-user akan otomatis berada di bawah akun admin Anda.' }}
        </p>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password"
                       class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                <select name="role" class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 pt-2">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                    Simpan
                </button>
                <a href="{{ route('users.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
