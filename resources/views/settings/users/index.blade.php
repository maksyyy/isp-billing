<x-app-layout>
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">User Management</h2>

    <a href="{{ route('users.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded">
        + Tambah User
    </a>

    <table class="w-full mt-4 border">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2">Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
            <tr class="border">
                <td class="p-2">{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                    <span class="px-2 py-1 bg-gray-300 rounded">
                        {{ $u->role }}
                    </span>
                </td>
                <td>
                    <form action="{{ route('users.destroy', $u->id) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button class="bg-red-500 text-white px-2 py-1 rounded">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
</x-app-layout>