<x-app-layout>
<div class="p-6 max-w-xl">

    <h2 class="text-xl font-bold mb-4">Tambah User</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <input type="text" name="name" placeholder="Nama"
               class="border p-2 w-full mb-3" required>

        <input type="email" name="email" placeholder="Email"
               class="border p-2 w-full mb-3" required>

        <input type="password" name="password" placeholder="Password"
               class="border p-2 w-full mb-3" required>

        <select name="role" class="border p-2 w-full mb-3">
            <option value="admin">Admin</option>
            <option value="finance">Finance</option>
            <option value="teknisi">Teknisi</option>
            <option value="noc">NOC</option>
        </select>

        <button class="bg-green-600 text-white px-4 py-2 rounded">
            Simpan
        </button>
    </form>

</div>
</x-app-layout>