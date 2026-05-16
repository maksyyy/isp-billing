<x-app-layout>
<div class="p-6">
    @if(auth()->user()->role == 'master')
        <div class="mb-6 rounded-lg border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase">Akun Master</p>
                    <h1 class="text-xl font-bold text-blue-950">{{ auth()->user()->name }}</h1>
                    <p class="text-blue-700/70">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('users.edit', auth()->id()) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    Edit Master
                </a>
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-semibold text-blue-600 uppercase">
                {{ auth()->user()->role == 'master' ? 'Master Control' : 'User Management' }}
            </p>
            <h2 class="text-2xl font-bold">{{ $title }}</h2>
            <p class="text-gray-500 mt-1">
                {{ auth()->user()->role == 'master'
                    ? 'Admin di bawah ini adalah akun utama penyewa website.'
                    : 'Sub-user ini berada di bawah akun admin Anda.' }}
            </p>
        </div>

        <a href="{{ route('users.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Tambah {{ auth()->user()->role == 'master' ? 'Admin' : 'Sub User' }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Role</th>
                    @if(auth()->user()->role == 'master')
                        <th class="p-3 text-left">Sub User</th>
                    @endif
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $u)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold">{{ $u->name }}</td>
                        <td class="p-3">{{ $u->email }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-semibold uppercase">
                                {{ $u->role }}
                            </span>
                        </td>
                        @if(auth()->user()->role == 'master')
                            <td class="p-3">{{ $u->sub_users_count ?? 0 }} user</td>
                        @endif
                        <td class="p-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('users.edit', $u->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                    Edit
                                </a>

                            <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')

                                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                    Hapus
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role == 'master' ? 5 : 4 }}" class="p-6 text-center text-gray-500">
                            Belum ada data user.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
