<x-app-layout>
<div class="p-6">
    <div class="max-w-2xl bg-white rounded-lg shadow-sm border border-blue-100 p-6">
        <p class="text-sm font-semibold text-blue-600 uppercase">
            {{ $user->role == 'master' ? 'Akun Master' : 'Edit User' }}
        </p>
        <h2 class="text-2xl font-bold mt-1 text-blue-950">
            {{ $user->role == 'master' ? 'Ubah User Master' : 'Ubah Data ' . $user->name }}
        </h2>
        <p class="text-blue-700/70 mt-1 mb-6">
            Kosongkan password jika tidak ingin menggantinya.
        </p>

        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-blue-950 mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-blue-950 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-blue-950 mb-1">Role</label>
                <select name="role"
                        class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) == $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-blue-950 mb-1">Password Baru</label>
                    <input type="password" name="password"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-blue-950 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                           class="border p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}"
                   class="bg-white border border-blue-200 hover:border-blue-400 text-blue-700 px-4 py-2 rounded">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
